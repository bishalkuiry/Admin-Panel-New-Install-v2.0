<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerComplaint;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReturn;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $q = trim($request->input('q'));

        if (empty($q) || strlen($q) < 2) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $results = [
            'orders' => Order::where('order_number', 'like', "%{$q}%")
                ->orWhere('id', $q)
                ->limit(5)
                ->get(['id', 'order_number', 'total', 'status'])
                ->map(fn($o) => [
                    'title' => "Order #{$o->order_number}",
                    'subtitle' => "Total: ₹{$o->total} | Status: " . (is_object($o->status) ? $o->status->value : $o->status),
                    'url' => route('admin.orders.show', $o->id),
                    'type' => 'Order',
                ]),

            'products' => Product::where('name', 'like', "%{$q}%")
                ->orWhere('sku', 'like', "%{$q}%")
                ->orWhere('barcode', 'like', "%{$q}%")
                ->limit(5)
                ->get(['id', 'name', 'price', 'sku'])
                ->map(fn($p) => [
                    'title' => $p->name,
                    'subtitle' => "Price: ₹{$p->price} | SKU: {$p->sku}",
                    'url' => route('admin.products.edit', $p->id),
                    'type' => 'Product',
                ]),

            'stores' => Store::where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('phone', 'like', "%{$q}%")
                ->limit(5)
                ->get(['id', 'name', 'phone', 'status'])
                ->map(fn($s) => [
                    'title' => $s->name,
                    'subtitle' => "Phone: {$s->phone} | Status: {$s->status}",
                    'url' => route('admin.stores.show', $s->id),
                    'type' => 'Store',
                ]),

            'customers' => User::where('role', 'customer')
                ->where(function ($query) use ($q) {
                    $query->where('name', 'like', "%{$q}%")
                          ->orWhere('email', 'like', "%{$q}%")
                          ->orWhere('phone', 'like', "%{$q}%");
                })
                ->limit(5)
                ->get(['id', 'name', 'email', 'phone'])
                ->map(fn($u) => [
                    'title' => $u->name,
                    'subtitle' => "Email: {$u->email} | Phone: {$u->phone}",
                    'url' => route('admin.users.show', $u->id),
                    'type' => 'Customer',
                ]),

            'riders' => User::where('role', 'delivery_partner')
                ->where(function ($query) use ($q) {
                    $query->where('name', 'like', "%{$q}%")
                          ->orWhere('email', 'like', "%{$q}%")
                          ->orWhere('phone', 'like', "%{$q}%");
                })
                ->limit(5)
                ->get(['id', 'name', 'email', 'phone'])
                ->map(fn($r) => [
                    'title' => $r->name,
                    'subtitle' => "Phone: {$r->phone}",
                    'url' => route('admin.delivery-partners.edit', $r->id),
                    'type' => 'Delivery Partner',
                ]),

            'invoices' => Invoice::where('invoice_number', 'like', "%{$q}%")
                ->limit(5)
                ->get(['id', 'invoice_number', 'total_amount'])
                ->map(fn($i) => [
                    'title' => "Invoice #{$i->invoice_number}",
                    'subtitle' => "Amount: ₹{$i->total_amount}",
                    'url' => route('admin.invoices.show', $i->id),
                    'type' => 'Invoice',
                ]),

            'returns' => ProductReturn::where('return_number', 'like', "%{$q}%")
                ->limit(5)
                ->get(['id', 'return_number', 'status'])
                ->map(fn($r) => [
                    'title' => "Return #{$r->return_number}",
                    'subtitle' => "Status: {$r->status}",
                    'url' => route('admin.returns.index', ['search' => $r->return_number]),
                    'type' => 'Product Return',
                ]),

            'complaints' => CustomerComplaint::where('ticket_number', 'like', "%{$q}%")
                ->limit(5)
                ->get(['id', 'ticket_number', 'status'])
                ->map(fn($c) => [
                    'title' => "Complaint #{$c->ticket_number}",
                    'subtitle' => "Status: {$c->status}",
                    'url' => route('admin.complaints.index'),
                    'type' => 'Complaint',
                ]),
        ];

        // Flatten all non-empty search categories
        $merged = [];
        foreach ($results as $category => $items) {
            foreach ($items as $item) {
                $merged[] = $item;
            }
        }

        return response()->json([
            'success' => true,
            'query' => $q,
            'data' => $merged,
        ]);
    }
}
