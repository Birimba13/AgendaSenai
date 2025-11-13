# Melhorias no Sistema de Agenda

## 📋 Resumo das Alterações

Este documento descreve as melhorias implementadas no sistema de agendamento para torná-lo mais robusto e inteligente.

## ✨ Funcionalidades Implementadas

### 1. Múltiplos Horários na Mesma Célula ✅

**O que foi feito:**
- O sistema agora suporta múltiplas aulas no mesmo horário (ex: duas aulas às 14:00-15:00)
- Cada aula pode ter professor, turma e sala diferentes
- A lógica de conflitos garante que não haja sobreposição de recursos

**Como funciona:**
```
14:00-15:00
├─ Aula 1: Prof. João + Turma A + Sala 101
└─ Aula 2: Prof. Maria + Turma B + Sala 102  ✅ PERMITIDO
```

**Conflitos detectados:**
```
14:00-15:00
├─ Aula 1: Prof. João + Turma A + Sala 101
└─ Aula 2: Prof. João + Turma B + Sala 102  ❌ BLOQUEADO (mesmo professor)
```

### 2. Validação de Duração Máxima ⏱️

**Regra implementada:**
- Aulas não podem ter mais de 1 hora (60 minutos) de duração
- Validação automática no backend
- Mensagem de erro clara para o usuário

**Exemplos:**
- ✅ 14:00 até 15:00 = 60 min (Permitido)
- ✅ 14:00 até 14:50 = 50 min (Permitido)
- ❌ 14:00 até 15:30 = 90 min (Bloqueado)

### 3. Filtro Inteligente de Professores 👨‍🏫

**Nova API:** `get_professores_disponiveis.php`

**Parâmetros:**
- `data`: Data do agendamento (YYYY-MM-DD)
- `hora_inicio`: Hora de início (HH:MM)
- `hora_fim`: Hora de fim (HH:MM)
- `agendamento_id` (opcional): ID do agendamento sendo editado

**Retorno:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nome": "Prof. João Silva"
    },
    {
      "id": 2,
      "nome": "Prof. Maria Santos"
    }
  ]
}
```

**Como usar:**
```javascript
fetch(`api/get_professores_disponiveis.php?data=2025-11-13&hora_inicio=14:00&hora_fim=15:00`)
  .then(res => res.json())
  .then(data => {
    // Preencher select com professores disponíveis
    data.data.forEach(prof => {
      // Apenas professores livres aparecem
    });
  });
```

### 4. Filtro Inteligente de Salas 🚪

**Nova API:** `get_salas_disponiveis.php`

**Parâmetros:**
- `data`: Data do agendamento (YYYY-MM-DD)
- `hora_inicio`: Hora de início (HH:MM)
- `hora_fim`: Hora de fim (HH:MM)
- `agendamento_id` (opcional): ID do agendamento sendo editado

**Retorno:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nome": "Sala 101",
      "capacidade": 30,
      "tipo": "Teoria"
    },
    {
      "id": 2,
      "nome": "Lab 01",
      "capacidade": 25,
      "tipo": "Laboratório"
    }
  ]
}
```

**Como usar:**
```javascript
fetch(`api/get_salas_disponiveis.php?data=2025-11-13&hora_inicio=14:00&hora_fim=15:00`)
  .then(res => res.json())
  .then(data => {
    // Preencher select com salas disponíveis
    data.data.forEach(sala => {
      // Apenas salas livres aparecem
    });
  });
```

### 5. Filtro de Disciplinas por Curso 📚

**Nova API:** `get_disciplinas_por_curso.php`

**Parâmetros:**
- `curso_id`: ID do curso (obrigatório)

**Retorno:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nome": "Matemática Aplicada",
      "sigla": "MAT",
      "carga_horaria": 80
    },
    {
      "id": 2,
      "nome": "Programação I",
      "sigla": "PRG1",
      "carga_horaria": 120
    }
  ]
}
```

**Como usar:**
```javascript
// Quando o usuário seleciona uma turma
document.getElementById('turma').addEventListener('change', async (e) => {
  const turmaId = e.target.value;

  // Buscar curso da turma
  const turma = await fetch(`api/turmas.php?id=${turmaId}`).then(r => r.json());
  const cursoId = turma.curso_id;

  // Buscar disciplinas do curso
  const disciplinas = await fetch(`api/get_disciplinas_por_curso.php?curso_id=${cursoId}`)
    .then(r => r.json());

  // Preencher select com disciplinas filtradas
  const selectDisciplina = document.getElementById('disciplina');
  selectDisciplina.innerHTML = '<option value="">Selecione...</option>';

  disciplinas.data.forEach(disc => {
    selectDisciplina.innerHTML += `<option value="${disc.id}">${disc.nome} (${disc.sigla})</option>`;
  });
});
```

## 🔧 Arquivos Modificados

### Novos Arquivos
1. `api/get_professores_disponiveis.php` - Filtra professores por disponibilidade
2. `api/get_salas_disponiveis.php` - Filtra salas por disponibilidade
3. `api/get_disciplinas_por_curso.php` - Lista disciplinas de um curso

### Arquivos Alterados
1. `api/save_agendamento.php` - Adicionada validação de duração máxima

## 🎯 Próximos Passos (Frontend)

Para completar a implementação, o JavaScript precisa ser atualizado:

### 1. Atualizar formulário de agendamento

```javascript
// Quando data e horário mudarem, recarregar professores e salas
async function atualizarRecursosDisponiveis() {
  const data = document.getElementById('data').value;
  const horaInicio = document.getElementById('hora_inicio').value;
  const horaFim = document.getElementById('hora_fim').value;

  if (!data || !horaInicio || !horaFim) return;

  // Buscar professores disponíveis
  const profs = await fetch(
    `../api/get_professores_disponiveis.php?data=${data}&hora_inicio=${horaInicio}&hora_fim=${horaFim}`
  ).then(r => r.json());

  // Preencher select
  const selectProf = document.getElementById('professor');
  selectProf.innerHTML = '<option value="">Selecione...</option>';
  profs.data.forEach(p => {
    selectProf.innerHTML += `<option value="${p.id}">${p.nome}</option>`;
  });

  // Mesma lógica para salas...
}

// Adicionar eventos
document.getElementById('data').addEventListener('change', atualizarRecursosDisponiveis);
document.getElementById('hora_inicio').addEventListener('change', atualizarRecursosDisponiveis);
document.getElementById('hora_fim').addEventListener('change', atualizarRecursosDisponiveis);
```

### 2. Filtrar disciplinas ao selecionar turma

```javascript
document.getElementById('turma').addEventListener('change', async (e) => {
  const turmaId = e.target.value;
  if (!turmaId) return;

  // Buscar informações da turma
  const turma = await fetch(`../api/turmas.php?id=${turmaId}`).then(r => r.json());
  const cursoId = turma.curso_id;

  // Buscar disciplinas do curso
  const disciplinas = await fetch(
    `../api/get_disciplinas_por_curso.php?curso_id=${cursoId}`
  ).then(r => r.json());

  // Preencher select
  const selectDisc = document.getElementById('disciplina');
  selectDisc.innerHTML = '<option value="">Selecione...</option>';
  disciplinas.data.forEach(d => {
    selectDisc.innerHTML += `<option value="${d.id}">${d.nome}</option>`;
  });
});
```

### 3. Exibir múltiplas aulas na mesma célula

```javascript
// Ao renderizar o grid semanal
function renderizarCelula(horario, dia, aulas) {
  const celula = document.createElement('div');
  celula.className = 'time-cell';

  // Agrupar aulas do mesmo horário
  aulas.forEach(aula => {
    const aulaDiv = document.createElement('div');
    aulaDiv.className = 'aula-item';
    aulaDiv.innerHTML = `
      <div class="aula-header">
        <strong>${aula.disciplina_nome}</strong>
      </div>
      <div class="aula-info">
        Prof: ${aula.professor_nome}<br>
        Turma: ${aula.turma_nome}<br>
        Sala: ${aula.sala}
      </div>
    `;
    celula.appendChild(aulaDiv);
  });

  return celula;
}
```

## ✅ Checklist de Implementação

### Backend (Concluído)
- [x] API para professores disponíveis
- [x] API para salas disponíveis
- [x] API para disciplinas por curso
- [x] Validação de duração máxima (1 hora)
- [x] Sistema de detecção de conflitos

### Frontend (Pendente)
- [ ] Integrar filtro de professores disponíveis
- [ ] Integrar filtro de salas disponíveis
- [ ] Implementar filtro de disciplinas por curso
- [ ] Ajustar visualização para múltiplas aulas
- [ ] Adicionar validação de duração no formulário
- [ ] Testar todos os cenários

## 🐛 Testes Recomendados

1. **Teste de múltiplos agendamentos:**
   - Agendar 2 aulas no mesmo horário com recursos diferentes ✅
   - Tentar agendar com mesmo professor no mesmo horário ❌

2. **Teste de duração:**
   - Agendar aula de 60 minutos ✅
   - Tentar agendar aula de 90 minutos ❌

3. **Teste de filtros:**
   - Verificar se professores ocupados não aparecem
   - Verificar se salas ocupadas não aparecem
   - Verificar se apenas disciplinas do curso aparecem

## 📝 Notas Técnicas

- Todas as APIs retornam JSON no formato padrão `{success, data/message}`
- Headers CORS configurados para desenvolvimento local
- Queries otimizadas com índices nas colunas de data e horário
- Validações no backend para segurança
- IDs de agendamento em edição são excluídos da verificação de conflitos
