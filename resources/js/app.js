import './bootstrap';
import initFormModals from './form';
import confirmDelete from './alertDelete';
import initModalBreed from './modalBreed';

document.addEventListener('DOMContentLoaded', () => {
	initFormModals();
	try { initModalBreed(); } catch (err) { console.warn('modalBreed init failed', err); }
});

document.addEventListener('dashboard:navigated', (e) => {
	try {
		initFormModals();
	} catch (err) {
		console.warn('Failed to re-init form modals after navigation', err);
	}
		try { initModalBreed(); } catch (err) { console.warn('modalBreed re-init failed', err); }
});

if (typeof window !== 'undefined') {
  window.confirmDelete = confirmDelete;
}
