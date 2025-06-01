document.addEventListener("DOMContentLoaded", function () {
  // recupere le bouton services et la section correspondante
  const servicesBtn = document.getElementById('servicesBtn');
  const servicesSection = document.getElementById('servicesSection');
  // recupere la zone ou s affichera le contenu des sous sections
  const contentArea = document.getElementById('serviceContent');

  // des qu on clique sur le bouton services on montre ou cache la section services
  servicesBtn.addEventListener('click', () => {
    // si la section est deja visible on la cache et on vide le contenu
    if (servicesSection.style.display === 'block') {
      servicesSection.style.display = 'none';
      contentArea.innerHTML = '';
    } else {
      // sinon on l affiche
      servicesSection.style.display = 'block';
    }
  });

  // memoire du dernier type clique pour gerer l ouverture/fermeture du contenu
  let lastClickedType = null;

  // fonction appelee depuis le html pour afficher le contenu selon le type clique
  window.showContent = function(type) {
    // si on reclique sur le meme type et que le contenu est deja affiche on le vide
    if (lastClickedType === type && contentArea.innerHTML.trim() !== '') {
      contentArea.innerHTML = '';
      lastClickedType = null;
      return;
    }

    // sinon on met a jour le type en memoire
    lastClickedType = type;
    let html = '';

    // selon le type on prepare le contenu a afficher
    switch (type) {
      case 'horaire':
        // contenu pour afficher le planning de la salle
        html = `
          <table class="gym-schedule">
            <thead>
              <tr><th>jour</th><th>ouverture</th><th>fermeture</th></tr>
            </thead>
            <tbody>
              <tr><td>lundi</td><td>07:00</td><td>22:00</td></tr>
              <tr><td>mardi</td><td>07:00</td><td>22:00</td></tr>
              <tr><td>mercredi</td><td>07:00</td><td>22:00</td></tr>
              <tr><td>jeudi</td><td>07:00</td><td>22:00</td></tr>
              <tr><td>vendredi</td><td>07:00</td><td>22:00</td></tr>
              <tr><td>samedi</td><td>08:00</td><td>18:00</td></tr>
              <tr><td>dimanche</td><td colspan="2">ferme</td></tr>
            </tbody>
          </table>`;
        break;

      case 'regles':
        // contenu pour afficher les regles d utilisation des machines
        html = `
          <div class="machine-rules-text">
            <p>voici les regles d utilisation des machines :</p>
            <ul>
              <li>utiliser une serviette personnelle</li>
              <li>reinitialiser les reglages des machines</li>
              <li>nettoyer apres usage</li>
              <li>ranger les accessoires</li>
              <li>30 min max sur cardio en periode de pointe</li>
              <li>ne pas jeter les halteres</li>
              <li>utiliser des ecouteurs uniquement</li>
              <li>respecter le calme</li>
              <li>signaler tout probleme a un responsable</li>
            </ul>
          </div>`;
        break;

      case 'nutrition':
        // contenu pour afficher la galerie de produits nutritionnels
        html = `
          <div class="nutrition-gallery">
            <div class="product">
              <img src="../images_salle_de_sport/whey.jpg" alt="whey">
              <p>whey proteine</p>
            </div>
            <div class="product">
              <img src="../images_salle_de_sport/barres.jpg" alt="barres proteinees">
              <p>barres proteinees</p>
            </div>
            <div class="product">
              <img src="../images_salle_de_sport/shaker.jpg" alt="shaker">
              <p>shaker sportif</p>
            </div>
            <div class="product">
              <img src="../images_salle_de_sport/preworkout.jpg" alt="pre-workout">
              <p>pre-workout booster</p>
            </div>
            <div class="product">
              <img src="../images_salle_de_sport/bcaa.jpg" alt="bcaa">
              <p>bcaa acides amines</p>
            </div>
          </div>`;
        break;
    }

    // on insere le html generate dans la zone de contenu
    contentArea.innerHTML = html;
  };
});
