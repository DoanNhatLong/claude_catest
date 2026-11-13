import { PROVINCES, STEPS, FOOTER_COLUMNS } from './data.js';

function Placeholder({ label, className = '' }) {
  return (
    <div className={`placeholder ${className}`} role="img" aria-label={label}>
      <span>{label}</span>
    </div>
  );
}

function Header() {
  return (
    <header className="site-header">
      <div className="container header-inner">
        <a className="brand" href="#top">
          <span className="brand-check" aria-hidden="true">✓</span>
          Canada driving tests
        </a>

        <div className="guest-info">
          <div>
            Hi, <span className="link-blue">Guest</span> • You have <strong className="text-red">3</strong> free tests left
          </div>
          <div className="guest-sub">
            Unlock unlimited access, <a href="#premium">click here to upgrade</a>
          </div>
        </div>

        <div className="header-actions">
          <a className="pill pill-blue" href="#login">👤 Premium Login</a>
          <a className="pill pill-green" href="#guarantee">💎 Pass Guarantee</a>
        </div>
      </div>
    </header>
  );
}

function Hero() {
  return (
    <section className="hero">
      <h1>
        Canadian driving test practice that <span className="text-accent">trains your brain</span>, not just your memory
      </h1>
      <p>
        Handbook pages won’t dodge black ice. Real exam-like questions, plain-English explanations and hazard
        video clips turn nerves into calm confidence – so the real test feels like déjà vu.
      </p>
      <a className="btn btn-solid btn-lg" href="#start">Try practice test for free</a>
    </section>
  );
}

function Steps() {
  return (
    <section className="steps">
      <h2 className="section-title">3 steps to Practice Driving Knowledge Test That Actually Works</h2>
      <p className="steps-lead">
        The Smarter &amp; Faster Way to Study for the Drivers Knowledge Test. It just gives you an idea of the
        topics that will be covered in the driver knowledge test, but you shouldn’t rely on it alone to pass the test.
      </p>
      <div className="step-grid">
        {STEPS.map((step) => (
          <a className="step-card" href="#step" key={step.title}>
            <div className="step-media">
              <Placeholder label={step.image} />
            </div>
            <div className="step-body">
              <h3>{step.title}</h3>
              <p>{step.text}</p>
            </div>
          </a>
        ))}
      </div>
    </section>
  );
}

function Introduce() {
  return (
    <section className="introduce">
      <h2 className="section-title">Introduce</h2>
      <div className="introduce-grid">
        <div className="card province-card">
          <div className="province-grid">
            {PROVINCES.map((name) => (
              <a className="province-btn" href="#province" key={name}>{name}</a>
            ))}
          </div>
        </div>
        <div className="card map-card">
          <span className="must-try">👑 MUST-TRY</span>
          <Placeholder label="Map of Canada" className="map-placeholder" />
          <a className="start-now" href="#start">Start Practice Now →</a>
        </div>
      </div>
    </section>
  );
}

function Local() {
  return (
    <section className="local">
      <div className="local-text">
        <span className="eyebrow">Based on the driver’s handbook</span>
        <h2 className="section-title">Always current, always local</h2>
        <h3>Specific to your province</h3>
        <p>
          We regularly review provincial handbooks and licensing guidance and update affected material when
          rules change – so you’re never memorising yesterday’s law.
        </p>
      </div>
      <div className="local-visual">
        <div className="rating-badge">⭐ Canada’s #1 online driver training program</div>
        <Placeholder label="Select Your Region screenshot" className="region-placeholder" />
      </div>
    </section>
  );
}

function ReadyToStart() {
  return (
    <section className="ready">
      <h2 className="section-title">Ready to start ?</h2>
      <a className="btn btn-solid" href="#start">Try practice test for free</a>
      <Placeholder label="Laptop showing a practice test" className="laptop-placeholder" />
    </section>
  );
}

function Footer() {
  return (
    <footer className="site-footer">
      <div className="container">
        <p className="footer-tagline">
          Forging road-ready Canadians one practice question at a time — because black ice won’t forgive guesses.
        </p>
        <div className="footer-cols">
          {FOOTER_COLUMNS.map((col) => (
            <div key={col.title}>
              <h4>{col.title}</h4>
              <ul>
                {col.links.map((link) => (
                  <li key={link}><a href="#footer">{link}</a></li>
                ))}
              </ul>
            </div>
          ))}
        </div>
      </div>
    </footer>
  );
}

export default function App() {
  return (
    <div id="top">
      <Header />
      <main className="container">
        <div className="content-card">
          <Hero />
          <Steps />
          <Introduce />
          <Local />
          <ReadyToStart />
        </div>
      </main>
      <Footer />
    </div>
  );
}
