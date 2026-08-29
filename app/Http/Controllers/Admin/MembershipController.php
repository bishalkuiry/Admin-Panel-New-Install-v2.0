<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MembershipPlan;
use App\Models\UserMembership;
use App\Models\MembershipPage;
use App\Models\UserMembershipHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MembershipController extends Controller
{
    public function index()
    {
        $plans = MembershipPlan::orderBy('sort_order', 'asc')->get();
        $memberships = UserMembership::with(['user', 'plan'])->latest()->paginate(20);

        $stats = [
            'total_members' => UserMembership::where('status', 'active')->count(),
            'total_revenue' => (float) UserMembershipHistory::where('action', 'PURCHASE')->sum('amount'),
            'total_free_deliveries' => (int) UserMembership::sum('free_deliveries_used_this_month'),
            'total_savings_provided' => (float) UserMembership::sum('total_discount_saved'),
        ];

        return view('admin.subscriptions.user', compact('plans', 'memberships', 'stats'));
    }

    public function storePlan(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'cashback_percentage' => 'required|numeric|min:0|max:100',
            'max_cashback_per_month' => 'nullable|numeric|min:0',
            'free_delivery' => 'boolean',
            'max_free_deliveries_per_month' => 'nullable|integer|min:0',
            'min_order_for_free_delivery' => 'nullable|numeric|min:0',
            'extra_discount_percentage' => 'nullable|numeric|min:0|max:100',
            'max_discount_per_order' => 'nullable|numeric|min:0',
            'priority_support' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']) . '-' . time();
        $validated['free_delivery'] = $request->has('free_delivery');
        $validated['priority_support'] = $request->has('priority_support');
        $validated['is_active'] = $request->has('is_active');
        $validated['badge_icon'] = '👑';

        MembershipPlan::create($validated);

        return redirect()->back()->with('success', 'VIP Membership Plan created successfully!');
    }

    public function destroyPlan(MembershipPlan $plan)
    {
        // Safety: Do not hard delete if active subscribers exist
        if ($plan->userMemberships()->where('status', 'active')->exists()) {
            $plan->update(['is_active' => false]);
            return redirect()->back()->with('success', 'Plan has active members so it was deactivated instead of deleted.');
        }

        $plan->delete();
        return redirect()->back()->with('success', 'Membership Plan deleted!');
    }

    /**
     * Render Visual No-Code Page Builder
     */
    public function builder()
    {
        $page = MembershipPage::where('is_published', true)->first();
        $sections = $page ? $page->sections_data : $this->getDefaultSections();

        return view('admin.subscriptions.builder', compact('page', 'sections'));
    }

    /**
     * Save Page Builder Layout
     */
    public function saveBuilder(Request $request)
    {
        $request->validate([
            'sections_data' => 'required|array',
        ]);

        MembershipPage::updateOrCreate(
            ['slug' => 'vip-user-membership-page'],
            [
                'title' => 'VIP User Membership Landing Page',
                'sections_data' => $request->sections_data,
                'is_published' => true,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'VIP Membership Page Layout published successfully!',
        ]);
    }

    private function getDefaultSections(): array
    {
        return [
            'theme_style' => 'zomato_gold',
            'badge_text' => 'VIP MEMBERSHIP',
            'sub_header_motto' => 'More Perks. More Moments.',
            'hero_title' => 'Upgrade your everyday experience with a premium membership built for people who expect more. Save more, earn more and get treated like a VIP every time.',
            'primary_color' => '#F97316',
            'secondary_color' => '#EA580C',
            'sub_heading' => 'Premium benefits. Effortless savings. One membership.',
            'vip_advantage_title' => 'Your VIP advantage',
            'vip_advantage_subtitle' => 'Everything you love, with more value. Four powerful benefits designed to make every order, purchase and support moment feel better.',
            'perks' => [
                ['icon' => '🚚', 'title' => 'Unlimited Free Delivery', 'desc' => 'Skip delivery fees and enjoy your favorites whenever you want, without counting every order.'],
                ['icon' => '🏷️', 'title' => 'Extra Member Discount', 'desc' => 'Unlock exclusive member-only pricing and stack more value into the purchases you already make.'],
                ['icon' => '💰', 'title' => 'Wallet Cashback', 'desc' => 'Get rewarded as you spend. Cashback goes straight to your wallet for your next experience.'],
                ['icon' => '⚡', 'title' => 'Priority Support', 'desc' => "Need help? VIP members move to the front of the line for faster, more attentive support."],
            ],
            'why_vip_title' => 'Why Go VIP?',
            'why_vip_subtitle' => "Because ordinary is overrated. VIP turns everyday spending into a smarter, more rewarding experience. Whether you're ordering in, shopping your favorites or looking for support, your membership keeps giving back.",
            'highlights' => [
                'Designed for frequent users who want maximum value.',
                'Benefits work together to amplify your savings.',
                'One premium membership. A better everyday experience.',
                '✦ Member-only value',
                '✓ More savings',
            ],
            'upgrade_title' => 'Your upgrade starts here',
            'upgrade_subtitle' => 'Ready to live a little more VIP? Unlock premium benefits and make every experience count.',
            'footer_tagline' => 'VIP Membership · Premium experiences, everyday value.',
            'full_html_css' => '',
        ];
    }
}
