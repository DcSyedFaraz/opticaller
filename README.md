# OptiCaller

OptiCaller is a fullstack call center management platform for running outbound calling campaigns. Agents make calls directly from the browser via Twilio Voice. Admins get real-time visibility into active conferences, agent activity, campaign analytics, and call transcriptions — all in one place.

## Stack

- **Backend**: Laravel 10, PHP 8.1+
- **Frontend**: Vue 3, Inertia.js, PrimeVue, Tailwind CSS
- **Database**: MySQL
- **Real-time**: Laravel Reverb (WebSockets)
- **Calling**: Twilio Voice SDK (browser + server)
- **Auth**: Laravel Sanctum + Spatie Permissions

## Setup

```bash
git clone https://github.com/DcSyedFaraz/opticaller.git && cd opticaller
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
npm run dev && php artisan serve
```

Configure the following in your `.env` before running:

```env
# Database
DB_DATABASE=opticaller
DB_USERNAME=root
DB_PASSWORD=

# Twilio (required for calling features)
TWILIO_ACCOUNT_SID=
TWILIO_AUTH_TOKEN=
TWILIO_API_KEY=
TWILIO_API_SECRET=
TWILIO_APP_SID=
ADMIN_APP_SID=
TWILIO_PHONE_NUMBER=
TWILIO_VOICE_INTELLIGENCE_SERVICE_SID=

# Laravel Reverb (required for real-time features)
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=127.0.0.1
PUSHER_PORT=8080
PUSHER_SCHEME=http
BROADCAST_DRIVER=reverb
```

To run WebSockets locally:

```bash
php artisan reverb:start
php artisan queue:work
```

## What Was Built

- **Authentication** — Role-based login (Admin / Agent) via Laravel Sanctum and Spatie Permissions
- **Campaign Management** — Projects → SubProjects → Addresses hierarchy for organizing calling campaigns
- **Contact Management (Addresses)** — Full CRUD with bulk import via CSV, soft deletes, field locking, follow-up/re-call date tracking, and forbidden flag for opt-out compliance
- **Twilio Voice Calling** — Browser-based outbound calls using Twilio Voice SDK; calls are routed through Twilio Conferences (not direct dial) to enable admin monitoring and recording
- **Call Recording & Transcription** — Calls are recorded automatically; transcription is requested via Twilio Intelligence API on completion, stored with speaker identification (Agent vs Caller), and recordings are deleted after transcription
- **Real-time Conference Monitoring** — Admin can observe all active conferences live via WebSockets (Laravel Reverb)
- **Agent Activity Tracking** — Tracks call logs, break times, and time-on-call per agent per session
- **Analytics Dashboard** — Today's call counts, completed contacts, and per-agent productivity metrics
- **Feedback System** — Customizable feedback templates per campaign with configurable field visibility
- **Dual Token System** — Separate Twilio Voice tokens for agents and admins with different TwiML App SIDs

## What Was Cut

- **Inbound call handling** — Twilio webhook routes exist but inbound call routing to agents is not fully implemented. Descoped to keep the outbound flow complete and solid.
- **Email notifications** — MAIL config is present in `.env.example` but no notification emails are triggered. Not needed for the core use case.
- **Automated tests** — No feature or unit tests written. Given the time constraints, manual testing was prioritised over test coverage.
- **Mobile responsiveness** — The UI is built for desktop use. The nature of the app (agents on workstations) made this a reasonable cut.
- **Google Maps integration** — `VITE_GOOGLE_MAPS_API_KEY` is in `.env.example` but the map feature was not completed.

## Known Issues

- Twilio webhooks require a publicly accessible URL. For local development, use [ngrok](https://ngrok.com) and update your Twilio app's webhook URLs to point to your tunnel.
- Laravel Reverb must be running separately (`php artisan reverb:start`) for real-time features to work. The app does not degrade gracefully if Reverb is offline.
- Queue worker (`php artisan queue:work`) is required for transcription processing after calls complete.
- The `.env.example` does not include Twilio keys — they must be added manually as documented above.
