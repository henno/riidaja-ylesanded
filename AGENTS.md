# AGENTS.md - Riidaja Ülesanded

This file provides guidance for AI agents working in this repository.

## Project Overview

Riidaja Ülesanded is a PHP web application for student exercises with timed tasks. Students complete exercises, and their results are tracked and stored in a SQLite database.

## Tech Stack

- **PHP 8.x** with built-in web server
- **SQLite** database
- **Docker** for development environment
- **OAuth2** (Azure/Google) for authentication
- **Vanilla JavaScript** (ES6+)

## Commands

### Running the Application

```bash
# Start the server (builds Docker image and starts container)
./php.sh start

# Stop the server
./php.sh stop

# Restart the server
./php.sh restart
```

### Composer Commands

```bash
# Install dependencies
./php.sh composer install

# Add a package
./php.sh composer require vendor/package
```

### Database

```bash
# Run migrations manually (usually automatic)
php migrate.php
```

### Testing (Manual)

Since there's no automated test suite, test changes by:
1. Start the Docker server: `./php.sh start`
2. Access http://localhost:8000
3. Use the Bypass Auth mode for testing (enabled by default in config)

## Code Style Guidelines

### PHP

#### Naming Conventions
- Classes: `PascalCase` (e.g., `ResultsModel`, `TaskController`)
- Methods: `camelCase` (e.g., `getUserBest`, `delete`)
- Variables: `camelCase` (e.g., `$resultsModel`, `$exerciseId`)
- Constants: `UPPER_SNAKE_CASE` (e.g., `ADMIN_EMAIL`, `DB_FILE_PATH`)
- Private/static members: prefix with `self::` or `$this->`

#### File Structure
```php
<?php

class ClassName
{
    private $privateProperty;
    public static $staticProperty;

    public function methodName($param) { }
    
    private static function privateMethod() { }
}
```

#### Best Practices
- Use `require_once __DIR__ . '/path/to/file.php'` for includes
- Use `__DIR__` for reliable path resolution
- Always use prepared statements for database queries (SQL injection prevention)
- Use `htmlspecialchars()` when outputting user data to HTML
- Use strict comparisons (`===`, `!==`) when possible
- Use `??` null coalescing operator for default values
- Return `PDO::FETCH_ASSOC` for associative arrays
- Handle null values explicitly to avoid PHP warnings

#### Error Handling
- Use try-catch for operations that may fail
- Use `error_log()` for logging errors
- Re-throw exceptions after logging when appropriate
- Always check if variables/arrays exist before accessing

```php
// Good: null coalescing
$value = $array['key'] ?? 'default';

// Good: isset check
if (isset($array['key'])) { }

// Good: prepared statements
$stmt = $this->db->prepare('SELECT * FROM table WHERE id = ?');
$stmt->execute([$id]);
```

### JavaScript

#### Naming Conventions
- Classes: `PascalCase` (e.g., `SessionTracker`)
- Functions/variables: `camelCase` (e.g., `isConnected`, `handleInput`)
- Constants: `UPPER_SNAKE_CASE` (e.g., `MAX_RECONNECT_ATTEMPTS`)

#### Best Practices
- Use ES6+ features: `const`, `let`, arrow functions, template literals
- Use IIFE or modules to avoid global scope pollution
- Expose classes to window object for PHP integration: `window.ClassName = ClassName`
- Use `fetch` API for HTTP requests
- Use `JSON.stringify()` for request bodies

```javascript
// Good: IIFE pattern
(() => {
    const privateVar = 'value';
    
    function init() { }
    
    init();
})();

// Good: expose to window
window.SessionTracker = SessionTracker;
```

### SQL

- Always use prepared statements with placeholders (`?` or named `:param`)
- Table names should be lowercase with underscores
- Column names should be lowercase with underscores

```sql
-- Good
SELECT * FROM results WHERE email = ? AND exercise_id = ?;

-- Bad (SQL injection vulnerability)
SELECT * FROM results WHERE email = '$email';
```

## Architecture

### Directory Structure

```
├── controllers/     # Request handlers (TaskController, ResultsController)
├── models/          # Database access (Database, ResultsModel, StudentsModel)
├── views/           # PHP templates (task_list, results, results_summary)
├── exercises/       # Individual exercise files (NNN.php format)
├── js/              # JavaScript files
├── migrations/      # SQL migration files
├── websocket/       # WebSocket server for session tracking
├── index.php        # Main entry point with routing
├── save_result.php  # API endpoint for saving results
├── config.php       # Configuration (auto-created from sample)
└── database.db      # SQLite database (gitignored)
```

### MVC Pattern

- **Models**: `Database.php` (singleton PDO connection), `ResultsModel.php`, `StudentsModel.php`
- **Controllers**: Handle business logic, include views
- **Views**: Pure PHP templates that output HTML

### Exercise System

Exercises are PHP files in `exercises/` with naming format `NNN.php` (e.g., `001.php`).

Each exercise should:
1. Include a `<style>` block for its CSS
2. Include a `<p>` description paragraph (auto-extracted)
3. Include HTML structure
4. Include a `<script>` with JavaScript logic
5. Track time and call `save_result.php` API on completion/timeout

Target time is extracted from exercise files automatically. Use positive elapsed time for passed, negative for failed attempts.

### Database Schema

Main tables:
- `migrations` - tracks applied migrations
- `exercises` - exercise metadata (id, title, target_time, description, result_type)
- `results` - user attempts (email, name, exercise_id, elapsed, timestamp)

## Configuration

Configuration is in `config.php` (auto-created from `config.sample.php`):

```php
const AZURE_CLIENT_ID='...';
const AZURE_CLIENT_SECRET='...';
const BYPASS_AZURE_AUTH=true;  // Set to false for production
const ADMIN_EMAIL='admin@example.com';
const DOCKER_PORT=8000;
```

## Security Considerations

- Always use prepared statements for database queries
- Use `htmlspecialchars()` when outputting user data
- Never commit secrets (Azure/Google OAuth credentials)
- Use `BYPASS_AZURE_AUTH` only for local development
- Validate and sanitize all user input
