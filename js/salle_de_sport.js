document.addEventListener("DOMContentLoaded", function () {
  const servicesBtn = document.getElementById('servicesBtn');
  const servicesSection = document.getElementById('servicesSection');
  const contentArea = document.getElementById('serviceContent');

  // Toggle affichage section services
  servicesBtn.addEventListener('click', () => {
    if (servicesSection.style.display === 'block') {
      servicesSection.style.display = 'none';
      contentArea.innerHTML = '';
    } else {
      servicesSection.style.display = 'block';
    }
  });

  // Gestion des sous-sections
  let lastClickedType = null;

  window.showContent = function(type) {
    if (lastClickedType === type && contentArea.innerHTML.trim() !== '') {
      contentArea.innerHTML = '';
      lastClickedType = null;
      return;
    }

    lastClickedType = type;
    let html = '';

    switch (type) {
     

      case 'horaire':
        html = `
          <table class="gym-schedule">
            <thead>
              <tr><th>Jour</th><th>Ouverture</th><th>Fermeture</th></tr>
            </thead>
            <tbody>
              <tr><td>Lundi</td><td>07:00</td><td>22:00</td></tr>
              <tr><td>Mardi</td><td>07:00</td><td>22:00</td></tr>
              <tr><td>Mercredi</td><td>07:00</td><td>22:00</td></tr>
              <tr><td>Jeudi</td><td>07:00</td><td>22:00</td></tr>
              <tr><td>Vendredi</td><td>07:00</td><td>22:00</td></tr>
              <tr><td>Samedi</td><td>08:00</td><td>18:00</td></tr>
              <tr><td>Dimanche</td><td colspan="2">Fermé</td></tr>
            </tbody>
          </table>`;
        break;

      case 'regles':
        html = `
          <div class="machine-rules-text">
            <p>Voici les règles d'utilisation des machines :</p>
            <ul>
              <li>Utiliser une serviette personnelle.</li>
              <li>Réinitialiser les réglages des machines.</li>
              <li>Nettoyer après usage.</li>
              <li>Ranger les accessoires.</li>
              <li>30 min max sur cardio en période de pointe.</li>
              <li>Ne pas jeter les haltères.</li>
              <li>Utiliser des écouteurs uniquement.</li>
              <li>Respecter le calme.</li>
              <li>Signaler tout problème à un responsable.</li>
            </ul>
          </div>`;
        break;

   case 'nutrition':
  html = `
    <div class="nutrition-gallery">
      <div class="product">
        <img src="../images_salle_de_sport/whey.jpg" alt="Whey">
        <p>Whey Protéine</p>
      </div>
      <div class="product">
        <img src="../images_salle_de_sport/barres.jpg" alt="Barres Protéinées">
        <p>Barres Protéinées</p>
      </div>
      <div class="product">
        <img src="../images_salle_de_sport/shaker.jpg" alt="Shaker">
        <p>Shaker Sportif</p>
      </div>
      <div class="product">
        <img src="../images_salle_de_sport/preworkout.jpg" alt="Pré-Workout">
        <p>Pré-Workout Booster</p>
      </div>
      <div class="product">
        <img src="../images_salle_de_sport/bcaa.jpg" alt="BCAA">
        <p>BCAA Acides Aminés</p>
      </div>
    </div>`;
  break;
    }

    contentArea.innerHTML = html;
  };
});
