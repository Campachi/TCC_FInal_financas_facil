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


// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $salario = isset($_POST['salario']) ? floatval(str_replace(',', '.', $_POST['salario'])) : 0;
    $outras_despesas = isset($_POST['outras_despesas']) ? floatval(str_replace(',', '.', $_POST['outras_despesas'])) : 0;
    $agua = isset($_POST['agua']) ? floatval(str_replace(',', '.', $_POST['agua'])) : 0;
    $energia = isset($_POST['energia']) ? floatval(str_replace(',', '.', $_POST['energia'])) : 0;
    $mercado = isset($_POST['mercado']) ? floatval(str_replace(',', '.', $_POST['mercado'])) : 0;
    $prestacoes_veiculares = isset($_POST['prestacoes_veiculares']) ? floatval(str_replace(',', '.', $_POST['prestacoes_veiculares'])) : 0;
    $gas = isset($_POST['gas']) ? floatval(str_replace(',', '.', $_POST['gas'])) : 0;
    $condominio = isset($_POST['condominio']) ? floatval(str_replace(',', '.', $_POST['condominio'])) : 0;
    $internet = isset($_POST['internet']) ? floatval(str_replace(',', '.', $_POST['internet'])) : 0;
    $dividas = isset($_POST['dividas']) ? floatval(str_replace(',', '.', $_POST['dividas'])) : 0;
    $percapita = isset($_POST['percapita']) ? intval($_POST['percapita']) : 1;
    $data = isset($_POST['data']) ? $_POST['data'] : date('Y-m-d');

    // Consulta SQL para inserir os dados no banco de dados
    $sql = "INSERT INTO finance_calculations (user_id, salario, outras_despesas, agua, energia, mercado, prestacoes_veiculares, gas, condominio, internet, dividas, percapita, data) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Preparar a consulta
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Erro ao preparar a consulta: " . $conn->error);
    }

    // Associar os parâmetros à consulta
    $stmt->bind_param("iddddddddddis", $user_id, $salario, $outras_despesas, $agua, $energia, $mercado, $prestacoes_veiculares, $gas, $condominio, $internet, $dividas, $percapita, $data);

    // Executar a consulta
    if ($stmt->execute()) {
        // Redirecionar para a página de histórico após a inserção bem-sucedida
        header("Location: historico.php");
        exit();
    } else {
        // Exibir mensagem de erro caso haja falha na execução
        echo "Erro ao registrar cálculo financeiro: " . $stmt->error;
    }

    // Fechar a consulta e a conexão
    $stmt->close();
    $conn->close();
}
?>
