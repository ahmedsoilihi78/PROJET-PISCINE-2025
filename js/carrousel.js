const track = document.querySelector('.carousel-track');
const btnPrev = document.querySelector('.carousel-btn.prev');
const btnNext = document.querySelector('.carousel-btn.next');
const dotsContainer = document.querySelector('.carousel-dots');
const slides = track.children;
const totalSlides = slides.length;

let index = 0;
let interval;

function updateCarousel() {
  track.style.transform = `translateX(-${index * 100}%)`;
  dots.forEach((dot, i) => {
    dot.classList.toggle('active', i === index);
  });
  resetAutoSlide();
}

function resetAutoSlide() {
  clearInterval(interval);
  interval = setInterval(() => {
    index = (index + 1) % totalSlides;
    updateCarousel();
  }, 5000);
}

// Crée les dots
for (let i = 0; i < totalSlides; i++) {
  const dot = document.createElement('span');
  dot.classList.add('dot');
  if (i === 0) dot.classList.add('active');
  dot.addEventListener('click', () => {
    index = i;
    updateCarousel();
  });
  dotsContainer.appendChild(dot);
}

const dots = dotsContainer.querySelectorAll('.dot');

btnNext.addEventListener('click', () => {
  index = (index + 1) % totalSlides;
  updateCarousel();
});

btnPrev.addEventListener('click', () => {
  index = (index - 1 + totalSlides) % totalSlides;
  updateCarousel();
});

updateCarousel();
