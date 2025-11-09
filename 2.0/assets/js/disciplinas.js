let disciplinas = [];
let cursos = [];
let disciplinaEditando = null;

// Carrega disciplinas e cursos quando a página carrega
document.addEventListener('DOMContentLoaded', function() {
    carregarCursos();
    carregarDisciplinas();
});

// Função para carregar cursos
async function carregarCursos() {
    try {
        const response = await fetch('../api/get_cursos.php');
        const result = await response.json();

        if (result.success) {
            cursos = result.data;
            popularDropdownCursos();
        }
    } catch (error) {
        console.error('Erro ao carregar cursos:', error);
    }
}

// Função para popular dropdowns de cursos
function popularDropdownCursos() {
    const cursosAtivos = cursos.filter(c => c.ativo);

    // Dropdown do formulário
    const selectCurso = document.getElementById('curso');
    selectCurso.innerHTML = '<option value="">Selecione um curso</option>' +
        cursosAtivos.map(curso => `<option value="${curso.id}">${curso.nome}</option>`).join('');

    // Dropdown do filtro
    const filtroCurso = document.getElementById('filtroCurso');
    filtroCurso.innerHTML = '<option value="">Todos os cursos</option>' +
        cursos.map(curso => `<option value="${curso.id}">${curso.nome}</option>`).join('');
}

// Função para carregar disciplinas do banco
async function carregarDisciplinas() {
    const container = document.getElementById('disciplinasContainer');

    try {
        container.innerHTML = '<div style="text-align: center; padding: 40px;"><div style="font-size: 2rem; color: #0a2342;">⏳</div><br>Carregando disciplinas...</div>';

        const response = await fetch('../api/get_disciplinas.php');
        const result = await response.json();

        if (result.success && result.data.length > 0) {
            disciplinas = result.data;
            renderizarDisciplinas(disciplinas);
        } else {
            container.innerHTML = '<div class="empty-state"><h3>Nenhuma disciplina encontrada</h3><p>Clique em "Adicionar Disciplina" para começar</p></div>';
        }
    } catch (error) {
        console.error('Erro ao carregar disciplinas:', error);
        container.innerHTML = '<div class="empty-state"><h3>⚠️ Erro ao carregar disciplinas</h3><p>Tente novamente mais tarde</p></div>';
    }
}

// Função para renderizar disciplinas em cards
function renderizarDisciplinas(listaDisciplinas) {
    const container = document.getElementById('disciplinasContainer');
    
    if (listaDisciplinas.length === 0) {
        container.innerHTML = '<div class="empty-state"><h3>Nenhuma disciplina encontrada</h3><p>Tente ajustar os filtros</p></div>';
        return;
    }
    
    container.innerHTML = listaDisciplinas.map(disc => `
        <div class="disciplina-card">
            <div class="card-header">
                <div class="card-titulo">
                    <h3>${disc.nome}</h3>
                    <span class="card-sigla">${disc.sigla}</span>
                </div>
                <span class="badge ${disc.ativo ? 'badge-ativo' : 'badge-inativo'}">
                    ${disc.ativo ? 'Ativo' : 'Inativo'}
                </span>
            </div>
            <div class="card-info">
                <div class="info-item">
                    <strong>Curso:</strong> ${disc.curso_nome || 'N/A'}
                </div>
                <div class="info-item">
                    <strong>Carga Horária:</strong> ${disc.carga_horaria} horas
                </div>
                ${disc.descricao ? `<div class="info-item">
                    <strong>Descrição:</strong> ${disc.descricao}
                </div>` : ''}
            </div>
            <div class="card-acoes">
                <button class="btn-acao btn-editar" onclick="editarDisciplina(${disc.id})">Editar</button>
                <button class="btn-acao btn-excluir" onclick="excluirDisciplina(${disc.id})">Excluir</button>
            </div>
        </div>
    `).join('');
}

// Função para filtrar disciplinas
function filtrarDisciplinas() {
    const busca = document.getElementById('busca').value.toLowerCase();
    const cursoId = document.getElementById('filtroCurso').value;
    const status = document.getElementById('filtroStatus').value;

    const disciplinasFiltradas = disciplinas.filter(disc => {
        const matchBusca = busca === '' ||
            disc.nome.toLowerCase().includes(busca) ||
            disc.sigla.toLowerCase().includes(busca);

        const matchCurso = cursoId === '' || disc.curso_id == cursoId;

        const matchStatus = status === '' || disc.ativo == status;

        return matchBusca && matchCurso && matchStatus;
    });

    renderizarDisciplinas(disciplinasFiltradas);
}

// Função para abrir modal (adicionar)
function abrirModal() {
    disciplinaEditando = null;
    document.getElementById('modal').classList.add('active');
    document.getElementById('modalTitulo').textContent = 'Adicionar Disciplina';
    document.getElementById('formDisciplina').reset();
}

// Função para fechar modal
function fecharModal() {
    document.getElementById('modal').classList.remove('active');
    disciplinaEditando = null;
}

// Função para editar disciplina
function editarDisciplina(id) {
    const disciplina = disciplinas.find(d => d.id === id);

    if (!disciplina) {
        alert('Disciplina não encontrada!');
        return;
    }

    disciplinaEditando = disciplina;

    document.getElementById('modal').classList.add('active');
    document.getElementById('modalTitulo').textContent = 'Editar Disciplina';

    document.getElementById('curso').value = disciplina.curso_id || '';
    document.getElementById('nomeDisciplina').value = disciplina.nome;
    document.getElementById('sigla').value = disciplina.sigla;
    document.getElementById('cargaHoraria').value = disciplina.carga_horaria;
    document.getElementById('descricao').value = disciplina.descricao || '';
    document.getElementById('ativo').value = disciplina.ativo ? '1' : '0';
}

// Função para excluir disciplina
async function excluirDisciplina(id) {
    const disciplina = disciplinas.find(d => d.id === id);
    
    if (!disciplina) {
        alert('Disciplina não encontrada!');
        return;
    }
    
    if (!confirm(`Tem certeza que deseja excluir a disciplina "${disciplina.nome}"?\n\nEsta ação não pode ser desfeita.`)) {
        return;
    }
    
    try {
        const response = await fetch('../api/delete_disciplinas.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}`
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            carregarDisciplinas();
        } else {
            alert('Erro: ' + result.message);
        }
    } catch (error) {
        console.error('Erro ao excluir disciplina:', error);
        alert('Erro ao excluir disciplina. Tente novamente.');
    }
}

// Função para salvar disciplina (criar ou atualizar)
document.getElementById('formDisciplina').addEventListener('submit', async function (e) {
    e.preventDefault();

    const dados = {
        curso_id: parseInt(document.getElementById('curso').value),
        nome: document.getElementById('nomeDisciplina').value.trim(),
        sigla: document.getElementById('sigla').value.trim(),
        carga_horaria: parseInt(document.getElementById('cargaHoraria').value),
        descricao: document.getElementById('descricao').value.trim() || null,
        ativo: parseInt(document.getElementById('ativo').value)
    };

    if (disciplinaEditando) {
        dados.id = disciplinaEditando.id;
    }

    // Validações
    if (!dados.curso_id) {
        alert('Curso é obrigatório!');
        return;
    }

    if (!dados.nome) {
        alert('Nome é obrigatório!');
        return;
    }

    if (!dados.sigla) {
        alert('Sigla é obrigatória!');
        return;
    }

    if (!dados.carga_horaria || dados.carga_horaria < 1) {
        alert('Carga horária inválida!');
        return;
    }

    try {
        const response = await fetch('../api/save_disciplinas.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(dados)
        });

        const result = await response.json();

        if (result.success) {
            alert(result.message);
            fecharModal();
            carregarDisciplinas();
        } else {
            alert('Erro: ' + result.message);
        }
    } catch (error) {
        console.error('Erro ao salvar disciplina:', error);
        alert('Erro ao salvar disciplina. Tente novamente.');
    }
});

// Fechar modal ao clicar fora
document.getElementById('modal').addEventListener('click', function (e) {
    if (e.target === this) {
        fecharModal();
    }
});