<?php
require_once '../config.php';
require_once '../inc/helpers.php';

$page_title = 'Editar Sala';
$page_subtitle = 'Atualizar dados da sala';

require_once HEADER_TEMPLATE;
require_once NAVBAR_TEMPLATE;
require_once DBAPI;

$conn = open_database();

if (!isset($_GET['sala_id'])) {
  header("Location: index.php");
  exit;
}

$id = intval($_GET['sala_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $descricao   = $_POST['descricao'];
  $numero_sala = $_POST['numero_sala'] ?: null;

  $stmt = $conn->prepare(
    "UPDATE salas
             SET descricao = ?, numero_sala = ?
             WHERE id = ?"
  );

  $stmt->bind_param(
    "sii",
    $descricao,
    $numero_sala,
    $id
  );

  $stmt->execute();

  header("Location: index.php?msg=Sala editada com sucesso");
  exit;
}
?>

<?php require_once FOOTER_TEMPLATE; ?>
