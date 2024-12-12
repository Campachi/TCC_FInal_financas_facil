<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../html/login.html");
    exit();
}


// Conectar ao banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "financas_facil";
$port = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Verificar conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}



// Obter dados do usuário logado
$user_id = $_SESSION['user_id'];
$sql = "SELECT nome, telefone, cpf, email, imagem_perfil, imagem_perfil_tipo FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($nome, $telefone, $cpf, $email, $imagem_perfil, $imagem_perfil_tipo);

if ($stmt->fetch()) {
    // Dados do usuário foram encontrados
    $stmt->close();
} else {
    // Se não encontrar, inicializa as variáveis
    $nome = '';
    $telefone = '';
    $cpf = '';
    $email = '';
    $imagem_perfil = null; // Se não houver imagem, define como nulo
    $imagem_perfil_tipo = '';
}

// Atualizar os dados se o formulário for enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $novo_nome = trim($_POST['name']);
    $novo_telefone = trim($_POST['phone']);
    $novo_cpf = trim($_POST['cpf']);
    $novo_email = trim($_POST['email']);

    // Inicializar variável para a imagem
    $novo_imagem_perfil = $imagem_perfil;
    $novo_imagem_perfil_tipo = $imagem_perfil_tipo;

    // Verificar se uma nova imagem foi enviada
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['avatar']['tmp_name'];
        $fileName = $_FILES['avatar']['name'];
        $fileSize = $_FILES['avatar']['size'];
        $fileType = mime_content_type($fileTmpPath);
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Extensões permitidas
        $allowedfileExtensions = array('jpg', 'jpeg', 'png', 'gif');

        if (in_array($fileExtension, $allowedfileExtensions)) {
            // Limite de tamanho (ex: 2MB)
            if ($fileSize <= 2 * 1024 * 1024) {
                // Verificar se o tipo MIME corresponde à extensão
                $allowedMimeTypes = array('image/jpeg', 'image/png', 'image/gif');
                if (in_array($fileType, $allowedMimeTypes)) {
                    // Ler o conteúdo do arquivo
                    $imagem_dados = file_get_contents($fileTmpPath);
                    // Atualizar as variáveis com a nova imagem
                    $novo_imagem_perfil = $imagem_dados;
                    $novo_imagem_perfil_tipo = $fileType;
                } else {
                    $message = "Tipo de arquivo não corresponde à extensão.";
                }
            } else {
                $message = "O tamanho do arquivo excede o limite de 2MB.";
            }
        } else {
            $message = "Tipo de arquivo não permitido. Apenas JPG, JPEG, PNG e GIF são aceitos.";
        }
    }

    // Atualizar os dados no banco de dados
    $sql_update = "UPDATE usuarios SET nome = ?, telefone = ?, cpf = ?, email = ?, imagem_perfil = ?, imagem_perfil_tipo = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ssssssi", $novo_nome, $novo_telefone, $novo_cpf, $novo_email, $novo_imagem_perfil, $novo_imagem_perfil_tipo, $user_id);

    if ($stmt_update->execute()) {
        $message = "Dados atualizados com sucesso.";
        // Atualizar as variáveis para refletir as mudanças
        $nome = $novo_nome;
        $telefone = $novo_telefone;
        $cpf = $novo_cpf;
        $email = $novo_email;
        $imagem_perfil = $novo_imagem_perfil;
        $imagem_perfil_tipo = $novo_imagem_perfil_tipo;
    } else {
        $message = "Erro ao atualizar os dados: " . $stmt_update->error;
    }
    $stmt_update->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil</title>
    <link rel="stylesheet" href="../css/perfil.css">
    <style>
        .highlight {
            background-color: yellow; /* Ou qualquer cor que você prefira */
            font-weight: bold; /* Para destacar mais */
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <img class="logotip" src="../html/logotipo.png.png" alt="Logotipo">
            <ul class="nav-links">
                <li><a href="../html/pagina_inicial.html">Início</a></li>
                <li><a href="historico.php">Histórico</a></li>
                <li class="dropdown">
                    <a href="perfil.php" class="dropbtn">opções</a>
                    <div class="dropdown-content">
                        <a href="perfil.php">Perfil</a>
                        <a href="../html/configuraçao.html">Configurações</a>
                        <a href="logout.php">Sair</a>
                    </div>
                </li>
            </ul>
        </nav>
    </header>
    <main class="main-content">
        <h1>Perfil</h1>
        <div class="profile-container">
            <div class="profile-header">
                <?php if (!empty($imagem_perfil)) : ?>
                    <img class="profile-avatar" src="data:<?php echo $imagem_perfil_tipo; ?>;base64,<?php echo base64_encode($imagem_perfil); ?>" alt="Avatar do Usuário" id="profileAvatar">
                <?php else : ?>
                    <img class="profile-avatar" src="../images/default_avatar.png" alt="Avatar do Usuário" id="profileAvatar">
                <?php endif; ?>
                <div class="upload-avatar">
                    <label for="uploadAvatar">Usuário <?php echo "$nome"?></label>
                </div>
            </div>
            <form action="perfil.php" method="post" enctype="multipart/form-data">
                <h2 class="titulo_informa">Informações Pessoais</h2>
                <table class="profile-table">
                    <tr>
                        <th>Nome Completo:</th>
                        <td><?php echo htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                    <tr>
                        <th>Telefone:</th>
                        <td><?php echo htmlspecialchars($telefone, ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                    <tr>
                        <th>CPF:</th>
                        <td><?php echo htmlspecialchars($cpf, ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td><?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                </table>

                <h2 class="titulo_informa">Atualizar Informações</h2>
                <table class="profile-table">
                    <tr>
                        <th>Nome Completo:</th>
                        <td><input type="text" name="name" value="<?php echo htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); ?>" required></td>
                    </tr>
                    <tr>
                        <th>Telefone:</th>
                        <td><input type="text" name="phone" value="<?php echo htmlspecialchars($telefone, ENT_QUOTES, 'UTF-8'); ?>" required></td>
                    </tr>
                    <tr>
                        <th>CPF:</th>
                        <td><input type="text" name="cpf" value="<?php echo htmlspecialchars($cpf, ENT_QUOTES, 'UTF-8'); ?>" required></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td><input type="email" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required></td>
                    </tr>
                    <tr>
                        <th>Imagem de Perfil:</th>
                        <td><input type="file" name="avatar" accept="image/*"></td>
                    </tr>
                </table>
                <input type="submit" value="Salvar Alterações">
            </form>
            <?php if (isset($message)) : ?>
                <p><?php echo $message; ?></p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
