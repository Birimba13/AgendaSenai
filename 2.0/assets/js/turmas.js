let turmas = [];
let disciplinas = [];
let professores = [];
let turmaEditando = null;

document.addEventListener('DOMContentLoaded', function() {
    carregarProfessores();
    carregarDisciplinas();
    carregarTurmas();
});

// Carregar professores
async function carregarProfessores() {
    try {
        const response = await fetch('../api/get_professores.php');
        const result = await response.json();
        
        if (result.success) {
            professores = result.data;
            preencherSelectProfessores();
            preencherFiltroProfessores();
        }
    } catch (error) {
        console.error('Erro ao carregar professores:', error);
    }
}

// Preencher select de professores no modal
function preencherSelectProfessores() {
    const select = document.getElementById('professorId');
    select.innerHTML = '<option value="">Selecione um professor</option>';
    
    professores.forEach(prof => {
        const option = document.createElement('option');
        option.value = prof.id;
        option.textContent = prof.nome;
        select.appendChild(option);
    });
}

// Preencher filtro de professores
function preencherFiltroProfessores() {
    const select = document.getElementById('filtroProfessor');
    if (!select) return;
    
    select.innerHTML = '<option value="">Todos</option>';
    
    professores.forEach(prof => {
        const option = document.createElement('option');
        option.value = prof.id;
        option.textContent = prof.nome;
        select.appendChild(option);
    });
}

// Carregar disciplinas
async function carregarDisciplinas() {
    try {
        const response = await fetch('../api/get_disciplinas.php');
        const result = await response.json();
        
        if (result.success) {
            disciplinas = result.data;
            preencherSelectDisciplinas();
            preencherFiltroDisciplinas();
        }
    } catch (error) {
        console.error('Erro ao carregar disciplinas:', error);
    }
}

// Preencher select de disciplinas no modal
function preencherSelectDisciplinas() {
    const select = document.getElementById('disciplinas');
    select.innerHTML = '';
    
    if (disciplinas.length === 0) {
        const option = document.createElement('option');
        option.textContent = 'Nenhuma disciplina cadastrada';
        option.disabled = true;
        select.appendChild(option);
        return;
    }
    
    disciplinas.forEach(disc => {
        const option = document.createElement('option');
        option.value = disc.id;
        option.textContent = `${disc.nome} (${disc.sigla})`;
        select.appendChild(option);
    });
}

// Preencher filtro de disciplinas
function preencherFiltroDisciplinas() {
    const select = document.getElementById('filtroDisciplina');
    select.innerHTML = '<option value="">Todas</option>';
    
    disciplinas.forEach(disc => {
        const option = document.createElement('option');
        option.value = disc.id;
        option.textContent = disc.nome;
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

function renderizarTurmas(listaTurmas) {
    const container = document.getElementById('turmasContainer');
    
    if (listaTurmas.length === 0) {
        container.innerHTML = '<div style="text-align: center; padding: 60px; grid-column: 1/-1;"><h3>Nenhuma turma encontrada</h3><p>Tente ajustar os filtros</p></div>';
        return;
    }
    
    container.innerHTML = listaTurmas.map(turma => {
        const dataInicio = new Date(turma.data_inicio + 'T00:00:00').toLocaleDateString('pt-BR');
        const dataFim = new Date(turma.data_fim + 'T00:00:00').toLocaleDateString('pt-BR');
        
        const statusBadge = {
            'ativo': '<span class="badge badge-ativo">Ativo</span>',
            'concluido': '<span class="badge badge-concluido">Concluído</span>',
            'aguardando': '<span class="badge" style="background: #fff3cd; color: #856404;">Aguardando</span>'
        };
        
        const totalDisciplinas = turma.total_disciplinas || 0;

        return `
            <div class="turma-card">
                <div class="card-header">
                    <h3>${turma.nome}</h3>
                    <p>Professor: ${turma.professor_nome}</p>
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <span class="info-label">Período:</span>
                        <span class="info-value">${dataInicio} - ${dataFim}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Disciplinas:</span>
                        <span class="info-value">${totalDisciplinas} disciplinas</span> 
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status:</span>
                        ${statusBadge[turma.status] || statusBadge['ativo']}
                    </div>
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
    const status = document.getElementById('filtroStatus').value;
    const disciplinaId = document.getElementById('filtroDisciplina').value;
    const professorId = document.getElementById('filtroProfessor')?.value || '';

    const turmasFiltradas = turmas.filter(turma => {
        const matchBusca = busca === '' || turma.nome.toLowerCase().includes(busca);
        const matchStatus = status === '' || turma.status === status;
        const matchProfessor = professorId === '' || turma.professor_id.toString() === professorId;
        
        let matchDisciplina = true;
        if (disciplinaId !== '') {
             if (turma.disciplinas_ids) {
                const turmaDisciplinaIds = String(turma.disciplinas_ids).split(',');
                matchDisciplina = turmaDisciplinaIds.includes(disciplinaId);
             } else {
                 matchDisciplina = false; 
             }
        }
        
        return matchBusca && matchStatus && matchDisciplina && matchProfessor;
    });
    
    renderizarTurmas(turmasFiltradas);
}

function abrirModal() {
    turmaEditando = null;
    document.getElementById('modal').classList.add('active');
    document.getElementById('modalTitulo').textContent = 'Adicionar Turma';
    document.getElementById('formTurma').reset();
    document.querySelectorAll('#disciplinas option').forEach(option => {
        option.selected = false;
    });
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
    
    document.getElementById('nomeTurma').value = turma.nome;
    document.getElementById('professorId').value = turma.professor_id;
    document.getElementById('dataInicio').value = turma.data_inicio;
    document.getElementById('dataFim').value = turma.data_fim;

    const selectDisciplinas = document.getElementById('disciplinas');
    const idsVinculados = turma.disciplinas_ids ? String(turma.disciplinas_ids).split(',') : [];

    selectDisciplinas.querySelectorAll('option').forEach(option => {
        option.selected = idsVinculados.includes(option.value);
    });
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
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}`
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
    
    const selectElement = document.getElementById('disciplinas');
    const selectedDisciplinas = Array.from(selectElement.selectedOptions).map(option => option.value);

    const dados = {
        nome: document.getElementById('nomeTurma').value.trim(),
        professor_id: document.getElementById('professorId').value,
        data_inicio: document.getElementById('dataInicio').value,
        data_fim: document.getElementById('dataFim').value,
        disciplinas: selectedDisciplinas 
    };
    
    if (turmaEditando) {
        dados.id = turmaEditando.id;
    }

    console.log("Enviando para a API:", dados); 
    
    if (!dados.nome || !dados.professor_id || !dados.data_inicio || !dados.data_fim) {
        alert('Nome, professor, data de início e data de fim são obrigatórios!');
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