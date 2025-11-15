document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('login-form');
  if (!form) return;

  const submitBtn = form.querySelector('button[type="submit"]');

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    const email = form.querySelector('#email').value.trim();
    const password = form.querySelector('#password').value;

    if (!email || !password) {
      if (typeof window.showToast === 'function') {
        window.showToast('Por favor completa correo y contraseña', { type: 'error', duration: 4000 });
      } else {
        alert('Por favor completa correo y contraseña');
      }
      return;
    }

    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.classList.add('opacity-70');
    }

    try {
      const res = await fetch('/api/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ email, password })
      });

      const data = await res.json();

      if (res.ok && data.token) {
        localStorage.setItem('token', data.token);
        if (typeof window.showToast === 'function') {
          window.showToast('Inicio de sesión correcto', { type: 'success', duration: 2000 });
        }
        setTimeout(() => {
          window.location.href = '/dashboard';
        }, 900);
      } else {
        let message = '';
        if (data && data.error) {
          if (data.error === 'Invalid credentials') {
            message = 'Credenciales inválidas';
          } else {
            message = data.error;
          }
        } else if (data && data.errors) {
          message = Object.values(data.errors).flat().join('; ');
        } else {
          message = 'Credenciales inválidas';
        }
        if (typeof window.showToast === 'function') {
          window.showToast(message, { type: 'error', duration: 6000 });
        } else {
          alert(message);
        }
      }
    } catch (err) {
      if (typeof window.showToast === 'function') {
        window.showToast('Error de red. Intenta de nuevo.', { type: 'error', duration: 6000 });
      } else {
        alert('Error de red. Intenta de nuevo.');
      }
    } finally {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.classList.remove('opacity-70');
      }
    }
  });
});
