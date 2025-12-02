# Glossary MVP

French ↔ English glossary that checks MySQL first and falls back to Gemini for translations/definitions. Admins can log in to manage terms; users can send feedback via Formspree.

## Tech Stack
- PHP 8+, minimal MVC structure (`models/`, `controllers/`, `views/`)
- MySQL (`terms`, `users` tables)
- Google Gemini API (`gemini-2.5-flash`)
- Tailwind (CDN) + custom CSS, vanilla JS
- Formspree for feedback

## Setup
1) Copy `.env.example` to `.env` and fill in your values:
   - `GEMINI_API_KEY` (keep this private; `.env` is gitignored)
   - DB credentials for the `glossary` database
2) Ensure the `glossary` DB exists with `terms` and `users` tables. A sample `glossary.sql` dump is gitignored by default; import your own if needed.
3) Start PHP server: `php -S localhost:8000`
4) Visit `http://localhost:8000`

## Admin
- Default admin user: `admin` / `admin123` (stored in DB)
- Admin panel supports list/search/create/update/delete for terms.

## API Notes
- Gemini endpoints use model `gemini-2.5-flash` via `GEMINI_API_KEY`.
- Feedback posts to `https://formspree.io/f/mblnyonz`.

## Security
- `.env` holds secrets and is excluded from git.
- Do not commit real API keys or DB dumps; `glossary.sql` is gitignored.
