<?php
require_once '../config.php';
require_once '../inc/helpers.php';
require_once DBAPI;
require_once HEADER_TEMPLATE;
require_once NAVBAR_TEMPLATE;

if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../login.php");
  exit;
}

$erro = $_GET["erro"] ?? "";
$msg = $_GET["msg"] ?? "";

$conn = open_database();

$sql = "
  SELECT e.*, s.numero_sala
  FROM equipamentos e
  LEFT JOIN salas s ON e.sala_id = s.id
";

if (!empty($_GET['sala_id'])) {
  $sala_id = intval($_GET['sala_id']);
  $sala_num = $conn->query("SELECT numero_sala FROM salas WHERE id = " . $sala_id)->fetch_assoc();
  $sql .= " WHERE e.sala_id = ?";
}

$sql .= " ORDER BY e.id";

$stmt = $conn->prepare($sql);

if (!empty($sala_id)) {
  $stmt->bind_param("i", $sala_id);
}

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
      <?php if (isset($sala_id)) : ?>
        <h3 class="col">Equipamentos <?= $sala_num["numero_sala"] ?></h3>

        <div class="col text-end">
          <a href="index.php?sala_id=<?= $sala_id ?>" class="col col-2 col-lg-1 btn btn-primary"><i class="fas fa-sync-alt"></i></a>
          <a href="add.php?sala_id=<?= $sala_id ?>" class="col col-2 col-lg-4 btn btn-success "><i class="fas fa-plus"></i><span class="d-none d-xl-inline"> Novo equipamento</span></a>
        </div>

      <?php else : ?>
        <h3 class="col">Equipamentos</h3>

        <div class="col text-end">
          <a href="index.php" class="col col-2 col-lg-1 btn btn-primary"><i class="fas fa-sync-alt"></i></a>
          <a href="add.php" class="col col-2 col-lg-4 btn btn-success "><i class="fas fa-plus"></i><span class="d-none d-xl-inline"> Novo equipamento</span></a>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <table class="table table-bordered table-hover">
    <thead class="table-dark">
      <tr>
        <th>Nome</th>
        <th>Sala</th>
        <th>Qtd</th>
        <th>Status</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($e = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($e['nome']) ?></td>
          <td><?= $e['numero_sala'] ?? '—' ?></td>
          <td><?= $e['quantidade'] ?></td>
          <td>
            <span class="badge bg-<?= $e['status'] == 'ok' ? 'success' : 'danger' ?>">
              <?= $e['status'] ?>
            </span>
          </td>
          <td>
            <a href="edit.php?id=<?= $e['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i> Editar</a>
            <a href="../avarias/reportar.php?id=<?= $e['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-exclamation"></i> Reportar avaria</a>
            <a href="delete.php?id=<?= $e['id'] ?>"
              class="btn btn-sm btn-danger"
              onclick="return confirm('Remover equipamento?')">
              <i class="fas fa-trash"></i> Remover
            </a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

</div>

</body>

</html>
