const LAST_UPDATED = 'November 12, 2026';

export default function App() {
  return (
    <main className="notice">
      <div className="notice-card">
        <div className="brand">
          <span className="brand-mark" aria-hidden="true">✓</span>
          <span className="brand-name">Canada driving tests</span>
        </div>

        <span className="badge">Preview</span>

        <h1>Demo coming soon</h1>

        <p>
          We are preparing an interactive preview of the new practice test experience.
          This page will soon let you walk through the main screens and share your feedback.
        </p>

        <p className="updated">Last updated: {LAST_UPDATED}</p>
      </div>
    </main>
  );
}
