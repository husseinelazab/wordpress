document.addEventListener('DOMContentLoaded', () => {
  const cards = document.querySelectorAll('.metric-card strong');
  cards.forEach((card) => {
    card.animate([{ opacity: 0.4, transform: 'translateY(6px)' }, { opacity: 1, transform: 'translateY(0)' }], {
      duration: 500,
      easing: 'ease-out',
      fill: 'forwards',
    });
  });
});
