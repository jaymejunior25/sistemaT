<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de s</title>
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <script>
        function adicionarLaboratorio() {
            const tabelaLaboratorios = document.getElementById('tabelaLaboratorios');
            const laboratorio = document.getElementById('laboratorio').value;
            const numeroAmostras = document.getElementById('numero_amostras').value;
            
            const row = tabelaLaboratorios.insertRow();
            const cell1 = row.insertCell(0);
            const cell2 = row.insertCell(1);
            cell1.innerHTML = laboratorio;
            cell2.innerHTML = numeroAmostras;

            const hiddenLaboratorio = document.createElement('input');
            hiddenLaboratorio.type = 'hidden';
            hiddenLaboratorio.name = 'laboratorios[]';
            hiddenLaboratorio.value = laboratorio;
            document.getElementById('Form').appendChild(hiddenLaboratorio);

            const hiddenAmostras = document.createElement('input');
            hiddenAmostras.type = 'hidden';
            hiddenAmostras.name = 'amostras[]';
            hiddenAmostras.value = numeroAmostras;
            document.getElementById('Form').appendChild(hiddenAmostras);

            document.getElementById('laboratorio').value = '';
            document.getElementById('numero_amostras').value = '';
        }

        function conferirDados() {
            const doador = document.getElementById('amostras_doador').checked ? 'Sim' : 'Não';
            const paciente = document.getElementById('amostras_paciente').checked ? 'Sim' : 'Não';
            const transplante = document.getElementById('amostras_transplante').checked ? 'Sim' : 'Não';
            const outros = document.getElementById('amostras_outros').checked ? 'Sim' : 'Não';
            const observacoes = document.getElementById('observacoes').value;

            let laboratorios = '<ul>';
            document.querySelectorAll('#tabelaLaboratorios tbody tr').forEach(function (row) {
                laboratorios += `<li>${row.cells[0].innerText} - ${row.cells[1].innerText} amostras</li>`;
            });
            laboratorios += '</ul>';

            const conferidos = `
                <p>Amostras de Doadores: ${doador}</p>
                <p>Amostras de Pacientes: ${paciente}</p>
                <p>Amostras de Transplantes: ${transplante}</p>
                <p>Amostras de Outros: ${outros}</p>
                <p>Observações: ${observacoes}</p>
                <h4>Laboratórios</h4>
                ${laboratorios}
            `;
            document.getElementById('conferencia').innerHTML = conferidos;
            document.getElementById('confirmarDiv').style.display = 'block';
        }
    </script>
</head>
<body>
    <h1>Cadastro de s</h1>
    <form id="Form" method="POST" action="teste 28.php">
        <label for="amostras_doador">Amostras de Doadores</label>
        <input type="checkbox" name="amostras_doador" id="amostras_doador" value="1"><br>

        <label for="amostras_paciente">Amostras de Pacientes</label>
        <input type="checkbox" name="amostras_paciente" id="amostras_paciente" value="1"><br>

        <label for="amostras_transplante">Amostras de Transplantes</label>
        <input type="checkbox" name="amostras_transplante" id="amostras_transplante" value="1"><br>

        <label for="amostras_outros">Amostras de Outros</label>
        <input type="checkbox" name="amostras_outros" id="amostras_outros" value="1"><br>

        <label for="laboratorio">Laboratório</label>
        <select name="laboratorio" id="laboratorio">
            <!-- Options populated dynamically from the database -->
            <option value="">Selecione</option>
            <option value="8">GECOQ</option>
            <option value="7">GEMER</option>
            <option value="5">GETDT</option>
            <option value="9">REDOME</option>
            <option value="0">NAT</option>
            <option value="6">GEHEM</option>
            <!-- Adicione os demais laboratórios aqui -->
        </select>
        <label for="numero_amostras">Número de Amostras</label>
        <input type="number" name="numero_amostras" id="numero_amostras"><br>
        <button type="button" onclick="adicionarLaboratorio()">Adicionar Laboratório</button>

        <table id="tabelaLaboratorios" border="1">
            <thead>
                <tr>
                    <th>Laboratório</th>
                    <th>Número de Amostras</th>
                </tr>
            </thead>
            <tbody>
                <!-- Laboratórios adicionados serão exibidos aqui -->
            </tbody>
        </table>

        <label for="observacoes">Observações</label>
        <textarea name="observacoes" id="observacoes"></textarea><br>

        <button type="button" onclick="conferirDados()">Conferir Dados</button>
        <div id="conferencia"></div>

        <div id="confirmarDiv" style="display:none;">
            <label for="senha">Digite sua senha para confirmar:</label>
            <input type="password" name="senha" id="senha" required><br>
            <button type="submit">Confirmar e Cadastrar </button>
        </div>
    </form>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Função para monitorar inatividade
        let inactivityTime = function () {
            let time;
            window.onload = resetTimer;
            document.onmousemove = resetTimer;
            document.onkeypress = resetTimer;
            document.onscroll = resetTimer;
            document.onclick = resetTimer;

            function logout() {
                alert("Você foi desconectado devido à inatividade.");
                window.location.href = 'logout.php';
            }

            function resetTimer() {
                clearTimeout(time);
                time = setTimeout(logout, 900000);  // Tempo em milissegundos 900000 = (15 minutos)
            }
        };

        inactivityTime();
    </script>
</body>
</html>
