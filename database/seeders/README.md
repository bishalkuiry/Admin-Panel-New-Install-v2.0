# Database Seeders

## Fresh Installation

To seed the database with demo data, run:

```bash
php artisan migrate:fresh --seed
```

This will:
1. Drop all tables
2. Run all migrations
3. Seed the database with demo data

## What Gets Seeded

### 1. **Users** (4 users)
- **Admin**: admin@quixko.com / password
  - Role: Super Admin
  - Full access to admin panel

- **Customer**: customer@example.com / password
  - Role: Customer
  - Wallet Balance: ₹500
  - Has 1 delivery address

- **Seller**: seller@example.com / password
  - Role: Store Owner
  - Owns "Fresh Mart" store

- **Delivery Partner**: delivery@example.com / password
  - Role: Delivery Partner

### 2. **Store** (1 store)
- **Fresh Mart**
  - Status: Active
  - KYC: Approved
  - Delivery: Enabled (₹5 flat rate)
  - Rating: 4.5 ⭐ (150 reviews)

### 3. **Categories** (5 main + 25 subcategories)
- 🥬 Fruits & Vegetables (5 subcategories)
- 🥛 Dairy & Breakfast (5 subcategories)
- 🍿 Snacks & Beverages (5 subcategories)
- 🌾 Staples (5 subcategories)
- 🧴 Personal Care (5 subcategories)

### 4. **Brands** (5 brands)
- Amul, Britannia, Parle, Tata, Nestle

### 5. **Products** (20 products)
- All products are active and published
- First 10 products are featured
- Realistic pricing with compare prices
- Stock quantities: 40-300 units
- Categories: Distributed across all categories

### 6. **Orders** (5 orders with different statuses)
- Order 1: Pending
- Order 2: Confirmed
- Order 3: Processing
- Order 4: Out for Delivery
- Order 5: Delivered

Each order contains 2-3 random products.

### 7. **Home Header Config** (Zepto-style)
- Settings: Tabs, Background, Cards enabled
- 4 Tabs: Linked to main categories
- Each tab has cards linking to subcategories
- Colors: Professional Zepto-style palette

### 8. **App Content Widgets** (5 widgets)
- Banner Media Slider (3 images)
- Category Grid (8 categories, 4x2 grid)
- Featured Products Carousel
- Recent Products Carousel
- Brand Carousel (Top brands)

### 9. **Settings**
- All system settings configured
- Wallet system enabled
- Signup bonus: ₹100
- Single store cart: Enabled
- Delivery: ₹5 flat rate

## Individual Seeders

You can run individual seeders:

```bash
# Settings only
php artisan db:seed --class=ConsolidatedSettingsSeeder

# Wallet settings only
php artisan db:seed --class=WalletSettingsSeeder

# Units only
php artisan db:seed --class=UnitsSeeder

# Demo data only (requires settings to be seeded first)
php artisan db:seed --class=DemoDataSeeder
```

## Testing the App

After seeding, you can:

1. **Login to Admin Panel**
   - URL: http://your-domain.com/admin
   - Email: admin@quixko.com
   - Password: password

2. **Test Mobile App**
   - Login as customer: customer@example.com / password
   - Wallet balance: ₹500
   - Browse 20 products across 5 categories
   - View 5 orders with different statuses
   - Test wallet payment (partial/full)

3. **Test Seller Panel**
   - Login as seller: seller@example.com / password
   - Manage "Fresh Mart" store
   - View orders and products

4. **Test Delivery Partner**
   - Login as delivery: delivery@example.com / password
   - View assigned deliveries

## Notes

- All passwords are: `password`
- Emojis are used as placeholder images
- All data is realistic and production-ready
- Wallet transactions are properly recorded
- Orders have realistic timestamps (last 5 days)
- Products have proper inventory tracking

## Resetting Data

To reset and reseed:

```bash
php artisan migrate:fresh --seed
```

⚠️ **Warning**: This will delete ALL data and reseed from scratch!

## Production Use

For production, you should:
1. Remove or disable DemoDataSeeder
2. Keep ConsolidatedSettingsSeeder and WalletSettingsSeeder
3. Create your own data through admin panel
4. Update DatabaseSeeder.php to only call settings seeders

```php
// Production DatabaseSeeder.php
public function run(): void
{
    $this->call([
        ConsolidatedSettingsSeeder::class,
        WalletSettingsSeeder::class,
        UnitsSeeder::class,
        // DemoDataSeeder::class, // Comment out for production
    ]);
}
```
