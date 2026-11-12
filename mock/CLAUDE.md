# catest.ca - Frontend Mock

## Purpose

- This folder holds a frontend-only mock of the catest.ca site (Canadian driving test practice).
- It will be deployed to Netlify and shared with the client so they can review and evaluate the UI and user flows.
- It is a static demo, not the real product. The live site (WordPress + Code Snippets + Quiz Maker + WooCommerce) stays untouched.

## Rules

- Before starting any task, give the user an estimate of the token usage it will take (a rough range plus the main cost drivers: files to read/write, builds, browser checks). Wait for the user's approval before doing the work. Do not start until they agree.

- Frontend code only. No backend, no database, no server-side code.
- Never call the live site (https://catest.ca), its REST API, or wp-admin from this mock.
- Never put credentials, API keys, or anything from `../.env` or `../.browser-profile/` in this folder.
- All data shown (provinces, test categories, questions, users, packages) is mock data stored inside this folder.
- Everything in the deployed build is public. Do not include anything that must stay private.
- Keep it simple: a small React app that builds to static files Netlify can serve.
- All code, comments, and docs in this folder are written in English. Report to the user in Vietnamese.

## Stack

- Vite 5 + React 18, plain JavaScript (no TypeScript).
- Plain CSS (`src/App.css`), no CSS framework.
- No router yet. Add React Router only when the mock needs more than one page.
- Dependency versions are pinned exactly in `package.json`. Do not use `latest`.

## Commands (run inside `mock/`)

- `npm install` - install dependencies
- `npm run dev` - local dev server
- `npm run build` - build static files into `dist/`
- `npm run preview` - serve the built `dist/` at http://localhost:4173

## Netlify

- `netlify.toml` holds the build command (`npm run build`), publish dir (`dist`), Node version (22) and the SPA redirect (`/*` -> `/index.html`).
- Deploy option 1: run `npm run build` and drag `mock/dist` into https://app.netlify.com/drop.
- Deploy option 2: connect the Git repo and set Base directory = `mock`.
- Do not deploy unless the user asks.

## To Be Decided

- Screens and flows to include.
- Source and amount of sample question data.
