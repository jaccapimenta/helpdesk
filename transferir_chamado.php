<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'tecnico') {
    header('Location: login.html');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: painel-tecnico.php');
    exit();
}

$tickets_id = filter_input(INPUT_POST, 'tickets_id', FILTER_VALIDATE_INT);
$target_tech_id = filter_input(INPUT_POST, 'target_tech_id', FILTER_VALIDATE_INT);
$current_tech_id = (int) $_SESSION['usuario_id'];

if (!$tickets_id || !$target_tech_id || $target_tech_id === $current_tech_id) {
    die('Dados invalidos para transferencia.');
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT name FROM tech_support WHERE id = :id');
    $stmt->execute([':id' => $target_tech_id]);
    $target_name = $stmt->fetchColumn();

    if (!$target_name) {
        throw new RuntimeException('Tecnico de destino nao encontrado.');
    }

    $sql = "UPDATE tickets
            SET tech_support_id = :target_tech_id
            WHERE id = :tickets_id
              AND tech_support_id = :current_tech_id
              AND status_id IN (2, 5)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':target_tech_id' => $target_tech_id,
        ':tickets_id' => $tickets_id,
        ':current_tech_id' => $current_tech_id
    ]);

    if ($stmt->rowCount() !== 1) {
        throw new RuntimeException('Este chamado nao pode ser transferido por este tecnico.');
    }

    $sql = "INSERT INTO feedback_histories
                (tickets_id, status_id, description, tech_support_id, Date)
            SELECT id, status_id, :description, :current_tech_id, NOW()
            FROM tickets WHERE id = :tickets_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':description' => 'Chamado transferido para o tecnico ' . $target_name . '.',
        ':current_tech_id' => $current_tech_id,
        ':tickets_id' => $tickets_id
    ]);

    $pdo->commit();
    header('Location: painel-tecnico.php?sucesso=transferido#chamados');
    exit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die('Erro ao transferir chamado: ' . $e->getMessage());
}
