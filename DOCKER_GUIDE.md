# Production Mode & Docker Operations Guide

## Production Mode (Already Applied)

### What was done:
1. **docker-compose.yml**: Changed `CI_ENV=development` to `CI_ENV=production`
2. **public/index.php**: Set `define('ENVIRONMENT', 'production');`
3. **header.php**: Added `js-cookie` script tag in production mode, added navbar CSS override + `!important` styles
4. **header_js.php**: Removed auto-hide navbar JavaScript, now always shows navbar

### To verify production mode:
```bash
# Check that minified assets are loaded (not individual bower_components)
curl -s -b cookies.txt "http://localhost/cashups" | grep "opensourcepos.min.js" | head -1

# Check that js-cookie is loaded
curl -s -b cookies.txt "http://localhost/cashups" | grep "js.cookie" | head -1
```

## Log Paths

### Web Container (ospos) - Apache Access & Error Logs
```bash
# View Apache access logs (HTTP requests)
docker logs opensourcepos-master_ospos_1 2>&1

# View Apache error logs
docker exec opensourcepos-master_ospos_1 tail -f /var/log/apache2/error.log

# Live tail with filter
docker logs opensourcepos-master_ospos_1 2>&1 | grep -E "POST|GET|error|500|503|403"
```

### Web Container - CodeIgniter Application Logs
```bash
# CI application logs (PHP-level, stored in mounted volume)
/var/log/apache2/error.log          # Apache errors (inside container)

# Access logs via host mount point
docker logs opensourcepos-master_ospos_1 2>&1 | tail -20

# CI JSON logs (event tracking)
docker exec opensourcepos-master_ospos_1 cat /app/application/logs/json/2026-09-28.log

# CI PHP error logs (traditional)
docker exec opensourcepos-master_ospos_1 cat /app/application/logs/log-2026-09-28.php
```

### Database Container (PostgreSQL)
```bash
# View PostgreSQL logs (SQL errors, connection issues)
docker logs postgres 2>&1

# Filter for errors only
docker logs postgres 2>&1 | grep -i "error"
```

### How to tail logs live:
```bash
# Web container logs (real-time)
docker logs -f opensourcepos-master_ospos_1

# PostgreSQL logs (real-time)
docker logs -f postgres
```

## Running SQL Queries in Docker

### PostgreSQL (Database Container)
```bash
# Run a single query
docker exec postgres psql -U felix -d fogypos -c "SELECT * FROM ospos_items LIMIT 5;"

# Run a multi-statement query
docker exec postgres psql -U felix -d fogypos -c "
  ALTER TABLE ospos_items ADD COLUMN test_col VARCHAR(10);
  CREATE INDEX idx_test ON ospos_items (test_col);
"

# Show table structure
docker exec postgres psql -U felix -d fogypos -c "\d ospos_sales_taxes"

# List all tables
docker exec postgres psql -U felix -d fogypos -c "\dt ospos_*"

# Execute SQL from a file
docker exec -i postgres psql -U felix -d fogypos < /path/to/script.sql

# Export database to SQL file
docker exec postgres pg_dump -U felix fogypos > backup.sql

# Interactive psql session
docker exec -it postgres psql -U felix -d fogypos
```

### Web Container (PHP)
```bash
# Run PHP script
docker exec opensourcepos-master_ospos_1 php -r 'echo "PHP version: " . PHP_VERSION . "\n";'

# Check PHP config
docker exec opensourcepos-master_ospos_1 php -i 2>/dev/null | grep -i "memory_limit\|display_errors"

# View file in container
docker exec opensourcepos-master_ospos_1 cat /app/application/config/config.php
```

## Database Connection Details

| Parameter    | Value              |
|-------------|---------------------|
| Host        | postgres (container)|
| Port        | 5432               |
| Database    | fogypos            |
| Username    | felix              |
| Password    | Chengecha@26!      |

## Transferring to Windows

### What to copy (Project Files):
```
opensourcepos-master/
├── docker-compose.yml          # ✅ MODIFIED (CI_ENV=production)
├── Dockerfile
├── database/
│   ├── 01-tables.sql           # Base table definitions
│   ├── 02-constraints.sql      # Foreign key constraints
│   ├── 03-docker-init.sql      # ✅ MODIFIED (schema fixes)
│   └── rename_tables.sql
├── application/
│   └── views/
│       └── partial/
│           ├── header.php      # ✅ MODIFIED (navbar CSS, js-cookie)
│           └── header_js.php   # ✅ MODIFIED (navbar always visible)
├── public/
│   ├── index.php               # ✅ MODIFIED (production mode)
│   ├── css/
│   │   └── fluxwave.css
│   ├── dist/
│   │   ├── opensourcepos.min.js
│   │   └── opensourcepos.min.css
│   └── bower_components/js-cookie/  # Required for CSRF in production
├── public/uploads/              # User uploaded images (Docker volume)
└── public/css/                  # Theme CSS files
```

### What to copy (Docker Volumes):
```bash
# 1. Export PostgreSQL data (before shutting down)
docker exec postgres pg_dump -U felix fogypos > fogypos_backup.sql

# 2. Copy uploads directory from container
docker cp postgres:/var/lib/postgresql/data/. ./postgres_data/
# OR (if using named volumes)
docker volume ls
docker volume inspect <volume_name>

# 3. Copy uploads volume
docker volume ls
docker volume inspect opensourcepos-master_uploads
```

### On Windows:
1. Install Docker Desktop for Windows
2. Copy the entire project folder
3. Import the database backup:
   ```cmd
   docker-compose up -d postgres
   # Wait for PostgreSQL to start, then:
   docker cp fogypos_backup.sql postgres:/tmp/
   docker exec postgres psql -U felix -d fogypos -f /tmp/fogypos_backup.sql
   ```
4. Start the app:
   ```cmd
   docker-compose up -d ospos
   ```

### Docker Volume Information:
```bash
# List all volumes
docker volume ls

# Inspect a volume (get mount path)
docker volume inspect opensourcepos-master_uploads
docker volume inspect opensourcepos-master_logs
docker volume inspect opensourcepos-master_postgres
```

## Common Troubleshooting Commands

```bash
# Restart all services
docker compose restart

# Stop and remove containers + volumes
docker compose down -v

# Rebuild images (if Dockerfile changes)
docker compose build --no-cache

# View container status
docker compose ps

# View container logs (specific service)
docker compose logs ospos
docker compose logs postgres
```
