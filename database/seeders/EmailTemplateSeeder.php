<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $commonLayout = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333333; margin: 0; padding: 0; background-color: #f4f4f7; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { max-height: 50px; }
        .content { margin-bottom: 30px; }
        .button { display: inline-block; background-color: #4f46e5; color: #ffffff; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 600; margin-top: 20px; }
        .footer { text-align: center; font-size: 12px; color: #999999; margin-top: 40px; border-top: 1px solid #eeeeee; padding-top: 20px; }
        .order-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .order-table th { text-align: left; padding: 10px; background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; }
        .order-table td { padding: 10px; border-bottom: 1px solid #e2e8f0; }
        .total-row { font-weight: bold; }
        .otp-box { background: #f0fdf4; border: 2px dashed #16a34a; color: #16a34a; font-size: 24px; font-weight: bold; padding: 15px; text-align: center; letter-spacing: 5px; border-radius: 8px; margin: 20px 0; }
    </style>
</head>
<body>
    <div style="padding: 40px 0;">
        <div class="container">
            <div class="header">
                <h2>{{app_name}}</h2>
            </div>
            <div class="content">
                {{content}}
            </div>
            <div class="footer">
                <p>&copy; {{year}} {{app_name}}. All rights reserved.</p>
                <p>{{address}}</p>
            </div>
        </div>
    </div>
</body>
</html>';

        $templates = [
            [
                'type' => 'otp_verification',
                'name' => 'OTP Verification',
                'subject' => 'Your Verification Code - {{otp}}',
                'body' => str_replace('{{content}}', '
                    <h1 style="color: #1f2937; margin-bottom: 15px;">Verify Your Account</h1>
                    <p>Hello,</p>
                    <p>Use the code below to verify your account or complete your action. This code will expire in 10 minutes.</p>
                    <div class="otp-box">{{otp}}</div>
                    <p>If you did not request this code, please ignore this email.</p>
                ', $commonLayout),
                'placeholders' => ['{{otp}}', '{{app_name}}', '{{year}}']
            ],
            [
                'type' => 'order_placed',
                'name' => 'Order Confirmation',
                'subject' => 'Order Confirmed! #{{order_number}}',
                'body' => str_replace('{{content}}', '
                    <h1 style="color: #4f46e5; margin-bottom: 10px;">Thanks for your order!</h1>
                    <p>Hi {{customer_name}},</p>
                    <p>We\'ve received your order and are getting it ready. We\'ll notify you when it\'s on the way!</p>
                    
                    <div style="background: #f9fafb; padding: 20px; border-radius: 8px; margin: 20px 0;">
                        <h3 style="margin-top: 0;">Order Summary</h3>
                        <p><strong>Order ID:</strong> #{{order_number}}</p>
                        <p><strong>Order Date:</strong> {{order_date}}</p>
                        <p><strong>Payment Method:</strong> {{payment_method}}</p>
                        <p><strong>Shipping Address:</strong> {{shipping_address}}</p>
                    </div>

                    <table class="order-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{items_list_html}}
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td colspan="2" style="text-align: right;">Total:</td>
                                <td>{{order_total}}</td>
                            </tr>
                        </tfoot>
                    </table>

                    <div style="text-align: center;">
                        <a href="{{order_tracking_url}}" class="button">Track Your Order</a>
                    </div>
                ', $commonLayout),
                'placeholders' => ['{{customer_name}}', '{{order_number}}', '{{order_date}}', '{{order_total}}', '{{items_list_html}}', '{{shipping_address}}', '{{payment_method}}', '{{order_tracking_url}}', '{{app_name}}']
            ],
            [
                'type' => 'welcome',
                'name' => 'Welcome Email',
                'subject' => 'Welcome to {{app_name}}!',
                'body' => str_replace('{{content}}', '
                    <h1 style="color: #1f2937;">Welcome, {{customer_name}}! 🎉</h1>
                    <p>We\'re thrilled to have you on board. At {{app_name}}, we strive to provide the best shopping experience possible.</p>
                    <p>Here you can discover amazing products, track your orders easily, and enjoy exclusive deals.</p>
                    <div style="text-align: center;">
                        <a href="{{app_url}}" class="button">Start Shopping</a>
                    </div>
                ', $commonLayout),
                'placeholders' => ['{{customer_name}}', '{{app_name}}', '{{app_url}}']
            ],
            [
                'type' => 'promotion',
                'name' => 'Promotional Email',
                'subject' => 'Exclusive Offer Just for You!',
                'body' => str_replace('{{content}}', '
                    <h1 style="color: #db2777;">Special Deal inside! 🎁</h1>
                    <p>Hi {{customer_name}},</p>
                    <p>We wouldn\'t want you to miss out on this exclusive offer.</p>
                    <div style="background: #fdf2f8; padding: 20px; text-align: center; border-radius: 8px; border: 1px solid #fbcfe8; margin: 20px 0;">
                        <h2 style="color: #be185d; margin: 0;">{{promo_title}}</h2>
                        <p style="margin: 10px 0;">{{promo_description}}</p>
                        <p style="font-size: 18px; font-weight: bold;">Use Code: <span style="background: #be185d; color: white; padding: 5px 10px; border-radius: 4px;">{{promo_code}}</span></p>
                    </div>
                    <div style="text-align: center;">
                        <a href="{{promo_url}}" class="button" style="background-color: #be185d;">Shop Now</a>
                    </div>
                ', $commonLayout),
                'placeholders' => ['{{customer_name}}', '{{promo_title}}', '{{promo_description}}', '{{promo_code}}', '{{promo_url}}', '{{app_url}}']
            ],
            [
                'type' => 'order_shipped',
                'name' => 'Order Shipped',
                'subject' => 'Your Order #{{order_number}} is on the way! 🚚',
                'body' => str_replace('{{content}}', '
                    <h1 style="color: #0ea5e9;">Your Order is Shipped!</h1>
                    <p>Good news, {{customer_name}}!</p>
                    <p>Your order #{{order_number}} has been dispatched and is making its way to you.</p>
                    <p>Delivery Partner: <strong>{{delivery_partner}}</strong></p>
                    <div style="text-align: center;">
                        <a href="{{order_tracking_url}}" class="button">Track Delivery</a>
                    </div>
                ', $commonLayout),
                'placeholders' => ['{{customer_name}}', '{{order_number}}', '{{delivery_partner}}', '{{order_tracking_url}}']
            ],
            [
                'type' => 'order_delivered',
                'name' => 'Order Delivered',
                'subject' => 'Your Order #{{order_number}} has been delivered',
                'body' => str_replace('{{content}}', '
                    <h1 style="color: #16a34a;">Delivered! ✔️</h1>
                    <p>Hi {{customer_name}},</p>
                    <p>Your order #{{order_number}} has been successfully delivered. We hope you enjoy your purchase!</p>
                    <div style="text-align: center;">
                        <a href="{{order_tracking_url}}" class="button">View Order</a>
                    </div>
                ', $commonLayout),
                'placeholders' => ['{{customer_name}}', '{{order_number}}', '{{order_tracking_url}}']
            ]
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                ['type' => $template['type']],
                $template
            );
        }
    }
}
