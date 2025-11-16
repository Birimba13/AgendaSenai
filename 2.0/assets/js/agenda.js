// Variáveis globais
let currentWeekStart = null;
let currentYear = new Date().getFullYear();
let agendamentos = [];
let feriados = [];
let professores = [];
let turmas = [];
let disciplinas = [];
let salas = [];
let selectedAgendamento = null;

// Inicialização
document.addEventListener('DOMContentLoaded', async () => {
    initializeTabs();
    initializeModals();
    initializeFilters();

    await loadSelectData();
    await loadFeriados();

    // Inicializar semana atual
    currentWeekStart = getWeekStart(new Date());
    await loadWeekView();

    // Inicializar ano atual
    loadYearView();

    // Event Listeners
    document.getElementById('prevWeek').addEventListener('click', () => navigateWeek(-1));
    document.getElementById('nextWeek').addEventListener('click', () => navigateWeek(1));
    document.getElementById('todayBtn').addEventListener('click', goToToday);

    document.getElementById('prevYear').addEventListener('click', () => navigateYear(-1));
    document.getElementById('nextYear').addEventListener('click', () => navigateYear(1));

    document.getElementById('newAgendamento').addEventListener('click', openNewAgendamentoModal);
    document.getElementById('formAgendamento').addEventListener('submit', saveAgendamento);
    document.getElementById('btnCancelar').addEventListener('click', closeModal);

    document.getElementById('filterProfessor').addEventListener('change', loadWeekView);
    document.getElementById('filterTurma').addEventListener('change', loadWeekView);
    document.getElementById('filterStatus').addEventListener('change', loadWeekView);
    document.getElementById('clearFilters').addEventListener('click', clearFilters);

    document.getElementById('data').addEventListener('change', () => {
        updateDiaSemana();
        reloadProfessoresDisponiveis();
        reloadSalasDisponiveis();
    });
    document.getElementById('horaInicio').addEventListener('change', () => {
        reloadProfessoresDisponiveis();
        reloadSalasDisponiveis();
    });
    document.getElementById('horaFim').addEventListener('change', () => {
        reloadProfessoresDisponiveis();
        reloadSalasDisponiveis();
    });
    document.getElementById('turmaId').addEventListener('change', reloadDisciplinasPorCurso);
});

// Gerenciamento de Abas
function initializeTabs() {
    const tabs = document.querySelectorAll('.tab-button');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const targetTab = tab.dataset.tab;

            // Remover classe active de todas as abas e conteúdos
            document.querySelectorAll('.tab-button').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

            // Adicionar classe active na aba clicada e seu conteúdo
            tab.classList.add('active');
            document.getElementById(targetTab).classList.add('active');

            // Recarregar dados se necessário
            if (targetTab === 'anual') {
                loadYearView();
            }
        });
    });
}

// Gerenciamento de Modais
function initializeModals() {
    const modals = document.querySelectorAll('.modal');
    const closes = document.querySelectorAll('.close');

    closes.forEach(close => {
        close.addEventListener('click', closeModal);
    });

    window.addEventListener('click', (e) => {
        modals.forEach(modal => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
}

function closeModal() {
    document.getElementById('modalAgendamento').style.display = 'none';
    document.getElementById('modalDetalhes').style.display = 'none';
}

// Carregar dados para os selects
async function loadSelectData() {
    try {
        // Carregar professores
        const profRes = await fetch('../api/get_professores_select.php');
        const profData = await profRes.json();
        professores = profData.data || [];

        const selectProf = document.getElementById('professorId');
        const filterProf = document.getElementById('filterProfessor');
        professores.forEach(prof => {
            selectProf.innerHTML += `<option value="${prof.id}">${prof.nome}</option>`;
            filterProf.innerHTML += `<option value="${prof.id}">${prof.nome}</option>`;
        });

        // Carregar turmas
        const turmaRes = await fetch('../api/get_turmas_select.php');
        const turmaData = await turmaRes.json();
        turmas = turmaData.data || [];

        const selectTurma = document.getElementById('turmaId');
        const filterTurma = document.getElementById('filterTurma');
        turmas.forEach(turma => {
            selectTurma.innerHTML += `<option value="${turma.id}">${turma.nome} - ${turma.turno}</option>`;
            filterTurma.innerHTML += `<option value="${turma.id}">${turma.nome}</option>`;
        });

        // Carregar disciplinas
        const discRes = await fetch('../api/get_disciplinas_select.php');
        const discData = await discRes.json();
        disciplinas = discData.data || [];

        const selectDisc = document.getElementById('disciplinaId');
        disciplinas.forEach(disc => {
            selectDisc.innerHTML += `<option value="${disc.id}">${disc.nome} (${disc.sigla})</option>`;
        });

        // Carregar salas
        const salaRes = await fetch('../api/get_salas_select.php');
        const salaData = await salaRes.json();
        salas = salaData.data || [];

        const selectSala = document.getElementById('salaId');
        salas.forEach(sala => {
            const info = sala.codigo ? `${sala.codigo} - ${sala.nome}` : sala.nome;
            selectSala.innerHTML += `<option value="${sala.codigo}">${info}</option>`;
        });

    } catch (error) {
        console.error('Erro ao carregar dados dos selects:', error);
    }
}

// Recarregar professores disponíveis baseado em data e horário
async function reloadProfessoresDisponiveis() {
    const data = document.getElementById('data').value;
    const horaInicio = document.getElementById('horaInicio').value;
    const horaFim = document.getElementById('horaFim').value;
    const agendamentoId = document.getElementById('agendamentoId').value;

    if (!data || !horaInicio || !horaFim) {
        // Se não tem os dados necessários, não fazer nada
        return;
    }

    try {
        const params = new URLSearchParams({
            data,
            hora_inicio: horaInicio,
            hora_fim: horaFim
        });

        if (agendamentoId) {
            params.append('agendamento_id', agendamentoId);
        }

        const res = await fetch(`../api/get_professores_disponiveis.php?${params}`);

        if (!res.ok) {
            const errorText = await res.text();
            console.error('Erro ao carregar professores disponíveis:', errorText);
            return;
        }

        const data_res = await res.json();

        if (data_res.success) {
            const professorAtual = document.getElementById('professorId').value;
            const selectProf = document.getElementById('professorId');
            selectProf.innerHTML = '<option value="">Selecione...</option>';

            data_res.data.forEach(prof => {
                selectProf.innerHTML += `<option value="${prof.id}">${prof.nome}</option>`;
            });

            // Restaurar seleção anterior se ainda disponível
            if (professorAtual) {
                selectProf.value = professorAtual;
            }
        } else {
            console.error('Erro na resposta:', data_res.message);
        }
    } catch (error) {
        console.error('Erro ao carregar professores disponíveis:', error);
    }
}

// Recarregar salas disponíveis baseado em data e horário
async function reloadSalasDisponiveis() {
    const data = document.getElementById('data').value;
    const horaInicio = document.getElementById('horaInicio').value;
    const horaFim = document.getElementById('horaFim').value;
    const agendamentoId = document.getElementById('agendamentoId').value;

    if (!data || !horaInicio || !horaFim) {
        // Se não tem os dados necessários, não fazer nada
        return;
    }

    try {
        const params = new URLSearchParams({
            data,
            hora_inicio: horaInicio,
            hora_fim: horaFim
        });

        if (agendamentoId) {
            params.append('agendamento_id', agendamentoId);
        }

        const res = await fetch(`../api/get_salas_disponiveis.php?${params}`);

        if (!res.ok) {
            const errorText = await res.text();
            console.error('Erro ao carregar salas disponíveis:', errorText);
            return;
        }

        const data_res = await res.json();

        if (data_res.success) {
            const salaAtual = document.getElementById('salaId').value;
            const selectSala = document.getElementById('salaId');
            selectSala.innerHTML = '<option value="">Selecione...</option>';

            data_res.data.forEach(sala => {
                const info = sala.id ? `${sala.nome}` : sala.nome;
                selectSala.innerHTML += `<option value="${sala.nome}">${info}</option>`;
            });

            // Restaurar seleção anterior se ainda disponível
            if (salaAtual) {
                selectSala.value = salaAtual;
            }
        } else {
            console.error('Erro na resposta:', data_res.message);
        }
    } catch (error) {
        console.error('Erro ao carregar salas disponíveis:', error);
    }
}

// Recarregar disciplinas baseado no curso da turma selecionada
async function reloadDisciplinasPorCurso() {
    const turmaId = document.getElementById('turmaId').value;

    if (!turmaId) {
        // Se não tem turma selecionada, não fazer nada
        return;
    }

    // Encontrar o curso_id da turma selecionada
    const turmaSelecionada = turmas.find(t => t.id == turmaId);
    if (!turmaSelecionada || !turmaSelecionada.curso_id) {
        console.error('Turma não encontrada ou sem curso_id');
        return;
    }

    try {
        const res = await fetch(`../api/get_disciplinas_por_curso.php?curso_id=${turmaSelecionada.curso_id}`);

        if (!res.ok) {
            const errorText = await res.text();
            console.error('Erro ao carregar disciplinas por curso:', errorText);
            return;
        }

        const data = await res.json();

        if (data.success) {
            const disciplinaAtual = document.getElementById('disciplinaId').value;
            const selectDisc = document.getElementById('disciplinaId');
            selectDisc.innerHTML = '<option value="">Selecione...</option>';

            data.data.forEach(disc => {
                selectDisc.innerHTML += `<option value="${disc.id}">${disc.nome} (${disc.sigla})</option>`;
            });

            // Restaurar seleção anterior se ainda disponível
            if (disciplinaAtual) {
                selectDisc.value = disciplinaAtual;
            }
        } else {
            console.error('Erro na resposta:', data.message);
        }
    } catch (error) {
        console.error('Erro ao carregar disciplinas por curso:', error);
    }
}

// Carregar feriados
async function loadFeriados() {
    try {
        const res = await fetch('../api/get_calendario.php');
        const data = await res.json();
        feriados = data.data || [];
    } catch (error) {
        console.error('Erro ao carregar feriados:', error);
    }
}

// Funções de data
function getWeekStart(date) {
    const d = new Date(date);
    const day = d.getDay();
    const diff = d.getDate() - day + (day === 0 ? -6 : 1); // Segunda-feira
    return new Date(d.setDate(diff));
}

function formatDate(date) {
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${year}-${month}-${day}`;
}

function formatDateBR(date) {
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${day}/${month}/${year}`;
}

function getDayName(date) {
    const days = ['Domingo', 'Segunda', 'Terca', 'Quarta', 'Quinta', 'Sexta', 'Sabado'];
    return days[new Date(date).getDay()];
}

// Navegação de semana
function navigateWeek(direction) {
    currentWeekStart.setDate(currentWeekStart.getDate() + (direction * 7));
    loadWeekView();
}

function goToToday() {
    currentWeekStart = getWeekStart(new Date());
    loadWeekView();
}

// Navegação de ano
function navigateYear(direction) {
    currentYear += direction;
    loadYearView();
}

// Filtros
function initializeFilters() {
    // Filtros já inicializados nos event listeners
}

function clearFilters() {
    document.getElementById('filterProfessor').value = '';
    document.getElementById('filterTurma').value = '';
    document.getElementById('filterStatus').value = '';
    loadWeekView();
}

// Visualização Semanal
async function loadWeekView() {
    const weekEnd = new Date(currentWeekStart);
    weekEnd.setDate(weekEnd.getDate() + 6);

    // Atualizar título
    document.getElementById('weekTitle').textContent =
        `Semana de ${formatDateBR(currentWeekStart)} a ${formatDateBR(weekEnd)}`;

    // Carregar agendamentos
    const params = new URLSearchParams({
        data_inicio: formatDate(currentWeekStart),
        data_fim: formatDate(weekEnd)
    });

    const professor = document.getElementById('filterProfessor').value;
    const turma = document.getElementById('filterTurma').value;
    const status = document.getElementById('filterStatus').value;

    if (professor) params.append('professor_id', professor);
    if (turma) params.append('turma_id', turma);
    if (status) params.append('status', status);

    try {
        const res = await fetch(`../api/get_agendamentos.php?${params}`);
        const data = await res.json();
        agendamentos = data.data || [];

        renderWeekGrid();
    } catch (error) {
        console.error('Erro ao carregar agendamentos:', error);
    }
}

function renderWeekGrid() {
    const grid = document.getElementById('weekGrid');
    grid.innerHTML = '';

    // Criar header com dias da semana
    const header = document.createElement('div');
    header.className = 'week-header';

    const timeCol = document.createElement('div');
    timeCol.className = 'time-column-header';
    timeCol.textContent = 'Horário';
    header.appendChild(timeCol);

    const diasSemana = ['Segunda', 'Terca', 'Quarta', 'Quinta', 'Sexta', 'Sabado'];
    const daysLabels = ['Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];

    for (let i = 0; i < 6; i++) {
        const date = new Date(currentWeekStart);
        date.setDate(date.getDate() + i);

        const dayCol = document.createElement('div');
        dayCol.className = 'day-column-header';

        const feriado = feriados.find(f => f.data === formatDate(date));
        if (feriado) {
            dayCol.classList.add('feriado');
            dayCol.innerHTML = `
                <div class="day-name">${daysLabels[i]}</div>
                <div class="day-date">${formatDateBR(date)}</div>
                <div class="feriado-label">${feriado.descricao}</div>
            `;
        } else {
            dayCol.innerHTML = `
                <div class="day-name">${daysLabels[i]}</div>
                <div class="day-date">${formatDateBR(date)}</div>
            `;
        }

        header.appendChild(dayCol);
    }

    grid.appendChild(header);

    // Criar grid de horários (7h às 22h)
    const horaInicio = 7;
    const horaFim = 22;

    for (let hora = horaInicio; hora <= horaFim; hora++) {
        const row = document.createElement('div');
        row.className = 'time-row';

        const timeCell = document.createElement('div');
        timeCell.className = 'time-cell';
        timeCell.textContent = `${String(hora).padStart(2, '0')}:00`;
        row.appendChild(timeCell);

        for (let dia = 0; dia < 6; dia++) {
            const date = new Date(currentWeekStart);
            date.setDate(date.getDate() + dia);
            const dateStr = formatDate(date);
            const diaSemana = diasSemana[dia];

            const cell = document.createElement('div');
            cell.className = 'day-cell';
            cell.dataset.date = dateStr;
            cell.dataset.hour = hora;
            cell.dataset.dia = diaSemana;

            // Encontrar agendamentos para este horário
            const agendasDoDia = agendamentos.filter(a => {
                const agendaHora = parseInt(a.hora_inicio.split(':')[0]);
                return a.data === dateStr && agendaHora === hora;
            });

            agendasDoDia.forEach(agenda => {
                const agendaCard = createAgendaCard(agenda);
                cell.appendChild(agendaCard);
            });

            // Permitir criar novo agendamento clicando na célula vazia
            if (agendasDoDia.length === 0) {
                cell.addEventListener('click', () => {
                    openNewAgendamentoModal(dateStr, hora, diaSemana);
                });
                cell.style.cursor = 'pointer';
            }

            row.appendChild(cell);
        }

        grid.appendChild(row);
    }
}

function createAgendaCard(agenda) {
    const card = document.createElement('div');
    card.className = `agenda-card ${agenda.status} ${agenda.tipo}`;

    const duracao = calculateDuration(agenda.hora_inicio, agenda.hora_fim);

    // Cada linha de horário tem 60px, então a altura é duração * 60px
    // Mas precisa ocupar múltiplas células, então ajustamos para cobrir exatamente o espaço
    const alturaEmPixels = duracao * 60;
    card.style.height = `${alturaEmPixels}px`;
    card.style.minHeight = `${alturaEmPixels}px`;

    // Posicionar o card de forma absoluta para sobrepor múltiplas células
    card.style.position = 'relative';
    card.style.zIndex = '10';

    card.innerHTML = `
        <div class="agenda-time">${agenda.hora_inicio} - ${agenda.hora_fim}</div>
        <div class="agenda-title">${agenda.disciplina_sigla} - ${agenda.turma_nome}</div>
        <div class="agenda-professor">${agenda.professor_nome}</div>
        <div class="agenda-sala">Sala: ${agenda.sala}</div>
    `;

    card.addEventListener('click', (e) => {
        e.stopPropagation();
        showAgendaDetails(agenda);
    });

    return card;
}

function calculateDuration(inicio, fim) {
    const [h1, m1] = inicio.split(':').map(Number);
    const [h2, m2] = fim.split(':').map(Number);
    return (h2 - h1) + (m2 - m1) / 60;
}

// Visualização Anual
function loadYearView() {
    document.getElementById('yearTitle').textContent = currentYear;
    renderYearGrid();
}

function renderYearGrid() {
    const grid = document.getElementById('yearGrid');
    grid.innerHTML = '';

    const meses = [
        'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
        'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
    ];

    for (let mes = 0; mes < 12; mes++) {
        const monthCard = document.createElement('div');
        monthCard.className = 'month-card';

        const monthTitle = document.createElement('h4');
        monthTitle.textContent = meses[mes];
        monthCard.appendChild(monthTitle);

        const monthGrid = renderMonthCalendar(currentYear, mes);
        monthCard.appendChild(monthGrid);

        grid.appendChild(monthCard);
    }
}

function renderMonthCalendar(year, month) {
    const calendar = document.createElement('div');
    calendar.className = 'month-calendar';

    // Header com dias da semana
    const daysHeader = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];
    daysHeader.forEach(day => {
        const dayLabel = document.createElement('div');
        dayLabel.className = 'day-label';
        dayLabel.textContent = day;
        calendar.appendChild(dayLabel);
    });

    // Primeiro dia do mês
    const firstDay = new Date(year, month, 1);
    let dayOfWeek = firstDay.getDay();
    dayOfWeek = dayOfWeek === 0 ? 6 : dayOfWeek - 1; // Ajustar para segunda = 0

    // Dias vazios antes do início do mês
    for (let i = 0; i < dayOfWeek; i++) {
        const emptyDay = document.createElement('div');
        emptyDay.className = 'day-cell empty';
        calendar.appendChild(emptyDay);
    }

    // Dias do mês
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    for (let day = 1; day <= daysInMonth; day++) {
        const date = new Date(year, month, day);
        const dateStr = formatDate(date);

        const dayCell = document.createElement('div');
        dayCell.className = 'day-cell';
        dayCell.textContent = day;

        // Verificar se é feriado
        const feriado = feriados.find(f => f.data === dateStr);
        if (feriado) {
            dayCell.classList.add(feriado.tipo);
            dayCell.title = feriado.descricao;
        }

        // Verificar se tem aulas (buscar na lista de agendamentos)
        const temAulas = agendamentos.some(a => a.data === dateStr);
        if (temAulas) {
            dayCell.classList.add('com-aula');
        }

        // Destacar hoje
        const hoje = new Date();
        if (date.toDateString() === hoje.toDateString()) {
            dayCell.classList.add('today');
        }

        // Destacar fim de semana
        if (date.getDay() === 0) {
            dayCell.classList.add('weekend');
        }

        calendar.appendChild(dayCell);
    }

    return calendar;
}

// Modal de Novo Agendamento
function openNewAgendamentoModal(date = null, hour = null, diaSemana = null) {
    document.getElementById('modalTitle').textContent = 'Novo Agendamento';
    document.getElementById('formAgendamento').reset();
    document.getElementById('agendamentoId').value = '';

    // Recarregar todos os selects para o estado inicial (todas as opções)
    loadSelectData();

    if (date) {
        document.getElementById('data').value = date;
        document.getElementById('diaSemana').value = diaSemana;
    }

    if (hour) {
        document.getElementById('horaInicio').value = `${String(hour).padStart(2, '0')}:00`;
        document.getElementById('horaFim').value = `${String(hour + 1).padStart(2, '0')}:00`;
    }

    // Se tiver data e horários, já carregar professores e salas disponíveis
    if (date && hour) {
        setTimeout(() => {
            reloadProfessoresDisponiveis();
            reloadSalasDisponiveis();
        }, 100);
    }

    document.getElementById('modalAgendamento').style.display = 'block';
}

function updateDiaSemana() {
    const data = document.getElementById('data').value;
    if (data) {
        const date = new Date(data + 'T00:00:00');
        const diaSemana = getDayName(date);
        document.getElementById('diaSemana').value = diaSemana;
    }
}

// Salvar Agendamento
async function saveAgendamento(e) {
    e.preventDefault();

    const horaInicio = document.getElementById('horaInicio').value;
    const horaFim = document.getElementById('horaFim').value;

    // Validar duração máxima de 1 hora
    const [h1, m1] = horaInicio.split(':').map(Number);
    const [h2, m2] = horaFim.split(':').map(Number);
    const duracaoMinutos = (h2 * 60 + m2) - (h1 * 60 + m1);

    if (duracaoMinutos <= 0) {
        alert('A hora de fim deve ser posterior à hora de início.');
        return;
    }

    if (duracaoMinutos > 60) {
        alert('A duração da aula não pode ser maior que 1 hora (60 minutos).');
        return;
    }

    const formData = {
        id: document.getElementById('agendamentoId').value || null,
        professor_id: document.getElementById('professorId').value,
        turma_id: document.getElementById('turmaId').value,
        disciplina_id: document.getElementById('disciplinaId').value,
        sala: document.getElementById('salaId').value,
        data: document.getElementById('data').value,
        dia_semana: document.getElementById('diaSemana').value,
        hora_inicio: horaInicio,
        hora_fim: horaFim,
        tipo: document.getElementById('tipo').value,
        modalidade: document.getElementById('modalidade').value,
        observacoes: document.getElementById('observacoes').value
    };

    try {
        const res = await fetch('../api/save_agendamento.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        const data = await res.json();

        if (data.success) {
            // Mostrar mensagem de sucesso
            let mensagemCompleta = data.message;

            // Se houver informações de carga horária, adicionar detalhes
            if (data.carga_horaria) {
                const { usada, maxima, percentual } = data.carga_horaria;
                mensagemCompleta += `\n\nCarga Horária Atual: ${usada}h / ${maxima}h (${percentual}%)`;
            }

            // Se houver alerta, mostrar em destaque
            if (data.alerta) {
                mensagemCompleta += `\n\n${data.alerta}`;
            }

            alert(mensagemCompleta);
            closeModal();
            loadWeekView();
        } else {
            alert('Erro: ' + data.message);
        }
    } catch (error) {
        console.error('Erro ao salvar agendamento:', error);
        alert('Erro ao salvar agendamento');
    }
}

// Mostrar detalhes do agendamento
function showAgendaDetails(agenda) {
    selectedAgendamento = agenda;

    const content = document.getElementById('detalhesContent');
    content.innerHTML = `
        <div class="detalhes-grid">
            <div class="detalhe-item">
                <strong>Professor:</strong> ${agenda.professor_nome}
            </div>
            <div class="detalhe-item">
                <strong>Turma:</strong> ${agenda.turma_nome} (${agenda.turma_turno})
            </div>
            <div class="detalhe-item">
                <strong>Disciplina:</strong> ${agenda.disciplina_nome}
            </div>
            <div class="detalhe-item">
                <strong>Sala:</strong> ${agenda.sala}
            </div>
            <div class="detalhe-item">
                <strong>Data:</strong> ${formatDateBR(agenda.data)}
            </div>
            <div class="detalhe-item">
                <strong>Horário:</strong> ${agenda.hora_inicio} - ${agenda.hora_fim}
            </div>
            <div class="detalhe-item">
                <strong>Tipo:</strong> ${agenda.tipo}
            </div>
            <div class="detalhe-item">
                <strong>Modalidade:</strong> ${agenda.modalidade}
            </div>
            <div class="detalhe-item">
                <strong>Status:</strong> <span class="status-badge ${agenda.status}">${agenda.status}</span>
            </div>
            ${agenda.observacoes ? `
            <div class="detalhe-item full-width">
                <strong>Observações:</strong> ${agenda.observacoes}
            </div>
            ` : ''}
        </div>
    `;

    document.getElementById('btnEditar').onclick = () => editAgendamento(agenda);
    document.getElementById('btnExcluir').onclick = () => deleteAgendamento(agenda.id);

    document.getElementById('modalDetalhes').style.display = 'block';
}

// Editar agendamento
function editAgendamento(agenda) {
    document.getElementById('modalDetalhes').style.display = 'none';

    document.getElementById('modalTitle').textContent = 'Editar Agendamento';
    document.getElementById('agendamentoId').value = agenda.id;
    document.getElementById('professorId').value = agenda.professor_id;
    document.getElementById('turmaId').value = agenda.turma_id;
    document.getElementById('disciplinaId').value = agenda.disciplina_id;
    document.getElementById('salaId').value = agenda.sala;
    document.getElementById('data').value = agenda.data;
    document.getElementById('diaSemana').value = agenda.dia_semana;
    document.getElementById('horaInicio').value = agenda.hora_inicio;
    document.getElementById('horaFim').value = agenda.hora_fim;
    document.getElementById('tipo').value = agenda.tipo;
    document.getElementById('modalidade').value = agenda.modalidade;
    document.getElementById('observacoes').value = agenda.observacoes || '';

    document.getElementById('modalAgendamento').style.display = 'block';
}

// Deletar agendamento
async function deleteAgendamento(id) {
    if (!confirm('Tem certeza que deseja excluir este agendamento?')) {
        return;
    }

    try {
        const res = await fetch('../api/delete_agendamento.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id })
        });

        const data = await res.json();

        if (data.success) {
            alert(data.message);
            closeModal();
            loadWeekView();
        } else {
            alert('Erro: ' + data.message);
        }
    } catch (error) {
        console.error('Erro ao excluir agendamento:', error);
        alert('Erro ao excluir agendamento');
    }
}
