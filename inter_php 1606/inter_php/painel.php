<?php
session_start();

// Proteção: Se NÃO existir um id de usuário na sessão, manda para o login
if (!isset($_SESSION['users_id'])) {
    header("Location: login.html"); 
    exit();
}

// Opcional: pega o nome do usuário para dar as boas-vindas
$users_name = $_SESSION['users_name'] ?? 'Usuário';

// Carrega o arquivo visual do painel
require_once 'painel.html';
?>