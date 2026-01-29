<?php
require_once '../config.php';
require_once '../inc/helpers.php';
require_once HEADER_TEMPLATE;
require_once NAVBAR_TEMPLATE;
require_once DBAPI;

if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../login.php");
  exit;
}

if ($_SESSION['usuario_tipo'] !== "Direção") {
  header("location: ../login.php");
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

$equipamentos = $conn->query("SELECT id, nome FROM equipamentos");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $stmt = $conn->prepare("SELECT * FROM equipamentos WHERE id = ?");
  $stmt->bind_param("i", $_POST['equipamento_id']);
  $stmt->execute();
  $equipamento = $stmt->get_result()->fetch_assoc();

  $descricao  = $_POST['descricao'];

  $stmt = $conn->prepare("UPDATE avarias SET equipamento_id = ?, descricao = ? WHERE id = ?");
  $stmt->bind_param("isi", $equipamento['id'], $descricao, $avaria['id']);
  $stmt->execute();

  header("Location: index.php?msg=Equipamento editado com sucesso");
  exit;
}
?>
<div class="container mt-4">

  <h3>Editar avaria</h3>

  <?php if (isset($erro)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
  <?php endif; ?>

  <form method="POST" class="mt-3">

    <div class="mb-3">
      <label>Equipamento</label>
      <select name="equipamento_id" class="form-select">
        <?php while ($e = $equipamentos->fetch_assoc()): ?>
          <option value="<?= $e['id'] ?>"
            <?= $avaria['equipamento_id'] == $e['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($e['nome']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="mb-3">
      <label>Descrição</label>
      <textarea name="descricao" class="form-control"><?= htmlspecialchars($avaria['descricao']) ?></textarea>
    </div>

    <button type="submit" class="btn btn-success">Salvar</button>
    <a href="index.php" class="btn btn-danger">cancelar</a>
  </form>

</div>
</body>

</html>
