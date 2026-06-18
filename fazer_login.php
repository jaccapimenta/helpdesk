<?php
// 1. Inclui o arquivo que faz a conexão com o banco de dados
require_once 'conexao.php'; 

// Pega os dados vindos do formulário HTML de login
$emailDigitado = $_POST['email'] ?? '';
$senhaDigitada = $_POST['password'] ?? '';

// 2. Busca o usuário pelo e-mail digitado (Ajustado 'users' para 'users' se necessário)
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute(['email' => $emailDigitado]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC); // FETCH_ASSOC garante o mapeamento correto das colunas

// 3. Verifica se o usuário existe
if ($usuario) {
    
    // 4. Verifica se a senha está correta 
    if ($senhaDigitada == $usuario['password']) {
        
        // LOGIN COM SUCESSO!
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Guarda as informações do usuário logado na sessão
        $_SESSION['users_id'] = $usuario['id'];    // id maiúsculo do seu banco
        $_SESSION['users_name'] = $usuario['name']; // name com 'N' maiúsculo do seu banco
        
        // REDIRECIOnameNTO AJUSTADO: Agora envia o usuário diretamente para o painel de escolha
        header("Location: painel.php"); 
        exit;
        
    } else {
        echo "A senha digitada está incorreta!";
    }
} else {
    echo "E-mail não encontrado!";
}
?>