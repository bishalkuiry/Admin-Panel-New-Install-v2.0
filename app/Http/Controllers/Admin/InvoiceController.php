<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Setting;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    public function __construct(private StorageService $storage) {}
    /**
     * List all invoices
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['order', 'store', 'user']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('invoice_number', 'like', "%{$request->search}%")
                  ->orWhere('customer_name', 'like', "%{$request->search}%")
                  ->orWhereHas('order', fn($q) => $q->where('order_number', 'like', "%{$request->search}%"));
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->date_from) {
            $query->whereDate('issued_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('issued_at', '<=', $request->date_to);
        }

        $invoices = $query->latest()->paginate(20);

        $stats = [
            'total' => Invoice::count(),
            'issued' => Invoice::where('status', 'issued')->count(),
            'paid' => Invoice::where('status', 'paid')->count(),
            'total_revenue' => Invoice::whereIn('status', ['issued', 'paid'])->sum('total'),
        ];

        // Invoice template settings
        $invoiceSettings = [
            'invoice_logo' => Setting::get('invoice_logo', ''),
            'invoice_company_name' => Setting::get('invoice_company_name', Setting::get('app_name', config('app.name'))),
            'invoice_company_address' => Setting::get('invoice_company_address', ''),
            'invoice_company_phone' => Setting::get('invoice_company_phone', ''),
            'invoice_company_email' => Setting::get('invoice_company_email', ''),
            'invoice_gstin' => Setting::get('invoice_gstin', ''),
            'invoice_prefix' => Setting::get('invoice_prefix', 'INV'),
            'invoice_accent_color' => Setting::get('invoice_accent_color', '#111827'),
            'invoice_footer_text' => Setting::get('invoice_footer_text', 'Thank you for your business!'),
            'invoice_terms' => Setting::get('invoice_terms', ''),
            'invoice_show_logo' => Setting::get('invoice_show_logo', '1'),
            'invoice_show_store_info' => Setting::get('invoice_show_store_info', '1'),
        ];

        return view('admin.invoices.index', compact('invoices', 'stats', 'invoiceSettings'));
    }

    /**
     * Update invoice template settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'invoice_logo' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'invoice_company_name' => 'nullable|string|max:255',
            'invoice_company_address' => 'nullable|string|max:1000',
            'invoice_company_phone' => 'nullable|string|max:50',
            'invoice_company_email' => 'nullable|email|max:255',
            'invoice_gstin' => 'nullable|string|max:50',
            'invoice_prefix' => 'nullable|string|max:10',
            'invoice_accent_color' => 'nullable|string|max:20',
            'invoice_footer_text' => 'nullable|string|max:500',
            'invoice_terms' => 'nullable|string|max:2000',
        ]);

        // Handle logo upload
        if ($request->hasFile('invoice_logo')) {
            $oldLogo = Setting::get('invoice_logo');
            if ($oldLogo) {
                $this->storage->delete($oldLogo);
            }
            $logoPath = $this->storage->store($request->file('invoice_logo'), 'invoice');
            Setting::set('invoice_logo', $logoPath, 'invoice');
        }

        // Handle logo removal
        if ($request->boolean('remove_logo')) {
            $oldLogo = Setting::get('invoice_logo');
            if ($oldLogo) {
                $this->storage->delete($oldLogo);
            }
            Setting::set('invoice_logo', '', 'invoice');
        }

        // Save all text settings
        $settingsKeys = [
            'invoice_company_name', 'invoice_company_address', 'invoice_company_phone',
            'invoice_company_email', 'invoice_gstin', 'invoice_prefix',
            'invoice_accent_color', 'invoice_footer_text', 'invoice_terms',
        ];

        foreach ($settingsKeys as $key) {
            if ($request->has($key)) {
                Setting::set($key, $request->input($key, ''), 'invoice');
            }
        }

        // Toggle settings
        Setting::set('invoice_show_logo', $request->boolean('invoice_show_logo') ? '1' : '0', 'invoice');
        Setting::set('invoice_show_store_info', $request->boolean('invoice_show_store_info') ? '1' : '0', 'invoice');

        return back()->with('success', 'Invoice settings updated successfully.');
    }

    /**
     * Show invoice preview
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['order.items.product', 'store', 'user']);
        return view('admin.invoices.show', compact('invoice'));
    }

    /**
     * Edit invoice
     */
    public function edit(Invoice $invoice)
    {
        $invoice->load(['order.items.product', 'store', 'user']);
        return view('admin.invoices.edit', compact('invoice'));
    }

    /**
     * Update invoice
     */
    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'business_name' => 'nullable|string|max:255',
            'business_address' => 'nullable|string|max:1000',
            'business_phone' => 'nullable|string|max:50',
            'business_email' => 'nullable|email|max:255',
            'business_gstin' => 'nullable|string|max:50',
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'shipping_address' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:2000',
            'status' => 'required|in:draft,issued,paid,cancelled',
            'due_date' => 'nullable|date',
        ]);

        $invoice->update($validated);

        return redirect()->route('admin.invoices.show', $invoice)->with('success', 'Invoice updated successfully.');
    }

    /**
     * Generate invoice from order (used from order details page)
     */
    public function createFromOrder(Order $order)
    {
        $existing = Invoice::where('order_id', $order->id)->first();
        if ($existing) {
            return redirect()->route('admin.invoices.show', $existing)->with('info', 'Invoice already exists for this order.');
        }

        $invoice = Invoice::createFromOrder($order);

        return redirect()->route('admin.invoices.show', $invoice)->with('success', 'Invoice generated successfully.');
    }

    /**
     * Print-optimized invoice view
     */
    public function print(Invoice $invoice)
    {
        $invoice->load(['order.items.product', 'store', 'user']);
        return view('admin.invoices.print', compact('invoice'));
    }

    /**
     * Delete invoice
     */
    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return redirect()->route('admin.invoices.index')->with('success', 'Invoice deleted successfully.');
    }
}
