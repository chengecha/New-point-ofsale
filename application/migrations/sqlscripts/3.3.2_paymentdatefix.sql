-- PostgreSQL conversion
-- Update payment_time to match sale_time

UPDATE ospos_sales_payments
SET payment_time = sales.sale_time
FROM ospos_sales AS sales
WHERE ospos_sales_payments.sale_id = sales.sale_id;