<?php
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



// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capturar dados do formulário
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $email = isset($_POST['username']) ? $_POST['username'] : '';
    $cpf = isset($_POST['cpf']) ? $_POST['cpf'] : '';
    $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirm-password']) ? $_POST['confirm-password'] : '';

    // Verificar se todos os campos obrigatórios estão preenchidos
    if (empty($name) || empty($email) || empty($cpf) || empty($phone) || empty($password) || empty($confirmPassword)) {
        die("Por favor, preencha todos os campos.");
    }

    // Verificar se as senhas coincidem
    if ($password !== $confirmPassword) {
        die("As senhas não coincidem.");
    }

    // Criar o hash da senha apenas após a verificação
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Iniciar a sessão para obter o ID do usuário
    session_start();
    $userId = $_SESSION['user_id'];

    // Preparar a atualização dos dados no banco de dados
    $sql = $conn->prepare("UPDATE usuarios SET nome=?, email=?, cpf=?, telefone=?, senha=? WHERE id=?");
    $sql->bind_param("sssssi", $name, $email, $cpf, $phone, $passwordHash, $userId);

    if ($sql->execute()) {
        echo "Dados alterados com sucesso.";
        header("Location: ../html/perfil.html"); // Redirecionar para a página de perfil
    } else {
        echo "Erro ao alterar os dados: " . $sql->error;
    }

    $sql->close();
}

$conn->close();
?>
