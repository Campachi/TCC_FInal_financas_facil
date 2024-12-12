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


// Capturar dados do formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['username'];
    $senha = $_POST['password'];

    if (!empty($email) && !empty($senha)) {
        // Preparar a consulta para buscar o usuário de forma segura
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verificar a senha
            if (password_verify($senha, $user['senha'])) {
                // Iniciar sessão e salvar ID do usuário na sessão
                session_start();
                $_SESSION['user_id'] = $user['id'];

                // Redirecionar para uma página protegida
                header("Location: ../html/pagina_inicial.html");
                exit();
            } else {
                echo "Senha incorreta.";
            }
        } else {
            echo "Usuário não encontrado.";
        }

        $stmt->close();
    } else {
        echo "Por favor, preencha todos os campos.";
    }
}

$conn->close();
?>
