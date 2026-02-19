-- ============================================
-- FIX: Drop strict CHECK constraints from live DB
-- Run this ONCE against your Neon PostgreSQL
-- ============================================

-- 1. Drop CHECK constraint on orders.status
ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_status_check;

-- 2. Drop CHECK constraint on orders.payment_mode
ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_payment_mode_check;

-- 3. Drop CHECK constraint on plants.category
ALTER TABLE plants DROP CONSTRAINT IF EXISTS plants_category_check;

-- 4. Drop CHECK constraint on inventory_log.type
ALTER TABLE inventory_log DROP CONSTRAINT IF EXISTS inventory_log_type_check;

-- 5. Widen payment_mode column to accept longer strings
ALTER TABLE orders ALTER COLUMN payment_mode TYPE VARCHAR(50);

-- Verify constraints are gone
SELECT conname, conrelid::regclass, pg_get_constraintdef(oid)
FROM pg_constraint
WHERE conrelid IN ('orders'::regclass, 'plants'::regclass, 'inventory_log'::regclass)
AND contype = 'c';
