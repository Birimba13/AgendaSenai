<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salas - Agenda Senai</title>
    <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
    <div class="header">
        <div class="logo">
            <svg width="175" height="50" viewBox="0 0 112 32" fill="none"><path d="M9.03241 6.00271C8.71306 5.74823 8.35879 5.54365 7.97956 5.39396C7.58038 5.22929 7.15625 5.07461 6.70218 4.92991L5.26512 4.48582C4.72123 4.3461 4.2023 4.12655 3.72328 3.83215C3.45882 3.67747 3.28917 3.40303 3.2742 3.09866C3.2742 2.89407 3.31911 2.69448 3.41391 2.51485C3.50872 2.35019 3.63346 2.20548 3.79314 2.09571C3.9578 1.98094 4.14242 1.89612 4.33702 1.84622C4.54659 1.79133 4.76115 1.76638 4.97571 1.76638C5.82897 1.65661 6.61236 2.26536 6.71715 3.11862C6.72713 3.20843 6.73212 3.30324 6.72713 3.39305H9.69605C9.71102 2.84418 9.59126 2.30029 9.34676 1.81129C9.12222 1.38716 8.79789 1.0279 8.3987 0.758447C7.9596 0.474029 7.47559 0.279428 6.96664 0.174642C6.37784 0.0548876 5.76909 0 5.16034 0C4.53661 0 3.91289 0.0648672 3.29915 0.199591C2.74029 0.314356 2.21137 0.523927 1.72238 0.823314C1.27829 1.09775 0.909042 1.47199 0.639593 1.91608C0.360166 2.40009 0.220452 2.95395 0.235421 3.51281C0.235421 3.8571 0.290309 4.2014 0.410063 4.52573C0.534808 4.84009 0.72442 5.12451 0.968919 5.36402C1.26332 5.64345 1.59763 5.87298 1.96189 6.04263C2.43591 6.26717 2.92491 6.45179 3.42888 6.59649L5.28009 7.13539C5.49964 7.19527 5.7142 7.26513 5.92876 7.34995C6.11338 7.41981 6.28803 7.50962 6.45269 7.6194C6.5924 7.71421 6.71216 7.83895 6.80197 7.98365C6.89179 8.13834 6.93171 8.31797 6.92672 8.4976C6.93171 8.73711 6.87183 8.97163 6.75208 9.17621C6.63731 9.36582 6.48263 9.5255 6.303 9.65024C6.10839 9.77998 5.89383 9.87478 5.66929 9.93466C5.43477 9.99953 5.19526 10.0295 4.95076 10.0295C3.6634 10.0295 3.00475 9.40075 2.97481 8.14832H0.000901286C-0.00907828 8.74709 0.120656 9.34087 0.380125 9.87977C0.614644 10.3438 0.963929 10.738 1.39305 11.0324C1.8571 11.3468 2.38103 11.5663 2.93489 11.6811C3.5686 11.8158 4.22226 11.8857 4.87093 11.8807C5.4697 11.8807 6.06349 11.8158 6.64729 11.701C7.2261 11.5913 7.78496 11.3817 8.29891 11.0923C8.78791 10.8129 9.20705 10.4237 9.51641 9.95462C9.84574 9.43069 10.0104 8.82194 9.99044 8.20321C10.0054 7.75911 9.92059 7.31502 9.73597 6.90586C9.5713 6.56157 9.33179 6.25719 9.03241 6.01768V6.00271Z" fill="white"></path></svg>
        </div>
        <div class="user-info">
            <a href="index.php" class="btn-voltar">← Voltar ao Menu</a>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Gestão de Salas</h1>
            <button class="btn-adicionar" onclick="abrirModal()">+ Adicionar Sala</button>
        </div>

        <div class="filtros">
            <div class="filtros-grid">
                <div class="campo-filtro">
                    <label>Buscar</label>
                    <input type="text" id="busca" placeholder="Código ou nome..." onkeyup="filtrarTabela()">
                </div>
                <div class="campo-filtro">
                    <label>Tipo</label>
                    <select id="filtroTipo" onchange="filtrarTabela()">
                        <option value="">Todos</option>
                        <option value="sala_aula">Sala de Aula</option>
                        <option value="laboratorio">Laboratório</option>
                        <option value="auditorio">Auditório</option>
                        <option value="oficina">Oficina</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="tabela-container">
            <table id="tabelaSalas">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Capacidade</th>
                        <th>Local</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div id="modal" class="modal">
        <div class="modal-conteudo">
            <div class="modal-header">
                <h2 id="modalTitulo">Adicionar Sala</h2>
                <button class="btn-fechar" onclick="fecharModal()">×</button>
            </div>
            <form id="formSala">
                <div class="form-group">
                    <label>Código *</label>
                    <input type="text" id="codigo" required maxlength="20">
                </div>
                <div class="form-group">
                    <label>Nome *</label>
                    <input type="text" id="nome" required>
                </div>
                <div class="form-group">
                    <label>Tipo *</label>
                    <select id="tipo" required>
                        <option value="sala_aula">Sala de Aula</option>
                        <option value="laboratorio">Laboratório</option>
                        <option value="auditorio">Auditório</option>
                        <option value="oficina">Oficina</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Capacidade</label>
                    <input type="number" id="capacidade" min="1">
                </div>
                <div class="form-group">
                    <label>Local</label>
                    <input type="text" id="local" value="Afonso Pena">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select id="ativo">
                        <option value="1">Ativo</option>
                        <option value="0">Inativo</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancelar" onclick="fecharModal()">Cancelar</button>
                    <button type="submit" class="btn-salvar">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="./assets/js/salas.js"></script>
</body>
</html>
