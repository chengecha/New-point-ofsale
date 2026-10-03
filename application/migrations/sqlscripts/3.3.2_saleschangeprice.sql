-- PostgreSQL conversion
-- Add permission for changing sales price

INSERT INTO ospos_permissions (permission_id, module_id) VALUES
('sales_change_price', 'sales')
ON CONFLICT (permission_id) DO NOTHING;

INSERT INTO ospos_grants (permission_id, person_id, menu_group) VALUES
('sales_change_price', 1, '--')
ON CONFLICT (permission_id, person_id) DO NOTHING;