<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receber Pacote</title>
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container container-customlistas">
        <h1 class="text-center mb-4" style="color: #28a745;">Receber Pacote</h1>
        <form id="pacoteForm">
            <div class="form-group">
                <label for="laboratorio" style="color: #28a745;">Selecione o Laboratório:</label>
                <select id="laboratorio" class="form-control" required>
                    <option value="">Selecione</option>
                    <option value="GECOQ">GECOQ</option>
                    <option value="GEMER">GEMER</option>
                    <option value="GETDT">GETDT</option>
                    <option value="REDOME">REDOME</option>
                    <option value="NAT">NAT</option>
                    <option value="GEHEM">GEHEM</option>
                    <!-- Adicione os demais laboratórios aqui -->
                </select>
            </div>
            <div class="form-group">
                <label for="codigobarras" style="color: #28a745;">Código de Barras:</label>
                <input type="text" name="codigobarras" id="codigobarras" class="form-control" required>
            </div>
            <button type="button" id="adicionarPacote" class="btn btn-primary btn-block mt-3"><i class="fas fa-plus"></i> Adicionar Pacote</button>
        </form>
        <div id="pacotesList" class="mt-3"></div>
        <button type="button" id="receberTodos" class="btn btn-success btn-block mt-3"><i class="fas fa-check"></i> Receber Todos</button>
        <div class="text-center mt-3">
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-angle-left"></i> Voltar</a>
        </div>
        <a href="logout.php" class="btn btn-danger btn-lg mt-3">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
    <div class="fixed-bottom toggle-footer cursor_to_down" id="footer_fixed">
        <div class="fixed-bottom border-top bg-light text-center footer-content p-2" style="z-index:4;">
            <div class="footer-text">
                Desenvolvido com &#128151; por Gerencia de Informatica - GETIN <br>
                <a class="text-reset fw-bold" href="http://www.hemopa.pa.gov.br/site/">© Todos os direitos reservados 2024 Hemopa.</a>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        let pacotes = [];

        function filtrarCodigoBarras(codigoBarras) {
            let digitoverificarp = codigoBarras.charAt(0);
            let digitoverificaru = codigoBarras.charAt(codigoBarras.length - 1);

            if (digitoverificarp === '=' && !isNaN(digitoverificaru)) {
                return codigoBarras.slice(1);
            } else if ((digitoverificarp === 'B' || digitoverificarp === 'b') && !isNaN(digitoverificaru)) {
                return codigoBarras.slice(0, -2) + '0' + codigoBarras.slice(-1);
            } else if ((digitoverificarp === 'A' || digitoverificarp === 'a') && (digitoverificaru === 'B' || digitoverificaru === 'b')) {
                return codigoBarras.slice(1, -1);
            } else if ((digitoverificarp === 'A' || digitoverificarp === 'a') && (digitoverificaru === 'A' || digitoverificaru === 'a')){
                return codigoBarras.slice(1, -1);
            } else {
                return codigoBarras;
            }
        }

        function codigoBarrasDuplicado(codigobarrasFiltrado) {
            return pacotes.some(pacote => pacote.codigobarrasFiltrado === codigobarrasFiltrado);
        }

        document.getElementById('adicionarPacote').addEventListener('click', function() {
            const codigobarras = document.getElementById('codigobarras').value;
            const laboratorio = document.getElementById('laboratorio').value;

            if (codigobarras && laboratorio) {
                // Verificação de duplicidade na lista dinâmica
                const codigobarrasFiltrado = filtrarCodigoBarras(codigobarras);
                let duplicado = pacotes.some(pacote => pacote.codigobarras === codigobarrasFiltrado);

                if (duplicado) {
                    alert('Pacote com código de barras ' + codigobarrasFiltrado + ' já existe na lista.');
                    return;
                }
                // Verificar duplicidade de código de barras
                if (codigoBarrasDuplicado(codigobarrasFiltrado)) {
                    alert('Código de barras duplicado.');
                    document.getElementById('codigobarras').value = '';
                    document.getElementById('codigobarras').focus();
                    return;
                }
                
                // Verificar status "enviado"
                fetch('verificar_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'codigobarras=' + encodeURIComponent(codigobarrasFiltrado)+'&laboratorio=' + encodeURIComponent(laboratorio)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'enviado') {
                        pacotes.push({ laboratorio, codigobarras: codigobarrasFiltrado });
                        atualizarListaPacotes();
                        document.getElementById('codigobarras').value = '';
                        document.getElementById('codigobarras').focus();
                    } else {
                        alert('A amostra referente ao código de barras ' + codigobarrasFiltrado + ' não está com status "enviado" ou o Laboratorio selecionado não é compativel com o do Codigo de Barras.');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao verificar status.');
                });
            } else {
                alert('Por favor, preencha todos os campos.');
            }
        });

        document.getElementById('receberTodos').addEventListener('click', function() {
            if (pacotes.length === 0) {
                alert('Nenhum pacote para receber.');
                return;
            }

            fetch('processar_pacotesR.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'pacotes=' + encodeURIComponent(JSON.stringify(pacotes))
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    pacotes = [];
                    atualizarListaPacotes();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao processar a solicitação.');
            });
        });

        function atualizarListaPacotes() {
            const lista = document.getElementById('pacotesList');
            lista.innerHTML = '';

            // Percorre a lista de pacotes de forma invertida para adicionar no topo
            for (let i = pacotes.length - 1; i >= 0; i--) {
                const pacote = pacotes[i];

                const item = document.createElement('div');
                item.className = 'alert alert-secondary d-flex justify-content-between align-items-center';
                item.innerHTML = `
                    <span>Laboratório: ${pacote.laboratorio}, Código de Barras: ${pacote.codigobarras}</span>
                    <button class="btn btn-danger btn-sm" onclick="removerPacote(${i})">Excluir</button>
                `;
                lista.appendChild(item);
            }
        }

        function removerPacote(index) {
            pacotes.splice(index, 1);
            atualizarListaPacotes();
        }

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
