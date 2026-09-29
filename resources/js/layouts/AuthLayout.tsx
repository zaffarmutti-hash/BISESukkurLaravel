import React from 'react';

interface AuthLayoutProps {
  children: React.ReactNode;
  /** Optional heading shown above the form (e.g. Change Password) */
  heading?: string;
  subheading?: string;
}

/**
 * Figma-inspired premium split-panel auth shell.
 */
export default function AuthLayout({ children, heading, subheading }: AuthLayoutProps) {
  return (
    <div className="auth-shell">
      {/* Left: Rich Institutional Visual Side */}
      <div className="auth-visual" aria-hidden="true">
        {/* Glow lights & ambient mesh */}
        <div className="auth-visual-glow-1" />
        <div className="auth-visual-glow-2" />
        <div className="auth-visual-grid-pattern" />

        <div className="auth-visual-content">
          {/* Header Tag */}
          <div className="auth-visual-header">
            <span className="auth-visual-gov-tag">
              <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" />
              Government of Sindh • Education Department
            </span>
          </div>

          {/* Main Visual Title */}
          <div className="auth-visual-hero">
            <h2 className="auth-visual-title">
              Board of Intermediate &amp; Secondary Education
            </h2>
            <p className="auth-visual-subtitle">
              Sukkur, Sindh — Central Examination &amp; Candidate Enrollment Portal
            </p>
          </div>

          {/* Glassmorphism Feature Cards */}
          <div className="auth-visual-cards">
            <div className="auth-glass-card">
              <div className="auth-glass-icon bg-blue-500/20 text-blue-400">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="w-5 h-5">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" />
                  <path strokeLinecap="round" strokeLinejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                </svg>
              </div>
              <div>
                <h4 className="auth-glass-title">Online Candidate Enrollment</h4>
                <p className="auth-glass-desc">Real-time SSC &amp; HSC student registration &amp; verification</p>
              </div>
            </div>

            <div className="auth-glass-card">
              <div className="auth-glass-icon bg-amber-500/20 text-amber-400">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="w-5 h-5">
                  <rect x="2" y="5" width="20" height="14" rx="2" />
                  <line x1="2" y1="10" x2="22" y2="10" />
                </svg>
              </div>
              <div>
                <h4 className="auth-glass-title">Automated Challan Clearing</h4>
                <p className="auth-glass-desc">Instant bank invoice verification &amp; payment audit trail</p>
              </div>
            </div>

            <div className="auth-glass-card">
              <div className="auth-glass-icon bg-emerald-500/20 text-emerald-400">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="w-5 h-5">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
              </div>
              <div>
                <h4 className="auth-glass-title">Roll &amp; Seat Allotment Engine</h4>
                <p className="auth-glass-desc">Secure examination center allocation and gap reporting</p>
              </div>
            </div>
          </div>

          <div className="auth-visual-footer">
            <span>© 2026 BISE Sukkur • All Rights Reserved</span>
          </div>
        </div>
      </div>

      {/* Right: Clean & Elevated Auth Panel */}
      <div className="auth-panel">
        <div className="auth-panel-card">
          <div className="auth-brand">
            <div className="auth-logo-badge">
              <img
                src="/images/bise-sukkur-logo.png"
                alt="BISE Sukkur Logo"
                className="auth-logo-image"
              />
            </div>
            <h1 className="auth-title">BISE Sukkur Portal</h1>
            <p className="auth-subtitle">
              Sign in to manage enrollment, exam forms &amp; verification
            </p>
          </div>

          {(heading || subheading) && (
            <div className="auth-page-heading">
              {heading && <h2 className="auth-page-title">{heading}</h2>}
              {subheading && <p className="auth-page-subtitle">{subheading}</p>}
            </div>
          )}

          <div className="auth-form">{children}</div>

          <footer className="auth-footer">
            <div className="auth-security-badge">
              <svg className="w-3.5 h-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0110 0v4"/>
              </svg>
              <span>256-Bit SSL Encrypted Connection</span>
            </div>
            <p className="auth-footer-contact">For technical assistance, contact BISE Sukkur IT Division</p>
          </footer>
        </div>
      </div>
    </div>
  );
}

