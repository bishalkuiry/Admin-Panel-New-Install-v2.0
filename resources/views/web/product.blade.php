<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $product->name }} | {{ config('app.name') }}</title>

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="product">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $product->name }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($product->description), 160) }}">
    <meta property="og:image" content="{{ storage_url($product->primaryImage?->image) ?? asset('assets/img/placeholder.png') }}">
    
    <!-- App Links Configuration (Optional but good) -->
    <meta property="al:android:url" content="{{ config('app.scheme', 'inallcart') }}://product/{{ $product->id }}">
    <meta property="al:android:package" content="{{ config('app.package', 'com.your.package.name') }}">
    <meta property="al:android:app_name" content="{{ config('app.name') }}">
    <meta property="al:ios:url" content="{{ config('app.scheme', 'inallcart') }}://product/{{ $product->id }}">
    <meta property="al:ios:app_store_id" content="{{ config('app.app_store_id', 'YOUR_APP_STORE_ID') }}">

    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #FFC107;
            --secondary: #0C831F;
            --dark: #1a1a1a;
            --white: #ffffff;
        }
        body {
            font-family: 'Manrope', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
        }
        .container {
            padding: 24px;
            max-width: 400px;
        }
        .logo {
            font-size: 28px;
            font-weight: 800;
            color: var(--secondary);
            margin-bottom: 40px;
        }
        .logo span { color: var(--primary); }
        .product-preview {
            margin-bottom: 30px;
        }
        .product-image {
            width: 120px;
            height: 120px;
            border-radius: 20px;
            object-fit: cover;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h1 { font-size: 20px; margin: 0 0 10px 0; }
        p { color: #666; font-size: 14px; margin-bottom: 30px; }
        
        .btn-open {
            display: block;
            background: var(--secondary);
            color: var(--white);
            padding: 16px;
            border-radius: 12px;
            font-weight: 700;
            text-decoration: none;
            margin-bottom: 12px;
        }
        .btn-download {
            display: block;
            background: #f0f0f0;
            color: var(--dark);
            padding: 16px;
            border-radius: 12px;
            font-weight: 700;
            text-decoration: none;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="logo">{{ config('app.name') }}</div>

        <div class="product-preview">
            <img src="{{ storage_url($product->primaryImage?->image) ?? asset('assets/img/placeholder.png') }}" class="product-image">
            <h1>Opening in App...</h1>
            <p>We're whisking you away to the {{ config('app.name') }} app to view <strong>{{ $product->name }}</strong>.</p>
        </div>

        <a href="{{ config('app.scheme', 'inallcart') }}://product/{{ $product->id }}" class="btn-open" id="openLink">Open in App</a>
        <a href="#" class="btn-download">Download {{ config('app.name') }} App</a>

        <div style="margin-top: 40px; font-size: 12px; color: #999;">
            Directly open the app for the best experience.
        </div>
    </div>

    <script>
        window.onload = function() {
            // Attempt to open the app automatically
            window.location = "{{ config('app.scheme', 'inallcart') }}://product/{{ $product->id }}";
            
            // Redirect to App Store/Play Store if nothing happens after 2.5 seconds
            // setTimeout(function() {
            //     window.location = "YOUR_STORE_URL";
            // }, 2500);
        };
    </script>
</body>
</html>
