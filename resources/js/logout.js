document.addEventListener('DOMContentLoaded', function () {
  const logoutLink = document.getElementById('logout-link');
  if (!logoutLink) return;

  logoutLink.addEventListener('click', async function (e) {
    e.preventDefault();

    const token = localStorage.getItem('token');
    if (!token) {
      if (typeof window.showToast === 'function') {
        window.showToast('No hay sesión activa', { type: 'info', duration: 3000 });
      }
      // redirect to login
      window.location.href = '/';
      return;
    }

    try {
      const res = await fetch('/api/logout', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer ' + token
        }
      });

      if (res.ok) {
        localStorage.removeItem('token');
        if (typeof window.showToast === 'function') {
          window.showToast('Sesión cerrada', { type: 'success', duration: 2000 });
        }
        setTimeout(() => {
          window.location.href = '/';
        }, 900);
      } else {
        const data = await res.json().catch(() => ({}));
        const msg = data.message || data.error || 'Error al cerrar sesión';
        if (typeof window.showToast === 'function') {
          window.showToast(msg, { type: 'error', duration: 4000 });
        } else {
          alert(msg);
        }
      }
    } catch (err) {
      if (typeof window.showToast === 'function') {
        window.showToast('Error de red. Intenta nuevamente.', { type: 'error', duration: 4000 });
      } else {
        alert('Error de red. Intenta nuevamente.');
      }
    }
  });
});
