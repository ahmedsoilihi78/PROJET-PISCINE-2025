document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById('ajouterCoachForm');
  const notification = document.getElementById('notification');

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(form);

    fetch('../../php/coach/ajouterCoach.php', {
      method: 'POST',
      body: formData
    })
      .then(response => response.text())
      .then(data => {
        notification.innerHTML = `<div style="background:#dff0d8; color:#3c763d; padding:15px; border-radius:5px;">${data}</div>`;
      })
      .catch(err => {
        console.error("Erreur AJAX :", err);
        notification.innerHTML = `<div style="background:#f2dede; color:#a94442; padding:15px; border-radius:5px;">Erreur lors de l’ajout du coach.</div>`;
      });
  });
});
