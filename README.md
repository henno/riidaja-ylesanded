# Riidaja Ülesanded

This is a web application for student exercises.

## Quick Start with Docker

The easiest way to get started is using Docker, which sets up PHP with SQLite support.

### Prerequisites
- Docker

### Running with Docker

1. **Start the server**
   ```bash
   ./php.sh start
   ```
   This will build the Docker image and start the PHP development server at http://localhost:8000

2. **Access the application**
   - Web interface: http://localhost:8000

3. **Stopping the server**
   ```bash
   ./php.sh stop
   ```

4. **Restart the server**
   ```bash
   ./php.sh restart
   ```

5. **Run Composer commands**
   ```bash
   ./php.sh composer install
   ./php.sh composer require vendor/package
   ```

### Docker Details

The Docker setup uses:
- PHP 8.4.7 with Alpine Linux (minimal footprint)
- PHP's built-in development server
- SQLite support pre-installed
- Code mounted as a volume for live updates

## Database Setup

The application automatically creates and initializes the database if it doesn't exist. When you first access the application, it will:
- Create the database file if it doesn't exist
- Apply all necessary migrations
- Register all exercise files from the `exercises/` directory

This means you can simply start the application and it will handle the database setup for you.

## Configuration

The application automatically creates a default configuration file (`config.php`) if it doesn't exist by copying from `config.sample.php`. The default configuration enables development mode without Azure authentication.

If you need to use Azure authentication, edit `config.php` and add your Azure client ID and secret:
   ```php
   const AZURE_CLIENT_ID='your-client-id';
   const AZURE_CLIENT_SECRET='your-client-secret';
   const BYPASS_AZURE_AUTH=false;
   ```

## Adding New Exercises

Create a new PHP file in the `exercises/` directory following the naming convention `NNN.php` (e.g., `004.php`, `005.php`).

When you access the application, it will automatically:
- Detect new exercise files
- Extract information (target time, description) from the exercise files
- Register them in the database
- Update existing exercises if they've changed

## Database Migrations

The project uses a simple migration system:

- The initial migration file (`001_complete_schema.sql`) creates the full database schema
- Migrations are tracked in the `migrations` table
- The application automatically applies any new migrations when it starts

## Automatic Exercise Detection

The application automatically:

1. Scans the `exercises/` directory for exercise files (`NNN.php`)
2. Extracts information from each exercise file:
   - Target time (extracted from the timer code or description)
   - Description (extracted from the first paragraph)
3. Registers new exercises in the database
4. Updates existing exercises if they've changed

## Development

- The database file (`database.db`) is excluded from version control to prevent overwriting live data
- When adding new exercises, simply create the files in the exercises directory
- The database schema is maintained automatically by the application
- The application includes robust null value handling to prevent PHP deprecation warnings
- Database tables are automatically created if they don't exist when the application is first accessed
- Configuration file (`config.php`) is automatically created if it doesn't exist
