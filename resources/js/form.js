// Generic form/modal controller
// Usage: any trigger with class `js-open-form-modal` and attribute `data-modal-target="#modalId"`
// The modal should have an overlay with class `modal-overlay` and close buttons with `js-close-modal`.
export function initFormModals() {
  const triggers = document.querySelectorAll('.js-open-form-modal');
  const openModal = (modal) => {
    if (!modal) return;
    // Ensure element is displayed before manipulating classes so it doesn't flash
    modal.style.display = 'grid';
    // Force a reflow so the following class changes trigger transitions
    // eslint-disable-next-line no-unused-expressions
    modal.offsetHeight;
    modal.classList.remove('opacity-0', 'pointer-events-none');
    modal.classList.add('opacity-100');
    document.body.classList.add('overflow-hidden');
    // focus first focusable element after a short delay for transition
    setTimeout(() => {
      const first = modal.querySelector('input, select, textarea, button');
      if (first) first.focus();
    }, 120);
  };

  const closeModal = (modal) => {
    if (!modal) return;
    // Start fade-out
    modal.classList.add('opacity-0', 'pointer-events-none');
    modal.classList.remove('opacity-100');
    document.body.classList.remove('overflow-hidden');
    // After transition ends, hide the element to avoid it flashing before JS runs
    const onTransitionEnd = (e) => {
      if (e.target !== modal) return;
      modal.style.display = 'none';
      modal.removeEventListener('transitionend', onTransitionEnd);
    };
    modal.addEventListener('transitionend', onTransitionEnd);
  };

  triggers.forEach(btn => {
    const target = btn.dataset.modalTarget || btn.getAttribute('data-modal-target');
    if (!target) return;
    const modal = document.querySelector(target);
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      openModal(modal);
    });
  });

  // delegate close buttons and overlay clicks
  document.addEventListener('click', (e) => {
    const closeBtn = e.target.closest('.js-close-modal');
    if (closeBtn) {
      const modal = closeBtn.closest('[id]');
      closeModal(modal);
      return;
    }
    const overlay = e.target.closest('.modal-overlay');
    if (overlay) {
      const modal = overlay.closest('[id]');
      closeModal(modal);
      return;
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      // close any open modals
      document.querySelectorAll('.opacity-100').forEach(modal => {
        if (modal.classList.contains('modal-root')) {
          closeModal(modal);
        }
      });
    }
  });
}

export default initFormModals;
