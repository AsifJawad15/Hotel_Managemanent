# SmartStay Database - Quick Installation Guide

## Prerequisites
- **MySQL:** Version 5.7 or higher
- **MariaDB:** Version 10.4 or higher (recommended)
- **PHP:** Version 8.0 or higher
- **phpMyAdmin:** Optional but recommended for GUI management

## Method 1: Using phpMyAdmin (Easiest)

### Step 1: Create Database
1. Open phpMyAdmin (typically http://localhost/phpmyadmin)
2. Click "New" in the left sidebar
3. Database name: `smart_stay`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"

### Step 2: Import SQL Files
1. Select the `smart_stay` database from the left sidebar
2. Click the "Import" tab at the top
3. Click "Choose File" and select SQL files in this order:
   - **01_schema.sql** (tables and structure)
   - **02_procedures.sql** (stored procedures)
   - **03_functions.sql** (functions)
   - **04_triggers.sql** (triggers)
   - **05_views.sql** (views)
   - **06_indexes.sql** (performance indexes)
   - **07_sample_data.sql** (test data - optional)
4. Click "Go" for each file
5. Wait for success message before importing next file

### Important Settings in phpMyAdmin:
- ✅ Enable foreign key checks
- ✅ Use default character set: utf8mb4
- ✅ Format: SQL

## Method 2: Using MySQL Command Line

### Step 1: Open Command Prompt/Terminal
For XAMPP on Windows:
```cmd
cd C:\xampp\mysql\bin
```

### Step 2: Login to MySQL
```bash
mysql -u root -p
```
Enter your MySQL root password when prompted.

### Step 3: Create Database
```sql
CREATE DATABASE smart_stay CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smart_stay;
exit;
```

### Step 4: Import Files
Navigate to your SmartStay db folder:
```cmd
cd d:\xampp\htdocs\SmartStay\db
```

Then import each file:
```bash
mysql -u root -p smart_stay < 01_schema.sql
mysql -u root -p smart_stay < 02_procedures.sql
mysql -u root -p smart_stay < 03_functions.sql
mysql -u root -p smart_stay < 04_triggers.sql
mysql -u root -p smart_stay < 05_views.sql
mysql -u root -p smart_stay < 06_indexes.sql
mysql -u root -p smart_stay < 07_sample_data.sql
```

## Method 3: One-Command Installation (Advanced)

Create a batch file `install_database.bat` in the db folder:

```batch
@echo off
echo SmartStay Database Installation
echo ================================
echo.
set /p MYSQL_PASS="Enter MySQL root password: "
echo.
echo Creating database...
mysql -u root -p%MYSQL_PASS% -e "CREATE DATABASE IF NOT EXISTS smart_stay CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
echo.
echo Installing schema...
mysql -u root -p%MYSQL_PASS% smart_stay < 01_schema.sql
echo Installing procedures...
mysql -u root -p%MYSQL_PASS% smart_stay < 02_procedures.sql
echo Installing functions...
mysql -u root -p%MYSQL_PASS% smart_stay < 03_functions.sql
echo Installing triggers...
mysql -u root -p%MYSQL_PASS% smart_stay < 04_triggers.sql
echo Installing views...
mysql -u root -p%MYSQL_PASS% smart_stay < 05_views.sql
echo Installing indexes...
mysql -u root -p%MYSQL_PASS% smart_stay < 06_indexes.sql
echo Installing sample data...
mysql -u root -p%MYSQL_PASS% smart_stay < 07_sample_data.sql
echo.
echo ================================
echo Installation complete!
echo Database: smart_stay
echo Default credentials:
echo   Admin: admin / admin123
echo   Hotel: hotel email / hotel123
echo   Guest: guest email / guest123
echo ================================
pause
```

Run it:
```cmd
install_database.bat
```

## Verification

### Check Tables
```sql
USE smart_stay;
SHOW TABLES;
```

Expected output (16 tables):
- admins
- bookings
- event_bookings
- events
- guests
- hotels
- maintenance_schedule
- payments
- reviews
- room_types
- rooms
- service_bookings
- services
- staff
- system_logs

### Check Procedures
```sql
SHOW PROCEDURE STATUS WHERE Db = 'smart_stay';
```

Expected: 9 procedures

### Check Functions
```sql
SHOW FUNCTION STATUS WHERE Db = 'smart_stay';
```

Expected: 4 functions

### Check Triggers
```sql
SHOW TRIGGERS;
```

Expected: 10 triggers

### Check Views
```sql
SHOW FULL TABLES WHERE Table_Type = 'VIEW';
```

Expected: 7 views

### Test Sample Data
```sql
-- Count hotels
SELECT COUNT(*) FROM hotels;  -- Should return 9

-- Count rooms
SELECT COUNT(*) FROM rooms;   -- Should return 90

-- Count guests
SELECT COUNT(*) FROM guests;  -- Should return 10

-- Count events
SELECT COUNT(*) FROM events;  -- Should return 45
```

## Database Configuration for PHP

Update your `includes/db_connect.php`:

```php
<?php
$servername = "localhost";
$username = "root";
$password = "";  // Your MySQL root password
$database = "smart_stay";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");
?>
```

## Test Login Credentials

After installation, you can login with these default accounts:

### Admin Panel
- **URL:** `http://localhost/SmartStay/pages/admin/admin_login.php`
- **Username:** `admin`
- **Password:** `admin123`

### Hotel Dashboard
- **URL:** `http://localhost/SmartStay/pages/hotel/hotel_login.php`
- **Email:** `contact@grandplaza.com` (or any hotel email from sample data)
- **Password:** `hotel123`

### Guest Portal
- **URL:** `http://localhost/SmartStay/pages/guest/guest_login.php`
- **Email:** `john.smith@email.com` (or any guest email from sample data)
- **Password:** `guest123`

## Troubleshooting

### Error: "Can't connect to MySQL server"
**Solution:** 
- Make sure MySQL/MariaDB service is running
- For XAMPP: Start MySQL in XAMPP Control Panel
- Check port 3306 is not blocked

### Error: "Access denied for user"
**Solution:**
- Verify MySQL username and password
- Default XAMPP: username=`root`, password=`` (empty)
- Try resetting MySQL password if forgotten

### Error: "Table already exists"
**Solution:**
- Drop existing database first:
```sql
DROP DATABASE IF EXISTS smart_stay;
```
- Then reinstall from Step 1

### Error: "Procedure/Function already exists"
**Solution:**
- Drop procedures before reimporting:
```sql
DROP PROCEDURE IF EXISTS CalculateLoyaltyPoints;
DROP FUNCTION IF EXISTS CalculateAge;
```
- Or drop entire database and reinstall

### Error: "Foreign key constraint fails"
**Solution:**
- Ensure files are imported in correct order
- Tables must exist before adding foreign keys
- Check if related records exist before inserting

### Warning: "#1265 Data truncated"
**Solution:**
- Usually safe to ignore during sample data import
- Indicates data fits but was adjusted to field size

## Performance Tips

### After Installation
```sql
-- Optimize all tables
OPTIMIZE TABLE admins, bookings, events, guests, hotels, rooms, reviews;

-- Update statistics
ANALYZE TABLE bookings, rooms, events;

-- Check table status
SHOW TABLE STATUS;
```

### Enable Query Cache (if available)
Add to `my.ini` or `my.cnf`:
```ini
query_cache_type = 1
query_cache_size = 64M
query_cache_limit = 2M
```

## Backup After Installation

Create a backup immediately after successful installation:

```bash
mysqldump -u root -p smart_stay > backup_smartstay_initial.sql
```

Or with compression:
```bash
mysqldump -u root -p smart_stay | gzip > backup_smartstay_initial.sql.gz
```

## Next Steps

1. ✅ **Verify Installation** - Run all verification queries
2. ✅ **Test Login** - Try logging in as admin, hotel, and guest
3. ✅ **Create Backup** - Save initial clean database
4. ✅ **Update Passwords** - Change default passwords for security
5. ✅ **Explore Features** - Test booking, events, price updates
6. ✅ **Read Documentation** - Review README.md for detailed info

## Getting Help

If you encounter issues:
1. Check `system_logs` table for error messages
2. Review MySQL error log (XAMPP: `xampp/mysql/data/*.err`)
3. Verify PHP error log for application issues
4. Test queries in phpMyAdmin before using in code
5. Consult README.md for detailed documentation

## Security Recommendations

### Before Going Live:
1. **Change all default passwords**
2. **Remove or secure sample data**
3. **Create database user with limited privileges:**
```sql
CREATE USER 'smartstay_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON smart_stay.* TO 'smartstay_user'@'localhost';
FLUSH PRIVILEGES;
```
4. **Enable SSL for database connections**
5. **Regular backups - daily recommended**
6. **Monitor system_logs table regularly**

## File Structure Overview

```
db/
├── 01_schema.sql           # Tables and structure (REQUIRED)
├── 02_procedures.sql       # Stored procedures (REQUIRED)
├── 03_functions.sql        # Functions (REQUIRED)
├── 04_triggers.sql         # Triggers (REQUIRED)
├── 05_views.sql           # Views (REQUIRED)
├── 06_indexes.sql         # Indexes (REQUIRED)
├── 07_sample_data.sql     # Test data (OPTIONAL)
├── README.md              # Full documentation
├── database_structure.txt # Structure reference
└── INSTALLATION.md        # This file
```

## Installation Checklist

- [ ] MySQL/MariaDB installed and running
- [ ] phpMyAdmin accessible (optional)
- [ ] Database created: `smart_stay`
- [ ] Character set: `utf8mb4_unicode_ci`
- [ ] Imported 01_schema.sql ✓
- [ ] Imported 02_procedures.sql ✓
- [ ] Imported 03_functions.sql ✓
- [ ] Imported 04_triggers.sql ✓
- [ ] Imported 05_views.sql ✓
- [ ] Imported 06_indexes.sql ✓
- [ ] Imported 07_sample_data.sql ✓ (optional)
- [ ] Verified tables (17 tables)
- [ ] Verified procedures (9 procedures)
- [ ] Verified functions (4 functions)
- [ ] Verified triggers (10 triggers)
- [ ] Verified views (7 views)
- [ ] Tested admin login
- [ ] Tested hotel login
- [ ] Tested guest login
- [ ] Created initial backup
- [ ] Updated db_connect.php
- [ ] Changed default passwords

## Success!

Your SmartStay database is now installed and ready to use!

**Database:** smart_stay  
**Tables:** 16  
**Procedures:** 9  
**Functions:** 4  
**Triggers:** 10  
**Views:** 7  
**Sample Hotels:** 9  
**Sample Rooms:** 90  

---
SmartStay Hotel Management System  
Database Version 2.0  
© 2025
