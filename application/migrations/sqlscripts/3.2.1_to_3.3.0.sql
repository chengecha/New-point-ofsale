-- PostgreSQL conversion
-- Add support for Multi-Package Items
--

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

--
-- Add support for Discount on Sales Fixed
--

INSERT INTO ospos_app_config (key, value) VALUES
('default_sales_discount_type', '0')
ON CONFLICT (key) DO NOTHING;

-- Rename and add columns to item_kits
ALTER TABLE ospos_item_kits 
RENAME COLUMN kit_discount_percent TO kit_discount;

ALTER TABLE ospos_item_kits 
ALTER COLUMN kit_discount TYPE DECIMAL(15,2) USING kit_discount::DECIMAL(15,2),
ALTER COLUMN kit_discount SET DEFAULT 0,
ALTER COLUMN kit_discount SET NOT NULL;

ALTER TABLE ospos_item_kits
ADD COLUMN IF NOT EXISTS kit_discount_type SMALLINT NOT NULL DEFAULT 0;

-- Rename and add columns to customers
ALTER TABLE ospos_customers 
RENAME COLUMN discount_percent TO discount;

ALTER TABLE ospos_customers 
ALTER COLUMN discount TYPE DECIMAL(15,2) USING discount::DECIMAL(15,2),
ALTER COLUMN discount SET DEFAULT 0,
ALTER COLUMN discount SET NOT NULL;

ALTER TABLE ospos_customers
ADD COLUMN IF NOT EXISTS discount_type SMALLINT NOT NULL DEFAULT 0;

-- Rename and add columns to sales_items
ALTER TABLE ospos_sales_items 
RENAME COLUMN discount_percent TO discount;

ALTER TABLE ospos_sales_items 
ALTER COLUMN discount TYPE DECIMAL(15,2) USING discount::DECIMAL(15,2),
ALTER COLUMN discount SET DEFAULT 0,
ALTER COLUMN discount SET NOT NULL;

ALTER TABLE ospos_sales_items
ADD COLUMN IF NOT EXISTS discount_type SMALLINT NOT NULL DEFAULT 0;

-- Rename and add columns to receivings_items
ALTER TABLE ospos_receivings_items 
RENAME COLUMN discount_percent TO discount;

ALTER TABLE ospos_receivings_items 
ALTER COLUMN discount TYPE DECIMAL(15,2) USING discount::DECIMAL(15,2),
ALTER COLUMN discount SET DEFAULT 0,
ALTER COLUMN discount SET NOT NULL;

ALTER TABLE ospos_receivings_items
ADD COLUMN IF NOT EXISTS discount_type SMALLINT NOT NULL DEFAULT 0;

--
-- Add support for module cashups
--

-- Set config module sort number to one of the latest

UPDATE ospos_modules
SET sort = 900
WHERE name_lang_key = 'module_config';

-- Add cashup module

INSERT INTO ospos_modules (name_lang_key, desc_lang_key, sort, module_id) VALUES
('module_cashups', 'module_cashups_desc', 110, 'cashups')
ON CONFLICT (module_id) DO NOTHING;

INSERT INTO ospos_permissions (permission_id, module_id) VALUES
('cashups', 'cashups')
ON CONFLICT (permission_id) DO NOTHING;

INSERT INTO ospos_grants (permission_id, person_id) VALUES
('cashups', 1)
ON CONFLICT (permission_id, person_id) DO NOTHING;

-- Table structure for table ospos_cash_up

CREATE TABLE IF NOT EXISTS ospos_cash_up (
    cashup_id SERIAL,
    open_date TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    close_date TIMESTAMP NULL,
    open_amount_cash DECIMAL(15,2) NOT NULL,
    transfer_amount_cash DECIMAL(15,2) NOT NULL,
    note INTEGER NOT NULL,
    closed_amount_cash DECIMAL(15,2) NOT NULL,
    closed_amount_card DECIMAL(15,2) NOT NULL,
    closed_amount_check DECIMAL(15,2) NOT NULL,
    closed_amount_total DECIMAL(15,2) NOT NULL,
    description VARCHAR(255) NOT NULL,
    open_employee_id INTEGER NOT NULL,
    close_employee_id INTEGER NOT NULL,
    deleted INTEGER NOT NULL DEFAULT 0,
    closed_amount_due DECIMAL(15,2) NOT NULL,
    PRIMARY KEY (cashup_id)
);

CREATE INDEX IF NOT EXISTS idx_cash_up_open_employee_id ON ospos_cash_up (open_employee_id);
CREATE INDEX IF NOT EXISTS idx_cash_up_close_employee_id ON ospos_cash_up (close_employee_id);

ALTER TABLE ospos_cash_up
ADD CONSTRAINT fk_cash_up_open_employee 
FOREIGN KEY (open_employee_id) REFERENCES ospos_employees (person_id);

ALTER TABLE ospos_cash_up
ADD CONSTRAINT fk_cash_up_close_employee 
FOREIGN KEY (close_employee_id) REFERENCES ospos_employees (person_id);

--
-- Add Suppliers category
--

ALTER TABLE ospos_suppliers
ADD COLUMN IF NOT EXISTS category SMALLINT NOT NULL DEFAULT 0;

UPDATE ospos_suppliers
SET category = 0;

--
-- Link Expenses with Suppliers
--

-- Add supplier id
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