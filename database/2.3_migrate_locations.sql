-- PostgreSQL conversion

-- Insert distinct locations from items into stock_locations
INSERT INTO ospos_stock_locations (location_name)
SELECT DISTINCT location 
FROM ospos_items 
WHERE location IS NOT NULL
AND NOT EXISTS (
    SELECT 1 
    FROM ospos_stock_locations 
    WHERE location_name = ospos_items.location
)
ON CONFLICT (location_name) DO NOTHING;

-- Insert item quantities from items table
INSERT INTO ospos_item_quantities (item_id, location_id, quantity)
SELECT 
    i.item_id, 
    sl.location_id, 
    i.quantity
FROM ospos_items i
INNER JOIN ospos_stock_locations sl 
    ON i.location = sl.location_name
WHERE i.location IS NOT NULL
ON CONFLICT (item_id, location_id) DO NOTHING;

-- Drop the location column from items table
ALTER TABLE ospos_items 
DROP COLUMN IF EXISTS location;