-- PostgreSQL conversion
-- Drop and recreate foreign key constraint for attribute_links

-- Drop existing foreign key constraint
ALTER TABLE ospos_attribute_links
DROP CONSTRAINT IF EXISTS ospos_attribute_links_ibfk_4;

-- Add foreign key constraint with ON DELETE CASCADE and ON UPDATE RESTRICT
ALTER TABLE ospos_attribute_links
ADD CONSTRAINT ospos_attribute_links_ibfk_4
FOREIGN KEY (receiving_id) REFERENCES ospos_receivings (receiving_id)
ON DELETE CASCADE
ON UPDATE RESTRICT;