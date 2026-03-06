<?php
require_once '../config.php';
require_once DBAPI;

$page_title = 'Adicionar Equipamentos';
$page_subtitle = 'Vincular equipamentos a uma sala';

require_once HEADER_TEMPLATE;
require_once NAVBAR_TEMPLATE;

if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../index.php");
  exit;
}

if ($_SESSION["usuario_tipo"] !== "Direção") {
  header("Location: index.php?erro=Você não permissão para adicionar equipamentos à uma sala");
  exit;
}

$conn = open_database();

if (!isset($_GET['sala_id'])) {
  header("Location: index.php");
  exit;
}

$sala_id = intval($_GET['sala_id']);

$stmt = $conn->prepare("SELECT numero_sala FROM salas WHERE id = ?");
$stmt->bind_param("i", $sala_id);
$stmt->execute();
$sala = $stmt->get_result()->fetch_assoc();

$equipamentos = $conn->query("
  SELECT id, nome FROM equipamentos
  WHERE sala_id IS NULL
  ORDER BY nome
");

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
<div class="container mt-4">

  <h3>Adicionar Equipamento à Sala <?= htmlspecialchars($sala['numero_sala']) ?></h3>

  <form method="POST">

    <div class="mb-3">
      <label>Equipamentos disponíveis</label>
      <select name="equipamento_ids[]" class="form-select" multiple size="8" required>
        <?php while ($e = $equipamentos->fetch_assoc()): ?>
          <option value="<?= $e['id'] ?>">
            <?= htmlspecialchars($e['nome']) ?>
          </option>
        <?php endwhile; ?>
      </select>
      <small class="text-muted">
        Use Ctrl (Windows) ou Cmd (Mac) para selecionar vários
      </small>
    </div>

    <button class="btn btn-success">Adicionar</button>
    <a href="index.php" class="btn btn-danger">Cancelar</a>

  </form>

</div>

<?php require_once FOOTER_TEMPLATE; ?>
