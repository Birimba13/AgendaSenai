// Variável global para armazenar professores
let professores = [];
let professorEditando = null;

// Carrega professores quando a página carrega
document.addEventListener('DOMContentLoaded', function() {
    carregarProfessores();
});

// Função para carregar professores do banco
async function carregarProfessores() {
    const tbody = document.querySelector('#tabelaProfessores tbody');

    try {
        // Mostra loading
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px;"><div style="font-size: 2rem; color: #0a2342;">⏳</div><br>Carregando professores...</td></tr>';

        // Busca dados da API
        const response = await fetch('../api/get_professores.php');
        const result = await response.json();

        if (result.success && result.data.length > 0) {
            professores = result.data;
            renderizarProfessores(professores);
        } else {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px;">Nenhum professor encontrado. <button class="btn-acao btn-editar" onclick="abrirModal()" style="margin-top: 10px;">Adicionar Primeiro Professor</button></td></tr>';
        }
    } catch (error) {
        console.error('Erro ao carregar professores:', error);
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px; color: #dc2626;">⚠️<br><br>Erro ao carregar professores. Tente novamente mais tarde.</td></tr>';
    }
}

// Função para renderizar professores na tabela
function renderizarProfessores(listaProfessores) {
    const tbody = document.querySelector('#tabelaProfessores tbody');

    if (listaProfessores.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px;">Nenhum professor encontrado.</td></tr>';
        return;
    }
    
    tbody.innerHTML = listaProfessores.map(prof => `
        <tr>
            <td>${prof.nome}</td>
            <td>${prof.email}</td>
            <td>${prof.local_lotacao || 'Afonso Pena'}</td>
            <td>
                ${prof.turnos.map(turno =>
                    `<span class="badge badge-turno">${turno}</span>`
                ).join(' ')}
            </td>
            <td>${prof.carga_horaria_usada}h / ${prof.carga_horaria_semanal}h</td>
            <td>
                <span class="badge ${prof.ativo ? 'badge-ativo' : 'badge-inativo'}">
                    ${prof.ativo ? 'Ativo' : 'Inativo'}
                </span>
            </td>
            <td>
                <div class="acoes">
                    <button class="btn-acao btn-editar" onclick="editarProfessor(${prof.id})">Editar</button>
                    <button class="btn-acao btn-excluir" onclick="excluirProfessor(${prof.id})">Excluir</button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Função para filtrar tabela
function filtrarTabela() {
    const busca = document.getElementById('busca').value.toLowerCase();
    const turno = document.getElementById('filtroTurno').value;
    const status = document.getElementById('filtroStatus').value;
    
    const professoresFiltrados = professores.filter(prof => {
        // Filtro de busca
        const matchBusca = busca === '' || 
            prof.nome.toLowerCase().includes(busca) || 
            prof.email.toLowerCase().includes(busca);
        
        // Filtro de turno
        let matchTurno = true;
        if (turno === 'manha') {
            matchTurno = prof.turno_manha;
        } else if (turno === 'tarde') {
            matchTurno = prof.turno_tarde;
        } else if (turno === 'noite') {
            matchTurno = prof.turno_noite;
        }
        
        // Filtro de status
        let matchStatus = true;
        if (status === 'ativo') {
            matchStatus = prof.ativo;
        } else if (status === 'inativo') {
            matchStatus = !prof.ativo;
        }
        
        return matchBusca && matchTurno && matchStatus;
    });
    
    renderizarProfessores(professoresFiltrados);
}

// Função para abrir modal (adicionar)
function abrirModal() {
    professorEditando = null;
    document.getElementById('modal').classList.add('active');
    document.getElementById('modalTitulo').textContent = 'Adicionar Professor';
    document.getElementById('formProfessor').reset();
}

// Função para fechar modal
function fecharModal() {
    document.getElementById('modal').classList.remove('active');
    professorEditando = null;
}

// Função para editar professor
function editarProfessor(id) {
    const professor = professores.find(p => p.id === id);

    if (!professor) {
        alert('Professor não encontrado!');
        return;
    }

    professorEditando = professor;

    document.getElementById('modal').classList.add('active');
    document.getElementById('modalTitulo').textContent = 'Editar Professor';

    // Preenche o formulário com os dados do professor
    document.getElementById('nome').value = professor.nome;
    document.getElementById('email').value = professor.email;
    document.getElementById('cargaHoraria').value = professor.carga_horaria_semanal;
    document.getElementById('celular').value = professor.celular || '';
    document.getElementById('localLotacao').value = professor.local_lotacao || 'Afonso Pena';
    document.getElementById('turnoManha').checked = professor.turno_manha;
    document.getElementById('turnoTarde').checked = professor.turno_tarde;
    document.getElementById('turnoNoite').checked = professor.turno_noite;
    document.getElementById('status').value = professor.ativo ? 'ativo' : 'inativo';
}

// Função para excluir professor
async function excluirProfessor(id) {
    const professor = professores.find(p => p.id === id);
    
    if (!professor) {
        alert('Professor não encontrado!');
        return;
    }
    
    if (!confirm(`Tem certeza que deseja excluir o professor "${professor.nome}"?\n\nEsta ação não pode ser desfeita.`)) {
        return;
    }
    
    try {
        const response = await fetch('../api/delete_professor.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}`
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            carregarProfessores(); // Recarrega a lista
        } else {
            alert('Erro: ' + result.message);
        }
    } catch (error) {
        console.error('Erro ao excluir professor:', error);
        alert('Erro ao excluir professor. Tente novamente.');
    }
}

// Função para salvar professor (criar ou atualizar)
document.getElementById('formProfessor').addEventListener('submit', async function (e) {
    e.preventDefault();

    // Coleta dados do formulário
    const dados = {
        nome: document.getElementById('nome').value.trim(),
        email: document.getElementById('email').value.trim(),
        carga_horaria: document.getElementById('cargaHoraria').value,
        celular: document.getElementById('celular').value.trim() || null,
        local_lotacao: document.getElementById('localLotacao').value.trim() || 'Afonso Pena',
        turno_manha: document.getElementById('turnoManha').checked ? 1 : 0,
        turno_tarde: document.getElementById('turnoTarde').checked ? 1 : 0,
        turno_noite: document.getElementById('turnoNoite').checked ? 1 : 0,
        status: document.getElementById('status').value
    };

    // Se estiver editando, adiciona o ID
    if (professorEditando) {
        dados.id = professorEditando.id;
    }
    
    // Validações
    if (!dados.nome) {
        alert('Nome é obrigatório!');
        return;
    }
    
    if (!dados.email) {
        alert('Email é obrigatório!');
        return;
    }
    
    if (!dados.turno_manha && !dados.turno_tarde && !dados.turno_noite) {
        alert('Selecione pelo menos um turno!');
        return;
    }
    
    if (!dados.carga_horaria || dados.carga_horaria < 0) {
        alert('Carga horária inválida!');
        return;
    }
    
    try {
        const response = await fetch('../api/save_professor.php', {
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
            carregarProfessores(); // Recarrega a lista
        } else {
            alert('Erro: ' + result.message);
        }
    } catch (error) {
        console.error('Erro ao salvar professor:', error);
        alert('Erro ao salvar professor. Tente novamente.');
    }
});

// Fechar modal ao clicar fora
document.getElementById('modal').addEventListener('click', function (e) {
    if (e.target === this) {
        fecharModal();
    }
});

// Aplicar máscara de telefone no campo celular
document.getElementById('celular')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');

    if (value.length <= 11) {
        if (value.length <= 10) {
            value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
            value = value.replace(/(\d)(\d{4})$/, '$1-$2');
        } else {
            value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
            value = value.replace(/(\d)(\d{4})$/, '$1-$2');
        }
        e.target.value = value;
    }
});