<?php
require_once '../config.php';
require_once DBAPI;

$page_title = 'Criar Sala';
$page_subtitle = 'Adicionar nova sala';

require_once HEADER_TEMPLATE;
require_once NAVBAR_TEMPLATE;

/* PROTEÇÃO */
if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../index.php");
  exit;
}

if ($_SESSION['usuario_tipo'] != 'Direção') {
  header('Location: index.php?erro=Você não tem permissão para adicionar uma sala');
  exit;
}

/* PROCESSAMENTO */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $conn = open_database();

  $numero = trim($_POST['numero_sala']);
  $desc   = trim($_POST['descricao'] ?? '');
  $equipamento_ids = $_POST['equipamento_ids'] ?? [];

  /* Inserir sala */
  $stmt = $conn->prepare(
    "INSERT INTO salas (numero_sala, descricao) VALUES (?, ?)"
  );
  $stmt->bind_param("ss", $numero, $desc);
  $stmt->execute();

  $sala_id = $conn->insert_id;

  /* Associar equipamentos selecionados */
  if (!empty($equipamento_ids)) {

    $ids = array_map('intval', $equipamento_ids);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $sql = "
      UPDATE equipamentos
      SET sala_id = ?
      WHERE id IN ($placeholders)
        AND sala_id IS NULL
    ";

    $stmt = $conn->prepare($sql);

    $types = 'i' . str_repeat('i', count($ids));
    $params = array_merge([$sala_id], $ids);

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
  }

  header("Location: index.php?msg=Sala adicionada com sucesso");
  exit;
}

/* BUSCAR EQUIPAMENTOS DISPONÍVEIS */
$conn = open_database();
$equipamentos = $conn->query("
  SELECT id, nome
  FROM equipamentos
  WHERE sala_id IS NULL
  ORDER BY nome
");
?>

<div class="container mt-4">

  <h3>Cadastrar Sala</h3>

  <form method="POST" class="mt-3">

    <div class="mb-3">
      <label>Número da Sala</label>
      <input type="text" name="numero_sala" class="form-control" required>
    </div>

    <div class="mb-3">
      <label>Descrição</label>
      <textarea name="descricao" class="form-control"></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Equipamentos iniciais (opcional)</label>

      <select name="equipamento_ids[]" class="form-select" multiple size="8">
        <?php while ($e = $equipamentos->fetch_assoc()): ?>
          <option value="<?= $e['id'] ?>">
            <?= htmlspecialchars($e['nome']) ?>
          </option>
        <?php endwhile; ?>
      </select>

      <small class="text-muted">
        Segure Ctrl (Windows) ou Cmd (Mac) para selecionar vários
      </small>
    </div>

    <button class="btn btn-success">Salvar</button>
    <a href="index.php" class="btn btn-danger">Cancelar</a>

  </form>

</div>

<?php require_once FOOTER_TEMPLATE; ?>
