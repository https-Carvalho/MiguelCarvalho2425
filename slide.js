let slider = document.querySelector('.slider .list');
let items = document.querySelectorAll('.slider .list .item');
let next = document.getElementById('next');
let prev = document.getElementById('prev');
let dots = document.querySelectorAll('.slider .dots li');

let active = 0; // Índice da imagem atual
let lengthItems = items.length;

// Função para mudar o slider
function mudarSlide(index) {
    active = index;

    // Move o slider
    slider.style.transform = `translateX(-${active * 100}%)`;

    // Atualiza os *dots*
    dots.forEach(dot => dot.classList.remove('active'));
    dots[active].classList.add('active');
}

// Botões de navegação
next.addEventListener('click', () => {
    active = (active + 1) % lengthItems; // Próximo índice
    mudarSlide(active);
});

prev.addEventListener('click', () => {
    active = (active - 1 + lengthItems) % lengthItems; // Índice anterior
    mudarSlide(active);
});

// Navegação pelos *dots*
dots.forEach((dot, index) => {
    dot.addEventListener('click', () => mudarSlide(index));
});

// Atualiza automaticamente
setInterval(() => {
    active = (active + 1) % lengthItems;
    mudarSlide(active);
}, 3000); // Tempo em milissegundos
