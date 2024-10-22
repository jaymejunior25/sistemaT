<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$local_id = $_SESSION['unidade_nome'];
$user_id = $_SESSION['user_id'];
$sql = "UPDATE user_sessions SET last_activity = NOW() WHERE user_id = :user_id";
$stmt = $dbconn->prepare($sql);
$stmt->execute([':user_id' => $user_id]);

// Função para verificar prefixos e adicionar amostras ao agrupamento
function buscarPrefixosEAdicionarAoAgrupamento($dbconn1, $local_id, &$agrupamentos, &$mensagens) {
    // Mapeamento do código de local
    $codigolocal_map = [
        'Castanheira' => 4,
        'Coleta Externa' => 5,
        'Metropole' => 6
    ];
    
    if (!isset($codigolocal_map[$local_id])) {
        $mensagens[] = "Local não reconhecido.";
        return;
    }

    $codigolocal = $codigolocal_map[$local_id];

    // Consulta no novo banco para buscar amostras da data atual com base no local
    $sql = "SELECT cdamostra FROM coleta WHERE dtcoleta = current_date AND hrtermcoleta IS NOT NULL AND cdentjurloccoleta = :codigolocal";
    $stmt = $dbconn1->prepare($sql);
    $stmt->execute([':codigolocal' => $codigolocal]);

    $amostras = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Adicionar amostras nos agrupamentos com base no prefixo
    foreach ($amostras as $amostra) {
        $prefixo = substr($amostra['cdamostra'], 0, 13); // Considera os 3 primeiros caracteres como prefixo

        if (!isset($agrupamentos[$prefixo])) {
            $agrupamentos[$prefixo] = ['amostras' => [], 'max_amostras' => 5];
        }

        if (count($agrupamentos[$prefixo]['amostras']) < $agrupamentos[$prefixo]['max_amostras']) {
            $agrupamentos[$prefixo]['amostras'][] = $amostra['cdamostra'];
        }
    }
}

// Função para verificar amostras no banco existente e adicionar ao agrupamento
function verificarEAdicionarAmostrasExistentes($dbconn, &$agrupamentos, &$mensagens) {
    // Consulta no banco existente para verificar amostras já cadastradas
    $sql = "SELECT codigobarras FROM pacotes WHERE DATE(data_cadastro) = CURRENT_DATE";
    $stmt = $dbconn->prepare($sql);
    $stmt->execute();

    $amostras_existentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($amostras_existentes as $amostra) {
        $prefixo = substr($amostra['codigobarras'], 0, 13); // Considera os 3 primeiros caracteres como prefixo

        if (!isset($agrupamentos[$prefixo])) {
            $agrupamentos[$prefixo] = ['amostras' => [], 'max_amostras' => 5];
        }

        if (count($agrupamentos[$prefixo]['amostras']) < $agrupamentos[$prefixo]['max_amostras']) {
            $agrupamentos[$prefixo]['amostras'][] = $amostra['codigobarras'];
        }
    }
}



if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pacotes = json_decode($_POST['pacotes'], true);

    $usuario_cadastro_id = $_SESSION['user_id'];
    $local_id = $_SESSION['unidade_id'];

    $agrupamentos = []; // Armazenar os agrupamentos por prefixo
    $ids_laboratorios = [];
    $messages = [];

    // Buscar prefixos e adicionar amostras ao agrupamento
    buscarPrefixosEAdicionarAoAgrupamento($dbconn1, $local_id, $agrupamentos, $mensagens);

    // Verificar amostras existentes e adicionar ao agrupamento
    verificarEAdicionarAmostrasExistentes($dbconn, $agrupamentos, $mensagens);

    foreach ($pacotes as $pacote) {
        $descricao = $pacote['descricao'];
        $codigobarras = $pacote['codigobarras'];

        // Verificar se o código de barras e descrição já foram enviados hoje
        $stmt = $dbconn->prepare("SELECT * FROM pacotes WHERE codigobarras = :codigobarras OR (unidade_cadastro_id = :unidade_cadastro_id and descricao = :descricao AND DATE(data_cadastro) = CURRENT_DATE) and (status = 'enviado' or status = 'recebido' or status = 'recebidolab') ");
        $stmt->execute([':codigobarras' => $codigobarras,':unidade_cadastro_id'=> $local_id, ':descricao' => $descricao]);
        $pacote_existente_enviado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($pacote_existente_enviado) {
            $messages[] =  'O ' . $descricao . 'desta unidade já foi concluido! Por favor, tente utilza o proximo envio.';
            continue;
        }

        // Verificar se o código de barras já existe no banco de dados
        $stmt = $dbconn->prepare("SELECT * FROM pacotes WHERE codigobarras = :codigobarras");
        $stmt->execute([':codigobarras' => $codigobarras]);
        $pacote_existente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($pacote_existente) {
            $messages[] = 'Pacote com código de barras ' . $codigobarras . ' já existe no banco de dados.';
            continue;
        }


        // Verificar se o novo código de barras contém algum código existente no banco de dados
        $stmt = $dbconn->prepare("SELECT codigobarras FROM pacotes");
        $stmt->execute();
        $pacotes_existentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $codigo_contido = false;

        foreach ($pacotes_existentes as $pacote) {
            if (strpos($codigobarras, $pacote['codigobarras']) !== false) {
                $messages[] = 'O código de barras informado ' . $codigobarras . ' contém o código já cadastrado: ' . $pacote['codigobarras'] . '.';
                $codigo_contido = true;
                break;
            }
        }

        if ($codigo_contido) {
            continue;
        }

        

        // Separa o primeiro e o último dígito do código de barras
        $digitoverificarp = substr($codigobarras, 0, 1);
        $digitoverificaru = substr($codigobarras, -1);

        if (($digitoverificarp === 'A' || $digitoverificarp === 'a') && ($digitoverificaru === 'B' || $digitoverificaru === 'b')) {
            $doisultimos_digitos = 20; // ID do laboratório LABMASTER
            $codigobarras = substr($codigobarras, 1, -1); // Remove o primeiro e o último dígito
        } else {
            if ($digitoverificarp === '=' && ctype_digit($digitoverificaru)) {
                $codigobarras = substr($codigobarras, 1);
                $codigobarras = substr_replace($codigobarras, 'B', 0, 1);
                $doisultimos_digitos = substr($codigobarras, -2);
                
            } elseif(strlen($codigobarras) === 15){
                $codigobarras = substr_replace($codigobarras, 'B', 0, 1);
                $doisultimos_digitos = substr($codigobarras, -2);
            }else {
                if (($digitoverificarp === 'B' || $digitoverificarp === 'b') && ctype_digit($digitoverificaru)) {
                    $codigobarras = substr_replace($codigobarras, '0', -2, 1);
                    $penultimo_digito = substr($codigobarras, -2, 1);
                } elseif (($digitoverificarp === 'A' || $digitoverificarp === 'a') && ($digitoverificaru === 'A' || $digitoverificaru === 'A')){
                    $codigobarras = substr($codigobarras, 1, -1);
                    $penultimo_digito = substr($codigobarras, -2, 1);
                }
                else {
                    if(strlen($codigobarras) === 9){
                        $doisultimos_digitos = 20; // ID do laboratório LABMASTER
                    }else{
                        $penultimo_digito = substr($codigobarras, -2, 1);
                    }
                }
            }

        }
            // Verificar qual dígito usar: os dois últimos ou o penúltimo
            $digito_a_utilizar = ($digitoverificarp === '=' && ctype_digit($digitoverificaru)) || (strlen($codigobarras) === 15) || (strlen($codigobarras) === 9) || (($digitoverificarp === 'A' || $digitoverificarp === 'a') && ($digitoverificaru === 'B' || $digitoverificaru === 'b')) ? $doisultimos_digitos : $penultimo_digito;
            
            // Consultar o ID do laboratório correspondente ao dígito
            $stmt = $dbconn->prepare("SELECT * FROM laboratorio WHERE digito = :digito");
            $stmt->execute([':digito' => $digito_a_utilizar]);
            $lab = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($lab) {
                    // Verificar agrupamento correspondente pelo prefixo
                    if(strlen($codigobarras) === 15){
                    $prefixo = substr($codigobarras, 0, 13);}
                    
                    if (isset($agrupamentos[$prefixo])) {
                        $total_amostras_agrupamento = count($agrupamentos[$prefixo]['amostras']);

                        if ($total_amostras_agrupamento < $agrupamentos[$prefixo]['max_amostras']) {
                            // Adicionar a amostra ao agrupamento
                            $agrupamentos[$prefixo]['amostras'][] = $codigobarras;
                            $total_amostras_restantes = $agrupamentos[$prefixo]['max_amostras'] - $total_amostras_agrupamento - 1;
                            $mensagens[] = "Amostra adicionada ao agrupamento $prefixo. Faltam $total_amostras_restantes amostras.";
                        } else {
                            $mensagens[] = "Agrupamento $prefixo já está completo.";
                        }
                    } else {
                        $laboratorio_id = $lab['id'];
                        $ids_laboratorios[] = $laboratorio_id;
                        // Inserir o novo pacote no banco de dados
                        $stmt = $dbconn->prepare("INSERT INTO pacotes (descricao, codigobarras, usuario_cadastro_id, unidade_cadastro_id, data_cadastro, lab_id) VALUES (:descricao, :codigobarras, :usuario_cadastro_id, :unidade_cadastro_id, NOW(), :lab_id)");
                        $stmt->execute([
                            ':descricao' => $descricao,
                            ':codigobarras' => $codigobarras,
                            ':usuario_cadastro_id' => $usuario_cadastro_id,
                            ':unidade_cadastro_id' => $local_id,
                            ':lab_id' => $laboratorio_id
                        ]);
                    }
            }else{
                $messages[] = 'Pacote com código de barras ' . $codigobarras . ' não é de nenhum laboratorio cadastrado no sistema.';
                continue;
            }




    }

    if (empty($messages)) {
        echo json_encode(['status' => 'success', 'message' => 'Pacotes cadastrados com sucesso!', 'labs' => $ids_laboratorios]);
    } else {
        echo json_encode(['status' => 'error', 'message' => $messages]);
    }
}
?>
