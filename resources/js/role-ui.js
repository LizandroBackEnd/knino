document.addEventListener('DOMContentLoaded', async () => {
  // hide role-restricted items by default (any element that has a data-role attribute)
  document.querySelectorAll('[data-role]').forEach(el => el.classList.add('hidden'));

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
    // set header/sidebar username from the API response (if present)
    try {
      const name = (function () {
        if (!user) return 'Invitado';
        if (user.full_name) return user.full_name;
        // build full name from parts when full_name isn't available
        const parts = [user.name, user.last_name_primary, user.last_name_secondary].filter(Boolean);
        if (parts.length) return parts.join(' ');
        return user.name || 'Invitado';
      })();

      const headerEl = document.getElementById('header-username');
      if (headerEl) headerEl.textContent = name;
      const sidebarEl = document.getElementById('sidebar-username');
      if (sidebarEl) sidebarEl.textContent = name;
    } catch (e) { console.warn('role-ui: could not set username', e); }

    const role = user && user.role ? String(user.role).toLowerCase() : '';
    // reveal elements whose data-role list includes the current role
    document.querySelectorAll('[data-role]').forEach(el => {
      try {
        const attr = (el.getAttribute('data-role') || '').toLowerCase();
        const allowed = attr.split(',').map(s => s.trim()).filter(Boolean);
        if (allowed.length === 0) return;
        if (allowed.includes(role)) el.classList.remove('hidden');
        else el.classList.add('hidden');
      } catch(e) { console.warn('role-ui: error processing data-role', e); }
    });
  } catch (err) {
    console.error('role-ui: could not fetch user', err);
  }
});
