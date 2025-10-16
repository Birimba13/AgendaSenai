function abrirModal() {
    document.getElementById('modal').classList.add('active');
    document.getElementById('modalTitulo').textContent = 'Adicionar Disciplina';
    document.getElementById('formDisciplina').reset();
}

function fecharModal() {
    document.getElementById('modal').classList.remove('active');
}

function editarDisciplina(id) {
    document.getElementById('modal').classList.add('active');
    document.getElementById('modalTitulo').textContent = 'Editar Disciplina';
    alert('Editando disciplina ID: ' + id);
}

function excluirDisciplina(id) {
    if (confirm('Tem certeza que deseja excluir esta disciplina?')) {
        alert('Disciplina excluída: ' + id);
    }
}

function filtrarDisciplinas() {
    const busca = document.getElementById('busca').value.toLowerCase();
    const cards = document.querySelectorAll('.disciplina-card');

    cards.forEach(card => {
        const texto = card.textContent.toLowerCase();
        card.style.display = texto.includes(busca) ? '' : 'none';
    });
}

document.getElementById('formDisciplina').addEventListener('submit', function (e) {
    e.preventDefault();
    alert('Disciplina salva com sucesso!');
    fecharModal();
});

document.getElementById('modal').addEventListener('click', function (e) {
    if (e.target === this) {
        fecharModal();
    }
});