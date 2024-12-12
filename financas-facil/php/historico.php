<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
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

// Pegar o ID do usuário logado
$user_id = $_SESSION['user_id'];

// Inicializar a variável de pesquisa
$searchTerm = '';

// Verificar se o formulário de pesquisa foi enviado
if (isset($_POST['search'])) {
    $searchTerm = $_POST['search'];
}

// Preparar a consulta para obter o histórico de cálculos do usuário
$sql = "SELECT * FROM finance_calculations WHERE user_id = ?";

// Adicionar filtro de pesquisa se houver um termo de pesquisa
if (!empty($searchTerm)) {
    $sql .= " AND (data LIKE ? OR salario LIKE ? OR outras_despesas LIKE ?)";
}

$sql .= " ORDER BY data DESC";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Erro ao preparar a consulta: " . $conn->error);
}

// Bind dos parâmetros
if (!empty($searchTerm)) {
    $searchTermLike = "%$searchTerm%";
    $stmt->bind_param("isss", $user_id, $searchTermLike, $searchTermLike, $searchTermLike);
} else {
    $stmt->bind_param("i", $user_id);
}

// Executar a consulta e verificar o resultado
if ($stmt->execute()) {
    $result = $stmt->get_result();
} else {
    $result = null;
    echo "<p>Erro ao executar a consulta: " . $stmt->error . "</p>";
}

// Fechar a consulta
$stmt->close();
$conn->close();

// Função para destacar o termo pesquisado
function highlightSearchTerm($text, $term) {
    if (empty($term)) {
        return htmlspecialchars($text);
    }
    return preg_replace(
        '/(' . preg_quote($term, '/') . ')/i',
        '<span class="highlight">$1</span>',
        htmlspecialchars($text)
    );
}

// Função para dar um conselho baseado no resultado final
function giveAdvice($result) {
    if ($result > 0) {
        return "Parabéns! Você conseguirá pagar as contas!.";
    } elseif ($result < 0) {
        return "Atenção! Você tem um saldo negativo. Verifique suas despesas e ajuste seu orçamento para evitar dívidas.";
    } else {
        return "Você está no equilíbrio. Continue monitorando suas finanças e ajustando seu orçamento para alcançar seus objetivos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico - Finanças Fácil</title>
    <link rel="stylesheet" href="../css/historico.css">
    <style>
        .highlight {
            background-color: yellow;
            font-weight: bold;
        }
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <img class="logotip" src="../html/logotipo.png.png" alt="Logotipo">

            <div class="search-container">
                <form method="POST" action="">
                    <input type="text" name="search" placeholder="Buscar..." value="<?= htmlspecialchars($searchTerm) ?>">
                    <button type="submit">Buscar</button>
                </form>
            </div>
        </nav>
    </header>

    <main class="historico-page">
        <fieldset>
            <legend>Histórico de Cálculos</legend>
            <?php if ($result && $result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Salário</th>
                            <th>Per Capita</th>
                            <th>Água</th>
                            <th>Energia</th>
                            <th>Mercado</th>
                            <th>Prestações Veiculares</th>
                            <th>Gás</th>
                            <th>Condomínio</th>
                            <th>Internet</th>
                            <th>Dívidas</th>
                            <th>Outras Despesas</th>
                            <th>Saldo Final</th>
                            <th>Conselho</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php
                                // Calcular o saldo final
                                $finalBalance = $row['salario'] - ($row['outras_despesas'] + $row['agua'] + $row['energia'] + $row['mercado'] + $row['prestacoes_veiculares'] + $row['gas'] + $row['condominio'] + $row['internet'] + $row['dividas']);
                                $advice = giveAdvice($finalBalance);
                            ?>
                            <tr>
                                <td><?= highlightSearchTerm(date('d/m/Y', strtotime($row['data'])), $searchTerm) ?></td>
                                <td>R$ <?= highlightSearchTerm(number_format($row['salario'], 2, ',', '.'), $searchTerm) ?></td>
                                <td><?= highlightSearchTerm(number_format($row['percapita'], 0, ',', '.'), $searchTerm) ?></td>
                                <td>R$ <?= highlightSearchTerm(number_format($row['agua'], 2, ',', '.'), $searchTerm) ?></td>
                                <td>R$ <?= highlightSearchTerm(number_format($row['energia'], 2, ',', '.'), $searchTerm) ?></td>
                                <td>R$ <?= highlightSearchTerm(number_format($row['mercado'], 2, ',', '.'), $searchTerm) ?></td>
                                <td>R$ <?= highlightSearchTerm(number_format($row['prestacoes_veiculares'], 2, ',', '.'), $searchTerm) ?></td>
                                <td>R$ <?= highlightSearchTerm(number_format($row['gas'], 2, ',', '.'), $searchTerm) ?></td>
                                <td>R$ <?= highlightSearchTerm(number_format($row['condominio'], 2, ',', '.'), $searchTerm) ?></td>
                                <td>R$ <?= highlightSearchTerm(number_format($row['internet'], 2, ',', '.'), $searchTerm) ?></td>
                                <td>R$ <?= highlightSearchTerm(number_format($row['dividas'], 2, ',', '.'), $searchTerm) ?></td>
                                <td>R$ <?= highlightSearchTerm(number_format($row['outras_despesas'], 2, ',', '.'), $searchTerm) ?></td>
                                <td>R$ <?= highlightSearchTerm(number_format($finalBalance, 2, ',', '.'), $searchTerm) ?></td>
                                <td><?= htmlspecialchars($advice) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Não há registros de cálculos financeiros ou ocorreu um erro.</p>
            <?php endif; ?>
        </fieldset>
        <a href="../html/pagina_inicial.html" class="button">Voltar ao Início</a>
    </main>

    <footer>
        <p>&copy; 2024 Finança Fácil. Todos os direitos reservados.</p>
    </footer>
</body>
</html>
