# Google OAuth Setup for vkok.ee

## Overview
This guide explains how to set up Google OAuth authentication for the vkok.ee Riidaja application.

## Prerequisites
- Access to Google Cloud Console (console.cloud.google.com)
- Admin access to vkok.ee domain (optional, but recommended)
- The vkok.ee domain must be verified in Google

## Step-by-Step Setup

### Step 1: Create a Google Cloud Project

1. Go to https://console.cloud.google.com
2. Click the **Project Selector** dropdown (top left)
3. Click **NEW PROJECT**
4. Enter project name: `Vkok Riidaja`
5. Click **CREATE**
6. Wait for the project to be created (this may take a few moments)

### Step 2: Enable Google People API

1. In the Google Cloud Console, go to **APIs & Services** > **Library**
2. Search for "People API"
3. Click on "Google People API"
4. Click the **ENABLE** button
5. Wait for it to be enabled

### Step 3: Create OAuth 2.0 Client ID

1. Go to **APIs & Services** > **Credentials**
2. Click **+ CREATE CREDENTIALS** > **OAuth client ID**
3. If prompted, configure the OAuth consent screen first:
   - Click **CONFIGURE CONSENT SCREEN**
   - Choose **Internal** (restricts to vkok.ee organization)
   - Fill in the required fields:
     - App name: `Vkok Riidaja`
     - User support email: (your school email)
     - Developer contact: (your email)
   - Click **SAVE AND CONTINUE**
   - On "Scopes" page, click **ADD OR REMOVE SCOPES**
   - Search for and add these scopes:
     - `openid`
     - `email`
     - `profile`
   - Click **UPDATE**
   - Click **SAVE AND CONTINUE** through the remaining pages
   - Click **BACK TO DASHBOARD**

4. Now create the OAuth client:
   - Go back to **APIs & Services** > **Credentials**
   - Click **+ CREATE CREDENTIALS** > **OAuth client ID**
   - Application type: **Web application**
   - Name: `Vkok Riidaja`
   - Under "Authorized redirect URIs", click **ADD URI**
   - Enter: `https://vkok.ee/riidaja/`
   - Click **CREATE**

5. A dialog will appear with your credentials:
   - **Client ID**: Copy this value
   - **Client Secret**: Copy this value

### Step 4: Update config.php

Open `/sites/torva.ee_visittorva.ee_kultuur.torva.ee_sport.torva.ee/htdocs/riidaja/config.php`

Replace the empty Google constants:

```php
const GOOGLE_CLIENT_ID = 'YOUR_CLIENT_ID_HERE';
const GOOGLE_CLIENT_SECRET = 'YOUR_CLIENT_SECRET_HERE';
```

Example (with fake values):
```php
const GOOGLE_CLIENT_ID = '123456789-abc123def456ghi789jkl.apps.googleusercontent.com';
const GOOGLE_CLIENT_SECRET = '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456';
```

### Step 5 (Optional): Set vkok.ee Admin Email

If you want to grant admin privileges to a vkok.ee user, update:

```php
const VKOK_ADMIN_EMAIL = 'admin@vkok.ee';
```

### Step 6: Test the Setup

1. Open https://vkok.ee/riidaja/ in a browser
2. You should be redirected to Google login
3. The login URL should include `hd=vkok.ee` to restrict to vkok.ee accounts
4. Login with a @vkok.ee email address
5. You should be redirected back to the application and see the dashboard

## Troubleshooting

### "Invalid redirect URI" error
- Ensure the redirect URI in Google Cloud Console exactly matches: `https://vkok.ee/riidaja/`
- Check for trailing slashes, protocols (https not http), and exact domain match

### "Client ID not found" or blank page
- Verify Google credentials are correctly copied into `config.php`
- Check that there are no extra spaces or quotes in the credentials

### Login screen shows all Google accounts (not restricted to vkok.ee)
- The `hd=vkok.ee` parameter is just a UI hint
- The server-side restriction (`str_ends_with($email, '@vkok.ee')`) is the actual enforcement
- If a user logs in with a non-vkok.ee account, they will be blocked with a 403 error

### WebSocket errors (connection refused on port 8766)
- Ensure the WebSocket servers are running:
  ```bash
  bash /sites/torva.ee_visittorva.ee_kultuur.torva.ee_sport.torva.ee/htdocs/riidaja/websocket/start-servers.sh
  ```
- Check if port 8766 is already in use: `netstat -tlnp | grep 8766`
- Check the WebSocket logs: `tail -f /sites/torva.ee_visittorva.ee_kultuur.torva.ee_sport.torva.ee/htdocs/riidaja/logs/websocket-8766.log`

## Security Notes

- Keep `GOOGLE_CLIENT_SECRET` secure and never commit it to version control
- The `config.php` file should be protected from public access (it already is via nginx config)
- Only @vkok.ee accounts can log in (enforced server-side)
- Sessions are isolated per domain (torva.ee vs vkok.ee)

## Additional Resources

- [Google Cloud Console](https://console.cloud.google.com)
- [Google OAuth 2.0 Documentation](https://developers.google.com/identity/protocols/oauth2)
- [Google People API Documentation](https://developers.google.com/people)
