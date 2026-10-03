ALTER TABLE ospos_attribute_values RENAME COLUMN attribute_datetime TO attribute_date;
ALTER TABLE ospos_attribute_values ALTER COLUMN attribute_date TYPE DATE USING attribute_date::DATE;