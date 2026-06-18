<?php
// 1. Inclui a conexão com o banco
require_once 'conexao.php';

// Criamos uma sessão fictícia aqui para simular o id do usuário logado
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se NÃO existir um id de usuário na sessão, bloqueia o acesso
if (!isset($_SESSION['users_id'])) {
    // Redireciona o usuário para a página de login para que ele se identifique
    header("Location: login.html"); 
    exit();
}

// Se o código chegar até aqui, significa que o usuário está logado!
// O $users_id agora será 100% dinâmico baseado em quem fez o login.
$users_id = $_SESSION['users_id'];

// 2. Verifica se o formulário foi enviado via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 3. Pega os dados digitados na interface
    $tickets_type = $_POST['tickets_type'] ?? null;
    $description = $_POST['description'] ?? null;
    
    // Regras automáticas do sistema para um novo chamado:
    // Define o fuso horário para o de Brasília
    date_default_timezone_set('America/Sao_Paulo');
    // Agora a função date() vai pegar o horário correto de Brasília
    $opening_date = date('Y-m-d H:i:s');
    $status_id = 1;                      // '1' significa "Aberto"
    $tech_support_id = null;             // Começa sem técnico
    $image_path = null;                  // Valor padrão se não houver imagem

    // --- LOGICA DE UPLOAD DA imageM ---
    // Verifica se o arquivo foi enviado e se não há erros de upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = $_FILES['image']['name'];
        
        // Extrai e normaliza a extensão do arquivo
        $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Array de extensões permitidas para maior segurança
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($extension, $allowed_extensions)) {
            // Cria a pasta 'uploads' automaticamente caso ela não exista
            if (!is_dir('uploads')) {
                mkdir('uploads', 0755, true);
            }

            // Gera um name único para evitar que arquivos com o mesmo name se sobrescrevam
            $new_name = uniqid('tickets_', true) . '.' . $extension;
            
            // Define o caminho final onde o arquivo físico será salvo
            $destination_directory = 'uploads/' . $new_name;

            // Move o arquivo da pasta temporária para a pasta do servidor
            if (move_uploaded_file($file_tmp, $destination_directory)) {
                $image_path = $destination_directory; // Salva o caminho para guardar no banco
            }
        } else {
            echo "Aviso: Tipo de arquivo não permitido. O chamado será enviado sem imagem.<br>";
        }
    }
    // ----------------------------------

    try {
        // 4. Prepara a query SQL
        $sql = "INSERT INTO tickets (tickets_type, openningdate, users_id, status_id, description, tech_support_id, image) 
                VALUES (:tickets_type, :opening_date, :users_id, :status_id, :description, :tech_support_id, :image)";
        
        $stmt = $pdo->prepare($sql);

        // 5. Vincula os valores de forma segura aos parâmetros da query
        $stmt->bindValue(':tickets_type', $tickets_type);
        $stmt->bindValue(':opening_date', $opening_date);

        // Garante que os ids entrem como números inteiros
        $stmt->bindValue(':users_id', $users_id, PDO::PARAM_INT);
        $stmt->bindValue(':status_id', $status_id, PDO::PARAM_INT);
        
        $stmt->bindValue(':description', $description);

        // Trata o técnico como nulo no início
        $stmt->bindValue(':tech_support_id', $tech_support_id, PDO::PARAM_NULL);

        // Se houver imagem, salva o caminho. Se não houver, salva NULL no banco.
        if ($image_path !== null) {
            $stmt->bindValue(':image', $image_path);
        } else {
            $stmt->bindValue(':image', null, PDO::PARAM_NULL);
        }

        // 6. Executa a gravação no banco de dados
        if ($stmt->execute()) {
            // Redireciona o usuário em caso de sucesso
            header("Location: meus-chamados.php?sucesso=1");
            exit();
        }
        
    } catch (PDOException $e) {
        echo "Erro ao salvar o chamado: " . $e->getMessage();
    }
}
?>