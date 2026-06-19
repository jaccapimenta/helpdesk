<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['users_id'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tickets_id = $_POST['tickets_id'] ?? null;
    $satisfaction_level = $_POST['satisfaction_level'] ?? null;
    $users_id = $_SESSION['users_id'];

    if ($tickets_id && $satisfaction_level) {
        $sql = "UPDATE tickets
                SET status_id = 3,
                    conclusiondate = NOW(),
                    satisfaction_level = :satisfaction_level,
                    last_user_response = NOW()
                WHERE id = :tickets_id
                  AND users_id = :users_id
                  AND status_id = 4";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':satisfaction_level' => $satisfaction_level,
            ':tickets_id' => $tickets_id,
            ':users_id' => $users_id
        ]);
    }
}

header("Location: meus-chamados.php?confirmado=1");
exit();
?>
