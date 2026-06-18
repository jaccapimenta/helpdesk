<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'tecnico') {
    die("Acesso negado.");
}

$tickets_id = $_GET['id'] ?? null;
$status_concluido = 3; // id do status 'Concluído' na sua tabela status
$conclusion_date = date('Y-m-d H:i:s');

if ($tickets_id) {
    try {
        // Atualiza a data de conclusão e muda o status do tickets
        $sql = "UPDATE tickets 
                SET conclusiondate = :conclusion_date, status_id = :status_id 
                WHERE id = :tickets_id";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':conclusion_date' => $conclusion_date,
            ':status_id' => $status_concluido,
            ':tickets_id' => $tickets_id
        ]);

        header("Location: painel-tecnico.php?sucesso=chamado_concluido");
        exit();
    } catch (PDOException $e) {
        echo "Erro ao fechar o chamado: " . $e->getMessage();
    }
}