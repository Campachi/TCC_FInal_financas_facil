<?php
session_start();

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
    // Sanitização das entradas
    $nome = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_EMAIL);
    $cpf = filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_STRING);
    $telefone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $senha = $_POST['password'];
    $confirmSenha = $_POST['confirm-password'];

    // Validações
    if (empty($nome) || empty($email) || empty($cpf) || empty($telefone) || empty($senha) || empty($confirmSenha)) {
        die("Por favor, preencha todos os campos.");
    }

    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Email inválido");
    }

    // Verificar se as senhas coincidem
    if ($senha !== $confirmSenha) {
        die("As senhas não coincidem.");
    }

    // Verificar se email já existe
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        die("Este email já está cadastrado.");
    }

    // Criar hash da senha
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    // Inserir dados
    $sql = $conn->prepare("INSERT INTO usuarios (nome, email, cpf, telefone, senha) VALUES (?, ?, ?, ?, ?)");
    $sql->bind_param("sssss", $nome, $email, $cpf, $telefone, $senhaHash);

    if ($sql->execute()) {
        $_SESSION['mensagem'] = "Cadastro realizado com sucesso.";
        header("Location: ../html/login.html");
        exit();
    } else {
        die("Erro ao cadastrar: " . $sql->error);
    }
}

$conn->close();
?>
