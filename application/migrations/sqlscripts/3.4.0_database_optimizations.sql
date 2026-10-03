-- PostgreSQL conversion
-- Index and constraint modifications for all tables

-- ospos_attribute_values table
ALTER TABLE ospos_attribute_values ADD CONSTRAINT ospos_attribute_values_attribute_date_unique UNIQUE (attribute_date);
ALTER TABLE ospos_attribute_values ADD CONSTRAINT ospos_attribute_values_attribute_decimal_unique UNIQUE (attribute_decimal);

-- opsos_attribute_definitions table
ALTER TABLE ospos_attribute_definitions ALTER COLUMN definition_flags TYPE SMALLINT USING definition_flags::SMALLINT;
ALTER TABLE ospos_attribute_definitions ALTER COLUMN definition_flags SET NOT NULL;
CREATE INDEX IF NOT EXISTS idx_attribute_definitions_definition_name ON ospos_attribute_definitions (definition_name);
CREATE INDEX IF NOT EXISTS idx_attribute_definitions_definition_type ON ospos_attribute_definitions (definition_type);

-- opsos_attribute_links table
CREATE UNIQUE INDEX IF NOT EXISTS attribute_links_uq2 ON ospos_attribute_links (item_id, sale_id, receiving_id, definition_id, attribute_id);

-- ospos_cash_up table
ALTER TABLE ospos_cash_up ALTER COLUMN deleted TYPE SMALLINT USING deleted::SMALLINT;
ALTER TABLE ospos_cash_up ALTER COLUMN deleted SET DEFAULT 0;
ALTER TABLE ospos_cash_up ALTER COLUMN deleted SET NOT NULL;

-- ospos_customers table
DROP INDEX IF EXISTS ospos_customers_person_id;
ALTER TABLE ospos_customers ALTER COLUMN taxable TYPE SMALLINT USING taxable::SMALLINT;
ALTER TABLE ospos_customers ALTER COLUMN taxable SET DEFAULT 1;
ALTER TABLE ospos_customers ALTER COLUMN taxable SET NOT NULL;

ALTER TABLE ospos_customers ALTER COLUMN deleted TYPE SMALLINT USING deleted::SMALLINT;
ALTER TABLE ospos_customers ALTER COLUMN deleted SET DEFAULT 0;
ALTER TABLE ospos_customers ALTER COLUMN deleted SET NOT NULL;

ALTER TABLE ospos_customers ALTER COLUMN discount_type TYPE SMALLINT USING discount_type::SMALLINT;
ALTER TABLE ospos_customers ALTER COLUMN discount_type SET DEFAULT 0;
ALTER TABLE ospos_customers ALTER COLUMN discount_type SET NOT NULL;

ALTER TABLE ospos_customers ADD PRIMARY KEY (person_id);
CREATE INDEX IF NOT EXISTS idx_customers_company_name ON ospos_customers (company_name);

-- ospos_customers_packages table
ALTER TABLE ospos_customers_packages ALTER COLUMN deleted TYPE SMALLINT USING deleted::SMALLINT;
ALTER TABLE ospos_customers_packages ALTER COLUMN deleted SET DEFAULT 0;
ALTER TABLE ospos_customers_packages ALTER COLUMN deleted SET NOT NULL;

-- ospos_dinner_tables table
ALTER TABLE ospos_dinner_tables ALTER COLUMN deleted TYPE SMALLINT USING deleted::SMALLINT;
ALTER TABLE ospos_dinner_tables ALTER COLUMN deleted SET DEFAULT 0;
ALTER TABLE ospos_dinner_tables ALTER COLUMN deleted SET NOT NULL;

CREATE INDEX IF NOT EXISTS idx_dinner_tables_status ON ospos_dinner_tables (status);

-- ospos_employees table
DROP INDEX IF EXISTS ospos_employees_person_id;
ALTER TABLE ospos_employees ALTER COLUMN deleted TYPE SMALLINT USING deleted::SMALLINT;
ALTER TABLE ospos_employees ALTER COLUMN deleted SET DEFAULT 0;
ALTER TABLE ospos_employees ALTER COLUMN deleted SET NOT NULL;

ALTER TABLE ospos_employees ALTER COLUMN hash_version TYPE SMALLINT USING hash_version::SMALLINT;
ALTER TABLE ospos_employees ALTER COLUMN hash_version SET DEFAULT 2;
ALTER TABLE ospos_employees ALTER COLUMN hash_version SET NOT NULL;

ALTER TABLE ospos_employees ADD PRIMARY KEY (person_id);

-- ospos_expenses table
ALTER TABLE ospos_expenses ALTER COLUMN deleted TYPE SMALLINT USING deleted::SMALLINT;
ALTER TABLE ospos_expenses ALTER COLUMN deleted SET DEFAULT 0;
ALTER TABLE ospos_expenses ALTER COLUMN deleted SET NOT NULL;

CREATE INDEX IF NOT EXISTS idx_expenses_payment_type ON ospos_expenses (payment_type);
CREATE INDEX IF NOT EXISTS idx_expenses_amount ON ospos_expenses (amount);

-- ospos_expense_categories table
ALTER TABLE ospos_expense_categories ALTER COLUMN deleted TYPE SMALLINT USING deleted::SMALLINT;
ALTER TABLE ospos_expense_categories ALTER COLUMN deleted SET DEFAULT 0;
ALTER TABLE ospos_expense_categories ALTER COLUMN deleted SET NOT NULL;

CREATE INDEX IF NOT EXISTS idx_expense_categories_category_description ON ospos_expense_categories (category_description);

-- ospos_giftcards table
ALTER TABLE ospos_giftcards ALTER COLUMN deleted TYPE SMALLINT USING deleted::SMALLINT;
ALTER TABLE ospos_giftcards ALTER COLUMN deleted SET DEFAULT 0;
ALTER TABLE ospos_giftcards ALTER COLUMN deleted SET NOT NULL;

-- ospos_items table
ALTER TABLE ospos_items ALTER COLUMN deleted TYPE SMALLINT USING deleted::SMALLINT;
ALTER TABLE ospos_items ALTER COLUMN deleted SET DEFAULT 0;
ALTER TABLE ospos_items ALTER COLUMN deleted SET NOT NULL;

ALTER TABLE ospos_items ALTER COLUMN stock_type TYPE SMALLINT USING stock_type::SMALLINT;
ALTER TABLE ospos_items ALTER COLUMN stock_type SET DEFAULT 0;
ALTER TABLE ospos_items ALTER COLUMN stock_type SET NOT NULL;

ALTER TABLE ospos_items ALTER COLUMN item_type TYPE SMALLINT USING item_type::SMALLINT;
ALTER TABLE ospos_items ALTER COLUMN item_type SET DEFAULT 0;
ALTER TABLE ospos_items ALTER COLUMN item_type SET NOT NULL;

CREATE INDEX IF NOT EXISTS idx_items_deleted_item_type ON ospos_items (deleted, item_type);
CREATE UNIQUE INDEX IF NOT EXISTS items_uq1 ON ospos_items (supplier_id, item_id, deleted, item_type);

-- ospos_item_kits table
ALTER TABLE ospos_item_kits ALTER COLUMN kit_discount_type TYPE SMALLINT USING kit_discount_type::SMALLINT;
ALTER TABLE ospos_item_kits ALTER COLUMN kit_discount_type SET DEFAULT 0;
ALTER TABLE ospos_item_kits ALTER COLUMN kit_discount_type SET NOT NULL;

ALTER TABLE ospos_item_kits ALTER COLUMN price_option TYPE SMALLINT USING price_option::SMALLINT;
ALTER TABLE ospos_item_kits ALTER COLUMN price_option SET DEFAULT 0;
ALTER TABLE ospos_item_kits ALTER COLUMN price_option SET NOT NULL;

ALTER TABLE ospos_item_kits ALTER COLUMN print_option TYPE SMALLINT USING print_option::SMALLINT;
ALTER TABLE ospos_item_kits ALTER COLUMN print_option SET DEFAULT 0;
ALTER TABLE ospos_item_kits ALTER COLUMN print_option SET NOT NULL;

CREATE INDEX IF NOT EXISTS idx_item_kits_name_description ON ospos_item_kits (name, description);

-- ospos_item_quantities table
CREATE INDEX IF NOT EXISTS idx_item_quantities_pk_item_location ON ospos_item_quantities (item_id, location_id);

-- ospos_people table
CREATE INDEX IF NOT EXISTS idx_people_name_email_phone ON ospos_people (first_name, last_name, email, phone_number);

-- ospos_receivings_items
ALTER TABLE ospos_receivings_items ALTER COLUMN discount_type TYPE SMALLINT USING discount_type::SMALLINT;
ALTER TABLE ospos_receivings_items ALTER COLUMN discount_type SET DEFAULT 0;
ALTER TABLE ospos_receivings_items ALTER COLUMN discount_type SET NOT NULL;

-- ospos_sales
ALTER TABLE ospos_sales ALTER COLUMN sale_status TYPE SMALLINT USING sale_status::SMALLINT;
ALTER TABLE ospos_sales ALTER COLUMN sale_status SET DEFAULT 0;
ALTER TABLE ospos_sales ALTER COLUMN sale_status SET NOT NULL;

ALTER TABLE ospos_sales ALTER COLUMN sale_type TYPE SMALLINT USING sale_type::SMALLINT;
ALTER TABLE ospos_sales ALTER COLUMN sale_type SET DEFAULT 0;
ALTER TABLE ospos_sales ALTER COLUMN sale_type SET NOT NULL;

-- ospos_sales_items
ALTER TABLE ospos_sales_items ALTER COLUMN discount_type TYPE SMALLINT USING discount_type::SMALLINT;
ALTER TABLE ospos_sales_items ALTER COLUMN discount_type SET DEFAULT 0;
ALTER TABLE ospos_sales_items ALTER COLUMN discount_type SET NOT NULL;

ALTER TABLE ospos_sales_items ALTER COLUMN print_option TYPE SMALLINT USING print_option::SMALLINT;
ALTER TABLE ospos_sales_items ALTER COLUMN print_option SET DEFAULT 0;
ALTER TABLE ospos_sales_items ALTER COLUMN print_option SET NOT NULL;

-- ospos_sales_items_taxes
ALTER TABLE ospos_sales_items_taxes ALTER COLUMN tax_type TYPE SMALLINT USING tax_type::SMALLINT;
ALTER TABLE ospos_sales_items_taxes ALTER COLUMN tax_type SET DEFAULT 0;
ALTER TABLE ospos_sales_items_taxes ALTER COLUMN tax_type SET NOT NULL;

ALTER TABLE ospos_sales_items_taxes ALTER COLUMN rounding_code TYPE SMALLINT USING rounding_code::SMALLINT;
ALTER TABLE ospos_sales_items_taxes ALTER COLUMN rounding_code SET DEFAULT 0;
ALTER TABLE ospos_sales_items_taxes ALTER COLUMN rounding_code SET NOT NULL;

ALTER TABLE ospos_sales_items_taxes ALTER COLUMN cascade_sequence TYPE SMALLINT USING cascade_sequence::SMALLINT;
ALTER TABLE ospos_sales_items_taxes ALTER COLUMN cascade_sequence SET DEFAULT 0;
ALTER TABLE ospos_sales_items_taxes ALTER COLUMN cascade_sequence SET NOT NULL;

-- ospos_sales_taxes
ALTER TABLE ospos_sales_taxes ALTER COLUMN print_sequence TYPE SMALLINT USING print_sequence::SMALLINT;
ALTER TABLE ospos_sales_taxes ALTER COLUMN print_sequence SET DEFAULT 0;
ALTER TABLE ospos_sales_taxes ALTER COLUMN print_sequence SET NOT NULL;

ALTER TABLE ospos_sales_taxes ALTER COLUMN rounding_code TYPE SMALLINT USING rounding_code::SMALLINT;
ALTER TABLE ospos_sales_taxes ALTER COLUMN rounding_code SET DEFAULT 0;
ALTER TABLE ospos_sales_taxes ALTER COLUMN rounding_code SET NOT NULL;

-- ospos_sessions table
CREATE INDEX IF NOT EXISTS idx_sessions_id ON ospos_sessions (id);
CREATE INDEX IF NOT EXISTS idx_sessions_ip_address ON ospos_sessions (ip_address);

-- ospos_stock_locations table
ALTER TABLE ospos_stock_locations ALTER COLUMN deleted TYPE SMALLINT USING deleted::SMALLINT;
ALTER TABLE ospos_stock_locations ALTER COLUMN deleted SET DEFAULT 0;
ALTER TABLE ospos_stock_locations ALTER COLUMN deleted SET NOT NULL;

-- ospos_suppliers table
DROP INDEX IF EXISTS ospos_suppliers_person_id;
ALTER TABLE ospos_suppliers ALTER COLUMN deleted TYPE SMALLINT USING deleted::SMALLINT;
ALTER TABLE ospos_suppliers ALTER COLUMN deleted SET DEFAULT 0;
ALTER TABLE ospos_suppliers ALTER COLUMN deleted SET NOT NULL;

ALTER TABLE ospos_suppliers ALTER COLUMN category TYPE SMALLINT USING category::SMALLINT;
ALTER TABLE ospos_suppliers ALTER COLUMN category SET NOT NULL;

ALTER TABLE ospos_suppliers ADD PRIMARY KEY (person_id);
CREATE INDEX IF NOT EXISTS idx_suppliers_category ON ospos_suppliers (category);
CREATE INDEX IF NOT EXISTS idx_suppliers_company_name_deleted ON ospos_suppliers (company_name, deleted);

-- ospos_tax_categories table
ALTER TABLE ospos_tax_categories ALTER COLUMN deleted TYPE SMALLINT USING deleted::SMALLINT;
ALTER TABLE ospos_tax_categories ALTER COLUMN deleted SET DEFAULT 0;
ALTER TABLE ospos_tax_categories ALTER COLUMN deleted SET NOT NULL;

ALTER TABLE ospos_tax_categories ALTER COLUMN tax_group_sequence TYPE SMALLINT USING tax_group_sequence::SMALLINT;
ALTER TABLE ospos_tax_categories ALTER COLUMN tax_group_sequence SET NOT NULL;

-- ospos_tax_codes table
ALTER TABLE ospos_tax_codes ALTER COLUMN deleted TYPE SMALLINT USING deleted::SMALLINT;
ALTER TABLE ospos_tax_codes ALTER COLUMN deleted SET DEFAULT 0;
ALTER TABLE ospos_tax_codes ALTER COLUMN deleted SET NOT NULL;

-- ospos_tax_jurisdictions table
ALTER TABLE ospos_tax_jurisdictions ALTER COLUMN deleted TYPE SMALLINT USING deleted::SMALLINT;
ALTER TABLE ospos_tax_jurisdictions ALTER COLUMN deleted SET DEFAULT 0;
ALTER TABLE ospos_tax_jurisdictions ALTER COLUMN deleted SET NOT NULL;

ALTER TABLE ospos_tax_jurisdictions ALTER COLUMN tax_group_sequence TYPE SMALLINT USING tax_group_sequence::SMALLINT;
ALTER TABLE ospos_tax_jurisdictions ALTER COLUMN tax_group_sequence SET DEFAULT 0;
ALTER TABLE ospos_tax_jurisdictions ALTER COLUMN tax_group_sequence SET NOT NULL;

ALTER TABLE ospos_tax_jurisdictions ALTER COLUMN cascade_sequence TYPE SMALLINT USING cascade_sequence::SMALLINT;
ALTER TABLE ospos_tax_jurisdictions ALTER COLUMN cascade_sequence SET DEFAULT 0;
ALTER TABLE ospos_tax_jurisdictions ALTER COLUMN cascade_sequence SET NOT NULL;

-- ospos_tax_rates table
ALTER TABLE ospos_tax_rates ALTER COLUMN tax_rounding_code TYPE SMALLINT USING tax_rounding_code::SMALLINT;
ALTER TABLE ospos_tax_rates ALTER COLUMN tax_rounding_code SET DEFAULT 0;
ALTER TABLE ospos_tax_rates ALTER COLUMN tax_rounding_code SET NOT NULL;