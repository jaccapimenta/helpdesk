<?php
// 1. Inclui o arquivo que faz a conexao com o banco de dados
require_once 'conexao.php';

// Pega os dados vindos do formulario HTML de login
$emailDigitado = $_POST['email'] ?? '';
$senhaDigitada = $_POST['password'] ?? '';

// 2. Busca o aluno pelo e-mail digitado
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute(['email' => $emailDigitado]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    if ($senhaDigitada == $usuario['password']) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['users_id'] = $usuario['id'];
        $_SESSION['users_name'] = $usuario['name'];
        $_SESSION['tipo_usuario'] = 'aluno';

        header("Location: painel.php");
        exit;
    }

    echo "A senha digitada esta incorreta!";
    exit;
}

// 3. Se nao for aluno, tenta entrar como tecnico
$stmt = $pdo->prepare("SELECT * FROM tech_support WHERE email = :email");
$stmt->execute(['email' => $emailDigitado]);
$tecnico = $stmt->fetch(PDO::FETCH_ASSOC);

if ($tecnico) {
    if ($senhaDigitada == $tecnico['password']) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['usuario_id'] = $tecnico['id'];
        $_SESSION['usuario_name'] = $tecnico['name'];
        $_SESSION['tipo_usuario'] = 'tecnico';

        header("Location: painel-tecnico.php");
        exit;
    }

    echo "A senha digitada esta incorreta!";
    exit;
}

echo "E-mail nao encontrado!";
?>
