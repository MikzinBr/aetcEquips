<?php
require_once '../config.php';
require_once '../inc/database.php';

$conn = open_database();

if (!isset($_GET['id'])) {
  header("Location: index.php");
  exit;
}

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $stmt = $conn->prepare("SELECT * FROM equipamentos WHERE id = ?");
  $stmt->bind_param("i", $_POST['equipamento_id']);
  $stmt->execute();
  $equipamento = $stmt->get_result()->fetch_assoc();

  $e_antigo = $conn->query("SELECT equipamento_id FROM avarias WHERE id = " . $id)->fetch_assoc()['equipamento_id'];

  $descricao  = $_POST['descricao'];

  $stmt = $conn->prepare("UPDATE avarias SET equipamento_id = ?, descricao = ? WHERE id = ?");
  $stmt->bind_param("isi", $_POST['equipamento_id'], $descricao, $id);
  $stmt->execute();

  $stmt = $conn->prepare("UPDATE equipamentos SET status = 'avariado' WHERE id = ?");
  $stmt->bind_param("i", $_POST['equipamento_id']);
  $stmt->execute();

  if ($conn->query("SELECT id FROM avarias WHERE equipamento_id = " . $e_antigo)->num_rows == 0) {
    $stmt = $conn->prepare("UPDATE equipamentos SET status = 'ok' WHERE id = ?");
    $stmt->bind_param("i", $e_antigo);
    $stmt->execute();
  }

  header("Location: index.php?msg=Equipamento editado com sucesso");
  exit;
}
?>

<?php require_once FOOTER_TEMPLATE; ?>
