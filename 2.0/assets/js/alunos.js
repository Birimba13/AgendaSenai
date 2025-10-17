let alunos = [];
let turmas = [];
let alunoEditando = null;

document.addEventListener('DOMContentLoaded', function() {
    carregarTurmas();
    carregarAlunos();
    aplicarMascaras();
});

// Carrega turmas para os selects
async function carregarTurmas() {
    try {
        const response = await fetch('../api/get_turmas.php');
        const result = await response.json();
        
        if (result.success) {
            turmas = result.data;
            preencherSelectTurmas();
        }
    } catch (error) {
        console.error('Erro ao carregar turmas:', error);
    }
}

function preencherSelectTurmas() {
    const selectFiltro = document.getElementById('filtroCurso');
    const selectForm = document.getElementById('cursoId');
    
    // Preenche filtro
    selectFiltro.innerHTML = '<option value="">Todas</option>';
    turmas.forEach(turma => {
        selectFiltro.innerHTML += `<option value="${turma.id}">${turma.nome}</option>`;
    });
    
    // Preenche formulário
    selectForm.innerHTML = '<option value="">Sem turma</option>';
    turmas.forEach(turma => {
        selectForm.innerHTML += `<option value="${turma.id}">${turma.nome}</option>`;
    });
}

async function carregarAlunos() {
    const tbody = document.querySelector('#tabelaAlunos tbody');
    
    try {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px;"><div style="font-size: 2rem; color: #0a2342;">⏳</div><br>Carregando alunos...</td></tr>';
        
        const response = await fetch('../api/get_alunos.php');
        const result = await response.json();
        
        if (result.success && result.data.length > 0) {
            alunos = result.data;
            renderizarAlunos(alunos);
        } else {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px;">Nenhum aluno encontrado. <button class="btn-acao btn-editar" onclick="abrirModal()" style="margin-top: 10px;">Adicionar Primeiro Aluno</button></td></tr>';
        }
    } catch (error) {
        console.error('Erro ao carregar alunos:', error);
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px; color: #dc2626;">⚠️<br><br>Erro ao carregar alunos. Tente novamente mais tarde.</td></tr>';
    }
}

function renderizarAlunos(listaAlunos) {
    const tbody = document.querySelector('#tabelaAlunos tbody');
    
    if (listaAlunos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px;">Nenhum aluno encontrado.</td></tr>';
        return;
    }
    
    const statusBadge = {
        'ativo': 'badge-ativo',
        'inativo': 'badge-inativo',
        'concluido': 'badge badge-concluido',
        'trancado': 'badge'
    };
    
    const statusTexto = {
        'ativo': 'Ativo',
        'inativo': 'Inativo',
        'concluido': 'Concluído',
        'trancado': 'Trancado'
    };
    
    tbody.innerHTML = listaAlunos.map(aluno => {
        const dataMatricula = new Date(aluno.data_matricula + 'T00:00:00').toLocaleDateString('pt-BR');
        
        return `
        <tr>
            <td>${aluno.nome}</td>
            <td>${aluno.email}</td>
            <td>${aluno.cpf || '-'}</td>
            <td>${aluno.curso_nome || 'Sem turma'}</td>
            <td>${dataMatricula}</td>
            <td>
                <span class="badge ${statusBadge[aluno.status]}">
                    ${statusTexto[aluno.status]}
                </span>
            </td>
            <td>
                <div class="acoes">
                    <button class="btn-acao btn-editar" onclick="editarAluno(${aluno.id})">Editar</button>
                    <button class="btn-acao btn-excluir" onclick="excluirAluno(${aluno.id})">Excluir</button>
                </div>
            </td>
        </tr>
        `;
    }).join('');
}

function filtrarTabela() {
    const busca = document.getElementById('busca').value.toLowerCase();
    const cursoId = document.getElementById('filtroCurso').value;
    const status = document.getElementById('filtroStatus').value;
    
    const alunosFiltrados = alunos.filter(aluno => {
        // Filtro de busca
        const matchBusca = busca === '' || 
            aluno.nome.toLowerCase().includes(busca) || 
            aluno.email.toLowerCase().includes(busca) ||
            (aluno.cpf && aluno.cpf.includes(busca));
        
        // Filtro de curso
        const matchCurso = cursoId === '' || 
            (aluno.curso_id && aluno.curso_id.toString() === cursoId);
        
        // Filtro de status
        const matchStatus = status === '' || aluno.status === status;
        
        return matchBusca && matchCurso && matchStatus;
    });
    
    renderizarAlunos(alunosFiltrados);
}

function abrirModal() {
    alunoEditando = null;
    document.getElementById('modal').classList.add('active');
    document.getElementById('modalTitulo').textContent = 'Adicionar Aluno';
    document.getElementById('formAluno').reset();
    
    // Define data de matrícula padrão como hoje
    const hoje = new Date().toISOString().split('T')[0];
    document.getElementById('dataMatricula').value = hoje;
}

function fecharModal() {
    document.getElementById('modal').classList.remove('active');
    alunoEditando = null;
}

function editarAluno(id) {
    const aluno = alunos.find(a => a.id === id);
    
    if (!aluno) {
        alert('Aluno não encontrado!');
        return;
    }
    
    alunoEditando = aluno;
    
    document.getElementById('modal').classList.add('active');
    document.getElementById('modalTitulo').textContent = 'Editar Aluno';
    
    document.getElementById('nome').value = aluno.nome;
    document.getElementById('email').value = aluno.email;
    document.getElementById('cpf').value = aluno.cpf || '';
    document.getElementById('telefone').value = aluno.telefone || '';
    document.getElementById('dataNascimento').value = aluno.data_nascimento || '';
    document.getElementById('cursoId').value = aluno.curso_id || '';
    document.getElementById('dataMatricula').value = aluno.data_matricula;
    document.getElementById('status').value = aluno.status;
}

async function excluirAluno(id) {
    const aluno = alunos.find(a => a.id === id);
    
    if (!aluno) {
        alert('Aluno não encontrado!');
        return;
    }
    
    if (!confirm(`Tem certeza que deseja excluir o aluno "${aluno.nome}"?\n\nEsta ação não pode ser desfeita.`)) {
        return;
    }
    
    try {
        const response = await fetch('../api/delete_aluno.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}`
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            carregarAlunos();
        } else {
            alert('Erro: ' + result.message);
        }
    } catch (error) {
        console.error('Erro ao excluir aluno:', error);
        alert('Erro ao excluir aluno. Tente novamente.');
    }
}

document.getElementById('formAluno').addEventListener('submit', async function (e) {
    e.preventDefault();
    
    const dados = {
        nome: document.getElementById('nome').value.trim(),
        email: document.getElementById('email').value.trim(),
        cpf: document.getElementById('cpf').value.trim() || null,
        telefone: document.getElementById('telefone').value.trim() || null,
        data_nascimento: document.getElementById('dataNascimento').value || null,
        curso_id: document.getElementById('cursoId').value || null,
        data_matricula: document.getElementById('dataMatricula').value,
        status: document.getElementById('status').value
    };
    
    if (alunoEditando) {
        dados.id = alunoEditando.id;
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
    
    if (!dados.data_matricula) {
        alert('Data de matrícula é obrigatória!');
        return;
    }
    
    try {
        const response = await fetch('../api/save_aluno.php', {
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
            carregarAlunos();
        } else {
            alert('Erro: ' + result.message);
        }
    } catch (error) {
        console.error('Erro ao salvar aluno:', error);
        alert('Erro ao salvar aluno. Tente novamente.');
    }
});

// Máscaras para CPF e Telefone
function aplicarMascaras() {
    const cpfInput = document.getElementById('cpf');
    const telefoneInput = document.getElementById('telefone');
    
    cpfInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 11) {
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            e.target.value = value;
        }
    });
    
    telefoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 11) {
            value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
            value = value.replace(/(\d)(\d{4})$/, '$1-$2');
            e.target.value = value;
        }
    });
}

// Fechar modal ao clicar fora
document.getElementById('modal').addEventListener('click', function (e) {
    if (e.target === this) {
        fecharModal();
    }
});