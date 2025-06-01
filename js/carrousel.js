const track = document.querySelector('.carousel-track');
const btnPrev = document.querySelector('.carousel-btn.prev');
const btnNext = document.querySelector('.carousel-btn.next');
const dotsContainer = document.querySelector('.carousel-dots');
const slides = track.children;
const totalSlides = slides.length;

let index = 0;
let interval;

// met a jour la position du carousel et les points
function updateCarousel() {
  // translate le track pour afficher la diapositive courante
  track.style.transform = `translateX(-${index * 100}%)`;
  // met a jour la classe active pour chaque point
  dots.forEach((dot, i) => {
    dot.classList.toggle('active', i === index);
  });
  // relance ou remet a zero le defilement automatique
  resetAutoSlide();
}

// initialise ou remet a zero l intervalle de defilement automatique
function resetAutoSlide() {
  // arrete l intervalle precedent si existant
  clearInterval(interval);
  // cree un nouvel intervalle pour defiler toutes les 5 secondes
  interval = setInterval(() => {
    index = (index + 1) % totalSlides;
    updateCarousel();
  }, 5000);
}

// cree les points pour chaque diapositive
for (let i = 0; i < totalSlides; i++) {
  const dot = document.createElement('span');
  dot.classList.add('dot');
  // ajoute la classe active au premier point
  if (i === 0) dot.classList.add('active');
  // ajoute un ecouteur pour aller a la diapositive correspondante au clic
  dot.addEventListener('click', () => {
    index = i;
    updateCarousel();
  });
  dotsContainer.appendChild(dot);
}

const dots = dotsContainer.querySelectorAll('.dot');

// ecouteur pour passer a la diapositive suivante
btnNext.addEventListener('click', () => {
  index = (index + 1) % totalSlides;
  updateCarousel();
});

// ecouteur pour passer a la diapositive precedente
btnPrev.addEventListener('click', () => {
  index = (index - 1 + totalSlides) % totalSlides;
  updateCarousel();
});

// lance la fonction de mise a jour au demarrage
updateCarousel();
