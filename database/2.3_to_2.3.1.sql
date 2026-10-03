-- PostgreSQL conversion

-- Drop and recreate permissions table
DROP TABLE IF EXISTS ospos_permissions CASCADE;

CREATE TABLE ospos_permissions (
    permission_id VARCHAR(255) NOT NULL,
    module_id VARCHAR(255) NOT NULL,
    location_id INTEGER DEFAULT NULL,
    PRIMARY KEY (permission_id)
);

CREATE INDEX IF NOT EXISTS idx_permissions_module_id ON ospos_permissions (module_id);

ALTER TABLE ospos_permissions
ADD CONSTRAINT fk_permissions_module 
FOREIGN KEY (module_id) REFERENCES ospos_modules (module_id) ON DELETE CASCADE;

ALTER TABLE ospos_permissions
ADD CONSTRAINT fk_permissions_location 
FOREIGN KEY (location_id) REFERENCES ospos_stock_locations (location_id) ON DELETE CASCADE;

-- Insert permissions
INSERT INTO ospos_permissions (permission_id, module_id) VALUES
('reports_customers', 'reports'),
('reports_receivings', 'reports'),
('reports_items', 'reports'),
('reports_employees', 'reports'),
('reports_suppliers', 'reports'),
('reports_sales', 'reports'),
('reports_discounts', 'reports'),
('reports_taxes', 'reports'),
('reports_inventory', 'reports'),
('reports_categories', 'reports'),
('reports_payments', 'reports'),
('customers', 'customers'),
('employees', 'employees'),
('giftcards', 'giftcards'),
('items', 'items'),
('item_kits', 'item_kits'),
('receivings', 'receivings'),
('reports', 'reports'),
('sales', 'sales'),
('config', 'config'),
('suppliers', 'suppliers'),
('sales_stock', 'sales'),
('receivings_stock', 'receivings')
ON CONFLICT (permission_id) DO NOTHING;

-- Add permissions for existing stock locations
INSERT INTO ospos_permissions (permission_id, module_id, location_id)
SELECT 
    CONCAT('items_', location_name) AS permission_id, 
    'items' AS module_id, 
    location_id 
FROM ospos_stock_locations
ON CONFLICT (permission_id) DO NOTHING;

-- Create grants table
CREATE TABLE IF NOT EXISTS ospos_grants (
    permission_id VARCHAR(255) NOT NULL,
    person_id INTEGER NOT NULL,
    PRIMARY KEY (permission_id, person_id)
);

ALTER TABLE ospos_grants
ADD CONSTRAINT fk_grants_person 
FOREIGN KEY (person_id) REFERENCES ospos_employees (person_id) ON DELETE CASCADE;

ALTER TABLE ospos_grants
ADD CONSTRAINT fk_grants_permission 
FOREIGN KEY (permission_id) REFERENCES ospos_permissions (permission_id) ON DELETE CASCADE;

-- Add grants for all employees
INSERT INTO ospos_grants (permission_id, person_id) VALUES
('reports_customers', 1),
('reports_receivings', 1), 
('reports_items', 1),
('reports_inventory', 1),
('reports_employees', 1),
('reports_suppliers', 1),
('reports_sales', 1),
('reports_categories', 1),
('reports_discounts', 1),    
('reports_payments', 1),    
('reports_taxes', 1),    
('customers', 1),
('employees', 1),
('giftcards', 1),
('items', 1),
('item_kits', 1),
('receivings', 1),
('reports', 1),
('sales', 1),
('config', 1),
('items_stock', 1),
('sales_stock', 1),
('receivings_stock', 1),
('suppliers', 1)
ON CONFLICT (permission_id, person_id) DO NOTHING;

-- Add config options for tax inclusive sales
INSERT INTO ospos_app_config (key, value) VALUES 
('tax_included', '0'),
('recv_invoice_format', '$CO'),
('sales_invoice_format', '$CO')
ON CONFLICT (key) DO NOTHING;

-- Add invoice_number column to receivings table
ALTER TABLE ospos_receivings 
ADD COLUMN IF NOT EXISTS invoice_number VARCHAR(32) DEFAULT NULL;

CREATE UNIQUE INDEX IF NOT EXISTS idx_receivings_invoice_number ON ospos_receivings (invoice_number);

-- Add invoice_number column to sales table
ALTER TABLE ospos_sales 
ADD COLUMN IF NOT EXISTS invoice_number VARCHAR(32) DEFAULT NULL;

CREATE UNIQUE INDEX IF NOT EXISTS idx_sales_invoice_number ON ospos_sales (invoice_number);

-- -- Add invoice_number column to suspended sales table
ALTER TABLE ospos_sales_suspended 
ADD COLUMN IF NOT EXISTS invoice_number VARCHAR(32) DEFAULT NULL;

CREATE UNIQUE INDEX IF NOT EXISTS idx_sales_suspended_invoice_number ON ospos_sales_suspended (invoice_number);

-- Add receiving_quantity column and remove quantity column from items
ALTER TABLE ospos_items 
ADD COLUMN IF NOT EXISTS receiving_quantity INTEGER DEFAULT 1;

ALTER TABLE ospos_items 
DROP COLUMN IF EXISTS quantity;

-- Add record_time column to giftcards table
ALTER TABLE ospos_giftcards 
ADD COLUMN IF NOT EXISTS record_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- Add foreign key to giftcards table
ALTER TABLE ospos_giftcards
ADD CONSTRAINT fk_giftcards_person 
FOREIGN KEY (person_id) REFERENCES ospos_people (person_id);