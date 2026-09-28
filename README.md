# Dealer Portal (demo)

A neutral B2B dealer-portal mock showing how the SimplyBoost agent is embedded
**only for logged-in users**, the equivalent of wrapping the embed snippet in
Laravel's Blade `@auth … @endauth`. All names, dealers and chassis numbers are
fictional.

What it demonstrates:

- **Logged out:** the page contains no widget, no script and no bot ID.
- **Logged in:** the agent appears bottom right.
- **Logout:** `window.SimplyBoost.reset()` runs in the logout click handler, so
  the next user on a shared computer starts with an empty chat. It must run in
  the click handler: `form.submit()` does not fire the form's `submit` event.

## Configuration

Set these environment variables (see `.env.example`). The app returns `503` until
all required ones are set.

| Variable | Required | Purpose |
|---|---|---|
| `BASIC_AUTH_USER` / `BASIC_AUTH_PASSWORD` | yes | Site-wide password prompt, so the demo is never publicly browsable |
| `DEMO_PASSWORD` | yes | Password for both demo logins: `admin@demo.test` and `dealer@demo.test` |
| `SIMPLYBOOST_BOT_ID` | yes | The bot to embed after login |
| `SIMPLYBOOST_WIDGET_URL` | no | Defaults to `https://get.simplyboost.io/widget.js` |

Use long random values for both passwords.

## Deploy to Railway

1. Push this folder to its own Git repository (private), or deploy it directly
   with the Railway CLI: `railway up` from this folder.
2. In Railway: **New Project → Deploy from GitHub repo** (or the CLI project).
   Railway picks up `railway.json` and builds the `Dockerfile`.
3. In the service's **Variables** tab, add the variables above.
4. In **Settings → Networking**, click **Generate Domain**.
5. Open the domain: the browser asks for the basic-auth user and password, then
   shows the portal login.

`/healthz` is the health check. It is the only path reachable without the
basic-auth password, and it returns `503` when the app is misconfigured, so a
broken deploy never goes live.

## Run locally

```bash
cp .env.example .env   # then fill in the values
docker build -t dealer-portal-demo .
docker run --rm -p 8090:8090 -e PORT=8090 --env-file .env dealer-portal-demo
```

Open http://localhost:8090.

## Notes

- PHP's built-in web server is used. That is fine for a low-traffic, password-
  protected demo, not for production traffic.
- Sessions live in the container, so a redeploy logs everyone out.
- The pages are marked `noindex` (header and meta tag) so search engines skip them.
