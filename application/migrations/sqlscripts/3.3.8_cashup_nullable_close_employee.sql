-- Make close_employee_id nullable for open cashups
ALTER TABLE ospos_cash_up ALTER COLUMN close_employee_id DROP NOT NULL;
ALTER TABLE ospos_cash_up ALTER COLUMN note SET DEFAULT 0;
ALTER TABLE ospos_cash_up ALTER COLUMN note SET NOT NULL;
