-- PostgreSQL conversion
-- Attribute Definitions and Values Tables

-- Create attribute_definitions table
CREATE TABLE IF NOT EXISTS ospos_attribute_definitions (
    definition_id SERIAL,
    definition_name VARCHAR(255) NOT NULL,
    definition_type VARCHAR(45) NOT NULL,
    definition_flags SMALLINT NOT NULL,
    definition_fk INTEGER NULL,
    deleted SMALLINT NOT NULL DEFAULT 0,
    PRIMARY KEY (definition_id)
);

CREATE INDEX IF NOT EXISTS idx_attribute_definitions_definition_fk ON ospos_attribute_definitions (definition_fk);

-- Create attribute_values table
CREATE TABLE IF NOT EXISTS ospos_attribute_values (
    attribute_id SERIAL,
    attribute_value VARCHAR(255) UNIQUE NULL,
    attribute_datetime TIMESTAMP NULL,
    PRIMARY KEY (attribute_id)
);

-- Create attribute_links table
CREATE TABLE IF NOT EXISTS ospos_attribute_links (
    attribute_id INTEGER NULL,
    definition_id INTEGER NOT NULL,
    item_id INTEGER NULL,
    sale_id INTEGER NULL,
    receiving_id INTEGER NULL
);

CREATE INDEX IF NOT EXISTS idx_attribute_links_attribute_id ON ospos_attribute_links (attribute_id);
CREATE INDEX IF NOT EXISTS idx_attribute_links_definition_id ON ospos_attribute_links (definition_id);
CREATE INDEX IF NOT EXISTS idx_attribute_links_item_id ON ospos_attribute_links (item_id);
CREATE INDEX IF NOT EXISTS idx_attribute_links_sale_id ON ospos_attribute_links (sale_id);
CREATE INDEX IF NOT EXISTS idx_attribute_links_receiving_id ON ospos_attribute_links (receiving_id);

-- Unique constraint with NULL handling - PostgreSQL treats NULL as distinct
-- For PostgreSQL, we need to use a partial unique index to handle NULLs properly
-- Since PostgreSQL allows multiple NULLs in unique constraints by default, this works
CREATE UNIQUE INDEX IF NOT EXISTS idx_attribute_links_uq1 
ON ospos_attribute_links (attribute_id, definition_id, COALESCE(item_id, -1), COALESCE(sale_id, -1), COALESCE(receiving_id, -1));

-- Alternative: Use a unique constraint with NULLs allowed
-- PostgreSQL allows multiple NULLs in unique constraints, so this works:
-- CREATE UNIQUE INDEX IF NOT EXISTS idx_attribute_links_uq1 
-- ON ospos_attribute_links (attribute_id, definition_id, item_id, sale_id, receiving_id);

-- Add foreign key constraints
ALTER TABLE ospos_attribute_definitions
ADD CONSTRAINT fk_attribute_definitions_definition_fk 
FOREIGN KEY (definition_fk) REFERENCES ospos_attribute_definitions (definition_id);

ALTER TABLE ospos_attribute_links
ADD CONSTRAINT fk_attribute_links_definition 
FOREIGN KEY (definition_id) REFERENCES ospos_attribute_definitions (definition_id) ON DELETE CASCADE;

ALTER TABLE ospos_attribute_links
ADD CONSTRAINT fk_attribute_links_attribute 
FOREIGN KEY (attribute_id) REFERENCES ospos_attribute_values (attribute_id) ON DELETE CASCADE;

ALTER TABLE ospos_attribute_links
ADD CONSTRAINT fk_attribute_links_item 
FOREIGN KEY (item_id) REFERENCES ospos_items (item_id);

ALTER TABLE ospos_attribute_links
ADD CONSTRAINT fk_attribute_links_receiving 
FOREIGN KEY (receiving_id) REFERENCES ospos_receivings (receiving_id);

ALTER TABLE ospos_attribute_links
ADD CONSTRAINT fk_attribute_links_sale 
FOREIGN KEY (sale_id) REFERENCES ospos_sales (sale_id);

-- Add module and permissions
INSERT INTO ospos_modules (name_lang_key, desc_lang_key, sort, module_id) VALUES
('module_attributes', 'module_attributes_desc', 107, 'attributes')
ON CONFLICT (module_id) DO NOTHING;

INSERT INTO ospos_permissions (permission_id, module_id) VALUES
('attributes', 'attributes')
ON CONFLICT (permission_id) DO NOTHING;

INSERT INTO ospos_grants (permission_id, person_id, menu_group) VALUES
('attributes', 1, 'office')
ON CONFLICT (permission_id, person_id) DO NOTHING;

-- Migrate custom fields to text attributes
-- NOTE: items with custom attributes won't keep their selected category!!
INSERT INTO ospos_attribute_definitions (definition_name, definition_type, definition_flags) 
SELECT value, 'TEXT', 1 FROM ospos_app_config WHERE key = 'custom1_name' AND value <> ''
ON CONFLICT DO NOTHING;

INSERT INTO ospos_attribute_definitions (definition_name, definition_type, definition_flags) 
SELECT value, 'TEXT', 1 FROM ospos_app_config WHERE key = 'custom2_name' AND value <> ''
ON CONFLICT DO NOTHING;

INSERT INTO ospos_attribute_definitions (definition_name, definition_type, definition_flags) 
SELECT value, 'TEXT', 1 FROM ospos_app_config WHERE key = 'custom3_name' AND value <> ''
ON CONFLICT DO NOTHING;

INSERT INTO ospos_attribute_definitions (definition_name, definition_type, definition_flags) 
SELECT value, 'TEXT', 1 FROM ospos_app_config WHERE key = 'custom4_name' AND value <> ''
ON CONFLICT DO NOTHING;

INSERT INTO ospos_attribute_definitions (definition_name, definition_type, definition_flags) 
SELECT value, 'TEXT', 1 FROM ospos_app_config WHERE key = 'custom5_name' AND value <> ''
ON CONFLICT DO NOTHING;

INSERT INTO ospos_attribute_definitions (definition_name, definition_type, definition_flags) 
SELECT value, 'TEXT', 1 FROM ospos_app_config WHERE key = 'custom6_name' AND value <> ''
ON CONFLICT DO NOTHING;

INSERT INTO ospos_attribute_definitions (definition_name, definition_type, definition_flags) 
SELECT value, 'TEXT', 1 FROM ospos_app_config WHERE key = 'custom7_name' AND value <> ''
ON CONFLICT DO NOTHING;

INSERT INTO ospos_attribute_definitions (definition_name, definition_type, definition_flags) 
SELECT value, 'TEXT', 1 FROM ospos_app_config WHERE key = 'custom8_name' AND value <> ''
ON CONFLICT DO NOTHING;

INSERT INTO ospos_attribute_definitions (definition_name, definition_type, definition_flags) 
SELECT value, 'TEXT', 1 FROM ospos_app_config WHERE key = 'custom9_name' AND value <> ''
ON CONFLICT DO NOTHING;

INSERT INTO ospos_attribute_definitions (definition_name, definition_type, definition_flags) 
SELECT value, 'TEXT', 1 FROM ospos_app_config WHERE key = 'custom10_name' AND value <> ''
ON CONFLICT DO NOTHING;

-- Insert attribute links for each custom field
-- Custom1
INSERT INTO ospos_attribute_links (definition_id, item_id) 
SELECT ad.definition_id, i.item_id 
FROM ospos_attribute_definitions ad, ospos_app_config ac, ospos_items i
WHERE ac.key = 'custom1_name' AND ac.value = ad.definition_name AND i.custom1 IS NOT NULL AND i.custom1 != ''
ON CONFLICT DO NOTHING;

-- Custom2
INSERT INTO ospos_attribute_links (definition_id, item_id) 
SELECT ad.definition_id, i.item_id 
FROM ospos_attribute_definitions ad, ospos_app_config ac, ospos_items i
WHERE ac.key = 'custom2_name' AND ac.value = ad.definition_name AND i.custom2 IS NOT NULL AND i.custom2 != ''
ON CONFLICT DO NOTHING;

-- Custom3
INSERT INTO ospos_attribute_links (definition_id, item_id) 
SELECT ad.definition_id, i.item_id 
FROM ospos_attribute_definitions ad, ospos_app_config ac, ospos_items i
WHERE ac.key = 'custom3_name' AND ac.value = ad.definition_name AND i.custom3 IS NOT NULL AND i.custom3 != ''
ON CONFLICT DO NOTHING;

-- Custom4
INSERT INTO ospos_attribute_links (definition_id, item_id) 
SELECT ad.definition_id, i.item_id 
FROM ospos_attribute_definitions ad, ospos_app_config ac, ospos_items i
WHERE ac.key = 'custom4_name' AND ac.value = ad.definition_name AND i.custom4 IS NOT NULL AND i.custom4 != ''
ON CONFLICT DO NOTHING;

-- Custom5
INSERT INTO ospos_attribute_links (definition_id, item_id) 
SELECT ad.definition_id, i.item_id 
FROM ospos_attribute_definitions ad, ospos_app_config ac, ospos_items i
WHERE ac.key = 'custom5_name' AND ac.value = ad.definition_name AND i.custom5 IS NOT NULL AND i.custom5 != ''
ON CONFLICT DO NOTHING;

-- Custom6
INSERT INTO ospos_attribute_links (definition_id, item_id) 
SELECT ad.definition_id, i.item_id 
FROM ospos_attribute_definitions ad, ospos_app_config ac, ospos_items i
WHERE ac.key = 'custom6_name' AND ac.value = ad.definition_name AND i.custom6 IS NOT NULL AND i.custom6 != ''
ON CONFLICT DO NOTHING;

-- Custom7
INSERT INTO ospos_attribute_links (definition_id, item_id) 
SELECT ad.definition_id, i.item_id 
FROM ospos_attribute_definitions ad, ospos_app_config ac, ospos_items i
WHERE ac.key = 'custom7_name' AND ac.value = ad.definition_name AND i.custom7 IS NOT NULL AND i.custom7 != ''
ON CONFLICT DO NOTHING;

-- Custom8
INSERT INTO ospos_attribute_links (definition_id, item_id) 
SELECT ad.definition_id, i.item_id 
FROM ospos_attribute_definitions ad, ospos_app_config ac, ospos_items i
WHERE ac.key = 'custom8_name' AND ac.value = ad.definition_name AND i.custom8 IS NOT NULL AND i.custom8 != ''
ON CONFLICT DO NOTHING;

-- Custom9
INSERT INTO ospos_attribute_links (definition_id, item_id) 
SELECT ad.definition_id, i.item_id 
FROM ospos_attribute_definitions ad, ospos_app_config ac, ospos_items i
WHERE ac.key = 'custom9_name' AND ac.value = ad.definition_name AND i.custom9 IS NOT NULL AND i.custom9 != ''
ON CONFLICT DO NOTHING;

-- Custom10
INSERT INTO ospos_attribute_links (definition_id, item_id) 
SELECT ad.definition_id, i.item_id 
FROM ospos_attribute_definitions ad, ospos_app_config ac, ospos_items i
WHERE ac.key = 'custom10_name' AND ac.value = ad.definition_name AND i.custom10 IS NOT NULL AND i.custom10 != ''
ON CONFLICT DO NOTHING;

-- Insert attribute values (using ON CONFLICT to handle duplicates)
INSERT INTO ospos_attribute_values (attribute_value) 
SELECT DISTINCT custom1 FROM ospos_items 
WHERE custom1 <> '' AND '' <> (SELECT value FROM ospos_app_config WHERE key = 'custom1_name')
ON CONFLICT (attribute_value) DO NOTHING;

INSERT INTO ospos_attribute_values (attribute_value) 
SELECT DISTINCT custom2 FROM ospos_items 
WHERE custom2 <> '' AND '' <> (SELECT value FROM ospos_app_config WHERE key = 'custom2_name')
ON CONFLICT (attribute_value) DO NOTHING;

INSERT INTO ospos_attribute_values (attribute_value) 
SELECT DISTINCT custom3 FROM ospos_items 
WHERE custom3 <> '' AND '' <> (SELECT value FROM ospos_app_config WHERE key = 'custom3_name')
ON CONFLICT (attribute_value) DO NOTHING;

INSERT INTO ospos_attribute_values (attribute_value) 
SELECT DISTINCT custom4 FROM ospos_items 
WHERE custom4 <> '' AND '' <> (SELECT value FROM ospos_app_config WHERE key = 'custom4_name')
ON CONFLICT (attribute_value) DO NOTHING;

INSERT INTO ospos_attribute_values (attribute_value) 
SELECT DISTINCT custom5 FROM ospos_items 
WHERE custom5 <> '' AND '' <> (SELECT value FROM ospos_app_config WHERE key = 'custom5_name')
ON CONFLICT (attribute_value) DO NOTHING;

INSERT INTO ospos_attribute_values (attribute_value) 
SELECT DISTINCT custom6 FROM ospos_items 
WHERE custom6 <> '' AND '' <> (SELECT value FROM ospos_app_config WHERE key = 'custom6_name')
ON CONFLICT (attribute_value) DO NOTHING;

INSERT INTO ospos_attribute_values (attribute_value) 
SELECT DISTINCT custom7 FROM ospos_items 
WHERE custom7 <> '' AND '' <> (SELECT value FROM ospos_app_config WHERE key = 'custom7_name')
ON CONFLICT (attribute_value) DO NOTHING;

INSERT INTO ospos_attribute_values (attribute_value) 
SELECT DISTINCT custom8 FROM ospos_items 
WHERE custom8 <> '' AND '' <> (SELECT value FROM ospos_app_config WHERE key = 'custom8_name')
ON CONFLICT (attribute_value) DO NOTHING;

INSERT INTO ospos_attribute_values (attribute_value) 
SELECT DISTINCT custom9 FROM ospos_items 
WHERE custom9 <> '' AND '' <> (SELECT value FROM ospos_app_config WHERE key = 'custom9_name')
ON CONFLICT (attribute_value) DO NOTHING;

INSERT INTO ospos_attribute_values (attribute_value) 
SELECT DISTINCT custom10 FROM ospos_items 
WHERE custom10 <> '' AND '' <> (SELECT value FROM ospos_app_config WHERE key = 'custom10_name')
ON CONFLICT (attribute_value) DO NOTHING;

-- Update attribute links for each custom field
-- Custom1
UPDATE ospos_attribute_links
SET attribute_id = av.attribute_id
FROM ospos_items i, ospos_attribute_values av
WHERE ospos_attribute_links.item_id = i.item_id
AND av.attribute_value = i.custom1
AND ospos_attribute_links.definition_id IN (
    SELECT definition_id FROM ospos_attribute_definitions 
    WHERE definition_name = (SELECT value FROM ospos_app_config WHERE key = 'custom1_name' AND value IS NOT NULL)
);

-- Custom2
UPDATE ospos_attribute_links
SET attribute_id = av.attribute_id
FROM ospos_items i, ospos_attribute_values av
WHERE ospos_attribute_links.item_id = i.item_id
AND av.attribute_value = i.custom2
AND ospos_attribute_links.definition_id IN (
    SELECT definition_id FROM ospos_attribute_definitions 
    WHERE definition_name = (SELECT value FROM ospos_app_config WHERE key = 'custom2_name' AND value IS NOT NULL)
);

-- Custom3
UPDATE ospos_attribute_links
SET attribute_id = av.attribute_id
FROM ospos_items i, ospos_attribute_values av
WHERE ospos_attribute_links.item_id = i.item_id
AND av.attribute_value = i.custom3
AND ospos_attribute_links.definition_id IN (
    SELECT definition_id FROM ospos_attribute_definitions 
    WHERE definition_name = (SELECT value FROM ospos_app_config WHERE key = 'custom3_name' AND value IS NOT NULL)
);

-- Custom4
UPDATE ospos_attribute_links
SET attribute_id = av.attribute_id
FROM ospos_items i, ospos_attribute_values av
WHERE ospos_attribute_links.item_id = i.item_id
AND av.attribute_value = i.custom4
AND ospos_attribute_links.definition_id IN (
    SELECT definition_id FROM ospos_attribute_definitions 
    WHERE definition_name = (SELECT value FROM ospos_app_config WHERE key = 'custom4_name' AND value IS NOT NULL)
);

-- Custom5
UPDATE ospos_attribute_links
SET attribute_id = av.attribute_id
FROM ospos_items i, ospos_attribute_values av
WHERE ospos_attribute_links.item_id = i.item_id
AND av.attribute_value = i.custom5
AND ospos_attribute_links.definition_id IN (
    SELECT definition_id FROM ospos_attribute_definitions 
    WHERE definition_name = (SELECT value FROM ospos_app_config WHERE key = 'custom5_name' AND value IS NOT NULL)
);

-- Custom6
UPDATE ospos_attribute_links
SET attribute_id = av.attribute_id
FROM ospos_items i, ospos_attribute_values av
WHERE ospos_attribute_links.item_id = i.item_id
AND av.attribute_value = i.custom6
AND ospos_attribute_links.definition_id IN (
    SELECT definition_id FROM ospos_attribute_definitions 
    WHERE definition_name = (SELECT value FROM ospos_app_config WHERE key = 'custom6_name' AND value IS NOT NULL)
);

-- Custom7
UPDATE ospos_attribute_links
SET attribute_id = av.attribute_id
FROM ospos_items i, ospos_attribute_values av
WHERE ospos_attribute_links.item_id = i.item_id
AND av.attribute_value = i.custom7
AND ospos_attribute_links.definition_id IN (
    SELECT definition_id FROM ospos_attribute_definitions 
    WHERE definition_name = (SELECT value FROM ospos_app_config WHERE key = 'custom7_name' AND value IS NOT NULL)
);

-- Custom8
UPDATE ospos_attribute_links
SET attribute_id = av.attribute_id
FROM ospos_items i, ospos_attribute_values av
WHERE ospos_attribute_links.item_id = i.item_id
AND av.attribute_value = i.custom8
AND ospos_attribute_links.definition_id IN (
    SELECT definition_id FROM ospos_attribute_definitions 
    WHERE definition_name = (SELECT value FROM ospos_app_config WHERE key = 'custom8_name' AND value IS NOT NULL)
);

-- Custom9
UPDATE ospos_attribute_links
SET attribute_id = av.attribute_id
FROM ospos_items i, ospos_attribute_values av
WHERE ospos_attribute_links.item_id = i.item_id
AND av.attribute_value = i.custom9
AND ospos_attribute_links.definition_id IN (
    SELECT definition_id FROM ospos_attribute_definitions 
    WHERE definition_name = (SELECT value FROM ospos_app_config WHERE key = 'custom9_name' AND value IS NOT NULL)
);

-- Custom10
UPDATE ospos_attribute_links
SET attribute_id = av.attribute_id
FROM ospos_items i, ospos_attribute_values av
WHERE ospos_attribute_links.item_id = i.item_id
AND av.attribute_value = i.custom10
AND ospos_attribute_links.definition_id IN (
    SELECT definition_id FROM ospos_attribute_definitions 
    WHERE definition_name = (SELECT value FROM ospos_app_config WHERE key = 'custom10_name' AND value IS NOT NULL)
);

-- Drop custom columns from items table
ALTER TABLE ospos_items
DROP COLUMN IF EXISTS custom1,
DROP COLUMN IF EXISTS custom2,
DROP COLUMN IF EXISTS custom3,
DROP COLUMN IF EXISTS custom4,
DROP COLUMN IF EXISTS custom5,
DROP COLUMN IF EXISTS custom6,
DROP COLUMN IF EXISTS custom7,
DROP COLUMN IF EXISTS custom8,
DROP COLUMN IF EXISTS custom9,
DROP COLUMN IF EXISTS custom10;

-- Delete config entries
DELETE FROM ospos_app_config WHERE key IN ('custom1_name','custom2_name','custom3_name','custom4_name','custom5_name','custom6_name','custom7_name','custom8_name','custom9_name','custom10_name');