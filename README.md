# phptodolist
Next‑level Productivity Todo List (PHP + SQLite)

Features
- Fast JSON API: create, list, update, delete
- Search, filter by status, sort by created/due
- Tags (#work, #home), due dates, archive
- Keyboard shortcuts: n (new), / (search), e (edit inline), x (toggle done), del (delete)
- Focus mode UI for distraction‑free review
- Zero external dependencies; uses SQLite

Project layout
- public/ — front controller, UI, and assets
- src/ — app classes and bootstrap (SQLite init)
- data/ — SQLite database file (auto‑created)
- tests/ — quick smoke test script

Run locally
1) Ensure PHP 8.1+ is installed (CLI). SQLite is bundled with PHP by default.
2) Start the built‑in server:

```bash
php -S 127.0.0.1:8080 -t public public/index.php
```

3) Open http://127.0.0.1:8080 in your browser.

API
- GET /api/todos — list todos (q, status, sort)
- GET /api/todos/{id} — fetch one
- POST /api/todos — create { title, tags?, due_at? }
- PATCH /api/todos/{id} — update any of { title, status, tags, due_at }
- DELETE /api/todos/{id} — remove

Tip: For production, restrict CORS, add auth, and serve PHP via nginx/Apache with opcache.

License
MIT
