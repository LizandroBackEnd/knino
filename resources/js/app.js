import './bootstrap';
import initFormModals from './form';
import confirmDelete from './alertDelete';

document.addEventListener('DOMContentLoaded', () => {
	initFormModals();
});

document.addEventListener('dashboard:navigated', (e) => {
	try {
		initFormModals();
	} catch (err) {
		console.warn('Failed to re-init form modals after navigation', err);
	}
});

if (typeof window !== 'undefined') {
  window.confirmDelete = confirmDelete;
}
