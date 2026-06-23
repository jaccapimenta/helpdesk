<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['users_id'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tickets_id = filter_input(INPUT_POST, 'tickets_id', FILTER_VALIDATE_INT);
    $description = trim($_POST['description'] ?? '');
    $users_id = (int) $_SESSION['users_id'];

    if ($tickets_id && $description !== '') {
        $pdo->beginTransaction();
        $sql = "UPDATE tickets
                SET status_id = 5,
                    conclusiondate = NULL,
                    last_user_response = NOW()
                WHERE id = :tickets_id
                  AND users_id = :users_id
                  AND status_id = 4";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':tickets_id' => $tickets_id,
            ':users_id' => $users_id
        ]);

        if ($stmt->rowCount() === 1) {
            $sql = "INSERT INTO feedback_histories
                        (tickets_id, status_id, description, users_id, Date)
                    VALUES (:tickets_id, 5, :description, :users_id, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':tickets_id' => $tickets_id,
                ':description' => $description,
                ':users_id' => $users_id
            ]);
        }
        $pdo->commit();
    }
}

header("Location: meus-chamados.php?nao_resolvido=1");
exit();
?>
