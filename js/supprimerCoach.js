document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById('supprimerCoachForm');
  const notification = document.getElementById('notification');

  // des qu on soumet le formulaire on annule son comportement par defaut
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    // recupere la valeur de l id a supprimer
    const id = document.getElementById('id').value;

    // appel ajax vers le script php pour supprimer le coach
    fetch('../../php/coach/supprimerCoach.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      // envoie l id encodé dans le body
      body: `id=${encodeURIComponent(id)}`
    })
        .then(response => response.text())
        .then(data => {
          // affiche le message de retour en cas de succes
          notification.innerHTML = `<div style="background:#dff0d8; color:#3c763d; padding:15px; border-radius:5px;">${data}</div>`;
        })
        .catch(err => {
          // en cas d erreur d appel ajax, affiche le message d erreur
          console.error("erreur ajax :", err);
          notification.innerHTML = `<div style="background:#f2dede; color:#a94442; padding:15px; border-radius:5px;">erreur lors de la suppression du coach.</div>`;
        });
  });
});
