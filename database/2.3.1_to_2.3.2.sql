-- =============================================
-- PostgreSQL Conversion - App Config & Permissions
-- =============================================

-- 1. Insert application configuration values
INSERT INTO ospos_app_config (key, value) VALUES 
('barcode_content', 'id'),
('barcode_first_row', 'category'),
('barcode_second_row', 'item_code'),
('barcode_third_row', 'cost_price'),
('barcode_num_in_row', '2'),
('barcode_font', 'Arial'),
('barcode_font_size', '10'),
('barcode_height', '50'),
('barcode_quality', '100'),
('barcode_type', 'Code39'),
('barcode_width', '250'),
('company_logo', ''),
('barcode_page_width', '100'),      
('barcode_page_cellspacing', '20'),
('receipt_show_taxes', '0'),
('use_invoice_template', '1'),
('invoice_default_comments', 'This is a default comment'),
('invoice_email_message', 'Dear $CU, In attachment the receipt for sale $CO'),
('print_silently', '1'),
('print_header', '0'),
('print_footer', '0'),
('print_top_margin', '0'),
('print_left_margin', '0'),
('print_bottom_margin', '0'),
('print_right_margin', '0'),
('default_sales_discount', '0'),
('lines_per_page', '25'),
('show_total_discount', '25')
ON CONFLICT (key) DO NOTHING;

-- 2. Add sales_ location permissions for each stock location
INSERT INTO ospos_permissions (permission_id, module_id, location_id)
SELECT 
    CONCAT('sales_', location_name) AS permission_id, 
    'sales' AS module_id, 
    location_id 
FROM ospos_stock_locations
ON CONFLICT (permission_id) DO NOTHING;

-- 3. Add receivings_ location permissions for each stock location
INSERT INTO ospos_permissions (permission_id, module_id, location_id)
SELECT 
    CONCAT('receivings_', location_name) AS permission_id, 
    'receivings' AS module_id, 
    location_id 
FROM ospos_stock_locations
ON CONFLICT (permission_id) DO NOTHING;

-- 4. Add item_pic column to items table
ALTER TABLE ospos_items 
ADD COLUMN IF NOT EXISTS item_pic INTEGER DEFAULT NULL;

-- 5. Add gender column to people table
ALTER TABLE ospos_people 
ADD COLUMN IF NOT EXISTS gender INTEGER DEFAULT NULL;

-- 6. Drop payment_type column and add index to sale_time in sales table
ALTER TABLE ospos_sales 
DROP COLUMN IF EXISTS payment_type;

CREATE INDEX IF NOT EXISTS idx_sales_sale_time ON ospos_sales (sale_time);

-- 7. Add company_name column to customers table
ALTER TABLE ospos_customers 
ADD COLUMN IF NOT EXISTS company_name VARCHAR(255) DEFAULT NULL;

-- 8. Modify person_id column in giftcards table to allow NULL values
ALTER TABLE ospos_giftcards 
ALTER COLUMN person_id DROP NOT NULL;

-- 9. Add receiving_quantity column to receivings_items table
ALTER TABLE ospos_receivings_items 
ADD COLUMN IF NOT EXISTS receiving_quantity INTEGER NOT NULL DEFAULT 1;