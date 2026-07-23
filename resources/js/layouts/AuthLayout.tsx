import React from 'react';

interface AuthLayoutProps {
  children: React.ReactNode;
  /** Optional heading shown above the form (e.g. Change Password) */
  heading?: string;
  subheading?: string;
}

/**
 * Modern split-panel auth shell — white form panel + blurred campus imagery.
 */
export default function AuthLayout({ children, heading, subheading }: AuthLayoutProps) {
  return (
    <div className="auth-shell">
      {/* Left: blurred institutional background */}
      <div className="auth-visual" aria-hidden="true">
        <div className="auth-visual-bg" />
        <div className="auth-visual-overlay" />
        <div className="auth-visual-caption">
          <p className="auth-visual-tag">Board Management System</p>
          <h2 className="auth-visual-title">
            Board of Intermediate &amp; Secondary Education
          </h2>
          <p className="auth-visual-location">Sukkur, Sindh, Pakistan</p>
        </div>
      </div>

      {/* Right: clean white login panel */}
      <div className="auth-panel">
        <div className="auth-panel-inner">
          <div className="auth-brand">
            <img
              src="/images/bise-sukkur-logo.png"
              alt="BISE Sukkur Logo"
              className="auth-logo-image"
            />
            <h1 className="auth-title">BISE Sukkur</h1>
            <p className="auth-subtitle">
              Secure portal for authorized board personnel
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
            <p>Authorized personnel only.</p>
            <p>For access issues, contact BISE Sukkur IT department.</p>
          </footer>
        </div>
      </div>
    </div>
  );
}
