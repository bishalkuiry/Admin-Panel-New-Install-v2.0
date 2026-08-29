<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Settings
    |--------------------------------------------------------------------------
    */
    'name' => env('APP_NAME', 'InAllCart'),
    'currency' => env('CURRENCY', 'INR'),
    'currency_symbol' => env('CURRENCY_SYMBOL', '₹'),

    /*
    |--------------------------------------------------------------------------
    | Order Settings
    |--------------------------------------------------------------------------
    */
    'order' => [
        'min_order_amount' => env('MIN_ORDER_AMOUNT', 99),
        'free_delivery_above' => env('FREE_DELIVERY_ABOVE', 499),
        'default_delivery_fee' => env('DEFAULT_DELIVERY_FEE', 25),
        'tax_percentage' => env('TAX_PERCENTAGE', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Delivery Settings
    |--------------------------------------------------------------------------
    */
    'delivery' => [
        'slot_duration_minutes' => 60,
        'express_delivery_minutes' => 10,
        'max_delivery_radius_km' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Inventory Settings
    |--------------------------------------------------------------------------
    */
    'inventory' => [
        'low_stock_threshold' => env('LOW_STOCK_THRESHOLD', 10),
        'track_inventory' => true,
        'allow_backorder' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Settings
    |--------------------------------------------------------------------------
    */
    'images' => [
        'max_size_kb' => 2048,
        'allowed_types' => ['jpeg', 'jpg', 'png', 'webp'],
        'product_sizes' => [
            'thumbnail' => [150, 150],
            'medium' => [400, 400],
            'large' => [800, 800],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'products_ttl' => 300, // 5 minutes
        'categories_ttl' => 600, // 10 minutes
        'home_ttl' => 180, // 3 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */
    'pagination' => [
        'admin_per_page' => 15,
        'api_per_page' => 20,
        'max_per_page' => 100,
    ],
];
