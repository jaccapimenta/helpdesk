<?php
session_start();
require_once 'conexao.php';

// Proteção: Só técnicos podem acessar este arquivo
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'tecnico') {
    die("Acesso negado.");
}

// Pega o id do chamado vindo da URL (ex: assumir-chamado.php?id=5)
$tickets_id = $_GET['id'] ?? null;
$tech_id = $_SESSION['usuario_id']; // id do técnico logado
$status_em_atendimento = 2; // id do status correspondente no seu banco

if ($tickets_id) {
    try {
        // Atualiza o chamado colocando o id do técnico e mudando o status
        $sql = "UPDATE tickets 
                SET tech_support_id = :tech_id, status_id = :status_id 
                WHERE id = :tickets_id";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':tech_id' => $tech_id,
            ':status_id' => $status_em_atendimento,
            ':tickets_id' => $tickets_id
        ]);

        // Redireciona de volta para o painel do técnico
        header("Location: painel-tecnico.php?sucesso=chamado_assumido");
        exit();
    } catch (PDOException $e) {
        echo "Erro ao assumir chamado: " . $e->getMessage();
    }
}