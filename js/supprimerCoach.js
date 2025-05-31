document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById('supprimerCoachForm');
  const notification = document.getElementById('notification');

  form.addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('id').value;

    // Appel AJAX vers le bon script PHP
    fetch('../../php/coach/supprimerCoach.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: `id=${encodeURIComponent(id)}`
    })
    .then(response => response.text())
    .then(data => {
      notification.innerHTML = `<div style="background:#dff0d8; color:#3c763d; padding:15px; border-radius:5px;">${data}</div>`;
    })
    .catch(err => {
      console.error("Erreur AJAX :", err);
      notification.innerHTML = `<div style="background:#f2dede; color:#a94442; padding:15px; border-radius:5px;">Erreur lors de la suppression du coach.</div>`;
    });
  });
});
