// des quune action sur le bouton parcourir est demandee, on montre ou cache les liens
function toggleParcourir() {
  // recupere la div contenant les liens a afficher ou masquer
  const links = document.getElementById('parcourirLinks');
  // recupere le bouton qui controle la visibilite des liens
  const btn = document.querySelector('button[aria-controls="parcourirLinks"]');
  // verifie si les liens sont deja visibles
  const isExpanded = links.style.display === 'block';
  // si visible alors on cache, sinon on affiche
  links.style.display = isExpanded ? 'none' : 'block';
  // si le bouton existe, on met a jour l attribut aria-expanded
  if (btn) btn.setAttribute('aria-expanded', !isExpanded);
}

// des quune action sur le bouton recherche est demandee, on montre ou cache le formulaire
function toggleRecherche() {
  // recupere le conteneur du formulaire de recherche
  const container = document.getElementById('rechercheContainer');
  // si le formulaire est visible on le cache, sinon on le montre
  container.style.display = container.style.display === 'block' ? 'none' : 'block';
}
