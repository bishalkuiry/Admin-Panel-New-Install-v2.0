<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Get available payment methods
     */
    public function getPaymentMethods(): JsonResponse
    {
        $methods = $this->paymentService->getEnabledGateways();

        return response()->json([
            'success' => true,
            'data' => $methods,
        ]);
    }

    /**
     * Initialize payment
     */
    public function initializePayment(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_gateway' => 'required|string',
        ]);

        $order = Order::with('user')->findOrFail($request->order_id);

        // Check if user owns the order
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $additionalData = [
            'currency' => $request->currency ?? 'INR',
            'callback_url' => $request->callback_url,
            'email' => $order->user->email,
            'name' => $order->user->name,
            'mobile' => $order->user->phone,
            'user_id' => $order->user_id,
        ];

        $result = $this->paymentService->createPayment(
            $order,
            $request->payment_gateway,
            $additionalData
        );

        return response()->json($result);
    }

    /**
     * Verify payment
     */
    public function verifyPayment(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_gateway' => 'required|string',
        ]);

        $order = Order::findOrFail($request->order_id);

        // Check if user owns the order
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $verified = $this->paymentService->verifyPayment(
            $order,
            $request->payment_gateway,
            $request->all()
        );

        if ($verified) {
            return response()->json([
                'success' => true,
                'message' => 'Payment verified successfully',
                'order' => $order->fresh(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Payment verification failed',
        ], 400);
    }

    /**
     * Razorpay webhook
     */
    public function razorpayWebhook(Request $request): JsonResponse
    {
        $signature = $request->header('X-Razorpay-Signature');
        $payload = $request->getContent();

        $gateway = $this->paymentService->getGateway('razorpay');
        
        if (!$gateway->verifyWebhookSignature($payload, $signature)) {
            return response()->json(['success' => false], 400);
        }

        $event = $request->all();

        // Handle different events
        switch ($event['event']) {
            case 'payment.captured':
                $this->handlePaymentCaptured($event['payload']['payment']['entity']);
                break;
            case 'payment.failed':
                $this->handlePaymentFailed($event['payload']['payment']['entity']);
                break;
        }

        return response()->json(['success' => true]);
    }

    /**
     * Paystack webhook
     */
    public function paystackWebhook(Request $request): JsonResponse
    {
        $signature = $request->header('x-paystack-signature');
        $payload = $request->getContent();
        $gateway = $this->paymentService->getGateway('paystack');

        if (!$gateway->verifyWebhookSignature($payload, $signature)) {
            return response()->json(['success' => false], 400);
        }

        $event = $request->all();

        if ($event['event'] === 'charge.success') {
            $this->handlePaystackSuccess($event['data']);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Stripe webhook
     */
    public function stripeWebhook(Request $request): JsonResponse
    {
        $signature = $request->header('Stripe-Signature');
        $payload = $request->getContent();
        $gateway = $this->paymentService->getGateway('stripe');

        if (!$gateway->verifyWebhookSignature($payload, $signature)) {
            return response()->json(['success' => false], 400);
        }

        $event = $request->all();

        if ($event['type'] === 'payment_intent.succeeded') {
            $this->handleStripeSuccess($event['data']['object']);
        }

        return response()->json(['success' => true]);
    }

    protected function handlePaymentCaptured(array $payment): void
    {
        $order = Order::where('payment_id', $payment['order_id'])->first();
        
        if ($order) {
            $order->update([
                'payment_status' => 'paid',
                'transaction_id' => $payment['id'],
            ]);
        }
    }

    protected function handlePaymentFailed(array $payment): void
    {
        $order = Order::where('payment_id', $payment['order_id'])->first();
        
        if ($order) {
            $order->update([
                'payment_status' => 'failed',
            ]);
        }
    }

    protected function handlePaystackSuccess(array $data): void
    {
        $order = Order::where('order_number', $data['reference'])->first();
        
        if ($order) {
            $order->update([
                'payment_status' => 'paid',
                'transaction_id' => $data['id'],
            ]);
        }
    }

    protected function handleStripeSuccess(array $data): void
    {
        $order = Order::where('payment_id', $data['id'])->first();
        
        if ($order) {
            $order->update([
                'payment_status' => 'paid',
                'transaction_id' => $data['id'],
            ]);
        }
    }
}
