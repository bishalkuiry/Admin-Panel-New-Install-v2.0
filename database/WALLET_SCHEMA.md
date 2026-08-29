# Wallet System Database Schema

## Overview

This document describes the database schema for the Quixko Wallet System. The schema consists of three main tables that handle wallet balances, transaction history, and withdrawal requests.

## Tables

### 1. `wallets`

Stores wallet information for each user in the system.

**Columns:**

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique wallet identifier |
| `user_id` | BIGINT UNSIGNED | FOREIGN KEY (users.id), NOT NULL, UNIQUE | Reference to the user who owns this wallet |
| `balance` | DECIMAL(15,2) | NOT NULL, DEFAULT 0.00 | Current wallet balance |
| `currency` | VARCHAR(3) | NOT NULL, DEFAULT 'INR' | Currency code (ISO 4217) |
| `created_at` | TIMESTAMP | NULL | Wallet creation timestamp |
| `updated_at` | TIMESTAMP | NULL | Last update timestamp |

**Indexes:**

- `PRIMARY KEY (id)` - Primary key index
- `UNIQUE KEY unique_user_wallet (user_id)` - Ensures one wallet per user
- `INDEX idx_balance (balance)` - Optimizes balance range queries
- `FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE` - Maintains referential integrity

**Constraints:**

- Each user can have only one wallet (enforced by unique constraint on `user_id`)
- Balance must be non-negative (enforced at application level)
- When a user is deleted, their wallet is automatically deleted (CASCADE)

---

### 2. `wallet_transactions`

Records all wallet transactions for audit trail and transaction history.

**Columns:**

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique transaction identifier |
| `wallet_id` | BIGINT UNSIGNED | FOREIGN KEY (wallets.id), NOT NULL | Reference to the wallet |
| `type` | VARCHAR(50) | NOT NULL | Transaction type (see types below) |
| `amount` | DECIMAL(15,2) | NOT NULL | Transaction amount (positive for credit, negative for debit) |
| `balance_before` | DECIMAL(15,2) | NOT NULL | Wallet balance before transaction |
| `balance_after` | DECIMAL(15,2) | NOT NULL | Wallet balance after transaction |
| `description` | TEXT | NOT NULL | Human-readable transaction description |
| `reference_type` | VARCHAR(100) | NULL | Polymorphic reference type (e.g., 'App\Models\Order') |
| `reference_id` | BIGINT UNSIGNED | NULL | Polymorphic reference ID |
| `metadata` | JSON | NULL | Additional transaction metadata |
| `created_by` | BIGINT UNSIGNED | FOREIGN KEY (users.id), NULL | User who initiated the transaction (for admin operations) |
| `created_at` | TIMESTAMP | NULL | Transaction creation timestamp |
| `updated_at` | TIMESTAMP | NULL | Last update timestamp |

**Transaction Types:**

- `signup_bonus` - Bonus credited on user registration
- `top_up` - Wallet top-up via payment gateway
- `order_payment` - Payment for an order
- `refund` - Refund from cancelled order
- `withdrawal` - Approved withdrawal
- `withdrawal_reversal` - Rejected withdrawal refund
- `admin_credit` - Manual credit by admin
- `admin_debit` - Manual debit by admin

**Indexes:**

- `PRIMARY KEY (id)` - Primary key index
- `INDEX idx_wallet_created (wallet_id, created_at)` - Optimizes transaction history queries
- `INDEX idx_type (type)` - Optimizes filtering by transaction type
- `INDEX idx_reference (reference_type, reference_id)` - Optimizes polymorphic reference lookups
- `FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE CASCADE` - Maintains referential integrity
- `FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL` - Maintains referential integrity

**Constraints:**

- All transactions are immutable (no updates after creation)
- When a wallet is deleted, all its transactions are deleted (CASCADE)
- When the creator user is deleted, the `created_by` field is set to NULL (SET NULL)
- Balance calculations: `balance_after = balance_before + amount`

---

### 3. `wallet_withdrawals`

Manages withdrawal requests from sellers and delivery boys.

**Columns:**

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique withdrawal identifier |
| `wallet_id` | BIGINT UNSIGNED | FOREIGN KEY (wallets.id), NOT NULL | Reference to the wallet |
| `amount` | DECIMAL(15,2) | NOT NULL | Withdrawal amount |
| `bank_name` | VARCHAR(255) | NOT NULL | Name of the bank |
| `account_number` | VARCHAR(50) | NOT NULL | Bank account number |
| `account_holder_name` | VARCHAR(255) | NOT NULL | Name of the account holder |
| `ifsc_code` | VARCHAR(20) | NULL | IFSC code (for Indian banks) |
| `status` | VARCHAR(20) | NOT NULL, DEFAULT 'pending' | Withdrawal status (see statuses below) |
| `notes` | TEXT | NULL | Admin notes or rejection reason |
| `processed_by` | BIGINT UNSIGNED | FOREIGN KEY (users.id), NULL | Admin who processed the withdrawal |
| `processed_at` | TIMESTAMP | NULL | Processing timestamp |
| `created_at` | TIMESTAMP | NULL | Request creation timestamp |
| `updated_at` | TIMESTAMP | NULL | Last update timestamp |

**Withdrawal Statuses:**

- `pending` - Awaiting admin approval
- `approved` - Approved and processed
- `rejected` - Rejected by admin

**Indexes:**

- `PRIMARY KEY (id)` - Primary key index
- `INDEX idx_status (status)` - Optimizes filtering by status
- `INDEX idx_wallet_status (wallet_id, status)` - Optimizes wallet-specific status queries
- `FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE CASCADE` - Maintains referential integrity
- `FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL` - Maintains referential integrity

**Constraints:**

- Amount is deducted from wallet immediately when request is created
- When a wallet is deleted, all its withdrawal requests are deleted (CASCADE)
- When the processor user is deleted, the `processed_by` field is set to NULL (SET NULL)
- Status transitions: `pending` → `approved` or `pending` → `rejected`

---

## Related Tables

### `orders` Table Modifications

The `orders` table has been extended with wallet payment support:

**Added Columns:**

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `wallet_amount` | DECIMAL(15,2) | NULL, DEFAULT 0.00 | Amount paid from wallet |

This column tracks the wallet portion of order payments, enabling partial payments (wallet + gateway).

---

### `settings` Table Entries

Wallet system configuration is stored in the `settings` table:

**Wallet Settings:**

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `signup_bonus_enabled` | boolean | false | Enable/disable signup bonus |
| `signup_bonus_amount` | number | 0 | Signup bonus amount |
| `min_topup_amount` | number | 10 | Minimum top-up amount |
| `max_topup_amount` | number | 10000 | Maximum top-up amount |
| `min_withdrawal_amount` | number | 50 | Minimum withdrawal amount |
| `withdrawal_processing_days` | number | 3 | Expected withdrawal processing time |
| `max_transaction_amount` | number | 100000 | Maximum single transaction amount |
| `allow_negative_balance` | boolean | false | Allow negative balances |
| `send_transaction_notifications` | boolean | true | Send transaction notifications |
| `send_withdrawal_notifications` | boolean | true | Send withdrawal notifications |

---

## Data Integrity

### Foreign Key Relationships

```
users (1) ──────────── (1) wallets
  │                         │
  │                         │
  └─────────────────────────┴─── (N) wallet_transactions
  │                         │
  │                         │
  └─────────────────────────┴─── (N) wallet_withdrawals

orders (1) ──────────── (N) wallet_transactions (polymorphic)
```

### Cascade Rules

- **User deletion**: Cascades to `wallets`, which cascades to `wallet_transactions` and `wallet_withdrawals`
- **Wallet deletion**: Cascades to `wallet_transactions` and `wallet_withdrawals`
- **Admin user deletion**: Sets `created_by` and `processed_by` to NULL

### Transaction Safety

All wallet operations use database transactions with row-level locking:

```php
DB::transaction(function () use ($wallet) {
    $wallet->lockForUpdate()->first();
    // Perform wallet operations
});
```

This ensures:
- **Atomicity**: All operations complete or none do
- **Consistency**: Balance calculations are always correct
- **Isolation**: Concurrent operations don't interfere
- **Durability**: Committed transactions are permanent

---

## Performance Considerations

### Optimized Queries

The indexes are designed to optimize common query patterns:

1. **User wallet lookup**: `unique_user_wallet` index on `user_id`
2. **Balance range queries**: `idx_balance` index for admin filtering
3. **Transaction history**: `idx_wallet_created` composite index for pagination
4. **Transaction filtering**: `idx_type` index for type-based filtering
5. **Reference lookups**: `idx_reference` composite index for polymorphic relations
6. **Withdrawal management**: `idx_status` and `idx_wallet_status` for admin panel

### Query Examples

```sql
-- Get user's wallet with balance
SELECT * FROM wallets WHERE user_id = ? LIMIT 1;

-- Get transaction history (paginated, newest first)
SELECT * FROM wallet_transactions 
WHERE wallet_id = ? 
ORDER BY created_at DESC 
LIMIT 20 OFFSET 0;

-- Get pending withdrawals
SELECT * FROM wallet_withdrawals 
WHERE status = 'pending' 
ORDER BY created_at ASC;

-- Get transactions by type
SELECT * FROM wallet_transactions 
WHERE wallet_id = ? AND type = 'order_payment' 
ORDER BY created_at DESC;
```

---

## Migration Files

The wallet system schema is implemented across three migration files:

1. **`2026_01_23_000000_create_wallet_tables.php`**
   - Creates `wallets`, `wallet_transactions`, and `wallet_withdrawals` tables
   - Defines all indexes and foreign key constraints

2. **`2026_01_23_000001_add_wallet_settings.php`**
   - Seeds default wallet settings in the `settings` table

3. **`2026_01_23_000002_add_wallet_payment_to_orders.php`**
   - Adds `wallet_amount` column to `orders` table

---

## Security Considerations

### Data Protection

- **Balance integrity**: Enforced through database transactions and row locking
- **Audit trail**: All transactions are immutable and permanently recorded
- **User isolation**: Foreign key constraints prevent cross-user data access
- **Soft deletes**: Creator/processor references use SET NULL to preserve audit trail

### Access Control

- **API authentication**: All wallet endpoints require authentication
- **Role-based access**: Withdrawals restricted to sellers and delivery boys
- **Admin operations**: Manual credit/debit requires admin role
- **Rate limiting**: API endpoints have rate limiting to prevent abuse

---

## Backup and Recovery

### Critical Data

The wallet system contains financial data that requires special backup considerations:

- **Daily backups**: Recommended for all wallet tables
- **Transaction logs**: Never delete transaction records
- **Point-in-time recovery**: Enable for production databases
- **Audit compliance**: Retain transaction history per regulatory requirements

### Recovery Procedures

In case of data corruption:

1. Restore from latest backup
2. Verify balance integrity: `balance = SUM(transactions.amount)`
3. Reconcile with payment gateway records
4. Notify affected users of any discrepancies

---

## Monitoring and Maintenance

### Health Checks

Regular monitoring should include:

- **Balance consistency**: Verify `balance = SUM(transactions.amount)` for all wallets
- **Orphaned records**: Check for transactions without valid wallet references
- **Pending withdrawals**: Monitor age of pending withdrawal requests
- **Transaction volume**: Track daily transaction counts and amounts

### Maintenance Tasks

- **Archive old transactions**: Consider archiving transactions older than 1 year
- **Index optimization**: Rebuild indexes periodically for performance
- **Statistics update**: Update table statistics for query optimizer
- **Disk space monitoring**: Monitor growth of transaction table

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2026-01-23 | Initial wallet system schema |

---

## References

- Requirements Document: `.kiro/specs/wallet-system/requirements.md`
- Design Document: `.kiro/specs/wallet-system/design.md`
- Implementation Tasks: `.kiro/specs/wallet-system/tasks.md`
