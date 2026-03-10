<?php
require_once '../config.php';
require_once '../inc/helpers.php';
require_once HEADER_TEMPLATE;
require_once NAVBAR_TEMPLATE;
require_once DBAPI;

$conn = open_database();

if (!isset($_GET['id'])) {
  header("Location: index.php");
  exit;
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM equipamentos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

$equipamento = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $nome       = $_POST['nome'];
  $descricao  = $_POST['descricao'];
  $status     = $_POST['status'];
  $sala_id    = $_POST['sala_id'] ?: null;
  $cod_equip  = $_POST['codigo'];

  $stmt = $conn->prepare(
    "UPDATE equipamentos
             SET nome = ?, sala_id = ?, quantidade = ?, custo_unitario = 0, descricao = ?, status = ?, codigo = ?
             WHERE id = ?"
  );

  $stmt->bind_param(
    "siisssi",
    $nome,
    $sala_id,
    1,
    $descricao,
    $status,
    $cod_equip,
    $id
  );

  $stmt->execute();

  header("Location: index.php?msg=Equipamento editado com sucesso");
  exit;
}
?>

<?php require_once FOOTER_TEMPLATE; ?>
