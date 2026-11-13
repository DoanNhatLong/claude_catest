import { useEffect } from 'react';
import { Link } from 'react-router-dom';
import { PROVINCES, practicePath } from './data.js';

// Province picker shown when a "Try practice test for free" button is clicked.
export default function ProvinceModal({ onClose }) {
  useEffect(() => {
    const onKeyDown = (event) => {
      if (event.key === 'Escape') onClose();
    };
    document.addEventListener('keydown', onKeyDown);
    document.body.style.overflow = 'hidden';
    return () => {
      document.removeEventListener('keydown', onKeyDown);
      document.body.style.overflow = '';
    };
  }, [onClose]);

  return (
    <div className="modal-backdrop" onClick={onClose}>
      <div
        className="modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="province-modal-title"
        onClick={(event) => event.stopPropagation()}
      >
        <button type="button" className="modal-close" aria-label="Close" onClick={onClose}>×</button>
        <h2 id="province-modal-title" className="modal-title">Select your province</h2>
        <p className="modal-lead">Choose the province or territory where you will take your knowledge test.</p>
        <div className="modal-grid">
          {PROVINCES.map((province) => (
            <Link className="modal-province" to={practicePath(province.code)} key={province.code}>
              <span className="modal-code">{province.code}</span>
              {province.name}
            </Link>
          ))}
        </div>
      </div>
    </div>
  );
}
