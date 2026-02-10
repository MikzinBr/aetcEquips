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

$erro = $_GET['erro'] ?? '';
$msg = $_GET["msg"] ?? "";

$conn = open_database();
$result = $conn->query("SELECT * FROM salas ORDER BY numero_sala");
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
      <h3 class="col">Salas</h3>

      <div class="col text-end">
        <a href="index.php" class="col col-2 col-lg-1 btn btn-primary"><i class="fas fa-sync-alt"></i></a>
        <a href="add.php" class="col col-2 col-xl-3 btn btn-success "><i class="fas fa-plus"></i><span class="d-none d-xl-inline"> Criar Sala</span></a>
      </div>
    </div>
  </div>

  <table class="table table-bordered table-hover">
    <thead class="table-dark">
      <tr>
        <th>Número</th>
        <th>Descrição</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($sala = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($sala['numero_sala']) ?></td>
          <td><?= htmlspecialchars(limitText($sala['descricao'], 30)) ?></td>
          <td>
            <a href="add_equipamentos.php?sala_id=<?= $sala['id'] ?>" class="btn btn-sm btn-success">
              <i class="fas fa-plus"></i> Adicionar equipamentos
            </a>
            <a href="../equipamentos?sala_id=<?= $sala['id'] ?>" class="btn btn-sm btn-primary">
              <i class="fas fa-eye"></i> Equipamentos
            </a>
            <a href="edit.php?sala_id=<?= $sala['id'] ?>" class="btn btn-sm btn-warning">
              <i class="fas fa-edit"></i>
            </a>
            <a href="delete.php?id=<?= $sala['id'] ?>"
              class="btn btn-sm btn-danger"
              onclick="return confirm('Remover esta sala?')">
              <i class="fas fa-trash"></i>
            </a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

</div>

</body>

</html>
