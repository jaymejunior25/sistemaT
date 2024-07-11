<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Tubos</title>
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container container-customlistas">
        <h1 class="text-center mb-4" style="color: #28a745;">Cadastro de Tubos</h1>
        <form id="tubosForm">
            <div class="form-group">
                <label for="local" style="color: #28a745;">Local:</label>
                <input type="text" name="local" id="local" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="tipos_amostras" style="color: #28a745;">Tipos de Amostras:</label>
                <select multiple name="tipos_amostras" id="tipos_amostras" class="form-control" required>
                    <option value="doador">Doador</option>
                    <option value="transplante">Transplante</option>
                    <option value="outras">Outras</option>
                </select>
            </div>
            <div class="form-group">
                <label for="numero_tubos" style="color: #28a745;">Número de Tubos:</label>
                <input type="number" name="numero_tubos" id="numero_tubos" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="observacao" style="color: #28a745;">Observação:</label>
                <textarea name="observacao" id="observacao" class="form-control"></textarea>
            </div>
            <button type="button" id="cadastrar" class="btn btn-success btn-block mt-3"><i class="fas fa-check"></i> Cadastrar</button>
        </form>
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
        document.getElementById('cadastrar').addEventListener('click', function() {
            const local = document.getElementById('local').value;
            const tiposAmostras = Array.from(document.getElementById('tipos_amostras').selectedOptions).map(option => option.value);
            const numeroTubos = document.getElementById('numero_tubos').value;
            const observacao = document.getElementById('observacao').value;

            if (!local || !tiposAmostras.length || !numeroTubos) {
                alert('Por favor, preencha todos os campos obrigatórios.');
                return;
            }

            const data = {
                local: local,
                tipos_amostras: JSON.stringify(tiposAmostras),
                numero_tubos: numeroTubos,
                observacao: observacao
            };

            fetch('teste 15.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    document.getElementById('tubosForm').reset();
                    window.location.href = 'teste 16.php?codigo=' + data.codigo;
                } else {
                    alert(data.message);
                }
            });
        });
    </script>
</body>
</html>


