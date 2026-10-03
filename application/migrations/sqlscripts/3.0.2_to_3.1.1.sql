-- PostgreSQL conversion
-- Alter quantity fields to be all decimal

-- Add columns to items table
ALTER TABLE ospos_items 
ADD COLUMN IF NOT EXISTS stock_type SMALLINT NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS item_type SMALLINT NOT NULL DEFAULT 0;

-- Add columns to item_kits table
ALTER TABLE ospos_item_kits 
ADD COLUMN IF NOT EXISTS item_id INTEGER NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS kit_discount_percent DECIMAL(15,2) NOT NULL DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS price_option SMALLINT NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS print_option SMALLINT NOT NULL DEFAULT 0;

-- Add kit_sequence to item_kit_items
ALTER TABLE ospos_item_kit_items 
ADD COLUMN IF NOT EXISTS kit_sequence INTEGER NOT NULL DEFAULT 0;

-- Add print_option to sales_items
ALTER TABLE ospos_sales_items 
ADD COLUMN IF NOT EXISTS print_option SMALLINT NOT NULL DEFAULT 0;

-- Add quote_number to sales_suspended
ALTER TABLE ospos_sales_suspended 
ADD COLUMN IF NOT EXISTS quote_number VARCHAR(32) DEFAULT NULL;

-- Add print_option to sales_suspended_items
ALTER TABLE ospos_sales_suspended_items 
ADD COLUMN IF NOT EXISTS print_option SMALLINT NOT NULL DEFAULT 0;

-- Rename pic_id to pic_filename
-- ALTER TABLE ospos_items 
-- RENAME COLUMN pic_id TO pic_filename;

-- Create dinner_tables table
CREATE TABLE IF NOT EXISTS ospos_dinner_tables (
    dinner_table_id SERIAL,
    name VARCHAR(30) NOT NULL,
    status SMALLINT NOT NULL DEFAULT 0,
    deleted INTEGER NOT NULL DEFAULT 0,
    PRIMARY KEY (dinner_table_id)
);

INSERT INTO ospos_dinner_tables (name, status, deleted) VALUES
('Delivery', 0, 0),
('Take Away', 0, 0)
ON CONFLICT (dinner_table_id) DO NOTHING;

-- Add dinner_table_id to sales table
ALTER TABLE ospos_sales 
ADD COLUMN IF NOT EXISTS dinner_table_id INTEGER DEFAULT NULL;

CREATE INDEX IF NOT EXISTS idx_sales_dinner_table_id ON ospos_sales (dinner_table_id);

ALTER TABLE ospos_sales 
ADD CONSTRAINT fk_sales_dinner_table 
FOREIGN KEY (dinner_table_id) REFERENCES ospos_dinner_tables (dinner_table_id);

-- Add dinner_table_id to sales_suspended table
ALTER TABLE ospos_sales_suspended 
ADD COLUMN IF NOT EXISTS dinner_table_id INTEGER DEFAULT NULL;

CREATE INDEX IF NOT EXISTS idx_sales_suspended_dinner_table_id ON ospos_sales_suspended (dinner_table_id);

ALTER TABLE ospos_sales_suspended 
ADD CONSTRAINT fk_sales_suspended_dinner_table 
FOREIGN KEY (dinner_table_id) REFERENCES ospos_dinner_tables (dinner_table_id);

-- Insert config values
INSERT INTO ospos_app_config (key, value) VALUES
('date_or_time_format', ''),
('sales_quote_format', 'Q%y{QSEQ:6}'),
('default_register_mode', 'sale'),
('last_used_invoice_number', '0'),
('last_used_quote_number', '0'),
('line_sequence', '0'),
('dinner_table_enable', '0'),
('customer_sales_tax_support', '0')
ON CONFLICT (key) DO NOTHING;

-- Create customers_packages table
CREATE TABLE IF NOT EXISTS ospos_customers_packages (
    package_id SERIAL,
    package_name VARCHAR(255) DEFAULT NULL,
    points_percent REAL NOT NULL DEFAULT 0,
    deleted INTEGER NOT NULL DEFAULT 0,
    PRIMARY KEY (package_id)
);

INSERT INTO ospos_customers_packages (package_name, points_percent, deleted) VALUES
('Default', 0, 0),
('Bronze', 10, 0),
('Silver', 20, 0),
('Gold', 30, 0),
('Premium', 50, 0)
ON CONFLICT (package_id) DO NOTHING;

-- Create customers_points table
CREATE TABLE IF NOT EXISTS ospos_customers_points (
    id SERIAL,
    person_id INTEGER NOT NULL,
    package_id INTEGER NOT NULL,
    sale_id INTEGER NOT NULL,
    points_earned INTEGER NOT NULL,
    PRIMARY KEY (id)
);

CREATE INDEX IF NOT EXISTS idx_customers_points_person_id ON ospos_customers_points (person_id);
CREATE INDEX IF NOT EXISTS idx_customers_points_package_id ON ospos_customers_points (package_id);
CREATE INDEX IF NOT EXISTS idx_customers_points_sale_id ON ospos_customers_points (sale_id);

-- Create sales_reward_points table
CREATE TABLE IF NOT EXISTS ospos_sales_reward_points (
    id SERIAL,
    sale_id INTEGER NOT NULL,
    earned REAL NOT NULL,
    used REAL NOT NULL,
    PRIMARY KEY (id)
);

CREATE INDEX IF NOT EXISTS idx_sales_reward_points_sale_id ON ospos_sales_reward_points (sale_id);

-- Add columns to customers table
ALTER TABLE ospos_customers 
ADD COLUMN IF NOT EXISTS package_id INTEGER DEFAULT NULL,
ADD COLUMN IF NOT EXISTS points INTEGER DEFAULT NULL;

-- Add customer_reward_enable config
INSERT INTO ospos_app_config (key, value) VALUES
('customer_reward_enable', '0')
ON CONFLICT (key) DO NOTHING;

-- Create tax tables
CREATE TABLE IF NOT EXISTS ospos_tax_codes (
    tax_code VARCHAR(32) NOT NULL,
    tax_code_name VARCHAR(255) NOT NULL DEFAULT '',
    tax_code_type SMALLINT NOT NULL DEFAULT 0,
    city VARCHAR(255) NOT NULL DEFAULT '',
    state VARCHAR(255) NOT NULL DEFAULT '',
    PRIMARY KEY (tax_code)
);

CREATE TABLE IF NOT EXISTS ospos_tax_code_rates (
    rate_tax_code VARCHAR(32) NOT NULL,
    rate_tax_category_id INTEGER NOT NULL,
    tax_rate DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
    rounding_code SMALLINT NOT NULL DEFAULT 0,
    PRIMARY KEY (rate_tax_code, rate_tax_category_id)
);

CREATE TABLE IF NOT EXISTS ospos_sales_taxes (
    sale_id INTEGER NOT NULL,
    tax_type SMALLINT NOT NULL,
    tax_group VARCHAR(32) NOT NULL,
    sale_tax_basis DECIMAL(15,4) NOT NULL,
    sale_tax_amount DECIMAL(15,4) NOT NULL,
    print_sequence SMALLINT NOT NULL DEFAULT 0,
    name VARCHAR(255) NOT NULL,
    tax_rate DECIMAL(15,4) NOT NULL,
    sales_tax_code VARCHAR(32) NOT NULL DEFAULT '',
    rounding_code SMALLINT NOT NULL DEFAULT 0,
    PRIMARY KEY (sale_id, tax_type, tax_group)
);

CREATE INDEX IF NOT EXISTS idx_sales_taxes_print_sequence ON ospos_sales_taxes (sale_id, print_sequence, tax_type, tax_group);

CREATE TABLE IF NOT EXISTS ospos_tax_categories (
    tax_category_id SERIAL,
    tax_category VARCHAR(32) NOT NULL,
    tax_group_sequence SMALLINT NOT NULL,
    PRIMARY KEY (tax_category_id)
);

-- Add tax_category_id to items
ALTER TABLE ospos_items 
ADD COLUMN IF NOT EXISTS tax_category_id INTEGER DEFAULT NULL;

-- Add quote_number and sale_status to sales
ALTER TABLE ospos_sales 
ADD COLUMN IF NOT EXISTS quote_number VARCHAR(32) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS sale_status SMALLINT NOT NULL DEFAULT 0;

-- Modify sales_items_taxes table
ALTER TABLE ospos_sales_items_taxes 
ALTER COLUMN percent TYPE DECIMAL(15,4) USING percent::DECIMAL(15,4);

ALTER TABLE ospos_sales_items_taxes 
ALTER COLUMN percent SET DEFAULT 0.0000;

ALTER TABLE ospos_sales_items_taxes 
ADD COLUMN IF NOT EXISTS tax_type SMALLINT NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS rounding_code SMALLINT NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS cascade_tax SMALLINT NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS cascade_sequence SMALLINT NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS item_tax_amount DECIMAL(15,4) NOT NULL DEFAULT 0;

-- Add sales_tax_code to customers
ALTER TABLE ospos_customers 
ADD COLUMN IF NOT EXISTS sales_tax_code VARCHAR(32) NOT NULL DEFAULT '1';

-- Insert config values
INSERT INTO ospos_app_config (key, value) VALUES
('customer_sales_tax_support', '0'),
('default_origin_tax_code', ''),
('default_tax_category', 'Standard'),
('default_tax_1_name', ''),
('default_tax_1_rate', ''),
('default_tax_2_name', ''),
('default_tax_2_rate', '')
ON CONFLICT (key) DO NOTHING;

INSERT INTO ospos_modules (name_lang_key, desc_lang_key, sort, module_id) VALUES
('module_taxes', 'module_taxes_desc', 105, 'taxes')
ON CONFLICT (module_id) DO NOTHING;

INSERT INTO ospos_permissions (permission_id, module_id) VALUES
('taxes', 'taxes')
ON CONFLICT (permission_id) DO NOTHING;

-- Add cash rounding config
INSERT INTO ospos_app_config (key, value) VALUES
('cash_decimals', '2'),
('cash_rounding_code', '0')
ON CONFLICT (key) DO NOTHING;

-- Add email index to people
CREATE INDEX IF NOT EXISTS idx_people_email ON ospos_people (email);

-- Add financial_year config
INSERT INTO ospos_app_config (key, value) VALUES
('financial_year', '1')
ON CONFLICT (key) DO NOTHING;

-- Modify giftcard_number to allow NULL
ALTER TABLE ospos_giftcards 
ALTER COLUMN giftcard_number DROP NOT NULL;

-- Add giftcard_number config
INSERT INTO ospos_app_config (key, value) VALUES
('giftcard_number', 'series')
ON CONFLICT (key) DO NOTHING;

-- Add receipt_show_company_name config
INSERT INTO ospos_app_config (key, value) VALUES
('receipt_show_company_name', '1')
ON CONFLICT (key) DO NOTHING;

-- Add migrate module
INSERT INTO ospos_modules (name_lang_key, desc_lang_key, sort, module_id) VALUES
('module_migrate', 'module_migrate_desc', 120, 'migrate')
ON CONFLICT (module_id) DO NOTHING;

INSERT INTO ospos_permissions (permission_id, module_id) VALUES
('migrate', 'migrate')
ON CONFLICT (permission_id) DO NOTHING;

INSERT INTO ospos_grants (permission_id, person_id) VALUES
('migrate', 1)
ON CONFLICT (permission_id, person_id) DO NOTHING;

-- Update receiving_quantity
UPDATE ospos_items SET receiving_quantity = 1 WHERE receiving_quantity = 0;

-- Modify sales_items description
ALTER TABLE ospos_sales_items 
ALTER COLUMN description TYPE VARCHAR(255) USING description::VARCHAR(255);

-- Fix tax categories
DELETE FROM ospos_tax_categories WHERE tax_category_id IN (0, 1, 2, 3);

-- Update tax_code_rates
UPDATE ospos_tax_code_rates SET rate_tax_category_id = 4 WHERE rate_tax_category_id = 3;
UPDATE ospos_tax_code_rates SET rate_tax_category_id = 3 WHERE rate_tax_category_id = 2;
UPDATE ospos_tax_code_rates SET rate_tax_category_id = 2 WHERE rate_tax_category_id = 1;
UPDATE ospos_tax_code_rates SET rate_tax_category_id = 1 WHERE rate_tax_category_id = 0;

-- Add receipt_font_size config
INSERT INTO ospos_app_config (key, value) VALUES
('receipt_font_size', '12')
ON CONFLICT (key) DO NOTHING;

-- Add foreign key constraints for rewards
ALTER TABLE ospos_customers_points 
ADD CONSTRAINT fk_customers_points_customer 
FOREIGN KEY (person_id) REFERENCES ospos_customers (person_id);

ALTER TABLE ospos_customers_points 
ADD CONSTRAINT fk_customers_points_package 
FOREIGN KEY (package_id) REFERENCES ospos_customers_packages (package_id);

ALTER TABLE ospos_customers_points 
ADD CONSTRAINT fk_customers_points_sale 
FOREIGN KEY (sale_id) REFERENCES ospos_sales (sale_id);

ALTER TABLE ospos_sales_reward_points 
ADD CONSTRAINT fk_sales_reward_points_sale 
FOREIGN KEY (sale_id) REFERENCES ospos_sales (sale_id);

ALTER TABLE ospos_customers 
ADD CONSTRAINT fk_customers_package 
FOREIGN KEY (package_id) REFERENCES ospos_customers_packages (package_id);

-- Add reCAPTCHA config
INSERT INTO ospos_app_config (key, value) VALUES
('gcaptcha_enable', '0'),
('gcaptcha_secret_key', ''),
('gcaptcha_site_key', '')
ON CONFLICT (key) DO NOTHING;

-- Add barcode_formats config
INSERT INTO ospos_app_config (key, value) VALUES
('barcode_formats', '[]')
ON CONFLICT (key) DO NOTHING;

-- Update tokens in app_config
UPDATE ospos_app_config SET value = REPLACE(value, '$CO', '{CO}');
UPDATE ospos_app_config SET value = REPLACE(value, '$CU', '{CU}');
UPDATE ospos_app_config SET value = REPLACE(value, '$INV', '{ISEQ}');
UPDATE ospos_app_config SET value = REPLACE(value, '$SCO', '{SCO}');

-- Copy suspended sales to sales table
INSERT INTO ospos_sales (sale_time, customer_id, employee_id, comment, invoice_number, sale_status)
SELECT sale_time, customer_id, employee_id, comment, invoice_number, 1 FROM ospos_sales_suspended
ON CONFLICT (sale_id) DO NOTHING;

INSERT INTO ospos_sales_items (sale_id, item_id, description, serialnumber, line, quantity, item_cost_price, item_unit_price,
    discount_percent, item_location) 
SELECT sale_id, item_id, description, serialnumber, line, quantity, item_cost_price, item_unit_price,
    discount_percent, item_location FROM ospos_sales_suspended_items
ON CONFLICT (sale_id, item_id, line) DO NOTHING;

INSERT INTO ospos_sales_payments (sale_id, payment_type, payment_amount) 
SELECT sale_id, payment_type, payment_amount FROM ospos_sales_suspended_payments
ON CONFLICT (sale_id, payment_type) DO NOTHING;

INSERT INTO ospos_sales_items_taxes (sale_id, item_id, line, name, percent) 
SELECT sale_id, item_id, line, name, percent FROM ospos_sales_suspended_items_taxes
ON CONFLICT (sale_id, item_id, line, name, percent) DO NOTHING;

-- Drop foreign keys from suspended tables
ALTER TABLE ospos_sales_suspended_payments DROP CONSTRAINT IF EXISTS ospos_sales_suspended_payments_ibfk_1;
ALTER TABLE ospos_sales_suspended_items_taxes DROP CONSTRAINT IF EXISTS ospos_sales_suspended_items_taxes_ibfk_1;
ALTER TABLE ospos_sales_suspended_items_taxes DROP CONSTRAINT IF EXISTS ospos_sales_suspended_items_taxes_ibfk_2;
ALTER TABLE ospos_sales_suspended_items DROP CONSTRAINT IF EXISTS ospos_sales_suspended_items_ibfk_1;
ALTER TABLE ospos_sales_suspended_items DROP CONSTRAINT IF EXISTS ospos_sales_suspended_items_ibfk_2;
ALTER TABLE ospos_sales_suspended_items DROP CONSTRAINT IF EXISTS ospos_sales_suspended_items_ibfk_3;
ALTER TABLE ospos_sales_suspended DROP CONSTRAINT IF EXISTS ospos_sales_suspended_ibfk_1;
ALTER TABLE ospos_sales_suspended DROP CONSTRAINT IF EXISTS ospos_sales_suspended_ibfk_2;
ALTER TABLE ospos_sales_suspended DROP CONSTRAINT IF EXISTS ospos_sales_suspended_ibfk_3;

-- Drop suspended tables
DROP TABLE IF EXISTS ospos_sales_suspended_payments CASCADE;
DROP TABLE IF EXISTS ospos_sales_suspended_items_taxes CASCADE;
DROP TABLE IF EXISTS ospos_sales_suspended_items CASCADE;
DROP TABLE IF EXISTS ospos_sales_suspended CASCADE;

-- General fixing
DELETE FROM ospos_app_config WHERE key = 'print_after_sale';

-- Modify column types
ALTER TABLE ospos_giftcards ALTER COLUMN value TYPE DECIMAL(15,2) USING value::DECIMAL(15,2);
ALTER TABLE ospos_giftcards ALTER COLUMN value SET NOT NULL;

ALTER TABLE ospos_items ALTER COLUMN cost_price TYPE DECIMAL(15,2) USING cost_price::DECIMAL(15,2);
ALTER TABLE ospos_items ALTER COLUMN cost_price SET NOT NULL;

ALTER TABLE ospos_items ALTER COLUMN unit_price TYPE DECIMAL(15,2) USING unit_price::DECIMAL(15,2);
ALTER TABLE ospos_items ALTER COLUMN unit_price SET NOT NULL;

ALTER TABLE ospos_receivings_items ALTER COLUMN discount_percent TYPE DECIMAL(15,2) USING discount_percent::DECIMAL(15,2);
ALTER TABLE ospos_receivings_items ALTER COLUMN discount_percent SET DEFAULT 0.00;
ALTER TABLE ospos_receivings_items ALTER COLUMN discount_percent SET NOT NULL;

ALTER TABLE ospos_receivings_items ALTER COLUMN item_unit_price TYPE DECIMAL(15,2) USING item_unit_price::DECIMAL(15,2);
ALTER TABLE ospos_receivings_items ALTER COLUMN item_unit_price SET NOT NULL;

ALTER TABLE ospos_sales_items ALTER COLUMN discount_percent TYPE DECIMAL(15,2) USING discount_percent::DECIMAL(15,2);
ALTER TABLE ospos_sales_items ALTER COLUMN discount_percent SET DEFAULT 0.00;
ALTER TABLE ospos_sales_items ALTER COLUMN discount_percent SET NOT NULL;

ALTER TABLE ospos_sales_items ALTER COLUMN item_unit_price TYPE DECIMAL(15,2) USING item_unit_price::DECIMAL(15,2);
ALTER TABLE ospos_sales_items ALTER COLUMN item_unit_price SET NOT NULL;

-- Increase custom fields length
ALTER TABLE ospos_items 
ALTER COLUMN custom1 TYPE VARCHAR(255) USING custom1::VARCHAR(255),
ALTER COLUMN custom1 SET DEFAULT NULL;

ALTER TABLE ospos_items 
ALTER COLUMN custom2 TYPE VARCHAR(255) USING custom2::VARCHAR(255),
ALTER COLUMN custom2 SET DEFAULT NULL;

ALTER TABLE ospos_items 
ALTER COLUMN custom3 TYPE VARCHAR(255) USING custom3::VARCHAR(255),
ALTER COLUMN custom3 SET DEFAULT NULL;

ALTER TABLE ospos_items 
ALTER COLUMN custom4 TYPE VARCHAR(255) USING custom4::VARCHAR(255),
ALTER COLUMN custom4 SET DEFAULT NULL;

ALTER TABLE ospos_items 
ALTER COLUMN custom5 TYPE VARCHAR(255) USING custom5::VARCHAR(255),
ALTER COLUMN custom5 SET DEFAULT NULL;

ALTER TABLE ospos_items 
ALTER COLUMN custom6 TYPE VARCHAR(255) USING custom6::VARCHAR(255),
ALTER COLUMN custom6 SET DEFAULT NULL;

ALTER TABLE ospos_items 
ALTER COLUMN custom7 TYPE VARCHAR(255) USING custom7::VARCHAR(255),
ALTER COLUMN custom7 SET DEFAULT NULL;

ALTER TABLE ospos_items 
ALTER COLUMN custom8 TYPE VARCHAR(255) USING custom8::VARCHAR(255),
ALTER COLUMN custom8 SET DEFAULT NULL;

ALTER TABLE ospos_items 
ALTER COLUMN custom9 TYPE VARCHAR(255) USING custom9::VARCHAR(255),
ALTER COLUMN custom9 SET DEFAULT NULL;

ALTER TABLE ospos_items 
ALTER COLUMN custom10 TYPE VARCHAR(255) USING custom10::VARCHAR(255),
ALTER COLUMN custom10 SET DEFAULT NULL;

-- Update language code
UPDATE ospos_app_config SET value = 'en-US' WHERE key = 'language_code' AND value = 'en';