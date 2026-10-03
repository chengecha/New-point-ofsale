-- PostgreSQL conversion
-- Alter quantity fields to be all decimal

-- Items table
ALTER TABLE ospos_items 
ALTER COLUMN reorder_level TYPE DECIMAL(15,3) USING reorder_level::DECIMAL(15,3),
ALTER COLUMN reorder_level SET DEFAULT 0,
ALTER COLUMN reorder_level SET NOT NULL;

ALTER TABLE ospos_items 
ALTER COLUMN receiving_quantity TYPE DECIMAL(15,3) USING receiving_quantity::DECIMAL(15,3),
ALTER COLUMN receiving_quantity SET DEFAULT 1,
ALTER COLUMN receiving_quantity SET NOT NULL;

-- Item kit items table
ALTER TABLE ospos_item_kit_items 
ALTER COLUMN quantity TYPE DECIMAL(15,3) USING quantity::DECIMAL(15,3),
ALTER COLUMN quantity SET NOT NULL;

-- Item quantities table
ALTER TABLE ospos_item_quantities 
ALTER COLUMN quantity TYPE DECIMAL(15,3) USING quantity::DECIMAL(15,3),
ALTER COLUMN quantity SET DEFAULT 0,
ALTER COLUMN quantity SET NOT NULL;

-- Inventory table
ALTER TABLE ospos_inventory 
ALTER COLUMN trans_inventory TYPE DECIMAL(15,3) USING trans_inventory::DECIMAL(15,3),
ALTER COLUMN trans_inventory SET DEFAULT 0,
ALTER COLUMN trans_inventory SET NOT NULL;

-- Receivings table - drop index, rename column, add index
DROP INDEX IF EXISTS ospos_receivings_invoice_number_idx;

ALTER TABLE ospos_receivings 
RENAME COLUMN invoice_number TO reference;

ALTER TABLE ospos_receivings 
ALTER COLUMN reference DROP NOT NULL,
ALTER COLUMN reference SET DEFAULT NULL;

CREATE INDEX IF NOT EXISTS idx_receivings_reference ON ospos_receivings (reference);

-- Receivings items table
ALTER TABLE ospos_receivings_items 
ALTER COLUMN quantity_purchased TYPE DECIMAL(15,3) USING quantity_purchased::DECIMAL(15,3),
ALTER COLUMN quantity_purchased SET DEFAULT 0,
ALTER COLUMN quantity_purchased SET NOT NULL;

ALTER TABLE ospos_receivings_items 
ALTER COLUMN receiving_quantity TYPE DECIMAL(15,3) USING receiving_quantity::DECIMAL(15,3),
ALTER COLUMN receiving_quantity SET DEFAULT 1,
ALTER COLUMN receiving_quantity SET NOT NULL;

-- Sales items table
ALTER TABLE ospos_sales_items 
ALTER COLUMN quantity_purchased TYPE DECIMAL(15,3) USING quantity_purchased::DECIMAL(15,3),
ALTER COLUMN quantity_purchased SET DEFAULT 0,
ALTER COLUMN quantity_purchased SET NOT NULL;

-- Sales suspended items table
ALTER TABLE ospos_sales_suspended_items 
ALTER COLUMN quantity_purchased TYPE DECIMAL(15,3) USING quantity_purchased::DECIMAL(15,3),
ALTER COLUMN quantity_purchased SET DEFAULT 0,
ALTER COLUMN quantity_purchased SET NOT NULL;

-- Sales items taxes table
ALTER TABLE ospos_sales_items_taxes 
ALTER COLUMN percent TYPE DECIMAL(15,3) USING percent::DECIMAL(15,3),
ALTER COLUMN percent SET NOT NULL;

-- Sales suspended items taxes table
ALTER TABLE ospos_sales_suspended_items_taxes 
ALTER COLUMN percent TYPE DECIMAL(15,3) USING percent::DECIMAL(15,3),
ALTER COLUMN percent SET NOT NULL;

-- Items taxes table
ALTER TABLE ospos_items_taxes 
ALTER COLUMN percent TYPE DECIMAL(15,3) USING percent::DECIMAL(15,3),
ALTER COLUMN percent SET NOT NULL;

-- Customers table
ALTER TABLE ospos_customers 
ADD COLUMN IF NOT EXISTS discount_percent DECIMAL(15,2) NOT NULL DEFAULT 0;


-- Alter config table
ALTER TABLE ospos_app_config 
ALTER COLUMN key TYPE VARCHAR(50) USING key::VARCHAR(50),
ALTER COLUMN key SET NOT NULL;

ALTER TABLE ospos_app_config 
ALTER COLUMN value TYPE VARCHAR(500) USING value::VARCHAR(500),
ALTER COLUMN value SET NOT NULL;

-- Update config keys
UPDATE ospos_app_config SET key = 'receipt_show_total_discount' WHERE key = 'show_total_discount';

-- Delete config entries
DELETE FROM ospos_app_config WHERE key = 'use_invoice_template';
DELETE FROM ospos_app_config WHERE key = 'language';
DELETE FROM ospos_app_config WHERE key = 'thousands_separator';

-- Insert new config entries
INSERT INTO ospos_app_config (key, value) VALUES
('receipt_show_description', '1'),
('receipt_show_serialnumber', '1'),
('invoice_enable', '1'),
('number_locale', 'en_US'),
('thousands_separator', '1'),
('currency_decimals', '2'),
('tax_decimals', '2'),
('quantity_decimals', '0'),
('country_codes', 'us'),
('notify_horizontal_position', 'right'),
('notify_vertical_position', 'top'),
('payment_options_order', 'cashdebitcredit'),
('protocol', 'mail'),
('mailpath', '/usr/sbin/sendmail'),
('smtp_port', '465'),
('smtp_timeout', '5'),
('smtp_crypto', 'ssl'),
('smtp_host', ''),
('smtp_pass', ''),
('smtp_user', ''),
('receipt_template', 'receipt_default'),
('theme', 'flatly'),
('statistics', '1'),
('language', 'english'),
('language_code', 'en'),
('msg_msg', ''),
('msg_uid', ''),
('msg_src', ''),
('msg_pwd', '')
ON CONFLICT (key) DO NOTHING;


-- Add messages (SMS) module and permissions
UPDATE ospos_modules SET sort = 110 WHERE name_lang_key = 'module_config';

INSERT INTO ospos_modules (name_lang_key, desc_lang_key, sort, module_id) VALUES
('module_messages', 'module_messages_desc', 100, 'messages')
ON CONFLICT (module_id) DO NOTHING;

INSERT INTO ospos_permissions (permission_id, module_id) VALUES
('messages', 'messages')
ON CONFLICT (permission_id) DO NOTHING;

INSERT INTO ospos_grants (permission_id, person_id) VALUES
('messages', 1)
ON CONFLICT (permission_id, person_id) DO NOTHING;


-- Alter sessions table
DROP TABLE IF EXISTS ospos_sessions CASCADE;

CREATE TABLE ospos_sessions (
    id VARCHAR(40) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    timestamp INTEGER NOT NULL DEFAULT 0,
    data BYTEA NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_sessions_timestamp ON ospos_sessions (timestamp);


-- Upgrade employees table
ALTER TABLE ospos_employees 
ADD COLUMN IF NOT EXISTS hash_version INTEGER NOT NULL DEFAULT 2;

UPDATE ospos_employees SET hash_version = 1;