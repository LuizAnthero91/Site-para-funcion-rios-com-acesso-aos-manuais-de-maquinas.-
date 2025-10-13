<?php
// Inicia a sessão para armazenar temporariamente o usuário se a troca for necessária
session_start();
// Define o tipo de conteúdo como JSON
header('Content-Type: application/json');

// 1. CONFIGURAÇÕES DO BANCO DE DADOS
$host = "localhost";
$usuario_db = "root"; 
$senha_db = ""; 
$banco = "correios_manutencao";
$SENHA_PADRAO = '123456'; 

// 2. CONEXÃO AO BANCO DE DADOS
$conn = new mysqli($host, $usuario_db, $senha_db, $banco);

if ($conn->connect_error) {
    echo json_encode([
        'acesso' => false, 
        'mensagem' => "Erro de Banco de Dados: Não foi possível conectar ao MySQL."
    ]);
    exit();
}

// 3. RECEBIMENTO DOS DADOS
$unidade = $_POST['unidade'] ?? '';
$matricula = $_POST['matricula'] ?? '';
$senha_digitada = $_POST['senha'] ?? ''; 

if (empty($unidade) || empty($matricula) || empty($senha_digitada)) {
    echo json_encode([
        'acesso' => false, 
        'mensagem' => "Matrícula e senha são obrigatórias."
    ]);
    exit();
}

// 4. CONSULTA E VERIFICAÇÃO DE LOGIN
$stmt = $conn->prepare("
    SELECT matricula 
    FROM usuarios 
    WHERE matricula = ? 
      AND unidade = ? 
      AND status = 'ativo' 
      AND senha = PASSWORD(?)
      
");

$stmt->bind_param("sss", $matricula, $unidade, $senha_digitada); 
$stmt->execute();
$stmt->store_result();

$resposta = array();

// 5. PROCESSAMENTO DO RESULTADO
if ($stmt->num_rows > 0) {
    $resposta['acesso'] = true;
    
    // VERIFICAÇÃO DE SENHA PADRÃO
    if ($senha_digitada === $SENHA_PADRAO) {
        $resposta['precisa_trocar'] = true;
        // Armazena temporariamente na sessão para o trocar_senha.php
        $_SESSION['usuario'] = $matricula;
        $_SESSION['unidade'] = $unidade; 
    } else {
        $resposta['precisa_trocar'] = false;
    }
    
    $resposta['mensagem'] = "Acesso liberado!";

} else {
    // Login falhou
    $resposta['acesso'] = false;
    $resposta['mensagem'] = "Matrícula, senha ou unidade incorreta. Verifique seus dados.";
}

// 6. FECHAMENTO DA CONEXÃO
$stmt->close();
$conn->close();

// 7. ENVIO DA RESPOSTA FINAL (JSON)
echo json_encode($resposta);
?>