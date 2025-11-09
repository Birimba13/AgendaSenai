let eventos = [];
let eventoEditando = null;

// Carrega eventos quando a página carrega
document.addEventListener('DOMContentLoaded', function() {
    popularFiltroAno();
    carregarEventos();
});

// Função para popular dropdown de anos
function popularFiltroAno() {
    const anoAtual = new Date().getFullYear();
    const filtroAno = document.getElementById('filtroAno');

    let opcoes = '<option value="">Todos os anos</option>';
    for (let i = anoAtual - 2; i <= anoAtual + 2; i++) {
        opcoes += `<option value="${i}" ${i === anoAtual ? 'selected' : ''}>${i}</option>`;
    }

    filtroAno.innerHTML = opcoes;
}

// Função para carregar eventos do banco
async function carregarEventos() {
    const tbody = document.querySelector('#tabelaEventos tbody');

    try {
        // Mostra loading
        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 40px;"><div style="font-size: 2rem; color: #0a2342;">⏳</div><br>Carregando eventos...</td></tr>';

        // Busca dados da API
        const response = await fetch('../api/get_calendario.php');
        const result = await response.json();

        if (result.success && result.data.length > 0) {
            eventos = result.data;
            renderizarEventos(eventos);
        } else {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 40px;">Nenhum evento encontrado. <button class="btn-acao btn-editar" onclick="abrirModal()" style="margin-top: 10px;">Adicionar Primeiro Evento</button></td></tr>';
        }
    } catch (error) {
        console.error('Erro ao carregar eventos:', error);
        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 40px; color: #dc2626;">⚠️<br><br>Erro ao carregar eventos. Tente novamente mais tarde.</td></tr>';
    }
}

// Função para renderizar eventos na tabela
function renderizarEventos(listaEventos) {
    const tbody = document.querySelector('#tabelaEventos tbody');

    if (listaEventos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 40px;">Nenhum evento encontrado.</td></tr>';
        return;
    }

    tbody.innerHTML = listaEventos.map(evento => {
        const dataFormatada = formatarData(evento.data);
        const tipoBadge = getBadgeTipo(evento.tipo);

        return `
            <tr>
                <td>${dataFormatada}</td>
                <td>${tipoBadge}</td>
                <td>${evento.descricao}</td>
                <td>
                    <span class="badge ${evento.dia_letivo ? 'badge-ativo' : 'badge-inativo'}">
                        ${evento.dia_letivo ? 'Sim' : 'Não'}
                    </span>
                </td>
                <td>
                    <div class="acoes">
                        <button class="btn-acao btn-editar" onclick="editarEvento(${evento.id})">Editar</button>
                        <button class="btn-acao btn-excluir" onclick="excluirEvento(${evento.id})">Excluir</button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

// Função para formatar data (YYYY-MM-DD -> DD/MM/YYYY)
function formatarData(dataStr) {
    const [ano, mes, dia] = dataStr.split('-');
    return `${dia}/${mes}/${ano}`;
}

// Função para retornar badge do tipo
function getBadgeTipo(tipo) {
    const tipos = {
        'feriado': { texto: 'Feriado', cor: 'badge-feriado' },
        'recesso': { texto: 'Recesso', cor: 'badge-recesso' },
        'evento': { texto: 'Evento', cor: 'badge-evento' },
        'suspensao': { texto: 'Suspensão', cor: 'badge-suspensao' }
    };

    const info = tipos[tipo] || { texto: tipo, cor: '' };
    return `<span class="badge ${info.cor}">${info.texto}</span>`;
}

// Função para filtrar tabela
function filtrarTabela() {
    const busca = document.getElementById('busca').value.toLowerCase();
    const tipo = document.getElementById('filtroTipo').value;
    const ano = document.getElementById('filtroAno').value;

    const eventosFiltrados = eventos.filter(evento => {
        // Filtro de busca
        const matchBusca = busca === '' ||
            evento.descricao.toLowerCase().includes(busca);

        // Filtro de tipo
        const matchTipo = tipo === '' || evento.tipo === tipo;

        // Filtro de ano
        const matchAno = ano === '' || evento.data.startsWith(ano);

        return matchBusca && matchTipo && matchAno;
    });

    renderizarEventos(eventosFiltrados);
}

// Função para abrir modal (adicionar)
function abrirModal() {
    eventoEditando = null;
    document.getElementById('modal').classList.add('active');
    document.getElementById('modalTitulo').textContent = 'Adicionar Evento';
    document.getElementById('formEvento').reset();

    // Define data de hoje como padrão
    const hoje = new Date().toISOString().split('T')[0];
    document.getElementById('data').value = hoje;
}

// Função para fechar modal
function fecharModal() {
    document.getElementById('modal').classList.remove('active');
    eventoEditando = null;
}

// Função para editar evento
function editarEvento(id) {
    const evento = eventos.find(e => e.id === id);

    if (!evento) {
        alert('Evento não encontrado!');
        return;
    }

    eventoEditando = evento;

    document.getElementById('modal').classList.add('active');
    document.getElementById('modalTitulo').textContent = 'Editar Evento';

    // Preenche o formulário com os dados do evento
    document.getElementById('data').value = evento.data;
    document.getElementById('tipo').value = evento.tipo;
    document.getElementById('descricao').value = evento.descricao;
    document.getElementById('diaLetivo').value = evento.dia_letivo ? '1' : '0';
    document.getElementById('observacoes').value = evento.observacoes || '';
}

// Função para excluir evento
async function excluirEvento(id) {
    const evento = eventos.find(e => e.id === id);

    if (!evento) {
        alert('Evento não encontrado!');
        return;
    }

    if (!confirm(`Tem certeza que deseja excluir o evento "${evento.descricao}"?\n\nEsta ação não pode ser desfeita.`)) {
        return;
    }

    try {
        const response = await fetch('../api/delete_calendario.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}`
        });

        const result = await response.json();

        if (result.success) {
            alert(result.message);
            carregarEventos(); // Recarrega a lista
        } else {
            alert('Erro: ' + result.message);
        }
    } catch (error) {
        console.error('Erro ao excluir evento:', error);
        alert('Erro ao excluir evento. Tente novamente.');
    }
}

// Função para salvar evento (criar ou atualizar)
document.getElementById('formEvento').addEventListener('submit', async function (e) {
    e.preventDefault();

    // Coleta dados do formulário
    const dados = {
        data: document.getElementById('data').value,
        tipo: document.getElementById('tipo').value,
        descricao: document.getElementById('descricao').value.trim(),
        dia_letivo: parseInt(document.getElementById('diaLetivo').value),
        observacoes: document.getElementById('observacoes').value.trim() || null
    };

    // Se estiver editando, adiciona o ID
    if (eventoEditando) {
        dados.id = eventoEditando.id;
    }

    // Validações
    if (!dados.data) {
        alert('Data é obrigatória!');
        return;
    }

    if (!dados.tipo) {
        alert('Tipo é obrigatório!');
        return;
    }

    if (!dados.descricao) {
        alert('Descrição é obrigatória!');
        return;
    }

    try {
        const response = await fetch('../api/save_calendario.php', {
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
            carregarEventos(); // Recarrega a lista
        } else {
            alert('Erro: ' + result.message);
        }
    } catch (error) {
        console.error('Erro ao salvar evento:', error);
        alert('Erro ao salvar evento. Tente novamente.');
    }
});

// Fechar modal ao clicar fora
document.getElementById('modal').addEventListener('click', function (e) {
    if (e.target === this) {
        fecharModal();
    }
});
