<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Pacote</title>
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container container-customlistas">
        <h1 class="text-center mb-4" style="color: #28a745;">Cadastrar Amostra</h1>
        <?php if (isset($mensagem)): ?>
            <div class="alert alert-success"><?php echo $mensagem; ?></div>
        <?php endif; ?>
        <form id="pacoteForm">
            <div class="form-group">
                <label for="descricao" style="color: #28a745;">Descrição:</label>
                <input type="text" name="descricao" id="descricao" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="codigobarras" style="color: #28a745;">Código de Barras:</label>
                <input type="text" name="codigobarras" id="codigobarras" class="form-control" required>
            </div>
        </form>
        <div id="pacotesList" class="mt-3"></div>
        <button type="button" id="cadastrarTodos" class="btn btn-success btn-block mt-3"><i class="fas fa-check"></i>Cadastrar Todos</button>
        <div class="text-center mt-3">
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-angle-left"></i> Voltar</a>
        </div>
        <a href="logout.php" class="btn btn-danger btn-lg mt-3">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
    <div class="fixed-bottom toggle-footer cursor_to_down" id="footer_fixed" >
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

        function filtrarCodigoBarras(codigoBarras) {
            let digitoverificarp = codigoBarras.charAt(0);
            let digitoverificaru = codigoBarras.charAt(codigoBarras.length - 1);

            if (digitoverificarp === '=' && !isNaN(digitoverificaru)) {
                return codigoBarras.slice(1);
            } else if ((digitoverificarp === 'B' || digitoverificarp === 'b') && !isNaN(digitoverificaru)) {
                return codigoBarras.slice(0, -2) + '0' + codigoBarras.slice(-1);
            } else {
                return codigoBarras.slice(1, -1);
            }
        }
        // Função para obter o nome do laboratório via AJAX
        function obterNomeLaboratorio(lab_id) {
            return fetch('get_nomeLab.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'lab_id=' + encodeURIComponent(lab_id)
            })
            .then(response => response.json())
            .then(data => data.nomeLaboratorio)
            .catch(error => console.error('Erro ao obter o nome do laboratório:', error));
        }
        
        document.getElementById('codigobarras').addEventListener('focusout', function() {
            const descricao = document.getElementById('descricao').value;
            const codigobarras = document.getElementById('codigobarras').value;

            if (descricao && codigobarras) {
                const codigobarrasFiltrado = filtrarCodigoBarras(codigobarras);
                pacotes.unshift({ descricao, codigobarras, codigobarrasFiltrado });
                atualizarListaPacotes();
                document.getElementById('codigobarras').value = '';
                document.getElementById('codigobarras').focus();
            } else {
                //alert('Por favor, preencha todos os campos.');
            }
        });

        document.getElementById('cadastrarTodos').addEventListener('click', function() {
            if (pacotes.length === 0) {
                alert('Nenhum pacote para cadastrar.');
                return;
            }

            fetch('processar_pacotesC.php', {
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
                    const labs = data.labs;

                    // Atualizar os pacotes com os nomes dos laboratórios
                    Promise.all(labs.map(lab_id => obterNomeLaboratorio(lab_id)))
                        .then(nomesLaboratorios => {
                            pacotes.forEach((pacote, index) => {
                                pacote.laboratorio_nome = nomesLaboratorios[index];
                            });
                            atualizarListaPacotes();
                        });
                    pacotes = [];
                } else {
                    alert('Erro ao cadastrar pacotes.');
                }
            });
        });

        function atualizarListaPacotes() {
            const lista = document.getElementById('pacotesList');
            lista.innerHTML = '';

            pacotes.forEach((pacote, index) => {
                const item = document.createElement('div');
                item.className = 'alert alert-secondary d-flex justify-content-between align-items-center';
                item.innerHTML = `
                    <span>Descrição: ${pacote.descricao}, Código de Barras: ${pacote.codigobarrasFiltrado}, Nome do Laboratório: ${pacote.laboratorio_nome || ''}</span>
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
                time = setTimeout(logout, 900000);  // Tempo em milissegundos 900000 = (15 minutos)
            }
        };

        inactivityTime();
    </script>
</body>
</html>
