-- PostgreSQL conversion

-- Add agency_name column to suppliers table
ALTER TABLE ospos_suppliers 
ADD COLUMN IF NOT EXISTS agency_name VARCHAR(255) DEFAULT '';

-- If you need it NOT NULL with a default value
-- ALTER TABLE ospos_suppliers 
-- ADD COLUMN IF NOT EXISTS agency_name VARCHAR(255) NOT NULL DEFAULT '';

-- Insert app config values
INSERT INTO ospos_app_config (key, value) VALUES
('dateformat', 'm/d/Y'),
('timeformat', 'H:i:s'),
('barcode_generate_if_empty', '0')
ON CONFLICT (key) DO NOTHING;

-- Drop the invoice_number index from sales_suspended table
DROP INDEX IF EXISTS ospos_sales_suspended_invoice_number_idx;

-- If the index was created with a different name, you may need to find it:
SELECT indexname FROM pg_indexes WHERE tablename = 'ospos_sales_suspended';

-- Rename item_pic column to pic_id in items table
ALTER TABLE ospos_items 
RENAME COLUMN item_pic TO pic_id;

-- Modify comment column to allow NULL in sales table
ALTER TABLE ospos_sales 
ALTER COLUMN comment DROP NOT NULL,
ALTER COLUMN comment SET DEFAULT NULL;

-- Modify comment column to allow NULL in receivings table
ALTER TABLE ospos_receivings 
ALTER COLUMN comment DROP NOT NULL,
ALTER COLUMN comment SET DEFAULT NULL;

-- Modify comment column to allow NULL in sales_suspended table
ALTER TABLE ospos_sales_suspended 
ALTER COLUMN comment DROP NOT NULL,
ALTER COLUMN comment SET DEFAULT NULL;

-- Update comment columns to NULL where value is '0'
UPDATE ospos_sales SET comment = NULL WHERE comment = '0';
UPDATE ospos_receivings SET comment = NULL WHERE comment = '0';
UPDATE ospos_sales_suspended SET comment = NULL WHERE comment = '0';