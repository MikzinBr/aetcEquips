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
  header('Location: index.php?erro=Você não tem permissão para editar um equipamento');
  exit;
}

$conn = open_database();

if (!isset($_GET['id'])) {
  header("Location: index.php");
  exit;
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM equipamentos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
  header("Location: index.php");
  exit;
}

$equipamento = $result->fetch_assoc();

$salas = $conn->query("SELECT id, numero_sala FROM salas");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $nome       = $_POST['nome'];
  $quantidade = intval($_POST['quantidade']);
  $descricao  = $_POST['descricao'];
  $status     = $_POST['status'];
  $sala_id    = $_POST['sala_id'] ?: null;
  $cod_equip  = $_POST['codigo'];

  $stmt = $conn->prepare(
    "UPDATE equipamentos
             SET nome = ?, sala_id = ?, quantidade = ?, custo_unitario = 0, descricao = ?, status = ?, codigo = ?
             WHERE id = ?"
  );

  $stmt->bind_param(
    "siisssi",
    $nome,
    $sala_id,
    $quantidade,
    $descricao,
    $status,
    $cod_equip,
    $id
  );

  $stmt->execute();

  header("Location: index.php?msg=Equipamento editado com sucesso");
  exit;
}
?>
<div class="container mt-4">

  <h3>Editar Equipamento</h3>

  <?php if (isset($erro)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
  <?php endif; ?>

  <form method="POST" class="mt-3">

    <div class="mb-3">
      <label>Nome</label>
      <input type="text" name="nome" class="form-control"
        value="<?= htmlspecialchars($equipamento['nome']) ?>" required>
    </div>

    <div class="mb-3">
      <label>Sala</label>
      <select name="sala_id" class="form-select">
        <option value="">Sem sala</option>
        <?php while ($s = $salas->fetch_assoc()): ?>
          <option value="<?= $s['id'] ?>"
            <?= $equipamento['sala_id'] == $s['id'] ? 'selected' : '' ?>>
            Sala <?= htmlspecialchars($s['numero_sala']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="mb-3">
      <label>Quantidade</label>
      <input type="number" name="quantidade" class="form-control"
        min="1" value="<?= $equipamento['quantidade'] ?>">
    </div>

    <div class="mb-3">
      <label>Código</label>
      <input type="text" name="codigo" class="form-control"
        value="<?= $equipamento['codigo'] ?>"
        required>
    </div>

    <div class="mb-3">
      <label>Status</label>
      <select name="status" class="form-select">
        <option value="ok" <?= $equipamento['status'] == 'ok' ? 'selected' : '' ?>>OK</option>
        <option value="avariado" <?= $equipamento['status'] == 'avariado' ? 'selected' : '' ?>>Avariado</option>
      </select>
    </div>

    <div class="mb-3">
      <label>Descrição</label>
      <textarea name="descricao" class="form-control"><?= htmlspecialchars($equipamento['descricao']) ?></textarea>
    </div>

    <button class="btn btn-success" onclick="return confirm('Deseja realmente salvar as alterações?')">Salvar Alterações</button>
    <a href="index.php" class="btn btn-danger">Cancelar</a>

  </form>

</div>

</body>

</html>
