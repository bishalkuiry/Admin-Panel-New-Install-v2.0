<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join {{ config('app.name') }} | Referral Invitation</title>

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="Join {{ config('app.name') }} and get rewarded!">
    <meta property="og:description" content="{{ $user->name }} invited you to join {{ config('app.name') }}. Use referral code {{ $code }} to get started with exclusive benefits!">
    <meta property="og:image" content="{{ asset('assets/img/referral_preview.png') }}">

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
        
        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #eee;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: 800;
            color: var(--secondary);
        }

        h1 { font-size: 22px; margin: 0 0 10px 0; font-weight: 800; }
        p { color: #666; font-size: 15px; margin-bottom: 30px; line-height: 1.5; }
        
        .code-pill {
            display: inline-block;
            background: #f8f9fa;
            border: 2px dashed var(--secondary);
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 18px;
            letter-spacing: 2px;
            color: var(--secondary);
            margin-bottom: 30px;
        }

        .btn-open {
            display: block;
            background: var(--secondary);
            color: var(--white);
            padding: 16px;
            border-radius: 12px;
            font-weight: 700;
            text-decoration: none;
            margin-bottom: 12px;
            box-shadow: 0 4px 15px rgba(12, 131, 31, 0.2);
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

        <div class="referral-content">
            <div class="user-avatar">{{ substr($user->name, 0, 1) }}</div>
            <h1>{{ $user->name }} invited you!</h1>
            <p>Join {{ config('app.name') }} today and start shopping smart with exclusive rewards and fastest deliveries.</p>
            
            <div class="code-pill">{{ $code }}</div>
        </div>

        <a href="quixko://invite/{{ $code }}" class="btn-open">Open in {{ config('app.name') }} App</a>
        <a href="#" class="btn-download">Download {{ config('app.name') }} App</a>

        <div style="margin-top: 40px; font-size: 12px; color: #999;">
            Opening the app will automatically apply the referral code.
        </div>
    </div>

    <script>
        window.onload = function() {
            // Attempt to open the app automatically
            window.location = "quixko://invite/{{ $code }}";
        };
    </script>
</body>
</html>
