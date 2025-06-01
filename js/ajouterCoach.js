document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById('ajouterCoachForm');
  const notification = document.getElementById('notification');

  // des que le formulaire est soumis on intercepte pour utiliser ajax
  form.addEventListener('submit', function (e) {
    e.preventDefault();

    // recupere les donnees du formulaire
    const formData = new FormData(form);

    // envoi des donnees vers php/coach/ajouterCoach.php en methode post
    fetch('../../php/coach/ajouterCoach.php', {
      method: 'POST',
      body: formData
    })
        // recupere la reponse texte du serveur
        .then(response => response.text())
        .then(data => {
          // affiche la notification de succes
          notification.innerHTML = `<div style="background:#dff0d8; color:#3c763d; padding:15px; border-radius:5px;">${data}</div>`;
        })
        .catch(err => {
          // affiche la notification d erreur si la requete ajax echoue
          console.error("erreur ajax :", err);
          notification.innerHTML = `<div style="background:#f2dede; color:#a94442; padding:15px; border-radius:5px;">erreur lors de la jout du coach.</div>`;
        });
  });
});
