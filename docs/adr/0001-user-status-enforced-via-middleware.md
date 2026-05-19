# User Status enforced via middleware, not LoginPage check

User Status (active/inactive) needed to block login and revoke active sessions when an admin deactivates a User. We chose a dedicated middleware registered on the `auth` web route group (plus a `canAccessPanel()` check for Filament) rather than checking status only inside `LoginPage::performLogin()`.

A LoginPage-only check would block new logins but leave existing sessions alive after deactivation — an admin deactivating a User would have no immediate effect on their current session. The middleware runs on every authenticated request, so deactivation takes effect on the next request regardless of when the session started.

## Considered options

- **LoginPage-only check** — simpler, one file, but active sessions survive deactivation.
- **Override the Laravel User provider** — enforcement at the auth layer, but requires extending the framework's internals and is harder to reason about.
- **Middleware on the `auth` group** — chosen. Runs on every request, visible and explicit, easy to test. Paired with a `canAccessPanel()` check to cover Filament routes which use a separate middleware stack.
