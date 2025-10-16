function abrirModal() {
    document.getElementById('modal').classList.add('active');
    document.getElementById('modalTitulo').textContent = 'Adicionar Professor';
    document.getElementById('formProfessor').reset();
}

function fecharModal() {
    document.getElementById('modal').classList.remove('active');
}

function editarProfessor(id) {
    document.getElementById('modal').classList.add('active');
    document.getElementById('modalTitulo').textContent = 'Editar Professor';
    // Aqui você carregaria os dados do professor
    alert('Editando professor ID: ' + id);
}

function excluirProfessor(id) {
    if (confirm('Tem certeza que deseja excluir este professor?')) {
        alert('Professor excluído: ' + id);
        // Aqui você faria a exclusão
    }
}

function filtrarTabela() {
    const busca = document.getElementById('busca').value.toLowerCase();
    const turno = document.getElementById('filtroTurno').value;
    const status = document.getElementById('filtroStatus').value;

    const linhas = document.querySelectorAll('#tabelaProfessores tbody tr');

    linhas.forEach(linha => {
        const texto = linha.textContent.toLowerCase();
        let mostrar = true;

        if (busca && !texto.includes(busca)) {
            mostrar = false;
        }

        if (turno && !texto.includes(turno)) {
            mostrar = false;
        }

        if (status && !texto.includes(status)) {
            mostrar = false;
        }

        linha.style.display = mostrar ? '' : 'none';
    });
}

document.getElementById('formProfessor').addEventListener('submit', function (e) {
    e.preventDefault();
    alert('Professor salvo com sucesso!');
    fecharModal();
});

// Fechar modal ao clicar fora
document.getElementById('modal').addEventListener('click', function (e) {
    if (e.target === this) {
        fecharModal();
    }
});