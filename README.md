# Riidaja Ülesanded

This is a web application for student exercises.

## Database Setup

The application uses SQLite for data storage. The database file is not included in the repository to avoid overwriting live data.

### Setting Up a New Database

1. Create a new SQLite database file:
   ```
   touch database.db
   ```

2. Run the migration script to initialize the database schema:
   ```
   php migrate.php
   ```

### Updating an Existing Database

To update an existing database to the latest schema:

1. Create a backup of your database:
   ```
   cp database.db database.db.backup
   ```

2. Run the migration script:
   ```
   php migrate.php
   ```

The migration script will:
- Check which migrations have already been applied
- Apply any new migrations in sequential order
- Update the database_schema.sql file with the current schema

### Database Migrations

The project uses a simple migration system:

- Migration files are stored in the `migrations/` directory
- Each migration file is named with a sequential number prefix (e.g., `001_initial_schema.sql`)
- The `migrate.php` script tracks which migrations have been applied in a `migrations` table

To create a new migration:

1. Create a new SQL file in the `migrations/` directory with a sequential number prefix
2. Add the necessary SQL statements to modify the database schema
3. Run `php migrate.php` to apply the migration

## Configuration

1. Copy the sample configuration file:
   ```
   cp config.sample.php config.php
   ```

2. Edit `config.php` and add your Azure client ID and secret.

## Installation

1. Install dependencies:
   ```
   composer install
   ```

2. Make sure the web server has write permissions to the `database.db` file.

## Development

- The database file (`database.db`) is excluded from version control to prevent overwriting live data.
- When making schema changes, create a new migration file rather than directly modifying the database.
- The `database_schema.sql` file is automatically updated when migrations are applied and serves as documentation of the current schema.
