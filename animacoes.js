
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
