# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

**Note**: This project can be run with Docker for easy setup and development. See the README.md file for more details on using the Docker environment.

## Project Overview

Riidaja Ülesanded is a web application for student exercises. It provides a platform where students can complete timed tasks and view their results.

## Commands

### Database Setup

```bash
# The database is automatically created and initialized when the application is first accessed
# You can manually run the migration script if needed
php migrate.php
```

### Installation

```bash
# Install dependencies (use the PHP script when working with Docker)
./php.sh composer install
# Or if not using Docker:
composer install

# Configuration files are created automatically from config.sample.php
# You only need to manually copy if you want to edit settings:
# cp config.sample.php config.php
```

## Architecture

1. **Authentication**: Uses Microsoft Azure for user authentication. Can be bypassed for testing with the `BYPASS_AZURE_AUTH` constant in config.php.

2. **Database**: 
   - SQLite database with migrations system
   - Database is automatically created and initialized when application is first accessed
   - Migrations are tracked in the `migrations` table
   - Main tables: `results`, `exercises`, `migrations`

3. **MVC Structure**:
   - **Models**: Database interaction classes (`ResultsModel`, `Database`)
   - **Controllers**: Handle business logic (`TaskController`, `ResultsController`)
   - **Views**: PHP templates for rendering HTML (`task_list.php`, `task_detail.php`, `results.php`, `results_summary.php`)

4. **Exercise System**:
   - Exercises are defined as individual PHP files in the `exercises/` directory (e.g., `001.php`)
   - Each exercise file contains HTML, CSS, and JavaScript for a specific task
   - Tasks are timed and results are saved to the database
   - Target times can be configured in the database
   - New exercises are automatically detected and registered in the database
   - Exercise metadata (target time, description) is automatically extracted from exercise files

5. **Results Management**:
   - Results are stored with user information, exercise ID, and elapsed time
   - Summary and detailed views available
   - Admin users can delete results

## Key Files

- **index.php**: Main application entry point, handles authentication and routing
- **save_result.php**: API endpoint to save exercise results
- **migrate.php**: Database migration script
- **models/Database.php**: Database connection and schema management
- **models/ResultsModel.php**: Main model for handling exercise results
- **exercises/**: Directory containing individual exercise PHP files
- **php.sh**: Shell script for running the application with Docker