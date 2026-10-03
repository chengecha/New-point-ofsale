-- PostgreSQL conversion
-- Rename tables from ospos_* to phppos_*

ALTER TABLE ospos_app_config RENAME TO phppos_app_config;
ALTER TABLE ospos_customers RENAME TO phppos_customers;
ALTER TABLE ospos_employees RENAME TO phppos_employees;
ALTER TABLE ospos_giftcards RENAME TO phppos_giftcards;
ALTER TABLE ospos_inventory RENAME TO phppos_inventory;
ALTER TABLE ospos_items RENAME TO phppos_items;
ALTER TABLE ospos_items_taxes RENAME TO phppos_items_taxes;
ALTER TABLE ospos_item_kits RENAME TO phppos_item_kits;
ALTER TABLE ospos_item_kit_items RENAME TO phppos_item_kit_items;
ALTER TABLE ospos_modules RENAME TO phppos_modules;
ALTER TABLE ospos_people RENAME TO phppos_people;
ALTER TABLE ospos_permissions RENAME TO phppos_permissions;
ALTER TABLE ospos_receivings RENAME TO phppos_receivings;
ALTER TABLE ospos_receivings_items RENAME TO phppos_receivings_items;
ALTER TABLE ospos_sales RENAME TO phppos_sales;
ALTER TABLE ospos_sales_items RENAME TO phppos_sales_items;
ALTER TABLE ospos_sales_items_taxes RENAME TO phppos_sales_items_taxes;
ALTER TABLE ospos_sales_payments RENAME TO phppos_sales_payments;
ALTER TABLE ospos_sales_suspended RENAME TO phppos_sales_suspended;
ALTER TABLE ospos_sales_suspended_items RENAME TO phppos_sales_suspended_items;
ALTER TABLE ospos_sales_suspended_items_taxes RENAME TO phppos_sales_suspended_items_taxes;
ALTER TABLE ospos_sales_suspended_payments RENAME TO phppos_sales_suspended_payments;
ALTER TABLE ospos_sessions RENAME TO phppos_sessions;
ALTER TABLE ospos_suppliers RENAME TO phppos_suppliers;
ALTER TABLE ospos_dinner_tables RENAME TO phppos_dinner_tables;

-- Also rename any tables that might have been missed
ALTER TABLE IF EXISTS ospos_cash_up RENAME TO phppos_cash_up;
ALTER TABLE IF EXISTS ospos_expenses RENAME TO phppos_expenses;
ALTER TABLE IF EXISTS ospos_expense_categories RENAME TO phppos_expense_categories;
ALTER TABLE IF EXISTS ospos_grants RENAME TO phppos_grants;
ALTER TABLE IF EXISTS ospos_stock_locations RENAME TO phppos_stock_locations;
ALTER TABLE IF EXISTS ospos_tax_codes RENAME TO phppos_tax_codes;
ALTER TABLE IF EXISTS ospos_tax_categories RENAME TO phppos_tax_categories;
ALTER TABLE IF EXISTS ospos_tax_jurisdictions RENAME TO phppos_tax_jurisdictions;
ALTER TABLE IF EXISTS ospos_tax_rates RENAME TO phppos_tax_rates;
ALTER TABLE IF EXISTS ospos_attribute_definitions RENAME TO phppos_attribute_definitions;
ALTER TABLE IF EXISTS ospos_attribute_links RENAME TO phppos_attribute_links;
ALTER TABLE IF EXISTS ospos_attribute_values RENAME TO phppos_attribute_values;
ALTER TABLE IF EXISTS ospos_customers_packages RENAME TO phppos_customers_packages;
ALTER TABLE IF EXISTS ospos_sales_reward_points RENAME TO phppos_sales_reward_points;
ALTER TABLE IF EXISTS ospos_item_quantities RENAME TO phppos_item_quantities;
ALTER TABLE IF EXISTS ospos_receivings_items_taxes RENAME TO phppos_receivings_items_taxes;
ALTER TABLE IF EXISTS ospos_sales_taxes RENAME TO phppos_sales_taxes;
ALTER TABLE IF EXISTS ospos_sales_items_taxes RENAME TO phppos_sales_items_taxes;