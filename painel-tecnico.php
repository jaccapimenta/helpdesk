<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'tecnico') {
    header("Location: login.html");
    exit();
}

$tech_name = $_SESSION['usuario_name'] ?? 'Tecnico';

$sql = "SELECT t.*, u.name AS user_name, s.description AS status_name
        FROM tickets t
        LEFT JOIN users u ON t.users_id = u.id
        LEFT JOIN status s ON t.status_id = s.id
        ORDER BY t.openningdate DESC";
$stmt = $pdo->query($sql);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_abertos = 0;
$total_andamento = 0;
$total_encerrados = 0;

foreach ($tickets as $ticket) {
    if ((int) $ticket['status_id'] === 1) {
        $total_abertos++;
    } elseif ((int) $ticket['status_id'] === 2) {
        $total_andamento++;
    } elseif ((int) $ticket['status_id'] === 3) {
        $total_encerrados++;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Tecnico</title>
    <link rel="stylesheet" href="tech.css">
</head>
<body>
    <aside class="sidebar">
        <h2>Help Desk</h2>
        <nav>
            <ul>
                <li>Dashboard</li>
                <li>Chamados</li>
                <li><a href="login.html" style="color: white; text-decoration: none;">Sair</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header>
            <h1>Painel do Tecnico</h1>
            <p>Bem-vindo, <?php echo htmlspecialchars($tech_name); ?></p>
        </header>

        <?php if (isset($_GET['sucesso'])): ?>
            <p style="background:#e8f5e9; color:#2e7d32; padding:12px; border-radius:8px; margin-bottom:20px;">
                Operacao realizada com sucesso.
            </p>
        <?php endif; ?>

        <section class="cards">
            <div class="card">
                <h2><?php echo $total_abertos; ?></h2>
                <p>Abertos</p>
            </div>
            <div class="card">
                <h2><?php echo $total_andamento; ?></h2>
                <p>Em andamento</p>
            </div>
            <div class="card">
                <h2><?php echo $total_encerrados; ?></h2>
                <p>Encerrados</p>
            </div>
            <div class="card">
                <h2><?php echo count($tickets); ?></h2>
                <p>Total</p>
            </div>
        </section>

        <section class="tickets">
            <div class="section-title">
                <h2>Chamados Recentes</h2>
            </div>

            <table>
                <tr>
                    <th>ID</th>
                    <th>Aluno</th>
                    <th>Tipo</th>
                    <th>Status</th>
                    <th>Acoes</th>
                </tr>

                <?php if (empty($tickets)): ?>
                    <tr>
                        <td colspan="5">Nenhum chamado encontrado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td>#<?php echo (int) $ticket['id']; ?></td>
                            <td><?php echo htmlspecialchars($ticket['user_name'] ?? 'Aluno'); ?></td>
                            <td><?php echo htmlspecialchars($ticket['tickets_type']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['status_name']); ?></td>
                            <td>
                                <a href="detalhes-chamado.php?id=<?php echo (int) $ticket['id']; ?>">Detalhes</a>
                                <?php if ((int) $ticket['status_id'] === 1): ?>
                                    |
                                    <a href="assumir_chamado.php?id=<?php echo (int) $ticket['id']; ?>">Assumir</a>
                                <?php endif; ?>
                                <?php if ((int) $ticket['status_id'] !== 3): ?>
                                    |
                                    <a href="fechar_ticket.php?id=<?php echo (int) $ticket['id']; ?>">Fechar</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </table>
        </section>
    </main>
</body>
</html>
