<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receber Pacote Labmaster</title>
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container container-customlistas">
        <h1 class="text-center mb-4" style="color: #28a745;">Receber Pacote Labmaster</h1>
        <form id="pacoteForm">
            <div class="form-group">
                <label for="laboratorio" style="color: #28a745;">Selecione o Laboratório:</label>
                <select id="laboratorio" class="form-control" required>
                    <option value="21">GERAC</option>
                    <option value="22">GEBIM</option>
                    <option value="23">GERIM</option>
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
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        let pacotes = [];

        document.getElementById('adicionarPacote').addEventListener('click', function() {
            const codigobarras = document.getElementById('codigobarras').value;
            const laboratorio = document.getElementById('laboratorio').value;
            const laboratorioNome = document.getElementById('laboratorio').options[document.getElementById('laboratorio').selectedIndex].text;
            if (codigobarras && laboratorio) {
                // Verificar duplicidade na lista dinâmica
                if (verificarDuplicidade(codigobarras)) {
                    alert('Este código de barras já foi adicionado.');
                    return;
                }

                // Verificar se o pacote está com status "enviado"
                verificarStatusEnviado(codigobarras, laboratorio)
                    .then(statusEnviado => {
                        if (statusEnviado) {
                            pacotes.unshift({ codigobarras, laboratorio, laboratorioNome });
                            atualizarListaPacotes();
                            document.getElementById('codigobarras').value = '';
                            document.getElementById('codigobarras').focus();
                        } else {
                            alert('Este pacote não está com status "enviado".');
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao verificar status:', error);
                        alert('Erro ao verificar status do pacote.');
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

            fetch('processarRlabmaster.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'pacotes=' + encodeURIComponent(JSON.stringify(pacotes))
            })
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 0) {
                    data.forEach(result => {
                        if (result.status === 'success') {
                            alert(result.message);
                        } else {
                            alert(result.message);
                        }
                    });
                    pacotes = [];
                    atualizarListaPacotes();
                } else {
                    alert('Erro ao processar os pacotes.');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao conectar com o servidor.');
            });
        });

        function verificarDuplicidade(codigo) {
            return pacotes.some(pacote => pacote.codigobarras === codigo);
        }

        function verificarStatusEnviado(codigo, lab) {
            return fetch('verificarStatus.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'codigobarras=' + encodeURIComponent(codigo) + '&laboratorio=' + encodeURIComponent(lab)
            })
            .then(response => response.json())
            .then(data => {
                return data.status === 'enviado';
            })
            .catch(error => {
                console.error('Erro ao verificar status:', error);
                throw error;
            });
        }

        function atualizarListaPacotes() {
            const lista = document.getElementById('pacotesList');
            lista.innerHTML = '';

            pacotes.forEach((pacote, index) => {
                const item = document.createElement('div');
                item.className = 'alert alert-secondary d-flex justify-content-between align-items-center';
                item.innerHTML = `
                    <span>Laboratório: ${pacote.laboratorioNome}, Código de Barras: ${pacote.codigobarras}</span>
                    <button class="btn btn-danger btn-sm" onclick="removerPacote(${index})">Excluir</button>
                `;
                lista.appendChild(item);
            });
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
                time = setTimeout(logout, 900000);  // Tempo em milissegundos (15 minutos)
            }
        };

        inactivityTime();
    </script>
</body>
</html>
