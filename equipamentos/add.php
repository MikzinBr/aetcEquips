<?php
require_once '../config.php';
require_once DBAPI;
require_once HEADER_TEMPLATE;
require_once NAVBAR_TEMPLATE;

/* PROTEÇÃO */
if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../index.php");
  exit;
}

if ($_SESSION['usuario_tipo'] != 'Direção') {
  header('Location: index.php?erro=Você não tem permissão para adicionar um equipamento');
  exit;
}

$conn = open_database();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $nome       = trim($_POST['nome']);
  $codigo     = trim($_POST['codigo']);
  $quantidade = intval($_POST['quantidade']);
  $descricao  = trim($_POST['descricao'] ?? '');
  $sala_id    = !empty($_POST['sala_id']) ? intval($_POST['sala_id']) : null;

  try {

    $stmt = $conn->prepare(
      "INSERT INTO equipamentos
       (nome, codigo, sala_id, quantidade, custo_unitario, descricao)
       VALUES (?, ?, ?, 1, 0, ?)"
    );

    for ($i = 1; $i <= $quantidade; $i++) {

      $codigo_final = $codigo;

      if (!empty($codigo) && $quantidade > 1) {
        $codigo_final = $codigo . '-' . $i;
      }

      $nomeFinal = $nome;

      if (!empty($nome) && $quantidade > 1) {
        $nomeFinal = $nome . '-' . $i;
      }


      $stmt->bind_param(
        "ssis",
        $nomeFinal,
        $codigo_final,
        $sala_id,
        $descricao
      );

      $stmt->execute();
    }

    header("Location: index.php?msg=Equipamentos inseridos com sucesso");
    exit;
  } catch (mysqli_sql_exception $e) {

    if ($e->getCode() == 1062) {
      header("Location: add.php?erro=Já existe um equipamento com esse código");
      exit;
    }

    header("Location: add.php?erro=Erro ao inserir equipamentos");
    exit;
  }
}

if (!empty($_GET['sala_id'])) {
  $salas = $conn->query(
    "SELECT id, numero_sala FROM salas WHERE id = " . intval($_GET['sala_id'])
  );
} else {
  $salas = $conn->query(
    "SELECT id, numero_sala FROM salas ORDER BY numero_sala"
  );
}
?>

<div class="container mt-4">

  <h3>Novo Equipamento</h3>

  <?php if (!empty($_GET['erro'])): ?>
    <div class="alert alert-danger">
      <?= htmlspecialchars($_GET['erro']) ?>
    </div>
  <?php endif; ?>

  <form method="POST">

    <div class="mb-3">
      <label>Nome</label>
      <input type="text" name="nome" class="form-control" required>
    </div>

    <div class="mb-3">
      <label>Código</label>
      <input type="text" name="codigo" class="form-control">
    </div>

    <div class="mb-3">
      <label>Sala</label>
      <select name="sala_id" class="form-select">
        <?php if (empty($_GET['sala_id'])): ?>
          <option value="">Sem sala</option>
        <?php endif; ?>
        <?php while ($s = $salas->fetch_assoc()): ?>
          <option value="<?= $s['id'] ?>">
            Sala <?= htmlspecialchars($s['numero_sala']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="mb-3">
      <label>Quantidade</label>
      <input type="number" name="quantidade" class="form-control" value="1" min="1" required>
    </div>

    <div class="mb-3">
      <label>Descrição</label>
      <textarea name="descricao" class="form-control"></textarea>
    </div>

    <button class="btn btn-success">Salvar</button>
    <a href="index.php" class="btn btn-danger">Cancelar</a>

  </form>

</div>

</body>

</html>
