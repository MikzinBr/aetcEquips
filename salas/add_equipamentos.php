<?php
require_once '../config.php';
require_once DBAPI;

$page_title = 'Adicionar Equipamentos';
$page_subtitle = 'Vincular equipamentos a uma sala';

require_once HEADER_TEMPLATE;
require_once NAVBAR_TEMPLATE;

$conn = open_database();

if (!isset($_GET['sala_id'])) {
  header("Location: index.php");
  exit;
}

$sala_id = intval($_GET['sala_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $equipamento_ids = $_POST['equipamento_ids'] ?? [];

  if (!empty($equipamento_ids)) {

    $ids = array_map('intval', $equipamento_ids);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $sql = "UPDATE equipamentos SET sala_id = ? WHERE id IN (" . $placeholders . ")";
    $stmt = $conn->prepare($sql);

    $types = 'i' . str_repeat('i', count($ids));
    $params = array_merge([$sala_id], $ids);

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
  }

  header("Location: index.php?msg=Equipamento(s) adicionado(s) com sucesso");
  exit;
}

?>

<?php require_once FOOTER_TEMPLATE; ?>
