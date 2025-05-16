# Riidaja Ülesanded

This is a web application for student exercises.

## Database Setup

### Setting Up a New Database from Scratch

1. **Create the database file**
   ```bash
   touch database.db
   ```

2. **Run the migration script**
   ```bash
   php migrate.php
   ```
   This will:
   - Create the complete database schema in one step with all necessary tables
   - Automatically register all exercise files from the `exercises/` directory
   - Generate an updated `database_schema.sql` file

3. **Verify database creation**
   ```bash
   sqlite3 database.db ".tables"
   ```
   You should see the tables: `migrations`, `results`, and `exercises`

## Configuration

1. Copy the sample configuration file:
   ```bash
   cp config.sample.php config.php
   ```

2. Edit `config.php` and add your Azure client ID and secret:
   ```php
   const AZURE_CLIENT_ID='your-client-id';
   const AZURE_CLIENT_SECRET='your-client-secret';
   ```

3. For development without Azure authentication, add:
   ```php
   const BYPASS_AZURE_AUTH=true;
   ```

## Installation

1. Install dependencies:
   ```bash
   composer install
   ```

2. Make sure the web server has write permissions to the `database.db` file:
   ```bash
   chmod 664 database.db
   chown www-data:www-data database.db  # Adjust user/group as needed for your web server
   ```

## Adding New Exercises

1. Create a new PHP file in the `exercises/` directory following the naming convention `NNN.php` (e.g., `004.php`, `005.php`)

2. After adding new exercise files, simply run:
   ```bash
   php migrate.php
   ```
   
   The migration script will:
   - Detect new exercise files
   - Extract information (target time, description) from the exercise files
   - Automatically register them in the database
   - Update existing exercises if they've changed

3. If you pull new exercises from the git repository, just run the migration script to update your database:
   ```bash
   git pull
   php migrate.php
   ```

## Database Migrations

The project uses a simple migration system:

- The initial migration file (`001_complete_schema.sql`) creates the full database schema
- The `migrate.php` script tracks applied migrations in the `migrations` table
- For future schema changes, create new migration files

To create a new migration (for future schema changes):

1. Create a new SQL file in the `migrations/` directory with the next sequential number (e.g., `002_add_new_feature.sql`)
2. Add only the SQL statements needed for the schema change
3. Run `php migrate.php` to apply the migration

## Automatic Exercise Detection

The `migrate.php` script automatically:

1. Scans the `exercises/` directory for exercise files (`NNN.php`)
2. Extracts information from each exercise file:
   - Target time (extracted from the timer code or description)
   - Description (extracted from the first paragraph)
3. Registers new exercises in the database
4. Updates existing exercises if they've changed

## Development

- The database file (`database.db`) is excluded from version control to prevent overwriting live data
- When making schema changes, create a new migration file rather than directly modifying the database
- When adding new exercises, simply create the files and run the migration script
- The `database_schema.sql` file is automatically updated when migrations are applied and serves as documentation of the current schema