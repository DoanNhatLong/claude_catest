# catest.ca - Frontend Mock

## Purpose

- This folder holds a frontend-only mock of the catest.ca site (Canadian driving test practice).
- It will be deployed to Netlify and shared with the client so they can review and evaluate the UI and user flows.
- It is a static demo, not the real product. The live site (WordPress + Code Snippets + Quiz Maker + WooCommerce) stays untouched.

## Rules

- Frontend code only. No backend, no database, no server-side code.
- Never call the live site (https://catest.ca), its REST API, or wp-admin from this mock.
- Never put credentials, API keys, or anything from `../.env` or `../.browser-profile/` in this folder.
- All data shown (provinces, test categories, questions, users, packages) is mock data stored inside this folder.
- Everything in the deployed build is public. Do not include anything that must stay private.
- Keep it simple: a small React app that builds to static files Netlify can serve.
- All code, comments, and docs in this folder are written in English. Report to the user in Vietnamese.

## To Be Decided

- Screens and flows to include.
- Stack details (e.g. Vite + React, styling approach).
- Source and amount of sample question data.
- Netlify setup (build command, publish directory, SPA routing).
