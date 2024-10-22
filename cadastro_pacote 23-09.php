<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}



$agrupamentos = []; // Armazenar os agrupamentos por prefixo

// Mapeamento do código de local
$local_id = $_SESSION['unidade_nome'];
$codigolocal_map = [
    'Castanheira' => 4,
    'Coleta Externa' => 5,
    'Metropole' => 6
];

// Verificação do local
// if (!isset($codigolocal_map[$local_id])) {
//     echo "<div class='alert alert-danger'>Local não reconhecido.</div>";
//     exit;
// }

// $codigolocal = $codigolocal_map[$local_id];

$codigolocal = isset($codigolocal_map[$local_id]) ? $codigolocal_map[$local_id] : null;

if (is_null($codigolocal)) {
    // echo "<div class='alert alert-danger'>Local não reconhecido.</div>";
    // Opcional: retornar um valor padrão ou apenas continuar sem fazer mais verificações.
    $codigolocal = null;  // Deixar vazio ou realizar outras ações
}

// Consulta ao banco de dados
$sql = "SELECT cdamostra FROM coleta WHERE dtcoleta = current_date AND hrtermcoleta IS NOT NULL AND cdentjurloccoleta = :codigolocal";
$stmt = $dbconn1->prepare($sql);
$stmt->execute([':codigolocal' => $codigolocal]);

$amostrasSBS = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Adicionar amostras nos agrupamentos com base no prefixo
foreach ($amostrasSBS as $amostra) {
    $prefixo = $amostra['cdamostra'];

    if (!isset($agrupamentos[$prefixo])) {
        $agrupamentos[$prefixo] = ['amostras' => [], 'max_amostras' => 6];
    }

    // if (count($agrupamentos[$prefixo]['amostras']) < $agrupamentos[$prefixo]['max_amostras']) {
    //     $agrupamentos[$prefixo]['amostras'][] = $prefixo;
    // }
}

$sql = "SELECT codigobarras FROM pacotes WHERE DATE(data_cadastro) = NOW()";
    $stmt = $dbconn->prepare($sql);
    $stmt->execute();

    $amostras_existentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($amostras_existentes as $amostra) {
    if (strlen($amostra['codigobarras']) === 15) {
        // Se o código de barras tiver 15 dígitos, pegar os 13 primeiros dígitos
        $prefixo = substr($amostra['codigobarras'], 0, 13); // Considera os 13 primeiros caracteres como prefixo
    } elseif (strlen($amostra['codigobarras']) === 12) {
        // Se o código de barras tiver 12 dígitos, pegar os 10 primeiros dígitos
        $prefixo = substr($amostra['codigobarras'], 0, 10); // Considera os 3 primeiros caracteres como prefixo
    } elseif (strlen($amostra['codigobarras']) === 17) {
        // Se o código de barras tiver 17 dígitos, pegar do 6º ao 15º dígito
        $prefixo = substr($amostra['codigobarras'], 5, 15);
    } else {
        // Caso o código de barras não tenha um número de dígitos esperado
        $prefixo = $amostra['codigobarras']; // Considera os 3 primeiros caracteres como prefixo
    }

    if (!isset($agrupamentos[$prefixo])) {
        $agrupamentos[$prefixo] = ['amostras' => [], 'max_amostras' => 6];
    }

    if (count($agrupamentos[$prefixo]['amostras']) < $agrupamentos[$prefixo]['max_amostras']) {
        $agrupamentos[$prefixo]['amostras'][] =$amostra['codigobarras'];
    }

}
// Preparar os agrupamentos para renderização no frontend
echo "<script>var agrupamentos = " . json_encode($agrupamentos) . ";</script>";


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Amostra</title>
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container container-customlistas">
        <h1 class="text-center mb-4" style="color: #28a745;">Cadastrar Amostra</h1>
        <form id="pacoteForm">
        <div class="form-group">
            <label for="descricao" style="color: #28a745;">Descrição:</label>
            <select name="descricao" id="descricao" class="form-control" required>
                <option value="1° ENVIO">1° ENVIO</option>
                <option value="2° ENVIO">2° ENVIO</option>
                <option value="3° ENVIO">3° ENVIO</option>
                <option value="4° ENVIO">4° ENVIO</option>
            </select>
        </div>
            <div class="form-group">
                <label for="codigobarras" style="color: #28a745;">Código de Barras:</label>
                <input type="text" name="codigobarras" id="codigobarras" class="form-control" required>
            </div>
            <button type="button" id="adicionarPacote" class="btn btn-primary btn-block mt-3"><i class="fas fa-plus"></i> Adicionar Pacote</button>
        </form>
        <div class="mt-3">
            <h4 id="totalPacotes" class="text-center"></h4> <!-- Total de Pacotes -->
        </div>
        <div id="pacotesList" class="mt-3"></div>
        <button type="button" id="cadastrarTodos" class="btn btn-success btn-block mt-3"><i class="fas fa-check"></i> Cadastrar Todos</button>
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
        let addingPackage = false;

        function filtrarCodigoBarras(codigoBarras) {
            let digitoverificarp = codigoBarras.charAt(0);
            let digitoverificaru = codigoBarras.charAt(codigoBarras.length - 1);

            if (digitoverificarp === '=' && !isNaN(digitoverificaru)) {
                codigoBarras=codigoBarras.slice(1);
                return ('B'+codigoBarras.slice(1));
            }else if(codigoBarras.length == 15) {
                return ('B'+codigoBarras.slice(1));
            }else if(codigoBarras.length == 12) {
                return codigoBarras;
            }
            else{ 
                if ((digitoverificarp === 'B' || digitoverificarp === 'b') && !isNaN(digitoverificaru)) {
                    return codigoBarras.slice(0, -2) + '0' + codigoBarras.slice(-1);
                } else if ((digitoverificarp === 'A' || digitoverificarp === 'a') && (digitoverificaru === 'B' || digitoverificaru === 'b')) {
                    return codigoBarras.slice(1, -1);
                } else if ((digitoverificarp === 'A' || digitoverificarp === 'a') && (digitoverificaru === 'A' || digitoverificaru === 'a')){
                    return codigoBarras.slice(1, -1);
                } else {
                    return codigoBarras;
                }
            }
        }

        function codigoBarrasDuplicado(codigobarrasFiltrado) {
            return pacotes.some(pacote => pacote.codigobarrasFiltrado === codigobarrasFiltrado);
        }

        function adicionarPacote() {
            if (addingPackage) return;

            addingPackage = true;
            setTimeout(() => addingPackage = false, 1000); // Evita adicionar o mesmo pacote em menos de 1 segundo

            const descricao = document.getElementById('descricao').value;
            const codigobarras = document.getElementById('codigobarras').value;

            if (descricao && codigobarras) {
                const codigobarrasFiltrado = filtrarCodigoBarras(codigobarras);

                // Verificação de duplicidade na lista dinâmica
                let duplicado = pacotes.some(pacote => pacote.codigobarrasFiltrado === codigobarrasFiltrado);

                if (duplicado) {
                    alert('Pacote com código de barras ' + codigobarrasFiltrado + ' já existe na lista.');
                    document.getElementById('codigobarras').value = ''; // Limpa o campo de código de barras
                    document.getElementById('codigobarras').focus(); // Mantém o foco no campo de código de barras
                    return;
                }
                // Verificação se algum código de barras na lista contém o código informado
                let codigoContido = pacotes.some(pacote => codigobarrasFiltrado.includes(pacote.codigobarrasFiltrado));

                if (codigoContido) {
                    alert('O código de barras ' + codigobarrasFiltrado + ' contém um código já existente na lista.');
                    document.getElementById('codigobarras').value = ''; // Limpa o campo de código de barras
                    document.getElementById('codigobarras').focus(); // Mantém o foco no campo de código de barras
                    return;
                }

                // Verificação de duplicidade no banco de dados
                fetch('verificar_codigo_barras.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'descricao=' + encodeURIComponent(descricao) + '&codigobarras=' + encodeURIComponent(codigobarrasFiltrado)
                            
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'exists') {
                        alert('Pacote com código de barras ' + codigobarrasFiltrado + ' já existe no banco de dados.');
                        document.getElementById('codigobarras').value = ''; // Limpa o campo de código de barras
                        document.getElementById('codigobarras').focus(); // Mantém o foco no campo de código de barras
                    } else if (data.status === 'lab_nexiste'){
                        alert('Pacote com código de barras ' + codigobarrasFiltrado + ' não é de nenhum laboratorio cadastrado no sistema.');
                        document.getElementById('codigobarras').value = ''; // Limpa o campo de código de barras
                        document.getElementById('codigobarras').focus(); // Mantém o foco no campo de código de barras
                    } else if (data.status === 'desc_exists') {
                        alert('O ' + descricao + ' desta unidade já foi concluido! Por favor, tente utilza o proximo envio.');
                        document.getElementById('codigobarras').value = ''; // Limpa o campo de código de barras
                        document.getElementById('codigobarras').focus(); // Mantém o foco no campo de código de barras
                    }else if(data.status === 'tamanho'){
                        alert('O tamanho do codigo ' + codigobarrasFiltrado + ' esta irregular. ');
                        document.getElementById('codigobarras').value = ''; // Limpa o campo de código de barras
                        document.getElementById('codigobarras').focus(); // Mantém o foco no campo de código de barras
                    }else{
                        pacotes.push({ descricao, codigobarras, codigobarrasFiltrado });
                        atualizarListaPacotes();
                        document.getElementById('codigobarras').value = '';
                        document.getElementById('codigobarras').focus();
                    }
                });
            } else {
                alert('Por favor, preencha todos os campos.');
            }
        }

        document.getElementById('adicionarPacote').addEventListener('click', adicionarPacote);

        document.getElementById('codigobarras').addEventListener('keydown', function(event) {
            if (event.key === 'Tab' || event.key === 'Enter') {
                event.preventDefault();
                adicionarPacote();
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
                    pacotes = [];
                    atualizarListaPacotes();
                } else if (data.status === 'error') {
                    alert(data.message);
                    atualizarListaPacotes();
                } else {
                    alert('Erro ao cadastrar pacotes.');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao processar a solicitação.');
            });
        });
        function filtrarCodigoBarrasA(codigoBarras) {
            let prefixo = '';

            if (codigoBarras.length === 15) {
                // Se o código de barras tiver 15 dígitos, pegar os 13 primeiros dígitos
                prefixo = codigoBarras.substring(0, 13);
            } else if (codigoBarras.length === 12) {
                // Se o código de barras tiver 12 dígitos, pegar os 10 primeiros dígitos
                prefixo = codigoBarras.substring(0, 10);
            } else if (codigoBarras.length === 17) {
                // Se o código de barras tiver 17 dígitos, pegar do 6º ao 15º dígito
                prefixo = codigoBarras.substring(5, 15);
            } else {
                // Caso o código de barras não tenha um número de dígitos esperado
                prefixo = codigoBarras;  // Pode ajustar para lidar com outros casos se necessário
            }

            return prefixo;
        }

        const pacotesList = document.getElementById('pacotesList');
        
        document.addEventListener('DOMContentLoaded', function() {
            // Verifica se os agrupamentos foram passados para o JavaScript
            if (typeof agrupamentos !== 'undefined') {
                // let pacotesList = document.getElementById('pacotesList');
                pacotesList.innerHTML = ''; // Limpa a lista atual

                // Para cada agrupamento, exibe as amostras
                for (const prefixo in agrupamentos) {
                    let grupo = agrupamentos[prefixo];
                    let divGrupo = document.createElement('div');
                    divGrupo.classList.add('agrupamento');

                    // Cria o título do agrupamento
                    const tituloGrupo = document.createElement('h5');
                    tituloGrupo.className = 'alert alert-info';
                    // tituloGrupo.textContent = `Prefixo: ${prefixo} (Faltam pelo menos ${grupo.max_amostras - grupo.amostras.length} amostras para completar)`;
                    tituloGrupo.textContent = `Prefixo: ${prefixo} - Total: ${grupo.amostras.length} amostras`;
                    divGrupo.appendChild(tituloGrupo);

                    // Cria a lista de amostras
                    let ul = document.createElement('ul');
                    grupo.amostras.forEach(function(amostra) {
                        let li = document.createElement('li');
                        li.innerText = amostra;
                        ul.appendChild(li);
                    });

                    divGrupo.appendChild(ul);
                    pacotesList.appendChild(divGrupo);
                }
            }
        });
        
        function atualizarListaPacotes() {
            // const lista = document.getElementById('pacotesList');
            const totalPacotes = document.getElementById('totalPacotes');
            pacotesList.innerHTML = '';

            // Atualizar o total de pacotes
            totalPacotes.textContent = `Total de Amostras: ${pacotes.length}`;

            // Agrupar pacotes pelos 13 primeiros dígitos do código de barras
            let grupos = {};

            pacotes.forEach(pacote => {
                
                const prefixoDigitos = filtrarCodigoBarrasA(pacote.codigobarrasFiltrado);
                
                // Se o grupo ainda não existir, cria um array para armazenar os pacotes
                if (!grupos[prefixoDigitos]) {
                    grupos[prefixoDigitos] = [];
                }
                grupos[prefixoDigitos].push(pacote);
            });

            // Exibe os pacotes agrupados
            for (let prefixo in grupos) {
                const grupoPacotes = grupos[prefixo];

                // Cria um título para o grupo com o prefixo e o número de pacotes
                const tituloGrupo = document.createElement('h5');
                tituloGrupo.className = 'alert alert-info';
                tituloGrupo.textContent = `Prefixo: ${prefixo} - Total: ${grupoPacotes.length} amostras`;
                pacotesList.appendChild(tituloGrupo);

                // Exibe cada pacote dentro do grupo
                grupoPacotes.forEach((pacote, index) => {
                    const item = document.createElement('div');
                    item.className = 'alert alert-secondary d-flex justify-content-between align-items-center';
                    item.innerHTML = `
                        <span>Descrição: ${pacote.descricao}, Código de Barras: ${pacote.codigobarrasFiltrado}</span>
                        <button class="btn btn-danger btn-sm" onclick="removerPacote(${index})">Excluir</button>
                    `;
                    pacotesList.appendChild(item);
                });
                // Aviso se ainda faltam amostras para completar o agrupamento de 4 ou 5 amostras
                if (grupoPacotes.length < 4) {
                    const aviso = document.createElement('div');
                    aviso.className = 'alert alert-warning';
                    aviso.textContent = `Faltam pelo menos ${4 - grupoPacotes.length} ou ${5 - grupoPacotes.length} amostras para completar o total desta coleta.`;
                    pacotesList.appendChild(aviso);
                }
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
