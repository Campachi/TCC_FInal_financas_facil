<?php
// get_image.php

// Verificar se o parâmetro 'id' foi passado
if (!isset($_GET['id'])) {
    http_response_code(400);
    echo "ID do usuário não fornecido.";
    exit();
}

$user_id = intval($_GET['id']);

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

// Buscar a imagem e o tipo MIME do usuário
$sql = "SELECT imagem_perfil, imagem_perfil_tipo FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo "Erro na preparação da consulta.";
    exit();
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($imagem_perfil, $imagem_perfil_tipo);
$stmt->fetch();
$stmt->close();
$conn->close();

// Verificar se a imagem existe
if (empty($imagem_perfil)) {
    // Exibir uma imagem padrão
    header("Content-Type: image/png");
    readfile("default_avatar.png"); // Certifique-se de ter uma imagem padrão
    exit();
}

// Definir os cabeçalhos apropriados
header("Content-Type: " . $imagem_perfil_tipo);
header("Content-Length: " . strlen($imagem_perfil));

// Exibir a imagem
echo $imagem_perfil;
?>
