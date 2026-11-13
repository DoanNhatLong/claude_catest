import { Link, useParams } from 'react-router-dom';
import { findProvince } from './data.js';

// Placeholder for the test selection page. Real content will come later.
export default function PracticePage() {
  const { code } = useParams();
  const province = findProvince(code);

  return (
    <main className="container">
      <div className="content-card practice-page">
        {province ? (
          <>
            <span className="practice-code">{province.code}</span>
            <h1 className="section-title">Test selection — {province.name}</h1>
            <p>
              This is the test selection page for <strong>{province.name}</strong> (region code{' '}
              <strong>{province.code}</strong>). Content is coming soon.
            </p>
          </>
        ) : (
          <>
            <h1 className="section-title">Province not found</h1>
            <p>The region code “{code}” does not match any Canadian province or territory.</p>
          </>
        )}
        <Link className="btn btn-solid" to="/">← Back to home</Link>
      </div>
    </main>
  );
}
