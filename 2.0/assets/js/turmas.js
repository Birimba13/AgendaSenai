function abrirModal() {
    document.getElementById('modal').classList.add('active');
    document.getElementById('modalTitulo').textContent = 'Adicionar Turma';
    document.getElementById('formTurma').reset();
}

function fecharModal() {
    document.getElementById('modal').classList.remove('active');
}

function verDetalhes(id) {
    alert('Visualizando detalhes da turma ID: ' + id);
}

function editarTurma(id) {
    document.getElementById('modal').classList.add('active');
    document.getElementById('modalTitulo').textContent = 'Editar Turma';
    alert('Editando turma ID: ' + id);
}

function excluirTurma(id) {
    if (confirm('Tem certeza que deseja excluir esta turma?')) {
        alert('Turma excluída: ' + id);
    }
}

function filtrarTurmas() {
    const busca = document.getElementById('busca').value.toLowerCase();
    const curso = document.getElementById('filtroCurso').value;
    const turno = document.getElementById('filtroTurno').value;
    const status = document.getElementById('filtroStatus').value;

    const cards = document.querySelectorAll('.turma-card');

    cards.forEach(card => {
        const texto = card.textContent.toLowerCase();
        let mostrar = true;

        if (busca && !texto.includes(busca)) mostrar = false;
        if (curso && !texto.includes(curso)) mostrar = false;
        if (turno && !texto.includes(turno)) mostrar = false;
        if (status && !texto.includes(status)) mostrar = false;

        card.style.display = mostrar ? '' : 'none';
    });
}

document.getElementById('formTurma').addEventListener('submit', function (e) {
    e.preventDefault();
    alert('Turma salva com sucesso!');
    fecharModal();
});

document.getElementById('modal').addEventListener('click', function (e) {
    if (e.target === this) {
        fecharModal();
    }
});