<?php
// Inicia a sessão para verificar se o usuário está logado
session_start();

// Verifica se o usuário está logado. Caso contrário, redireciona para a página de login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../html/login.html");
    exit(); // Encerra o script
}

// Dados de conexão com o banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "financas_facil";
$port = 3306;

// Conexão com o banco de dados
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Verifica se a conexão foi bem-sucedida
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error); // Encerra o script em caso de erro
}

// Recupera o ID do usuário da sessão
$user_id = $_SESSION['user_id'];

// Verifica se o ID do usuário existe
if (!$user_id) {
    die("Erro: ID do usuário não encontrado na sessão."); // Encerra o script caso não encontre o ID
}

// Inicia a transação no banco de dados (para garantir que todas as operações sejam feitas corretamente)
$conn->begin_transaction();

try {
    // Deleta as entradas relacionadas ao usuário nas tabelas de cálculos financeiros
    $sql_calculations = "DELETE FROM finance_calculations WHERE user_id = ?";
    $stmt_calculations = $conn->prepare($sql_calculations);
    $stmt_calculations->bind_param("i", $user_id); // Liga o parâmetro do usuário ao SQL
    $stmt_calculations->execute(); // Executa a consulta
    $stmt_calculations->close(); // Fecha a consulta

    // Deleta as contas relacionadas ao usuário
    $sql_accounts = "DELETE FROM accounts WHERE user_id = ?";
    $stmt_accounts = $conn->prepare($sql_accounts);
    $stmt_accounts->bind_param("i", $user_id); // Liga o parâmetro do usuário ao SQL
    $stmt_accounts->execute(); // Executa a consulta
    $stmt_accounts->close(); // Fecha a consulta

    // Deleta o usuário da tabela 'usuarios'
    $sql_user = "DELETE FROM usuarios WHERE id = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("i", $user_id); // Liga o parâmetro do usuário ao SQL
    $stmt_user->execute(); // Executa a consulta
    $stmt_user->close(); // Fecha a consulta

    // Se tudo correr bem, confirma a transação
    $conn->commit();

    // Destrói a sessão do usuário e redireciona para a página de login
    session_destroy();
    header("Location: ../html/login.html");
    exit(); // Encerra o script

} catch (Exception $e) {
    // Caso ocorra um erro, reverte a transação para manter a integridade dos dados
    $conn->rollback();
    echo "Erro ao excluir o usuário: " . $e->getMessage(); // Exibe a mensagem de erro
}

// Fecha a conexão com o banco de dados
$conn->close();
?>
