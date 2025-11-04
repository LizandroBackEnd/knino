import './bootstrap';
import initFormModals from './form';

document.addEventListener('DOMContentLoaded', () => {
	initFormModals();
});

// Re-initialize behaviors after client-side dashboard navigation
document.addEventListener('dashboard:navigated', (e) => {
	// Re-run form modal bindings so newly injected DOM elements get handlers
	try {
		initFormModals();
	} catch (err) {
		console.warn('Failed to re-init form modals after navigation', err);
	}
});
