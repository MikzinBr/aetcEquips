<?php
require_once '../config.php';
require_once '../inc/helpers.php';
require_once DBAPI;

$page_title = 'Perfis';
$page_subtitle = 'Gestão de contas e histórico';

require_once HEADER_TEMPLATE;
require_once NAVBAR_TEMPLATE;

if (!isset($_SESSION['usuario_id'])) {
  header('Location: ../index.php');
  exit;
}

if (($_SESSION['usuario_tipo'] ?? '') !== 'Direção') {
  header('Location: ../dashboard.php');
  exit;
}

$erro = $_GET['erro'] ?? '';
$msg  = $_GET['msg'] ?? '';
$tipos = ['Professor', 'Técnico', 'Direção'];

$conn = open_database();
ensure_profile_schema($conn);
$stmt = $conn->prepare('SELECT id, nome, email, tipo, foto, bio, telefone, criado_em, ultimo_login_at FROM usuarios ORDER BY id DESC');
$stmt->execute();
$result = $stmt->get_result();
$usuarios = [];
$userModals = [];
while ($row = $result->fetch_assoc()) {
  $usuarios[] = $row;
}
?>

<div class="container-fluid px-0" style="width: 90vw">
  <?php if ($erro) : ?>
    <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
  <?php endif; ?>

  <?php if ($msg) : ?>
    <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <section class="page-hero m-4">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <span class="badge bg-white text-primary mb-3 px-3 py-2">Área de gestão</span>
        <div class="h2 mb-2">Perfis de utilizador</div>
        <p class="mb-0">Controle contas, fotografias, contactos e histórico num espaço visual mais limpo e profissional.</p>
      </div>
      <div class="col-lg-4">
        <div class="d-flex gap-2 justify-content-lg-end flex-wrap">
          <a href="index.php" class="btn btn-light btn-sm" title="Atualizar"><i class="fas fa-sync-alt me-1"></i>Atualizar</a>
          <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#novoPerfilModal"><i class="fas fa-plus me-1"></i>Novo perfil</button>
        </div>
      </div>
    </div>
  </section>

  <div class="card border-0 shadow-sm mb-3 filter-shell">
    <div class="card-body p-4">
      <div class="accordion filter-accordion" id="usersFiltersAccordion">
        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#usersFiltersCollapse" aria-expanded="false">
              <span><i class="fas fa-sliders-h me-2 text-primary"></i>Filtros avançados</span>
            </button>
          </h2>
          <div id="usersFiltersCollapse" class="accordion-collapse collapse" data-bs-parent="#usersFiltersAccordion">
            <div class="accordion-body px-0 pb-0">
              <p class="text-muted mb-3">Pesquise, combine critérios e ordene os perfis com mais precisão.</p>
              <form class="row g-3 align-items-end filter-grid" data-advanced-filter-form="usersTable" data-empty-state="usersEmptyState" data-results-count="usersCount">
        <div class="col-12 col-md-5 col-xl-3">
          <label class="form-label small text-muted">Pesquisa geral</label>
          <div class="filter-search">
            <i class="fas fa-search"></i>
            <input type="text" class="form-control form-control-sm" placeholder="Nome, email, telefone..." data-global-search>
          </div>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
          <label class="form-label small text-muted">Cargo</label>
          <select class="form-select form-select-sm" data-filter-key="tipo">
            <option value="all">Todos</option>
            <?php foreach ($tipos as $t): ?>
              <option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
          <label class="form-label small text-muted">Telefone</label>
          <select class="form-select form-select-sm" data-filter-key="telefone" data-filter-mode="presence">
            <option value="all">Todos</option>
            <option value="with">Com telefone</option>
            <option value="without">Sem telefone</option>
          </select>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
          <label class="form-label small text-muted">Acesso</label>
          <select class="form-select form-select-sm" data-filter-key="acesso">
            <option value="all">Todos</option>
            <option value="com-acesso">Com login</option>
            <option value="sem-acesso">Sem login</option>
          </select>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
          <label class="form-label small text-muted">Criado a partir de</label>
          <input type="date" class="form-control form-control-sm" data-filter-key="criado" data-filter-mode="date-min">
        </div>
        <div class="col-6 col-md-3 col-xl-2">
          <label class="form-label small text-muted">Criado até</label>
          <input type="date" class="form-control form-control-sm" data-filter-key="criado" data-filter-mode="date-max">
        </div>
        <div class="col-12 col-md-6 col-xl-2">
          <label class="form-label small text-muted">Nome</label>
          <select class="form-select form-select-sm" data-sort-key="nome" data-sort-type="text">
            <option value="default" selected>Padrão</option>
            <option value="asc">A-Z</option>
            <option value="desc">Z-A</option>
          </select>
        </div>
        <div class="col-12 col-md-6 col-xl-2">
          <label class="form-label small text-muted">Último acesso</label>
          <select class="form-select form-select-sm" data-sort-key="ultimo" data-sort-type="date">
            <option value="desc" selected>Mais recente</option>
            <option value="asc">Mais antigo</option>
            <option value="default">Ignorar</option>
          </select>
        </div>
        <div class="col-12 col-xl-1 filter-actions d-grid">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-filter-reset>Limpar</button>
        </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card border-0 shadow-sm table-card-shell">
    <div class="card-body p-3 p-lg-4">
      <div class="table-panel-header mb-3">
        <div>
          <div class="table-panel-title">Lista de utilizadores</div>
          <div class="table-panel-subtitle"><span id="usersCount"><?= count($usuarios) ?></span> perfil(is) encontrado(s)</div>
        </div>
        <div class="table-panel-hint">Combine cargo, período, login e telefone para um filtro mais preciso.</div>
      </div>

      <div class="table-responsive table-modern-wrap">
        <table class="table table-hover align-middle mb-0 table-modern-like" id="usersTable">
          <thead>
            <tr>
              <th>Perfil</th>
              <th>Contacto</th>
              <th>Cargo</th>
              <th>Último acesso</th>
              <th class="text-end">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($usuarios as $u): ?>
              <?php $tipo = (string)($u['tipo'] ?? ''); $badge = ($tipo === 'Direção') ? 'primary' : (($tipo === 'Técnico') ? 'warning' : 'secondary'); ?>
              <tr
                data-id="<?= (int)$u['id'] ?>"
                data-nome="<?= htmlspecialchars($u['nome']) ?>"
                data-tipo="<?= htmlspecialchars($tipo) ?>"
                data-telefone="<?= htmlspecialchars(trim((string)($u['telefone'] ?? ''))) ?>"
                data-acesso="<?= !empty($u['ultimo_login_at']) ? 'com-acesso' : 'sem-acesso' ?>"
                data-criado="<?= !empty($u['criado_em']) ? htmlspecialchars(date('Y-m-d', strtotime($u['criado_em']))) : '' ?>"
                data-ultimo="<?= !empty($u['ultimo_login_at']) ? htmlspecialchars(date('Y-m-d H:i:s', strtotime($u['ultimo_login_at']))) : '' ?>"
                data-search="<?= htmlspecialchars(mb_strtolower(trim(($u['nome'] ?? '') . ' ' . ($u['email'] ?? '') . ' ' . ($u['telefone'] ?? '') . ' ' . ($u['tipo'] ?? '')))) ?>"
              >
                <td>
                  <div class="table-main-cell">
                    <img src="<?= htmlspecialchars(get_avatar_url($u)) ?>" alt="Avatar" class="table-avatar">
                    <div class="table-main-stack">
                      <div class="table-main-title"><?= limitText(htmlspecialchars($u['nome']), 32) ?></div>
                      <div class="table-main-meta">ID #<?= (int)$u['id'] ?></div>
                    </div>
                  </div>
                </td>
                <td>
                  <div><?= limitText(htmlspecialchars($u['email']), 40) ?></div>
                  <div class="small text-muted"><?= htmlspecialchars($u['telefone'] ?: 'Sem telefone') ?></div>
                </td>
                <td>
                  <span class="badge rounded-pill bg-<?= $badge ?>-subtle text-<?= $badge ?> border border-<?= $badge ?>-subtle">
                    <?= htmlspecialchars($tipo ?: '—') ?>
                  </span>
                </td>
                <td class="small text-muted"><?= !empty($u['ultimo_login_at']) ? date('d/m/Y H:i', strtotime($u['ultimo_login_at'])) : 'Ainda sem acesso' ?></td>
                <td class="text-end">
                  <div class="btn-group" role="group" aria-label="Ações">
                    <button type="button" class="btn btn-outline-primary btn-sm" title="Ver perfil" data-bs-toggle="modal" data-bs-target="#verPerfil<?= (int)$u['id'] ?>">
                      <i class="fas fa-id-card"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" title="Editar" data-bs-toggle="modal" data-bs-target="#editarPerfil<?= (int)$u['id'] ?>">
                      <i class="fas fa-pen"></i>
                    </button>
                    <?php if ($_SESSION['usuario_id'] != $u['id']) : ?>
                      <a href="delete.php?id=<?= (int)$u['id'] ?>" class="btn btn-outline-danger btn-sm" title="Remover" onclick="return confirm('Deseja realmente remover este perfil?')">
                        <i class="fas fa-trash"></i>
                      </a>
                    <?php else : ?>
                      <button class="btn btn-outline-secondary disabled btn-sm"><i class="fas fa-trash"></i></button>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php
              ob_start();
              ?>
<div class="modal fade" id="verPerfil<?= (int)$u['id'] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h1 class="modal-title fs-5">Perfil de utilizador</h1>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <div class="row g-4 align-items-start">
                        <div class="col-12 col-md-4 text-center">
                          <img src="<?= htmlspecialchars(get_avatar_url($u)) ?>" alt="Avatar" class="rounded-circle border shadow-sm mb-3" style="width: 132px; height: 132px; object-fit: cover;">
                          <div class="fw-semibold fs-5"><?= htmlspecialchars($u['nome']) ?></div>
                          <div class="text-muted"><?= htmlspecialchars($u['tipo']) ?></div>
                        </div>
                        <div class="col-12 col-md-8">
                          <div class="row g-3">
                            <div class="col-12 col-sm-6"><label class="form-label small text-muted mb-1">Email</label><div class="fw-semibold"><?= htmlspecialchars($u['email']) ?></div></div>
                            <div class="col-12 col-sm-6"><label class="form-label small text-muted mb-1">Telefone</label><div class="fw-semibold"><?= htmlspecialchars($u['telefone'] ?: 'Não informado') ?></div></div>
                            <div class="col-12 col-sm-6"><label class="form-label small text-muted mb-1">Criado em</label><div class="fw-semibold"><?= !empty($u['criado_em']) ? date('d/m/Y H:i', strtotime($u['criado_em'])) : '—' ?></div></div>
                            <div class="col-12 col-sm-6"><label class="form-label small text-muted mb-1">Último acesso</label><div class="fw-semibold"><?= !empty($u['ultimo_login_at']) ? date('d/m/Y H:i', strtotime($u['ultimo_login_at'])) : 'Ainda sem acesso' ?></div></div>
                            <div class="col-12">
                              <label class="form-label small text-muted mb-1">Bio / observações</label>
                              <div class="border rounded-3 p-3 bg-light-subtle small" style="min-height: 88px;"><?= nl2br(htmlspecialchars($u['bio'] ?: 'Sem observações no perfil.')) ?></div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <a href="profile.php?id=<?= (int)$u['id'] ?>" class="btn btn-outline-primary">Abrir página completa</a>
                      <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
                    </div>
                  </div>
                </div>
              </div>

              <div class="modal fade" id="editarPerfil<?= (int)$u['id'] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h1 class="modal-title fs-5">Editar perfil</h1>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" enctype="multipart/form-data" action="edit.php?id=<?= (int)$u['id'] ?>">
                      <div class="modal-body">
                        <div class="row g-3">
                          <div class="col-12 col-md-8"><label class="form-label">Nome</label><input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($u['nome']) ?>" required></div>
                          <div class="col-12 col-md-4"><label class="form-label">Tipo</label><select name="tipo" class="form-select" required><?php foreach ($tipos as $t): ?><option value="<?= htmlspecialchars($t) ?>" <?= ($u['tipo'] === $t) ? 'selected' : '' ?>><?= htmlspecialchars($t) ?></option><?php endforeach; ?></select></div>
                          <div class="col-12 col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($u['email']) ?>" required></div>
                          <div class="col-12 col-md-6"><label class="form-label">Telefone</label><input type="text" name="telefone" class="form-control" value="<?= htmlspecialchars($u['telefone'] ?? '') ?>"></div>
                          <div class="col-12"><label class="form-label">Nova foto do perfil</label><input type="file" name="foto" class="form-control" accept="image/*"></div>
                          <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="remover_foto" id="remover_foto_<?= (int)$u['id'] ?>"><label class="form-check-label" for="remover_foto_<?= (int)$u['id'] ?>">Remover foto atual</label></div></div>
                          <div class="col-12"><label class="form-label">Bio / observações</label><textarea name="bio" class="form-control" rows="4"><?= htmlspecialchars($u['bio'] ?? '') ?></textarea></div>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success" onclick="return confirm('Deseja realmente salvar as alterações?')">Salvar alterações</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            
              <?php
              $userModals[] = ob_get_clean();
              ?>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div id="usersEmptyState" class="text-muted small mt-3 d-none">Nenhum perfil encontrado com os filtros selecionados.</div>

      <?php if (count($usuarios) === 0): ?>
        <div class="text-muted small mt-3">Nenhum utilizador encontrado.</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if (!empty($userModals)) echo implode("\n", $userModals); ?>

<div class="modal fade" id="novoPerfilModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5">Criar perfil</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="process_signup.php" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12 col-md-8"><label class="form-label">Nome</label><input type="text" name="nome" class="form-control" required></div>
            <div class="col-12 col-md-4"><label class="form-label">Tipo de usuário</label><select name="tipo" class="form-select" required><option value="">Selecione</option><?php foreach ($tipos as $t): ?><option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></option><?php endforeach; ?></select></div>
            <div class="col-12 col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
            <div class="col-12 col-md-6"><label class="form-label">Telefone</label><input type="text" name="telefone" class="form-control" placeholder="Ex.: 912 345 678"></div>
            <div class="col-12 col-md-6"><label class="form-label">Senha</label><input type="password" name="senha" class="form-control" required></div>
            <div class="col-12 col-md-6"><label class="form-label">Confirmar senha</label><input type="password" name="confirmar_senha" class="form-control" required></div>
            <div class="col-12"><label class="form-label">Foto do perfil</label><input type="file" name="foto" class="form-control" accept="image/*"><div class="form-text">Opcional. Tamanho máximo: 3 MB.</div></div>
            <div class="col-12"><label class="form-label">Bio / observações</label><textarea name="bio" class="form-control" rows="4" placeholder="Função, notas rápidas, responsabilidades, etc."></textarea></div>
            <div class="col-12"><label class="form-label">Confirme com a sua senha</label><input type="password" name="senha_confirmacao" class="form-control" required><div class="form-text">Por segurança, a criação do novo perfil só é concluída após confirmar a senha da Direção.</div></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success">Criar perfil</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once FOOTER_TEMPLATE; ?>
