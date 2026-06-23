<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'tecnico') {
    http_response_code(403);
    die('Acesso negado.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: painel-tecnico.php');
    exit();
}

$tickets_id = filter_input(INPUT_POST, 'tickets_id', FILTER_VALIDATE_INT);
$status_id = filter_input(INPUT_POST, 'status_id', FILTER_VALIDATE_INT);
$description = trim($_POST['description'] ?? '');
$tech_id = (int) $_SESSION['usuario_id'];
$date_now = date('Y-m-d H:i:s');

if (!$tickets_id || !in_array($status_id, [2, 4], true) || $description === '') {
    die('Dados invalidos.');
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        "SELECT id FROM tickets
         WHERE id = :tickets_id
           AND tech_support_id = :tech_id
           AND status_id IN (2, 5)
         FOR UPDATE"
    );
    $stmt->execute([
        ':tickets_id' => $tickets_id,
        ':tech_id' => $tech_id
    ]);

    if (!$stmt->fetchColumn()) {
        throw new RuntimeException('Este chamado nao esta em atendimento por este tecnico.');
    }

    $sql = "INSERT INTO feedback_histories (tickets_id, status_id, description, tech_support_id, Date)
            VALUES (:tickets_id, :status_id, :description, :tech_id, :date_now)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':tickets_id' => $tickets_id,
        ':status_id' => $status_id,
        ':description' => $description,
        ':tech_id' => $tech_id,
        ':date_now' => $date_now
    ]);

    $sql = "UPDATE tickets
                SET status_id = :status_id,
                    tech_support_id = COALESCE(tech_support_id, :tech_id),
                    conclusiondate = NULL
            WHERE id = :tickets_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':status_id' => $status_id,
        ':tech_id' => $tech_id,
        ':tickets_id' => $tickets_id
    ]);

    $pdo->commit();
    header('Location: detalhes-chamado.php?id=' . $tickets_id . '&sucesso=historico');
    exit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die('Erro ao salvar historico: ' . $e->getMessage());
}
