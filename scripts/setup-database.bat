@echo off
REM Windows batch script to set up the CSU CMS database locally
REM Usage: setup-database.bat [reset]
REM   reset - Drop and recreate the database from scratch

if "%1"=="reset" (
    echo ======================================
    echo RESETTING DATABASE - ALL DATA WILL BE LOST!
    echo ======================================
    set /p confirm=Are you sure you want to reset the database? Type 'YES' to confirm: 
    if not "%confirm%"=="YES" (
        echo Operation cancelled.
        pause
        exit /b 0
    )
    set RESET_MODE=1
) else (
    set RESET_MODE=0
)

echo Setting up CSU CMS Platform Database...
echo.

REM Check if XAMPP MySQL is running
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe" >NUL
if "%ERRORLEVEL%"=="1" (
    echo ERROR: MySQL is not running. Please start XAMPP first.
    echo 1. Open XAMPP Control Panel
    echo 2. Click "Start" next to MySQL
    echo 3. Then run this script again
    pause
    exit /b 1
)

echo MySQL is running. Proceeding with database setup...
echo.

REM Set paths
set PROJECT_DIR=%~dp0..
set MYSQL_PATH=C:\xampp\mysql\bin\mysql.exe

REM Check if MySQL exists
if not exist "%MYSQL_PATH%" (
    echo ERROR: MySQL not found at %MYSQL_PATH%
    echo Please check your XAMPP installation.
    pause
    exit /b 1
)

REM Create database if it doesn't exist
if "%RESET_MODE%"=="1" (
    echo Dropping existing database...
    "%MYSQL_PATH%" -u root -e "DROP DATABASE IF EXISTS csu_cms_platform;"
    echo Creating fresh database...
    "%MYSQL_PATH%" -u root -e "CREATE DATABASE csu_cms_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
) else (
    echo Creating database if it doesn't exist...
    "%MYSQL_PATH%" -u root -e "CREATE DATABASE IF NOT EXISTS csu_cms_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
)

if %ERRORLEVEL% neq 0 (
    echo ERROR: Failed to create database. Please check MySQL connection.
    pause
    exit /b 1
)

REM Check if tables exist (skip check in reset mode)
if "%RESET_MODE%"=="1" (
    echo Applying database schema...
    "%MYSQL_PATH%" -u root csu_cms_platform < "%PROJECT_DIR%\database\schema.sql"
    
    if %ERRORLEVEL% neq 0 (
        echo ERROR: Failed to apply schema.
        pause
        exit /b 1
    )
) else (
    echo Checking existing database structure...
    for /f %%i in ('"%MYSQL_PATH%" -u root csu_cms_platform -e "SHOW TABLES LIKE 'campuses';" -s -N 2^>nul') do set TABLE_EXISTS=%%i

    if "%TABLE_EXISTS%"=="campuses" (
        echo Database tables already exist. Checking for any missing data...
        
        REM Check campus count
        for /f %%i in ('"%MYSQL_PATH%" -u root csu_cms_platform -e "SELECT COUNT(*) FROM campuses;" -s -N 2^>nul') do set CAMPUS_COUNT=%%i
        
        if "%CAMPUS_COUNT%"=="9" (
            echo Database appears to be properly set up with all 9 campuses.
        ) else (
            echo Warning: Expected 9 campuses, found %CAMPUS_COUNT%. Database may need repair.
        )
    ) else (
        echo Applying database schema...
        "%MYSQL_PATH%" -u root csu_cms_platform < "%PROJECT_DIR%\database\schema.sql"
        
        if %ERRORLEVEL% neq 0 (
            echo ERROR: Failed to apply schema.
            pause
            exit /b 1
        )
    )
)

REM Check if seeding is needed
echo Checking if seeding is needed...
for /f %%i in ('"%MYSQL_PATH%" -u root csu_cms_platform -e "SELECT COUNT(*) FROM users;" -s -N 2^>nul') do set USER_COUNT=%%i

if "%USER_COUNT%"=="0" (
    echo Seeding database with initial data...
    "%MYSQL_PATH%" -u root csu_cms_platform < "%PROJECT_DIR%\database\seed.sql"
    
    if %ERRORLEVEL% neq 0 (
        echo ERROR: Failed to seed database.
        pause
        exit /b 1
    )
    
    echo.
    echo ========================================
    echo Database seeded successfully!
    echo.
    echo Default Super Admin Login:
    echo Email: superadmin@csu.edu.ph
    echo Password: admin123
    echo ========================================
) else (
    echo Database already contains data. Skipping seeding.
)

echo.
echo Database setup completed successfully!
echo.

REM Show current database status
echo ========================================
echo Current Database Status:
for /f %%i in ('"%MYSQL_PATH%" -u root csu_cms_platform -e "SELECT COUNT(*) FROM campuses;" -s -N 2^>nul') do set FINAL_CAMPUS_COUNT=%%i
for /f %%i in ('"%MYSQL_PATH%" -u root csu_cms_platform -e "SELECT COUNT(*) FROM users;" -s -N 2^>nul') do set FINAL_USER_COUNT=%%i
echo - Campuses: %FINAL_CAMPUS_COUNT%
echo - Users: %FINAL_USER_COUNT%
echo ========================================
echo.

echo Next steps for testing:
echo 1. Create a simple test file to verify database connection
echo 2. Test the Campus class functionality
echo 3. Access system at: http://localhost/campus_website2/
echo.
echo Available test commands:
echo - Run again normally: setup-database.bat
echo - Reset database: setup-database.bat reset
echo.
pause
