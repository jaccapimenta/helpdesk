<?php
$host = 'localhost';
$dbname = 'helpdesk';
$usersname = 'root';
$password = 'root';

try {
    // Cria a conexão com o banco de dados
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $usersname, $password);
    
    // Configura o PDO para lançar erros em caso de falhas
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}
?>