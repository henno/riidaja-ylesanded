# Google OAuth + Azure Setup Status

## ✅ Completed Implementation

### Architecture
- **Single domain**: `torva.ee/riidaja` (no separate vkok.ee deployment)
- **Single database**: `database.db` for all users regardless of auth provider
- **User-choice authentication**: Login page presents two options
  - "Logi sisse Azure'ga" → for torva.edu.ee users
  - "Logi sisse Google'ga" → for any Google account holder

### Code Changes
1. ✅ **login.php** - User-facing login page with styled provider selection buttons
2. ✅ **login-callback.php** - OAuth 2.0 callback handler supporting both Google and Azure
3. ✅ **index.php** - Simplified to redirect unauthenticated users to login.php
4. ✅ **bootstrap.php** - Defines single `DB_FILE_PATH` constant
5. ✅ **config.sample.php** - Updated with Google OAuth configuration template
6. ✅ **models/Database.php** - Uses `DB_FILE_PATH` constant instead of hardcoded path
7. ✅ **save_result.php, session_abort.php, save_grade.php, save_class.php** - Updated to use `DB_FILE_PATH`
8. ✅ **websocket/server.js** - Updated to support `WS_PORT` and `DB_PATH` environment variables
9. ✅ **js/session-tracker.js** - Uses `window.RIIDAJA_WS_PORT` for WebSocket connection

## ⏳ Next Steps - Manual Configuration Required

### 1. Create Google OAuth 2.0 Credentials

You need to create OAuth 2.0 credentials in your Google Cloud project:

1. Go to [Google Cloud Console](https://console.cloud.google.com)
2. Select project: **torva-ee-riidaja** (Project number: 131660262565)
3. Navigate to **APIs & Services** > **Credentials**
4. Click **+ CREATE CREDENTIALS** > **OAuth client ID**
5. Choose **Web application** as application type
6. Set **Authorized redirect URIs**:
   - `https://torva.ee/riidaja/login-callback.php?provider=google`
7. Click **CREATE**
8. Copy the **Client ID** and **Client Secret**

### 2. Update config.php

Add your Google OAuth credentials to `/sites/torva.ee_visittorva.ee_kultuur.torva.ee_sport.torva.ee/htdocs/riidaja/config.php`:

```php
const GOOGLE_CLIENT_ID='YOUR_CLIENT_ID_HERE';
const GOOGLE_CLIENT_SECRET='YOUR_CLIENT_SECRET_HERE';
const GOOGLE_REDIRECT_URI='https://torva.ee/riidaja/login-callback.php?provider=google';
```

### 3. Enable Required APIs

In Google Cloud Console, enable:
- ✅ Google People API (for user profile info)

### 4. Configure OAuth Consent Screen

1. Go to **APIs & Services** > **OAuth consent screen**
2. Choose **Internal** (restricts to your organization's Google accounts)
3. Fill in required fields
4. Add scopes: `openid`, `email`, `profile`
5. Save

### 5. Test the Implementation

1. Visit `https://torva.ee/riidaja/`
2. Click **"Logi sisse Google'ga"** button
3. You should be redirected to Google login
4. After successful login, you should see the Riidaja dashboard
5. Click **"Logi välja"** to test logout (should return to login.php)

### 6. Verify Azure Login Still Works

1. Visit `https://torva.ee/riidaja/`
2. Click **"Logi sisse Azure'ga"** button
3. Verify Azure login flow still works correctly

### 7. Test Multi-Provider Sessions

1. Log in with Google account
2. Logout and log in with Azure account
3. Verify that switching between providers works correctly
4. Check that results are accessible in shared database

## Database Migration

The database will be automatically initialized with the current schema on first access. No manual migration is required.

## WebSocket Servers

To run WebSocket servers for session tracking:

```bash
# Terminal 1: Azure/torva.ee WebSocket server (port 8765)
cd /sites/torva.ee_visittorva.ee_kultuur.torva.ee_sport.torva.ee/htdocs/riidaja
node websocket/server.js

# Terminal 2: Google/vkok.ee WebSocket server (port 8766)
cd /sites/torva.ee_visittorva.ee_kultuur.torva.ee_sport.torva.ee/htdocs/riidaja
WS_PORT=8766 DB_PATH=./database.db node websocket/server.js
```

Both servers use the same `database.db` file since we unified the database.

## Configuration Reference

### config.php Constants

- `AZURE_CLIENT_ID` - Azure OAuth Client ID (existing, should already be set)
- `AZURE_CLIENT_SECRET` - Azure OAuth Client Secret (existing, should already be set)
- `GOOGLE_CLIENT_ID` - Google OAuth Client ID (needs to be filled)
- `GOOGLE_CLIENT_SECRET` - Google OAuth Client Secret (needs to be filled)
- `GOOGLE_REDIRECT_URI` - Already set to `https://torva.ee/riidaja/login-callback.php?provider=google`
- `ADMIN_EMAIL` - Email of admin user (currently `henno.taht@torva.edu.ee`)

## Files Modified

- `bootstrap.php` - Simplified to single database path
- `index.php` - Removed domain detection, simplified auth, uses session auth_provider for logout
- `config.sample.php` - Updated Google OAuth config template
- `login.php` - NEW: User-facing login page
- `login-callback.php` - NEW: OAuth 2.0 callback handler
- `models/Database.php` - Uses DB_FILE_PATH constant
- `save_result.php` - Uses DB_FILE_PATH constant
- `session_abort.php` - Uses DB_FILE_PATH constant
- `save_grade.php` - Uses DB_FILE_PATH constant
- `save_class.php` - Uses DB_FILE_PATH constant
- `websocket/server.js` - Supports WS_PORT and DB_PATH env vars
- `js/session-tracker.js` - Uses window.RIIDAJA_WS_PORT

## Testing Checklist

- [ ] Google OAuth credentials created in Google Cloud Console
- [ ] config.php updated with Google credentials
- [ ] Google login button on login.php redirects to Google sign-in
- [ ] Successfully authenticate with Google account
- [ ] Azure login button still works
- [ ] Can switch between Google and Azure authentication
- [ ] Results stored correctly in database
- [ ] Logout works for both providers
- [ ] WebSocket servers start without errors
