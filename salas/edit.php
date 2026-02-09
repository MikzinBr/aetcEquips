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

if ($_SESSION['usuario_tipo'] != 'Direção') {
  header('Location: index.php?erro=Você não tem permissão para editar uma sala');
  exit;
}

$conn = open_database();

if (!isset($_GET['sala_id'])) {
  header("Location: index.php");
  exit;
}

$id = intval($_GET['sala_id']);

$stmt = $conn->prepare("SELECT * FROM salas WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
  header("Location: index.php");
  exit;
}

$sala = $result->fetch_assoc();

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
<div class="container mt-4">

  <h3>Editar Sala</h3>

  <?php if (isset($erro)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
  <?php endif; ?>

  <form method="POST" class="mt-3">

    <div class="mb-3">
      <label>Numero da sala</label>
      <input type="number" name="numero_sala" class="form-control"
        min="1" value="<?= $sala['numero_sala'] ?>">
    </div>

    <div class="mb-3">
      <label>Descrição</label>
      <textarea name="descricao" class="form-control"><?= htmlspecialchars($sala['descricao']) ?></textarea>
    </div>

    <button class="btn btn-success">Salvar Alterações</button>
    <a href="index.php" class="btn btn-danger">Cancelar</a>

  </form>

</div>

</body>

</html>
