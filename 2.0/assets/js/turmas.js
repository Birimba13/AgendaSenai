let turmas = [];
let turmaEditando = null;

document.addEventListener('DOMContentLoaded', function() {
    carregarTurmas();
});

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
        
        return `
            <div class="turma-card">
                <div class="card-header">
                    <h3>${turma.nome}</h3>
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <span class="info-label">Período:</span>
                        <span class="info-value">${dataInicio} - ${dataFim}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Disciplinas:</span>
                        <span class="info-value">${turma.total_disciplinas} disciplinas</span>
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
    
    const turmasFiltradas = turmas.filter(turma => {
        const matchBusca = busca === '' || turma.nome.toLowerCase().includes(busca);
        const matchStatus = status === '' || turma.status === status;
        
        return matchBusca && matchStatus;
    });
    
    renderizarTurmas(turmasFiltradas);
}

function abrirModal() {
    turmaEditando = null;
    document.getElementById('modal').classList.add('active');
    document.getElementById('modalTitulo').textContent = 'Adicionar Turma';
    document.getElementById('formTurma').reset();
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
    document.getElementById('dataInicio').value = turma.data_inicio;
    document.getElementById('dataFim').value = turma.data_fim;
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
    
    const dados = {
        nome: document.getElementById('nomeTurma').value.trim(),
        data_inicio: document.getElementById('dataInicio').value,
        data_fim: document.getElementById('dataFim').value
    };
    
    if (turmaEditando) {
        dados.id = turmaEditando.id;
    }
    
    if (!dados.nome) {
        alert('Nome é obrigatório!');
        return;
    }
    
    if (!dados.data_inicio) {
        alert('Data de início é obrigatória!');
        return;
    }
    
    if (!dados.data_fim) {
        alert('Data de fim é obrigatória!');
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