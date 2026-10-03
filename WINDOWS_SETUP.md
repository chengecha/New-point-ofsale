# Running Fluxwave POS on Windows

This guide explains how to run the Fluxwave POS application on a Windows client machine.

## Prerequisites

- Windows 10 or Windows 11 (64-bit)
- At least 4GB RAM (8GB+ recommended)
- At least 2GB free disk space

---

## Option 1: Docker Desktop (Recommended)

Docker provides the simplest and most consistent way to run Fluxwave POS on Windows. All dependencies (PHP 7.4, Apache, PostgreSQL) are handled automatically.

### Step 1: Install Docker Desktop for Windows

1. Download Docker Desktop from [https://www.docker.com/products/docker-desktop](https://www.docker.com/products/docker-desktop)
2. Run the installer and follow the on-screen instructions
3. After installation, start Docker Desktop and wait for it to fully launch
4. Verify Docker is running by checking the system tray icon (whale icon)

**Note**: Docker Desktop requires WSL 2 (Windows Subsystem for Linux v2). If not already enabled, Docker Desktop will prompt you to enable it. You may need to:
- Enable the "Virtual Machine Platform" and "Windows Subsystem for Linux" optional features in Windows
- Set WSL 2 as your default version

### Step 2: Clone or Extract the Project

Place the project files in a convenient location, e.g.:
```
C:\Projects\opensourcepos
```

### Step 3: Run the Application

**Using Command Prompt or PowerShell:**
```cmd
cd C:\Projects\opensourcepos
docker-compose up -d
```

**Using the provided batch script (easiest):**
```cmd
cd C:\Projects\opensourcepos
start-windows.bat
```

### Database

The Docker setup automatically creates and initializes a PostgreSQL 13 database named `fogypos` with the user `felix`. The database schema and default seed data (including the admin user) are loaded from `database/01-tables.sql` and `database/02-constraints.sql` on first run. No manual database setup is required.

To run custom SQL manually against the database:

**Interactive SQL prompt:**
```cmd
docker-compose exec postgres psql -U felix -d fogypos
```

**Run a single SQL command:**
```cmd
docker-compose exec postgres psql -U felix -d fogypos -c "SELECT version();"
```

**Import a SQL file:**
```cmd
type your_script.sql | docker-compose exec -T postgres psql -U felix -d fogypos
```

**Connect with pgAdmin or another GUI tool:** The PostgreSQL port is not exposed to the host by default. To connect externally, add a port mapping to the postgres service in `docker-compose.yml`:
```yaml
ports:
  - "5432:5432"
```
Then restart with `docker-compose down` and `docker-compose up -d --build`. Connect to `localhost:5432` with database `fogypos`, user `felix`, password `Chengecha@26!`.

### Step 4: Access the Application

Open your browser and navigate to:
```
http://localhost:80
```

### Default Credentials

- Username: `admin`
- Password: `pointofsale`

### Managing the Application

| Action | Command |
|--------|---------|
| Start the application | `docker-compose up -d` or `start-windows.bat` |
| Stop the application | `docker-compose down` or `stop-windows.bat` |
| View logs | `docker-compose logs -f` |
| Restart | `docker-compose down` then `docker-compose up -d` |
| Rebuild after updates | `docker-compose up -d --build` |

### Changing the Default Port

If port 80 is already in use, edit `docker-compose.yml` and change the ports mapping:
```yaml
ports:
  - "127.0.0.1:8080:80"  # Change 8080 to any available port
```

Then run:
```cmd
docker-compose up -d --build
```

### Data Persistence

The Docker setup uses named volumes for:
- `postgres` - database data
- `uploads` - uploaded files (images, etc.)
- `logs` - application logs

These volumes persist across container restarts. To completely reset, run:
```cmd
docker-compose down -v
```
Then start again with `docker-compose up -d`.

---

## Option 2: Manual Setup (XAMPP + PostgreSQL)

If you prefer not to use Docker, you can install the dependencies manually.

### Step 1: Install XAMPP

1. Download XAMPP for Windows from [https://www.apachefriends.org](https://www.apachefriends.org)
2. Run the installer and follow the setup wizard
3. Start Apache and MySQL services from the XAMPP Control Panel (MySQL is optional if using PostgreSQL)

### Step 2: Install PHP Extensions

Fluxwave POS requires PHP 7.2-7.4. XAMPP ships with PHP 7.4+ by default. You need these extensions enabled (in `php.ini`):
- `php-gd`
- `php-bcmath`
- `php-intl`
- `php-openssl`
- `php-mbstring`
- `php-curl`
- `php-pgsql` (for PostgreSQL support)

### Step 3: Install PostgreSQL

1. Download PostgreSQL from [https://www.postgresql.org/download/windows/](https://www.postgresql.org/download/windows/)
2. Install with these settings:
   - Port: `5432`
   - Username: `felix`
   - Password: `Chengecha@26!` (or change to your preferred password)
3. During installation, note the password you set for the `postgres` user

### Step 4: Set Up the Database

1. Open pgAdmin or use `psql` command line
2. Create a database named `fogypos`:
```sql
CREATE DATABASE fogypos;
```
3. Import the database schema and seed data:
```cmd
psql -U postgres -d fogypos -f database\01-tables.sql
psql -U postgres -d fogypos -f database\02-constraints.sql
```

This creates all tables and inserts default data including the admin user.

### Step 5: Configure the Application

1. **Edit database configuration**: Open `application/config/.env` and update the database credentials to match your PostgreSQL setup:
```
POSTGRES_HOST_NAME="127.0.0.1"
POSTGRES_PORT="5432"
POSTGRES_USERNAME="felix"
POSTGRES_PASSWORD="Chengecha@26!"
POSTGRES_DB_NAME="fogypos"
```

2. **Set encryption key**: Open `application/config/config.php` and set a unique encryption key:
```php
$config['encryption_key'] = 'your-random-encryption-key-here';
```

### Step 6: Configure Apache

1. Open `xampp\apache\conf\extra\httpd-vhosts.conf`
2. Add a virtual host:
```apache
<VirtualHost *:80>
    DocumentRoot "C:/Projects/opensourcepos/public"
    ServerName ospos.local
</VirtualHost>
```
3. Add `ospos.local` to your Windows hosts file (`C:\Windows\System32\drivers\etc\hosts`):
```
127.0.0.1 ospos.local
```
4. Ensure `.htaccess` is enabled. In `httpd.conf`, uncomment:
```apache
LoadModule rewrite_module modules/mod_rewrite.so
```

### Step 7: Run the Application

1. Start Apache and PostgreSQL from the XAMPP Control Panel
2. Open your browser and go to `http://ospos.local` or `http://localhost/opensourcepos/public`

### Default Credentials

- Username: `admin`
- Password: `pointofsale`

---

## Troubleshooting

### Docker: Port 80 Already in Use

Use a different port in `docker-compose.yml`:
```yaml
ports:
  - "127.0.0.1:8080:80"
```

### Docker: Permission Denied on Uploads

Ensure the uploads directory is writable:
```cmd
docker-compose exec ospos chmod -R 750 /app/public/uploads
```

### Docker: Build Fails

If the Docker build fails (e.g., network issues downloading dependencies):
```cmd
docker-compose build --no-cache
```

### Database Connection Issues

- Verify PostgreSQL is running and accepting connections
- Check credentials in `application/config/.env`
- Ensure the database name and user exist

### PHP Version Issues

Fluxwave POS requires PHP 7.2-7.4. PHP 8.0+ is not supported. If using XAMPP, ensure the installed PHP version is within the supported range.
