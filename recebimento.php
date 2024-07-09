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
    <div class="container container-custom">
        <h1 class="text-center mb-4" style="color: #28a745;">Receber Amostra</h1>
        <?php if (isset($mensagem)): ?>
            <div class="alert alert-success"><?php echo $mensagem; ?></div>
        <?php endif; ?>
        <form id="pacoteForm">
            <div class="form-group">
                <label for="codigobarras" style="color: #28a745;">Código de Barras:</label>
                <input type="text" name="codigobarras" id="codigobarras" class="form-control" required>
            </div>
        </form>
        <div id="pacotesList" class="mt-3"></div>
        <button type="button" id="receberTodos" class="btn btn-success btn-block mt-3"><i class="fas fa-check"></i>Receber Todos</button>
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

        document.getElementById('codigobarras').addEventListener('focusout', function() {
            const codigobarras = document.getElementById('codigobarras').value;

            if (codigobarras) {
                pacotes.unshift({ codigobarras });
                atualizarListaPacotes();
                document.getElementById('codigobarras').value = '';
                document.getElementById('codigobarras').focus();
            } else {
                alert('Por favor, preencha o campo de código de barras.');
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
                    alert('Erro ao receber pacotes.');
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
                    <span>Código de Barras: ${pacote.codigobarras}</span>
                    <button class="btn btn-danger btn-sm" onclick="removerPacote(${index})">Excluir</button>
                `;
                lista.appendChild(item);
            });
        }

        function removerPacote(index) {
            pacotes.splice(index, 1);
            atualizarListaPacotes();
        }
    </script>
</body>
</html>
