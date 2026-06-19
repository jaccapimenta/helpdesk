<?php
// 1. Inclui a conexão e inicia a sessão
require_once 'conexao.php';
session_start();
require_once 'auto_finalizar_chamados.php';

// Simula o usuário logado
if (!isset($_SESSION['users_id'])) {
    header("Location: login.html");
    exit();
}

$users_id = $_SESSION['users_id'];

try {
    // 2. Busca os chamados do usuário juntamente com a descrição do status
    $sql = "SELECT t.*, s.description AS status_name 
            FROM tickets t
            INNER JOIN status s ON t.status_id = s.id
            WHERE t.users_id = :users_id
            ORDER BY t.openningdate DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':users_id', $users_id, PDO::PARAM_INT);
    $stmt->execute();
    
    // Armazena os registros para o HTML utilizar
    $ticketss = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro ao buscar chamados: " . $e->getMessage());
}

// 3. SEPARAÇÃO: Carrega o arquivo visual que contém o HTML
require_once 'meus-chamados.html';
?>
