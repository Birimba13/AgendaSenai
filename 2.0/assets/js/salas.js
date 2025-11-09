let salas = [];
let salaEditando = null;

document.addEventListener('DOMContentLoaded', () => {
    carregarSalas();
});

async function carregarSalas() {
    try {
        const response = await fetch('../api/get_salas.php');
        const data = await response.json();
        if (data.success) {
            salas = data.data;
            renderizarTabela();
        }
    } catch (error) {
        console.error('Erro:', error);
    }
}

function renderizarTabela() {
    const tbody = document.querySelector('#tabelaSalas tbody');
    const salasFiltradas = filtrarSalas();

    if (salasFiltradas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">Nenhuma sala encontrada</td></tr>';
        return;
    }

    tbody.innerHTML = salasFiltradas.map(sala => `
        <tr>
            <td>${sala.codigo}</td>
            <td>${sala.nome}</td>
            <td>${formatarTipo(sala.tipo)}</td>
            <td>${sala.capacidade || '-'}</td>
            <td>${sala.local}</td>
            <td>
                <span class="badge ${sala.ativo ? 'badge-ativo' : 'badge-inativo'}">
                    ${sala.ativo ? 'Ativo' : 'Inativo'}
                </span>
            </td>
            <td class="acoes">
                <button class="btn-editar" onclick="editarSala(${sala.id})">✏️</button>
                <button class="btn-excluir" onclick="excluirSala(${sala.id})">🗑️</button>
            </td>
        </tr>
    `).join('');
}

function formatarTipo(tipo) {
    const tipos = {
        'sala_aula': 'Sala de Aula',
        'laboratorio': 'Laboratório',
        'auditorio': 'Auditório',
        'oficina': 'Oficina'
    };
    return tipos[tipo] || tipo;
}

function filtrarSalas() {
    const busca = document.getElementById('busca').value.toLowerCase();
    const tipo = document.getElementById('filtroTipo').value;

    return salas.filter(sala => {
        const matchBusca = !busca || sala.codigo.toLowerCase().includes(busca) || sala.nome.toLowerCase().includes(busca);
        const matchTipo = !tipo || sala.tipo === tipo;
        return matchBusca && matchTipo;
    });
}

function filtrarTabela() {
    renderizarTabela();
}

function abrirModal(sala = null) {
    const modal = document.getElementById('modal');
    const titulo = document.getElementById('modalTitulo');
    const form = document.getElementById('formSala');

    if (sala) {
        titulo.textContent = 'Editar Sala';
        salaEditando = sala;
        preencherFormulario(sala);
    } else {
        titulo.textContent = 'Adicionar Sala';
        salaEditando = null;
        form.reset();
    }

    modal.style.display = 'flex';
}

function fecharModal() {
    document.getElementById('modal').style.display = 'none';
    salaEditando = null;
}

function preencherFormulario(sala) {
    document.getElementById('codigo').value = sala.codigo;
    document.getElementById('nome').value = sala.nome;
    document.getElementById('tipo').value = sala.tipo;
    document.getElementById('capacidade').value = sala.capacidade || '';
    document.getElementById('local').value = sala.local;
    document.getElementById('ativo').value = sala.ativo ? '1' : '0';
}

function editarSala(id) {
    const sala = salas.find(s => s.id === id);
    if (sala) abrirModal(sala);
}

async function excluirSala(id) {
    const sala = salas.find(s => s.id === id);
    if (!confirm(`Excluir sala "${sala.nome}"?`)) return;

    try {
        const response = await fetch('../api/delete_salas.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });

        const data = await response.json();
        if (data.success) {
            alert('Sala excluída com sucesso!');
            await carregarSalas();
        } else {
            alert('Erro: ' + data.message);
        }
    } catch (error) {
        console.error('Erro:', error);
    }
}

document.getElementById('formSala').addEventListener('submit', async (e) => {
    e.preventDefault();

    const dados = {
        codigo: document.getElementById('codigo').value.toUpperCase(),
        nome: document.getElementById('nome').value,
        tipo: document.getElementById('tipo').value,
        capacidade: document.getElementById('capacidade').value || null,
        local: document.getElementById('local').value,
        ativo: parseInt(document.getElementById('ativo').value)
    };

    if (salaEditando) dados.id = salaEditando.id;

    try {
        const response = await fetch('../api/save_salas.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dados)
        });

        const data = await response.json();
        if (data.success) {
            alert(data.message);
            fecharModal();
            await carregarSalas();
        } else {
            alert('Erro: ' + data.message);
        }
    } catch (error) {
        console.error('Erro:', error);
    }
});

window.onclick = (event) => {
    if (event.target === document.getElementById('modal')) fecharModal();
};
