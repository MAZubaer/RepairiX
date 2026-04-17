# Test scripts (dev)

Purpose
- Quick, local helpers to reproduce the registration flow (fetch page to obtain `XSRF-TOKEN` cookie, decode it, then POST with `X-XSRF-TOKEN`, `X-Requested-With`, and `X-Inertia` headers) so requests mimic a real browser/Inertia client.

Files
- `register_shop_pw.ps1` — PowerShell script for Windows/PowerShell. Saves GET/POST responses under `scripts/`.
- `register_shop_curl.sh` — Bash/curl script for Unix-like shells or WSL. Uses `python` to URL-decode the cookie.

Quick run
- PowerShell (Windows):
  ```powershell
  powershell -ExecutionPolicy Bypass -File .\scripts\register_shop_pw.ps1
  ```
- Bash (WSL/macOS/Linux):
  ```bash
  chmod +x ./scripts/register_shop_curl.sh
  ./scripts/register_shop_curl.sh http://127.0.0.1:8000
  ```

Notes & safety
- These are developer utilities — do not run them in production or expose them in public CI without access controls.
- The scripts store temporary responses/cookies in `scripts/` for inspection; remove those files before committing if they contain sensitive data.
- `register_shop_curl.sh` depends on `curl` and `python` for URL-decoding; adjust to use `urldecode` if available.

Why keep them
- They let you reproduce the browser CSRF flow from the CLI, useful for CI sanity checks and debugging headless environments.

If you prefer removal or moving to `scripts/dev/`, tell me and I will do it.
