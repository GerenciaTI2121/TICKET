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
      --pending: #f39c12; /* Cor para tickets pendentes/em andamento */
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
      background: var(--bg-light);
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
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

  <div class="ticket-list">
    <h2>Tickets Existentes</h2>
    <div id="ticketsContainer">
      <p style="text-align: center;">Carregando tickets...</p>
    </div>
  </div>

  <p class="logout"><a href="logout.php">Sair</a></p>

  <script>
    // Lista de técnicos atualizada
    const allTechs = ["Alexandre", "Bruno", "Jhonatan", "Matheus", "Marcio", "Willy"];
    let currentSelectedTechs = [];

    // Renderiza os checkboxes dos técnicos
    function renderTechCheckboxes() {
      const container = document.getElementById('techsContainer');
      container.innerHTML = '';
      allTechs.forEach(tech => {
        const checkboxId = `tech_${tech.toLowerCase()}`;
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.id = checkboxId;
        checkbox.value = tech;
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

    async function renderTickets() {
      const ticketsContainer = document.getElementById('ticketsContainer');
      ticketsContainer.innerHTML = '<p style="text-align: center;">Carregando tickets...</p>';
      const res = await fetch('get_tickets.php');
      const tickets = await res.json();

      ticketsContainer.innerHTML = ''; // Limpa o conteúdo antes de renderizar

      if (tickets.length === 0) {
        ticketsContainer.innerHTML = '<p style="text-align: center;">Nenhum ticket encontrado.</p>';
        return;
      }

      tickets.forEach(ticket => {
        const div = document.createElement('div');
        div.classList.add('ticket-item');

        const createdAt = new Date(ticket.createdAt).toLocaleString('pt-BR');
        const techsText = ticket.techs.length > 0 ? ticket.techs.join(', ') : 'Nenhum';

        let statusClass = '';
        if (ticket.status === 'Aberto') {
            statusClass = 'Aberto';
        } else if (ticket.status === 'Resolvido') {
            statusClass = 'Resolvido';
        }

        // Usando textContent para dados que não contêm HTML do usuário para prevenir XSS
        // innerHTML usado para descrição com sanitização no backend
        div.innerHTML = `
          <h3>Ticket ID: <span style="color: #666;">${ticket.id.substring(0, 8)}...</span></h3>
          <div class="info-row">
            <span><strong>Usuário:</strong> ${encodeURIComponent(ticket.usuario)}</span>
            <span><strong>Setor:</strong> ${encodeURIComponent(ticket.sector)}</span>
            <span><strong>Tipo:</strong> ${encodeURIComponent(ticket.type)}</span>
            <span><strong>Prioridade:</strong> ${encodeURIComponent(ticket.priority)}</span>
          </div>
          <p><strong>Descrição:</strong> ${ticket.description}</p>
          <p><strong>Atribuído a:</strong> ${encodeURIComponent(techsText)}</p>
          <p><strong>Criado em:</strong> ${createdAt}</p>
          <p><strong>Status:</strong> <span class="status ${statusClass}">${encodeURIComponent(ticket.status)}</span></p>
          <div class="actions">
            ${ticket.status === 'Aberto' ? `<button class="resolve-btn" onclick="updateTicketStatus('${ticket.id}', 'Resolvido')"><i class="fa-solid fa-check"></i> Resolver</button>` : ''}
            <button class="delete-btn" onclick="deleteTicket('${ticket.id}')"><i class="fa-solid fa-trash"></i> Apagar</button>
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
  </script>
</body>
</html>