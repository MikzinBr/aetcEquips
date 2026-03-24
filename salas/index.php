<?php
require_once '../config.php';
require_once '../inc/helpers.php';
require_once DBAPI;

$page_title = 'Salas';
$page_subtitle = 'Espaços com equipamentos agrupados';

require_once HEADER_TEMPLATE;
require_once NAVBAR_TEMPLATE;

if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../index.php");
  exit;
}

$erro = $_GET['erro'] ?? '';
$msg = $_GET["msg"] ?? "";

$conn = open_database();
$result = $conn->query("SELECT * FROM salas ORDER BY numero_sala");
?>

<div class="container-fluid px-0">

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

  <div class="d-flex align-items-center justify-content-between mb-3">
    <div class="h5 mb-0">Salas</div>
    <div class="d-flex gap-2">
      <a href="index.php" class="btn btn-outline-secondary btn-sm" title="Atualizar">
        <i class="fas fa-sync-alt"></i>
      </a>
      <a href="add.php" class="btn btn-success btn-sm">
        <i class="fas fa-plus me-1"></i>
        Criar Sala
      </a>
    </div>
  </div>

  <?php if ($result->num_rows === 0) : ?>
    <div class="card border-0 shadow-sm">
      <div class="card-body text-center py-5">
        <div class="text-muted">Nenhuma sala registada.</div>
      </div>
    </div>
  <?php else : ?>
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex align-items-center gap-2 mb-3" style="max-width: 360px;">
          <span class="text-muted"><i class="fas fa-search"></i></span>
          <input type="text" class="form-control form-control-sm" placeholder="Pesquisar salas..." data-table-filter="salasTable">
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="salasTable">
            <thead class="table-light">
              <tr>
                <th>Número</th>
                <th>Descrição</th>
                <th class="text-end">Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($sala = $result->fetch_assoc()): ?>
                <tr>
                  <td class="fw-semibold">Sala <?= htmlspecialchars($sala['numero_sala']) ?></td>
                  <td><?= htmlspecialchars(limitText($sala['descricao'], 50)) ?></td>
                  <td class="text-end">
                    <div class="btn-group" role="group" aria-label="Ações">
                      <a href="../equipamentos?sala_id=<?= $sala['id'] ?>" class="btn btn-outline-info btn-sm" title="Adicionar equipamentos">
                        <i class="fas fa-desktop"></i>
                      </a>
                      <button type="button" class="btn btn-outline-<?= $_SESSION['usuario_tipo'] == "Direção" ? "success" : "secondary disabled" ?> btn-sm" data-bs-toggle="<?= $_SESSION['usuario_tipo'] == "Direção" ? "modal" : "" ?>" data-bs-target="#addEquip<?= $sala['id'] ?>">
                        <i class="fas fa-plus"></i>
                      </button>
                      <button type="button" class="btn btn-outline-secondary btn-sm <?= $_SESSION['usuario_tipo'] != "Direção" ? "disabled" : "" ?>" data-bs-toggle="<?= $_SESSION['usuario_tipo'] == "Direção" ? "modal" : "" ?>" data-bs-target="#editarSala<?= $sala['id'] ?>">
                        <i class="fas fa-pen"></i>
                      </button>
                      <?php if ($_SESSION['usuario_tipo'] == "Direção") : ?>
                        <a href="delete.php?id=<?= $sala['id'] ?>" class="btn btn-outline-danger btn-sm" title="Remover" onclick="return confirm('Remover esta sala?')">
                          <i class="fas fa-trash"></i>
                        </a>
                      <?php else : ?>
                        <button class="btn btn-outline-secondary btn-sm disabled">
                          <i class="fas fa-trash"></i>
                        </button>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>

                <!-- Modal Add equipamento -->

                <?php

                $equipamentos = $conn->query("
                  SELECT id, nome FROM equipamentos
                  WHERE sala_id IS NULL
                  ORDER BY nome
                ");

                ?>

                <div class="modal fade" id="addEquip<?= $sala['id'] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h1 class="modal-title fs-5" id="staticBackdropLabel">Adicionar equipamentos</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <form method="POST" action="add_equipamentos.php?sala_id=<?= $sala['id'] ?>">

                          <div class="mb-3">
                            <label>Equipamentos disponíveis</label>
                            <select name="equipamento_ids[]" class="form-select" multiple size="8" required>
                              <?php while ($e = $equipamentos->fetch_assoc()): ?>
                                <option value="<?= $e['id'] ?>">
                                  <?= htmlspecialchars($e['nome']) ?>
                                </option>
                              <?php endwhile; ?>
                            </select>
                            <small class="text-muted">
                              Use Ctrl (Windows) ou Cmd (Mac) para selecionar vários
                            </small>
                          </div>


                      </div>
                      <div class="modal-footer">
                        <button class="btn btn-success">Adicionar</button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">cancelar</button>
                      </div>
                      </form>
                    </div>
                  </div>
                </div>

                <!-- Modal editar sala -->
                <div class="modal fade" id="editarSala<?= $sala['id'] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h1 class="modal-title fs-5" id="staticBackdropLabel">Editar sala</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <form method="POST" class="mt-3" action="edit.php?sala_id=<?= $sala['id'] ?>">

                          <div class="mb-3">
                            <label>Numero da sala</label>
                            <input type="number" name="numero_sala" class="form-control"
                              min="1" value="<?= $sala['numero_sala'] ?>">
                          </div>

                          <div class="mb-3">
                            <label>Descrição</label>
                            <textarea name="descricao" class="form-control"><?= htmlspecialchars($sala['descricao']) ?></textarea>
                          </div>

                      </div>
                      <div class="modal-footer">
                        <button class="btn btn-success" onclick="return confirm('Deseja realmente salvar as alterações?')">Salvar Alterações</button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">cancelar</button>
                      </div>
                      </form>
                    </div>
                  </div>
                </div>

              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php endif; ?>

</div>

<?php require_once FOOTER_TEMPLATE; ?>
