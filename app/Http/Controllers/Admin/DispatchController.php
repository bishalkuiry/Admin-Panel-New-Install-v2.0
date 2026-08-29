<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

class DispatchController extends Controller
{
    /**
     * Get dynamic module definitions for all core and plugin order types
     */
    protected function getModuleRegistry()
    {
        // Core E-Commerce Store Default Module
        $registry = [
            [
                'table'          => 'orders',
                'module_type'    => 'ecommerce',
                'module_name'    => 'E-Commerce Store',
                'badge'          => 'badge-orange',
                'icon'           => '🛍️',
                'number_col'     => 'order_number',
                'amount_col'     => 'total',
                'user_col'       => 'user_id',
                'provider_col'   => 'store_id',
                'provider_table' => 'stores',
            ]
        ];

        // 1. Dynamically discover dispatch config from installed plugins' plugin.json
        try {
            $pluginsPath = base_path('plugins');
            if (File::exists($pluginsPath)) {
                $directories = File::directories($pluginsPath);
                foreach ($directories as $dir) {
                    $manifestFile = $dir . '/plugin.json';
                    if (File::exists($manifestFile)) {
                        $manifest = json_decode(File::get($manifestFile), true);
                        if ($manifest && !empty($manifest['show_in_dispatch']) && !empty($manifest['dispatch'])) {
                            $dispatchConf = $manifest['dispatch'];
                            // Ensure table is unique in registry
                            $alreadyIn = false;
                            foreach ($registry as $item) {
                                if ($item['table'] === ($dispatchConf['table'] ?? '')) {
                                    $alreadyIn = true;
                                    break;
                                }
                            }
                            if (!$alreadyIn) {
                                $registry[] = $dispatchConf;
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Fail silently
        }

        // 2. Default convention fallbacks for popular addon modules if installed without manifest declaration
        $conventionAddons = [
            [
                'table'          => 'rides',
                'module_type'    => 'ride',
                'module_name'    => 'Ride Sharing (Taxi)',
                'badge'          => 'badge-purple',
                'icon'           => '🚖',
                'number_col'     => 'ride_number',
                'amount_col'     => 'total_fare',
                'user_col'       => 'rider_id',
                'provider_col'   => 'driver_id',
                'provider_table' => 'users',
            ],
            [
                'table'          => 'service_bookings',
                'module_type'    => 'service',
                'module_name'    => 'Service Booking',
                'badge'          => 'badge-blue',
                'icon'           => '🛠️',
                'number_col'     => 'booking_number',
                'amount_col'     => 'total_amount',
                'user_col'       => 'user_id',
                'provider_col'   => 'provider_id',
                'provider_table' => 'users',
            ],
            [
                'table'          => 'parcels',
                'module_type'    => 'parcel',
                'module_name'    => 'Parcel Delivery',
                'badge'          => 'badge-green',
                'icon'           => '📦',
                'number_col'     => 'parcel_number',
                'amount_col'     => 'total_fee',
                'user_col'       => 'sender_id',
                'provider_col'   => 'driver_id',
                'provider_table' => 'users',
            ],
            [
                'table'          => 'parcel_orders',
                'module_type'    => 'parcel',
                'module_name'    => 'Parcel Delivery',
                'badge'          => 'badge-green',
                'icon'           => '📦',
                'number_col'     => 'order_number',
                'amount_col'     => 'total',
                'user_col'       => 'user_id',
                'provider_col'   => 'driver_id',
                'provider_table' => 'users',
            ],
            [
                'table'          => 'digital_printing_orders',
                'module_type'    => 'printing',
                'module_name'    => 'Digital Printing',
                'badge'          => 'badge-purple',
                'icon'           => '🖨️',
                'number_col'     => 'order_number',
                'amount_col'     => 'total',
                'user_col'       => 'user_id',
                'provider_col'   => 'vendor_id',
                'provider_table' => 'stores',
            ],
            [
                'table'          => 'printing_orders',
                'module_type'    => 'printing',
                'module_name'    => 'Digital Printing',
                'badge'          => 'badge-purple',
                'icon'           => '🖨️',
                'number_col'     => 'order_number',
                'amount_col'     => 'total',
                'user_col'       => 'user_id',
                'provider_col'   => 'vendor_id',
                'provider_table' => 'stores',
            ],
            [
                'table'          => 'doctor_appointments',
                'module_type'    => 'doctor',
                'module_name'    => 'Doctor Appointment',
                'badge'          => 'badge-red',
                'icon'           => '🏥',
                'number_col'     => 'appointment_number',
                'amount_col'     => 'total_fee',
                'user_col'       => 'patient_id',
                'provider_col'   => 'doctor_id',
                'provider_table' => 'users',
            ],
            [
                'table'          => 'food_orders',
                'module_type'    => 'food',
                'module_name'    => 'Food Delivery',
                'badge'          => 'badge-orange',
                'icon'           => '🍕',
                'number_col'     => 'order_number',
                'amount_col'     => 'total',
                'user_col'       => 'user_id',
                'provider_col'   => 'restaurant_id',
                'provider_table' => 'stores',
            ]
        ];

        foreach ($conventionAddons as $addon) {
            $exists = false;
            foreach ($registry as $r) {
                if (($r['table'] ?? '') === $addon['table']) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $registry[] = $addon;
            }
        }

        return $registry;
    }

    /**
     * Display unified Dispatch Management Center for all InAllCart modules and plugins
     */
    public function index(Request $request)
    {
        $selectedModule = $request->query('module', 'all');
        $search = strtolower(trim($request->query('search', '')));

        $unifiedOrders = collect();
        $activeModulesList = [];

        $totalOrdersCount = 0;
        $totalRevenue = 0;

        $registry = $this->getModuleRegistry();

        foreach ($registry as $mod) {
            $tableName = $mod['table'] ?? null;
            if (!$tableName || !Schema::hasTable($tableName)) {
                continue;
            }

            // Record as active module available on system
            $moduleType = $mod['module_type'] ?? 'custom';
            if (!isset($activeModulesList[$moduleType])) {
                $activeModulesList[$moduleType] = [
                    'type'    => $moduleType,
                    'name'    => $mod['module_name'] ?? ucfirst($moduleType),
                    'icon'    => $mod['icon'] ?? '📦',
                    'badge'   => $mod['badge'] ?? 'badge-gray',
                    'count'   => 0,
                    'revenue' => 0,
                ];
            }

            // Get columns of this table
            $columns = Schema::getColumnListing($tableName);
            $numberCol = in_array($mod['number_col'] ?? '', $columns) ? $mod['number_col'] : 'id';
            $amountCol = in_array($mod['amount_col'] ?? '', $columns) ? $mod['amount_col'] : (in_array('total', $columns) ? 'total' : 'id');
            $statusCol = in_array('status', $columns) ? 'status' : null;
            $payStatusCol = in_array('payment_status', $columns) ? 'payment_status' : null;
            $payMethodCol = in_array('payment_method', $columns) ? 'payment_method' : null;
            $userCol = in_array($mod['user_col'] ?? '', $columns) ? $mod['user_col'] : (in_array('user_id', $columns) ? 'user_id' : null);
            $providerCol = in_array($mod['provider_col'] ?? '', $columns) ? $mod['provider_col'] : null;

            // Stats
            $count = DB::table($tableName)->count();
            $rev = $amountCol !== 'id' 
                ? DB::table($tableName)->when($statusCol, function($q) use ($statusCol) { $q->whereNotIn($statusCol, ['cancelled', 'rejected']); })->sum($amountCol) 
                : 0;

            $activeModulesList[$moduleType]['count'] += $count;
            $activeModulesList[$moduleType]['revenue'] += $rev;

            $totalOrdersCount += $count;
            $totalRevenue += $rev;

            // Fetch records if matching filter
            if ($selectedModule === 'all' || $selectedModule === $moduleType) {
                $query = DB::table($tableName);

                if ($userCol && Schema::hasTable('users')) {
                    $query->leftJoin('users as u_customer', "{$tableName}.{$userCol}", '=', 'u_customer.id');
                }

                if ($providerCol) {
                    $provTable = $mod['provider_table'] ?? 'users';
                    if (Schema::hasTable($provTable)) {
                        $query->leftJoin("{$provTable} as u_provider", "{$tableName}.{$providerCol}", '=', 'u_provider.id');
                    }
                }

                $selects = ["{$tableName}.id", "{$tableName}.created_at"];
                if ($numberCol !== 'id') $selects[] = "{$tableName}.{$numberCol} as order_num_val";
                if ($amountCol !== 'id') $selects[] = "{$tableName}.{$amountCol} as amount_val";
                if ($statusCol) $selects[] = "{$tableName}.{$statusCol} as status_val";
                if ($payStatusCol) $selects[] = "{$tableName}.{$payStatusCol} as pay_status_val";
                if ($payMethodCol) $selects[] = "{$tableName}.{$payMethodCol} as pay_method_val";

                if ($userCol && Schema::hasTable('users')) {
                    $selects[] = 'u_customer.name as cust_name';
                    $selects[] = 'u_customer.email as cust_email';
                }
                if ($providerCol && Schema::hasTable($mod['provider_table'] ?? 'users')) {
                    $selects[] = 'u_provider.name as prov_name';
                }

                $records = $query->select($selects)->get()->map(function($r) use ($mod) {
                    $num = $r->order_num_val ?? ('#' . $r->id);
                    $amount = (float) ($r->amount_val ?? 0);
                    $status = strtolower($r->status_val ?? 'pending');
                    $payStatus = strtolower($r->pay_status_val ?? 'pending');
                    $payMethod = strtoupper($r->pay_method_val ?? 'ONLINE');

                    $mType = strtolower($mod['module_type'] ?? '');
                    if ($mType === 'ecommerce' || $mType === 'food' || $mType === 'grocery' || $mType === 'pharmacy') {
                        $viewUrl = route('admin.orders.show', $r->id);
                    } elseif ($mType === 'ride' || $mType === 'taxi') {
                        $viewUrl = \Illuminate\Support\Facades\Route::has('admin.ride-sharing.rides.show') 
                            ? route('admin.ride-sharing.rides.show', $r->id) 
                            : (\Illuminate\Support\Facades\Route::has('admin.ride-sharing.rides.index') ? route('admin.ride-sharing.rides.index') : route('admin.orders.show', $r->id));
                    } elseif ($mType === 'service') {
                        $viewUrl = \Illuminate\Support\Facades\Route::has('admin.service-booking.bookings.show') 
                            ? route('admin.service-booking.bookings.show', $r->id) 
                            : (\Illuminate\Support\Facades\Route::has('admin.service-booking.bookings.index') ? route('admin.service-booking.bookings.index') : route('admin.orders.show', $r->id));
                    } else {
                        $viewUrl = route('admin.orders.show', $r->id);
                    }

                    return (object) [
                        'module_type'    => $mod['module_type'] ?? 'custom',
                        'module_name'    => $mod['module_name'] ?? 'Plugin Module',
                        'module_badge'   => $mod['badge'] ?? 'badge-gray',
                        'icon'           => $mod['icon'] ?? '📦',
                        'id'             => $r->id,
                        'order_number'   => $num,
                        'customer_name'  => $r->cust_name ?? 'Customer',
                        'customer_email' => $r->cust_email ?? '-',
                        'provider_name'  => $r->prov_name ?? 'Assigned Agent / Store',
                        'total_amount'   => $amount,
                        'payment_method' => $payMethod,
                        'payment_status' => $payStatus,
                        'status'         => $status,
                        'created_at'     => $r->created_at,
                        'view_url'       => $viewUrl,
                        'details'        => [
                            'Module'         => $mod['module_name'] ?? 'Plugin Module',
                            'Order Number'   => $num,
                            'Total Amount'   => '$' . number_format($amount, 2),
                            'Payment Method' => $payMethod,
                            'Payment Status' => ucfirst($payStatus),
                            'Status'         => ucfirst($status),
                        ]
                    ];
                });

                $unifiedOrders = $unifiedOrders->concat($records);
            }
        }

        // Search Filter
        if ($search !== '') {
            $unifiedOrders = $unifiedOrders->filter(function ($item) use ($search) {
                return str_contains(strtolower($item->order_number), $search) ||
                       str_contains(strtolower($item->customer_name), $search) ||
                       str_contains(strtolower($item->provider_name), $search) ||
                       str_contains(strtolower($item->module_name), $search);
            });
        }

        // Sort by created_at DESC
        $unifiedOrders = $unifiedOrders->sortByDesc('created_at')->values();

        return view('admin.dispatch.index', compact(
            'unifiedOrders',
            'selectedModule',
            'search',
            'totalOrdersCount',
            'totalRevenue',
            'activeModulesList'
        ));
    }
}
