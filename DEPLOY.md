# Deploying Finance Made Easy to Railway

This app is a **single Laravel service** + a **managed MySQL** service. Deploy them together
in one Railway project (the app is server-rendered Blade — there is no separate frontend).

## 1. Push the code to GitHub
```bash
git init
git add .
git commit -m "Finance Made Easy - final project"
git branch -M main
git remote add origin https://github.com/<you>/finance-made-easy.git
git push -u origin main
```

## 2. Create the Railway project
1. Go to https://railway.app → **New Project** → **Deploy from GitHub repo** → pick this repo.
   Railway detects the `Dockerfile` and builds it automatically.
2. In the same project, click **New** → **Database** → **Add MySQL**.

## 3. Set environment variables on the **app** service
Open the app service → **Variables** → add:

| Key | Value |
|-----|-------|
| `APP_NAME` | Finance Made Easy |
| `APP_ENV` | `production` |
| `APP_KEY` | *(copy from your local `.env` — the `base64:...` value)* |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://<your-app>.up.railway.app` |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | `${{MySQL.MYSQLHOST}}` |
| `DB_PORT` | `${{MySQL.MYSQLPORT}}` |
| `DB_DATABASE` | `${{MySQL.MYSQLDATABASE}}` |
| `DB_USERNAME` | `${{MySQL.MYSQLUSER}}` |
| `DB_PASSWORD` | `${{MySQL.MYSQLPASSWORD}}` |
| `SESSION_DRIVER` | `database` |

> The `${{MySQL.*}}` values are **Railway reference variables** — they auto-fill from the
> MySQL service you added. Type them exactly as shown.

Get your APP_KEY locally with:
```bash
php artisan key:generate --show
```

## 4. (Optional but recommended) Email for verification
Registration works without this, but verification emails won't deliver. To enable them,
sign up for a free SMTP provider (Brevo / Mailtrap / Resend) and add:

| Key | Value |
|-----|-------|
| `MAIL_MAILER` | `smtp` |
| `MAIL_HOST` | *(from provider)* |
| `MAIL_PORT` | `587` |
| `MAIL_USERNAME` | *(from provider)* |
| `MAIL_PASSWORD` | *(from provider)* |
| `MAIL_FROM_ADDRESS` | `no-reply@yourapp.com` |

## 5. Deploy
Railway builds and runs automatically. On boot the container:
- runs `php artisan migrate --force`
- creates the storage symlink (for receipt uploads)
- caches config/routes/views
- starts Apache on Railway's `$PORT`

Then open the generated URL. To create demo data, run once from the Railway service shell:
```bash
php artisan db:seed
```

## Notes
- **Receipt uploads** are stored on the container's local disk, which resets on redeploy.
  For permanent storage, add a Railway **Volume** mounted at `storage/app/public`, or switch
  the `public` disk to S3. (Fine to skip for a demo/portfolio.)
