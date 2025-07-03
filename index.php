<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
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
    :root {
      --main-color: #2ecc71;
      --text-color: #333;
      --bg-light: #f9f9f9;
      --white: #fff;
      --danger: #e74c3c;
      --resolved: #27ae60;
      --pending: #f39c12; /* Cor para tickets abertos/pendentes */
    }
    body {
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      padding: 20px;
      background: var(--bg-light);
      color: var(--text-color);
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
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 1em;
      box-sizing: border-box;
    }
    textarea {
      resize: vertical;
      min-height: 80px;
    }
    button {
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
    }
    button:hover {
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
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .ticket-item {
      border: 1px solid #eee;
      padding: 15px;
      margin-bottom: 15px;
      border-radius: 8px;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    /* NOVOS ESTILOS PARA AS CORES DE FUNDO DOS TICKETS */
    .ticket-item.status-Aberto-bg {
      background-color: #ffeccf; /* Laranja/amarelo claro para aberto */
    }
    .ticket-item.status-Resolvido-bg {
      background-color: #e6ffe6; /* Verde claro para resolvido */
    }
    /* FIM DOS NOVOS ESTILOS */
    .ticket-item h3 {
      margin: 0 0 5px 0;
      color: var(--main-color);
    }
    .ticket-item p {
      margin: 0;
      line-height: 1.5;
    }
    .ticket-item .status {
      font-weight: bold;
      padding: 5px 10px;
      border-radius: 5px;
      display: inline-block;
      font-size: 0.9em;
    }
    .ticket-item .status.Aberto {
      background: var(--pending);
      color: var(--white);
    }
    .ticket-item .status.Resolvido {
      background: var(--resolved);
      color: var(--white);
    }
    .ticket-item .actions {
      display: flex;
      gap: 10px;
      margin-top: 10px;
      justify-content: flex-end;
    }
    .ticket-item .actions button {
      width: auto;
      padding: 8px 15px;
      font-size: 0.9em;
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
    .ticket-item .info-row {
      display: flex;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 10px;
    }
    .ticket-item .info-row span {
      flex: 1;
      min-width: 150px;
    }
    .ticket-item .info-row span strong {
      color: #555;
    }
    .logout {
      display: block;
      text-align: center;
      margin-top: 20px;
    }
    .logout a {
      color: var(--danger);
      text-decoration: none;
      font-weight: bold;
      padding: 8px 15px;
      border: 1px solid var(--danger);
      border-radius: 5px;
      transition: all 0.3s ease;
    }
    .logout a:hover {
      background: var(--danger);
      color: var(--white);
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
  </style>
</head>
<body>
  <h1><i class="fa-solid fa-ticket"></i> Gestão de Tickets de Serviço</h1>

  <form id="ticketForm">
    <h2>Abrir Novo Ticket</h2>
    <div class="error-messages" id="formErrors"></div>

    <div class="tech-selection">
      <label>Atribuir a Funcionário(s) da TI:</label>
      <div id="techsContainer">
        </div>
    </div>

    <label for="sector">Setor:</label>
    <input type="text" id="sector" name="sector" required />

    <label for="type">Tipo de Serviço:</label>
    <select id="type" name="type" required>
      <option value="">Selecione</option>
      <option value="Hardware">Hardware</option>
      <option value="Software">Software</option>
      <option value="Rede">Rede</option>
      <option value="Suporte Geral">Suporte Geral</option>
    </select>

    <label for="priority">Prioridade:</label>
    <select id="priority" name="priority" required>
      <option value="">Selecione</option>
      <option value="Baixa">Baixa</option>
      <option value="Média">Média</option>
      <option value="Alta">Alta</option>
      <option value="Urgente">Urgente</option>
    </select>

    <label for="description">Descrição do Problema:</label>
    <textarea id="description" name="description" required></textarea>

    <button type="submit">Abrir Ticket</button>
  </form>

  <div style="text-align: center; margin-top: 20px; margin-bottom: 20px;">
    <button onclick="generateReport()" style="background-color: #3498db; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; display: inline-block; width: auto;">
      <i class="fa-solid fa-file-alt"></i> Gerar Relatório Geral
    </button>

    <a href="generate_pdf.php" target="_blank" style="background-color: #e74c3c; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; display: inline-block; width: auto; text-decoration: none; margin-left: 10px;">
      <i class="fa-solid fa-file-pdf"></i> Baixar Relatório PDF
    </a>
  </div>
  <div class="ticket-list">
    <div style="text-align: center; margin-bottom: 20px;">
        <button id="filterTodos" onclick="applyFilter('Todos')" style="background-color: #6c757d; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; display: inline-block; width: auto;">
            Todos
        </button>
        <button id="filterAbertos" onclick="applyFilter('Aberto')" style="background-color: #f39c12; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; display: inline-block; width: auto; margin-left: 10px;">
            Abertos
        </button>
        <button id="filterResolvidos" onclick="applyFilter('Resolvido')" style="background-color: #27ae60; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; display: inline-block; width: auto; margin-left: 10px;">
            Resolvidos
        </button>
    </div>
    <h2>Tickets Existentes</h2>
    <div id="ticketsContainer">
      <p style="text-align: center;">Carregando tickets...</p>
    </div>
  </div>

  <div class="ticket-list" id="reportContainer" style="display: none;">
    <h2>Relatório Geral de Tickets</h2>
    <div id="reportContent">
      <p style="text-align: center;">Clique no botão acima para gerar o relatório.</p>
    </div>
    <div style="text-align: center; margin-top: 20px;">
        <button onclick="hideReport()" style="background-color: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; display: inline-block; width: auto;">
            <i class="fa-solid fa-times"></i> Fechar Relatório
        </button>
    </div>
  </div>

  <p class="logout"><a href="logout.php">Sair</a></p>

  <script>
    // Lista de técnicos atualizada
    const allTechs = ["Alexandre", "Bruno", "Jhonatan", "Matheus", "Marcio", "Willy"];
    let currentSelectedTechs = [];
    let currentFilter = 'Todos'; // Estado inicial: mostrar todos os tickets

    // Renderiza os checkboxes dos técnicos
    function renderTechCheckboxes() {
      const container = document.getElementById('techsContainer');
      container.innerHTML = '';
      allTechs.forEach(tech => {
        const checkboxId = `tech_${tech.toLowerCase().replace(/\s/g, '')}`; // Remove espaços para ID válido
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.id = checkboxId;
        checkbox.value = tech;
        // Marca o checkbox se o técnico já estiver selecionado em currentSelectedTechs
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

    renderTechCheckboxes(); // Chamada inicial para renderizar os técnicos

    // Função para aplicar o filtro
    function applyFilter(filterType) {
        currentFilter = filterType;
        renderTickets(); // Recarrega os tickets com o novo filtro
        updateFilterButtonStyles(); // Atualiza o estilo dos botões de filtro
    }

    // Função para atualizar o estilo dos botões de filtro
    function updateFilterButtonStyles() {
        const buttons = document.querySelectorAll('#filterTodos, #filterAbertos, #filterResolvidos');
        buttons.forEach(button => {
            button.style.fontWeight = 'normal';
            button.style.border = 'none';
            button.style.boxShadow = 'none';
            // Você pode adicionar ou remover classes aqui se preferir
        });

        const activeButton = document.getElementById(`filter${currentFilter}`);
        if (activeButton) {
            activeButton.style.fontWeight = 'bold';
            activeButton.style.border = '2px solid white';
            activeButton.style.boxShadow = '0 0 5px rgba(0,0,0,0.3)';
        }
    }


    async function renderTickets() {
      const ticketsContainer = document.getElementById('ticketsContainer');
      ticketsContainer.innerHTML = '<p style="text-align: center;">Carregando tickets...</p>';
      const res = await fetch('get_tickets.php');
      const tickets = await res.json();

      let filteredTickets = tickets; // Começa com todos os tickets
      if (currentFilter === 'Aberto') {
        filteredTickets = tickets.filter(ticket => ticket.status === 'Aberto');
      } else if (currentFilter === 'Resolvido') {
        filteredTickets = tickets.filter(ticket => ticket.status === 'Resolvido');
      }

      ticketsContainer.innerHTML = ''; // Limpa o conteúdo antes de renderizar

      if (filteredTickets.length === 0) {
        ticketsContainer.innerHTML = '<p style="text-align: center;">Nenhum ticket encontrado com este filtro.</p>';
        return;
      }

      filteredTickets.forEach(ticket => {
        const div = document.createElement('div');
        div.classList.add('ticket-item');

        // Adiciona a classe de fundo baseada no status
        let itemBgClass = '';
        if (ticket.status === 'Aberto') {
            itemBgClass = 'status-Aberto-bg';
        } else if (ticket.status === 'Resolvido') {
            itemBgClass = 'status-Resolvido-bg';
        }
        div.classList.add(itemBgClass); // Adiciona a classe de cor de fundo aqui

        const createdAt = new Date(ticket.createdAt).toLocaleString('pt-BR');
        const techsText = ticket.techs && ticket.techs.length > 0 ? ticket.techs.join(', ') : 'Nenhum';

        let statusClass = '';
        if (ticket.status === 'Aberto') {
            statusClass = 'Aberto';
        } else if (ticket.status === 'Resolvido') {
            statusClass = 'Resolvido';
        }

        div.innerHTML = `
          <h3>Ticket ID: <span style="color: #666;">${ticket.id.substring(0, 8)}...</span></h3>
          <div class="info-row">
            <span><strong>Usuário:</strong> ${ticket.usuario}</span>
            <span><strong>Setor:</strong> ${ticket.sector}</span>
            <span><strong>Tipo:</strong> ${ticket.type}</span>
            <span><strong>Prioridade:</strong> ${ticket.priority}</span>
          </div>
          <p><strong>Descrição:</strong> ${ticket.description}</p>
          <p><strong>Atribuído a:</strong> ${techsText}</p>
          <p><strong>Criado em:</strong> ${createdAt}</p>
          <p><strong>Status:</strong> <span class="status ${statusClass}">${ticket.status}</span></p>
          <div class="actions">
            ${ticket.status === 'Aberto' ? `<button class="resolve-btn" onclick="updateTicketStatus('${encodeURIComponent(ticket.id)}', 'Resolvido')"><i class="fa-solid fa-check"></i> Resolver</button>` : ''}
            <button class="delete-btn" onclick="deleteTicket('${encodeURIComponent(ticket.id)}')"><i class="fa-solid fa-trash"></i> Apagar</button>
          </div>
        `;
        ticketsContainer.appendChild(div);
      });
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

    // Função para Gerar Relatório
    async function generateReport() {
      const reportContainer = document.getElementById('reportContainer');
      const reportContent = document.getElementById('reportContent');

      // Esconde a lista de tickets e mostra o container do relatório
      document.querySelector('.ticket-list').style.display = 'none'; // Esconde a lista de tickets
      reportContainer.style.display = 'block'; // Mostra o container do relatório

      reportContent.innerHTML = '<p style="text-align: center;">Gerando relatório...</p>';

      const res = await fetch('get_report.php');
      const tickets = await res.json();

      // Verifica se houve erro na requisição ou se o usuário não tem permissão
      if (tickets.success === false && tickets.message) {
          reportContent.innerHTML = `<p style="text-align: center; color: red;">${tickets.message}</p>`;
          return;
      }

      if (tickets.length === 0) {
        reportContent.innerHTML = '<p style="text-align: center;">Nenhum ticket encontrado para o relatório.</p>';
        return;
      }

      // Constrói uma tabela HTML simples para o relatório
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
            <td>${createdAt}</td>
          </tr>
        `;
      });
      tableHtml += `
          </tbody>
        </table>
      `;
      reportContent.innerHTML = tableHtml;
    }

    // Função para esconder o relatório e mostrar a lista de tickets novamente
    function hideReport() {
        document.getElementById('reportContainer').style.display = 'none'; // Esconde o relatório
        document.querySelector('.ticket-list').style.display = 'block'; // Mostra a lista de tickets
        renderTickets(); // Recarrega a lista de tickets para garantir que esteja atualizada
        updateFilterButtonStyles(); // Atualiza o estilo dos botões de filtro
    }


    document.getElementById('ticketForm').onsubmit = async e => {
      e.preventDefault();
      const formErrorsDiv = document.getElementById('formErrors');
      formErrorsDiv.innerHTML = ''; // Limpa erros anteriores

      // Validação básica no cliente (reforçada no servidor)
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

      const data = await res.json(); // Espera uma resposta JSON

      if (data.success) {
        renderTickets();
        document.getElementById('ticketForm').reset(); // Limpa o formulário
        currentSelectedTechs = []; // Reseta os técnicos selecionados
        renderTechCheckboxes(); // Re-renderiza para limpar os checks
      } else {
        // Exibe erros retornados pelo servidor
        if (data.errors && data.errors.length > 0) {
          formErrorsDiv.innerHTML = '<ul>' + data.errors.map(err => `<li>${err}</li>`).join('') + '</ul>';
        } else {
          formErrorsDiv.innerHTML = '<ul><li>Erro ao abrir ticket. Tente novamente.</li></ul>';
        }
      }
    };

    renderTickets(); // Carrega os tickets quando a página é carregada
    updateFilterButtonStyles(); // Define o estilo inicial do botão "Todos"
  </script>
</body>
</html>