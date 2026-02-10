<?php
require_once '../config.php';
require_once DBAPI;
require_once HEADER_TEMPLATE;
require_once NAVBAR_TEMPLATE;

if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../login.php");
  exit;
}

$conn = open_database();

$id_equip = $_GET["id"] ?? "";

if ($id_equip) {
  $equipamentos = $conn->query("SELECT id, nome FROM equipamentos WHERE id = " . $id_equip);
} else {
  $equipamentos = $conn->query("SELECT id, nome FROM equipamentos ORDER BY nome");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $equipamento_id = intval($_POST['equipamento_id']);
  $descricao = trim($_POST['descricao']);
  $usuario_id = $_SESSION['usuario_id'];

  if ($descricao === '') {
    $erro = "Descreva o problema.";
  } else {

    $stmt = $conn->prepare(
      "INSERT INTO avarias (equipamento_id, usuario_id, descricao)
           VALUES (?, ?, ?)"
    );
    $stmt->bind_param("iis", $equipamento_id, $usuario_id, $descricao);
    $stmt->execute();

    $stmt2 = $conn->prepare("UPDATE equipamentos SET status = 'avariado' WHERE id = ?");
    $stmt2->bind_param("i", $equipamento_id);
    $stmt2->execute();

    header("Location: index.php?msg=Avaria reportada com sucesso");
    exit;
  }
}
?>
<div class="container mt-4">

  <h3>Reportar Avaria</h3>

  <?php if (isset($erro)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
  <?php endif; ?>

  <form method="POST">

    <div class="mb-3">
      <label>Equipamento</label>
      <select name="equipamento_id" class="form-select" required>
        <?php while ($e = $equipamentos->fetch_assoc()): ?>
          <option value="<?= $e['id'] ?>">
            <?= htmlspecialchars($e['nome']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="mb-3">
      <label>Descrição do Problema</label>
      <textarea name="descricao" class="form-control" required></textarea>
    </div>

    <button class="btn btn-warning">Reportar</button>
    <a href="index.php" class="btn btn-danger">Cancelar</a>

  </form>

</div>
</body>

</html>
