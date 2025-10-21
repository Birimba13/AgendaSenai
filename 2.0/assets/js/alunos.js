let alunos = [];
let turmas = [];
let alunoEditando = null;

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM carregado, iniciando sistema...');
    carregarTurmas();
    carregarAlunos();
    aplicarMascaras();
});

// Carrega turmas para os selects
async function carregarTurmas() {
    try {
        const response = await fetch('../api/get_turmas.php');
        const text = await response.text();
        
        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            console.error('Erro ao parsear turmas:', e);
            console.log('Resposta:', text);
            return;
        }
        
        if (result.success) {
            turmas = result.data;
            preencherSelectTurmas();
            console.log('Turmas carregadas:', turmas.length);
        }
    } catch (error) {
        console.error('Erro ao carregar turmas:', error);
    }
}

function preencherSelectTurmas() {
    const selectFiltro = document.getElementById('filtroCurso');
    const selectForm = document.getElementById('cursoId');
    
    if (!selectFiltro || !selectForm) {
        console.error('Elementos select não encontrados');
        return;
    }
    
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
    
    if (!tbody) {
        console.error('Tbody não encontrado');
        return;
    }
    
    try {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px;"><div style="font-size: 2rem; color: #0a2342;">⏳</div><br>Carregando alunos...</td></tr>';
        
        const response = await fetch('../api/get_alunos.php');
        const text = await response.text();
        
        console.log('Resposta da API get_alunos:', text);
        
        // Tenta fazer parse do JSON
        let result;
        try {
            result = JSON.parse(text);
        } catch (parseError) {
            console.error('Erro ao fazer parse do JSON:', parseError);
            console.error('Texto recebido:', text);
            tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px; color: #dc2626;">⚠️<br><br>Erro ao processar resposta da API.<br><small>Verifique o console para mais detalhes</small></td></tr>';
            return;
        }
        
        console.log('Resultado parseado:', result);
        
        if (result.success && result.data && result.data.length > 0) {
            alunos = result.data;
            console.log('Alunos carregados:', alunos.length);
            renderizarAlunos(alunos);
        } else if (result.success) {
            console.log('Nenhum aluno encontrado');
            tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px;">Nenhum aluno encontrado. <button class="btn-acao btn-editar" onclick="abrirModal()" style="margin-top: 10px;">Adicionar Primeiro Aluno</button></td></tr>';
        } else {
            throw new Error(result.message || 'Erro desconhecido');
        }
    } catch (error) {
        console.error('Erro ao carregar alunos:', error);
        tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; padding: 40px; color: #dc2626;">⚠️<br><br>Erro: ${error.message}</td></tr>`;
    }
}

function renderizarAlunos(listaAlunos) {
    const tbody = document.querySelector('#tabelaAlunos tbody');
    
    if (!tbody) {
        console.error('Tbody não encontrado');
        return;
    }
    
    if (!listaAlunos || listaAlunos.length === 0) {
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
                <span class="badge ${statusBadge[aluno.status] || 'badge'}">
                    ${statusTexto[aluno.status] || aluno.status}
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
    
    console.log('Tabela renderizada com', listaAlunos.length, 'alunos');
}

function filtrarTabela() {
    const busca = document.getElementById('busca').value.toLowerCase();
    const cursoId = document.getElementById('filtroCurso').value;
    const status = document.getElementById('filtroStatus').value;
    
    const alunosFiltrados = alunos.filter(aluno => {
        const matchBusca = busca === '' || 
            aluno.nome.toLowerCase().includes(busca) || 
            aluno.email.toLowerCase().includes(busca) ||
            (aluno.cpf && aluno.cpf.includes(busca));
        
        const matchCurso = cursoId === '' || 
            (aluno.curso_id && aluno.curso_id.toString() === cursoId);
        
        const matchStatus = status === '' || aluno.status === status;
        
        return matchBusca && matchCurso && matchStatus;
    });
    
    renderizarAlunos(alunosFiltrados);
}

function abrirModal() {
    alunoEditando = null;
    const modal = document.getElementById('modal');
    if (!modal) {
        console.error('Modal não encontrado');
        return;
    }
    
    modal.classList.add('active');
    document.getElementById('modalTitulo').textContent = 'Adicionar Aluno';
    document.getElementById('formAluno').reset();
    
    const hoje = new Date().toISOString().split('T')[0];
    document.getElementById('dataMatricula').value = hoje;
}

function fecharModal() {
    const modal = document.getElementById('modal');
    if (!modal) return;
    
    modal.classList.remove('active');
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
        const response = await fetch('../api/delete_alunos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}`
        });
        
        const text = await response.text();
        let result;
        
        try {
            result = JSON.parse(text);
        } catch (e) {
            console.error('Erro ao parsear resposta de exclusão:', e);
            alert('Erro ao processar resposta do servidor');
            return;
        }
        
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

document.getElementById('formAluno')?.addEventListener('submit', async function (e) {
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
        const response = await fetch('../api/save_alunos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(dados)
        });
        
        const text = await response.text();
        let result;
        
        try {
            result = JSON.parse(text);
        } catch (e) {
            console.error('Erro ao parsear resposta de salvamento:', e);
            console.log('Resposta:', text);
            alert('Erro ao processar resposta do servidor');
            return;
        }
        
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

function aplicarMascaras() {
    const cpfInput = document.getElementById('cpf');
    const telefoneInput = document.getElementById('telefone');
    
    if (!cpfInput || !telefoneInput) {
        console.error('Inputs de CPF ou telefone não encontrados');
        return;
    }
    
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
}

document.getElementById('modal')?.addEventListener('click', function (e) {
    if (e.target === this) {
        fecharModal();
    }
});

console.log('Script alunos.js carregado com sucesso');