-- PostgreSQL conversion
-- Add support for office menu group
--

ALTER TABLE ospos_grants
ADD COLUMN IF NOT EXISTS menu_group VARCHAR(32) DEFAULT 'home';

INSERT INTO ospos_modules (name_lang_key, desc_lang_key, sort, module_id) VALUES
('module_office', 'module_office_desc', 1, 'office'),
('module_home', 'module_home_desc', 1, 'home')
ON CONFLICT (module_id) DO NOTHING;

INSERT INTO ospos_permissions (permission_id, module_id) VALUES
('office', 'office'),
('home', 'home')
ON CONFLICT (permission_id) DO NOTHING;

INSERT INTO ospos_grants (permission_id, person_id, menu_group) VALUES
('office', 1, 'home'),
('home', 1, 'office')
ON CONFLICT (permission_id, person_id) DO NOTHING;

UPDATE ospos_grants
SET menu_group = 'office'
WHERE permission_id IN ('config', 'home', 'employees', 'taxes', 'migrate')
AND person_id = 1;

--
-- Add support for Work Orders
--

INSERT INTO ospos_app_config (key, value) VALUES
('work_order_enable', '0'),
('work_order_format', 'W%y{WSEQ:6}'),
('last_used_work_order_number', '0')
ON CONFLICT (key) DO NOTHING;

ALTER TABLE ospos_sales
ADD COLUMN IF NOT EXISTS work_order_number VARCHAR(32) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS sale_type SMALLINT NOT NULL DEFAULT 0;

-- sale_type (0=pos, 1=invoice, 2=work order, 3=quote, 4=return)

UPDATE ospos_sales
SET sale_type = 0;

UPDATE ospos_sales t1
SET sale_type = 4
WHERE EXISTS (SELECT t2.sale_id FROM ospos_sales_items t2 WHERE t1.sale_id = t2.sale_id AND t2.quantity < 0);

UPDATE ospos_sales
SET sale_type = 3
WHERE quote_number IS NOT NULL;

-- The following is needed only if quotes were being treated as work orders.
-- UPDATE ospos_sales
--   SET sale_type = 2, work_order_number = quote_number
-- WHERE quote_number IS NOT NULL;

-- Identify invoices
UPDATE ospos_sales
SET sale_type = 1
WHERE invoice_number IS NOT NULL;

-- Add permissions for deleting sales and default grant for employee id 1

INSERT INTO ospos_permissions (permission_id, module_id) VALUES
('sales_delete', 'sales')
ON CONFLICT (permission_id) DO NOTHING;

INSERT INTO ospos_grants (permission_id, person_id, menu_group) VALUES
('sales_delete', 1, '--')
ON CONFLICT (permission_id, person_id) DO NOTHING;

-- Add columns to save per-user language selection

ALTER TABLE ospos_employees 
ADD COLUMN IF NOT EXISTS language VARCHAR(48) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS language_code VARCHAR(8) DEFAULT NULL;

-- Add support for custom search suggestion format

INSERT INTO ospos_app_config (key, value) VALUES
('suggestions_first_column', 'name'),
('suggestions_second_column', ''),
('suggestions_third_column', '')
ON CONFLICT (key) DO NOTHING;

-- Add key->value to save setting for allowing duplicate barcodes

INSERT INTO ospos_app_config (key, value) VALUES
('allow_duplicate_barcodes', '0')
ON CONFLICT (key) DO NOTHING;

-- Modify items table to allow duplicate barcodes

DROP INDEX IF EXISTS ospos_items_item_number;

CREATE INDEX IF NOT EXISTS idx_items_item_number ON ospos_items (item_number);

-- Remove Migrate module as auto migration is supported

DELETE FROM ospos_modules WHERE module_id = 'migrate';
DELETE FROM ospos_permissions WHERE permission_id = 'migrate';
DELETE FROM ospos_grants WHERE permission_id = 'migrate' AND person_id = 1;

-- Move Office Module to Right side of Modules list

UPDATE ospos_modules
SET sort = 999
WHERE name_lang_key = 'module_office';

UPDATE ospos_modules
SET sort = 98
WHERE name_lang_key = 'module_messages';

--
-- Add support for expenses tracking
--

INSERT INTO ospos_modules (name_lang_key, desc_lang_key, sort, module_id) VALUES
('module_expenses', 'module_expenses_desc', 108, 'expenses'),
('module_expenses_categories', 'module_expenses_categories_desc', 109, 'expenses_categories')
ON CONFLICT (module_id) DO NOTHING;

INSERT INTO ospos_permissions (permission_id, module_id) VALUES
('expenses_categories', 'expenses_categories'),
('expenses', 'expenses'),
('reports_expenses_categories', 'reports')
ON CONFLICT (permission_id) DO NOTHING;

INSERT INTO ospos_grants (permission_id, person_id) VALUES 
('expenses', 1),
('expenses_categories', 1),
('reports_expenses_categories', 1)
ON CONFLICT (permission_id, person_id) DO NOTHING;

-- Table structure for table ospos_expense_categories

CREATE TABLE IF NOT EXISTS ospos_expense_categories (
    expense_category_id SERIAL,
    category_name VARCHAR(255) DEFAULT NULL,
    category_description VARCHAR(255) NOT NULL,
    deleted INTEGER NOT NULL DEFAULT 0,
    CONSTRAINT ospos_expense_categories_category_name UNIQUE (category_name),
    PRIMARY KEY (expense_category_id)
);

-- Table structure for table ospos_expenses

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

-- Indexes for table ospos_expenses

CREATE INDEX IF NOT EXISTS idx_expenses_expense_category_id ON ospos_expenses (expense_category_id);
CREATE INDEX IF NOT EXISTS idx_expenses_employee_id ON ospos_expenses (employee_id);

-- Constraints for table ospos_expenses

ALTER TABLE ospos_expenses
ADD CONSTRAINT fk_expenses_expense_category 
FOREIGN KEY (expense_category_id) REFERENCES ospos_expense_categories (expense_category_id);

ALTER TABLE ospos_expenses
ADD CONSTRAINT fk_expenses_employee 
FOREIGN KEY (employee_id) REFERENCES ospos_employees (person_id);

-- Remove unused barcode quality

DELETE FROM ospos_app_config WHERE key = 'barcode_quality';

-- Add new config option to allow derive sale quantity feature

INSERT INTO ospos_app_config (key, value) VALUES
('derive_sale_quantity', '0')
ON CONFLICT (key) DO NOTHING;

-- Add new config option to set print and email receipt behaviour

INSERT INTO ospos_app_config (key, value) VALUES
('email_receipt_check_behaviour', 'last'),
('print_receipt_check_behaviour', 'last')
ON CONFLICT (key) DO NOTHING;

-- This is to provide distinct default comments for both quotes and invoices

INSERT INTO ospos_app_config (key, value) VALUES
('quote_default_comments', 'This is a default quote comment')
ON CONFLICT (key) DO NOTHING;