-- PostgreSQL conversion
-- Improve payment tracking

-- Rename existing sales_payments table
ALTER TABLE ospos_sales_payments RENAME TO ospos_sales_payments_backup;

-- Create new sales_payments table
CREATE TABLE IF NOT EXISTS ospos_sales_payments (
    payment_id SERIAL,
    sale_id INTEGER NOT NULL,
    payment_type VARCHAR(40) NOT NULL,
    payment_amount DECIMAL(15,2) NOT NULL,
    payment_user INTEGER NOT NULL DEFAULT 0,
    payment_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reference_code VARCHAR(40) NOT NULL DEFAULT '',
    PRIMARY KEY (payment_id)
);

-- Create index on sale_id and payment_type
CREATE INDEX IF NOT EXISTS idx_sales_payments_sale_payment ON ospos_sales_payments (sale_id, payment_type);

-- Insert data from backup table
INSERT INTO ospos_sales_payments (sale_id, payment_type, payment_amount, payment_user)
SELECT payments.sale_id, payments.payment_type, payments.payment_amount, sales.employee_id
FROM ospos_sales_payments_backup AS payments
INNER JOIN ospos_sales AS sales ON payments.sale_id = sales.sale_id
ORDER BY payments.sale_id, payments.payment_type;

-- Drop backup table
DROP TABLE IF EXISTS ospos_sales_payments_backup CASCADE;

-- Add foreign key constraint
ALTER TABLE ospos_sales_payments
ADD CONSTRAINT fk_sales_payments_sale 
FOREIGN KEY (sale_id) REFERENCES ospos_sales (sale_id);