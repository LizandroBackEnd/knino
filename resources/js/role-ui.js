document.addEventListener('DOMContentLoaded', async () => {
  // hide admin-only items by default
  document.querySelectorAll('[data-role="admin"]').forEach(el => el.classList.add('hidden'));

  const token = localStorage.getItem('token');
  if (!token) return;

  try {
    const res = await fetch('/api/user', {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Authorization': 'Bearer ' + token
      }
    });

    if (!res.ok) return;
    const user = await res.json();
    const role = user && user.role ? String(user.role).toLowerCase() : '';

    if (role === 'admin') {
      document.querySelectorAll('[data-role="admin"]').forEach(el => el.classList.remove('hidden'));
    } else {
      document.querySelectorAll('[data-role="admin"]').forEach(el => el.classList.add('hidden'));
    }
  } catch (err) {
    console.error('role-ui: could not fetch user', err);
  }
});
