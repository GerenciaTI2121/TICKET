<?php
session_start();
// Verifica se o usuário está logado, caso contrário, redireciona para a página de login
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['usuario'];

// Caminho para o arquivo JSON de tickets
$file = 'tickets.json';
$tickets = [];

// Carrega tickets do arquivo JSON
if (file_exists($file)) {
    $json_content = file_get_contents($file);
    $tickets = json_decode($json_content, true);
    if (!is_array($tickets)) { // Garante que $tickets seja um array
        $tickets = [];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gestão de Tickets de Serviço</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Variáveis de cor globais */
        :root {
            --main-color: #2ecc71;
            --text-color: #333;
            --bg-light: #f9f9f9;
            --white: #fff;
            --danger: #e74c3c;
            --resolved: #27ae60;
            --pending: #f39c12; /* Cor para tickets abertos/pendentes */
            --info-color: #3498db; /* Cor para botões de informação/relatório */
            --secondary-button-color: #6c757d; /* Cor para botões secundários */
            --border-color: #ddd; /* Cor de borda para itens de ticket */
            --shadow-color: rgba(0,0,0,0.1);
            --archived-bg: #f0f8ff; /* Cor de fundo para tickets arquivados */
            --archived-border: #a0c4ff;
            /* Removidas variáveis específicas de dashboard */
        }

        /* Estilos base */
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 20px;
            background: var(--bg-light);
            color: var(--text-color);
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* Garante que o body ocupe a altura total da viewport */
        }
        h1, h2 {
            text-align: center;
            color: var(--main-color);
            margin-bottom: 20px;
        }
        form {
            max-width: 600px;
            margin: 20px auto;
            background: var(--white);
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px var(--shadow-color);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: var(--text-color);
        }
        input[type="text"], textarea, select {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 5px; /* Ajuste para espaçamento dentro do form-group */
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
            box-sizing: border-box;
            color: var(--text-color);
            background-color: var(--white);
        }
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        button[type="submit"] {
            background: var(--main-color);
            color: var(--white);
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            transition: background 0.3s ease;
            width: 100%;
            box-sizing: border-box;
            margin-top: 15px;
        }
        button[type="submit"]:hover {
            background: #27ae60;
        }
        .error-messages {
            color: var(--danger);
            margin-bottom: 15px;
            font-size: 0.9em;
        }
        .error-messages ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .error-messages li {
            margin-bottom: 5px;
        }
        .ticket-list {
            max-width: 800px;
            margin: 30px auto;
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px var(--shadow-color);
        }

        /* ESTILOS PARA O LAYOUT RETANGULAR DEITADO DO TICKET */
        .ticket-item {
            border: 1px solid var(--border-color);
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            display: flex;
            flex-direction: column; /* Conteúdo principal em coluna */
            gap: 10px;
        }
        /* Estilo específico para tickets arquivados */
        .ticket-item.archived-item {
            background-color: var(--archived-bg);
            border-color: var(--archived-border);
        }

        /* Cores de fundo dos tickets */
        .ticket-item.status-Aberto-bg {
            background-color: #ffeccf; /* Laranja/amarelo claro para aberto */
        }
        .ticket-item.status-Resolvido-bg {
            background-color: #e6ffe6; /* Verde claro para resolvido */
        }

        .ticket-item h3 {
            margin: 0;
            color: var(--main-color);
            font-size: 1.2em;
            display: flex; /* Para alinhar o ID e o status */
            justify-content: space-between;
            align-items: center;
        }
        .ticket-item h3 .status { /* Estilo para o status dentro do H3 */
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9em;
            color: var(--white);
        }
        .ticket-item h3 .status.Aberto {
            background: var(--pending);
        }
        .ticket-item h3 .status.Resolvido {
            background: var(--resolved);
        }

        .ticket-item p {
            margin: 0;
            line-height: 1.5;
        }
        
        .ticket-item .actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            flex-wrap: wrap; /* Para que os botões quebrem linha se necessário */
        }
        .ticket-item .actions button {
            width: auto;
            padding: 8px 15px;
            font-size: 0.9em;
            margin-top: 0; /* Override default button margin-top */
            background-color: var(--main-color); /* Default para botões de ação do ticket */
            color: var(--white);
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .ticket-item .actions button:hover {
            opacity: 0.9;
        }
        .ticket-item .actions .resolve-btn {
            background: var(--resolved);
        }
        .ticket-item .actions .resolve-btn:hover {
            background: #218c53;
        }
        .ticket-item .actions .delete-btn {
            background: var(--danger);
        }
        .ticket-item .actions .delete-btn:hover {
            background: #c0392b;
        }

        /* Grid para as informações detalhadas dentro do ticket-item (HORIZONTAL) */
        .info-grid-details {
            display: flex; /* Alterado para flexbox */
            flex-wrap: wrap; /* Permite que os itens quebrem linha se não houver espaço */
            gap: 8px 20px; /* Espaçamento entre itens (linha e coluna) */
            margin-bottom: 5px;
            border: 1px solid var(--border-color);
            padding: 10px;
            border-radius: 5px;
            background-color: #fcfcfc;
        }
        .info-grid-details div {
            white-space: nowrap; /* Impede que o texto quebre dentro do div */
            /* A largura mínima aqui ajuda a controlar quantos itens ficam por linha */
            flex: 1 1 auto; /* Permite que o item cresça, diminua e tenha uma base flexível */
            min-width: 150px; /* Largura mínima para cada item. Ajuste se necessário */
            padding: 2px 0;
        }
        .info-grid-details div strong {
            color: #555;
            display: inline; /* Altera para inline, para que o valor fique ao lado */
            margin-right: 5px; /* Espaço entre o label e o valor */
            font-size: 0.85em;
        }
        /* Ajuste para remover o "display: block" apenas para o strong */
        .info-grid-details div strong + span { /* Para o valor que vem depois do strong */
            display: inline;
        }

        .description-box {
            border: 1px solid var(--border-color);
            padding: 10px;
            border-radius: 5px;
            background-color: #fcfcfc;
            margin-top: 5px;
        }
        .description-box strong {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-size: 0.9em;
        }

        .tech-selection {
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 10px;
            background-color: #f0f0f0;
        }
        .tech-selection label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .tech-selection div {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .tech-selection input[type="checkbox"] {
            display: none;
        }
        .tech-selection label.checkbox-label {
            background-color: #e0e0e0;
            border: 1px solid #bbb;
            padding: 8px 12px;
            border-radius: 20px;
            cursor: pointer;
            transition: background-color 0.3s, border-color 0.3s;
            font-size: 0.9em;
            color: #555;
        }
        .tech-selection input[type="checkbox"]:checked + label.checkbox-label {
            background-color: var(--main-color);
            border-color: var(--main-color);
            color: var(--white);
        }
        /* Estilos para a tabela do relatório */
        #reportContainer table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 0.9em;
        }
        #reportContainer th, #reportContainer td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            color: var(--text-color);
        }
        #reportContainer th {
            background-color: #f2f2f2;
            font-weight: bold;
            color: var(--text-color);
        }
        #reportContainer tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        #reportContainer tr:hover {
            background-color: #f1f1f1;
        }

        /* CONTAINER DOS BOTÕES DE AÇÃO GLOBAL (RELATÓRIO, PDF, ARQUIVAR) */
        .main-action-buttons {
            max-width: 800px;
            margin: 20px auto 30px auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 10px;
            padding: 0 10px;
            box-sizing: border-box;
        }
        .main-action-buttons .action-button {
            background-color: var(--info-color);
            color: var(--white);
            padding: 12px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 1em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background-color 0.3s ease;
            width: 100%;
            box-sizing: border-box;
        }
        .main-action-buttons .action-button:hover {
            opacity: 0.9;
        }
        .main-action-buttons .action-button.logout-btn {
            background-color: var(--danger);
        }
        .main-action-buttons .action-button.archive-all-btn {
            background-color: #a04000;
        }
        .main-action-buttons .action-button.archive-all-btn:hover {
            background-color: #8a3700;
        }
        .main-action-buttons .action-button.view-archive-btn {
            background-color: #3f51b5;
        }
        .main-action-buttons .action-button.view-archive-btn:hover {
            background-color: #303f9f;
        }
        .main-action-buttons .action-button.active { /* Estilo para o botão ativo da aba */
            outline: 2px solid var(--main-color);
            box-shadow: 0 0 8px rgba(0,0,0,0.3);
            font-weight: bold;
        }


        /* Estilos para os botões de filtro */
        .filter-buttons {
            text-align: center;
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .filter-buttons .filter-button {
            background-color: var(--secondary-button-color);
            color: var(--white);
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: background-color 0.3s ease, font-weight 0.2s ease, box-shadow 0.2s ease;
        }
        .filter-buttons .filter-button:hover {
            opacity: 0.9;
        }
        .filter-buttons .filter-button.active {
            font-weight: bold;
            background-color: var(--main-color);
            box-shadow: 0 0 5px rgba(0,0,0,0.3);
        }
        /* Cores específicas para filtros ativos */
        #filterAberto.active { background-color: var(--pending); }
        #filterResolvido.active { background-color: var(--resolved); }

        /* Botão Sair - Mantido no topo para consistência */
        .logout-button-container {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
            width: 100%;
            box-sizing: border-box;
            padding-right: 20px;
        }
        .logout-button-container .action-button {
            background-color: var(--danger);
            color: var(--white);
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9em;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: background-color 0.3s ease;
        }
        .logout-button-container .action-button:hover {
            opacity: 0.9;
        }

        /* Estilos para o filtro de mês/ano dos arquivados */
        .archive-filter-controls {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .archive-filter-controls label {
            margin: 0;
            font-weight: bold;
        }
        .archive-filter-controls select {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
            cursor: pointer;
        }

        /* REMOVIDOS OS ESTILOS DO DASHBOARD AQUI */
    </style>
</head>
<body>
    <div class="logout-button-container">
        <a href="logout.php" class="action-button logout-btn">
            <i class="fa-solid fa-right-from-bracket"></i> Sair
        </a>
    </div>

    <h1><i class="fa-solid fa-ticket"></i> Gestão de Tickets de Serviço</h1>
    <h2>Bem-vindo, <?php echo htmlspecialchars($username); ?>!</h2>

    <form id="ticketForm">
        <h2>Abrir Novo Ticket</h2>
        <div class="error-messages" id="formErrors"></div>

        <div class="form-group">
            <div class="tech-selection">
                <label>Atribuir a Funcionário(s) da TI:</label>
                <div id="techsContainer">
                    </div>
            </div>
        </div>

        <div class="form-group">
            <label for="sector">Setor:</label>
            <input type="text" id="sector" name="sector" required />
        </div>

        <div class="form-group">
            <label for="type">Tipo de Serviço:</label>
            <select id="type" name="type" required>
                <option value="">Selecione</option>
                <option value="Hardware">Hardware</option>
                <option value="Software">Software</option>
                <option value="Rede">Rede</option>
                <option value="Suporte Geral">Suporte Geral</option>
            </select>
        </div>

        <div class="form-group">
            <label for="priority">Prioridade:</label>
            <select id="priority" name="priority" required>
                <option value="">Selecione</option>
                <option value="Baixa">Baixa</option>
                <option value="Média">Média</option>
                <option value="Alta">Alta</option>
                <option value="Urgente">Urgente</option>
            </select>
        </div>

        <div class="form-group">
            <label for="description">Descrição do Problema:</label>
            <textarea id="description" name="description" required></textarea>
        </div>

        <button type="submit">Abrir Ticket</button>
    </form>

    <div class="main-action-buttons">
        <button onclick="showMainTickets()" class="action-button" id="showMainTicketsBtn">
            <i class="fa-solid fa-ticket"></i> Tickets Ativos
        </button>
        <button onclick="generateReport()" class="action-button">
            <i class="fa-solid fa-file-alt"></i> Relatório Geral
        </button>
        <a href="generate_pdf.php" target="_blank" class="action-button">
            <i class="fa-solid fa-file-pdf"></i> Baixar PDF
        </a>
        <?php if ($username === 'admin'): ?>
            <button id="archiveAllTicketsButton" class="action-button archive-all-btn">
                <i class="fa-solid fa-box-archive"></i> Arquivar Todos
            </button>
        <?php endif; ?>
        <button onclick="showArchivedTickets()" class="action-button view-archive-btn">
            <i class="fa-solid fa-folder-open"></i> Ver Arquivados
        </button>
    </div>

    <hr>

    <div class="ticket-list" id="activeTicketsSection" style="display: none;">
        <div class="filter-buttons">
            <button id="filterTodos" onclick="applyFilter('Todos')" class="filter-button secondary-button-color">
                <i class="fa-solid fa-list-ul"></i> Todos
            </button>
            <button id="filterAberto" onclick="applyFilter('Aberto')" class="filter-button" style="background-color: var(--pending);">
                <i class="fa-solid fa-clock"></i> Abertos
            </button>
            <button id="filterResolvido" onclick="applyFilter('Resolvido')" class="filter-button" style="background-color: var(--resolved);">
                <i class="fa-solid fa-check-double"></i> Resolvidos
            </button>
        </div>
        <h2>Tickets Existentes</h2>
        <div id="ticketsContainer">
            <p style="text-align: center;">Carregando tickets...</p>
        </div>
    </div>

    <div class="ticket-list" id="archivedTicketsSection" style="display: none;">
        <h2>Tickets Arquivados</h2>
        <div class="archive-filter-controls">
            <label for="archiveMonth">Mês:</label>
            <select id="archiveMonth" onchange="filterArchivedTickets()">
                <option value="0">Todos</option>
                <option value="1">Janeiro</option>
                <option value="2">Fevereiro</option>
                <option value="3">Março</option>
                <option value="4">Abril</option>
                <option value="5">Maio</option>
                <option value="6">Junho</option>
                <option value="7">Julho</option>
                <option value="8">Agosto</option>
                <option value="9">Setembro</option>
                <option value="10">Outubro</option>
                <option value="11">Novembro</option>
                <option value="12">Dezembro</option>
            </select>

            <label for="archiveYear">Ano:</label>
            <select id="archiveYear" onchange="filterArchivedTickets()">
                </select>
        </div>
        <div id="archivedTicketsContainer">
            <p style="text-align: center;">Carregando tickets arquivados...</p>
        </div>
    </div>

    <div class="ticket-list" id="reportContainer" style="display: none;">
        <h2>Relatório Geral de Tickets</h2>
        <div id="reportContent">
            <p style="text-align: center;">Clique no botão acima para gerar o relatório.</p>
        </div>
        <div style="text-align: center; margin-top: 20px;">
            <button onclick="hideReport()" class="action-button secondary-button-color">
                <i class="fa-solid fa-times"></i> Fechar Relatório
            </button>
        </div>
    </div>

    <script>
        const allTechs = ["Alexandre", "Bruno", "Jhonatan", "Vitoria", "Matheus", "Marcio", "Willy"];
        let currentSelectedTechs = [];
        let currentFilter = 'Todos'; // Para tickets ativos

        // Removidas variáveis para instâncias de gráfico Chart.js

        // Função para mostrar a seção de tickets ativos
        function showMainTickets() {
            document.getElementById('activeTicketsSection').style.display = 'block';
            document.getElementById('archivedTicketsSection').style.display = 'none';
            document.getElementById('reportContainer').style.display = 'none';
            // Removida a linha que ocultava o dashboard
            renderTickets(); // Renderiza tickets ativos
            updateFilterButtonStyles(); // Atualiza estilo dos botões de filtro de ativos
            updateActionButtonStyles('showMainTicketsBtn'); // Ativa o botão correspondente
        }

        // Função para mostrar a seção de tickets arquivados
        function showArchivedTickets() {
            document.getElementById('activeTicketsSection').style.display = 'none';
            document.getElementById('archivedTicketsSection').style.display = 'block';
            document.getElementById('reportContainer').style.display = 'none';
            // Removida a linha que ocultava o dashboard
            populateArchiveYearFilter(); // Popula os anos antes de carregar
            filterArchivedTickets(); // Carrega tickets arquivados com o filtro atual
            updateActionButtonStyles('view-archive-btn'); // Ativa o botão correspondente
        }

        // Removida a função showDashboard()

        // Função para atualizar os estilos dos botões de ação global
        function updateActionButtonStyles(activeButtonId) {
            const buttons = document.querySelectorAll('.main-action-buttons .action-button');
            buttons.forEach(button => {
                button.classList.remove('active');
            });
            const activeButton = document.getElementById(activeButtonId);
            if (activeButton) {
                activeButton.classList.add('active');
            }
            // Workaround para o botão "Ver Arquivados" que tem uma classe genérica
            if (activeButtonId === 'view-archive-btn') {
                document.querySelector('.main-action-buttons .action-button.view-archive-btn').classList.add('active');
            }
        }

        function renderTechCheckboxes() {
            const container = document.getElementById('techsContainer');
            container.innerHTML = '';
            allTechs.forEach(tech => {
                const checkboxId = `tech_${tech.toLowerCase().replace(/\s/g, '')}`;
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.id = checkboxId;
                checkbox.value = tech;
                checkbox.checked = currentSelectedTechs.includes(tech);
                checkbox.onchange = (e) => {
                    if (e.target.checked) {
                        currentSelectedTechs.push(tech);
                    } else {
                        currentSelectedTechs = currentSelectedTechs.filter(t => t !== tech);
                    }
                };

                const label = document.createElement('label');
                label.htmlFor = checkboxId;
                label.textContent = tech;
                label.classList.add('checkbox-label');

                container.appendChild(checkbox);
                container.appendChild(label);
            });
        }

        renderTechCheckboxes();

        function applyFilter(filterType) {
            currentFilter = filterType;
            renderTickets();
            updateFilterButtonStyles();
        }

        function updateFilterButtonStyles() {
            const buttons = document.querySelectorAll('.filter-buttons .filter-button');
            buttons.forEach(button => {
                button.classList.remove('active');
            });

            const activeButton = document.getElementById(`filter${currentFilter.replace(/\s/g, '')}`);
            if (activeButton) {
                activeButton.classList.add('active');
            }
        }

        async function renderTickets() {
            const ticketsContainer = document.getElementById('ticketsContainer');
            ticketsContainer.innerHTML = '<p style="text-align: center;">Carregando tickets...</p>';
            const res = await fetch('get_tickets.php');
            const tickets = await res.json();

            let filteredTickets = tickets;
            if (currentFilter !== 'Todos') {
                filteredTickets = tickets.filter(ticket => ticket.status === currentFilter);
            }

            ticketsContainer.innerHTML = '';

            if (filteredTickets.length === 0) {
                ticketsContainer.innerHTML = '<p style="text-align: center;">Nenhum ticket encontrado com este filtro.</p>';
                return;
            }

            filteredTickets.forEach(ticket => {
                const div = document.createElement('div');
                div.classList.add('ticket-item');

                let itemBgClass = `status-${ticket.status.replace(/\s/g, '')}-bg`;
                div.classList.add(itemBgClass);

                const createdAt = new Date(ticket.createdAt).toLocaleString('pt-BR');
                const techsText = ticket.techs && ticket.techs.length > 0 ? ticket.techs.join(', ') : 'Nenhum';

                let statusClass = ticket.status.replace(/\s/g, '');

                div.innerHTML = `
                    <h3>Ticket ID: <span style="color: #666;">${ticket.id.substring(0, 8)}...</span>
                        <span class="status ${statusClass}">${ticket.status}</span>
                    </h3>
                    <div class="info-grid-details">
                        <div><strong>Usuário:</strong> <span>${ticket.usuario}</span></div>
                        <div><strong>Setor:</strong> <span>${ticket.sector}</span></div>
                        <div><strong>Tipo:</strong> <span>${ticket.type}</span></div>
                        <div><strong>Prioridade:</strong> <span>${ticket.priority}</span></div>
                        <div><strong>Atribuído a:</strong> <span>${techsText}</span></div>
                        <div><strong>Criado em:</strong> <span>${createdAt}</span></div>
                    </div>
                    <div class="description-box">
                        <strong>Descrição:</strong> ${ticket.description}
                    </div>
                    <div class="actions">
                        <?php if ($username === 'admin'): ?>
                            ${ticket.status === 'Aberto' ? `<button class="resolve-btn" onclick="updateTicketStatus('${encodeURIComponent(ticket.id)}', 'Resolvido')"><i class="fa-solid fa-check"></i> Resolver</button>` : ''}
                            <button class="delete-btn" onclick="deleteTicket('${encodeURIComponent(ticket.id)}')"><i class="fa-solid fa-trash"></i> Apagar</button>
                        <?php endif; ?>
                    </div>
                `;
                ticketsContainer.appendChild(div);
            });
        }

        async function filterArchivedTickets() {
            const archivedTicketsContainer = document.getElementById('archivedTicketsContainer');
            archivedTicketsContainer.innerHTML = '<p style="text-align: center;">Carregando tickets arquivados...</p>';

            const month = document.getElementById('archiveMonth').value;
            const year = document.getElementById('archiveYear').value;

            const res = await fetch(`get_archived_tickets.php?month=${month}&year=${year}`);
            const tickets = await res.json();

            archivedTicketsContainer.innerHTML = '';

            if (tickets.length === 0) {
                archivedTicketsContainer.innerHTML = '<p style="text-align: center;">Nenhum ticket arquivado encontrado para este período.</p>';
                return;
            }

            tickets.forEach(ticket => {
                const div = document.createElement('div');
                div.classList.add('ticket-item', 'archived-item'); // Adiciona classe para estilo de arquivado

                const createdAt = new Date(ticket.createdAt).toLocaleString('pt-BR');
                const techsText = ticket.techs && ticket.techs.length > 0 ? ticket.techs.join(', ') : 'Nenhum';
                let statusClass = ticket.status.replace(/\s/g, '');

                div.innerHTML = `
                    <h3>Ticket ID: <span style="color: #666;">${ticket.id.substring(0, 8)}...</span>
                        <span class="status ${statusClass}">${ticket.status}</span>
                    </h3>
                    <div class="info-grid-details">
                        <div><strong>Usuário:</strong> <span>${ticket.usuario}</span></div>
                        <div><strong>Setor:</strong> <span>${ticket.sector}</span></div>
                        <div><strong>Tipo:</strong> <span>${ticket.type}</span></div>
                        <div><strong>Prioridade:</strong> <span>${ticket.priority}</span></div>
                        <div><strong>Atribuído a:</strong> <span>${techsText}</span></div>
                        <div><strong>Criado em:</strong> <span>${createdAt}</span></div>
                    </div>
                    <div class="description-box">
                        <strong>Descrição:</strong> ${ticket.description}
                    </div>
                    `;
                archivedTicketsContainer.appendChild(div);
            });
        }

        // Função para preencher os anos no filtro de arquivados
        function populateArchiveYearFilter() {
            const yearSelect = document.getElementById('archiveYear');
            const currentYear = new Date().getFullYear();
            
            // Limpa opções existentes, exceto "Todos" se já houver
            yearSelect.innerHTML = '<option value="0">Todos</option>';

            // Adiciona 5 anos para trás e 1 ano para frente (ajuste conforme necessidade)
            for (let i = currentYear + 1; i >= currentYear - 5; i--) {
                const option = document.createElement('option');
                option.value = i;
                option.textContent = i;
                yearSelect.appendChild(option);
            }
            // Seleciona o ano atual por padrão, se estiver na lista
            yearSelect.value = currentYear;
        }

        async function updateTicketStatus(id, status) {
            const res = await fetch('update_ticket.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id, status})
            });
            const data = await res.json();
            if (data.success) {
                renderTickets();
            } else {
                alert('Erro ao atualizar ticket: ' + (data.message || 'Erro desconhecido.'));
            }
        }

        async function deleteTicket(id) {
            if (!confirm('Confirma apagar este ticket?')) return;
            const res = await fetch('delete_ticket.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id})
            });
            const data = await res.json();
            if (data.success) {
                renderTickets();
            } else {
                alert('Erro ao apagar ticket: ' + (data.message || 'Erro desconhecido.'));
            }
        }

        async function generateReport() {
            const reportContainer = document.getElementById('reportContainer');
            const reportContent = document.getElementById('reportContent');

            document.getElementById('activeTicketsSection').style.display = 'none';
            document.getElementById('archivedTicketsSection').style.display = 'none';
            // Removida a linha que ocultava o dashboard
            reportContainer.style.display = 'block'; // Mostra o container do relatório

            reportContent.innerHTML = '<p style="text-align: center;">Gerando relatório...</p>';

            const res = await fetch('get_report.php');
            const tickets = await res.json();

            if (tickets.success === false && tickets.message) {
                reportContent.innerHTML = `<p style="text-align: center; color: red;">${tickets.message}</p>`;
                return;
            }

            if (tickets.length === 0) {
                reportContent.innerHTML = '<p style="text-align: center;">Nenhum ticket encontrado para o relatório.</p>';
                return;
            }

            let tableHtml = `
                <table border="1">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuário</th>
                            <th>Setor</th>
                            <th>Tipo</th>
                            <th>Prioridade</th>
                            <th>Status</th>
                            <th>Atribuído a</th>
                            <th>Criado Em</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            tickets.forEach(ticket => {
                const createdAt = new Date(ticket.createdAt).toLocaleString('pt-BR');
                const techsText = ticket.techs && ticket.techs.length > 0 ? ticket.techs.join(', ') : 'Nenhum';
                tableHtml += `
                    <tr>
                        <td>${ticket.id.substring(0, 8)}...</td>
                        <td>${ticket.usuario}</td>
                        <td>${ticket.sector}</td>
                        <td>${ticket.type}</td>
                        <td>${ticket.priority}</td>
                        <td>${ticket.status}</td>
                        <td>${techsText}</td>
                    </tr>
                `;
            });
            tableHtml += `
                    </tbody>
                </table>
            `;
            reportContent.innerHTML = tableHtml;
        }

        function hideReport() {
            document.getElementById('reportContainer').style.display = 'none';
            // Volta para a seção de tickets ativos por padrão
            showMainTickets();
        }

        document.getElementById('ticketForm').onsubmit = async e => {
            e.preventDefault();
            const formErrorsDiv = document.getElementById('formErrors');
            formErrorsDiv.innerHTML = '';

            if (!currentSelectedTechs.length) {
                formErrorsDiv.innerHTML = '<ul><li>Selecione ao menos um funcionário da TI.</li></ul>';
                return;
            }

            const sector = document.getElementById('sector').value.trim();
            const type = document.getElementById('type').value;
            const priority = document.getElementById('priority').value;
            const description = document.getElementById('description').value.trim();

            if (!sector || !type || !priority || !description) {
                formErrorsDiv.innerHTML = '<ul><li>Todos os campos são obrigatórios.</li></ul>';
                return;
            }

            const formData = new URLSearchParams();
            formData.append('techs', JSON.stringify(currentSelectedTechs));
            formData.append('sector', sector);
            formData.append('type', type);
            formData.append('priority', priority);
            formData.append('description', description);

            const res = await fetch('processa_ticket.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: formData
            });

            const data = await res.json();

            if (data.success) {
                alert('Ticket aberto com sucesso!');
                renderTickets();
                document.getElementById('ticketForm').reset();
                currentSelectedTechs = [];
                renderTechCheckboxes();
            } else {
                if (data.errors && data.errors.length > 0) {
                    formErrorsDiv.innerHTML = '<ul>' + data.errors.map(err => `<li>${err}</li>`).join('') + '</ul>';
                } else {
                    formErrorsDiv.innerHTML = '<ul><li>Erro ao abrir ticket. Tente novamente.</li></ul>';
                }
            }
        };

        // Removidas todas as funções do Dashboard (fetchAllTickets, renderDashboard, renderChart)

        document.addEventListener('DOMContentLoaded', () => {
            // Event listener para o botão "Arquivar Todos os Tickets"
            const archiveAllButton = document.getElementById('archiveAllTicketsButton');
            if (archiveAllButton) {
                archiveAllButton.addEventListener('click', async () => {
                    if (!confirm('Tem certeza que deseja arquivar TODOS os tickets existentes? Esta ação não pode ser desfeita.')) {
                        return;
                    }

                    try {
                        const response = await fetch('archive_tickets.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ archive_all: true }) // Enviamos uma flag para o backend
                        });
                        const result = await response.json();

                        if (result.success) {
                            alert(result.message);
                            renderTickets(); // Atualiza a lista de tickets ativos
                            if (document.getElementById('archivedTicketsSection').style.display === 'block') {
                                filterArchivedTickets(); // Se estiver na aba de arquivados, atualiza também
                            }
                            // Removida a atualização do dashboard aqui
                        } else {
                            alert('Erro ao arquivar tickets: ' + result.message);
                        }
                    } catch (error) {
                        console.error('Erro na requisição de arquivamento:', error);
                        alert('Ocorreu um erro ao tentar arquivar os tickets. Verifique o console para detalhes.');
                    }
                });
            }
            showMainTickets(); // Mostra os tickets ativos ao carregar a página por padrão
            populateArchiveYearFilter(); // Popula os anos para o filtro de arquivados
        });
    </script>
</body>
</html>