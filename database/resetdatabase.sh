#!/bin/bash
read -s -p "Enter Password: " mypassword
echo ""
export PGPASSWORD="$mypassword"

PG_USER="${POSTGRES_USERNAME:-admin}"
PG_HOST="${POSTGRES_HOST_NAME:-localhost}"
PG_PORT="${POSTGRES_PORT:-5432}"

psql -U "$PG_USER" -h "$PG_HOST" -p "$PG_PORT" -d postgres -c "DROP DATABASE IF EXISTS ospos;"
psql -U "$PG_USER" -h "$PG_HOST" -p "$PG_PORT" -d postgres -c "CREATE DATABASE ospos;"

if [ ! -z "$1" ]; then
    psql -U "$PG_USER" -h "$PG_HOST" -p "$PG_PORT" -d ospos -f migrate_phppos.sql
else
    psql -U "$PG_USER" -h "$PG_HOST" -p "$PG_PORT" -d ospos -f tables.sql
    if [ -f "constraints.sql" ]; then
        psql -U "$PG_USER" -h "$PG_HOST" -p "$PG_PORT" -d ospos -f constraints.sql
    fi
fi

unset PGPASSWORD
