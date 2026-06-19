<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'tecnico') {
    die("Acesso negado.");
}

$tickets_id = $_GET['id'] ?? null;
$status_aguardando_confirmacao = 4;
$resolution_notice_date = date('Y-m-d H:i:s');

if ($tickets_id) {
    try {
        $sql = "UPDATE tickets
                SET resolution_notice_date = :resolution_notice_date,
                    status_id = :status_id
                WHERE id = :tickets_id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':resolution_notice_date' => $resolution_notice_date,
            ':status_id' => $status_aguardando_confirmacao,
            ':tickets_id' => $tickets_id
        ]);

        header("Location: painel-tecnico.php?sucesso=aguardando_confirmacao");
        exit();
    } catch (PDOException $e) {
        echo "Erro ao avisar resolucao do chamado: " . $e->getMessage();
    }
}
?>
