-- PostgreSQL conversion

-- Create stock_locations table
CREATE TABLE IF NOT EXISTS ospos_stock_locations (
    location_id SERIAL PRIMARY KEY,
    location_name VARCHAR(255) DEFAULT NULL,
    deleted INTEGER NOT NULL DEFAULT 0
);

-- Insert default stock location
INSERT INTO ospos_stock_locations (deleted, location_name) 
VALUES (0, 'stock')
ON CONFLICT DO NOTHING;

-- Create item_quantities table
CREATE TABLE IF NOT EXISTS ospos_item_quantities (
    item_id INTEGER NOT NULL,
    location_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL,
    PRIMARY KEY (item_id, location_id)
);

-- Create indexes
CREATE INDEX IF NOT EXISTS idx_item_quantities_item_id ON ospos_item_quantities (item_id);
CREATE INDEX IF NOT EXISTS idx_item_quantities_location_id ON ospos_item_quantities (location_id);

-- Update item_quantities with default location
UPDATE ospos_item_quantities 
SET location_id = (SELECT MIN(location_id) FROM ospos_stock_locations)
WHERE location_id IS NULL;

-- Add trans_location column to inventory
ALTER TABLE ospos_inventory 
ADD COLUMN IF NOT EXISTS trans_location INTEGER;

-- Update inventory with default location
UPDATE ospos_inventory 
SET trans_location = (SELECT MIN(location_id) FROM ospos_stock_locations)
WHERE trans_location IS NULL;

-- Modify trans_location to NOT NULL and add constraints
ALTER TABLE ospos_inventory 
ALTER COLUMN trans_location SET NOT NULL;

CREATE INDEX IF NOT EXISTS idx_inventory_trans_location ON ospos_inventory (trans_location);

ALTER TABLE ospos_inventory 
ADD CONSTRAINT fk_inventory_stock_location 
FOREIGN KEY (trans_location) REFERENCES ospos_stock_locations (location_id);

-- Add item_location to receivings_items
ALTER TABLE ospos_receivings_items 
ADD COLUMN IF NOT EXISTS item_location INTEGER;

-- Update receivings_items with default location
UPDATE ospos_receivings_items 
SET item_location = (SELECT MIN(location_id) FROM ospos_stock_locations)
WHERE item_location IS NULL;

-- Modify item_location to NOT NULL and add constraints
ALTER TABLE ospos_receivings_items 
ALTER COLUMN item_location SET NOT NULL;

CREATE INDEX IF NOT EXISTS idx_receivings_items_item_location ON ospos_receivings_items (item_location);

ALTER TABLE ospos_receivings_items 
ADD CONSTRAINT fk_receivings_items_stock_location 
FOREIGN KEY (item_location) REFERENCES ospos_stock_locations (location_id);

-- Add item_location to sales_items
ALTER TABLE ospos_sales_items 
ADD COLUMN IF NOT EXISTS item_location INTEGER;

-- Update sales_items with default location
UPDATE ospos_sales_items 
SET item_location = (SELECT MIN(location_id) FROM ospos_stock_locations)
WHERE item_location IS NULL;

-- Modify item_location to NOT NULL and add constraints
ALTER TABLE ospos_sales_items 
ALTER COLUMN item_location SET NOT NULL;

CREATE INDEX IF NOT EXISTS idx_sales_items_item_location ON ospos_sales_items (item_location);
CREATE INDEX IF NOT EXISTS idx_sales_items_sale_id ON ospos_sales_items (sale_id);

ALTER TABLE ospos_sales_items 
ADD CONSTRAINT fk_sales_items_stock_location 
FOREIGN KEY (item_location) REFERENCES ospos_stock_locations (location_id);

-- Add indexes to sales_items_taxes
CREATE INDEX IF NOT EXISTS idx_sales_items_taxes_sale_id ON ospos_sales_items_taxes (sale_id);

-- Add indexes to sales_payments
CREATE INDEX IF NOT EXISTS idx_sales_payments_sale_id ON ospos_sales_payments (sale_id);

-- Add item_location to sales_suspended_items
ALTER TABLE ospos_sales_suspended_items 
ADD COLUMN IF NOT EXISTS item_location INTEGER;

-- Update sales_suspended_items with default location
UPDATE ospos_sales_suspended_items 
SET item_location = (SELECT MIN(location_id) FROM ospos_stock_locations)
WHERE item_location IS NULL;

-- -- Modify item_location to NOT NULL and add constraints
ALTER TABLE ospos_sales_suspended_items 
ALTER COLUMN item_location SET NOT NULL;

CREATE INDEX IF NOT EXISTS idx_sales_suspended_items_item_location ON ospos_sales_suspended_items (item_location);
CREATE INDEX IF NOT EXISTS idx_sales_suspended_items_sale_id ON ospos_sales_suspended_items (sale_id);

-- ALTER TABLE ospos_sales_suspended_items 
ADD CONSTRAINT fk_sales_suspended_items_stock_location 
FOREIGN KEY (item_location) REFERENCES ospos_stock_locations (location_id);

-- Add foreign key constraints to item_quantities
ALTER TABLE ospos_item_quantities 
ADD CONSTRAINT fk_item_quantities_item 
FOREIGN KEY (item_id) REFERENCES ospos_items (item_id);

ALTER TABLE ospos_item_quantities 
ADD CONSTRAINT fk_item_quantities_location 
FOREIGN KEY (location_id) REFERENCES ospos_stock_locations (location_id);