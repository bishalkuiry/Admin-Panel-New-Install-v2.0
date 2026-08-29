<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Store;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Address;
use App\Enums\UserRole;
use App\Enums\StoreStatus;
use App\Enums\KycStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Home Content & Catalog Seeder
 * 
 * Seeds demo data for categories, brands, products, home header, and app contents.
 * 
 * IMPORTANT: This seeder is designed for FRESH installations only.
 * - All IDs are auto-incremented starting from 1
 * - Foreign key references (category_id, brand_id, etc.) assume sequential insertion order
 * - Do NOT run this seeder on existing data as it will cause ID mismatches
 * - Run this seeder BEFORE any other data seeders
 */
class HomeContentCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $this->createUsers();
        $this->createStore();
        $this->seedCategories();
        $this->seedBrands();
        $this->seedProducts();
        $this->seedProductImages();
        $this->seedHomeHeader();
        $this->seedAppContents();

        $this->command->info('✅ Home content & catalog data seeded successfully!');
    }

    // ─── Users & Store ────────────────────────────────────────────────────────

    private function createUsers(): void
    {
        $this->command->info('Creating demo users...');

        User::firstOrCreate(['email' => 'admin@inallcart.com'], [
            'name'      => 'Admin User',
            'password'  => Hash::make('password'),
            'phone'     => '+919876543210',
            'role'      => UserRole::SUPER_ADMIN,
            'is_active' => true,
        ]);

        $customer = User::firstOrCreate(['email' => 'customer@example.com'], [
            'name'      => 'John Doe',
            'password'  => Hash::make('password'),
            'phone'     => '+919876543211',
            'role'      => UserRole::CUSTOMER,
            'is_active' => true,
        ]);

        if (!$customer->wallet) {
            $wallet = Wallet::create([
                'user_id'  => $customer->id,
                'balance'  => 500.00,
                'currency' => 'INR',
            ]);
            WalletTransaction::create([
                'wallet_id'      => $wallet->id,
                'type'           => 'signup_bonus',
                'amount'         => 500.00,
                'balance_before' => 0.00,
                'balance_after'  => 500.00,
                'description'    => 'Welcome bonus',
                'metadata'       => json_encode(['reason' => 'Demo account setup']),
            ]);
            Address::create([
                'user_id'        => $customer->id,
                'type'           => 'Home',
                'name'           => 'John Doe',
                'phone'          => '+919876543211',
                'address_line_1' => '123 Main Street',
                'address_line_2' => 'Apartment 4B',
                'city'           => 'Mumbai',
                'state'          => 'Maharashtra',
                'postal_code'    => '400001',
                'country'        => 'India',
                'latitude'       => 19.0760,
                'longitude'      => 72.8777,
                'is_default'     => true,
            ]);
        }

        User::firstOrCreate(['email' => 'seller@example.com'], [
            'name'      => 'Rajesh Kumar',
            'password'  => Hash::make('password'),
            'phone'     => '+919876543212',
            'role'      => UserRole::STORE_OWNER,
            'is_active' => true,
        ]);

        User::firstOrCreate(['email' => 'delivery@example.com'], [
            'name'      => 'Amit Singh',
            'password'  => Hash::make('password'),
            'phone'     => '+919876543213',
            'role'      => UserRole::DELIVERY_PARTNER,
            'is_active' => true,
        ]);
    }

    private function createStore(): void
    {
        $this->command->info('Creating demo store...');

        $seller = User::where('email', 'seller@example.com')->first();
        if (!$seller || Store::where('slug', 'fresh-mart')->exists()) {
            return;
        }

        Store::create([
            'owner_id'          => $seller->id,
            'name'              => 'Fresh Mart',
            'slug'              => 'fresh-mart',
            'description'       => 'Your one-stop shop for fresh groceries and daily essentials',
            'email'             => 'store@freshmart.com',
            'phone'             => '+919876543214',
            'address_line_1'    => '456 Market Road',
            'city'              => 'Mumbai',
            'state'             => 'Maharashtra',
            'postal_code'       => '400002',
            'country'           => 'India',
            'latitude'          => 19.0760,
            'longitude'         => 72.8777,
            'status'            => StoreStatus::ACTIVE,
            'kyc_status'        => KycStatus::APPROVED,
            'is_online'         => true,
            'is_featured'       => true,
            'delivery_type'     => 'custom',
            'delivery_method'   => 'flat',
            'delivery_flat_rate' => 5.00,
            'store_free_delivery' => false,
            'min_order_amount'  => 0.00,
            'packing_charge'    => 0.00,
            'commission_percent' => 10.00,
            'commission_type'   => 'percent',
            'rating'            => 4.5,
            'rating_count'      => 150,
        ]);
    }

    // ─── Categories ──────────────────────────────────────────────────────────

    private function seedCategories(): void
    {
        $this->command->info('Seeding categories...');

        $now = now()->toDateTimeString();

        // Real category images already in storage/app/public/categories/
        // Using simple descriptive name that matches SQL database
        $catImages = [
            'categories/category-image.png',
        ];

        // Format: [name, slug, parent_id, sort_order, is_featured]
        // Note: parent_id set to null for fresh installation simplicity
        $categories = [
            // ── Fruits & Vegetables ──
            ['Fruits & Vegetables',     'fruits-vegetables',      null, 0, 1],
            ['Fresh Vegetables',         'fresh-vegetables',       null, 1, 0],
            ['Exotic Fruits',            'exotic-fruits',          null, 2, 0],
            ['Leafy Greens',             'leafy-greens',           null, 3, 0],
            ['Herbs & Seasonings',       'herbs-seasonings',       null, 4, 0],
            // ── Dairy & Breakfast ──
            ['Milk & Dairy',             'milk-dairy',             null, 0, 1],
            ['Milk & Curd',              'milk-curd',              null,   1, 0],
            ['Bread & Bakery',           'bread-bakery',           null,   1, 0],
            ['Butter & Cheese',          'butter-cheese',          null,   2, 0],
            ['Eggs',                     'eggs',                   null,   3, 0],
            ['Breakfast Cereals',        'breakfast-cereals',      null, 4, 0],
            // ── Snacks & Drinks ──
            ['Chips & Namkeen',          'chips-namkeen',          null, 0, 1],
            ['Chips',                    'chips',                  null,  0, 0],
            ['Biscuits & Cookies',       'biscuits-cookies',       null, 0, 0],
            ['Cold Drinks',              'cold-drinks',            null,  2, 0],
            ['Tea & Coffee',             'tea-coffee',             null, 0, 0],
            ['Health Drinks',            'health-drinks',          null,  4, 0],
            // ── Staples ──
            ['Rice & Rice Products',     'rice-rice-products',     null, 0, 0],
            ['Dals & Pulses',            'dals-pulses',            null, 1, 0],
            ['Atta & Flours',            'atta-flours',            null, 2, 0],
            ['Edible Oils',              'edible-oils',            null, 3, 0],
            ['Masalas & Spices',         'masalas-spices',         null, 4, 0],
            // ── Personal Care ──
            ['Personal Care',            'personal-care',          null, 0, 1],
            ['Hair Care',                'hair-care',              null,  1, 0],
            ['Skin Care',                'skin-care',              null,  3, 0],
            // ── Others – Grocery ──
            ['Ice Creams',               'ice-creams',             null, 0, 0],
            ['Stationery',               'stationery',             null, 0, 0],
            ['Toys & Sports',            'toys-sports',            null, 0, 0],
            ['Sauces & Spreads',         'sauces-spreads',         null, 0, 0],
            ['Pickles & Chutney',        'pickles-chutney',        null, 0, 0],
            ['Lassi, Shakes and More',   'lassi-shakes-and-more',  null, 0, 0],
            ['Ready to Cook & Eat',      'ready-to-cook-eat',      null, 0, 0],
            ['Pan Corner',               'pan-corner',             null, 0, 0],
            ['Oil & Ghee',               'oil-ghee',               null, 0, 0],
            ['Cold Drinks & Juices',     'cold-drinks-juices',     null, 0, 0],
            ['Honey',                    'honey',                  null, 0, 0],
            ['Pasta, Noodles & Soup',    'pasta-noodles-soup',     null, 0, 0],
            ['Vegetables',               'vegetables',             null, 0, 0],
            ['Health & Wellness',        'health-wellness',        null, 0, 0],
            ['Makeup Items',             'mackup-items',           null, 0, 0],
            ['Household Essentials',     'household-essentials',   null, 0, 0],
            ['Grocery & Kitchen',        'grocery-kitchen',        null, 0, 0],
            ['Meat, Fish & Egg',         'meat-fish-egg',          null, 0, 0],
            // ── Beauty / Makeup ──
            ['Epilators',               'epilators',               null, 0, 0],
            ['Acne Removal Tools',      'acne-removal-tools',      null, 0, 0],
            ['Cleansing Brushes',       'cleansing-brushes',       null, 0, 0],
            ['Styling Appliances',      'styling-appliances',      null, 0, 0],
            ['Combos',                  'combos',                  null, 0, 0],
            ['Shampoos',                'shampoos',                null, 0, 0],
            ['Color',                   'color',                   null, 0, 0],
            ['Tools & Brushes',         'tools-brushes',           null, 0, 0],
            ['Nail Polish',             'nail-polish',             null, 0, 0],
            ['Mascara',                 'mascara',                 null, 0, 0],
            ['Blush',                   'blush',                   null, 0, 0],
            ['Face Primer',             'face-primer',             null, 0, 0],
            ['Compact',                 'compact',                 null, 0, 0],
            ['Eyeliner & Kajals',       'eyeliner-kajals',         null, 0, 0],
            ['Concealer',               'concealer',               null, 0, 0],
            ['Base Makeup',             'base-makeup',             null, 0, 0],
            ['Eye Shadow',              'eye-shadow',              null, 0, 0],
            ['Foundation',              'foundation',              null, 0, 0],
            ['Lipstick',                'lipstick',                null, 0, 0],
            // ── Gift & Decor ──
            ['Decor Enthusiast',        'decor-enthusiast',        null, 0, 0],
            ['Gift',                    'gift',                    null, 0, 0],
            ['Rose',                    'rose',                    null, 0, 0],
            ['Occasions Flowers',       'occasions-flowers',       null, 0, 0],
            ['Festive Blooms',          'festive-blooms',          null, 0, 0],
            ['Colour Pop',              'colour-pop',              null, 0, 0],
            ['Bamboo',                  'bamboo',                  null, 0, 0],
            ['Dried Flowers',           'dried-flowers',           null, 0, 0],
            ['Flower Bouquet Book',     'flower-bookey',           null, 0, 0],
            ['Pooja Leaf',              'pooja-lef',               null, 0, 0],
            ['Flower Bouquet',          'flower-bouquet',          null, 0, 0],
            ['Flower Plants',           'flower-plants',           null, 0, 0],
            ['Flowers',                 'flowers',                 null, 0, 0],
            ['Show Plants',             'show-plants',             null, 0, 0],
            // ── Tab Root Categories ──
            ['All',                     'all',                     null, 0, 0],
            ["Valentine's",             'valentines',              null, 0, 0],
            ['Food',                    'food',                    null, 0, 0],
            ['Fresh',                   'fresh',                   null, 0, 0],
            ['Fashion',                 'fashion',                 null, 0, 0],
            ['Electronic',              'electronic',              null, 0, 0],
            ['Beauty',                  'beauty',                  null, 0, 0],
            ['Decor',                   'decor',                   null, 0, 0],
            ['Kids',                    'kids',                    null, 0, 0],
            ['Pharmacy',                'pharmacy',                null, 0, 0],
            ['Jewellery',               'jewellery',               null, 0, 0],
            ['Imported',                'imported',                null, 0, 0],
            // ── Electronics ──
            ['Airdopes',                'airdopes',                null, 0, 0],
            ['Smart Phones',            'smart-phones',            null, 0, 0],
            ['Televisions',             'televisions',             null, 0, 0],
            ['Laptops',                 'laptops',                 null, 0, 0],
            ['Headphones',              'headphones',              null, 0, 0],
            ['Smartwatches',            'smartwatches',            null, 0, 0],
            ['Mixer Grinders',          'mixer-grinders',          null, 0, 0],
            ['Soundbars',               'soundbars',               null, 0, 0],
            ['Microwave Ovens',         'microwave-ovens',         null, 0, 0],
            ['Washing Machines',        'washing-machines',        null, 0, 0],
            ['Cameras',                 'cameras',                 null, 0, 0],
            ['Air Conditioners',        'air-conditioners',        null, 0, 0],
            ['Refrigerators',           'refrigerators',           null, 0, 0],
            ['Smartphones',             'smartphones',             null, 0, 0],
            // ── Food / Restaurant ──
            ['Biriyani',                'biriyani',                null, 0, 0],
            ['Chowmin',                 'chowmin',                 null, 0, 0],
            ['Burger',                  'burger',                  null, 0, 0],
            ['Chaat & Street Food',     'chaat-street-food',       null, 0, 0],
            ['Samosa',                  'samosa',                  null, 0, 0],
            ['Pizza',                   'pizza',                   null, 0, 0],
            ['Mithai',                  'mithai',                  null, 0, 0],
            ['Thali',                   'thali',                   null, 0, 0],
            ['Fish Kabab',              'fish-kabab',              null, 0, 0],
            ['Ice Cream',               'ice-cream',               null, 0, 0],
            ['Rice',                    'rice',                    null, 0, 0],
            ['Salad',                   'salad',                   null, 0, 0],
            ['Cake',                    'cake',                    null, 0, 0],
            ['Roll',                    'roll',                    null, 0, 0],
            ['Momos',                   'momos',                   null, 0, 0],
            ['Dosa',                    'dosa',                    null, 0, 0],
            ['Pasta',                   'pasta',                   null, 0, 0],
            ['Chole Bhature',           'chole-bhature',           null, 0, 0],
            ['Pav Bhaji',               'pav-bhaji',               null, 0, 0],
            ['Coffee',                  'coffee',                  null, 0, 0],
            ['Veg Meal',                'veg-meal',                null, 0, 0],
            // ── Fashion ──
            ['Men Clothing',            'men-clothing',            null, 0, 0],
            ["Women's Clothing",        'womens-clothing',         null, 0, 0],
            ['Footwear',                'footwear',                null, 0, 0],
            ['Luggage',                 'luggage',                 null, 0, 0],
            ['Jewellery',               'jewllery',                null, 0, 0],
            ['Watch',                   'watch',                   null, 0, 0],
            ['Handbags',                'handbags',                null, 0, 0],
            ['Eyewear',                 'eyewear',                 null, 0, 0],
            // ── Home Decor ──
            ['Wall Shelves',            'wall-shelves',            null, 0, 0],
            ['Wall Decor',              'wall-decor',              null, 0, 0],
            ['Mirrors',                 'mirrors',                 null, 0, 0],
            ['Wall Art and Paintings',  'wall-art-and-paintings',  null, 0, 0],
            ['Artificial Plants',       'artificial-plants-flowers', null, 0, 0],
            ['Outdoor Decor',           'outdoor-decor',           null, 0, 0],
            ['Clocks',                  'clocks',                  null, 0, 0],
            ['Candle Holders',          'candle-holders',          null, 0, 0],
            // ── Pharmacy ──
            ['Best Offers',             'best-offers',             null, 0, 0],
            ['Vitamins & Supplements',  'vitamins-supplements',    null, 0, 0],
            ['Nutritional Drinks',      'nutritional-drinks',      null, 0, 0],
            ['Hair Oil',                'hair-oil',                null, 0, 0],
            ['Skin & Face Care',        'skin-face-care',          null, 0, 0],
            ['Sexual Wellness',         'sexual-wellness',         null, 0, 0],
            ['Ayurveda Products',       'ayurveda-products',       null, 0, 0],
            ['Pain Relief',             'pain-relief',             null, 0, 0],
            ['Homeopathy',              'homeopathy',              null, 0, 0],
            ['Petcare',                 'petcare',                 null, 0, 0],
            ['Fragrances Product',      'fragrances-product',      null, 0, 0],
            ['Devices',                 'devices',                 null, 0, 0],
            ['Derma Cosmetics',         'derma-cosmetics',         null, 0, 0],
            ['Vitamin Store',           'vitamin-store',           null, 0, 0],
            ['Diabetes',                'diabetes',                null, 0, 0],
            ['Surgicals',               'surgicals',               null, 0, 0],
            // ── Jewellery ──
            ['Earrings',                'earrings',                null, 0, 0],
            ['Finger Rings',            'finger-rings',            null, 0, 0],
            ['Pendants',                'pendants',                null, 0, 0],
            ['Premium Pendants',        'prium-pendants',          null, 0, 0],
            ['Mangalsutra',             'mangalsutra',             null, 0, 0],
            ['Bracelets',               'bracelets',               null, 0, 0],
            ['Bangles',                 'bangles',                 null, 0, 0],
            ['Chains',                  'chains',                  null, 0, 0],
            ['Premium Mangalsutra',     'premium-mangalsutra',     null, 0, 0],
            // ── Gift Special ──
            ["Valentine's Gift",        'valentines-gift',         null, 0, 0],
            ['Birthday',                'birthday',                null, 0, 0],
            ['Anniversary',             'anniversary',             null, 0, 0],
            ['Get Same Day',            'get-same-day',            null, 0, 0],
            ['Balloon Decor',           'balloon-decor',           null, 0, 0],
            ['Best Wishes',             'best-wishes',             null, 0, 0],
            ['Miss You',                'miss-you',                null, 0, 0],
            ['Bento Cakes',             'bento-cakes',             null, 0, 0],
            ['Get Well Soon',           'get-well-soon',           null, 0, 0],
            ['Latest Drops',            'latest-drops',            null, 0, 0],
            ['Gifts for Her',           'gifts-for-her',           null, 0, 0],
            ['Gifts for She',           'gifts-for-she',           null, 0, 0],
            ['Plants',                  'plants',                  null, 0, 0],
            ['Personalised Gifts',      'personalised-gifts',      null, 0, 0],
            ['Chocolates',              'chocolet',                null, 0, 0],
            ['7 Day 7 Gift',            '7day-7gift',              null, 0, 0],
            ['Hampers',                 'harmpers',                null, 0, 0],
            ['Hatke',                   'hatke',                   null, 0, 0],
            ['Cushion',                 'cushion',                 null, 0, 0],
            ['Muh',                     'muh',                     null, 0, 0],
            ['Photo Frames',            'photo-frames',            null, 0, 0],
            // ── Import/Premium ──
            ['International',           'international',           null, 0, 1],
            ['Overseas',                'overseas',                null, 0, 1],
            ['Premium',                 'premium',                 null, 0, 1],
            ['Gourmet',                 'gourmet',                 null, 0, 1],
            ['Exotic',                  'exotic',                  null, 0, 1],
            ['Select',                  'select',                  null, 0, 1],
            ['Special',                 'special',                 null, 0, 1],
            ['Originals',               'originals',               null, 0, 1],
            ['Global',                  'global',                  null, 0, 0],
            ['Deluxe',                  'deluxe',                  null, 0, 0],
            // ── Seasonal / Tab Roots ──
            ['Ramadan',                 'ramadam',                 null, 0, 0],
            ['Ecommerce',               'ecommerce',               null, 0, 0],
            // ── Grocery Sub-categories ──
            ['Bread & Buns',            'bread-buns',              null, 0, 0],
            ['Jams & Spreads',          'jams-spreads',            null, 0, 0],
            ['Dry Fruits & Nuts',       'dry-fruits-nuts',         null, 0, 0],
            ['Salt, Sugar & Jaggery',   'salt-sugar-jaggery',      null, 0, 0],
            ['Soap & Body Washes',      'soap-body-washes',        null, 0, 0],
            ['Dental Care',             'dental-care',             null, 0, 0],
            ['Perfumes & Powers',       'perfumes-powers',         null, 0, 0],
            ['Feminine Hygiene',        'feminine-hygiene',        null, 0, 0],
            ['Detergent',               'detergent',               null, 0, 0],
            ['Dishwash & Bars',         'dishwash-bars',           null, 0, 0],
            ['Toilet & Cleaning',       'toilet-cleaning',         null, 0, 0],
            ['Cleaning Tools',          'cleaning-tools',          null, 0, 0],
            ['Special Offer',           'special-offer',           null, 0, 0],
            ['Breads & Buns',           'breads-buns-breads-buns', null, 0, 0],
            ['Amul Butter',             'amul-butter',             null, 0, 0],
            ['Food Special',            'food-special',            null, 0, 0],
            ['Gift Special',            'gift-special',            null, 0, 0],
            ['Shop Special',            'shop-special',            null, 0, 0],
            ['Pharmacy Special',        'pharmacy-special',        null, 0, 0],
        ];

        $rows = [];
        $ci   = 0;
        
        foreach ($categories as [$name, $slug, $parentId, $sortOrder, $isFeatured]) {
            $rows[] = [
                'name'              => $name,
                'slug'              => $slug,
                'image'             => $catImages[$ci++ % count($catImages)],
                'banner'            => null,
                'parent_id'         => $parentId,
                'sort_order'        => $sortOrder,
                'is_active'         => 1,
                'is_featured'       => $isFeatured,
                'commission_percent' => null,
                'created_at'        => $now,
                'updated_at'        => $now,
                'deleted_at'        => null,
            ];
        }

        DB::table('categories')->insertOrIgnore($rows);
        $this->command->info('  → ' . count($rows) . ' categories seeded (duplicates skipped).');
    }

    // ─── Brands ──────────────────────────────────────────────────────────────

    private function seedBrands(): void
    {
        $this->command->info('Seeding brands...');

        $now  = now()->toDateTimeString();
        // Real brand image already in storage/app/public/brands/
        $logo = 'brands/brand-image.png';

        // [name, slug, is_featured]
        $brands = [
            ['Amul', 'amul', 1], ['Britannia', 'britannia', 1],
            ['Parle', 'parle', 1], ['Tata', 'tata', 1],
            ['Nestle', 'nestle', 1], ['Allen Solly', 'allen-solly', 0],
            ['Rare Rabbit', 'rare-rabbit', 0], ['Firstcry', 'firstcry', 0],
            ['H M', 'h-m', 0], ['Levis', 'levis', 0],
            ['Loreal', 'loreal', 0], ['Maybelline', 'maybelline', 0],
            ['Cipla', 'cipla', 0], ["Dr.Reddy's", 'drreddys', 0],
            ['Aurobindo', 'aurobindo', 0], ['Alkem', 'alkem', 0],
            ['Lupin', 'lupin', 0], ['Merck', 'merck', 0],
            ['Threadvibe', 'threadvibe', 0], ['Yellow Verandah', 'yellow-verandah', 0],
            ['Upcycle', 'upcycle', 0], ['Decore', 'decore', 0],
            ['Good Earth', 'good-earch', 0], ['Momentz', 'momentz', 0],
            ['AD Pro Directory', 'ad-pro-directory', 0], ['Tanishq', 'tanishq', 0],
            ['Kalyan', 'kalyan', 0], ['Caratlane', 'caratlane', 0],
            ['Senco', 'senco', 0], ['Hopscotch', 'hopscotch', 0],
            ['Lekme', 'lekme', 0], ['Titan', 'titan', 0],
            ['Mamaearth', 'mamaearth', 0], ['Noise', 'noise', 0],
            ['Portronics', 'portronics', 0], ['Ambrane', 'ambrane', 0],
            ['Nu Republic', 'nu-rupublic', 0], ['Fire Boltt', 'fire-boltt', 0],
            ['Philips', 'philips', 0], ['Bajaj', 'bajaj', 0],
            ['Agaro', 'agaro', 0], ['Pigon', 'pigon', 0],
            ['AJIO', 'ajio', 0], ['Aww Hunnie', 'aww-hunnie', 0],
            ['Woodland', 'woodland', 0], ['Campus', 'campus', 0],
            ['Licious', 'licious', 0], ['Kissan', 'kissan', 0],
            ['Classmate', 'classmate', 0], ['Moe Puppy', 'moe-puppy', 0],
            ['Godrej', 'goorej', 0], ['Drools', 'drools', 0],
            ['Bata', 'bata', 0], ['Apple', 'apple', 0],
            ['Realme', 'realme', 0], ['Samsung', 'samsung', 0],
            ['Oneplus', 'oneplus', 0], ['Ponds', 'ponds', 0],
            ['Vaseline', 'vaseline', 0], ['Himalaya', 'himalaya', 0],
            ["Johnson's", 'gohnsons', 0], ['Dove', 'dove', 0],
            ['NIVEA', 'nivea', 0], ['Adidas', 'adidas', 0],
            ['Nike', 'nike', 0], ['Xiaomi', 'xiaomi', 0],
            ['Sony', 'sony', 0], ['Dabur', 'dabur', 0],
            ['Fortune', 'fortune', 0], ['Aashirvaad', 'aashirvaad', 0],
            ['Ralph Lauren', 'ralph-laurren', 0], ['Prada', 'prada', 0],
            ['Gucci', 'gucci', 0], ['Louis Vuitton', 'louis-vuitton', 0],
            ['Flying Machine', 'flying-machine', 0], ["Harilal's", 'harilals', 0],
            ['Bikaner Elite', 'bikaner-elite', 0], ['Gokul', 'gokul', 0],
            ["Domino's Pizza", 'dominos-pizza', 0], ['KFC', 'kfc', 0],
            ['Biryani Mahal', 'biryani-mahal', 0], ["La Pino'z Pizza", 'la-pinoz-pizza', 0],
            ['Yo China', 'yo-china', 0], ['Monginis', 'monginis', 0],
            ['Pottery Barn', 'pottery-barn', 0], ['Pure Home Living', 'pure-home-living', 0],
            ['Nicobar', 'nicobar', 0], ['Ikea', 'ikea', 0],
            ['Nestasia', 'nestasia', 0], ['Carters', 'carters', 0],
            ['Mothercare', 'mothercare', 0], ['Gini & Jony', 'gini-jony', 0],
            ['Babyhug', 'babyhug', 0], ['Ed-a-Mamma', 'ed-a-mamma', 0],
            ['Pspeaches', 'pspeaches', 0], ['Abbvie', 'abbvie', 0],
            ['Roche', 'roche', 0], ['Sanofi', 'sanofi', 0],
        ];

        $rows = array_map(fn($b) => [
            'name'       => $b[0],
            'slug'       => $b[1],
            'logo'       => $logo,
            'is_active'  => 1,
            'is_featured' => $b[2],
            'sort_order' => 0,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ], $brands);

        DB::table('brands')->insertOrIgnore($rows);
        $this->command->info('  → ' . count($rows) . ' brands seeded (duplicates skipped).');
    }

    // ─── Products ────────────────────────────────────────────────────────────

    private function seedProducts(): void
    {
        $this->command->info('Seeding products...');

        $now  = now()->toDateTimeString();
        $base = [
            'store_id'           => null,
            'vendor_sku'         => null,
            'nutritional_info'   => null,
            'commission'         => null,
            'tax_rate'           => 0.00,
            'tax_class'          => null,
            'hsn_code'           => null,
            'low_stock_threshold' => 10,
            'track_inventory'    => 1,
            'allow_backorder'    => 0,
            'weight'             => null,
            'weight_unit'        => 'g',
            'length'             => null,
            'width'              => null,
            'height'             => null,
            'dimension_unit'     => 'cm',
            'brand'              => null,
            'brand_id'           => null,
            'is_active'          => 1,
            'is_featured'        => 0,
            'status'             => 'published',
            'visibility'         => 'global',
            'manufacture_date'   => null,
            'expiry_date'        => null,
            'shelf_life_days'    => null,
            'meta_title'         => null,
            'meta_description'   => null,
            'meta_image'         => null,
            'approved_by'        => null,
            'approved_at'        => null,
            'rejection_reason'   => null,
            'parent_product_id'  => null,
            'deleted_at'         => null,
            'created_at'         => $now,
            'updated_at'         => $now,
        ];

        // [name, slug, sku, barcode, short_desc, price, compare_price, qty, unit, category_id, brand_id, brand_name, is_featured]
        $products = [
            ['Fresh Bananas',           'fresh-bananas',           'QXK-0001', '8901014726752', 'Fresh, ripe bananas rich in potassium',         49,  59,  87,  'kg',    3,  null, null,        1],
            ['Organic Apples',          'organic-apples',          'QXK-0002', '8905835623023', 'Crisp and juicy organic apples',                189, 220, 50,  'kg',    4,  null, null,        1],
            ['Fresh Tomatoes',          'fresh-tomatoes',          'QXK-0003', '8909037824301', 'Farm-fresh red tomatoes',                       35,  45,  80,  'kg',    3,  null, null,        1],
            ['Green Capsicum',          'green-capsicum',          'QXK-0004', '8900471922455', 'Fresh green bell peppers',                      60,  75,  40,  'kg',    3,  null, null,        1],
            ['Fresh Spinach',           'fresh-spinach',           'QXK-0005', '8906261509042', 'Fresh green spinach leaves',                    25,  30,  60,  'bunch', 5,  null, null,        1],
            ['Amul Toned Milk',         'amul-toned-milk',         'QXK-0006', '8909235053152', 'Fresh toned milk from Amul',                    28,  30,  200, 'liter', 8,  1,    'Amul',      1],
            ['Amul Butter',             'amul-butter',             'QXK-0007', '8906647731876', 'Utterly butterly delicious',                    56,  60,  150, 'piece', 10, 1,    'Amul',      1],
            ['Britannia Bread',         'britannia-bread',         'QXK-0008', '8906431294938', 'Soft and fresh white bread',                    35,  40,  100, 'piece', 9,  2,    'Britannia', 1],
            ['Farm Fresh Eggs',         'farm-fresh-eggs',         'QXK-0009', '8903889938001', 'Fresh farm eggs - pack of 12',                  72,  80,  120, 'dozen', 11, null, null,        1],
            ['Lays Classic Salted',     'lays-classic-salted',     'QXK-0010', '8906598947562', 'Crispy potato chips',                           20,  25,  200, 'piece', 14, null, null,        1],
            ['Parle-G Biscuits',        'parle-g-biscuits',        'QXK-0011', '8908939165772', 'Classic glucose biscuits',                      10,  12,  300, 'piece', 15, 3,    'Parle',     1],
            ['Coca Cola',               'coca-cola',               'QXK-0012', '8900414669898', 'Refreshing cola drink - 750ml',                 40,  45,  150, 'piece', 16, null, null,        0],
            ['Tata Tea Gold',           'tata-tea-gold',           'QXK-0013', '8904980922500', 'Premium tea leaves - 500g',                    185, 200, 80,  'piece', 17, 4,    'Tata',      0],
            ['India Gate Basmati Rice', 'india-gate-basmati-rice', 'QXK-0014', '8902780475608', 'Premium basmati rice - 5kg',                   299, 350, 100, 'kg',    20, null, null,        0],
            ['Toor Dal',                'toor-dal',                'QXK-0015', '8908352195196', 'Premium quality toor dal',                     120, 140, 90,  'kg',    21, null, null,        0],
            ['Aashirvaad Atta',         'aashirvaad-atta',         'QXK-0016', '8906690729936', 'Whole wheat flour - 5kg',                      250, 280, 120, 'kg',    22, 73,   'Aashirvaad', 0],
            ['Fortune Sunflower Oil',   'fortune-sunflower-oil',   'QXK-0017', '8908392542523', 'Refined sunflower oil - 1L',                   180, 200, 70,  'liter', 23, 72,   'Fortune',   0],
            ['Dove Soap',               'dove-soap',               'QXK-0018', '8909185211800', 'Moisturizing beauty bar',                       45,  50,  150, 'piece', 214, 63,  'Dove',      0],
            ['Colgate Toothpaste',      'colgate-toothpaste',      'QXK-0019', '8905179229646', 'Total advanced health - 200g',                  85,  95,  180, 'piece', 215, null, null,        0],
            ['Pantene Shampoo',         'pantene-shampoo',         'QXK-0020', '8901052618545', 'Hair fall control - 340ml',                    165, 185, 100, 'piece', 27, null, null,         0],
        ];

        $rows = [];
        foreach ($products as [$name, $slugBase, $sku, $barcode, $shortDesc, $price, $comparePrice, $qty, $unit, $catId, $brandId, $brandName, $featured]) {
            $rows[] = array_merge($base, [
                'name'              => $name,
                'slug'              => $slugBase . '-' . strtoupper(substr(md5($sku), 0, 5)),
                'sku'               => $sku,
                'barcode'           => $barcode,
                'short_description' => $shortDesc,
                'description'       => $shortDesc,
                'price'             => $price,
                'compare_price'     => $comparePrice,
                'quantity'          => $qty,
                'unit'              => $unit,
                'category_id'       => $catId,
                'brand_id'          => $brandId,
                'brand'             => $brandName,
                'is_featured'       => $featured,
            ]);
        }

        DB::table('products')->insertOrIgnore($rows);
        $this->command->info('  → ' . count($rows) . ' products seeded (duplicates skipped).');
    }

    // ─── Product Images ──────────────────────────────────────────────────────

    private function seedProductImages(): void
    {
        $this->command->info('Seeding product images...');

        $now = now()->toDateTimeString();

        // Real product image already in storage/app/public/products/
        $productImage = 'products/product-image.png';

        // Product names for alt text
        $productNames = [
            1 => 'Fresh Bananas',       2 => 'Organic Apples',
            3 => 'Fresh Tomatoes',      4 => 'Green Capsicum',
            5 => 'Fresh Spinach',       6 => 'Amul Toned Milk',
            7 => 'Amul Butter',         8 => 'Britannia Bread',
            9 => 'Farm Fresh Eggs',     10 => 'Lays Classic Salted',
            11 => 'Parle-G Biscuits',   12 => 'Coca Cola',
            13 => 'Tata Tea Gold',      14 => 'India Gate Basmati Rice',
            15 => 'Toor Dal',           16 => 'Aashirvaad Atta',
            17 => 'Fortune Sunflower Oil', 18 => 'Dove Soap',
            19 => 'Colgate Toothpaste', 20 => 'Pantene Shampoo',
        ];

        $rows = [];
        foreach (range(1, 20) as $productId) {
            $rows[] = [
                'product_id' => $productId,
                'image'      => $productImage,
                'alt_text'   => $productNames[$productId] ?? null,
                'sort_order' => 0,
                'is_primary' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('product_images')->insertOrIgnore($rows);
        $this->command->info('  → ' . count($rows) . ' product images inserted.');
    }

    // ─── Home Header ─────────────────────────────────────────────────────────

    private function seedHomeHeader(): void
    {
        $this->command->info('Seeding home header config...');

        $now         = now()->toDateTimeString();
        // Real images already in storage/app/public/
        $cardImg     = '/storage/home-header/cards/card-image.png';

        // Settings - Enable all features with proper configuration
        DB::table('home_header_settings')->updateOrInsert(
            ['id' => 1],
            [
                'tabs_active'           => 1,  // Enable category tabs
                'background_active'     => 1,  // Enable background media
                'cards_active'          => 1,  // Enable quick access cards
                'cards_horizontal'      => 0,  // Quick access cards in GRID layout (not horizontal)
                'tabs_horizontal_style' => 1,  // Category tabs in HORIZONTAL/BOX style
                'created_at'            => $now,
                'updated_at'            => $now,
            ]
        );

        // Insert tabs and get their IDs - matching SQL exactly
        $tabsData = [
            ['category_id' => 88,  'name' => 'Grocery',   'use_header_name' => 0, 'background_type' => 'video', 'background_url' => '/storage/home-header/backgrounds/CtANxo82KaGrXS7GL0Oz79uYTDs2bDvG5TEIWFjx.mp4', 'sticky_header_color' => '#ededed', 'cards_horizontal' => 0, 'sort_order' => 0, 'is_active' => 1],
            ['category_id' => 75,  'name' => 'Gift',      'use_header_name' => 0, 'background_type' => 'video', 'background_url' => '/storage/home-header/backgrounds/vNmg0AkLYtm3mWC71qPREaUQsl9IzWVx7pgKq2pE.mp4', 'sticky_header_color' => '#cf2659', 'cards_horizontal' => 0, 'sort_order' => 6, 'is_active' => 1],
            ['category_id' => 90,  'name' => 'Food',      'use_header_name' => 0, 'background_type' => 'video', 'background_url' => '/storage/home-header/backgrounds/ypqiDdx4j3YuV8dGg1XZHYofH2b08tr29ma31z8Z.mp4', 'sticky_header_color' => '#ff841f', 'cards_horizontal' => 0, 'sort_order' => 2, 'is_active' => 1],
            ['category_id' => 208, 'name' => 'Ramadam',   'use_header_name' => 1, 'background_type' => 'video', 'background_url' => '/storage/home-header/backgrounds/SO73jqAXX0zalWb33RiM7Qxyatq3B3RBk9hiWcvt.mp4', 'sticky_header_color' => '#4a8751', 'cards_horizontal' => 0, 'sort_order' => 1, 'is_active' => 1],
            ['category_id' => 209, 'name' => 'Ecommerce', 'use_header_name' => 1, 'background_type' => 'image', 'background_url' => '/storage/home-header/backgrounds/bOKZeApUQ0rgXa55FMSC4CDtYS7wZ6dIXkHiYwd9.png', 'sticky_header_color' => '#FFFFFF', 'cards_horizontal' => 0, 'sort_order' => 4, 'is_active' => 1],
            ['category_id' => 97,  'name' => 'Pharmacy',  'use_header_name' => 0, 'background_type' => 'image', 'background_url' => '/storage/home-header/backgrounds/xu58PMXssE0x2jj1pyyd2xc5ZXQSR9HtWoD5BtRi.png', 'sticky_header_color' => '#FFFFFF', 'cards_horizontal' => 0, 'sort_order' => 5, 'is_active' => 1],
            ['category_id' => 94,  'name' => 'Beauty',    'use_header_name' => 0, 'background_type' => 'image', 'background_url' => null,                                                                                       'sticky_header_color' => '#FFFFFF', 'cards_horizontal' => 0, 'sort_order' => 7, 'is_active' => 0],
            ['category_id' => 91,  'name' => 'Fresh',     'use_header_name' => 1, 'background_type' => 'image', 'background_url' => '/storage/home-header/backgrounds/ljzdRI9fZ8B3rgzVdpbktrLVc5kUziWJhuALNqJ6.png', 'sticky_header_color' => '#0b7f56', 'cards_horizontal' => 0, 'sort_order' => 3, 'is_active' => 1],
        ];

        $tabIds = [];
        foreach ($tabsData as $tabData) {
            $tabData['created_at'] = $now;
            $tabData['updated_at'] = $now;
            $tabId = DB::table('home_header_tabs')->insertGetId($tabData);
            $tabIds[$tabData['name']] = $tabId;
        }

        // Cards per tab - using auto-generated tab IDs
        $cardsData = [
            // Grocery tab
            ['tab_name' => 'Grocery', 'link_id' => 22,  'sort_order' => 0],
            ['tab_name' => 'Grocery', 'link_id' => 1,   'sort_order' => 1],
            ['tab_name' => 'Grocery', 'link_id' => 7,   'sort_order' => 2],
            ['tab_name' => 'Grocery', 'link_id' => 21,  'sort_order' => 3],
            ['tab_name' => 'Grocery', 'link_id' => 15,  'sort_order' => 4],
            ['tab_name' => 'Grocery', 'link_id' => 12,  'sort_order' => 5],
            // Gift tab
            ['tab_name' => 'Gift', 'link_id' => 17,  'sort_order' => 3],
            ['tab_name' => 'Gift', 'link_id' => 179, 'sort_order' => 4],
            ['tab_name' => 'Gift', 'link_id' => 181, 'sort_order' => 5],
            ['tab_name' => 'Gift', 'link_id' => 148, 'sort_order' => 6],
            // Food tab
            ['tab_name' => 'Food', 'link_id' => 20,  'sort_order' => 1],
            ['tab_name' => 'Food', 'link_id' => 21,  'sort_order' => 0],
            ['tab_name' => 'Food', 'link_id' => 22,  'sort_order' => 2],
            ['tab_name' => 'Food', 'link_id' => 23,  'sort_order' => 3],
            ['tab_name' => 'Food', 'link_id' => 52,  'sort_order' => 4],
            ['tab_name' => 'Food', 'link_id' => 179, 'sort_order' => 5],
            // Ramadam tab
            ['tab_name' => 'Ramadam', 'link_id' => 44,  'sort_order' => 1],
            ['tab_name' => 'Ramadam', 'link_id' => 22,  'sort_order' => 2],
            ['tab_name' => 'Ramadam', 'link_id' => 46,  'sort_order' => 3],
            ['tab_name' => 'Ramadam', 'link_id' => 208, 'sort_order' => 4],
            // Pharmacy tab
            ['tab_name' => 'Pharmacy', 'link_id' => 159, 'sort_order' => 1],
            // Fresh tab
            ['tab_name' => 'Fresh', 'link_id' => 22,  'sort_order' => 1],
            ['tab_name' => 'Fresh', 'link_id' => 52,  'sort_order' => 2],
            ['tab_name' => 'Fresh', 'link_id' => 112, 'sort_order' => 3],
            ['tab_name' => 'Fresh', 'link_id' => 158, 'sort_order' => 4],
            ['tab_name' => 'Fresh', 'link_id' => 52,  'sort_order' => 5],
            ['tab_name' => 'Fresh', 'link_id' => 22,  'sort_order' => 6],
            // Ecommerce tab
            ['tab_name' => 'Ecommerce', 'link_id' => 80, 'sort_order' => 1],
        ];

        $cards = [];
        foreach ($cardsData as $cardData) {
            $cards[] = [
                'tab_id'     => $tabIds[$cardData['tab_name']],
                'image_url'  => $cardImg,
                'link_type'  => 'category',
                'link_id'    => $cardData['link_id'],
                'link_url'   => null,
                'sort_order' => $cardData['sort_order'],
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('home_header_cards')->insertOrIgnore($cards);

        $this->command->info('  → Home header settings, tabs and cards seeded.');
    }

    // ─── App Content Widgets ─────────────────────────────────────────────────

    private function seedAppContents(): void
    {
        $this->command->info('Seeding app content widgets...');

        $now        = now()->toDateTimeString();
        // Real files already in storage/app/public/app-content/
        // Using simple descriptive names that match SQL database
        $demoImg    = '/storage/app-content/media-items/demo-image.png';
        $bg1        = '/storage/app-content/backgrounds/background1.png';
        $bg2        = '/storage/app-content/backgrounds/background2.png';
        $gifBg      = '/storage/app-content/backgrounds/gif-bg.gif';

        // Real media item images to rotate through
        $mediaPool  = [$demoImg];
        $mi = 0;
        // Helper to build media_items JSON for banners, rotating through real images
        $mediaItems = function(array $items) use ($mediaPool, &$mi) {
            return json_encode(array_map(
                fn($i) => ['url' => $mediaPool[$mi++ % count($mediaPool)], 'type' => 'image', 'link_type' => $i[0], 'link_id' => $i[1], 'link_url' => null],
                $items
            ));
        };

        $rows = [
            // ── Global (tab_id = NULL) ──────────────────────────────────────
            ['header_tab_id' => null, 'type' => 'category', 'style' => 'style_1', 'title' => 'Shop by Category',             'show_title' => 1, 'subtitle' => 'Explore all categories',         'show_subtitle' => 0, 'show_view_all' => 0, 'source' => 'custom',   'enable_background' => 0, 'background_type' => null,    'background_color' => null,    'background_media_url' => null, 'grid_columns' => 4, 'grid_rows' => 2, 'enable_horizontal_animation' => 0, 'show_on_category_screen' => 0, 'show_on_tracking' => 0, 'media_items' => null, 'media_url' => null, 'media_type' => null, 'media_height' => 120, 'media_width' => null, 'link_type' => 'none', 'link_id' => null, 'link_url' => null, 'custom_items' => json_encode([1, 7, 13, 22, 25]), 'sort_order' => 1,   'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['header_tab_id' => null, 'type' => 'product',  'style' => 'style_1', 'title' => 'Trending Now',                  'show_title' => 1, 'subtitle' => 'Popular across all categories', 'show_subtitle' => 1, 'show_view_all' => 0, 'source' => 'featured', 'enable_background' => 0, 'background_type' => null,    'background_color' => null,    'background_media_url' => null, 'grid_columns' => 2, 'grid_rows' => 1, 'enable_horizontal_animation' => 1, 'show_on_category_screen' => 1, 'show_on_tracking' => 0, 'media_items' => null, 'media_url' => null, 'media_type' => null, 'media_height' => 200, 'media_width' => null, 'link_type' => 'none', 'link_id' => null, 'link_url' => null, 'custom_items' => null, 'sort_order' => 100, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['header_tab_id' => null, 'type' => 'brand',    'style' => 'style_1', 'title' => 'Top Brands',                    'show_title' => 1, 'subtitle' => 'Trusted quality',                'show_subtitle' => 1, 'show_view_all' => 0, 'source' => 'featured', 'enable_background' => 1, 'background_type' => 'color',  'background_color' => '#F5F5F5', 'background_media_url' => null, 'grid_columns' => 4, 'grid_rows' => 1, 'enable_horizontal_animation' => 1, 'show_on_category_screen' => 0, 'show_on_tracking' => 0, 'media_items' => null, 'media_url' => null, 'media_type' => null, 'media_height' => 80,  'media_width' => null, 'link_type' => 'none', 'link_id' => null, 'link_url' => null, 'custom_items' => json_encode([1, 2, 3, 4, 5]), 'sort_order' => 101, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['header_tab_id' => null, 'type' => 'product',  'style' => 'style_2', 'title' => 'Recently Added',                'show_title' => 1, 'subtitle' => 'Fresh arrivals',                 'show_subtitle' => 1, 'show_view_all' => 0, 'source' => 'recent',   'enable_background' => 0, 'background_type' => null,    'background_color' => null,    'background_media_url' => null, 'grid_columns' => 2, 'grid_rows' => 1, 'enable_horizontal_animation' => 1, 'show_on_category_screen' => 1, 'show_on_tracking' => 0, 'media_items' => null, 'media_url' => null, 'media_type' => null, 'media_height' => 200, 'media_width' => null, 'link_type' => 'none', 'link_id' => null, 'link_url' => null, 'custom_items' => null, 'sort_order' => 102, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],

            // ── Tab 1 – Grocery ─────────────────────────────────────────────
            ['header_tab_id' => 1, 'type' => 'category', 'style' => 'style_2', 'title' => 'Snacks & Drinks',                  'show_title' => 1, 'subtitle' => null,                             'show_subtitle' => 0, 'show_view_all' => 0, 'source' => 'custom',   'enable_background' => 1, 'background_type' => 'color',  'background_color' => '#fff9e5', 'background_media_url' => null, 'grid_columns' => 4, 'grid_rows' => 2, 'enable_horizontal_animation' => 0, 'show_on_category_screen' => 1, 'show_on_tracking' => 0, 'media_items' => null, 'media_url' => null, 'media_type' => 'image', 'media_height' => 200, 'media_width' => null, 'link_type' => 'none', 'link_id' => null, 'link_url' => null, 'custom_items' => json_encode([13, 15, 40, 17, 31, 34]), 'sort_order' => 1, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['header_tab_id' => 1, 'type' => 'category', 'style' => 'style_2', 'title' => 'Grocery',                          'show_title' => 1, 'subtitle' => null,                             'show_subtitle' => 0, 'show_view_all' => 0, 'source' => 'custom',   'enable_background' => 1, 'background_type' => 'color',  'background_color' => '#fff0e5', 'background_media_url' => null, 'grid_columns' => 4, 'grid_rows' => 2, 'enable_horizontal_animation' => 0, 'show_on_category_screen' => 0, 'show_on_tracking' => 0, 'media_items' => null, 'media_url' => null, 'media_type' => 'image', 'media_height' => 200, 'media_width' => null, 'link_type' => 'none', 'link_id' => null, 'link_url' => null, 'custom_items' => json_encode([20, 21, 22, 39, 24, 35, 212, 213]), 'sort_order' => 2, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['header_tab_id' => 1, 'type' => 'product',  'style' => 'style_1', 'title' => 'Featured Products',                'show_title' => 1, 'subtitle' => 'Handpicked for you',             'show_subtitle' => 1, 'show_view_all' => 0, 'source' => 'custom',   'enable_background' => 1, 'background_type' => 'image',  'background_color' => '#FFF9E6', 'background_media_url' => $bg2, 'grid_columns' => 3, 'grid_rows' => 2, 'enable_horizontal_animation' => 1, 'show_on_category_screen' => 0, 'show_on_tracking' => 0, 'media_items' => null, 'media_url' => null, 'media_type' => null, 'media_height' => 220, 'media_width' => null, 'link_type' => 'none', 'link_id' => null, 'link_url' => null, 'custom_items' => json_encode([1, 2, 3, 4, 5, 6, 7, 8]), 'sort_order' => 6, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['header_tab_id' => 1, 'type' => 'category', 'style' => 'style_2', 'title' => 'Daily & Breakfast',                'show_title' => 1, 'subtitle' => null,                             'show_subtitle' => 0, 'show_view_all' => 0, 'source' => 'custom',   'enable_background' => 1, 'background_type' => 'color',  'background_color' => '#f1fafe', 'background_media_url' => null, 'grid_columns' => 4, 'grid_rows' => 1, 'enable_horizontal_animation' => 0, 'show_on_category_screen' => 0, 'show_on_tracking' => 0, 'media_items' => null, 'media_url' => null, 'media_type' => 'image', 'media_height' => 200, 'media_width' => null, 'link_type' => 'none', 'link_id' => null, 'link_url' => null, 'custom_items' => json_encode([7, 210, 12, 211]), 'sort_order' => 3, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['header_tab_id' => 1, 'type' => 'category', 'style' => 'style_2', 'title' => 'Beauty & Personal Care',           'show_title' => 1, 'subtitle' => null,                             'show_subtitle' => 0, 'show_view_all' => 0, 'source' => 'custom',   'enable_background' => 1, 'background_type' => 'color',  'background_color' => '#ffeced', 'background_media_url' => null, 'grid_columns' => 4, 'grid_rows' => 2, 'enable_horizontal_animation' => 0, 'show_on_category_screen' => 0, 'show_on_tracking' => 0, 'media_items' => null, 'media_url' => null, 'media_type' => 'image', 'media_height' => 200, 'media_width' => null, 'link_type' => 'none', 'link_id' => null, 'link_url' => null, 'custom_items' => json_encode([214, 156, 27, 215, 216, 45, 46, 217]), 'sort_order' => 4, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['header_tab_id' => 1, 'type' => 'category', 'style' => 'style_2', 'title' => 'Cleaning Essentials',             'show_title' => 1, 'subtitle' => null,                             'show_subtitle' => 0, 'show_view_all' => 0, 'source' => 'custom',   'enable_background' => 1, 'background_type' => 'color',  'background_color' => '#e1fee5', 'background_media_url' => null, 'grid_columns' => 4, 'grid_rows' => 1, 'enable_horizontal_animation' => 0, 'show_on_category_screen' => 0, 'show_on_tracking' => 0, 'media_items' => null, 'media_url' => null, 'media_type' => 'image', 'media_height' => 200, 'media_width' => null, 'link_type' => 'none', 'link_id' => null, 'link_url' => null, 'custom_items' => json_encode([218, 219, 220, 221]), 'sort_order' => 5, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['header_tab_id' => 1, 'type' => 'media',    'style' => 'style_2', 'title' => 'Featured Banners',                 'show_title' => 1, 'subtitle' => null,                             'show_subtitle' => 0, 'show_view_all' => 0, 'source' => 'featured', 'enable_background' => 0, 'background_type' => 'color',  'background_color' => '#ffffff', 'background_media_url' => null, 'grid_columns' => 3, 'grid_rows' => 1, 'enable_horizontal_animation' => 0, 'show_on_category_screen' => 0, 'show_on_tracking' => 0, 'media_items' => $mediaItems([['none', null], ['category', 12], ['category', 31]]), 'media_url' => null, 'media_type' => 'image', 'media_height' => 127, 'media_width' => null, 'link_type' => 'none', 'link_id' => null, 'link_url' => null, 'custom_items' => null, 'sort_order' => 19, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['header_tab_id' => 1, 'type' => 'product',  'style' => 'style_2', 'title' => 'Biscuits & Cookies',               'show_title' => 1, 'subtitle' => null,                             'show_subtitle' => 0, 'show_view_all' => 1, 'source' => 'custom',   'enable_background' => 0, 'background_type' => 'color',  'background_color' => '#ffffff', 'background_media_url' => null, 'grid_columns' => 2, 'grid_rows' => 1, 'enable_horizontal_animation' => 0, 'show_on_category_screen' => 0, 'show_on_tracking' => 0, 'media_items' => null, 'media_url' => null, 'media_type' => 'image', 'media_height' => 200, 'media_width' => null, 'link_type' => 'none', 'link_id' => null, 'link_url' => null, 'custom_items' => json_encode([11, 10, 9, 8, 7, 6]), 'sort_order' => 9, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['header_tab_id' => 1, 'type' => 'product',  'style' => 'style_1', 'title' => 'Masalas & Spices',                 'show_title' => 1, 'subtitle' => 'Handpicked for Rich Taste',      'show_subtitle' => 1, 'show_view_all' => 0, 'source' => 'custom',   'enable_background' => 1, 'background_type' => 'color',  'background_color' => '#f5ecfe', 'background_media_url' => null, 'grid_columns' => 3, 'grid_rows' => 1, 'enable_horizontal_animation' => 0, 'show_on_category_screen' => 0, 'show_on_tracking' => 0, 'media_items' => null, 'media_url' => null, 'media_type' => 'image', 'media_height' => 200, 'media_width' => null, 'link_type' => 'none', 'link_id' => null, 'link_url' => null, 'custom_items' => json_encode([16, 15, 14]), 'sort_order' => 15, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['header_tab_id' => 1, 'type' => 'product',  'style' => 'style_1', 'title' => 'Dairy & Breakfast',                'show_title' => 1, 'subtitle' => 'Rise and shine!',                'show_subtitle' => 1, 'show_view_all' => 0, 'source' => 'custom',   'enable_background' => 1, 'background_type' => 'gif',    'background_color' => '#f5ecfe', 'background_media_url' => $gifBg, 'grid_columns' => 3, 'grid_rows' => 2, 'enable_horizontal_animation' => 0, 'show_on_category_screen' => 0, 'show_on_tracking' => 0, 'media_items' => null, 'media_url' => null, 'media_type' => 'image', 'media_height' => 200, 'media_width' => null, 'link_type' => 'none', 'link_id' => null, 'link_url' => null, 'custom_items' => json_encode([6, 7, 8, 9, 13]), 'sort_order' => 33, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['header_tab_id' => 1, 'type' => 'product',  'style' => 'style_2', 'title' => 'Great Deals on Fruits & Veggies',  'show_title' => 1, 'subtitle' => null,                             'show_subtitle' => 0, 'show_view_all' => 0, 'source' => 'custom',   'enable_background' => 0, 'background_type' => 'color',  'background_color' => '#ffffff', 'background_media_url' => null, 'grid_columns' => 1, 'grid_rows' => 1, 'enable_horizontal_animation' => 1, 'show_on_category_screen' => 0, 'show_on_tracking' => 0, 'media_items' => null, 'media_url' => null, 'media_type' => 'image', 'media_height' => 200, 'media_width' => null, 'link_type' => 'none', 'link_id' => null, 'link_url' => null, 'custom_items' => json_encode([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]), 'sort_order' => 31, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['header_tab_id' => 1, 'type' => 'category', 'style' => 'style_4', 'title' => 'New & Trending',                   'show_title' => 1, 'subtitle' => "Explore what's new",             'show_subtitle' => 1, 'show_view_all' => 0, 'source' => 'custom',   'enable_background' => 0, 'background_type' => 'color',  'background_color' => '#ffffff', 'background_media_url' => null, 'grid_columns' => 1, 'grid_rows' => 5, 'enable_horizontal_animation' => 0, 'show_on_category_screen' => 0, 'show_on_tracking' => 0, 'media_items' => null, 'media_url' => null, 'media_type' => 'image', 'media_height' => 200, 'media_width' => null, 'link_type' => 'none', 'link_id' => null, 'link_url' => null, 'custom_items' => json_encode([20, 22, 7, 22, 42, 156, 220, 13]), 'sort_order' => 20, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['header_tab_id' => 1, 'type' => 'product',  'style' => 'style_2', 'title' => 'Big Discounts, Bigger Savings',    'show_title' => 1, 'subtitle' => 'Fresh offers every day',         'show_subtitle' => 1, 'show_view_all' => 1, 'source' => 'custom',   'enable_background' => 0, 'background_type' => 'color',  'background_color' => '#ffffff', 'background_media_url' => null, 'grid_columns' => 1, 'grid_rows' => 1, 'enable_horizontal_animation' => 1, 'show_on_category_screen' => 0, 'show_on_tracking' => 0, 'media_items' => null, 'media_url' => null, 'media_type' => 'image', 'media_height' => 200, 'media_width' => null, 'link_type' => 'none', 'link_id' => null, 'link_url' => null, 'custom_items' => json_encode([1, 2, 3, 4, 5, 6, 7, 8, 9]), 'sort_order' => 26, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['header_tab_id' => 1, 'type' => 'product',  'style' => 'style_2', 'title' => 'Best Selling Rice',                'show_title' => 1, 'subtitle' => 'Premium quality',                'show_subtitle' => 1, 'show_view_all' => 0, 'source' => 'custom',   'enable_background' => 1, 'background_type' => 'gif',    'background_color' => '#e1fefe', 'background_media_url' => $gifBg, 'grid_columns' => 1, 'grid_rows' => 1, 'enable_horizontal_animation' => 1, 'show_on_category_screen' => 0, 'show_on_tracking' => 0, 'media_items' => null, 'media_url' => null, 'media_type' => 'image', 'media_height' => 200, 'media_width' => null, 'link_type' => 'none', 'link_id' => null, 'link_url' => null, 'custom_items' => json_encode([14, 15, 16, 17]), 'sort_order' => 16, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['header_tab_id' => 1, 'type' => 'brand',    'style' => 'style_3', 'title' => 'Top Selling Brands',               'show_title' => 1, 'subtitle' => null,                             'show_subtitle' => 0, 'show_view_all' => 0, 'source' => 'custom',   'enable_background' => 0, 'background_type' => 'color',  'background_color' => '#ffffff', 'background_media_url' => null, 'grid_columns' => 3, 'grid_rows' => 1, 'enable_horizontal_animation' => 1, 'show_on_category_screen' => 0, 'show_on_tracking' => 0, 'media_items' => null, 'media_url' => null, 'media_type' => 'image', 'media_height' => 200, 'media_width' => null, 'link_type' => 'none', 'link_id' => null, 'link_url' => null, 'custom_items' => json_encode([1, 2, 3, 4, 5]), 'sort_order' => 8, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['header_tab_id' => 1, 'type' => 'brand',    'style' => 'style_2', 'title' => 'Brands that you like',             'show_title' => 1, 'subtitle' => 'Just for you',                   'show_subtitle' => 1, 'show_view_all' => 0, 'source' => 'custom',   'enable_background' => 1, 'background_type' => 'color',  'background_color' => '#f0f5ff', 'background_media_url' => null, 'grid_columns' => 3, 'grid_rows' => 1, 'enable_horizontal_animation' => 1, 'show_on_category_screen' => 0, 'show_on_tracking' => 0, 'media_items' => null, 'media_url' => null, 'media_type' => 'image', 'media_height' => 200, 'media_width' => null, 'link_type' => 'none', 'link_id' => null, 'link_url' => null, 'custom_items' => json_encode([73, 1, 48, 72, 70, 2, 3, 4, 5]), 'sort_order' => 30, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],

            // ── Tab 4 – Food ────────────────────────────────────────────────
            ['header_tab_id' => 4, 'type' => 'category', 'style' => 'style_1', 'title' => "What's on your mind today?",       'show_title' => 1, 'subtitle' => 'Ready to Eat Meals',             'show_subtitle' => 1, 'show_view_all' => 0, 'source' => 'custom',   'enable_background' => 1, 'background_type' => 'color',  'background_color' => '#fff5e5', 'background_media_url' => null, 'grid_columns' => 3, 'grid_rows' => 1, 'enable_horizontal_animation' => 0, 'show_on_category_screen' => 0, 'show_on_tracking' => 0, 'media_items' => null, 'media_url' => null, 'media_type' => 'image', 'media_height' => 200, 'media_width' => null, 'link_type' => 'none', 'link_id' => null, 'link_url' => null, 'custom_items' => json_encode([135, 122, 118, 116, 129, 130, 133, 117, 128, 120, 115, 132, 126, 123, 50]), 'sort_order' => 2, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['header_tab_id' => 4, 'type' => 'category', 'style' => 'style_1', 'title' => 'Sweets & Desserts',                'show_title' => 1, 'subtitle' => 'A Little Slice of Happiness',    'show_subtitle' => 1, 'show_view_all' => 0, 'source' => 'custom',   'enable_background' => 1, 'background_type' => 'color',  'background_color' => '#ffe5f0', 'background_media_url' => null, 'grid_columns' => 3, 'grid_rows' => 1, 'enable_horizontal_animation' => 0, 'show_on_category_screen' => 0, 'show_on_tracking' => 0, 'media_items' => null, 'media_url' => null, 'media_type' => 'image', 'media_height' => 200, 'media_width' => null, 'link_type' => 'none', 'link_id' => null, 'link_url' => null, 'custom_items' => json_encode([121, 124, 31, 184, 127]), 'sort_order' => 3, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['header_tab_id' => 4, 'type' => 'product',  'style' => 'style_2', 'title' => 'Top Selling This Week',             'show_title' => 1, 'subtitle' => "This week's most wanted",      'show_subtitle' => 1, 'show_view_all' => 0, 'source' => 'custom',   'enable_background' => 0, 'background_type' => 'color',  'background_color' => '#ffffff', 'background_media_url' => null, 'grid_columns' => 2, 'grid_rows' => 2, 'enable_horizontal_animation' => 0, 'show_on_category_screen' => 0, 'show_on_tracking' => 0, 'media_items' => null, 'media_url' => null, 'media_type' => 'image', 'media_height' => 200, 'media_width' => null, 'link_type' => 'none', 'link_id' => null, 'link_url' => null, 'custom_items' => json_encode([14, 15, 16, 17, 13, 12, 11, 10]), 'sort_order' => 4, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['header_tab_id' => 4, 'type' => 'product',  'style' => 'style_4', 'title' => 'New Arrivals',                     'show_title' => 1, 'subtitle' => 'Just added',                      'show_subtitle' => 1, 'show_view_all' => 0, 'source' => 'custom',   'enable_background' => 1, 'background_type' => 'color',  'background_color' => '#E6F7FF', 'background_media_url' => null, 'grid_columns' => 1, 'grid_rows' => 1, 'enable_horizontal_animation' => 1, 'show_on_category_screen' => 0, 'show_on_tracking' => 0, 'media_items' => null, 'media_url' => null, 'media_type' => null, 'media_height' => 250, 'media_width' => null, 'link_type' => 'none', 'link_id' => null, 'link_url' => null, 'custom_items' => json_encode([]), 'sort_order' => 11, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],

            // ── Tab 9 – Fresh ────────────────────────────────────────────────
            ['header_tab_id' => 9, 'type' => 'category', 'style' => 'style_2', 'title' => 'Fresh Picks Daily',                'show_title' => 1, 'subtitle' => 'Purely Fresh Choices for You',   'show_subtitle' => 1, 'show_view_all' => 0, 'source' => 'custom',   'enable_background' => 0, 'background_type' => 'color',  'background_color' => '#ffffff', 'background_media_url' => null, 'grid_columns' => 3, 'grid_rows' => 4, 'enable_horizontal_animation' => 0, 'show_on_category_screen' => 0, 'show_on_tracking' => 0, 'media_items' => null, 'media_url' => null, 'media_type' => 'image', 'media_height' => 200, 'media_width' => null, 'link_type' => 'none', 'link_id' => null, 'link_url' => null, 'custom_items' => json_encode([3, 5, 4, 6, 11, 9]), 'sort_order' => 2, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['header_tab_id' => 9, 'type' => 'product',  'style' => 'style_2', 'title' => 'Seasonal Fresh Harvest',           'show_title' => 1, 'subtitle' => null,                             'show_subtitle' => 0, 'show_view_all' => 0, 'source' => 'custom',   'enable_background' => 1, 'background_type' => 'color',  'background_color' => '#fffef0', 'background_media_url' => null, 'grid_columns' => 3, 'grid_rows' => 1, 'enable_horizontal_animation' => 0, 'show_on_category_screen' => 0, 'show_on_tracking' => 0, 'media_items' => null, 'media_url' => null, 'media_type' => 'image', 'media_height' => 200, 'media_width' => null, 'link_type' => 'none', 'link_id' => null, 'link_url' => null, 'custom_items' => json_encode([3, 4, 5, 2, 1, 9, 8, 7, 6]), 'sort_order' => 3, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('app_contents')->insertOrIgnore($rows);
        $this->command->info('  → ' . count($rows) . ' app content widgets inserted.');
    }
}
