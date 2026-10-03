-- PostgreSQL conversion
-- Add attribute_decimal column to attribute_values table
ALTER TABLE ospos_attribute_values 
ADD COLUMN IF NOT EXISTS attribute_decimal DECIMAL(7,3) DEFAULT NULL;

-- Add definition_unit column to attribute_definitions table
ALTER TABLE ospos_attribute_definitions 
ADD COLUMN IF NOT EXISTS definition_unit VARCHAR(16) DEFAULT NULL;