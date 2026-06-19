<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['users_id'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tickets_id = $_POST['tickets_id'] ?? null;
    $users_id = $_SESSION['users_id'];

    if ($tickets_id) {
        $sql = "UPDATE tickets
                SET status_id = 1,
                    conclusiondate = NULL,
                    tech_support_id = NULL,
                    resolution_notice_date = NULL,
                    last_user_response = NOW(),
                    reopened_count = reopened_count + 1
                WHERE id = :tickets_id
                  AND users_id = :users_id
                  AND status_id = 4";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':tickets_id' => $tickets_id,
            ':users_id' => $users_id
        ]);
    }
}

header("Location: meus-chamados.php?reaberto=1");
exit();
?>
