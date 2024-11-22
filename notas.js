document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.nota-titulo').forEach(button => {
        button.addEventListener('click', () => {
            const content = button.nextElementSibling;
            const isOpen = button.classList.contains('active');

            // Recolher todas as outras
            document.querySelectorAll('.nota-conteudo').forEach(c => {
                c.style.display = 'none';
            });
            document.querySelectorAll('.nota-titulo').forEach(b => {
                b.classList.remove('active');
            });

            // Expandir o atual se não estiver aberto
            if (!isOpen) {
                content.style.display = 'block';
                button.classList.add('active');
            }
        });
    });
});
