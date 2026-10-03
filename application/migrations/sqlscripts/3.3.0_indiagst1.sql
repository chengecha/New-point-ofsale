UPDATE ospos_suppliers
SET tax_id = 0
WHERE tax_id IS NULL;

ALTER TABLE ospos_suppliers ALTER COLUMN tax_id SET NOT NULL;
ALTER TABLE ospos_suppliers ALTER COLUMN tax_id SET DEFAULT 0;