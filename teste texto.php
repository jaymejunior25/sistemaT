// Dentro do loop foreach onde você processa cada pacote

// Consultar o nome do laboratório correspondente ao penúltimo dígito
$stmtLab = $dbconn->prepare("SELECT nome FROM laboratorio WHERE digito = :digito");
$stmtLab->execute([':digito' => $penultimo_digito]);
$lab = $stmtLab->fetch(PDO::FETCH_ASSOC);

$laboratorio_nome = ($lab) ? $lab['nome'] : '';

// Inserir o novo pacote no banco de dados
$stmt = $dbconn->prepare("INSERT INTO pacotes (descricao, codigobarras, usuario_cadastro_id, unidade_cadastro_id, data_cadastro, lab_id ) VALUES (:descricao, :codigobarras, :usuario_cadastro_id, :unidade_cadastro_id, NOW(), :lab_id)");
$stmt->execute([
    ':descricao' => $descricao,
    ':codigobarras' => $codigobarras,
    ':usuario_cadastro_id' => $usuario_cadastro_id,
    ':unidade_cadastro_id' => $local_id,
    ':lab_id' => $laboratorio_id
]);

// Adicionar o nome do laboratório ao pacote para retorno
$pacote['laboratorio_nome'] = $laboratorio_nome;





function atualizarListaPacotes() {
    const lista = document.getElementById('pacotesList');
    lista.innerHTML = '';

    pacotes.forEach((pacote, index) => {
        const item = document.createElement('div');
        item.className = 'alert alert-secondary d-flex justify-content-between align-items-center';
        item.innerHTML = `
            <span>Descrição: ${pacote.descricao}, Código de Barras: ${pacote.codigobarras}, Laboratório: ${pacote.laboratorio_nome}</span>
            <button class="btn btn-danger btn-sm" onclick="removerPacote(${index})">Excluir</button>
        `;
        lista.appendChild(item);
    });
}

