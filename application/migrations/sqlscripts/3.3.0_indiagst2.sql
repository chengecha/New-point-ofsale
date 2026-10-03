-- PostgreSQL conversion
-- This is to cleanup any orphaned tax migration tables

DROP TABLE IF EXISTS ospos_tax_codes_backup CASCADE;
DROP TABLE IF EXISTS ospos_sales_taxes_backup CASCADE;
DROP TABLE IF EXISTS ospos_tax_code_rates_backup CASCADE;