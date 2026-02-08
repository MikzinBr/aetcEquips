<?php
require_once '../config.php';
require_once DBAPI;
require_once HEADER_TEMPLATE;
require_once NAVBAR_TEMPLATE;

if (!isset($_SESSION["usuario_id"])) {
  header("location: ../index.php");
  exit;
}

$conn = open_database();

$erro = $_GET["erro"] ?? "";
$msg = $_GET["msg"] ?? "";

$tipo = $_SESSION['usuario_tipo'];
$usuario_id = $_SESSION['usuario_id'];

$sql = "
  SELECT a.*, e.nome AS equipamento, u.nome AS usuario
  FROM avarias a
  JOIN equipamentos e ON a.equipamento_id = e.id
  JOIN usuarios u ON a.usuario_id = u.id
";

if ($tipo === 'Professor') {
  $sql .= " WHERE a.usuario_id = $usuario_id";
}

$sql .= " ORDER BY a.data_registro DESC";

$result = $conn->query($sql);
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
      <h3 class="col">Avarias</h3>

      <div class="col text-end">
        <a href="index.php" class="col col-2 col-lg-1 btn btn-primary"><i class="fas fa-sync-alt"></i></a>
        <a href="reportar.php" class="col col-2 col-xl-4 btn btn-warning "><i class="fas fa-plus"></i><span class="d-none d-xl-inline"> Reportar avaria</span></a>
      </div>
    </div>
  </div>

  <table class="table table-bordered">
    <thead class="table-dark">
      <tr>
        <th>Equipamento</th>
        <th>Reportado por</th>
        <th>Data</th>
        <th>Status</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>

      <?php while ($a = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($a['equipamento']) ?></td>
          <td><?= htmlspecialchars($a['usuario']) ?></td>
          <td><?= $a['data_registro'] ?></td>
          <td>
            <label href="resolver.php?id=<?= $a["id"] ?>" class="p-1 text-light rounded bg-<?= $a['resolvido'] ? 'success' : 'danger' ?>"><?= $a['resolvido'] ? 'Resolvido' : 'Pendente' ?></label>

            <?php if ($_SESSION['usuario_tipo'] == "Professor") : ?>
              <a href="#" class="btn text-tertiary"><span class="far fa-<?= $a['resolvido'] ? 'check-square' : 'square' ?>" </span></a>
            <?php else : ?>
              <a href="<?= $a['resolvido'] ? 'reavariar' : 'resolver' ?>.php?id=<?= $a['id'] ?>" class="btn text-<?= $a['resolvido'] ? 'success' : 'danger' ?>"><span class="far fa-<?= $a['resolvido'] ? 'check-square' : 'square' ?>" </span></a>
            <?php endif; ?>

          </td>
          <td>
            <a href="detalhes.php?id=<?= $a['id'] ?>" class="col col-2 btn btn-primary"><span class="fa fa-info"></span></a>
            <a href="edit.php?id=<?= $a['id'] ?>" class="col col-2 btn btn-<?= $_SESSION['usuario_tipo'] == "Direção" || $a['usuario_id'] == $_SESSION["usuario_id"] ? "warning" : "secondary disabled" ?>"><span class="fa fa-edit"></span></a>
            <a href="delete.php?id=<?= $a['id'] ?>" class="col col-2 btn btn-<?= $_SESSION['usuario_tipo'] == "Direção" || $a['usuario_id'] == $_SESSION["usuario_id"] ? "danger" : "secondary disabled" ?>"><span class="fa fa-trash"></span></a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

</div>
</body>

</html>
