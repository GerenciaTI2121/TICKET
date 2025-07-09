<?php
session_start();
// Verifica se o usuário está logado, caso contrário, redireciona para a página de login
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['usuario'];

// Caminho para o arquivo JSON de tickets (já não é mais usado, mas mantido para compatibilidade se houver outras referências)
$file = 'tickets.json';
$tickets = [];

// Carrega tickets do arquivo JSON (parte legada, agora os tickets vêm do DB)
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Variáveis de cor globais - NOVA PALETA PRETO E VERDE */
        :root {
            --primary-green: #2ecc71; /* Verde principal para botões, destaques */
            --dark-background: #1a1a1a; /* Fundo escuro */
            --dark-text: #e0e0e0; /* Texto claro para fundo escuro */
            --light-text: #333; /* Texto escuro para fundo claro */
            --card-background: #ffffff; /* Fundo dos cards de ticket */
            --border-light: #e0e0e0; /* Borda clara */
            --shadow-color: rgba(0, 0, 0, 0.1); /* Sombra suave */
            --danger-red: #e74c3c; /* Vermelho para ações de perigo */
            --info-blue: #3498db; /* Azul para informações */
            --status-resolved: #28a745; /* Verde para status resolvido */
            --status-open: #ffc107; /* Amarelo para status aberto/pendente */
            --secondary-button-bg: #6c757d; /* Cinza para botões secundários */
            --archive-bg: #f8f9fa; /* Fundo para seções de arquivo */
            --header-bg: #2b2b2b; /* Fundo do cabeçalho */
            --header-text: #ffffff; /* Cor do texto do cabeçalho */
            --input-border: #cccccc;
            --input-focus-border: var(--primary-green);
        }

        /* Estilos base */
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: var(--dark-background);
            color: var(--light-text); /* Default para elementos com fundo claro */
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Layout Principal */
        .main-wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Header / Navbar */
        header {
            background-color: var(--header-bg);
            color: var(--header-text);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        header h1 {
            margin: 0;
            font-size: 1.8em;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--primary-green);
        }
        header h1 i {
            color: var(--header-text);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .user-info span {
            font-size: 1em;
            font-weight: 500;
            color: var(--header-text);
        }
        .user-info .action-button {
            background-color: var(--danger-red);
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 0.9em;
            display: flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            color: var(--header-text);
            transition: background-color 0.2s ease;
        }
        .user-info .action-button:hover {
            background-color: #c0392b;
        }

        /* Conteúdo principal */
        main {
            flex-grow: 1; /* Permite que o conteúdo ocupe o espaço restante */
            padding: 30px 20px;
            max-width: 1200px; /* Largura máxima para o conteúdo central */
            margin: 0 auto; /* Centraliza o conteúdo */
            width: 100%;
            box-sizing: border-box;
        }

        h2 {
            text-align: center;
            color: var(--primary-green);
            margin-bottom: 30px;
            font-weight: 600;
            font-size: 1.8em;
        }

        /* Botões de Ação Principal (abas de navegação) */
        .main-action-buttons {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 30px;
            background-color: var(--card-background);
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 8px var(--shadow-color);
        }
        .main-action-buttons .action-button {
            background-color: var(--secondary-button-bg);
            color: var(--header-text);
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-size: 1em;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.2s ease, transform 0.1s ease;
        }
        .main-action-buttons .action-button:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }
        .main-action-buttons .action-button.active {
            background-color: var(--primary-green);
            box-shadow: 0 4px 8px rgba(46, 204, 113, 0.3);
            color: var(--header-text); /* Garante que o texto fique branco */
        }
        .main-action-buttons .action-button.active:hover {
            background-color: #27ae60;
            transform: translateY(0);
        }
        .main-action-buttons .action-button.danger {
            background-color: var(--danger-red);
        }
        .main-action-buttons .action-button.danger:hover {
            background-color: #c0392b;
        }
        .main-action-buttons .action-button.info-color {
            background-color: var(--info-blue);
        }
        .main-action-buttons .action-button.info-color:hover {
            background-color: #218cda;
        }

        /* Mensagens */
        #message {
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 8px;
            font-weight: 600;
            display: none; /* Controlado por JS */
            color: var(--header-text);
            background-color: var(--primary-green);
        }

        /* Formulário */
        form {
            max-width: 700px;
            margin: 20px auto;
            background: var(--card-background);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px var(--shadow-color);
            color: var(--light-text);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--light-text);
        }
        input[type="text"], textarea, select {
            width: calc(100% - 24px);
            padding: 12px;
            border: 1px solid var(--input-border);
            border-radius: 8px;
            font-size: 1em;
            box-sizing: border-box;
            color: var(--light-text);
            background-color: var(--card-background);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        input[type="text"]:focus, textarea:focus, select:focus {
            border-color: var(--input-focus-border);
            box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.2);
            outline: none;
        }
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        button[type="submit"] {
            background: var(--primary-green);
            color: var(--header-text);
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: 600;
            transition: background 0.2s ease, transform 0.1s ease;
            width: 100%;
            box-sizing: border-box;
            margin-top: 15px;
        }
        button[type="submit"]:hover {
            background: #27ae60;
            transform: translateY(-2px);
        }
        .error-messages {
            color: var(--danger-red);
            margin-top: 15px;
            font-size: 0.9em;
            background-color: rgba(231, 76, 60, 0.1);
            border: 1px solid var(--danger-red);
            border-radius: 8px;
            padding: 10px;
        }
        .error-messages ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .error-messages li {
            margin-bottom: 5px;
        }

        /* Lista de Tickets */
        .ticket-list {
            max-width: 1000px;
            margin: 30px auto;
            display: grid; /* Usando grid para os cards */
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); /* Colunas adaptáveis */
            gap: 25px; /* Espaçamento entre os cards */
            padding: 0 10px;
        }

        /* Estilo do Card de Ticket */
        .ticket-item {
            background: var(--card-background);
            border: 1px solid var(--border-light);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px var(--shadow-color);
            display: flex;
            flex-direction: column;
            gap: 15px;
            transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease; /* Adicionado transição para borda/sombra */
            color: var(--light-text);
        }
        .ticket-item:hover {
            transform: translateY(-5px);
        }

        /* NOVOS ESTILOS PARA CORES DO TICKET */
        .ticket-item.status-open-ticket {
            border-left: 8px solid var(--status-open);
            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.2); /* Sombra mais amarelada */
        }

        .ticket-item.status-resolved-ticket {
            border-left: 8px solid var(--status-resolved);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2); /* Sombra mais esverdeada */
        }
        /* FIM NOVOS ESTILOS */

        /* Status Badges */
        .ticket-item h3 {
            margin: 0;
            font-size: 1.25em;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            color: var(--light-text); /* Cor do texto do título do ticket */
        }
        .ticket-item h3 .status {
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 20px; /* Pills/badges */
            font-size: 0.85em;
            color: var(--header-text);
            text-transform: uppercase;
        }
        .ticket-item h3 .status.Aberto {
            background: var(--status-open);
        }
        .ticket-item h3 .status.Resolvido {
            background: var(--status-resolved);
        }

        /* Informações do Ticket (Grid Interno) */
        .info-grid-details {
            display: grid;
            grid-template-columns: 1fr 1fr; /* Duas colunas */
            gap: 10px 20px; /* Espaçamento entre linhas e colunas */
            padding-bottom: 15px;
            border-bottom: 1px dashed var(--border-light);
        }
        .info-grid-details div {
            font-size: 0.9em;
        }
        .info-grid-details div strong {
            display: block; /* Label em cima do valor */
            color: #777; /* Cor do label */
            font-weight: 500;
            margin-bottom: 3px;
        }
        /* Ajuste para que o valor não seja bold se não for necessário */
        .info-grid-details div span {
            font-weight: 400;
        }

        .description-box {
            background-color: #f9f9f9;
            border: 1px solid var(--border-light);
            padding: 15px;
            border-radius: 8px;
            font-size: 0.9em;
            color: #555;
            flex-grow: 1; /* Ocupa o espaço disponível */
        }
        .description-box strong {
            display: block;
            margin-bottom: 8px;
            color: var(--light-text);
            font-size: 1em;
            font-weight: 600;
        }
        .description-box p {
            margin: 0;
            line-height: 1.6;
        }

        /* Data de Criação/Atualização e Técnicos */
        .ticket-item p {
            margin: 0;
            font-size: 0.85em;
            color: #777;
        }
        .ticket-item p strong {
            color: #555;
            font-weight: 600;
        }

        /* Botões de Ação do Ticket */
        .ticket-item .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: flex-end; /* Alinha à direita */
            padding-top: 10px;
            border-top: 1px dashed var(--border-light);
        }
        .ticket-item .actions button {
            flex: 1 1 auto; /* Permite que os botões cresçam/diminuam */
            min-width: 90px; /* Largura mínima para os botões */
            padding: 10px 15px;
            font-size: 0.9em;
            border-radius: 8px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .ticket-item .actions .resolve-btn {
            background: var(--status-resolved);
            color: var(--header-text);
        }
        .ticket-item .actions .resolve-btn:hover {
            background: #218838;
        }
        .ticket-item .actions .edit-btn {
            background: var(--info-blue);
            color: var(--header-text);
        }
        .ticket-item .actions .edit-btn:hover {
            background: #218cda;
        }
        .ticket-item .actions .delete-btn {
            background: var(--danger-red);
            color: var(--header-text);
        }
        .ticket-item .actions .delete-btn:hover {
            background: #c0392b;
        }

        /* Filtros de Tickets */
        .filter-buttons {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 30px;
        }
        .filter-buttons .filter-button {
            background-color: var(--secondary-button-bg);
            color: var(--header-text);
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.95em;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.2s ease, transform 0.1s ease;
        }
        .filter-buttons .filter-button:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }
        .filter-buttons .filter-button.active {
            background-color: var(--primary-green);
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(46, 204, 113, 0.3);
            transform: translateY(0);
        }
        #filterAberto.active { background-color: var(--status-open); }
        #filterResolvido.active { background-color: var(--status-resolved); }

        /* Estilos específicos para tickets arquivados */
        .ticket-item.archived-item {
            background-color: var(--archive-bg);
            border-color: #d1d9e6; /* Uma cor de borda diferente para arquivados */
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border-left: none; /* Garante que tickets arquivados não tenham a borda de status ativa */
        }
        .ticket-item.archived-item h3 {
            color: #666;
        }
        .ticket-item.archived-item .description-box {
            background-color: #f0f0f0;
            border-color: #d1d1d1;
        }
        .ticket-item.archived-item .info-grid-details {
            border-color: #d1d1d1;
        }
        .ticket-item.archived-item .actions {
            display: none; /* Não mostra ações para tickets arquivados */
        }
        /* Filtro de arquivo */
        .archive-filter-controls {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            color: var(--header-text); /* Texto do filtro em branco/claro */
        }
        .archive-filter-controls label {
            font-weight: 500;
        }
        .archive-filter-controls select {
            background-color: var(--card-background);
            color: var(--light-text);
            border: 1px solid var(--border-light);
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 1em;
            cursor: pointer;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);
        }
        .archive-filter-controls select:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.2);
            outline: none;
        }

        /* Tech Selection (checkboxes personalizados) */
        .tech-selection {
            background-color: #f0f0f0;
            border: 1px solid var(--border-light);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .tech-selection label {
            font-weight: 600;
            color: var(--light-text);
            margin-bottom: 10px;
        }
        .tech-selection > div { /* Container dos checkboxes */
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .tech-selection input[type="checkbox"] {
            display: none; /* Esconde o checkbox nativo */
        }
        .tech-selection label.checkbox-label {
            background-color: #e9ecef;
            border: 1px solid #ced4da;
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
            font-size: 0.9em;
            color: #495057;
            margin-bottom: 0; /* Remove margin-bottom do label */
            display: inline-flex; /* Para que a label se ajuste ao conteúdo */
            align-items: center;
            gap: 5px;
        }
        .tech-selection input[type="checkbox"]:checked + label.checkbox-label {
            background-color: var(--primary-green);
            border-color: var(--primary-green);
            color: var(--header-text);
        }
        .tech-selection label.checkbox-label:hover {
            background-color: #d1d9e6;
        }
        .tech-selection input[type="checkbox"]:checked + label.checkbox-label:hover {
            background-color: #27ae60;
        }


        /* Modal de Edição */
        #editTicketModal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1001; /* Acima de outros elementos */
        }
        #editTicketModal > div { /* Conteúdo do modal */
            background: var(--card-background);
            padding: 30px;
            border-radius: 12px;
            width: 550px;
            max-width: 90%;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            color: var(--light-text);
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        #editTicketModal h2 {
            margin-top: 0;
            color: var(--primary-green);
            font-size: 1.6em;
            margin-bottom: 0;
        }
        #editTicketModal button {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 600;
            transition: background-color 0.2s ease;
            margin-top: 10px; /* Espaço entre os botões */
        }
        #editTicketModal #saveEditBtn {
            background-color: var(--primary-green);
            color: var(--header-text);
        }
        #editTicketModal #saveEditBtn:hover {
            background-color: #27ae60;
        }
        #editTicketModal #cancelEditBtn {
            background-color: var(--secondary-button-bg);
            color: var(--header-text);
        }
        #editTicketModal #cancelEditBtn:hover {
            background-color: #5a6268;
        }
        
        /* Media Queries para Responsividade */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                padding: 15px 20px;
                gap: 15px;
            }
            header h1 {
                font-size: 1.5em;
            }
            .user-info {
                width: 100%;
                justify-content: center;
            }
            main {
                padding: 20px 10px;
            }
            .main-action-buttons {
                flex-direction: column;
                align-items: stretch;
            }
            .main-action-buttons .action-button {
                width: 100%; /* Botões ocupam largura total em telas menores */
            }
            .ticket-list {
                grid-template-columns: 1fr; /* Uma coluna em telas menores */
            }
            .info-grid-details {
                grid-template-columns: 1fr; /* Uma coluna para detalhes do ticket */
            }
            form {
                padding: 20px;
            }
            input[type="text"], textarea, select {
                width: calc(100% - 20px); /* Ajusta padding */
            }
        }
        @media (max-width: 480px) {
            header h1 {
                font-size: 1.3em;
            }
            .user-info span {
                display: none; /* Esconde nome do usuário em telas muito pequenas */
            }
            .ticket-item .actions button {
                font-size: 0.85em;
                padding: 8px 12px;
            }
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <header>
            <h1><i class="fa-solid fa-headset"></i> Gestão de Tickets</h1>
            <div class="user-info">
                <span>Bem-vindo, <?php echo htmlspecialchars($username); ?>!</span>
                <a href="logout.php" class="action-button">
                    <i class="fa-solid fa-right-from-bracket"></i> Sair
                </a>
            </div>
        </header>

        <main>
            <div class="main-action-buttons">
                <button id="showMainTicketsBtn" class="action-button active"><i class="fa-solid fa-inbox"></i> Tickets Ativos</button>
                <button id="showNewTicketFormBtn" class="action-button"><i class="fa-solid fa-plus-circle"></i> Abrir Novo Ticket</button>
                <button id="showArchivedTicketsBtn" class="action-button"><i class="fa-solid fa-box-archive"></i> Tickets Arquivados</button>
                <button id="archiveAllResolvedBtn" class="action-button danger"><i class="fa-solid fa-box-archive"></i> Arquivar Resolvidos</button>
                <a href="generate_pdf.php" target="_blank" class="action-button info-color"><i class="fa-solid fa-file-pdf"></i> Gerar Relatório PDF</a>
            </div>

            <div id="message"></div>

            <form id="newTicketForm" style="display:none;">
                <h2>Abrir Novo Ticket</h2>
                <div class="form-group">
                    <label for="usuario">Solicitante:</label>
                    <input type="text" id="usuario" name="usuario" value="<?php echo htmlspecialchars($username); ?>" required readonly />
                </div>
                <div class="form-group">
                    <label for="sector">Setor:</label>
                    <input type="text" id="sector" name="sector" required />
                </div>
                <div class="form-group">
                    <label for="type">Tipo de Serviço:</label>
                    <select id="type" name="type" required>
                        <option value="">Selecione...</option>
                        <option value="Hardware">Hardware</option>
                        <option value="Software">Software</option>
                        <option value="Rede">Rede</option>
                        <option value="Outro">Outro</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="priority">Prioridade:</label>
                    <select id="priority" name="priority" required>
                        <option value="">Selecione...</option>
                        <option value="Baixa">Baixa</option>
                        <option value="Média">Média</option>
                        <option value="Alta">Alta</option>
                        <option value="Urgente">Urgente</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="description">Descrição:</label>
                    <textarea id="description" name="description" required></textarea>
                </div>
                
                <div class="tech-selection">
                    <label>Atribuir Técnicos:</label>
                    <div>
                        <input type="checkbox" id="techAlexandre" name="techs[]" value="Alexandre">
                        <label for="techAlexandre" class="checkbox-label"><i class="fa-solid fa-user-tie"></i> Alexandre</label>
                        
                        <input type="checkbox" id="techBruno" name="techs[]" value="Bruno">
                        <label for="techBruno" class="checkbox-label"><i class="fa-solid fa-user-tie"></i> Bruno</label>

                        <input type="checkbox" id="techJhonatan" name="techs[]" value="Jhonatan">
                        <label for="techJhonatan" class="checkbox-label"><i class="fa-solid fa-user-tie"></i> Jhonatan</label>

                        <input type="checkbox" id="techVitoria" name="techs[]" value="Vitoria">
                        <label for="techVitoria" class="checkbox-label"><i class="fa-solid fa-user-tie"></i> Vitoria</label>

                        <input type="checkbox" id="techMatheus" name="techs[]" value="Matheus">
                        <label for="techMatheus" class="checkbox-label"><i class="fa-solid fa-user-tie"></i> Matheus</label>

                        <input type="checkbox" id="techMarcio" name="techs[]" value="Marcio">
                        <label for="techMarcio" class="checkbox-label"><i class="fa-solid fa-user-tie"></i> Marcio</label>

                        <input type="checkbox" id="techWilly" name="techs[]" value="Willy">
                        <label for="techWilly" class="checkbox-label"><i class="fa-solid fa-user-tie"></i> Willy</label>
                    </div>
                </div>

                <button type="submit">Criar Ticket</button>
                <div class="error-messages" id="formErrorMessages"></div>
            </form>

            <section id="activeTicketsSection">
                <h2>Tickets Ativos</h2>
                <div class="filter-buttons">
                    <button id="filterTodos" class="filter-button active"><i class="fa-solid fa-list-ul"></i> Todos</button>
                    <button id="filterAberto" class="filter-button"><i class="fa-solid fa-hourglass-start"></i> Aberto</button>
                    <button id="filterResolvido" class="filter-button"><i class="fa-solid fa-check-double"></i> Resolvido</button>
                </div>
                <div id="ticketsContainer" class="ticket-list">
                    </div>
            </section>

            <section id="archivedTicketsSection" style="display:none;">
                <h2>Tickets Arquivados</h2>
                <div class="archive-filter-controls">
                    <label for="archiveYearFilter">Ano:</label>
                    <select id="archiveYearFilter"></select>
                    <label for="archiveMonthFilter">Mês:</label>
                    <select id="archiveMonthFilter">
                        <option value="">Todos</option>
                        <option value="01">Janeiro</option>
                        <option value="02">Fevereiro</option>
                        <option value="03">Março</option>
                        <option value="04">Abril</option>
                        <option value="05">Maio</option>
                        <option value="06">Junho</option>
                        <option value="07">Julho</option>
                        <option value="08">Agosto</option>
                        <option value="09">Setembro</option>
                        <option value="10">Outubro</option>
                        <option value="11">Novembro</option>
                        <option value="12">Dezembro</option>
                    </select>
                </div>
                <div id="archivedTicketsContainer" class="ticket-list">
                    </div>
            </section>

            <div id="editTicketModal" style="display:none;">
                <div>
                    <h2>Editar Ticket <span id="editTicketIdDisplay"></span></h2>
                    <input type="hidden" id="editTicketId">
                    <div class="form-group">
                        <label for="editStatus">Status:</label>
                        <select id="editStatus" required>
                            <option value="Aberto">Aberto</option>
                            <option value="Resolvido">Resolvido</option>
                        </select>
                    </div>
                    <div class="tech-selection">
                        <label>Atribuir Técnicos:</label>
                        <div id="editTechsContainer">
                            </div>
                    </div>
                    <button id="saveEditBtn">Salvar Alterações</button>
                    <button id="cancelEditBtn">Cancelar</button>
                </div>
            </div>
        </main>
    </div>

    <script>
        const ticketsContainer = document.getElementById('ticketsContainer');
        const archivedTicketsContainer = document.getElementById('archivedTicketsContainer');
        const newTicketForm = document.getElementById('newTicketForm');
        const activeTicketsSection = document.getElementById('activeTicketsSection');
        const archivedTicketsSection = document.getElementById('archivedTicketsSection');
        const showMainTicketsBtn = document.getElementById('showMainTicketsBtn');
        const showNewTicketFormBtn = document.getElementById('showNewTicketFormBtn');
        const showArchivedTicketsBtn = document.getElementById('showArchivedTicketsBtn');
        const archiveAllResolvedBtn = document.getElementById('archiveAllResolvedBtn');
        const messageDiv = document.getElementById('message');
        const formErrorMessages = document.getElementById('formErrorMessages');

        const filterTodos = document.getElementById('filterTodos');
        const filterAberto = document.getElementById('filterAberto');
        const filterResolvido = document.getElementById('filterResolvido');

        const editTicketModal = document.getElementById('editTicketModal');
        const editTicketIdDisplay = document.getElementById('editTicketIdDisplay');
        const editTicketIdInput = document.getElementById('editTicketId');
        const editStatusSelect = document.getElementById('editStatus');
        const editTechsContainer = document.getElementById('editTechsContainer');
        const saveEditBtn = document.getElementById('saveEditBtn');
        const cancelEditBtn = document.getElementById('cancelEditBtn');

        const archiveYearFilter = document.getElementById('archiveYearFilter');
        const archiveMonthFilter = document.getElementById('archiveMonthFilter');

        // Lista global de técnicos (importante para manter a consistência)
        const ALL_TECHS = ["Alexandre", "Bruno", "Jhonatan", "Vitoria", "Matheus", "Marcio", "Willy"];


        let currentFilter = 'Todos'; // 'Todos', 'Aberto', 'Resolvido'

        // Função para mostrar mensagens temporárias
        function showMessage(msg, type = 'success') {
            messageDiv.textContent = msg;
            messageDiv.style.backgroundColor = type === 'success' ? 'var(--primary-green)' : 'var(--danger-red)';
            messageDiv.style.display = 'block';
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 5000);
        }

        // Função para mostrar erros do formulário
        function showFormErrors(errors) {
            formErrorMessages.innerHTML = '';
            const ul = document.createElement('ul');
            errors.forEach(error => {
                const li = document.createElement('li');
                li.textContent = error;
                ul.appendChild(li);
            });
            formErrorMessages.appendChild(ul);
        }

        // Função para esconder erros do formulário
        function hideFormErrors() {
            formErrorMessages.innerHTML = '';
        }

        // Renderiza os tickets na UI
        function renderTickets(ticketsToRender, container, isArchivedView = false) {
            container.innerHTML = ''; // Limpa a lista existente
            if (ticketsToRender.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #777;">Nenhum ticket encontrado.</p>';
                return;
            }

            ticketsToRender.forEach(ticket => {
                const ticketItem = document.createElement('div');
                const statusClass = ticket.status === 'Aberto' ? 'Aberto' : 'Resolvido';
                
                ticketItem.classList.add('ticket-item');

                if (!isArchivedView) { // Adiciona classes de status apenas para tickets NÃO arquivados
                    if (ticket.status === 'Aberto') {
                        ticketItem.classList.add('status-open-ticket');
                    } else if (ticket.status === 'Resolvido') {
                        ticketItem.classList.add('status-resolved-ticket');
                    }
                } else {
                    ticketItem.classList.add('archived-item'); // Adiciona classe para estilo de arquivado
                }

                const techsList = ticket.techs && ticket.techs.length > 0 ? ticket.techs.map(tech => htmlspecialchars(tech)).join(', ') : 'N/A';

                let actionsHtml = '';
                if (!isArchivedView) { // Botões de ação só para tickets ativos
                    actionsHtml = `
                        <button class="resolve-btn" data-id="${ticket.id}"><i class="fa-solid fa-check-circle"></i> Resolver</button>
                        <button class="edit-btn" data-id="${ticket.id}"><i class="fa-solid fa-edit"></i> Editar</button>
                        <button class="delete-btn" data-id="${ticket.id}"><i class="fa-solid fa-trash"></i> Apagar</button>
                    `;
                }

                ticketItem.innerHTML = `
                    <h3>
                        Ticket ID: ${htmlspecialchars(ticket.id.substring(0, 8))} <span class="status ${statusClass}">${htmlspecialchars(ticket.status)}</span>
                    </h3>
                    <div class="info-grid-details">
                        <div><strong>Solicitante</strong> <span>${htmlspecialchars(ticket.usuario)}</span></div>
                        <div><strong>Setor</strong> <span>${htmlspecialchars(ticket.sector)}</span></div>
                        <div><strong>Tipo</strong> <span>${htmlspecialchars(ticket.type)}</span></div>
                        <div><strong>Prioridade</strong> <span>${htmlspecialchars(ticket.priority)}</span></div>
                    </div>
                    <div class="description-box">
                        <strong>Descrição</strong>
                        <p>${nl2br(htmlspecialchars(ticket.description))}</p>
                    </div>
                    <p><strong>Técnicos:</strong> ${techsList}</p>
                    <p><strong>Criado em:</strong> ${formatDate(ticket.createdAt)}</p>
                    <p><strong>Atualizado em:</strong> ${ticket.updatedAt ? formatDate(ticket.updatedAt) : 'N/A'}</p>
                    <div class="actions">
                        ${actionsHtml}
                    </div>
                `;
                container.appendChild(ticketItem);
            });

            if (!isArchivedView) {
                // Adiciona event listeners para os botões de resolver, editar e apagar
                container.querySelectorAll('.resolve-btn').forEach(button => {
                    button.addEventListener('click', async (event) => {
                        const ticketId = event.currentTarget.dataset.id;
                        if (confirm(`Tem certeza que deseja marcar o ticket ${ticketId} como RESOLVIDO?`)) {
                            try {
                                const response = await fetch('update_ticket.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ id: ticketId, status: 'Resolvido' })
                                });
                                const result = await response.json();
                                if (result.success) {
                                    showMessage('Ticket resolvido com sucesso!');
                                    fetchAndRenderTickets(currentFilter); // Atualiza a lista com o filtro atual
                                } else {
                                    showMessage(`Erro: ${result.message}`, 'error');
                                }
                            } catch (error) {
                                console.error('Erro ao resolver ticket:', error);
                                showMessage('Erro ao comunicar com o servidor.', 'error');
                            }
                        }
                    });
                });

                container.querySelectorAll('.edit-btn').forEach(button => {
                    button.addEventListener('click', async (event) => {
                        const ticketId = event.currentTarget.dataset.id;
                        await openEditModal(ticketId);
                    });
                });

                container.querySelectorAll('.delete-btn').forEach(button => {
                    button.addEventListener('click', async (event) => {
                        const ticketId = event.currentTarget.dataset.id;
                        if (confirm(`Tem certeza que deseja APAGAR o ticket ${ticketId}? Esta ação é irreversível.`)) {
                            try {
                                const response = await fetch('delete_ticket.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ id: ticketId })
                                });
                                const result = await response.json();
                                if (result.success) {
                                    showMessage('Ticket excluído com sucesso!');
                                    fetchAndRenderTickets(currentFilter); // Atualiza a lista com o filtro atual
                                } else {
                                    showMessage(`Erro ao excluir: ${result.message}`, 'error');
                                }
                            } catch (error) {
                                console.error('Erro ao excluir ticket:', error);
                                showMessage('Erro ao comunicar com o servidor.', 'error');
                            }
                        }
                    });
                });
            }
        }

        // Função para formatar a data para exibição
        function formatDate(dateString) {
            const options = { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' };
            return new Date(dateString).toLocaleString('pt-BR', options);
        }

        // Função para obter e exibir tickets (ativos)
        async function fetchAndRenderTickets(filter = 'Todos') {
            try {
                let url = 'get_tickets.php';
                const response = await fetch(url);
                let tickets = await response.json();

                // Filtragem no lado do cliente
                if (filter === 'Aberto') {
                    tickets = tickets.filter(ticket => ticket.status === 'Aberto');
                } else if (filter === 'Resolvido') {
                    tickets = tickets.filter(ticket => ticket.status === 'Resolvido');
                }
                renderTickets(tickets, ticketsContainer);
            } catch (error) {
                console.error('Erro ao buscar tickets:', error);
                showMessage('Erro ao carregar tickets.', 'error');
                ticketsContainer.innerHTML = '<p style="text-align: center; color: red;">Não foi possível carregar os tickets. Tente novamente.</p>';
            }
        }

        // Função para obter e exibir tickets arquivados com filtros
        async function filterArchivedTickets() {
            try {
                const selectedYear = archiveYearFilter.value;
                const selectedMonth = archiveMonthFilter.value;
                let url = 'get_archived_tickets.php';
                
                const response = await fetch(url);
                let tickets = await response.json();

                let filteredTickets = tickets;

                if (selectedYear) {
                    filteredTickets = filteredTickets.filter(ticket => {
                        const ticketYear = new Date(ticket.createdAt).getFullYear().toString();
                        return ticketYear === selectedYear;
                    });
                }

                if (selectedMonth) {
                    filteredTickets = filteredTickets.filter(ticket => {
                        const ticketMonth = (new Date(ticket.createdAt).getMonth() + 1).toString().padStart(2, '0');
                        return ticketMonth === selectedMonth;
                    });
                }
                renderTickets(filteredTickets, archivedTicketsContainer, true); // Passa true para isArchivedView
            } catch (error) {
                console.error('Erro ao buscar tickets arquivados:', error);
                showMessage('Erro ao carregar tickets arquivados.', 'error');
                archivedTicketsContainer.innerHTML = '<p style="text-align: center; color: red;">Não foi possível carregar os tickets arquivados. Tente novamente.</p>';
            }
        }

        // Função para popular os anos no filtro de arquivados
        async function populateArchiveYearFilter() {
            let years = new Set(); // Use um Set para armazenar anos únicos

            archiveYearFilter.innerHTML = '<option value="">Todos</option>'; // Adiciona sempre a opção 'Todos'

            // Adiciona anos de 2025 a 2040
            for (let year = 2040; year >= 2025; year--) {
                years.add(year);
            }
            
            const sortedYears = Array.from(years).sort((a, b) => b - a); // Ordena do mais recente para o mais antigo

            sortedYears.forEach(year => {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                archiveYearFilter.appendChild(option);
            });
        }

        // Função para abrir o modal de edição
        async function openEditModal(ticketId) {
            try {
                const response = await fetch('get_tickets.php'); // Poderia ser um endpoint para um único ticket
                const tickets = await response.json();
                const ticketToEdit = tickets.find(ticket => ticket.id === ticketId);

                if (ticketToEdit) {
                    editTicketIdDisplay.textContent = ticketId.substring(0, 8); // Exibe apenas parte do ID
                    editTicketIdInput.value = ticketId;
                    editStatusSelect.value = ticketToEdit.status;

                    // Popula checkboxes de técnicos usando a lista global ALL_TECHS
                    editTechsContainer.innerHTML = '';
                    ALL_TECHS.forEach(tech => {
                        const isChecked = ticketToEdit.techs && ticketToEdit.techs.includes(tech);
                        const checkboxId = `edit-tech-${tech.replace(/\s/g, '-')}`;
                        editTechsContainer.innerHTML += `
                            <input type="checkbox" id="${checkboxId}" name="editTechs[]" value="${htmlspecialchars(tech)}" ${isChecked ? 'checked' : ''}>
                            <label for="${checkboxId}" class="checkbox-label"><i class="fa-solid fa-user-tie"></i> ${htmlspecialchars(tech)}</label>
                        `;
                    });

                    editTicketModal.style.display = 'flex'; // Mostra o modal
                } else {
                    showMessage('Ticket não encontrado para edição.', 'error');
                }
            } catch (error) {
                console.error('Erro ao carregar dados do ticket para edição:', error);
                showMessage('Erro ao carregar dados do ticket.', 'error');
            }
        }

        // Event listener para salvar edições do ticket
        saveEditBtn.addEventListener('click', async () => {
            const ticketId = editTicketIdInput.value;
            const newStatus = editStatusSelect.value;
            const selectedTechs = Array.from(editTechsContainer.querySelectorAll('input[name="editTechs[]"]:checked')).map(cb => cb.value);

            try {
                const response = await fetch('update_ticket.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: ticketId, status: newStatus, techs: selectedTechs })
                });
                const result = await response.json();
                if (result.success) {
                    showMessage('Ticket atualizado com sucesso!');
                    editTicketModal.style.display = 'none'; // Esconde o modal
                    fetchAndRenderTickets(currentFilter); // Atualiza a lista de tickets
                } else {
                    showMessage(`Erro ao atualizar: ${result.message}`, 'error');
                }
            } catch (error) {
                console.error('Erro ao salvar edição do ticket:', error);
                showMessage('Erro ao comunicar com o servidor.', 'error');
            }
        });

        // Event listener para cancelar edição
        cancelEditBtn.addEventListener('click', () => {
            editTicketModal.style.display = 'none';
        });


        // Funções de manipulação de exibição das seções
        function showMainTickets() {
            activeTicketsSection.style.display = 'block';
            newTicketForm.style.display = 'none';
            archivedTicketsSection.style.display = 'none';
            showMainTicketsBtn.classList.add('active');
            showNewTicketFormBtn.classList.remove('active');
            showArchivedTicketsBtn.classList.remove('active');
            fetchAndRenderTickets(currentFilter); // Renderiza os tickets ativos com o filtro atual
        }

        function showNewTicketForm() {
            activeTicketsSection.style.display = 'none';
            newTicketForm.style.display = 'block';
            archivedTicketsSection.style.display = 'none';
            showMainTicketsBtn.classList.remove('active');
            showNewTicketFormBtn.classList.add('active');
            showArchivedTicketsBtn.classList.remove('active');
            hideFormErrors(); // Limpa erros anteriores ao abrir o formulário
        }

        function showArchivedTickets() {
            activeTicketsSection.style.display = 'none';
            newTicketForm.style.display = 'none';
            archivedTicketsSection.style.display = 'block';
            showMainTicketsBtn.classList.remove('active');
            showNewTicketFormBtn.classList.remove('active');
            showArchivedTicketsBtn.classList.add('active');
            filterArchivedTickets(); // Carrega e filtra os tickets arquivados
        }


        // Event Listeners para os botões principais de navegação
        showMainTicketsBtn.addEventListener('click', showMainTickets);
        showNewTicketFormBtn.addEventListener('click', showNewTicketForm);
        showArchivedTicketsBtn.addEventListener('click', showArchivedTickets);

        // Event Listener para o formulário de novo ticket
        newTicketForm.addEventListener('submit', async (event) => {
            event.preventDefault(); // Evita o recarregamento da página

            const formData = new FormData(newTicketForm);
            const data = Object.fromEntries(formData.entries());

            // Coleta os técnicos selecionados (checkboxes)
            data.techs = Array.from(newTicketForm.querySelectorAll('input[name="techs[]"]:checked')).map(cb => cb.value);

            // Validação simples
            const errors = [];
            if (!data.usuario) errors.push('O campo Solicitante é obrigatório.');
            if (!data.sector) errors.push('O campo Setor é obrigatório.');
            if (!data.type) errors.push('O campo Tipo de Serviço é obrigatório.');
            if (!data.priority) errors.push('O campo Prioridade é obrigatório.');
            if (!data.description) errors.push('O campo Descrição é obrigatório.');

            if (errors.length > 0) {
                showFormErrors(errors);
                return;
            } else {
                hideFormErrors();
            }

            try {
                const response = await fetch('processa_ticket.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if (result.success) {
                    showMessage('Ticket criado com sucesso!');
                    newTicketForm.reset(); // Limpa o formulário
                    // Desmarca todos os técnicos após o reset
                    newTicketForm.querySelectorAll('input[name="techs[]"]').forEach(checkbox => {
                        checkbox.checked = false;
                    });
                    showMainTickets(); // Volta para a lista principal de tickets
                } else {
                    showMessage('Erro ao criar ticket: ' + result.message, 'error');
                }
            } catch (error) {
                console.error('Erro na requisição:', error);
                showMessage('Ocorreu um erro ao tentar criar o ticket. Verifique o console para detalhes.', 'error');
            }
        });

        // Event Listeners para os botões de filtro de tickets ativos
        filterTodos.addEventListener('click', () => {
            currentFilter = 'Todos';
            filterTodos.classList.add('active');
            filterAberto.classList.remove('active');
            filterResolvido.classList.remove('active');
            fetchAndRenderTickets(currentFilter);
        });

        filterAberto.addEventListener('click', () => {
            currentFilter = 'Aberto';
            filterTodos.classList.remove('active');
            filterAberto.classList.add('active');
            filterResolvido.classList.remove('active');
            fetchAndRenderTickets(currentFilter);
        });

        filterResolvido.addEventListener('click', () => {
            currentFilter = 'Resolvido';
            filterTodos.classList.remove('active');
            filterAberto.classList.remove('active');
            filterResolvido.classList.add('active');
            fetchAndRenderTickets(currentFilter);
        });

        // Event Listeners para os filtros de tickets arquivados
        archiveYearFilter.addEventListener('change', filterArchivedTickets);
        archiveMonthFilter.addEventListener('change', filterArchivedTickets);


        // Função de escape para HTML
        function htmlspecialchars(str) {
            if (typeof str != 'string') return str;
            return str.replace(/&/g, "&amp;")
                      .replace(/</g, "&lt;")
                      .replace(/>/g, "&gt;")
                      .replace(/"/g, "&quot;")
                      .replace(/'/g, "&#039;");
        }

        // Função para nl2br (newline to <br>)
        function nl2br(str) {
            return str.replace(/(?:\r\n|\r|\n)/g, '<br>');
        }

        // Event listener para o botão "Arquivar Resolvidos"
        if (archiveAllResolvedBtn) {
                archiveAllResolvedBtn.addEventListener('click', async () => {
                    if (!confirm('Tem certeza que deseja ARQUIVAR TODOS os tickets com status "Resolvido"? Esta ação pode ser irreversível dependendo da sua configuração.')) {
                        return; // Cancela se o usuário não confirmar
                    }

                    try {
                        const response = await fetch('archive_tickets.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ archive_all: true }) // Enviamos uma flag para o backend
                        });
                        const result = await response.json();

                        if (result.success) {
                            showMessage(result.message);
                            fetchAndRenderTickets(currentFilter); // Atualiza a lista de tickets ativos
                            if (document.getElementById('archivedTicketsSection').style.display === 'block') {
                                filterArchivedTickets(); // Se estiver na aba de arquivados, atualiza também
                            }
                            populateArchiveYearFilter(); // Atualiza a lista de anos caso novos tickets tenham sido arquivados
                        } else {
                            showMessage('Erro ao arquivar tickets: ' + result.message, 'error');
                        }
                    } catch (error) {
                        console.error('Erro na requisição de arquivamento:', error);
                        showMessage('Ocorreu um erro ao tentar arquivar os tickets. Verifique o console para detalhes.', 'error');
                    }
                });
            }

        // Inicialização
        document.addEventListener('DOMContentLoaded', () => {
            showMainTickets(); // Mostra os tickets ativos ao carregar a página por padrão
            populateArchiveYearFilter(); // Popula os anos para o filtro de arquivados
        });
    </script>
</body>
</html>