-- PostgreSQL conversion
-- Start of India GST Tax Changes
-- --------------------------------

-- Insert config values
INSERT INTO ospos_app_config (key, value) VALUES
('include_hsn', '0'),
('invoice_type', 'invoice'),
('default_tax_jurisdiction', ''),
('tax_id', '')
ON CONFLICT (key) DO NOTHING;

-- Update config keys
UPDATE ospos_app_config
SET key = 'use_destination_based_tax'
WHERE key = 'customer_sales_tax_support';

UPDATE ospos_app_config
SET key = 'default_tax_code'
WHERE key = 'default_origin_tax_code';

-- Rename tax_codes table
ALTER TABLE ospos_tax_codes RENAME TO ospos_tax_codes_backup;

-- Create new tax_codes table
CREATE TABLE IF NOT EXISTS ospos_tax_codes (
    tax_code_id SERIAL,
    tax_code VARCHAR(32) NOT NULL,
    tax_code_name VARCHAR(255) NOT NULL DEFAULT '',
    city VARCHAR(255) NOT NULL DEFAULT '',
    state VARCHAR(255) NOT NULL DEFAULT '',
    deleted INTEGER NOT NULL DEFAULT 0,
    PRIMARY KEY (tax_code_id)
);

-- Add columns to customers
ALTER TABLE ospos_customers
ADD COLUMN IF NOT EXISTS tax_id VARCHAR(32) NOT NULL DEFAULT '',
ADD COLUMN IF NOT EXISTS sales_tax_code_id INTEGER DEFAULT NULL;

-- Add hsn_code to items
ALTER TABLE ospos_items
ADD COLUMN IF NOT EXISTS hsn_code VARCHAR(32) NOT NULL DEFAULT '';

-- Modify sales_items_taxes table
ALTER TABLE ospos_sales_items_taxes
ADD COLUMN IF NOT EXISTS sales_tax_code_id INTEGER DEFAULT NULL,
ADD COLUMN IF NOT EXISTS jurisdiction_id INTEGER DEFAULT NULL,
ADD COLUMN IF NOT EXISTS tax_category_id INTEGER DEFAULT NULL;

ALTER TABLE ospos_sales_items_taxes
DROP COLUMN IF EXISTS cascade_tax;

-- Rename sales_taxes table
ALTER TABLE ospos_sales_taxes RENAME TO ospos_sales_taxes_backup;

-- Create new sales_taxes table
CREATE TABLE IF NOT EXISTS ospos_sales_taxes (
    sales_taxes_id SERIAL,
    sale_id INTEGER NOT NULL,
    jurisdiction_id INTEGER DEFAULT NULL,
    tax_category_id INTEGER DEFAULT NULL,
    tax_type SMALLINT NOT NULL,
    tax_group VARCHAR(32) NOT NULL,
    sale_tax_basis DECIMAL(15,4) NOT NULL,
    sale_tax_amount DECIMAL(15,4) NOT NULL,
    print_sequence SMALLINT NOT NULL DEFAULT 0,
    name VARCHAR(255) NOT NULL,
    tax_rate DECIMAL(15,4) NOT NULL,
    sales_tax_code_id INTEGER DEFAULT NULL,
    rounding_code SMALLINT NOT NULL DEFAULT 0,
    PRIMARY KEY (sales_taxes_id)
);

CREATE INDEX IF NOT EXISTS idx_sales_taxes_print_sequence ON ospos_sales_taxes (sale_id, print_sequence, tax_group);

-- Create tax_jurisdictions table
CREATE TABLE IF NOT EXISTS ospos_tax_jurisdictions (
    jurisdiction_id SERIAL,
    jurisdiction_name VARCHAR(255) DEFAULT NULL,
    tax_group VARCHAR(32) NOT NULL,
    tax_type SMALLINT NOT NULL,
    reporting_authority VARCHAR(255) DEFAULT NULL,
    tax_group_sequence SMALLINT NOT NULL DEFAULT 0,
    cascade_sequence SMALLINT NOT NULL DEFAULT 0,
    deleted INTEGER NOT NULL DEFAULT 0,
    PRIMARY KEY (jurisdiction_id)
);

-- Add tax_id to suppliers
ALTER TABLE ospos_suppliers
ADD COLUMN IF NOT EXISTS tax_id VARCHAR(32) DEFAULT NULL;

-- Add deleted column to tax_categories
ALTER TABLE ospos_tax_categories
ADD COLUMN IF NOT EXISTS deleted INTEGER NOT NULL DEFAULT 0;

-- Rename tax_code_rates table
ALTER TABLE ospos_tax_code_rates RENAME TO ospos_tax_code_rates_backup;

-- Create tax_rates table
CREATE TABLE IF NOT EXISTS ospos_tax_rates (
    tax_rate_id SERIAL,
    rate_tax_code_id INTEGER NOT NULL,
    rate_tax_category_id INTEGER NOT NULL,
    rate_jurisdiction_id INTEGER NOT NULL,
    tax_rate DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
    tax_rounding_code SMALLINT NOT NULL DEFAULT 0,
    PRIMARY KEY (tax_rate_id)
);

-- Add sales tax report permissions
INSERT INTO ospos_permissions (permission_id, module_id) VALUES
('reports_sales_taxes', 'reports')
ON CONFLICT (permission_id) DO NOTHING;

INSERT INTO ospos_grants (permission_id, person_id, menu_group) VALUES
('reports_sales_taxes', 1, 'home')
ON CONFLICT (permission_id, person_id) DO NOTHING;