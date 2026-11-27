let urlFotoAnterior = null;
function previewFoto(event) {
    const imagem = document.getElementById('preview');
    if (!imagem) return;
    const arquivo = event.target.files[0];
    if (!arquivo) return;
    if (urlFotoAnterior) URL.revokeObjectURL(urlFotoAnterior);
    urlFotoAnterior = URL.createObjectURL(arquivo);
    imagem.src = urlFotoAnterior;
    imagem.style.display = 'block';
    const texto = document.getElementById('semFoto');
    if (texto) texto.style.display = 'none';
}
document.addEventListener('DOMContentLoaded', function () {
    const deleteModal = document.getElementById('delete-modal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            const botao = event.relatedTarget;
            const campoId = deleteModal.querySelector('#delete-id');
            const titulo = deleteModal.querySelector('#modalLabel');
            if (campoId && botao) campoId.value = botao.getAttribute('data-id') || '';
            if (titulo && botao) {
                const contexto = deleteModal.getAttribute('data-modal-context') || titulo.textContent.trim();
                const nome = botao.getAttribute('data-nome');
                titulo.textContent = nome
                    ? `${contexto} ? - ${nome}`.toLocaleUpperCase('pt-BR')
                    : contexto.toLocaleUpperCase('pt-BR');
            }
        });
    }
});

// Máscara de telefone: funciona no cadastro e edição.
function mascaraTelefone(campo) {
    const numeros = campo.value.replace(/\D/g, '').slice(0, 11);
    if (!numeros) { campo.value = ''; return; }
    if (numeros.length <= 2) { campo.value = '(' + numeros; return; }
    const ddd = numeros.slice(0, 2);
    const restante = numeros.slice(2);
    const inicio = numeros.length === 11 ? 5 : 4;
    campo.value = '(' + ddd + ') ' + restante.slice(0, inicio) +
        (restante.length > inicio ? '-' + restante.slice(inicio) : '');
}
document.addEventListener('input', function (e) {
    if (e.target.matches('input[name="telefone"]')) mascaraTelefone(e.target);
});
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('input[name="telefone"]').forEach(mascaraTelefone);

    // Prévia de foto de clientes, usuários e administradores.
    const campo = document.getElementById('foto');
    const imagem = document.getElementById('previewFoto');
    const atual = document.getElementById('fotoAtual');
    if (!campo || !imagem) return;
    let urlTemporaria = null;
    campo.addEventListener('change', function () {
        const arquivo = campo.files[0];
        if (urlTemporaria) { URL.revokeObjectURL(urlTemporaria); urlTemporaria = null; }
        if (!arquivo) {
            imagem.style.display = 'none';
            imagem.removeAttribute('src');
            if (atual) atual.style.display = '';
            return;
        }
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(arquivo.type) || arquivo.size > 2 * 1024 * 1024) {
            alert('Escolha uma foto JPG, PNG ou WEBP de até 2 MB.');
            campo.value = '';
            imagem.style.display = 'none';
            imagem.removeAttribute('src');
            if (atual) atual.style.display = '';
            return;
        }
        urlTemporaria = URL.createObjectURL(arquivo);
        imagem.src = urlTemporaria;
        imagem.style.display = 'block';
        if (atual) atual.style.display = 'none';
    });
});
