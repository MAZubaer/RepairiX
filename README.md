# RepairiX

A marketplace that connects customers with mobile-phone repair shops. Customers find a shop, submit a repair request and follow it until delivery. Shops pay a subscription to receive and manage requests. Admins oversee shops, requests and customers.

**Stack:** Laravel 12 (PHP 8.2), Inertia.js v3, Vue 3, Tailwind CSS v4, Stripe Checkout and webhooks, a database queue for emails.

## Roles

| Role | What they can do |
|---|---|
| Customer | Browse active shops, send repair requests, track status, accept delivery, rate the shop |
| Shop | Subscribe (monthly or yearly through Stripe), manage requests (diagnosis, cost, status), edit profile, logo and gallery |
| Admin | Dashboard stats, activate or deactivate shops, change plan and expiry, view all requests, delete customers |

## How a request moves

```
pending ─┬─> accepted ─> in progress ─> completed ─> sent from shop ─> delivered ─> rated
         └─> rejected
```

- The shop drives every step up to "sent from shop". The customer accepts delivery, then rates the shop, and the shop's rating is the average over its delivered requests.
- A request can only be marked completed once the shop has entered a diagnosis and a cost. Both can be edited only while the request is pending, accepted or in progress.
- Each status change creates an in-app notification. "In progress" and "completed" also queue an email to the customer.

## Local setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed        # admin + demo shop (+ demo customer test@example.com / password in local env)
composer run dev           # app server, queue worker, logs and Vite together
```

Set `ADMIN_PASSWORD` and `SHOP_OWNER_PASSWORD` in `.env` before seeding. If either is empty, the seeder generates a random password and prints it once.

The app runs on SQLite (the default) or MySQL.

## Stripe

Set `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`, `STRIPE_MONTHLY_PRICE_ID` and `STRIPE_YEARLY_PRICE_ID`. Point a Stripe webhook at `POST /stripe/webhook` with these events:

- `checkout.session.completed`
- `customer.subscription.created`
- `customer.subscription.updated`
- `customer.subscription.deleted`

The webhook is the only thing that activates a shop. The checkout success page just waits for it. For local testing, use `stripe listen --forward-to localhost:8000/stripe/webhook`.

## Tests

```bash
php artisan test
```

The feature tests cover:

- registration, login redirects by role, login throttling and guest redirects
- the full repair lifecycle, including emails, notifications, rating and permissions
- the Stripe webhook, using a locally signed payload
- admin shop activation

They run on in-memory SQLite.

## Deployment (Docker / Render)

The `Dockerfile` builds the frontend with Node, then serves Laravel with PHP 8.2 and Apache. On start, `start.sh` caches config, routes and views, runs migrations, links storage, starts a queue worker that restarts itself if it exits, and then starts Apache.

**Uploaded images:** a container's disk is wiped on every redeploy. Set `SHOP_IMAGES_DISK` to persistent storage: either `s3` (install `league/flysystem-aws-s3-v3` and set the `AWS_*` variables) or the `public` disk on a mounted persistent volume.
