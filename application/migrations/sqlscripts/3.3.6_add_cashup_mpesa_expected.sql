-- Add M-Pesa and Expected cash columns to cash_up table
ALTER TABLE ospos_cash_up ADD COLUMN IF NOT EXISTS closed_amount_mpesa DECIMAL(15,2) NOT NULL DEFAULT 0;
ALTER TABLE ospos_cash_up ADD COLUMN IF NOT EXISTS expected_cash DECIMAL(15,2) NOT NULL DEFAULT 0;
