<?php
// 1. Inclui a conexão e inicia a sessão
require_once 'conexao.php';
session_start();

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

    $historicos_por_chamado = [];
    if (!empty($ticketss)) {
        $ticket_ids = array_column($ticketss, 'id');
        $placeholders = implode(',', array_fill(0, count($ticket_ids), '?'));
        $sql = "SELECT fh.tickets_id, fh.description, fh.Date,
                       fh.tech_support_id, fh.users_id,
                       ts.name AS tech_name, u.name AS user_name,
                       s.description AS status_name
                FROM feedback_histories fh
                LEFT JOIN tech_support ts ON fh.tech_support_id = ts.id
                LEFT JOIN users u ON fh.users_id = u.id
                LEFT JOIN status s ON fh.status_id = s.id
                WHERE fh.tickets_id IN ($placeholders)
                ORDER BY fh.Date ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($ticket_ids);

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $historico) {
            $historicos_por_chamado[$historico['tickets_id']][] = $historico;
        }
    }

} catch (PDOException $e) {
    die("Erro ao buscar chamados: " . $e->getMessage());
}

// 3. SEPARAÇÃO: Carrega o arquivo visual que contém o HTML
require_once 'meus-chamados.html';
?>
