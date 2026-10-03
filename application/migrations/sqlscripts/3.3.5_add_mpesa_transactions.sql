-- PostgreSQL conversion
-- Add M-Pesa transactions table with support for offline, STK push, and verification

CREATE TABLE IF NOT EXISTS ospos_mpesa_transactions (
    id SERIAL,
    checkout_request_id VARCHAR(255) NOT NULL,
    merchant_request_id VARCHAR(255) NOT NULL DEFAULT '0',
    amount DECIMAL(15,2) NOT NULL,
    transaction_id VARCHAR(255) NOT NULL DEFAULT '',
    receipt_no VARCHAR(255) NOT NULL DEFAULT '',
    manual_code VARCHAR(255) NOT NULL DEFAULT '',
    phone_number VARCHAR(20) NOT NULL DEFAULT '',
    sale_id INTEGER,
    success SMALLINT NOT NULL DEFAULT 0,
    status SMALLINT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Ensure sale_id column exists (handles case where table was created without it)
ALTER TABLE ospos_mpesa_transactions ADD COLUMN IF NOT EXISTS sale_id INTEGER;

CREATE UNIQUE INDEX IF NOT EXISTS idx_mpesa_transactions_checkout
    ON ospos_mpesa_transactions (checkout_request_id);

CREATE INDEX IF NOT EXISTS idx_mpesa_transactions_sale_id
    ON ospos_mpesa_transactions (sale_id);

CREATE INDEX IF NOT EXISTS idx_mpesa_transactions_manual_code
    ON ospos_mpesa_transactions (manual_code);

ALTER TABLE ospos_mpesa_transactions
ADD CONSTRAINT fk_mpesa_transactions_sale
FOREIGN KEY (sale_id) REFERENCES ospos_sales (sale_id);
