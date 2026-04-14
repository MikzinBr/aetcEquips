<?php
require_once '../config.php';
require_once '../inc/helpers.php';
require_once DBAPI;

ini_set('display_errors', 1);
error_reporting(E_ALL);

$page_title = 'Perfil';
$page_subtitle = 'Foto, dados e atividade';

require_once HEADER_TEMPLATE;
require_once NAVBAR_TEMPLATE;

if (!isset($_SESSION['usuario_id'])) {
  header('Location: ../index.php');
  exit;
}

$conn = open_database();
ensure_profile_schema($conn);

$id = isset($_GET['id']) ? (int)$_GET['id'] : (int)$_SESSION['usuario_id'];
$canManageUsers = (($_SESSION['usuario_tipo'] ?? '') === 'Direção');

if (!$canManageUsers) {
  $id = (int)$_SESSION['usuario_id'];
}

$stmt = $conn->prepare('
  SELECT id, nome, email, tipo, foto, bio, telefone, criado_em, ultimo_login_at
  FROM usuarios
  WHERE id = ?
');

if (!$stmt) {
  die('Erro ao preparar consulta do perfil: ' . $conn->error);
}

$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result ? $result->fetch_assoc() : null;
$stmt->close();

if (!$usuario) {
  die('Perfil não encontrado');
}

$stmt = $conn->prepare(
  "SELECT acao, descricao, criado_em, origem FROM (
      SELECT acao, descricao, criado_em, 'perfil' AS origem
      FROM usuario_atividades
      WHERE usuario_id = ?

      UNION ALL

      SELECT
        CASE
          WHEN COALESCE(resolvido, 0) = 1 THEN 'avaria_resolvida'
          ELSE 'avaria_reportada'
        END AS acao,
        CONCAT('Registo de avaria: ', descricao) AS descricao,
        data_registro AS criado_em,
        'avaria' AS origem
      FROM avarias
      WHERE usuario_id = ?
    ) historico
    ORDER BY criado_em DESC
    LIMIT 20"
);

if (!$stmt) {
  die('Erro ao preparar consulta do histórico: ' . $conn->error);
}

$stmt->bind_param('ii', $id, $id);
$stmt->execute();
$atividades = $stmt->get_result();
$stmt->close();

$msg = $_GET['msg'] ?? '';
$erro = $_GET['erro'] ?? '';
?>

<div class="container-fluid px-0" style="width: 92vw;">
  <?php if ($erro): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
  <?php endif; ?>

  <?php if ($msg): ?>
    <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <div class="row g-4">
    <div class="col-12 col-xl-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body text-center">
          <img
            src="<?= htmlspecialchars(get_avatar_url($usuario)) ?>"
            alt="Foto do perfil"
            class="rounded-circle border shadow-sm mb-3"
            style="width: 148px; height: 148px; object-fit: cover;"
          >

          <h4 class="mb-1"><?= htmlspecialchars($usuario['nome']) ?></h4>
          <div class="text-muted mb-3"><?= htmlspecialchars($usuario['tipo']) ?></div>

          <div class="text-start small">
            <div class="mb-2">
              <strong>Email:</strong>
              <?= htmlspecialchars($usuario['email']) ?>
            </div>

            <div class="mb-2">
              <strong>Telefone:</strong>
              <?= htmlspecialchars($usuario['telefone'] ?: 'Não informado') ?>
            </div>

            <div class="mb-2">
              <strong>Criado em:</strong>
              <?= !empty($usuario['criado_em']) ? date('d/m/Y H:i', strtotime($usuario['criado_em'])) : '—' ?>
            </div>

            <div class="mb-0">
              <strong>Último acesso:</strong>
              <?= !empty($usuario['ultimo_login_at']) ? date('d/m/Y H:i', strtotime($usuario['ultimo_login_at'])) : '—' ?>
            </div>
          </div>

          <hr>

          <div class="text-start">
            <div class="fw-semibold mb-2">Bio</div>
            <div class="text-muted small">
              <?= nl2br(htmlspecialchars($usuario['bio'] ?: 'Sem observações no perfil.')) ?>
            </div>
          </div>

          <div class="d-flex gap-2 mt-4 justify-content-center flex-wrap">
            <?php if ($canManageUsers): ?>
              <a href="edit.php?id=<?= (int)$usuario['id'] ?>" class="btn btn-success btn-sm">
                <i class="fas fa-pen me-1"></i>Editar perfil
              </a>
              <a href="index.php" class="btn btn-outline-secondary btn-sm">Voltar à lista</a>
            <?php else: ?>
              <a href="../dashboard.php" class="btn btn-outline-secondary btn-sm">Voltar</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-8">
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
              <div class="h5 mb-0">Resumo do perfil</div>
              <div class="text-muted small">Visão rápida das principais informações do utilizador.</div>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-md-4">
              <div class="rounded-4 border bg-light p-3 h-100">
                <div class="text-muted small mb-1">Identidade</div>
                <div class="fw-semibold"><?= htmlspecialchars($usuario['nome']) ?></div>
                <div class="small text-muted">Perfil de <?= htmlspecialchars($usuario['tipo']) ?></div>
              </div>
            </div>

            <div class="col-md-4">
              <div class="rounded-4 border bg-light p-3 h-100">
                <div class="text-muted small mb-1">Contacto</div>
                <div class="fw-semibold"><?= htmlspecialchars($usuario['telefone'] ?: 'Sem telefone') ?></div>
                <div class="small text-muted"><?= htmlspecialchars($usuario['email']) ?></div>
              </div>
            </div>

            <div class="col-md-4">
              <div class="rounded-4 border bg-light p-3 h-100">
                <div class="text-muted small mb-1">Atividade</div>
                <div class="fw-semibold">
                  <?= !empty($usuario['ultimo_login_at']) ? date('d/m/Y H:i', strtotime($usuario['ultimo_login_at'])) : 'Sem login' ?>
                </div>
                <div class="small text-muted">Último acesso registado</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
              <div class="h5 mb-0">Histórico de atividades</div>
              <div class="text-muted small">Eventos do perfil e registos de avarias associados ao utilizador.</div>
            </div>
          </div>

          <?php if ($atividades && $atividades->num_rows > 0): ?>
            <div class="timeline-simple">
              <?php while ($atividade = $atividades->fetch_assoc()): ?>
                <div class="d-flex gap-3 pb-3 mb-3 border-bottom">
                  <div
                    class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center"
                    style="width: 42px; height: 42px; min-width: 42px;"
                  >
                    <i class="fas <?= $atividade['origem'] === 'avaria' ? 'fa-tools' : 'fa-user-clock' ?>"></i>
                  </div>

                  <div class="flex-grow-1">
                    <div class="fw-semibold text-capitalize">
                      <?= htmlspecialchars(str_replace('_', ' ', $atividade['acao'])) ?>
                    </div>

                    <div class="text-muted small mb-1">
                      <?= htmlspecialchars($atividade['descricao']) ?>
                    </div>

                    <div class="small text-secondary">
                      <?= !empty($atividade['criado_em']) ? date('d/m/Y H:i', strtotime($atividade['criado_em'])) : '—' ?>
                    </div>
                  </div>
                </div>
              <?php endwhile; ?>
            </div>
          <?php else: ?>
            <div class="text-muted small">Ainda não existem atividades registadas para este perfil.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once FOOTER_TEMPLATE; ?>