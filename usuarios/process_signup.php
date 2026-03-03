<?php
require_once '../config.php';
require_once DBAPI;
session_start();

$conn = open_database();

$senha_confirmacao = $_POST['senha_confirmacao'];
$senha_direcao = $conn->query("SELECT senha FROM usuarios WHERE id = " . $_SESSION['usuario_id'])->fetch_array()[0];

if (!password_verify($senha_confirmacao, $senha_direcao)) {
  header("location: index.php?erro=Senha de confirmação incorreta");
  exit;
}

$nome      = trim($_POST['nome'] ?? '');
$email     = trim($_POST['email'] ?? '');
$senha     = $_POST['senha'] ?? '';
$confirmar = $_POST['confirmar_senha'] ?? '';
$tipo      = $_POST['tipo'] ?? '';

if (!$nome || !$email || !$senha || !$confirmar || !$tipo) {
  header("Location: signup.php?erro=Preencha todos os campos.");
  exit;
}

if ($senha !== $confirmar) {
  header("Location: signup.php?erro=As senhas não coincidem.");
  exit;
}

$stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
  header("Location: signup.php?erro=Este email já está cadastrado.");
  exit;
}

$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

$stmt = $conn->prepare("
    INSERT INTO usuarios (nome, email, senha, tipo)
    VALUES (?, ?, ?, ?)
");
$stmt->bind_param("ssss", $nome, $email, $senha_hash, $tipo);
$stmt->execute();

header("Location: index.php");
exit;

