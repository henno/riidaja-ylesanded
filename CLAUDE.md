# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Riidaja Ülesanded is a web application for student exercises. It provides a platform where students can complete timed tasks and view their results.

## Commands

### Database Setup

```bash
# Create a new SQLite database file
touch database.db

# Initialize or update the database schema
php migrate.php
```

### Installation

```bash
# Install dependencies
composer install

# Copy sample configuration file
cp config.sample.php config.php
```

## Architecture

1. **Authentication**: Uses Microsoft Azure for user authentication. Can be bypassed for testing with the `BYPASS_AZURE_AUTH` constant in config.php.

2. **Database**: 
   - SQLite database with migrations system
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

5. **Results Management**:
   - Results are stored with user information, exercise ID, and elapsed time
   - Summary and detailed views available
   - Admin users can delete results

## Key Files

- **index.php**: Main application entry point, handles authentication and routing
- **save_result.php**: API endpoint to save exercise results
- **migrate.php**: Database migration script
- **models/ResultsModel.php**: Main model for handling exercise results
- **exercises/**: Directory containing individual exercise PHP files