<?php
require_once '../config.php';
require_once '../inc/helpers.php';

$page_title = 'Detalhes da Avaria';
$page_subtitle = 'Informações completas';

require_once HEADER_TEMPLATE;
require_once NAVBAR_TEMPLATE;
require_once DBAPI;

if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../index.php");
  exit;
}

$conn = open_database();

if (!isset($_GET['id'])) {
  header("Location: index.php");
  exit;
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM avarias WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
  header("Location: index.php");
  exit;
}

$avaria = $result->fetch_assoc();

$stmt = $conn->prepare("SELECT id, nome FROM equipamentos WHERE id = ?");
$stmt->bind_param("i", $avaria['equipamento_id']);
$stmt->execute();
$result = $stmt->get_result();
$equipamento = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $equipamento_id = $_POST['equipamento_id'];
  $descricao      = $_POST['descricao'];
}
?>
<div class="container mt-4">

  <h3>Vizualizar avaria</h3>

  <div class="mb-3">
    <label>Equipamento</label>
    <input type="text" class="form-select" value="<?= $equipamento['nome'] ?>" disabled>
  </div>

  <div class="mb-3">
    <label>Descrição</label>
    <textarea name="descricao" class="form-control" disabled><?= htmlspecialchars($avaria['descricao']) ?></textarea>
  </div>

  <a href="index.php" class="btn btn-success">Sair</a>

</div>

<?php require_once FOOTER_TEMPLATE; ?>
