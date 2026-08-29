<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Store;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Address;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\HomeHeaderSetting;
use App\Models\HomeHeaderTab;
use App\Models\HomeHeaderCard;
use App\Models\AppContent;
use App\Enums\UserRole;
use App\Enums\StoreStatus;
use App\Enums\KycStatus;
use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Enums\ProductVisibility;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Users
        $this->createUsers();
        
        // 2. Create Store
        $this->createStore();
        
        // 3. Create Categories
        $this->createCategories();
        
        // 4. Create Brands
        $this->createBrands();
        
        // 5. Create Products
        $this->createProducts();
        
        // 6. Create Orders
        $this->createOrders();
        
        // 7. Create Home Header Config
        $this->createHomeHeaderConfig();
        
        // 8. Create App Content Widgets
        $this->createAppContentWidgets();
        
        $this->command->info('✅ Demo data seeded successfully!');
    }

    private function createUsers(): void
    {
        $this->command->info('Creating users...');
        
        // Admin
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@quixko.com',
            'password' => Hash::make('password'),
            'phone' => '+919876543210',
            'role' => UserRole::SUPER_ADMIN,
            'is_active' => true,
        ]);

        // Customer with wallet
        $customer = User::create([
            'name' => 'John Doe',
            'email' => 'customer@example.com',
            'password' => Hash::make('password'),
            'phone' => '+919876543211',
            'role' => UserRole::CUSTOMER,
            'is_active' => true,
        ]);

        // Create wallet with ₹500 balance
        $wallet = Wallet::create([
            'user_id' => $customer->id,
            'balance' => 500.00,
            'currency' => 'INR',
        ]);

        // Add wallet transaction for initial balance
        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'signup_bonus',
            'amount' => 500.00,
            'balance_before' => 0.00,
            'balance_after' => 500.00,
            'description' => 'Welcome bonus',
            'metadata' => json_encode(['reason' => 'Demo account setup']),
        ]);

        // Create customer address
        Address::create([
            'user_id' => $customer->id,
            'type' => 'Home',
            'name' => 'John Doe',
            'phone' => '+919876543211',
            'address_line_1' => '123 Main Street',
            'address_line_2' => 'Apartment 4B',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'postal_code' => '400001',
            'country' => 'India',
            'latitude' => 19.0760,
            'longitude' => 72.8777,
            'is_default' => true,
        ]);

        // Store Owner (Seller)
        $seller = User::create([
            'name' => 'Rajesh Kumar',
            'email' => 'seller@example.com',
            'password' => Hash::make('password'),
            'phone' => '+919876543212',
            'role' => UserRole::STORE_OWNER,
            'is_active' => true,
        ]);

        // Delivery Partner
        User::create([
            'name' => 'Amit Singh',
            'email' => 'delivery@example.com',
            'password' => Hash::make('password'),
            'phone' => '+919876543213',
            'role' => UserRole::DELIVERY_PARTNER,
            'is_active' => true,
        ]);
    }

    private function createStore(): void
    {
        $this->command->info('Creating store...');
        
        $seller = User::where('email', 'seller@example.com')->first();
        
        Store::create([
            'owner_id' => $seller->id,
            'name' => 'Fresh Mart',
            'slug' => 'fresh-mart',
            'description' => 'Your one-stop shop for fresh groceries and daily essentials',
            'email' => 'store@freshmart.com',
            'phone' => '+919876543214',
            'address_line_1' => '456 Market Road',
            'address_line_2' => null,
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'postal_code' => '400002',
            'country' => 'India',
            'latitude' => 19.0760,
            'longitude' => 72.8777,
            'status' => StoreStatus::ACTIVE,
            'kyc_status' => KycStatus::APPROVED,
            'is_online' => true,
            'is_featured' => true,
            'delivery_type' => 'custom',
            'delivery_method' => 'flat',
            'delivery_flat_rate' => 5.00,
            'store_free_delivery' => false,
            'min_order_amount' => 0.00,
            'packing_charge' => 0.00,
            'commission_percent' => 10.00,
            'commission_type' => 'percent',
            'rating' => 4.5,
            'rating_count' => 150,
        ]);
    }

    private function createCategories(): void
    {
        $this->command->info('Creating categories...');
        
        $categories = [
            [
                'name' => 'Fruits & Vegetables',
                'icon' => '🥬',
                'color' => '#4CAF50',
                'subcategories' => [
                    'Fresh Fruits',
                    'Fresh Vegetables',
                    'Exotic Fruits',
                    'Leafy Greens',
                    'Herbs & Seasonings',
                ]
            ],
            [
                'name' => 'Dairy & Breakfast',
                'icon' => '🥛',
                'color' => '#2196F3',
                'subcategories' => [
                    'Milk & Curd',
                    'Bread & Bakery',
                    'Butter & Cheese',
                    'Eggs',
                    'Breakfast Cereals',
                ]
            ],
            [
                'name' => 'Snacks & Beverages',
                'icon' => '🍿',
                'color' => '#FF9800',
                'subcategories' => [
                    'Chips & Namkeen',
                    'Biscuits & Cookies',
                    'Cold Drinks',
                    'Tea & Coffee',
                    'Health Drinks',
                ]
            ],
            [
                'name' => 'Staples',
                'icon' => '🌾',
                'color' => '#795548',
                'subcategories' => [
                    'Rice & Rice Products',
                    'Dals & Pulses',
                    'Atta & Flours',
                    'Edible Oils',
                    'Masalas & Spices',
                ]
            ],
            [
                'name' => 'Personal Care',
                'icon' => '🧴',
                'color' => '#E91E63',
                'subcategories' => [
                    'Bath & Body',
                    'Hair Care',
                    'Oral Care',
                    'Skin Care',
                    'Fragrances',
                ]
            ],
        ];

        foreach ($categories as $catData) {
            $category = Category::create([
                'name' => $catData['name'],
                'slug' => Str::slug($catData['name']),
                'icon' => $catData['icon'],
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 0,
            ]);

            // Create subcategories
            foreach ($catData['subcategories'] as $index => $subName) {
                Category::create([
                    'parent_id' => $category->id,
                    'name' => $subName,
                    'slug' => Str::slug($subName),
                    'is_active' => true,
                    'sort_order' => $index,
                ]);
            }
        }
    }

    private function createBrands(): void
    {
        $this->command->info('Creating brands...');
        
        $brands = [
            ['name' => 'Amul', 'logo' => '🥛'],
            ['name' => 'Britannia', 'logo' => '🍪'],
            ['name' => 'Parle', 'logo' => '🍪'],
            ['name' => 'Tata', 'logo' => '☕'],
            ['name' => 'Nestle', 'logo' => '🍫'],
        ];

        foreach ($brands as $brand) {
            Brand::create([
                'name' => $brand['name'],
                'slug' => Str::slug($brand['name']),
                'logo' => $brand['logo'],
                'is_active' => true,
                'is_featured' => true,
            ]);
        }
    }

    private function createProducts(): void
    {
        $this->command->info('Creating products...');
        
        $store = Store::first();
        
        $products = [
            // Fruits & Vegetables
            [
                'name' => 'Fresh Bananas',
                'category' => 'Fresh Fruits',
                'price' => 49.00,
                'compare_price' => 59.00,
                'unit' => 'kg',
                'quantity' => 100,
                'description' => 'Fresh, ripe bananas rich in potassium',
                'image' => '🍌',
            ],
            [
                'name' => 'Organic Apples',
                'category' => 'Fresh Fruits',
                'price' => 189.00,
                'compare_price' => 220.00,
                'unit' => 'kg',
                'quantity' => 50,
                'description' => 'Crisp and juicy organic apples',
                'image' => '🍎',
            ],
            [
                'name' => 'Fresh Tomatoes',
                'category' => 'Fresh Vegetables',
                'price' => 35.00,
                'compare_price' => 45.00,
                'unit' => 'kg',
                'quantity' => 80,
                'description' => 'Farm-fresh red tomatoes',
                'image' => '🍅',
            ],
            [
                'name' => 'Green Capsicum',
                'category' => 'Fresh Vegetables',
                'price' => 60.00,
                'compare_price' => 75.00,
                'unit' => 'kg',
                'quantity' => 40,
                'description' => 'Fresh green bell peppers',
                'image' => '🫑',
            ],
            [
                'name' => 'Fresh Spinach',
                'category' => 'Leafy Greens',
                'price' => 25.00,
                'compare_price' => 30.00,
                'unit' => 'bunch',
                'quantity' => 60,
                'description' => 'Fresh green spinach leaves',
                'image' => '🥬',
            ],
            
            // Dairy & Breakfast
            [
                'name' => 'Amul Toned Milk',
                'category' => 'Milk & Curd',
                'brand' => 'Amul',
                'price' => 28.00,
                'compare_price' => 30.00,
                'unit' => 'liter',
                'quantity' => 200,
                'description' => 'Fresh toned milk from Amul',
                'image' => '🥛',
            ],
            [
                'name' => 'Amul Butter',
                'category' => 'Butter & Cheese',
                'brand' => 'Amul',
                'price' => 56.00,
                'compare_price' => 60.00,
                'unit' => 'piece',
                'quantity' => 150,
                'description' => 'Utterly butterly delicious',
                'image' => '🧈',
            ],
            [
                'name' => 'Britannia Bread',
                'category' => 'Bread & Bakery',
                'brand' => 'Britannia',
                'price' => 35.00,
                'compare_price' => 40.00,
                'unit' => 'piece',
                'quantity' => 100,
                'description' => 'Soft and fresh white bread',
                'image' => '🍞',
            ],
            [
                'name' => 'Farm Fresh Eggs',
                'category' => 'Eggs',
                'price' => 72.00,
                'compare_price' => 80.00,
                'unit' => 'dozen',
                'quantity' => 120,
                'description' => 'Fresh farm eggs - pack of 12',
                'image' => '🥚',
            ],
            
            // Snacks & Beverages
            [
                'name' => 'Lays Classic Salted',
                'category' => 'Chips & Namkeen',
                'price' => 20.00,
                'compare_price' => 25.00,
                'unit' => 'piece',
                'quantity' => 200,
                'description' => 'Crispy potato chips',
                'image' => '🥔',
            ],
            [
                'name' => 'Parle-G Biscuits',
                'category' => 'Biscuits & Cookies',
                'brand' => 'Parle',
                'price' => 10.00,
                'compare_price' => 12.00,
                'unit' => 'piece',
                'quantity' => 300,
                'description' => 'Classic glucose biscuits',
                'image' => '🍪',
            ],
            [
                'name' => 'Coca Cola',
                'category' => 'Cold Drinks',
                'price' => 40.00,
                'compare_price' => 45.00,
                'unit' => 'piece',
                'quantity' => 150,
                'description' => 'Refreshing cola drink - 750ml',
                'image' => '🥤',
            ],
            [
                'name' => 'Tata Tea Gold',
                'category' => 'Tea & Coffee',
                'brand' => 'Tata',
                'price' => 185.00,
                'compare_price' => 200.00,
                'unit' => 'piece',
                'quantity' => 80,
                'description' => 'Premium tea leaves - 500g',
                'image' => '☕',
            ],
            
            // Staples
            [
                'name' => 'India Gate Basmati Rice',
                'category' => 'Rice & Rice Products',
                'price' => 299.00,
                'compare_price' => 350.00,
                'unit' => 'kg',
                'quantity' => 100,
                'description' => 'Premium basmati rice - 5kg',
                'image' => '🌾',
            ],
            [
                'name' => 'Toor Dal',
                'category' => 'Dals & Pulses',
                'price' => 120.00,
                'compare_price' => 140.00,
                'unit' => 'kg',
                'quantity' => 90,
                'description' => 'Premium quality toor dal',
                'image' => '🫘',
            ],
            [
                'name' => 'Aashirvaad Atta',
                'category' => 'Atta & Flours',
                'price' => 250.00,
                'compare_price' => 280.00,
                'unit' => 'kg',
                'quantity' => 120,
                'description' => 'Whole wheat flour - 5kg',
                'image' => '🌾',
            ],
            [
                'name' => 'Fortune Sunflower Oil',
                'category' => 'Edible Oils',
                'price' => 180.00,
                'compare_price' => 200.00,
                'unit' => 'liter',
                'quantity' => 70,
                'description' => 'Refined sunflower oil - 1L',
                'image' => '🛢️',
            ],
            
            // Personal Care
            [
                'name' => 'Dove Soap',
                'category' => 'Bath & Body',
                'price' => 45.00,
                'compare_price' => 50.00,
                'unit' => 'piece',
                'quantity' => 150,
                'description' => 'Moisturizing beauty bar',
                'image' => '🧼',
            ],
            [
                'name' => 'Colgate Toothpaste',
                'category' => 'Oral Care',
                'price' => 85.00,
                'compare_price' => 95.00,
                'unit' => 'piece',
                'quantity' => 180,
                'description' => 'Total advanced health - 200g',
                'image' => '🪥',
            ],
            [
                'name' => 'Pantene Shampoo',
                'category' => 'Hair Care',
                'price' => 165.00,
                'compare_price' => 185.00,
                'unit' => 'piece',
                'quantity' => 100,
                'description' => 'Hair fall control - 340ml',
                'image' => '🧴',
            ],
        ];

        foreach ($products as $index => $prodData) {
            $category = Category::where('name', $prodData['category'])->first();
            $brand = isset($prodData['brand']) ? Brand::where('name', $prodData['brand'])->first() : null;
            
            $product = Product::create([
                'store_id' => $store->id,
                'name' => $prodData['name'],
                'slug' => Str::slug($prodData['name']) . '-' . Str::random(5),
                'sku' => 'QXK-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'price' => $prodData['price'],
                'compare_price' => $prodData['compare_price'],
                'quantity' => $prodData['quantity'],
                'low_stock_threshold' => 10,
                'category_id' => $category?->id,
                'brand_id' => $brand?->id,
                'brand' => $brand?->name,
                'unit' => $prodData['unit'],
                'description' => $prodData['description'],
                'short_description' => $prodData['description'],
                'status' => ProductStatus::PUBLISHED,
                'visibility' => ProductVisibility::GLOBAL,
                'is_active' => true,
                'is_featured' => $index < 10, // First 10 products are featured
                'track_inventory' => true,
                'allow_backorder' => false,
            ]);

            // Add product image (using emoji as placeholder)
            ProductImage::create([
                'product_id' => $product->id,
                'image' => $prodData['image'],
                'alt_text' => $prodData['name'],
                'is_primary' => true,
                'sort_order' => 0,
            ]);
        }
    }

    private function createOrders(): void
    {
        $this->command->info('Creating orders...');
        
        $customer = User::where('email', 'customer@example.com')->first();
        $store = Store::first();
        $address = $customer->addresses()->first();
        $products = Product::take(5)->get();

        $orderStatuses = [
            OrderStatus::PENDING,
            OrderStatus::CONFIRMED,
            OrderStatus::PACKED,
            OrderStatus::OUT_FOR_DELIVERY,
            OrderStatus::DELIVERED,
        ];

        foreach ($orderStatuses as $index => $status) {
            $subtotal = 0;
            $items = [];

            // Add 2-3 random products to each order
            $orderProducts = $products->random(rand(2, 3));
            
            foreach ($orderProducts as $product) {
                $quantity = rand(1, 3);
                $itemTotal = $product->price * $quantity;
                $subtotal += $itemTotal;
                
                $items[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'quantity' => $quantity,
                    'price' => $product->price,
                    'total' => $itemTotal,
                ];
            }

            $deliveryFee = 5.00;
            $tax = $subtotal * 0.05; // 5% tax
            $total = $subtotal + $deliveryFee + $tax;

            $order = Order::create([
                'user_id' => $customer->id,
                'store_id' => $store->id,
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'status' => $status,
                'subtotal' => $subtotal,
                'discount' => 0,
                'delivery_fee' => $deliveryFee,
                'tax' => $tax,
                'total' => $total,
                'wallet_amount' => 0,
                'address_id' => $address->id,
                'payment_method' => $index % 2 == 0 ? 'cod' : 'razorpay',
                'payment_status' => $status === OrderStatus::DELIVERED ? 'paid' : 'pending',
                'notes' => 'Demo order #' . ($index + 1),
                'created_at' => now()->subDays(5 - $index),
                'delivered_at' => $status === OrderStatus::DELIVERED ? now()->subDays(1) : null,
            ]);

            // Create order items
            foreach ($items as $item) {
                $order->items()->create($item);
            }
        }
    }

    private function createHomeHeaderConfig(): void
    {
        $this->command->info('Creating home header config...');
        
        // Update home header settings (already created by migration)
        HomeHeaderSetting::updateSettings([
            'tabs_active' => true,
            'background_active' => true,
            'cards_active' => true,
            'cards_horizontal' => false,
        ]);

        // Get main categories for tabs
        $categories = Category::whereNull('parent_id')->take(4)->get();

        // Create tabs
        foreach ($categories as $index => $category) {
            $tab = HomeHeaderTab::create([
                'category_id' => $category->id,
                'name' => null, // Will use category name
                'use_header_name' => false,
                'background_type' => 'image',
                'background_url' => null,
                'cards_horizontal' => false,
                'sort_order' => $index,
                'is_active' => true,
            ]);

            // Create cards for each tab
            $this->createCardsForTab($tab, $category);
        }
    }

    private function createCardsForTab($tab, $category): void
    {
        // Get subcategories for this category
        $subcategories = Category::where('parent_id', $category->id)->take(4)->get();
        
        foreach ($subcategories as $index => $subcategory) {
            HomeHeaderCard::create([
                'tab_id' => $tab->id,
                'image_url' => $subcategory->icon ?? '📦',
                'link_type' => 'category',
                'link_id' => $subcategory->id,
                'link_url' => null,
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }
    }

    private function createAppContentWidgets(): void
    {
        $this->command->info('Creating app content widgets...');
        
        // Get all tabs
        $tabs = HomeHeaderTab::all();
        
        // ============================================
        // GLOBAL WIDGETS (Show on all tabs)
        // ============================================
        
        // 1. Banner Media Slider - style_1
        AppContent::create([
            'header_tab_id' => null,
            'type' => 'media',
            'style' => 'style_1',
            'title' => 'Special Offers',
            'show_title' => false,
            'subtitle' => null,
            'show_subtitle' => false,
            'source' => null,
            'enable_background' => false,
            'background_type' => null,
            'background_color' => null,
            'background_media_url' => null,
            'grid_columns' => 1,
            'grid_rows' => 1,
            'enable_horizontal_animation' => true,
            'show_on_category_screen' => false,
            'media_items' => json_encode([
                ['url' => '🎉', 'type' => 'image'],
                ['url' => '🥬', 'type' => 'image'],
                ['url' => '🛒', 'type' => 'image'],
            ]),
            'media_url' => null,
            'media_type' => 'image',
            'media_height' => 180,
            'media_width' => null,
            'link_type' => 'none',
            'link_id' => null,
            'link_url' => null,
            'custom_items' => null,
            'sort_order' => 0,
            'is_active' => true,
        ]);

        // 2. Category Grid - style_1
        $allCategories = Category::whereNull('parent_id')->take(8)->pluck('id')->toArray();
        AppContent::create([
            'header_tab_id' => null,
            'type' => 'category',
            'style' => 'style_1',
            'title' => 'Shop by Category',
            'show_title' => true,
            'subtitle' => 'Explore all categories',
            'show_subtitle' => false,
            'source' => 'custom',
            'enable_background' => false,
            'background_type' => null,
            'background_color' => null,
            'background_media_url' => null,
            'grid_columns' => 4,
            'grid_rows' => 2,
            'enable_horizontal_animation' => false,
            'show_on_category_screen' => false,
            'media_items' => null,
            'media_url' => null,
            'media_type' => null,
            'media_height' => 120,
            'media_width' => null,
            'link_type' => 'none',
            'link_id' => null,
            'link_url' => null,
            'custom_items' => $allCategories,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // ============================================
        // TAB-SPECIFIC WIDGETS (Rich content per tab)
        // ============================================
        
        foreach ($tabs as $tabIndex => $tab) {
            $sortOrder = 2; // Start after global widgets
            
            // Get products from this tab's category
            $categoryProducts = Product::where('category_id', $tab->category_id)
                ->orWhereHas('category', function($q) use ($tab) {
                    $q->where('parent_id', $tab->category_id);
                })
                ->where('is_active', true)
                ->take(20)
                ->pluck('id')
                ->toArray();

            // Get subcategories for this tab
            $subcategories = Category::where('parent_id', $tab->category_id)
                ->take(6)
                ->pluck('id')
                ->toArray();

            if (!empty($categoryProducts)) {
                
                // Widget 1: Featured Products - style_1 (Horizontal Carousel)
                AppContent::create([
                    'header_tab_id' => $tab->id,
                    'type' => 'product',
                    'style' => 'style_1',
                    'title' => '⭐ Featured in ' . $tab->category->name,
                    'show_title' => true,
                    'subtitle' => 'Handpicked for you',
                    'show_subtitle' => true,
                    'source' => 'custom',
                    'enable_background' => true,
                    'background_type' => 'color',
                    'background_color' => '#FFF9E6',
                    'background_media_url' => null,
                    'grid_columns' => 2,
                    'grid_rows' => 1,
                    'enable_horizontal_animation' => true,
                    'show_on_category_screen' => false,
                    'media_items' => null,
                    'media_url' => null,
                    'media_type' => null,
                    'media_height' => 220,
                    'media_width' => null,
                    'link_type' => 'none',
                    'link_id' => null,
                    'link_url' => null,
                    'custom_items' => array_slice($categoryProducts, 0, 10),
                    'sort_order' => $sortOrder++,
                    'is_active' => true,
                ]);

                // Widget 2: Media Banner - style_2 (Full width banner)
                AppContent::create([
                    'header_tab_id' => $tab->id,
                    'type' => 'media',
                    'style' => 'style_2',
                    'title' => 'Special Deals',
                    'show_title' => false,
                    'subtitle' => null,
                    'show_subtitle' => false,
                    'source' => null,
                    'enable_background' => false,
                    'background_type' => null,
                    'background_color' => null,
                    'background_media_url' => null,
                    'grid_columns' => 1,
                    'grid_rows' => 1,
                    'enable_horizontal_animation' => false,
                    'show_on_category_screen' => false,
                    'media_items' => json_encode([
                        ['url' => $tab->category->icon ?? '🎯', 'type' => 'image'],
                    ]),
                    'media_url' => null,
                    'media_type' => 'image',
                    'media_height' => 150,
                    'media_width' => null,
                    'link_type' => 'category',
                    'link_id' => $tab->category_id,
                    'link_url' => null,
                    'custom_items' => null,
                    'sort_order' => $sortOrder++,
                    'is_active' => true,
                ]);

                // Widget 3: Subcategories - style_2 (Horizontal scroll)
                if (!empty($subcategories)) {
                    AppContent::create([
                        'header_tab_id' => $tab->id,
                        'type' => 'category',
                        'style' => 'style_2',
                        'title' => 'Browse ' . $tab->category->name,
                        'show_title' => true,
                        'subtitle' => 'Quick access',
                        'show_subtitle' => false,
                        'source' => 'custom',
                        'enable_background' => false,
                        'background_type' => null,
                        'background_color' => null,
                        'background_media_url' => null,
                        'grid_columns' => 3,
                        'grid_rows' => 1,
                        'enable_horizontal_animation' => true,
                        'show_on_category_screen' => false,
                        'media_items' => null,
                        'media_url' => null,
                        'media_type' => null,
                        'media_height' => 100,
                        'media_width' => null,
                        'link_type' => 'none',
                        'link_id' => null,
                        'link_url' => null,
                        'custom_items' => $subcategories,
                        'sort_order' => $sortOrder++,
                        'is_active' => true,
                    ]);
                }

                // Widget 4: Best Sellers - style_2 (Grid layout)
                AppContent::create([
                    'header_tab_id' => $tab->id,
                    'type' => 'product',
                    'style' => 'style_2',
                    'title' => '🔥 Best Sellers',
                    'show_title' => true,
                    'subtitle' => 'Most popular items',
                    'show_subtitle' => true,
                    'source' => 'custom',
                    'enable_background' => false,
                    'background_type' => null,
                    'background_color' => null,
                    'background_media_url' => null,
                    'grid_columns' => 2,
                    'grid_rows' => 2,
                    'enable_horizontal_animation' => false,
                    'show_on_category_screen' => false,
                    'media_items' => null,
                    'media_url' => null,
                    'media_type' => null,
                    'media_height' => 200,
                    'media_width' => null,
                    'link_type' => 'none',
                    'link_id' => null,
                    'link_url' => null,
                    'custom_items' => array_slice($categoryProducts, 0, 4),
                    'sort_order' => $sortOrder++,
                    'is_active' => true,
                ]);

                // Widget 5: Deals & Offers - style_3 (Compact carousel)
                AppContent::create([
                    'header_tab_id' => $tab->id,
                    'type' => 'product',
                    'style' => 'style_3',
                    'title' => '💰 Today\'s Deals',
                    'show_title' => true,
                    'subtitle' => 'Limited time offers',
                    'show_subtitle' => true,
                    'source' => 'custom',
                    'enable_background' => true,
                    'background_type' => 'color',
                    'background_color' => '#FFE6E6',
                    'background_media_url' => null,
                    'grid_columns' => 3,
                    'grid_rows' => 1,
                    'enable_horizontal_animation' => true,
                    'show_on_category_screen' => false,
                    'media_items' => null,
                    'media_url' => null,
                    'media_type' => null,
                    'media_height' => 180,
                    'media_width' => null,
                    'link_type' => 'none',
                    'link_id' => null,
                    'link_url' => null,
                    'custom_items' => array_slice($categoryProducts, 5, 8),
                    'sort_order' => $sortOrder++,
                    'is_active' => true,
                ]);

                // Widget 6: Categories Grid - style_3 (Compact grid)
                if (!empty($subcategories)) {
                    AppContent::create([
                        'header_tab_id' => $tab->id,
                        'type' => 'category',
                        'style' => 'style_3',
                        'title' => 'Explore More',
                        'show_title' => true,
                        'subtitle' => null,
                        'show_subtitle' => false,
                        'source' => 'custom',
                        'enable_background' => false,
                        'background_type' => null,
                        'background_color' => null,
                        'background_media_url' => null,
                        'grid_columns' => 3,
                        'grid_rows' => 2,
                        'enable_horizontal_animation' => false,
                        'show_on_category_screen' => false,
                        'media_items' => null,
                        'media_url' => null,
                        'media_type' => null,
                        'media_height' => 90,
                        'media_width' => null,
                        'link_type' => 'none',
                        'link_id' => null,
                        'link_url' => null,
                        'custom_items' => $subcategories,
                        'sort_order' => $sortOrder++,
                        'is_active' => true,
                    ]);
                }

                // Widget 7: New Arrivals - style_4 (Large cards)
                AppContent::create([
                    'header_tab_id' => $tab->id,
                    'type' => 'product',
                    'style' => 'style_4',
                    'title' => '✨ New Arrivals',
                    'show_title' => true,
                    'subtitle' => 'Just added',
                    'show_subtitle' => true,
                    'source' => 'custom',
                    'enable_background' => true,
                    'background_type' => 'color',
                    'background_color' => '#E6F7FF',
                    'background_media_url' => null,
                    'grid_columns' => 1,
                    'grid_rows' => 1,
                    'enable_horizontal_animation' => true,
                    'show_on_category_screen' => false,
                    'media_items' => null,
                    'media_url' => null,
                    'media_type' => null,
                    'media_height' => 250,
                    'media_width' => null,
                    'link_type' => 'none',
                    'link_id' => null,
                    'link_url' => null,
                    'custom_items' => array_slice($categoryProducts, 10, 6),
                    'sort_order' => $sortOrder++,
                    'is_active' => true,
                ]);

                // Widget 8: Media Grid - style_3 (Multiple banners)
                AppContent::create([
                    'header_tab_id' => $tab->id,
                    'type' => 'media',
                    'style' => 'style_3',
                    'title' => 'Promotions',
                    'show_title' => false,
                    'subtitle' => null,
                    'show_subtitle' => false,
                    'source' => null,
                    'enable_background' => false,
                    'background_type' => null,
                    'background_color' => null,
                    'background_media_url' => null,
                    'grid_columns' => 2,
                    'grid_rows' => 1,
                    'enable_horizontal_animation' => false,
                    'show_on_category_screen' => false,
                    'media_items' => json_encode([
                        ['url' => '🎁', 'type' => 'image'],
                        ['url' => '💝', 'type' => 'image'],
                    ]),
                    'media_url' => null,
                    'media_type' => 'image',
                    'media_height' => 120,
                    'media_width' => null,
                    'link_type' => 'none',
                    'link_id' => null,
                    'link_url' => null,
                    'custom_items' => null,
                    'sort_order' => $sortOrder++,
                    'is_active' => true,
                ]);

                // Widget 9: Categories - style_4 (Large category cards)
                if (!empty($subcategories)) {
                    AppContent::create([
                        'header_tab_id' => $tab->id,
                        'type' => 'category',
                        'style' => 'style_4',
                        'title' => 'Popular Categories',
                        'show_title' => true,
                        'subtitle' => 'Shop by type',
                        'show_subtitle' => true,
                        'source' => 'custom',
                        'enable_background' => false,
                        'background_type' => null,
                        'background_color' => null,
                        'background_media_url' => null,
                        'grid_columns' => 2,
                        'grid_rows' => 1,
                        'enable_horizontal_animation' => true,
                        'show_on_category_screen' => false,
                        'media_items' => null,
                        'media_url' => null,
                        'media_type' => null,
                        'media_height' => 140,
                        'media_width' => null,
                        'link_type' => 'none',
                        'link_id' => null,
                        'link_url' => null,
                        'custom_items' => array_slice($subcategories, 0, 4),
                        'sort_order' => $sortOrder++,
                        'is_active' => true,
                    ]);
                }
            }
        }

        // ============================================
        // MORE GLOBAL WIDGETS (Bottom of all tabs)
        // ============================================

        // Global Featured Products - style_1
        AppContent::create([
            'header_tab_id' => null,
            'type' => 'product',
            'style' => 'style_1',
            'title' => '🌟 Trending Now',
            'show_title' => true,
            'subtitle' => 'Popular across all categories',
            'show_subtitle' => true,
            'source' => 'featured',
            'enable_background' => false,
            'background_type' => null,
            'background_color' => null,
            'background_media_url' => null,
            'grid_columns' => 2,
            'grid_rows' => 1,
            'enable_horizontal_animation' => true,
            'show_on_category_screen' => true,
            'media_items' => null,
            'media_url' => null,
            'media_type' => null,
            'media_height' => 200,
            'media_width' => null,
            'link_type' => 'none',
            'link_id' => null,
            'link_url' => null,
            'custom_items' => null,
            'sort_order' => 100,
            'is_active' => true,
        ]);

        // Global Brand Carousel - style_1
        $brands = Brand::where('is_featured', true)->pluck('id')->toArray();
        if (!empty($brands)) {
            AppContent::create([
                'header_tab_id' => null,
                'type' => 'brand',
                'style' => 'style_1',
                'title' => '🏆 Top Brands',
                'show_title' => true,
                'subtitle' => 'Trusted quality',
                'show_subtitle' => true,
                'source' => 'featured',
                'enable_background' => true,
                'background_type' => 'color',
                'background_color' => '#F5F5F5',
                'background_media_url' => null,
                'grid_columns' => 4,
                'grid_rows' => 1,
                'enable_horizontal_animation' => true,
                'show_on_category_screen' => false,
                'media_items' => null,
                'media_url' => null,
                'media_type' => null,
                'media_height' => 80,
                'media_width' => null,
                'link_type' => 'none',
                'link_id' => null,
                'link_url' => null,
                'custom_items' => $brands,
                'sort_order' => 101,
                'is_active' => true,
            ]);
        }

        // Global Recent Products - style_2
        AppContent::create([
            'header_tab_id' => null,
            'type' => 'product',
            'style' => 'style_2',
            'title' => '🆕 Recently Added',
            'show_title' => true,
            'subtitle' => 'Fresh arrivals',
            'show_subtitle' => true,
            'source' => 'recent',
            'enable_background' => false,
            'background_type' => null,
            'background_color' => null,
            'background_media_url' => null,
            'grid_columns' => 2,
            'grid_rows' => 1,
            'enable_horizontal_animation' => true,
            'show_on_category_screen' => true,
            'media_items' => null,
            'media_url' => null,
            'media_type' => null,
            'media_height' => 200,
            'media_width' => null,
            'link_type' => 'none',
            'link_id' => null,
            'link_url' => null,
            'custom_items' => null,
            'sort_order' => 102,
            'is_active' => true,
        ]);
    }
}
