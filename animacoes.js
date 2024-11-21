
// Exemplo de funcionalidade JavaScript, caso queira adicionar algo interativo no futuro
document.addEventListener("DOMContentLoaded", () => {
    console.log("Página carregada e perfumes exibidos com efeitos por estação.");
});

  
function mudaImagem(id) {
  document.querySelector(`#img-${id}`).src = `<?php echo $perfume["caminho_imagem_hover"]; ?>`;
}

function voltaImagem(id) {
  document.querySelector(`#img-${id}`).src = `<?php echo $perfume["caminho_imagem"]; ?>`;
}

// carrossel para as imagens aparecem em carrossel\
function inicializarSlider() {
  let slider = document.querySelector('.slider .list');
  let items = document.querySelectorAll('.slider .list .item');
  let next = document.getElementById('next');
  let prev = document.getElementById('  ');
  let dots = document.querySelectorAll('.slider .dots li');

  let lengthItems = items.length - 1;
  let active = 0;

  next.onclick = function () {
      active = active + 1 <= lengthItems ? active + 1 : 0;
      reloadSlider();
  };
  prev.onclick = function () {
      active = active - 1 >= 0 ? active - 1 : lengthItems;
      reloadSlider();
  };

  let refreshInterval = setInterval(() => {
      next.click();
  }, 3000);

  function reloadSlider() {
      slider.style.left = -items[active].offsetLeft + 'px';

      let last_active_dot = document.querySelector('.slider .dots li.active');
      if (last_active_dot) {
          last_active_dot.classList.remove('active');
      }
      dots[active].classList.add('active');

      clearInterval(refreshInterval);
      refreshInterval = setInterval(() => {
          next.click();
      }, 3000);
  }

  dots.forEach((li, key) => {
      li.addEventListener('click', () => {
          active = key;
          reloadSlider();
      });
  });

  window.onresize = function (event) {
      reloadSlider();
  };

  // Inicializa o slider na primeira carga
  reloadSlider();
}

