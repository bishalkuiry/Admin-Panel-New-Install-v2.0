<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MembershipPlan;
use App\Models\UserMembership;
use App\Models\UserMembershipHistory;
use App\Models\MembershipPage;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserMembershipApiController extends Controller
{
    public function __construct(
        private WalletService $walletService
    ) {}

    private function getActivePlanQuery()
    {
        $query = MembershipPlan::query();
        if (\Illuminate\Support\Facades\Schema::hasColumn('membership_plans', 'is_active')) {
            $query->where(function($q) {
                $q->where('is_active', 1)->orWhere('is_active', true);
            });
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('membership_plans', 'status')) {
            $query->where(function($q) {
                $q->where('status', 1)->orWhere('status', true);
            });
        }
        return $query;
    }

    /**
     * List active VIP Membership Plans
     */
    public function plans()
    {
        $plans = $this->getActivePlanQuery()
            ->orderBy('sort_order', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $plans,
        ]);
    }

    /**
     * Get Dynamic Membership Page Layout & Full HTML/CSS Code with Variable Substitution
     */
    public function pageLayout()
    {
        $page = MembershipPage::where('is_published', true)->first();
        $sections = $page ? $page->sections_data : $this->getDefaultBuilderSections();
        $activePlans = $this->getActivePlanQuery()->orderBy('sort_order', 'asc')->get();

        // Check if raw HTML/CSS code template exists
        $rawHtmlCss = $sections['full_html_css'] ?? null;
        if (!$rawHtmlCss && is_array($sections)) {
            // Extract from custom_html section if present
            foreach ($sections as $sec) {
                if (isset($sec['html_content'])) {
                    $rawHtmlCss .= "\n" . $sec['html_content'];
                }
            }
        }

        if (!$rawHtmlCss) {
            $rawHtmlCss = $this->getDefaultFullHtmlCss();
        }

        $parsedHtml = $this->parsePlaceholders($rawHtmlCss, $activePlans);

        $themeStyle = $sections['theme_style'] ?? 'zomato_gold';
        $badgeText = $sections['badge_text'] ?? 'VIP MEMBERSHIP';
        $subHeaderMotto = $sections['sub_header_motto'] ?? 'More Perks. More Moments.';
        $heroTitle = $sections['hero_title'] ?? 'Upgrade your everyday experience with a premium membership built for people who expect more. Save more, earn more and get treated like a VIP every time.';
        $primaryColor = $sections['primary_color'] ?? '#F97316';
        $secondaryColor = $sections['secondary_color'] ?? '#EA580C';
        $subHeading = $sections['sub_heading'] ?? 'Premium benefits. Effortless savings. One membership.';
        $vipAdvantageTitle = $sections['vip_advantage_title'] ?? 'Your VIP advantage';
        $vipAdvantageSubtitle = $sections['vip_advantage_subtitle'] ?? 'Everything you love, with more value. Four powerful benefits designed to make every order, purchase and support moment feel better.';
        $perks = $sections['perks'] ?? [
            ['icon' => '🚚', 'title' => 'Unlimited Free Delivery', 'desc' => 'Skip delivery fees and enjoy your favorites whenever you want, without counting every order.'],
            ['icon' => '🏷️', 'title' => 'Extra Member Discount', 'desc' => 'Unlock exclusive member-only pricing and stack more value into the purchases you already make.'],
            ['icon' => '💰', 'title' => 'Wallet Cashback', 'desc' => 'Get rewarded as you spend. Cashback goes straight to your wallet for your next experience.'],
            ['icon' => '⚡', 'title' => 'Priority Support', 'desc' => "Need help? VIP members move to the front of the line for faster, more attentive support."],
        ];
        $whyVipTitle = $sections['why_vip_title'] ?? 'Why Go VIP?';
        $whyVipSubtitle = $sections['why_vip_subtitle'] ?? "Because ordinary is overrated. VIP turns everyday spending into a smarter, more rewarding experience. Whether you're ordering in, shopping your favorites or looking for support, your membership keeps giving back.";
        $highlights = $sections['highlights'] ?? [
            'Designed for frequent users who want maximum value.',
            'Benefits work together to amplify your savings.',
            'One premium membership. A better everyday experience.',
            '✦ Member-only value',
            '✓ More savings',
        ];
        $upgradeTitle = $sections['upgrade_title'] ?? 'Your upgrade starts here';
        $upgradeSubtitle = $sections['upgrade_subtitle'] ?? 'Ready to live a little more VIP? Unlock premium benefits and make every experience count.';
        $footerTagline = $sections['footer_tagline'] ?? 'VIP Membership · Premium experiences, everyday value.';

        return response()->json([
            'success' => true,
            'data' => [
                'title' => $page->title ?? 'VIP User Membership',
                'theme_style' => $themeStyle,
                'badge_text' => $badgeText,
                'sub_header_motto' => $subHeaderMotto,
                'hero_title' => $heroTitle,
                'hero_subtitle' => $subHeaderMotto,
                'primary_color' => $primaryColor,
                'secondary_color' => $secondaryColor,
                'sub_heading' => $subHeading,
                'vip_advantage_title' => $vipAdvantageTitle,
                'vip_advantage_subtitle' => $vipAdvantageSubtitle,
                'perks' => $perks,
                'why_vip_title' => $whyVipTitle,
                'why_vip_subtitle' => $whyVipSubtitle,
                'highlights' => $highlights,
                'upgrade_title' => $upgradeTitle,
                'upgrade_subtitle' => $upgradeSubtitle,
                'footer_tagline' => $footerTagline,
                'html_code' => $rawHtmlCss,
                'parsed_html' => $parsedHtml,
                'plans' => $activePlans,
                'sections_data' => $sections,
            ]
        ]);
    }

    /**
     * Get Current Customer's VIP Membership Status
     */
    public function myStatus(Request $request)
    {
        $user = $request->user();
        $membership = UserMembership::with('plan')
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        $isActive = $membership && $membership->isActive();

        return response()->json([
            'success' => true,
            'is_vip' => $isActive,
            'membership' => $isActive ? [
                'id' => $membership->id,
                'plan_id' => $membership->membership_plan_id,
                'plan_name' => $membership->plan?->name ?? 'VIP Member',
                'badge_icon' => $membership->plan?->badge_icon ?? '👑',
                'starts_at' => $membership->starts_at?->toIso8601String(),
                'expires_at' => $membership->expires_at?->toIso8601String(),
                'remaining_days' => max(0, (int) now()->diffInDays($membership->expires_at, false)),
                'free_deliveries_used' => $membership->free_deliveries_used_this_month,
                'free_deliveries_max' => $membership->plan?->max_free_deliveries_per_month ?? 10,
                'free_deliveries_remaining' => max(0, ($membership->plan?->max_free_deliveries_per_month ?? 10) - $membership->free_deliveries_used_this_month),
                'cashback_earned' => (float) $membership->cashback_earned_this_month,
                'total_discount_saved' => (float) $membership->total_discount_saved,
                'auto_renew' => (bool) $membership->auto_renew,
                'perks' => [
                    'free_delivery' => (bool) $membership->plan?->free_delivery,
                    'min_order_free_delivery' => (float) ($membership->plan?->min_order_for_free_delivery ?? 0),
                    'extra_discount_percentage' => (float) ($membership->plan?->extra_discount_percentage ?? 0),
                    'cashback_percentage' => (float) ($membership->plan?->cashback_percentage ?? 0),
                    'priority_support' => (bool) $membership->plan?->priority_support,
                ]
            ] : null,
        ]);
    }

    /**
     * Subscribe / Activate VIP Membership Plan (Wallet Debit or Online Payment Gateway)
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'membership_plan_id' => 'required|exists:membership_plans,id',
            'payment_method' => 'required|string',
            'transaction_reference' => 'nullable|string',
        ]);

        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please log in to subscribe to VIP Membership.',
            ], 401);
        }
        $plan = $this->getActivePlanQuery()
            ->where('id', $request->membership_plan_id)
            ->firstOrFail();

        $amount = (float) $plan->price;

        return DB::transaction(function () use ($user, $plan, $amount, $request) {
            // Option 1: Payment via Customer Wallet Balance
            if ($request->payment_method === 'wallet') {
                $wallet = $this->walletService->getOrCreateWallet($user);
                if ($wallet->balance < $amount) {
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient wallet balance. Required ₹{$amount}, available balance is ₹{$wallet->balance}.",
                    ], 422);
                }

                $this->walletService->debitBalance(
                    $wallet,
                    $amount,
                    'membership_purchase',
                    "VIP Membership Plan: {$plan->name}",
                    ['plan_id' => $plan->id]
                );
            }

            // Expiry date calculation
            $durationDays = $plan->duration_days > 0 ? $plan->duration_days : 30;
            $startsAt = now();
            $expiresAt = now()->addDays($durationDays);

            // Create or update active UserMembership
            $membership = UserMembership::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'membership_plan_id' => $plan->id,
                    'starts_at' => $startsAt,
                    'expires_at' => $expiresAt,
                    'status' => 'active',
                    'payment_method' => $request->payment_method,
                    'payment_status' => 'paid',
                    'payment_id' => $request->transaction_reference ?? ('PAY-' . strtoupper(uniqid())),
                    'amount_paid' => $amount,
                    'free_deliveries_used_this_month' => 0,
                    'cashback_earned_this_month' => 0.00,
                    'total_discount_saved' => 0.00,
                    'auto_renew' => (bool) $plan->auto_renewal,
                    'cancelled_at' => null,
                ]
            );

            // Record Purchase History Audit Entry
            UserMembershipHistory::create([
                'user_id' => $user->id,
                'membership_plan_id' => $plan->id,
                'action' => 'PURCHASE',
                'amount' => $amount,
                'payment_method' => $request->payment_method,
                'metadata' => [
                    'starts_at' => $startsAt->toIso8601String(),
                    'expires_at' => $expiresAt->toIso8601String(),
                    'payment_id' => $membership->payment_id,
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => "Congratulations! Your VIP Membership under the '{$plan->name}' plan is now active.",
                'data' => $membership->load('plan'),
            ]);
        });
    }

    /**
     * Cancel Auto Renewal
     */
    public function cancelAutoRenew(Request $request)
    {
        $user = $request->user();
        $membership = UserMembership::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$membership) {
            return response()->json(['success' => false, 'message' => 'No active membership found'], 404);
        }

        $membership->update([
            'auto_renew' => !$membership->auto_renew,
            'cancelled_at' => !$membership->auto_renew ? null : now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => $membership->auto_renew ? 'Auto renewal enabled' : 'Auto renewal cancelled',
            'auto_renew' => $membership->auto_renew,
        ]);
    }

    /**
     * Parse HTML/CSS Placeholders (e.g. {{buy_now_button:1}}, {{plan_price:1}}, {{buy_now_button}})
     */
    public function parsePlaceholders(string $html, $plans): string
    {
        $firstPlan = $plans->first();

        // 1. Generic {{buy_now_button}} -> First Plan
        if ($firstPlan) {
            $defaultBtn = sprintf(
                '<button type="button" class="btn-vip-subscribe" data-plan-id="%d" onclick="subscribeVipPlan(%d)">Buy Now - Subscribe VIP (%s)</button>',
                $firstPlan->id,
                $firstPlan->id,
                \App\Helpers\CurrencyHelper::format($firstPlan->price)
            );
            $html = str_replace('{{buy_now_button}}', $defaultBtn, $html);
        }

        // 2. Specific {{buy_now_button:ID}}
        $html = preg_replace_callback('/\{\{buy_now_button:(\d+)\}\}/', function ($matches) use ($plans) {
            $planId = (int) $matches[1];
            $plan = $plans->firstWhere('id', $planId);
            $priceText = $plan ? \App\Helpers\CurrencyHelper::format($plan->price) : '';
            return sprintf(
                '<button type="button" class="btn-vip-subscribe" data-plan-id="%d" onclick="subscribeVipPlan(%d)">Buy Now - Subscribe VIP (%s)</button>',
                $planId,
                $planId,
                $priceText
            );
        }, $html);

        // 3. Plan Price {{plan_price:ID}}
        $html = preg_replace_callback('/\{\{plan_price:(\d+)\}\}/', function ($matches) use ($plans) {
            $planId = (int) $matches[1];
            $plan = $plans->firstWhere('id', $planId);
            return $plan ? \App\Helpers\CurrencyHelper::format($plan->price) : \App\Helpers\CurrencyHelper::format(0);
        }, $html);

        // 4. Plan Name {{plan_name:ID}}
        $html = preg_replace_callback('/\{\{plan_name:(\d+)\}\}/', function ($matches) use ($plans) {
            $planId = (int) $matches[1];
            $plan = $plans->firstWhere('id', $planId);
            return $plan ? e($plan->name) : 'VIP Plan';
        }, $html);

        // 5. Plan Cashback {{plan_cashback:ID}}
        $html = preg_replace_callback('/\{\{plan_cashback:(\d+)\}\}/', function ($matches) use ($plans) {
            $planId = (int) $matches[1];
            $plan = $plans->firstWhere('id', $planId);
            return $plan ? ($plan->cashback_percentage . '%') : '0%';
        }, $html);

        // 6. Plan Extra Discount {{plan_discount:ID}}
        $html = preg_replace_callback('/\{\{plan_discount:(\d+)\}\}/', function ($matches) use ($plans) {
            $planId = (int) $matches[1];
            $plan = $plans->firstWhere('id', $planId);
            return $plan ? ($plan->extra_discount_percentage . '%') : '0%';
        }, $html);

        // 7. Plan Duration {{plan_duration:ID}}
        $html = preg_replace_callback('/\{\{plan_duration:(\d+)\}\}/', function ($matches) use ($plans) {
            $planId = (int) $matches[1];
            $plan = $plans->firstWhere('id', $planId);
            return $plan ? ($plan->duration_days . ' Days') : '30 Days';
        }, $html);

        return $html;
    }

    private function getDefaultFullHtmlCss(): string
    {
        return '<style>
.vip-hero { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: #fff; padding: 40px 20px; text-align: center; border-radius: 24px; box-shadow: 0 12px 32px rgba(249, 115, 22, 0.25); }
.vip-badge { display: inline-block; padding: 4px 12px; background: rgba(255,255,255,0.2); border-radius: 99px; font-size: 11px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 12px; }
.vip-title { font-size: 28px; font-weight: 900; margin-bottom: 8px; }
.vip-subtitle { font-size: 14px; opacity: 0.9; max-width: 480px; margin: 0 auto 24px; line-height: 1.5; }
.btn-vip-subscribe { background: #ffffff; color: #111827; font-weight: 900; font-size: 14px; padding: 14px 28px; border: none; border-radius: 14px; cursor: pointer; box-shadow: 0 8px 20px rgba(0,0,0,0.15); transition: transform 0.2s ease; }
.btn-vip-subscribe:hover { transform: scale(1.04); }
.vip-perks-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-top: 24px; }
.vip-perk-card { background: #ffffff; padding: 20px; border-radius: 18px; border: 1px solid #f3f4f6; text-align: left; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.vip-perk-icon { font-size: 24px; margin-bottom: 8px; }
.vip-perk-title { font-weight: 800; font-size: 14px; color: #111827; margin-bottom: 4px; }
.vip-perk-desc { font-size: 12px; color: #6b7280; line-height: 1.4; }
</style>

<div class="vip-hero">
  <div class="vip-badge">👑 CROWN VIP MEMBERSHIP</div>
  <h1 class="vip-title">Unlock Unlimited Free Delivery & Perks</h1>
  <p class="vip-subtitle">Get Unlimited Free Delivery, Extra Member Discounts & 5% Wallet Cashback on Every Order!</p>
  {{buy_now_button}}
</div>

<div class="vip-perks-grid">
  <div class="vip-perk-card">
    <div class="vip-perk-icon">🚚</div>
    <div class="vip-perk-title">Unlimited Free Delivery</div>
    <div class="vip-perk-desc">On orders over ₹199 across all favorite stores.</div>
  </div>
  <div class="vip-perk-card">
    <div class="vip-perk-icon">🏷️</div>
    <div class="vip-perk-title">Extra Member Discount</div>
    <div class="vip-perk-desc">Applied automatically on checkout for member orders.</div>
  </div>
  <div class="vip-perk-card">
    <div class="vip-perk-icon">💰</div>
    <div class="vip-perk-title">5% Wallet Cashback</div>
    <div class="vip-perk-desc">Credited straight to your wallet upon order delivery.</div>
  </div>
</div>';
    }

    private function getDefaultBuilderSections(): array
    {
        return [
            'full_html_css' => $this->getDefaultFullHtmlCss(),
        ];
    }
}
