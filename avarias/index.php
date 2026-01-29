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
            <?= $a['resolvido'] ? 'Resolvido' : 'Pendente' ?>
          </td>
          <td>
            <?php if (!$a['resolvido']): ?>
              <?php if ($tipo !== 'Professor'): ?>
                <a href="resolver.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-success col col-5">
                  <i class="fas fa-check"></i><span class="d-none d-xl-inline"> Marcar como resolvida</span>
                </a>
                <?php if ($tipo == "Direção") : ?>
                  <a href="edit.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-primary col col-3">
                    <i class="fas fa-edit"></i><span class="d-none d-lg-inline"> Editar</span>
                  </a>
                <?php else : ?>
                  <a href="detalhes.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-primary col col-3">
                    <i class="fas fa-ellipsis-h"></i><span class="d-none d-xl-inline"> Detalhes</span>
                  </a>
                <?php endif; ?>
              <?php else : ?>
                <a href="detalhes.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-primary col col-3">
                  <i class="fas fa-ellipsis-h"></i><span class="d-none d-xl-inline"> Detalhes</span>
                </a>
              <?php endif; ?>
            <?php else : ?>
              <a href="detalhes.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-primary col col-6">
                <i class="fas fa-ellipsis-h"></i><span class="d-none d-xl-inline"> Detalhes</span>
              </a>
            <?php endif; ?>
            <?php if ($tipo !== "Técnico") : ?>
              <a href="delete.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-danger col col-3">
                <i class="fas fa-trash"></i><span class="d-none d-xl-inline"> Remover</span>
              </a>
            <?php elseif ($a['usuario_id'] == $_SESSION['usuario_id']) : ?>
              <a href="delete.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-danger col col-3">
                <i class="fas fa-trash"></i><span class="d-none d-xl-inline"> Remover</span>
              </a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

</div>
</body>

</html>
