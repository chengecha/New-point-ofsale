-- PostgreSQL supplementary schema for Docker fresh install
-- Adds tables introduced in 3.1.x - 3.4.0 migrations that are not in 01-tables.sql

-- Add deleted column to tax_codes (added in 3.3.0 migration)
ALTER TABLE ospos_tax_codes
ADD COLUMN IF NOT EXISTS deleted INTEGER NOT NULL DEFAULT 0;

-- Create tax_jurisdictions table (from 3.3.0_indiagst.sql migration)
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

-- Create tax_rates table (from 3.3.0_indiagst.sql migration)
CREATE TABLE IF NOT EXISTS ospos_tax_rates (
    tax_rate_id SERIAL,
    rate_tax_code_id INTEGER NOT NULL,
    rate_tax_category_id INTEGER NOT NULL,
    rate_jurisdiction_id INTEGER NOT NULL,
    tax_rate DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
    tax_rounding_code SMALLINT NOT NULL DEFAULT 0,
    PRIMARY KEY (tax_rate_id)
);

-- Add tax_id column to customers (from 3.3.0 migration)
ALTER TABLE ospos_customers
ADD COLUMN IF NOT EXISTS tax_id VARCHAR(32) NOT NULL DEFAULT '',
ADD COLUMN IF NOT EXISTS sales_tax_code_id INTEGER DEFAULT NULL;

-- Add columns to customers (from 3.2.0_to_3.2.1 migration - GDPR utility)
ALTER TABLE ospos_customers
ADD COLUMN IF NOT EXISTS date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS employee_id INTEGER NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS consent INTEGER NOT NULL DEFAULT 0;

-- Add hsn_code to items (from 3.3.0 migration)
ALTER TABLE ospos_items
ADD COLUMN IF NOT EXISTS hsn_code VARCHAR(32) NOT NULL DEFAULT '';

-- Add tax_category_id to items (from 3.0.2_to_3.1.1 migration)
ALTER TABLE ospos_items
ADD COLUMN IF NOT EXISTS tax_category_id INTEGER DEFAULT NULL;

-- Add jurisdiction/tax columns to sales_items_taxes (from 3.3.0 migration)
ALTER TABLE ospos_sales_items_taxes
ADD COLUMN IF NOT EXISTS sales_tax_code_id INTEGER DEFAULT NULL,
ADD COLUMN IF NOT EXISTS jurisdiction_id INTEGER DEFAULT NULL,
ADD COLUMN IF NOT EXISTS tax_category_id INTEGER DEFAULT NULL;

-- Restructure sales_taxes table (from 3.3.0_indiagst.sql migration)
-- The migration renames the old table and creates a new one with
-- sales_taxes_id, jurisdiction_id, tax_category_id, sales_tax_code_id
-- For a fresh install we modify the existing table.
ALTER TABLE ospos_sales_taxes DROP CONSTRAINT IF EXISTS ospos_sales_taxes_pkey;
ALTER TABLE ospos_sales_taxes ADD COLUMN IF NOT EXISTS sales_taxes_id SERIAL;
ALTER TABLE ospos_sales_taxes ADD COLUMN IF NOT EXISTS jurisdiction_id INTEGER DEFAULT NULL;
ALTER TABLE ospos_sales_taxes ADD COLUMN IF NOT EXISTS tax_category_id INTEGER DEFAULT NULL;
ALTER TABLE ospos_sales_taxes ADD COLUMN IF NOT EXISTS sales_tax_code_id INTEGER DEFAULT NULL;
ALTER TABLE ospos_sales_taxes ADD PRIMARY KEY (sales_taxes_id);
DROP INDEX IF EXISTS ospos_sales_taxes_print_sequence;
CREATE INDEX IF NOT EXISTS idx_sales_taxes_print_sequence ON ospos_sales_taxes (sale_id, print_sequence, tax_group);

-- Add tax_id to suppliers (from 3.3.0 migration: change from int in 01-tables
-- to VARCHAR to match the migration script 3.3.0_indiagst.sql)
ALTER TABLE ospos_suppliers
ALTER COLUMN tax_id TYPE VARCHAR(32);

-- Set tax_id NOT NULL and DEFAULT (from 3.3.0_indiagst1.sql migration)
UPDATE ospos_suppliers SET tax_id = '0' WHERE tax_id IS NULL;
ALTER TABLE ospos_suppliers ALTER COLUMN tax_id SET NOT NULL;
ALTER TABLE ospos_suppliers ALTER COLUMN tax_id SET DEFAULT '0';

-- Add sales tax report permissions (from 3.3.0 migration)
INSERT INTO ospos_permissions (permission_id, module_id) VALUES
('reports_sales_taxes', 'reports')
ON CONFLICT (permission_id) DO NOTHING;

INSERT INTO ospos_grants (permission_id, person_id, menu_group) VALUES
('reports_sales_taxes', 1, 'home')
ON CONFLICT (permission_id, person_id) DO NOTHING;

-- Add deleted column to tax_categories (required by 3.4.0 migration)
ALTER TABLE ospos_tax_categories
ADD COLUMN IF NOT EXISTS deleted INTEGER NOT NULL DEFAULT 0;

-- Fix sessions table data column from bytea to text for PostgreSQL
-- CI3 session driver base64-encodes data for PostgreSQL, which requires text column
ALTER TABLE ospos_sessions
    ALTER COLUMN data TYPE text;

-- Rename quantity to quantity_purchased in sales_items to match application code
ALTER TABLE ospos_sales_items
    RENAME COLUMN quantity TO quantity_purchased;

-- Tables added in 3.1.1 -> 3.2.0 migration: expense_categories and expenses
CREATE TABLE IF NOT EXISTS ospos_expense_categories (
    expense_category_id SERIAL,
    category_name VARCHAR(255) DEFAULT NULL,
    category_description VARCHAR(255) NOT NULL,
    deleted INTEGER NOT NULL DEFAULT 0,
    CONSTRAINT ospos_expense_categories_category_name UNIQUE (category_name),
    PRIMARY KEY (expense_category_id)
);

CREATE TABLE IF NOT EXISTS ospos_expenses (
    expense_id SERIAL,
    date TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    amount DECIMAL(15,2) NOT NULL,
    payment_type VARCHAR(40) NOT NULL,
    expense_category_id INTEGER NOT NULL,
    description VARCHAR(255) NOT NULL,
    employee_id INTEGER NOT NULL,
    deleted INTEGER NOT NULL DEFAULT 0,
    supplier_name VARCHAR(255) DEFAULT NULL,
    supplier_tax_code VARCHAR(255) DEFAULT NULL,
    tax_amount DECIMAL(15,2) DEFAULT NULL,
    PRIMARY KEY (expense_id)
);

CREATE INDEX IF NOT EXISTS idx_expenses_expense_category_id ON ospos_expenses (expense_category_id);
CREATE INDEX IF NOT EXISTS idx_expenses_employee_id ON ospos_expenses (employee_id);

ALTER TABLE ospos_expenses
ADD CONSTRAINT fk_expenses_expense_category
FOREIGN KEY (expense_category_id) REFERENCES ospos_expense_categories (expense_category_id);

ALTER TABLE ospos_expenses
ADD CONSTRAINT fk_expenses_employee
FOREIGN KEY (employee_id) REFERENCES ospos_employees (person_id);

-- Table added in 3.2.1 -> 3.3.0 migration: cash_up
CREATE TABLE IF NOT EXISTS ospos_cash_up (
    cashup_id SERIAL,
    open_date TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    close_date TIMESTAMP NULL,
    open_amount_cash DECIMAL(15,2) NOT NULL,
    transfer_amount_cash DECIMAL(15,2) NOT NULL,
    note INTEGER NOT NULL DEFAULT 0,
    closed_amount_cash DECIMAL(15,2) NOT NULL,
    closed_amount_card DECIMAL(15,2) NOT NULL,
    closed_amount_check DECIMAL(15,2) NOT NULL,
    closed_amount_total DECIMAL(15,2) NOT NULL,
    description VARCHAR(255) NOT NULL,
    open_employee_id INTEGER NOT NULL,
    close_employee_id INTEGER,
    deleted INTEGER NOT NULL DEFAULT 0,
    closed_amount_due DECIMAL(15,2) NOT NULL,
    closed_amount_mpesa DECIMAL(15,2) NOT NULL DEFAULT 0,
    expected_cash DECIMAL(15,2) NOT NULL DEFAULT 0,
    cash_in_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    cash_in_type VARCHAR(32) NOT NULL DEFAULT 'cash',
    cash_out_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    cash_out_type VARCHAR(32) NOT NULL DEFAULT 'cash',
    total_trx_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    total_expense DECIMAL(15,2) NOT NULL DEFAULT 0,
    actual_cash_counted DECIMAL(15,2) NOT NULL DEFAULT 0,
    discrepancy_variance DECIMAL(15,2) NOT NULL DEFAULT 0,
    PRIMARY KEY (cashup_id)
);

CREATE INDEX IF NOT EXISTS idx_cash_up_open_employee_id ON ospos_cash_up (open_employee_id);
CREATE INDEX IF NOT EXISTS idx_cash_up_close_employee_id ON ospos_cash_up (close_employee_id);

-- Tables added in 3.3.0 migration: attribute_definitions, attribute_values, attribute_links
CREATE TABLE IF NOT EXISTS ospos_attribute_definitions (
    definition_id SERIAL,
    definition_name VARCHAR(255) NOT NULL,
    definition_type VARCHAR(45) NOT NULL,
    definition_flags SMALLINT NOT NULL,
    definition_fk INTEGER NULL,
    deleted SMALLINT NOT NULL DEFAULT 0,
    PRIMARY KEY (definition_id)
);

CREATE INDEX IF NOT EXISTS idx_attribute_definitions_definition_fk ON ospos_attribute_definitions (definition_fk);

CREATE TABLE IF NOT EXISTS ospos_attribute_values (
    attribute_id SERIAL,
    attribute_value VARCHAR(255) UNIQUE NULL,
    attribute_datetime TIMESTAMP NULL,
    PRIMARY KEY (attribute_id)
);

CREATE TABLE IF NOT EXISTS ospos_attribute_links (
    attribute_id INTEGER NULL,
    definition_id INTEGER NOT NULL,
    item_id INTEGER NULL,
    sale_id INTEGER NULL,
    receiving_id INTEGER NULL
);

CREATE INDEX IF NOT EXISTS idx_attribute_links_attribute_id ON ospos_attribute_links (attribute_id);
CREATE INDEX IF NOT EXISTS idx_attribute_links_definition_id ON ospos_attribute_links (definition_id);
CREATE INDEX IF NOT EXISTS idx_attribute_links_item_id ON ospos_attribute_links (item_id);
CREATE INDEX IF NOT EXISTS idx_attribute_links_sale_id ON ospos_attribute_links (sale_id);
CREATE INDEX IF NOT EXISTS idx_attribute_links_receiving_id ON ospos_attribute_links (receiving_id);

CREATE UNIQUE INDEX IF NOT EXISTS idx_attribute_links_uq1
ON ospos_attribute_links (attribute_id, definition_id, COALESCE(item_id, -1), COALESCE(sale_id, -1), COALESCE(receiving_id, -1));

-- Add foreign key constraints for attribute tables
ALTER TABLE ospos_attribute_definitions
ADD CONSTRAINT fk_attribute_definitions_definition_fk
FOREIGN KEY (definition_fk) REFERENCES ospos_attribute_definitions (definition_id);

ALTER TABLE ospos_attribute_links
ADD CONSTRAINT fk_attribute_links_definition
FOREIGN KEY (definition_id) REFERENCES ospos_attribute_definitions (definition_id) ON DELETE CASCADE;

ALTER TABLE ospos_attribute_links
ADD CONSTRAINT fk_attribute_links_attribute
FOREIGN KEY (attribute_id) REFERENCES ospos_attribute_values (attribute_id) ON DELETE CASCADE;

ALTER TABLE ospos_attribute_links
ADD CONSTRAINT fk_attribute_links_item
FOREIGN KEY (item_id) REFERENCES ospos_items (item_id);

ALTER TABLE ospos_attribute_links
ADD CONSTRAINT fk_attribute_links_receiving
FOREIGN KEY (receiving_id) REFERENCES ospos_receivings (receiving_id);

ALTER TABLE ospos_attribute_links
ADD CONSTRAINT fk_attribute_links_sale
FOREIGN KEY (sale_id) REFERENCES ospos_sales (sale_id);

-- Add module and permissions for attributes (from 3.3.0 migration)
INSERT INTO ospos_modules (name_lang_key, desc_lang_key, sort, module_id) VALUES
('module_attributes', 'module_attributes_desc', 107, 'attributes')
ON CONFLICT (module_id) DO NOTHING;

INSERT INTO ospos_permissions (permission_id, module_id) VALUES
('attributes', 'attributes')
ON CONFLICT (permission_id) DO NOTHING;

INSERT INTO ospos_grants (permission_id, person_id, menu_group) VALUES
('attributes', 1, 'home')
ON CONFLICT (permission_id, person_id) DO NOTHING;

-- Add modules for expenses (from 3.1.1 -> 3.2.0 migration)
INSERT INTO ospos_modules (name_lang_key, desc_lang_key, sort, module_id) VALUES
('module_expenses', 'module_expenses_desc', 108, 'expenses'),
('module_expenses_categories', 'module_expenses_categories_desc', 109, 'expenses_categories')
ON CONFLICT (module_id) DO NOTHING;

INSERT INTO ospos_permissions (permission_id, module_id) VALUES
('expenses_categories', 'expenses_categories'),
('expenses', 'expenses'),
('reports_expenses_categories', 'reports')
ON CONFLICT (permission_id) DO NOTHING;

INSERT INTO ospos_grants (permission_id, person_id, menu_group) VALUES
('expenses', 1, 'home'),
('expenses_categories', 1, 'home'),
('reports_expenses_categories', 1, 'home')
ON CONFLICT (permission_id, person_id) DO NOTHING;

-- Add work_order config and columns (from 3.1.1 -> 3.2.0 migration)
INSERT INTO ospos_app_config (key, value) VALUES
('work_order_enable', '0'),
('work_order_format', 'W%y{WSEQ:6}'),
('last_used_work_order_number', '0')
ON CONFLICT (key) DO NOTHING;

ALTER TABLE ospos_sales
ADD COLUMN IF NOT EXISTS work_order_number VARCHAR(32) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS sale_type SMALLINT NOT NULL DEFAULT 0;

UPDATE ospos_sales SET sale_type = 0 WHERE sale_type IS NULL;

INSERT INTO ospos_permissions (permission_id, module_id) VALUES
('sales_delete', 'sales')
ON CONFLICT (permission_id) DO NOTHING;

INSERT INTO ospos_grants (permission_id, person_id, menu_group) VALUES
('sales_delete', 1, '--')
ON CONFLICT (permission_id, person_id) DO NOTHING;

-- Add language columns to employees (from 3.1.1 -> 3.2.0 migration)
ALTER TABLE ospos_employees
ADD COLUMN IF NOT EXISTS language VARCHAR(48) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS language_code VARCHAR(8) DEFAULT NULL;

-- Add custom search config options (from 3.1.1 -> 3.2.0 migration)
INSERT INTO ospos_app_config (key, value) VALUES
('suggestions_first_column', 'name'),
('suggestions_second_column', ''),
('suggestions_third_column', '')
ON CONFLICT (key) DO NOTHING;

INSERT INTO ospos_app_config (key, value) VALUES
('allow_duplicate_barcodes', '0')
ON CONFLICT (key) DO NOTHING;

-- Derive sale quantity config (from 3.1.1 -> 3.2.0 migration)
INSERT INTO ospos_app_config (key, value) VALUES
('derive_sale_quantity', '0')
ON CONFLICT (key) DO NOTHING;

-- Receipt behaviour config (from 3.1.1 -> 3.2.0 migration)
INSERT INTO ospos_app_config (key, value) VALUES
('email_receipt_check_behaviour', 'last'),
('print_receipt_check_behaviour', 'last')
ON CONFLICT (key) DO NOTHING;

-- Quote default comments (from 3.1.1 -> 3.2.0 migration)
INSERT INTO ospos_app_config (key, value) VALUES
('quote_default_comments', 'This is a default quote comment')
ON CONFLICT (key) DO NOTHING;

-- Add attribute_values date/decimal columns (from 3.4.0 migration)
ALTER TABLE ospos_attribute_values
ADD COLUMN IF NOT EXISTS attribute_date DATE NULL,
ADD COLUMN IF NOT EXISTS attribute_decimal DECIMAL(15,2) NULL;

-- Add definition_unit column to attribute_definitions (from 3.3.0 migration)
ALTER TABLE ospos_attribute_definitions
ADD COLUMN IF NOT EXISTS definition_unit VARCHAR(16) DEFAULT NULL;

-- ============================================================
-- Changes from 3.2.1_to_3.3.0.sql migration
-- ============================================================

-- Add support for Multi-Pack Items
INSERT INTO ospos_app_config (key, value) VALUES
('multi_pack_enabled', '0')
ON CONFLICT (key) DO NOTHING;

ALTER TABLE ospos_items
ADD COLUMN IF NOT EXISTS qty_per_pack DECIMAL(15,3) NOT NULL DEFAULT 1,
ADD COLUMN IF NOT EXISTS pack_name VARCHAR(8) DEFAULT 'Each',
ADD COLUMN IF NOT EXISTS low_sell_item_id INTEGER DEFAULT 0;

UPDATE ospos_items
SET low_sell_item_id = item_id
WHERE low_sell_item_id = 0;

-- Add support for Discount on Sales Fixed
INSERT INTO ospos_app_config (key, value) VALUES
('default_sales_discount_type', '0')
ON CONFLICT (key) DO NOTHING;

-- Rename and add columns to item_kits
DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.columns
               WHERE table_name='ospos_item_kits' AND column_name='kit_discount_percent') THEN
        ALTER TABLE ospos_item_kits RENAME COLUMN kit_discount_percent TO kit_discount;
    END IF;
END $$;

ALTER TABLE ospos_item_kits
ALTER COLUMN kit_discount TYPE DECIMAL(15,2) USING kit_discount::DECIMAL(15,2),
ALTER COLUMN kit_discount SET DEFAULT 0,
ALTER COLUMN kit_discount SET NOT NULL;

ALTER TABLE ospos_item_kits
ADD COLUMN IF NOT EXISTS kit_discount_type SMALLINT NOT NULL DEFAULT 0;

-- Add item_kit_number to item_kits (from 3.3.3 migration)
ALTER TABLE ospos_item_kits
ADD COLUMN IF NOT EXISTS item_kit_number VARCHAR(255) DEFAULT NULL;
CREATE INDEX IF NOT EXISTS idx_item_kits_item_kit_number ON ospos_item_kits (item_kit_number);

-- Rename and add columns to customers
DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.columns
               WHERE table_name='ospos_customers' AND column_name='discount_percent') THEN
        ALTER TABLE ospos_customers RENAME COLUMN discount_percent TO discount;
    END IF;
END $$;

ALTER TABLE ospos_customers
ALTER COLUMN discount TYPE DECIMAL(15,2) USING discount::DECIMAL(15,2),
ALTER COLUMN discount SET DEFAULT 0,
ALTER COLUMN discount SET NOT NULL;

ALTER TABLE ospos_customers
ADD COLUMN IF NOT EXISTS discount_type SMALLINT NOT NULL DEFAULT 0;

-- Rename and add columns to sales_items
DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.columns
               WHERE table_name='ospos_sales_items' AND column_name='discount_percent') THEN
        ALTER TABLE ospos_sales_items RENAME COLUMN discount_percent TO discount;
    END IF;
END $$;

ALTER TABLE ospos_sales_items
ALTER COLUMN discount TYPE DECIMAL(15,2) USING discount::DECIMAL(15,2),
ALTER COLUMN discount SET DEFAULT 0,
ALTER COLUMN discount SET NOT NULL;

ALTER TABLE ospos_sales_items
ADD COLUMN IF NOT EXISTS discount_type SMALLINT NOT NULL DEFAULT 0;

-- Rename and add columns to receivings_items
DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.columns
               WHERE table_name='ospos_receivings_items' AND column_name='discount_percent') THEN
        ALTER TABLE ospos_receivings_items RENAME COLUMN discount_percent TO discount;
    END IF;
END $$;

ALTER TABLE ospos_receivings_items
ALTER COLUMN discount TYPE DECIMAL(15,2) USING discount::DECIMAL(15,2),
ALTER COLUMN discount SET DEFAULT 0,
ALTER COLUMN discount SET NOT NULL;

ALTER TABLE ospos_receivings_items
ADD COLUMN IF NOT EXISTS discount_type SMALLINT NOT NULL DEFAULT 0;

-- Add cashup module
UPDATE ospos_modules
SET sort = 900
WHERE name_lang_key = 'module_config';

INSERT INTO ospos_modules (name_lang_key, desc_lang_key, sort, module_id) VALUES
('module_cashups', 'module_cashups_desc', 110, 'cashups')
ON CONFLICT (module_id) DO NOTHING;

INSERT INTO ospos_permissions (permission_id, module_id) VALUES
('cashups', 'cashups')
ON CONFLICT (permission_id) DO NOTHING;

INSERT INTO ospos_grants (permission_id, person_id) VALUES
('cashups', 1)
ON CONFLICT (permission_id, person_id) DO NOTHING;

-- Add Suppliers category
ALTER TABLE ospos_suppliers
ADD COLUMN IF NOT EXISTS category SMALLINT NOT NULL DEFAULT 0;

UPDATE ospos_suppliers
SET category = 0;

-- Link Expenses with Suppliers
ALTER TABLE ospos_expenses
ADD COLUMN IF NOT EXISTS supplier_id INTEGER NULL;

-- Link suppliers
UPDATE ospos_expenses
SET supplier_id = ospos_suppliers.person_id
FROM ospos_suppliers
WHERE ospos_expenses.supplier_name = ospos_suppliers.company_name;

-- Save name in description for those expenses whose supplier isn't registered
UPDATE ospos_expenses
SET description = description || E'\nSupplier name: ' || supplier_name
WHERE supplier_id IS NULL;

-- Add foreign key
ALTER TABLE ospos_expenses
ADD CONSTRAINT fk_expenses_supplier
FOREIGN KEY (supplier_id) REFERENCES ospos_suppliers (person_id);

-- Delete supplier name
ALTER TABLE ospos_expenses
DROP COLUMN IF EXISTS supplier_name;

INSERT INTO ospos_app_config (key, value) VALUES
('default_receivings_discount_type', '0'),
('default_receivings_discount', '0')
ON CONFLICT (key) DO NOTHING;

-- ============================================================
-- Database optimizations (from 3.4.0 migration)
-- 3.4.0 migration adds PRIMARY KEYs that replace the unique indexes
-- from 01-tables.sql, and adds supporting indexes for query performance.
-- ============================================================

-- ospos_customers table
-- Drop FK constraints that depend on the unique index before dropping it
ALTER TABLE ospos_sales DROP CONSTRAINT IF EXISTS ospos_sales_ibfk_2;
ALTER TABLE ospos_customers_points DROP CONSTRAINT IF EXISTS ospos_customers_points_ibfk_1;
DROP INDEX IF EXISTS ospos_customers_person_id;
CREATE INDEX IF NOT EXISTS idx_customers_company_name ON ospos_customers (company_name);
ALTER TABLE ospos_customers ADD PRIMARY KEY (person_id);
-- Recreate dropped FK constraints now that the PK exists
ALTER TABLE ospos_sales ADD CONSTRAINT ospos_sales_ibfk_2 FOREIGN KEY (customer_id) REFERENCES ospos_customers (person_id);
ALTER TABLE ospos_customers_points ADD CONSTRAINT ospos_customers_points_ibfk_1 FOREIGN KEY (person_id) REFERENCES ospos_customers (person_id);

-- ospos_employees table
-- Drop FK constraints that depend on the unique index before dropping it
ALTER TABLE ospos_inventory DROP CONSTRAINT IF EXISTS ospos_inventory_ibfk_2;
ALTER TABLE ospos_grants DROP CONSTRAINT IF EXISTS ospos_grants_ibfk_2;
ALTER TABLE ospos_receivings DROP CONSTRAINT IF EXISTS ospos_receivings_ibfk_1;
ALTER TABLE ospos_sales DROP CONSTRAINT IF EXISTS ospos_sales_ibfk_1;
ALTER TABLE ospos_expenses DROP CONSTRAINT IF EXISTS fk_expenses_employee;
ALTER TABLE ospos_sales_payments DROP CONSTRAINT IF EXISTS fk_sales_payments_employee;
DROP INDEX IF EXISTS ospos_employees_person_id;
ALTER TABLE ospos_employees ADD PRIMARY KEY (person_id);
-- Recreate dropped FK constraints now that the PK exists
ALTER TABLE ospos_inventory ADD CONSTRAINT ospos_inventory_ibfk_2 FOREIGN KEY (trans_user) REFERENCES ospos_employees (person_id);
ALTER TABLE ospos_grants ADD CONSTRAINT ospos_grants_ibfk_2 FOREIGN KEY (person_id) REFERENCES ospos_employees (person_id) ON DELETE CASCADE;
ALTER TABLE ospos_receivings ADD CONSTRAINT ospos_receivings_ibfk_1 FOREIGN KEY (employee_id) REFERENCES ospos_employees (person_id);
ALTER TABLE ospos_sales ADD CONSTRAINT ospos_sales_ibfk_1 FOREIGN KEY (employee_id) REFERENCES ospos_employees (person_id);
ALTER TABLE ospos_expenses ADD CONSTRAINT fk_expenses_employee FOREIGN KEY (employee_id) REFERENCES ospos_employees (person_id);

-- ospos_suppliers table
-- Drop FK constraints that depend on the unique index before dropping it
ALTER TABLE ospos_items DROP CONSTRAINT IF EXISTS ospos_items_ibfk_1;
ALTER TABLE ospos_receivings DROP CONSTRAINT IF EXISTS ospos_receivings_ibfk_2;
ALTER TABLE ospos_expenses DROP CONSTRAINT IF EXISTS fk_expenses_supplier;
DROP INDEX IF EXISTS ospos_suppliers_person_id;
ALTER TABLE ospos_suppliers ADD PRIMARY KEY (person_id);
-- Recreate dropped FK constraints now that the PK exists
ALTER TABLE ospos_items ADD CONSTRAINT ospos_items_ibfk_1 FOREIGN KEY (supplier_id) REFERENCES ospos_suppliers (person_id);
ALTER TABLE ospos_receivings ADD CONSTRAINT ospos_receivings_ibfk_2 FOREIGN KEY (supplier_id) REFERENCES ospos_suppliers (person_id);
ALTER TABLE ospos_expenses ADD CONSTRAINT fk_expenses_supplier FOREIGN KEY (supplier_id) REFERENCES ospos_suppliers (person_id);
CREATE INDEX IF NOT EXISTS idx_suppliers_category ON ospos_suppliers (category);
CREATE INDEX IF NOT EXISTS idx_suppliers_company_name_deleted ON ospos_suppliers (company_name, deleted);

-- ospos_tax_categories table (from 3.4.0 migration)
ALTER TABLE ospos_tax_categories ALTER COLUMN deleted TYPE SMALLINT USING deleted::SMALLINT;
ALTER TABLE ospos_tax_categories ALTER COLUMN deleted SET DEFAULT 0;
ALTER TABLE ospos_tax_categories ALTER COLUMN deleted SET NOT NULL;
ALTER TABLE ospos_tax_categories ALTER COLUMN tax_group_sequence SET NOT NULL;

-- ============================================================
-- sales_payments table restructuring (from 3.3.0_paymenttracking +
-- 3.3.0_refundtracking + 20201108100000_cashrounding migrations)
-- Migration recreates the table with: payment_id, employee_id,
-- payment_time, reference_code, cash_refund, and cash_adjustment.
-- For a fresh install we add columns and restructure the primary key.
-- ============================================================

-- Drop old primary key (sale_id, payment_type) from 01-tables.sql
ALTER TABLE ospos_sales_payments DROP CONSTRAINT IF EXISTS ospos_sales_payments_pkey;

-- Add missing columns
ALTER TABLE ospos_sales_payments
ADD COLUMN IF NOT EXISTS payment_id SERIAL,
ADD COLUMN IF NOT EXISTS employee_id INTEGER DEFAULT 0,
ADD COLUMN IF NOT EXISTS payment_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS reference_code VARCHAR(40) NOT NULL DEFAULT '',
ADD COLUMN IF NOT EXISTS cash_refund DECIMAL(15,2) NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS cash_adjustment SMALLINT NOT NULL DEFAULT 0;

-- New primary key on payment_id (matching migration)
ALTER TABLE ospos_sales_payments ADD PRIMARY KEY (payment_id);

-- Index and FK for employee_id (from 3.3.0_dbfix migration)
CREATE INDEX IF NOT EXISTS idx_sales_payments_employee_id ON ospos_sales_payments (employee_id);
ALTER TABLE ospos_sales_payments
ADD CONSTRAINT fk_sales_payments_employee
FOREIGN KEY (employee_id) REFERENCES ospos_employees (person_id);

-- Replace old sale_id-only index with composite one from migration
DROP INDEX IF EXISTS ospos_sales_payments_sale_id;
CREATE INDEX IF NOT EXISTS idx_sales_payments_sale_payment ON ospos_sales_payments (sale_id, payment_type);

-- ============================================================
-- Set migration version to latest so app doesn't run migrations on login
-- ============================================================
-- CI3 uses dbprefix 'ospos_' (from application/config/database.php), so the
-- migration tracking table is 'ospos_migrations', not 'migrations'.
CREATE TABLE IF NOT EXISTS ospos_migrations (
    version bigint NOT NULL,
    PRIMARY KEY (version)
);

-- Set migration version to latest
INSERT INTO ospos_migrations (version) VALUES
(20210922000000)
ON CONFLICT (version) DO NOTHING;
