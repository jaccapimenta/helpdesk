<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'tecnico') {
    header("Location: login.html");
    exit();
}

$tech_name = $_SESSION['usuario_name'] ?? 'Tecnico';

$base_sql = "SELECT t.*, u.name AS user_name, s.description AS status_name,
                    assigned.name AS assigned_tech_name
        FROM tickets t
        LEFT JOIN users u ON t.users_id = u.id
        LEFT JOIN status s ON t.status_id = s.id
        LEFT JOIN tech_support assigned ON t.tech_support_id = assigned.id";

$all_tickets = $pdo->query($base_sql . " ORDER BY t.openningdate DESC")->fetchAll(PDO::FETCH_ASSOC);

$filter_status = filter_input(INPUT_GET, 'status_id', FILTER_VALIDATE_INT) ?: null;
$filter_tech_input = $_GET['tech_support_id'] ?? '';
$filter_tech = $filter_tech_input !== '' ? filter_var($filter_tech_input, FILTER_VALIDATE_INT) : null;
$filter_type = trim($_GET['tickets_type'] ?? '');
$filter_name = trim($_GET['user_name'] ?? '');
$filter_id_input = trim($_GET['ticket_id'] ?? '');
$filter_id = $filter_id_input !== '' ? filter_var($filter_id_input, FILTER_VALIDATE_INT) : null;

$where = [];
$params = [];

if ($filter_status) {
    $where[] = 't.status_id = :status_id';
    $params[':status_id'] = $filter_status;
}
if ($filter_tech_input === 'unassigned') {
    $where[] = 't.tech_support_id IS NULL';
} elseif ($filter_tech) {
    $where[] = 't.tech_support_id = :tech_support_id';
    $params[':tech_support_id'] = $filter_tech;
}
if ($filter_type !== '') {
    $where[] = 't.tickets_type = :tickets_type';
    $params[':tickets_type'] = $filter_type;
}
if ($filter_name !== '') {
    $where[] = 'u.name LIKE :user_name';
    $params[':user_name'] = '%' . $filter_name . '%';
}
if ($filter_id) {
    $where[] = 't.id = :ticket_id';
    $params[':ticket_id'] = $filter_id;
}

$sql = $base_sql;
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY t.openningdate DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tech_options = $pdo->query('SELECT id, name FROM tech_support ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
$type_options = $pdo->query('SELECT DISTINCT tickets_type FROM tickets ORDER BY tickets_type')->fetchAll(PDO::FETCH_COLUMN);

$total_abertos = 0;
$total_andamento = 0;
$total_encerrados = 0;
$total_aguardando = 0;
$total_nao_resolvidos = 0;

foreach ($all_tickets as $ticket) {
    if ((int) $ticket['status_id'] === 1) {
        $total_abertos++;
    } elseif ((int) $ticket['status_id'] === 2) {
        $total_andamento++;
    } elseif ((int) $ticket['status_id'] === 3) {
        $total_encerrados++;
    } elseif ((int) $ticket['status_id'] === 4) {
        $total_aguardando++;
    } elseif ((int) $ticket['status_id'] === 5) {
        $total_nao_resolvidos++;
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
    <main class="main-content panel-content">
        <header class="panel-header">
            <div>
                <span class="panel-brand">Help Desk</span>
                <h1>Painel do Tecnico</h1>
                <p>Bem-vindo, <?php echo htmlspecialchars($tech_name); ?></p>
            </div>
            <a href="logout.php" class="detail-logout">Sair</a>
        </header>

        <?php if (isset($_GET['sucesso'])): ?>
            <p style="background:#e8f5e9; color:#2e7d32; padding:12px; border-radius:8px; margin-bottom:20px;">
                <?php echo $_GET['sucesso'] === 'transferido' ? 'Chamado transferido com sucesso.' : 'Operacao realizada com sucesso.'; ?>
            </p>
        <?php endif; ?>

        <section class="cards">
            <a class="card status-filter-card card-filter-1 <?php echo $filter_status === 1 ? 'active' : ''; ?>" href="painel-tecnico.php?status_id=1#chamados">
                <h2><?php echo $total_abertos; ?></h2>
                <p>Abertos</p>
            </a>
            <a class="card status-filter-card card-filter-2 <?php echo $filter_status === 2 ? 'active' : ''; ?>" href="painel-tecnico.php?status_id=2#chamados">
                <h2><?php echo $total_andamento; ?></h2>
                <p>Em andamento</p>
            </a>
            <a class="card status-filter-card card-filter-3 <?php echo $filter_status === 3 ? 'active' : ''; ?>" href="painel-tecnico.php?status_id=3#chamados">
                <h2><?php echo $total_encerrados; ?></h2>
                <p>Encerrados</p>
            </a>
            <a class="card status-filter-card card-filter-4 <?php echo $filter_status === 4 ? 'active' : ''; ?>" href="painel-tecnico.php?status_id=4#chamados">
                <h2><?php echo $total_aguardando; ?></h2>
                <p>Aguardando aluno</p>
            </a>
            <a class="card status-filter-card card-filter-5 <?php echo $filter_status === 5 ? 'active' : ''; ?>" href="painel-tecnico.php?status_id=5#chamados">
                <h2><?php echo $total_nao_resolvidos; ?></h2>
                <p>Nao resolvidos</p>
            </a>
        </section>

        <section class="tickets" id="chamados">
            <div class="section-title">
                <h2>Chamados Recentes</h2>
            </div>

            <form method="GET" action="painel-tecnico.php#chamados" class="ticket-filters">
                <?php if ($filter_status): ?>
                    <input type="hidden" name="status_id" value="<?php echo (int) $filter_status; ?>">
                <?php endif; ?>
                <div class="filter-field">
                    <label for="tickets_type">Tipo</label>
                    <select id="tickets_type" name="tickets_type">
                        <option value="">Todos</option>
                        <?php foreach ($type_options as $option): ?>
                            <option value="<?php echo htmlspecialchars($option); ?>" <?php echo $filter_type === $option ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($option); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-field">
                    <label for="tech_support_id">Tecnico</label>
                    <select id="tech_support_id" name="tech_support_id">
                        <option value="">Todos</option>
                        <option value="unassigned" <?php echo $filter_tech_input === 'unassigned' ? 'selected' : ''; ?>>Nao atribuido</option>
                        <?php foreach ($tech_options as $option): ?>
                            <option value="<?php echo (int) $option['id']; ?>" <?php echo $filter_tech === (int) $option['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($option['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-field">
                    <label for="user_name">Usuario</label>
                    <input id="user_name" name="user_name" value="<?php echo htmlspecialchars($filter_name); ?>" placeholder="Nome do usuario">
                </div>

                <div class="filter-field filter-id">
                    <label for="ticket_id">ID</label>
                    <input id="ticket_id" name="ticket_id" type="number" min="1" value="<?php echo htmlspecialchars($filter_id_input); ?>">
                </div>

                <div class="filter-actions">
                    <a href="painel-tecnico.php" class="clear-filter">Limpar</a>
                </div>
            </form>

            <table>
                <tr>
                    <th>ID</th>
                    <th>Aluno</th>
                    <th>Tipo</th>
                    <th>Status</th>
                    <th>Tecnico</th>
                    <th>Acoes</th>
                </tr>

                <?php if (empty($tickets)): ?>
                    <tr>
                        <td colspan="6">Nenhum chamado encontrado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td>#<?php echo (int) $ticket['id']; ?></td>
                            <td><?php echo htmlspecialchars($ticket['user_name'] ?? 'Aluno'); ?></td>
                            <td><?php echo htmlspecialchars($ticket['tickets_type']); ?></td>
                            <td>
                                <span class="status-badge status-badge-<?php echo (int) $ticket['status_id']; ?>">
                                    <?php echo htmlspecialchars($ticket['status_name']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($ticket['assigned_tech_name'] ?? 'Nao atribuido'); ?></td>
                            <td>
                                <a href="detalhes-chamado.php?id=<?php echo (int) $ticket['id']; ?>">Detalhes</a>
                                <?php if ((int) $ticket['status_id'] === 1): ?>
                                    |
                                    <a href="assumir_chamado.php?id=<?php echo (int) $ticket['id']; ?>">Assumir</a>
                                <?php endif; ?>
                                <?php if (in_array((int) $ticket['status_id'], [2, 5], true) && (int) $ticket['tech_support_id'] === (int) $_SESSION['usuario_id']): ?>
                                    |
                                    <a href="detalhes-chamado.php?id=<?php echo (int) $ticket['id']; ?>#transferir">Transferir</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </table>
        </section>
    </main>
    <script>
        const filterForm = document.querySelector('.ticket-filters');
        let filterTimer;

        filterForm.querySelectorAll('select').forEach(function (field) {
            field.addEventListener('change', function () {
                filterForm.requestSubmit();
            });
        });

        filterForm.querySelectorAll('input:not([type="hidden"])').forEach(function (field) {
            field.addEventListener('input', function () {
                clearTimeout(filterTimer);
                filterTimer = setTimeout(function () {
                    filterForm.requestSubmit();
                }, 450);
            });
        });
    </script>
</body>
</html>
