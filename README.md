# AI Glossary (PHP + Firebase + Gemini)

French ↔ English glossary that checks Firebase Realtime Database first; if a term is missing, it asks Gemini (`gemini-2.5-flash`) for translation/definitions, shows the result, and stores it back. Includes an admin dashboard (login, CRUD with categories), live search, and a feedback form (Formspree).

## Stack
- PHP 8+, minimal MVC (`models/`, `controllers/`, `views/`)
- Firebase Realtime Database (categories enforced: Networking, Security, Databases, Programming, AI/ML)
- Google Gemini API (v1beta, `gemini-2.5-flash`)
- Tailwind (CDN) + custom CSS, vanilla JS
- Formspree for feedback

## Setup
1) Copy `.env.example` to `.env` and set:
   - `GEMINI_API_KEY`
   - `FIREBASE_DB_URL` (e.g., `https://final-8e953-default-rtdb.firebaseio.com`)
   - Optional `FIREBASE_DB_AUTH` if your rules require an auth token
   - `ADMIN_USER` / `ADMIN_PASS` (default `admin/admin123`)
2) Start the server: `php -S localhost:8000` (or your preferred port).
3) Open `http://localhost:8000`.

## Admin
- Login via Dashboard tab.
- CRUD with category assignment; filter/search terms.

## Feedback
- Posts to `https://formspree.io/f/mblnyonz`.

## Notes
- Keep `.env` private; `.env` is gitignored.
- Categories are fixed to keep data consistent.
