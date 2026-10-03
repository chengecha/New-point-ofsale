-- PostgreSQL conversion

-- Giftcards table
ALTER TABLE ospos_giftcards 
ALTER COLUMN value TYPE DECIMAL(15,2) USING value::DECIMAL(15,2);

-- Items table
ALTER TABLE ospos_items 
ALTER COLUMN cost_price TYPE DECIMAL(15,2) USING cost_price::DECIMAL(15,2);

ALTER TABLE ospos_items 
ALTER COLUMN unit_price TYPE DECIMAL(15,2) USING unit_price::DECIMAL(15,2);

ALTER TABLE ospos_items 
ALTER COLUMN quantity TYPE DECIMAL(15,0) USING quantity::DECIMAL(15,0);

ALTER TABLE ospos_items 
ALTER COLUMN reorder_level TYPE DECIMAL(15,0) USING reorder_level::DECIMAL(15,0);

-- Items taxes table
ALTER TABLE ospos_items_taxes 
ALTER COLUMN percent TYPE DECIMAL(15,2) USING percent::DECIMAL(15,2);

-- Item kit items table
ALTER TABLE ospos_item_kit_items 
ALTER COLUMN quantity TYPE DECIMAL(15,0) USING quantity::DECIMAL(15,0);

-- Receivings items table
ALTER TABLE ospos_receivings_items 
ALTER COLUMN quantity_purchased TYPE DECIMAL(15,0) USING quantity_purchased::DECIMAL(15,0);

ALTER TABLE ospos_receivings_items 
ALTER COLUMN item_unit_price TYPE DECIMAL(15,2) USING item_unit_price::DECIMAL(15,2);

ALTER TABLE ospos_receivings_items 
ALTER COLUMN discount_percent TYPE DECIMAL(15,2) USING discount_percent::DECIMAL(15,2);

-- Sales items taxes table
ALTER TABLE ospos_sales_items_taxes 
ALTER COLUMN percent TYPE DECIMAL(15,2) USING percent::DECIMAL(15,2);

-- Sales suspended items table
ALTER TABLE ospos_sales_suspended_items 
ALTER COLUMN quantity_purchased TYPE DECIMAL(15,0) USING quantity_purchased::DECIMAL(15,0);

ALTER TABLE ospos_sales_suspended_items 
ALTER COLUMN item_unit_price TYPE DECIMAL(15,2) USING item_unit_price::DECIMAL(15,2);

ALTER TABLE ospos_sales_suspended_items 
ALTER COLUMN discount_percent TYPE DECIMAL(15,2) USING discount_percent::DECIMAL(15,2);

-- Sales suspended items taxes table
ALTER TABLE ospos_sales_suspended_items_taxes 
ALTER COLUMN percent TYPE DECIMAL(15,2) USING percent::DECIMAL(15,2);

-- Sessions table
ALTER TABLE ospos_sessions 
ALTER COLUMN ip_address TYPE VARCHAR(45) USING ip_address::VARCHAR(45);