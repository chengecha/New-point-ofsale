-- PostgreSQL conversion
-- This migration script should be run after creating tables with the regular database script and before applying the constraints.

--
-- Copy data to table `ospos_app_config`
--

DELETE FROM ospos_app_config WHERE key IN ('address', 'company', 'default_tax_1_name', 'default_tax_1_rate', 
    'default_tax_2_name', 'default_tax_2_rate', 'default_tax_rate', 'email', 'fax', 'phone', 'website', 'return_policy');

INSERT INTO ospos_app_config (key, value)
SELECT key, value FROM phppos.phppos_app_config 
WHERE key IN ('address', 'company', 'default_tax_1_name', 'default_tax_1_rate', 
    'default_tax_2_name', 'default_tax_2_rate', 'default_tax_rate', 'email', 'fax', 'phone', 'website', 'return_policy')
ON CONFLICT (key) DO NOTHING;

--
-- Copy data to table `ospos_customers`
--

INSERT INTO ospos_customers (person_id, account_number, taxable, deleted)
SELECT person_id, account_number, taxable, deleted FROM phppos.phppos_customers
ON CONFLICT (person_id) DO NOTHING;

-- Handle duplicate account numbers (PostgreSQL version)
UPDATE ospos_customers c1 
SET account_number = NULL 
FROM ospos_customers c2 
WHERE c1.person_id > c2.person_id 
AND c1.account_number = c2.account_number;

--
-- Copy data to table `ospos_employees`
--

INSERT INTO ospos_employees (username, password, person_id, deleted, hash_version)
SELECT username, password, person_id, deleted, 1 FROM phppos.phppos_employees
ON CONFLICT (person_id) DO NOTHING;

--
-- Copy data to table `ospos_giftcards`
--

INSERT INTO ospos_giftcards (giftcard_id, giftcard_number, value, deleted, person_id)
SELECT giftcard_id, giftcard_number, value, deleted, person_id FROM phppos.phppos_giftcards
ON CONFLICT (giftcard_id) DO NOTHING;

--
-- Copy data to table `ospos_inventory`
--

INSERT INTO ospos_inventory (trans_id, trans_items, trans_user, trans_date, trans_comment, trans_location, trans_inventory)
SELECT trans_id, trans_items, trans_user, trans_date, trans_comment, 1, trans_inventory FROM phppos.phppos_inventory
ON CONFLICT (trans_id) DO NOTHING;

--
-- Copy data to table `ospos_items`
--

INSERT INTO ospos_items (name, category, supplier_id, item_number, description, cost_price, unit_price, 
    reorder_level, receiving_quantity, item_id, pic_id, allow_alt_description, is_serialized, deleted, 
    custom1, custom2, custom3, custom4, custom5, custom6, custom7, custom8, custom9, custom10)
SELECT name, category, supplier_id, item_number, description, cost_price, unit_price, 
    reorder_level, 1, item_id, NULL, allow_alt_description, is_serialized, deleted, 
    0, 0, 0, 0, 0, 0, 0, 0, 0, 0 
FROM phppos.phppos_items
ON CONFLICT (item_id) DO NOTHING;

--
-- Copy data to table `ospos_items_taxes`
--

INSERT INTO ospos_items_taxes (item_id, name, percent)
SELECT item_id, name, percent FROM phppos.phppos_items_taxes
ON CONFLICT (item_id, name) DO NOTHING;

--
-- Copy data to table `ospos_item_kits`
--

INSERT INTO ospos_item_kits (item_kit_id, name, description)
SELECT item_kit_id, name, description FROM phppos.phppos_item_kits
ON CONFLICT (item_kit_id) DO NOTHING;

--
-- Copy data to table `ospos_item_kit_items`
--

INSERT INTO ospos_item_kit_items (item_kit_id, item_id, quantity)
SELECT item_kit_id, item_id, quantity FROM phppos.phppos_item_kit_items
ON CONFLICT (item_kit_id, item_id) DO NOTHING;

--
-- Copy data to table `ospos_people`
--

INSERT INTO ospos_people (first_name, last_name, phone_number, email, address_1, address_2, city, state, zip, country, comments, person_id)
SELECT first_name, last_name, phone_number, email, address_1, address_2, city, state, zip, country, comments, person_id 
FROM phppos.phppos_people
ON CONFLICT (person_id) DO NOTHING;

--
-- Copy data to table `ospos_receivings`
--

INSERT INTO ospos_receivings (receiving_time, supplier_id, employee_id, comment, receiving_id, payment_type, reference)
SELECT receiving_time, supplier_id, employee_id, comment, receiving_id, payment_type, NULL 
FROM phppos.phppos_receivings
ON CONFLICT (receiving_id) DO NOTHING;

--
-- Copy data to table `ospos_receivings_items`
--

INSERT INTO ospos_receivings_items (receiving_id, item_id, description, serialnumber, line, quantity_purchased, 
    item_cost_price, item_unit_price, discount_percent, item_location)
SELECT receiving_id, item_id, description, serialnumber, line, quantity_purchased, 
    item_cost_price, item_unit_price, discount_percent, 1 
FROM phppos.phppos_receivings_items
ON CONFLICT (receiving_id, item_id, line) DO NOTHING;

--
-- Copy data to table `ospos_sales`
--

INSERT INTO ospos_sales (sale_time, customer_id, employee_id, comment, sale_id, invoice_number)
SELECT sale_time, customer_id, employee_id, comment, sale_id, NULL 
FROM phppos.phppos_sales
ON CONFLICT (sale_id) DO NOTHING;

--
-- Copy data to table `ospos_sales_items`
--

INSERT INTO ospos_sales_items (sale_id, item_id, description, serialnumber, line, quantity_purchased, 
    item_cost_price, item_unit_price, discount_percent, item_location)
SELECT sale_id, item_id, description, serialnumber, line, quantity_purchased, 
    item_cost_price, item_unit_price, discount_percent, 1 
FROM phppos.phppos_sales_items
ON CONFLICT (sale_id, item_id, line) DO NOTHING;

--
-- Copy data to table `ospos_sales_items_taxes`
--

INSERT INTO ospos_sales_items_taxes (sale_id, item_id, line, name, percent)
SELECT sale_id, item_id, line, name, percent FROM phppos.phppos_sales_items_taxes
ON CONFLICT (sale_id, item_id, line, name) DO NOTHING;

--
-- Copy data to table `ospos_sales_payments`
--

INSERT INTO ospos_sales_payments (sale_id, payment_type, payment_amount)
SELECT sale_id, payment_type, payment_amount FROM phppos.phppos_sales_payments
ON CONFLICT (sale_id, payment_type) DO NOTHING;

--
-- Copy data to table `ospos_item_quantities`
--

INSERT INTO ospos_item_quantities (item_id, location_id, quantity)
SELECT item_id, 1, quantity FROM phppos.phppos_items
ON CONFLICT (item_id, location_id) DO NOTHING;

--
-- Copy data to table `ospos_suppliers`
--

INSERT INTO ospos_suppliers (person_id, company_name, account_number, deleted)
SELECT person_id, company_name, account_number, deleted FROM phppos.phppos_suppliers
ON CONFLICT (person_id) DO NOTHING;

-- 
-- Copy data to table `ospos_dinner_tables`
--

INSERT INTO ospos_dinner_tables (dinner_table_id, name, status, deleted)
SELECT dinner_table_id, name, status, deleted FROM phppos.phppos_dinner_tables
ON CONFLICT (dinner_table_id) DO NOTHING;