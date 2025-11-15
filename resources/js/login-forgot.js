document.addEventListener('DOMContentLoaded', function () {
  const forgot = document.getElementById('forgot-password-link');
  if (!forgot) return;

  forgot.addEventListener('click', function (e) {
    e.preventDefault();
    if (typeof window.showToast === 'function') {
      window.showToast('Contáctate con el administrador para restablecer tu contraseña', { type: 'info', duration: 6000 });
    } else {
      alert('Contáctate con el administrador para restablecer tu contraseña');
    }
  });
});
