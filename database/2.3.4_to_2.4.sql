-- PostgreSQL conversion

-- Drop sessions table if exists
DROP TABLE IF EXISTS ospos_sessions CASCADE;

-- Create sessions table
CREATE TABLE ospos_sessions (
    id VARCHAR(40) NOT NULL DEFAULT '0',
    ip_address VARCHAR(45) NOT NULL DEFAULT '0',
    data BYTEA NOT NULL,
    timestamp INTEGER NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
);

-- Optional: Add an index on timestamp for better performance
CREATE INDEX IF NOT EXISTS idx_sessions_timestamp ON ospos_sessions (timestamp);