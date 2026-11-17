import { Link } from 'react-router-dom';

// Basic placeholder page for footer links. Edit the content in INFO_PAGES (data.js).
export default function InfoPage({ page }) {
  return (
    <main className="container">
      <div className="content-card practice-page">
        <h1 className="section-title">{page.title}</h1>
        <p>{page.body}</p>
        <Link className="btn btn-solid" to="/">← Back to home</Link>
      </div>
    </main>
  );
}
