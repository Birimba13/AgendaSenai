let turmas = [];
let cursos = [];
let turmaEditando = null;

document.addEventListener('DOMContentLoaded', function() {
    carregarCursos();
    carregarTurmas();
});

// Carregar cursos
async function carregarCursos() {
    try {
        const response = await fetch('../api/get_cursos.php');
        const result = await response.json();

        if (result.success) {
            cursos = result.data;
            preencherSelectCursos();
            preencherFiltroCursos();
        }
    } catch (error) {
        console.error('Erro ao carregar cursos:', error);
    }
}

// Preencher select de cursos no modal
function preencherSelectCursos() {
    const select = document.getElementById('curso');
    select.innerHTML = '<option value="">Selecione um curso</option>';

    const cursosAtivos = cursos.filter(c => c.ativo);
    cursosAtivos.forEach(curso => {
        const option = document.createElement('option');
        option.value = curso.id;
        option.textContent = curso.nome;
        select.appendChild(option);
    });
}

// Preencher filtro de cursos
function preencherFiltroCursos() {
    const select = document.getElementById('filtroCurso');
    select.innerHTML = '<option value="">Todos</option>';

    cursos.forEach(curso => {
        const option = document.createElement('option');
        option.value = curso.id;
        option.textContent = curso.nome;
        select.appendChild(option);
    });
}

async function carregarTurmas() {
    const container = document.getElementById('turmasContainer');

    try {
        container.innerHTML = '<div style="text-align: center; padding: 40px; grid-column: 1/-1;"><div style="font-size: 2rem; color: #0a2342;">⏳</div><br>Carregando turmas...</div>';

        const response = await fetch('../api/get_turmas.php');
        const result = await response.json();

        if (result.success && result.data.length > 0) {
            turmas = result.data;
            renderizarTurmas(turmas);
        } else {
            container.innerHTML = '<div style="text-align: center; padding: 60px; grid-column: 1/-1;"><h3 style="color: #0a2342;">Nenhuma turma encontrada</h3><p style="color: #666;">Clique em "Adicionar Turma" para começar</p></div>';
        }
    } catch (error) {
        console.error('Erro ao carregar turmas:', error);
        container.innerHTML = '<div style="text-align: center; padding: 60px; grid-column: 1/-1;"><h3 style="color: #dc3545;">⚠️ Erro ao carregar turmas</h3><p>Tente novamente mais tarde</p></div>';
    }
}

function formatarTurno(turno) {
    const turnos = {
        'Manha': 'Manhã',
        'Tarde': 'Tarde',
        'Noite': 'Noite',
        'Integral': 'Integral'
    };
    return turnos[turno] || turno;
}

function formatarStatus(status) {
    const statuses = {
        'planejamento': 'Planejamento',
        'ativo': 'Ativo',
        'concluido': 'Concluído',
        'cancelado': 'Cancelado'
    };
    return statuses[status] || status;
}

function renderizarTurmas(listaTurmas) {
    const container = document.getElementById('turmasContainer');

    if (listaTurmas.length === 0) {
        container.innerHTML = '<div style="text-align: center; padding: 60px; grid-column: 1/-1;"><h3>Nenhuma turma encontrada</h3><p>Tente ajustar os filtros</p></div>';
        return;
    }

    container.innerHTML = listaTurmas.map(turma => {
        const dataInicio = new Date(turma.data_inicio + 'T00:00:00').toLocaleDateString('pt-BR');
        const dataFim = new Date(turma.data_fim + 'T00:00:00').toLocaleDateString('pt-BR');

        const statusBadgeClass = {
            'planejamento': 'badge-planejamento',
            'ativo': 'badge-ativo',
            'concluido': 'badge-concluido',
            'cancelado': 'badge-inativo'
        };

        const totalAlunos = turma.total_alunos || 0;

        return `
            <div class="turma-card">
                <div class="card-header">
                    <div>
                        <h3>${turma.nome}</h3>
                        <p style="color: #666; margin-top: 5px;">
                            <strong>Curso:</strong> ${turma.curso_nome || 'N/A'}
                        </p>
                    </div>
                    <span class="badge ${statusBadgeClass[turma.status] || 'badge-ativo'}">
                        ${formatarStatus(turma.status)}
                    </span>
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <span class="info-label">Turno:</span>
                        <span class="info-value">${formatarTurno(turma.turno)}</span>
                    </div>
                    ${turma.periodo ? `<div class="info-row">
                        <span class="info-label">Período:</span>
                        <span class="info-value">${turma.periodo}</span>
                    </div>` : ''}
                    <div class="info-row">
                        <span class="info-label">Data:</span>
                        <span class="info-value">${dataInicio} - ${dataFim}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Alunos:</span>
                        <span class="info-value">${totalAlunos} matriculados</span>
                    </div>
                    ${!turma.ativo ? '<div class="info-row"><span class="badge badge-inativo">Inativa</span></div>' : ''}
                </div>
                <div class="card-footer">
                    <button class="btn-acao btn-editar" onclick="editarTurma(${turma.id})">Editar</button>
                    <button class="btn-acao btn-excluir" onclick="excluirTurma(${turma.id})">Excluir</button>
                </div>
            </div>
        `;
    }).join('');
}

function filtrarTurmas() {
    const busca = document.getElementById('busca').value.toLowerCase();
    const cursoId = document.getElementById('filtroCurso').value;
    const turno = document.getElementById('filtroTurno').value;
    const status = document.getElementById('filtroStatus').value;

    const turmasFiltradas = turmas.filter(turma => {
        const matchBusca = busca === '' || turma.nome.toLowerCase().includes(busca);
        const matchCurso = cursoId === '' || turma.curso_id == cursoId;
        const matchTurno = turno === '' || turma.turno === turno;
        const matchStatus = status === '' || turma.status === status;

        return matchBusca && matchCurso && matchTurno && matchStatus;
    });

    renderizarTurmas(turmasFiltradas);
}

function abrirModal() {
    turmaEditando = null;
    document.getElementById('modal').classList.add('active');
    document.getElementById('modalTitulo').textContent = 'Adicionar Turma';
    document.getElementById('formTurma').reset();
    document.getElementById('status').value = 'ativo';
    document.getElementById('ativo').value = '1';
}

function fecharModal() {
    document.getElementById('modal').classList.remove('active');
    turmaEditando = null;
}

function editarTurma(id) {
    const turma = turmas.find(t => t.id === id);

    if (!turma) {
        alert('Turma não encontrada!');
        return;
    }

    turmaEditando = turma;

    document.getElementById('modal').classList.add('active');
    document.getElementById('modalTitulo').textContent = 'Editar Turma';

    document.getElementById('curso').value = turma.curso_id || '';
    document.getElementById('nomeTurma').value = turma.nome;
    document.getElementById('periodo').value = turma.periodo || '';
    document.getElementById('turno').value = turma.turno || '';
    document.getElementById('dataInicio').value = turma.data_inicio;
    document.getElementById('dataFim').value = turma.data_fim;
    document.getElementById('status').value = turma.status || 'ativo';
    document.getElementById('ativo').value = turma.ativo ? '1' : '0';
    document.getElementById('observacoes').value = turma.observacoes || '';
}

async function excluirTurma(id) {
    const turma = turmas.find(t => t.id === id);

    if (!turma) {
        alert('Turma não encontrada!');
        return;
    }

    if (!confirm(`Tem certeza que deseja excluir a turma "${turma.nome}"?\n\nEsta ação não pode ser desfeita.`)) {
        return;
    }

    try {
        const response = await fetch('../api/delete_turmas.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id })
        });

        const result = await response.json();

        if (result.success) {
            alert(result.message);
            carregarTurmas();
        } else {
            alert('Erro: ' + result.message);
        }
    } catch (error) {
        console.error('Erro ao excluir turma:', error);
        alert('Erro ao excluir turma. Tente novamente.');
    }
}

document.getElementById('formTurma').addEventListener('submit', async function (e) {
    e.preventDefault();

    const dados = {
        nome: document.getElementById('nomeTurma').value.trim(),
        curso_id: parseInt(document.getElementById('curso').value),
        periodo: document.getElementById('periodo').value.trim() || null,
        turno: document.getElementById('turno').value,
        data_inicio: document.getElementById('dataInicio').value,
        data_fim: document.getElementById('dataFim').value,
        status: document.getElementById('status').value,
        ativo: parseInt(document.getElementById('ativo').value),
        observacoes: document.getElementById('observacoes').value.trim() || null
    };

    if (turmaEditando) {
        dados.id = turmaEditando.id;
    }

    // Validações
    if (!dados.nome) {
        alert('Nome é obrigatório!');
        return;
    }

    if (!dados.curso_id) {
        alert('Curso é obrigatório!');
        return;
    }

    if (!dados.turno) {
        alert('Turno é obrigatório!');
        return;
    }

    if (!dados.data_inicio || !dados.data_fim) {
        alert('Data de início e data de fim são obrigatórias!');
        return;
    }

    if (new Date(dados.data_fim) <= new Date(dados.data_inicio)) {
        alert('Data de fim deve ser maior que data de início!');
        return;
    }

    try {
        const response = await fetch('../api/save_turmas.php', {
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
            carregarTurmas();
        } else {
            alert('Erro: ' + result.message);
        }
    } catch (error) {
        console.error('Erro ao salvar turma:', error);
        alert('Erro ao salvar turma. Tente novamente.');
    }
});

document.getElementById('modal').addEventListener('click', function (e) {
    if (e.target === this) {
        fecharModal();
    }
});
