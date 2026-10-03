-- PostgreSQL conversion
-- Add item_kit_number column to item_kits table

ALTER TABLE ospos_item_kits
ADD COLUMN IF NOT EXISTS item_kit_number VARCHAR(255) DEFAULT NULL;

CREATE INDEX IF NOT EXISTS idx_item_kits_item_kit_number ON ospos_item_kits (item_kit_number);