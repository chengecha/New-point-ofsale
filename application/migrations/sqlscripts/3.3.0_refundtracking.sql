-- PostgreSQL conversion
-- Modify sales_payments table

-- Step 1: Add cash_refund column
ALTER TABLE ospos_sales_payments
ADD COLUMN IF NOT EXISTS cash_refund DECIMAL(15,2) NOT NULL DEFAULT 0;

-- Step 2: Drop NOT NULL constraint BEFORE renaming (if payment_user had it)
ALTER TABLE ospos_sales_payments
ALTER COLUMN payment_user DROP NOT NULL;

-- Step 3: Rename column to employee_id
ALTER TABLE ospos_sales_payments
RENAME COLUMN payment_user TO employee_id;

-- Step 4: Ensure employee_id is nullable (it should be after step 2)
-- This is now redundant but kept for safety
ALTER TABLE ospos_sales_payments
ALTER COLUMN employee_id DROP NOT NULL;

-- Step 5: Rename payment_date to payment_time
ALTER TABLE ospos_sales_payments
RENAME COLUMN payment_date TO payment_time;

-- Step 6: Set default for payment_time (if needed)
ALTER TABLE ospos_sales_payments
ALTER COLUMN payment_time SET DEFAULT CURRENT_TIMESTAMP;