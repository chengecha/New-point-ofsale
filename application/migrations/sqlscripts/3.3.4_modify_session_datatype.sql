-- PostgreSQL conversion
-- Modify data column type in sessions table
ALTER TABLE ospos_sessions 
ALTER COLUMN data TYPE BYTEA USING data::BYTEA;