<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Payment\PaymentService;
use Illuminate\Http\JsonResponse;

class DebugController extends Controller
{
    public function paymentGatewaysDebug(PaymentService $paymentService): JsonResponse
    {
        $debug = [];
        
        // Check config
        $debug['razorpay_config'] = config('payment.gateways.razorpay');
        
        // Check gateway instance
        $razorpayGateway = $paymentService->getGateway('razorpay');
        $debug['razorpay_gateway_exists'] = $razorpayGateway !== null;
        
        if ($razorpayGateway) {
            $debug['razorpay_is_enabled'] = $razorpayGateway->isEnabled();
        }
        
        // Get all enabled gateways
        $debug['enabled_gateways'] = $paymentService->getEnabledGateways();
        
        // Check all gateways
        $allGateways = ['razorpay', 'paystack', 'stripe', 'flutterwave', 'paytm', 'phonepe'];
        $debug['all_gateways_status'] = [];
        
        foreach ($allGateways as $gatewayName) {
            $gateway = $paymentService->getGateway($gatewayName);
            $debug['all_gateways_status'][$gatewayName] = [
                'exists' => $gateway !== null,
                'enabled' => $gateway ? $gateway->isEnabled() : false,
            ];
        }
        
        return response()->json([
            'success' => true,
            'debug' => $debug,
        ]);
    }
}
