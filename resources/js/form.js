// Minimal compatibility shim for legacy modal form initializer.
// The project previously imported `./form` from `resources/js/app.js`.
// In recent changes we migrated many flows to full-page forms and
// removed the original `form.js`, which caused Vite to fail resolving
// the import. Creating this file avoids the build error and provides
// a safe place to add modal initialization later if needed.

export default function initFormModals() {
  // No-op guard: if modal elements exist, attach handlers here.
  // Keep this lightweight to avoid side-effects during page loads.

  // Example placeholder (disabled by default):
  // const modalTriggers = document.querySelectorAll('[data-toggle="form-modal"]');
  // modalTriggers.forEach(btn => { /* attach listeners */ });

  return null;
}
