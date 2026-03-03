<?php
require_once '../config.php';
require_once HEADER_TEMPLATE;
require_once NAVBAR_TEMPLATE;
require_once DBAPI;

if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../index.php");
  exit;
}

if ($_SESSION['usuario_tipo'] != 'Direção') {
  header('Location: index.php?erro=Você não tem permissão para editar um usuario');
  exit;
}

$tipos = ["Direção", "Técnico", "Professor"];

$conn = open_database();

if (!isset($_GET['id'])) {
  header("Location: index.php");
  exit;
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
  header("Location: index.php");
  exit;
}

$usuario = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $nome   = $_POST['nome'];
  $email = $_POST['email'];
  $tipo = $_POST['tipo'];

  $stmt = $conn->prepare(
    "UPDATE usuarios
    SET nome = ?, email = ?, tipo = ?
    WHERE id = ?"
  );

  $stmt->bind_param(
    "sssi",
    $nome,
    $email,
    $tipo,
    $id
  );

  $stmt->execute();

  header("Location: index.php?msg=Usuario editado com sucesso");
  exit;
}
?>
<div class="container mt-4">

  <h3>Editar Usuario</h3>

  <?php if (isset($erro)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
  <?php endif; ?>

  <form method="POST" class="mt-3">

    <div class="mb-3">
      <label>Nome</label>
      <input type="text" name="nome" class="form-control"
        value="<?= $usuario['nome'] ?>">
    </div>

    <div class="mb-3">
      <label>Email</label>
      <input type="text" name="email" class="form-control"
        value="<?= $usuario['email'] ?>">
    </div>

    <div class="mb-3">
      <label>Tipo</label>
      <select name="tipo" class="form-select">
        <?php foreach ($tipos as $tipo) : ?>
          <option value="<?= $tipo ?>" <?= $usuario["tipo"] == $tipo ? "selected" : "" ?>><?= $tipo ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <button class="btn btn-success" onclick="return confirm('Deseja realmente salvar as alterações?')">Salvar Alterações</button>
    <a href="index.php" class="btn btn-danger">Cancelar</a>

  </form>

</div>

</body>

</html>
