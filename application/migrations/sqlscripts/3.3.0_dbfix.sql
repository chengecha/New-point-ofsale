-- PostgreSQL conversion
-- Add indexes and foreign key constraints

-- Add employee_id index to sales_payments
CREATE INDEX IF NOT EXISTS idx_sales_payments_employee_id ON ospos_sales_payments (employee_id);

-- Add foreign key constraint for employee_id
ALTER TABLE ospos_sales_payments
ADD CONSTRAINT fk_sales_payments_employee 
FOREIGN KEY (employee_id) REFERENCES ospos_employees (person_id);

-- Add sales_tax_code_id index to customers
CREATE INDEX IF NOT EXISTS idx_customers_sales_tax_code_id ON ospos_customers (sales_tax_code_id);

-- Add foreign key constraint for sales_tax_code_id
ALTER TABLE ospos_customers
ADD CONSTRAINT fk_customers_tax_code 
FOREIGN KEY (sales_tax_code_id) REFERENCES ospos_tax_codes (tax_code_id);

-- Add rate_tax_category_id index to tax_rates
CREATE INDEX IF NOT EXISTS idx_tax_rates_rate_tax_category_id ON ospos_tax_rates (rate_tax_category_id);

-- Add foreign key constraint for rate_tax_category_id
ALTER TABLE ospos_tax_rates
ADD CONSTRAINT fk_tax_rates_tax_category 
FOREIGN KEY (rate_tax_category_id) REFERENCES ospos_tax_categories (tax_category_id);

-- Add rate_tax_code_id index to tax_rates
CREATE INDEX IF NOT EXISTS idx_tax_rates_rate_tax_code_id ON ospos_tax_rates (rate_tax_code_id);

-- Add foreign key constraint for rate_tax_code_id
ALTER TABLE ospos_tax_rates
ADD CONSTRAINT fk_tax_rates_tax_code 
FOREIGN KEY (rate_tax_code_id) REFERENCES ospos_tax_codes (tax_code_id);

-- Add rate_jurisdiction_id index to tax_rates
CREATE INDEX IF NOT EXISTS idx_tax_rates_rate_jurisdiction_id ON ospos_tax_rates (rate_jurisdiction_id);

-- Add foreign key constraint for rate_jurisdiction_id
ALTER TABLE ospos_tax_rates
ADD CONSTRAINT fk_tax_rates_jurisdiction 
FOREIGN KEY (rate_jurisdiction_id) REFERENCES ospos_tax_jurisdictions (jurisdiction_id);

-- Add receiving_time index to receivings
CREATE INDEX IF NOT EXISTS idx_receivings_receiving_time ON ospos_receivings (receiving_time);

-- Add payment_time index to sales_payments
CREATE INDEX IF NOT EXISTS idx_sales_payments_payment_time ON ospos_sales_payments (payment_time);

-- Add trans_date index to inventory
CREATE INDEX IF NOT EXISTS idx_inventory_trans_date ON ospos_inventory (trans_date);

-- Add date index to expenses
CREATE INDEX IF NOT EXISTS idx_expenses_date ON ospos_expenses (date);