<?php
require_once '../config.php';
require_once DBAPI;
require_once HEADER_TEMPLATE;
require_once NAVBAR_TEMPLATE;

if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../index.php");
  exit;
}

if ($_SESSION['usuario_tipo'] != "Direção") {
  header("Location: ../index.php");
  exit;
}

$erro = $_GET["erro"] ?? "";
$msg = $_GET["msg"] ?? "";

$conn = open_database();

$sql = "SELECT * FROM usuarios";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container mt-4">

  <?php if ($erro) : ?>
    <div class="alert alert-danger">
      <?= htmlspecialchars($erro) ?>
    </div>
  <?php endif; ?>

  <?php if ($msg) : ?>
    <div class="alert alert-success">
      <?= htmlspecialchars($msg) ?>
    </div>
  <?php endif; ?>

  <div class="container mb-3">
    <div class="row justify-content-between">
      <h3 class="col">Usuarios</h3>

      <div class="col text-end">
        <a href="index.php" class="col col-2 col-lg-1 btn btn-primary"><i class="fas fa-sync-alt"></i></a>
        <a href="signup.php" class="col col-2 col-lg-4 btn btn-success "><i class="fas fa-plus"></i><span class="d-none d-xl-inline"> Novo usuario</span></a>
      </div>
    </div>
  </div>

  <table class="table table-bordered table-hover">
    <thead class="table-dark">
      <tr>
        <th>Nome</th>
        <th>Email</th>
        <th>Cargo</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($u = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $u['nome'] ?></td>
          <td><?= $u['email'] ?></td>
          <td><?= $u['tipo'] ?></td>
          <td>
            <a href="edit.php?id=<?= $u['id'] ?>" class="col col-xl-2 btn btn-primary"><i class="fa fa-edit"></i></a>
            <a href="delete.php?id=<?= $u['id'] ?>" class="col col-xl-2 btn btn-danger" onclick="return confirm('Deseja realmente remover o usuario?')"><i class="fa fa-trash"></i></a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

</div>

</body>

</html>
