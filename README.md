# VOX SMM

**SOCIAL GROWTH. REDEFINED.**

VOX SMM is a self-hosted social campaign management panel based on the PHP source in this repository. It includes customer and admin areas, email/password authentication, Google sign-in, wallet and order management, support tickets, and a panel API.

> This project uses the existing PHP/CodeIgniter-era application and MySQL schema. It is not the separate Next.js prototype.

## Brand

- Primary: `#8B5CF6`
- Cyan accent: `#06D6FF`
- Dark background: `#080B16`
- Surface: `#1A1B2E`
- Highlight: `#EC4899`
- Typeface: Plus Jakarta Sans

## Requirements

Use a PHP hosting environment compatible with this codebase, with MySQL or MariaDB and the required PHP extensions enabled, including cURL, OpenSSL, JSON, and session support. Host the site over HTTPS.

## Installation

1. Create a MySQL/MariaDB database and import [`_sql/install.sql`](_sql/install.sql).
2. Configure the database connection and site URL in the application’s configuration files.
3. Configure the PHP environment for hosting and HTTPS. Review `index.php` and set production mode only after configuring error logging.
4. Set the Google OAuth environment variables below in your hosting provider’s protected environment settings. Never commit real credentials.
5. In Google Cloud Console, create a Web OAuth client and add your exact HTTPS callback URL, for example:
   `https://your-domain.example/auth/google_callback`
6. Log in to the admin area and configure the site branding, email delivery, services, provider mappings, and payment methods.
7. Change any default administrator credentials before opening registration to customers.

For an existing database, back it up and run [`_sql/migrations/2026-11-17-google-oauth-vox.sql`](_sql/migrations/2026-11-17-google-oauth-vox.sql) once. This migration is not idempotent; do not run it twice. A fresh install already includes the Google account column in `_sql/install.sql`.

## Google sign-in configuration

Set these server-side environment variables:

```env
VOX_GOOGLE_CLIENT_ID=
VOX_GOOGLE_CLIENT_SECRET=
VOX_GOOGLE_REDIRECT_URI=https://your-domain.example/auth/google_callback
The redirect URI must exactly match the URI configured in Google Cloud Console and use HTTPS. Google sign-in uses a session-bound state value, exchanges the authorization code on the server, and requires a verified Google email. If OAuth is not configured, Google sign-in will report a setup error instead of creating an account.
Panel API
The panel API is available at /api/v1. See [`example.txt`](example.txt) for request examples and supported operations. Create and protect API keys within the panel. Use HTTPS, and never put privileged API keys in browser-side JavaScript.

Provider-backed fulfillment depends on configuring a compatible provider and its credentials in the panel. Payment and provider operations must be tested with the relevant service before accepting live orders or payments.
Responsible use
Use VOX for legitimate campaign management, advertising workflows, content planning, and analytics. Do not offer fake followers, likes, views, or other inauthentic engagement, and do not use the panel to evade platform rules.
Security and production readiness
Google credentials are read from server environment variables. The OAuth flow uses state validation and Google HTTPS endpoints with request timeouts. The application still inherits the age and security characteristics of its upstream codebase. This project has not been independently security-reviewed and should not be considered production-ready until configured, tested, and reviewed. Before launch, secure session cookies, configure database backups and error logging, review enabled payment/provider modules, and test all integrations in their safe test modes.
Source and licensing
This project is based on evansnguyen0104/smm-panel-free. The cloned source did not include a root LICENSE file. Confirm the upstream terms and obtain any rights required for redistribution or commercial hosting. This README does not grant a license.
