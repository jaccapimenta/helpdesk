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

    $satisfaction_level = filter_var($satisfaction_level, FILTER_VALIDATE_INT);

    if ($tickets_id && $satisfaction_level >= 1 && $satisfaction_level <= 5) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "SELECT status_id, satisfaction_level
                 FROM tickets
                 WHERE id = :tickets_id AND users_id = :users_id
                 FOR UPDATE"
            );
            $stmt->execute([
                ':tickets_id' => $tickets_id,
                ':users_id' => $users_id
            ]);
            $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($ticket && in_array((int) $ticket['status_id'], [3, 4], true) && empty($ticket['satisfaction_level'])) {
                $was_waiting_confirmation = (int) $ticket['status_id'] === 4;
                $sql = "UPDATE tickets
                        SET status_id = 3,
                            conclusiondate = NOW(),
                            satisfaction_level = :satisfaction_level,
                            last_user_response = NOW()
                        WHERE id = :tickets_id AND users_id = :users_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':satisfaction_level' => $satisfaction_level,
                    ':tickets_id' => $tickets_id,
                    ':users_id' => $users_id
                ]);

                $description = $was_waiting_confirmation
                    ? 'O usuário confirmou que o chamado foi resolvido. Satisfação: ' . $satisfaction_level . '/5.'
                    : 'O usuário avaliou o atendimento. Satisfação: ' . $satisfaction_level . '/5.';
                $sql = "INSERT INTO feedback_histories
                            (tickets_id, status_id, description, users_id, Date)
                        VALUES (:tickets_id, 3, :description, :users_id, NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':tickets_id' => $tickets_id,
                    ':description' => $description,
                    ':users_id' => $users_id
                ]);
            }

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            die('Erro ao confirmar solucao: ' . $e->getMessage());
        }
    }
}

header("Location: meus-chamados.php?confirmado=1");
exit();
?>
