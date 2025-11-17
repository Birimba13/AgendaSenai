let cursos = [];
let cursoEditando = null;

// Carregar cursos ao iniciar
document.addEventListener('DOMContentLoaded', () => {
    carregarCursos();
});

async function carregarCursos() {
    try {
        const response = await fetch('../api/get_cursos.php');
        const data = await response.json();

        if (data.success) {
            cursos = data.data;
            renderizarTabela();
        } else {
            alert('Erro ao carregar cursos: ' + data.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao carregar cursos');
    }
}

function renderizarTabela() {
    const tbody = document.querySelector('#tabelaCursos tbody');
    const cursosFiltrados = filtrarCursos();

    if (cursosFiltrados.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;">Nenhum curso encontrado</td></tr>';
        return;
    }

    tbody.innerHTML = cursosFiltrados.map(curso => {
        const cargaSemanal = curso.carga_horaria_semanal || 0;
        const cargaPreenchida = curso.carga_horaria_preenchida || 0;
        const percentual = cargaSemanal > 0 ? (cargaPreenchida / cargaSemanal) * 100 : 0;

        let classeProgresso = '';
        if (percentual >= 100) classeProgresso = 'completo';
        else if (percentual >= 80) classeProgresso = 'quase';

        return `
        <tr>
            <td>${curso.codigo}</td>
            <td>${curso.nome}</td>
            <td>${formatarNivel(curso.nivel)}</td>
            <td>${curso.carga_horaria_total || '-'} h</td>
            <td>${curso.duracao_meses || '-'} meses</td>
            <td>
                ${cargaSemanal > 0 ? `
                    <div class="mini-progresso">
                        <span>${cargaPreenchida}/${cargaSemanal}h</span>
                        <div class="mini-barra">
                            <div class="${classeProgresso}" style="width: ${Math.min(percentual, 100)}%"></div>
                        </div>
                    </div>
                ` : '-'}
            </td>
            <td>${curso.total_disciplinas}</td>
            <td>${curso.total_turmas}</td>
            <td>
                <span class="badge ${curso.ativo ? 'badge-ativo' : 'badge-inativo'}">
                    ${curso.ativo ? 'Ativo' : 'Inativo'}
                </span>
            </td>
            <td class="acoes">
                <button class="btn-editar" onclick="editarCurso(${curso.id})" title="Editar">✏️</button>
                <button class="btn-excluir" onclick="excluirCurso(${curso.id})" title="Excluir">🗑️</button>
            </td>
        </tr>
        `;
    }).join('');
}

function formatarNivel(nivel) {
    const niveis = {
        'tecnico': 'Técnico',
        'qualificacao': 'Qualificação',
        'aperfeicoamento': 'Aperfeiçoamento',
        'aprendizagem': 'Aprendizagem'
    };
    return niveis[nivel] || nivel;
}

function filtrarCursos() {
    const busca = document.getElementById('busca').value.toLowerCase();
    const nivel = document.getElementById('filtroNivel').value;
    const status = document.getElementById('filtroStatus').value;

    return cursos.filter(curso => {
        const matchBusca = !busca ||
            curso.nome.toLowerCase().includes(busca) ||
            curso.codigo.toLowerCase().includes(busca);
        const matchNivel = !nivel || curso.nivel === nivel;
        const matchStatus = !status ||
            (status === 'ativo' && curso.ativo) ||
            (status === 'inativo' && !curso.ativo);

        return matchBusca && matchNivel && matchStatus;
    });
}

function filtrarTabela() {
    renderizarTabela();
}

function abrirModal(curso = null) {
    const modal = document.getElementById('modal');
    const titulo = document.getElementById('modalTitulo');
    const form = document.getElementById('formCurso');

    if (curso) {
        titulo.textContent = 'Editar Curso';
        cursoEditando = curso;
        preencherFormulario(curso);
    } else {
        titulo.textContent = 'Adicionar Curso';
        cursoEditando = null;
        form.reset();
        // Esconder progresso ao criar novo curso
        document.getElementById('divProgressoCarga').style.display = 'none';
    }

    modal.style.display = 'flex';
}

function fecharModal() {
    document.getElementById('modal').style.display = 'none';
    document.getElementById('formCurso').reset();
    cursoEditando = null;
}

function preencherFormulario(curso) {
    document.getElementById('nome').value = curso.nome;
    document.getElementById('codigo').value = curso.codigo;
    document.getElementById('nivel').value = curso.nivel;
    document.getElementById('cargaHorariaTotal').value = curso.carga_horaria_total || '';
    document.getElementById('duracaoMeses').value = curso.duracao_meses || '';
    document.getElementById('cargaHorariaSemanal').value = curso.carga_horaria_semanal || 0;
    document.getElementById('descricao').value = curso.descricao || '';
    document.getElementById('ativo').value = curso.ativo ? '1' : '0';

    // Mostrar progresso de carga horária ao editar
    if (curso.id && curso.carga_horaria_semanal > 0) {
        mostrarProgressoCarga(curso.carga_horaria_preenchida || 0, curso.carga_horaria_semanal);
    }
}

function mostrarProgressoCarga(preenchida, total) {
    const div = document.getElementById('divProgressoCarga');
    const barra = document.getElementById('progressoBarraPreenchida');
    const texto = document.getElementById('progressoTexto');

    if (total > 0) {
        div.style.display = 'block';
        const percentual = (preenchida / total) * 100;
        barra.style.width = Math.min(percentual, 100) + '%';

        // Aplicar classes de cor
        barra.className = 'progresso-preenchido';
        if (percentual >= 100) {
            barra.className += ' completo';
        } else if (percentual >= 80) {
            barra.className += ' quase';
        }

        texto.textContent = `${preenchida}/${total} horas (${percentual.toFixed(1)}%)`;
    } else {
        div.style.display = 'none';
    }
}

function editarCurso(id) {
    const curso = cursos.find(c => c.id === id);
    if (curso) {
        abrirModal(curso);
    }
}

async function excluirCurso(id) {
    const curso = cursos.find(c => c.id === id);

    if (!confirm(`Tem certeza que deseja excluir o curso "${curso.nome}"?`)) {
        return;
    }

    try {
        const response = await fetch('../api/delete_cursos.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });

        const data = await response.json();

        if (data.success) {
            alert('Curso excluído com sucesso!');
            await carregarCursos();
        } else {
            alert('Erro ao excluir curso: ' + data.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao excluir curso');
    }
}

// Submissão do formulário
document.getElementById('formCurso').addEventListener('submit', async (e) => {
    e.preventDefault();

    const cargaSemanalInput = document.getElementById('cargaHorariaSemanal');
    const cargaSemanalValue = cargaSemanalInput ? cargaSemanalInput.value : null;

    console.log('DEBUG - cargaHorariaSemanal input:', cargaSemanalInput);
    console.log('DEBUG - cargaHorariaSemanal value:', cargaSemanalValue);
    console.log('DEBUG - cargaHorariaSemanal parsed:', parseInt(cargaSemanalValue) || 0);

    const dados = {
        nome: document.getElementById('nome').value,
        codigo: document.getElementById('codigo').value.toUpperCase(),
        nivel: document.getElementById('nivel').value,
        carga_horaria_total: document.getElementById('cargaHorariaTotal').value || null,
        duracao_meses: document.getElementById('duracaoMeses').value || null,
        carga_horaria_semanal: parseInt(cargaSemanalValue) || 0,
        descricao: document.getElementById('descricao').value || null,
        ativo: parseInt(document.getElementById('ativo').value)
    };

    console.log('DEBUG - Dados a serem enviados:', JSON.stringify(dados, null, 2));

    if (cursoEditando) {
        dados.id = cursoEditando.id;
    }

    try {
        const response = await fetch('../api/save_cursos.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dados)
        });

        const data = await response.json();

        if (data.success) {
            alert(data.message);
            fecharModal();
            await carregarCursos();
        } else {
            alert('Erro: ' + data.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao salvar curso');
    }
});

// Fechar modal ao clicar fora
window.onclick = (event) => {
    const modal = document.getElementById('modal');
    if (event.target === modal) {
        fecharModal();
    }
};
