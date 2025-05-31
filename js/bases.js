function toggleParcourir() {
  const links = document.getElementById('parcourirLinks');
  const btn = document.querySelector('button[aria-controls="parcourirLinks"]');
  const isExpanded = links.style.display === 'block';
  links.style.display = isExpanded ? 'none' : 'block';
  if (btn) btn.setAttribute('aria-expanded', !isExpanded);
}

function toggleRecherche() {
  const container = document.getElementById('rechercheContainer');
  container.style.display = container.style.display === 'block' ? 'none' : 'block';
}
