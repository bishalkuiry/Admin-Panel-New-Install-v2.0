<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    */
    'default_gateway' => env('DEFAULT_PAYMENT_GATEWAY', 'razorpay'),

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways Configuration
    |--------------------------------------------------------------------------
    */
    'gateways' => [
        'paypal' => [
            'enabled' => env('PAYPAL_ENABLED', false),
            'mode' => env('PAYPAL_MODE', 'sandbox'), // sandbox or live
            'client_id' => env('PAYPAL_CLIENT_ID'),
            'secret' => env('PAYPAL_SECRET'),
            'business_email' => env('PAYPAL_BUSINESS_EMAIL'),
            'currency' => env('PAYPAL_CURRENCY', 'USD'),
            'notification_url' => env('PAYPAL_NOTIFICATION_URL'),
        ],

        'razorpay' => [
            'enabled' => env('RAZORPAY_ENABLED', false),
            'key_id' => env('RAZORPAY_KEY_ID'),
            'key_secret' => env('RAZORPAY_KEY_SECRET'),
            'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
            'endpoint_url' => env('RAZORPAY_ENDPOINT_URL'),
        ],

        'paystack' => [
            'enabled' => env('PAYSTACK_ENABLED', false),
            'public_key' => env('PAYSTACK_PUBLIC_KEY'),
            'secret_key' => env('PAYSTACK_SECRET_KEY'),
        ],

        'stripe' => [
            'enabled' => env('STRIPE_ENABLED', false),
            'publishable_key' => env('STRIPE_PUBLISHABLE_KEY'),
            'secret_key' => env('STRIPE_SECRET_KEY'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        ],

        'flutterwave' => [
            'enabled' => env('FLUTTERWAVE_ENABLED', false),
            'public_key' => env('FLUTTERWAVE_PUBLIC_KEY'),
            'secret_key' => env('FLUTTERWAVE_SECRET_KEY'),
            'encryption_key' => env('FLUTTERWAVE_ENCRYPTION_KEY'),
        ],

        'paytm' => [
            'enabled' => env('PAYTM_ENABLED', false),
            'merchant_id' => env('PAYTM_MERCHANT_ID'),
            'merchant_key' => env('PAYTM_MERCHANT_KEY'),
            'website' => env('PAYTM_WEBSITE', 'WEBSTAGING'),
            'industry_type' => env('PAYTM_INDUSTRY_TYPE', 'Retail'),
            'channel_id' => env('PAYTM_CHANNEL_ID', 'WEB'),
            'environment' => env('PAYTM_ENVIRONMENT', 'staging'), // staging or production
        ],

        'midtrans' => [
            'enabled' => env('MIDTRANS_ENABLED', false),
            'server_key' => env('MIDTRANS_SERVER_KEY'),
            'client_key' => env('MIDTRANS_CLIENT_KEY'),
            'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
            'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),
            'is_3ds' => env('MIDTRANS_IS_3DS', true),
        ],

        'myfatoorah' => [
            'enabled' => env('MYFATOORAH_ENABLED', false),
            'api_key' => env('MYFATOORAH_API_KEY'),
            'country_code' => env('MYFATOORAH_COUNTRY_CODE', 'KWT'), // KWT, SAU, ARE, QAT, BHR, OMN, JOR, EGY
            'test_mode' => env('MYFATOORAH_TEST_MODE', true),
        ],

        'instamojo' => [
            'enabled' => env('INSTAMOJO_ENABLED', false),
            'api_key' => env('INSTAMOJO_API_KEY'),
            'auth_token' => env('INSTAMOJO_AUTH_TOKEN'),
            'salt' => env('INSTAMOJO_SALT'),
            'test_mode' => env('INSTAMOJO_TEST_MODE', true),
        ],

        'phonepe' => [
            'enabled' => env('PHONEPE_ENABLED', false),
            'merchant_id' => env('PHONEPE_MERCHANT_ID'),
            'salt_key' => env('PHONEPE_SALT_KEY'),
            'salt_index' => env('PHONEPE_SALT_INDEX', 1),
            'environment' => env('PHONEPE_ENVIRONMENT', 'sandbox'), // sandbox or production
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cash on Delivery
    |--------------------------------------------------------------------------
    */
    'cod' => [
        'enabled' => env('COD_ENABLED', true),
        'min_amount' => env('COD_MIN_AMOUNT', 100),
        'max_amount' => env('COD_MAX_AMOUNT', 5000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Direct Bank Transfer
    |--------------------------------------------------------------------------
    */
    'bank_transfer' => [
        'enabled'        => env('BANK_TRANSFER_ENABLED', false),
        'bank_name'      => env('BANK_TRANSFER_BANK_NAME'),
        'account_name'   => env('BANK_TRANSFER_ACCOUNT_NAME'),
        'account_number' => env('BANK_TRANSFER_ACCOUNT_NUMBER'),
        'ifsc_code'      => env('BANK_TRANSFER_IFSC_CODE'),
        'swift_code'     => env('BANK_TRANSFER_SWIFT_CODE'),
        'bank_code'      => env('BANK_TRANSFER_BANK_CODE'),
        'notes'          => env('BANK_TRANSFER_NOTES'),
    ],
];
