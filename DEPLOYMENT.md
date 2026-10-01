# NovaMart — deployment

Live: **https://novamart-red.vercel.app**
Health check: **https://novamart-red.vercel.app/_health.php**

Everything is deployed and working except the database, which Vercel cannot
host. One step remains: attach a free cloud MySQL and set five variables.

---

## Step 1 — create a free MySQL

Either provider works and neither asks for a card.

**Aiven** (closest to plain MySQL, recommended)
1. Sign up at <https://aiven.io> → *Create service* → **MySQL** → **Free plan**
2. Wait for it to turn green, then open the *Overview* tab and copy
   Host, Port, User, Password, Database name.

**TiDB Cloud Serverless** (MySQL-compatible, larger free tier)
1. Sign up at <https://tidbcloud.com> → *Create cluster* → **Serverless**
2. *Connect* → copy the host, port `4000`, user and password.

---

## Step 2 — import the database

`database/novamart-full.sql` is a complete dump: 26 tables, all products,
categories, banners, settings and demo accounts.

```bash
mysql -h YOUR_HOST -P YOUR_PORT -u YOUR_USER -p YOUR_DB < database/novamart-full.sql
```

On Windows, use the bundled client:

```bash
"C:\xampp\mysql\bin\mysql.exe" -h YOUR_HOST -P YOUR_PORT -u YOUR_USER -p YOUR_DB < database/novamart-full.sql
```

If the provider only offers a web console, paste the file contents there.

---

## Step 3 — set the variables on Vercel

```bash
vercel env add DB_HOST production
vercel env add DB_PORT production
vercel env add DB_NAME production
vercel env add DB_USER production
vercel env add DB_PASS production
vercel env add DB_SSL production      # value: 1
```

`SESSION_DRIVER=database` is already set.

Then redeploy so the new values are picked up:

```bash
vercel deploy --prod --yes
```

---

## Step 4 — confirm

Open `/_health.php`. A working deployment returns:

```json
{ "db": "connected", "products": 15, "tables": 26, "session_driver": "database" }
```

---

## Demo accounts

| Role     | Email                  | Password       |
|----------|------------------------|----------------|
| Customer | customer@example.com   | `Password123!` |
| Admin    | admin@example.com      | `Password123!` |

Change these before showing the site to anyone real.

---

## How it is wired

**One serverless function.** Vercel's Hobby plan allows 12 functions and this
app has 40+ PHP entry points, so `_router.php` is the single entry point. It
resolves the request path to the real script, restores `SCRIPT_NAME` so
`BASE_URL` and active-nav detection behave as they do locally, blocks path
traversal, and refuses to serve maintenance scripts. Apache never uses it.

**Sessions in MySQL.** Serverless invocations do not share a filesystem, so
PHP's default file sessions would drop logins and guest carts between requests.
`classes/DbSessionHandler.php` stores them in a `sessions` table instead,
activated by `SESSION_DRIVER=database`. Local XAMPP still uses files.

**Static assets** in `assets/` and `uploads/` are served by Vercel's CDN, not
by PHP.

**Excluded from the deployment** (`.vercelignore`): `setup.php`,
`download_images.php`, `config/installed.lock`, and logs.

---

## Known limitation: image uploads

Vercel's filesystem is read-only apart from `/tmp`, and `/tmp` is wiped between
invocations. Product and banner images that ship with the repo display
correctly, but **new uploads through the admin panel will not persist.**

To fix it, send uploads to object storage instead of local disk — Vercel Blob,
Cloudflare R2 or S3 — by changing `classes/FileUpload.php`. Everything else in
the admin panel works.

If you would rather not deal with that, a host that runs PHP and MySQL together
with a real disk (Railway, Render, or standard cPanel hosting) runs this app
unchanged, uploads included.
