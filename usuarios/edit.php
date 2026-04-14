<?php
require_once '../config.php';
require_once '../inc/helpers.php';
require_once DBAPI;

$page_title = 'Editar perfil';
$page_subtitle = 'Atualizar dados do utilizador';

require_once HEADER_TEMPLATE;
require_once NAVBAR_TEMPLATE;

if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../index.php");
  exit;
}

$conn = open_database();
ensure_profile_schema($conn);

if (($_SESSION['usuario_tipo'] ?? '') !== 'Direção') {
  header('Location: index.php?erro=Você não tem permissão para editar utilizadores');
  exit;
}

$tipos = ['Direção', 'Técnico', 'Professor'];

if (!isset($_GET['id'])) {
  header('Location: index.php');
  exit;
}

$id = intval($_GET['id']);

$stmt = $conn->prepare('SELECT id, nome, email, tipo, foto, bio, telefone, criado_em, ultimo_login_at FROM usuarios WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
  header('Location: index.php?erro=Utilizador não encontrado');
  exit;
}

$usuario = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nome     = trim($_POST['nome'] ?? '');
  $email    = trim($_POST['email'] ?? '');
  $tipo     = $_POST['tipo'] ?? '';
  $bio      = trim($_POST['bio'] ?? '');
  $telefone = trim($_POST['telefone'] ?? '');
  $removerFoto = isset($_POST['remover_foto']);
  $fotoPath = $usuario['foto'] ?? null;

  if (!$nome || !$email || !in_array($tipo, $tipos, true)) {
    header('Location: edit.php?id=' . $id . '&erro=Preencha todos os campos corretamente');
    exit;
  }

  try {
    if ($removerFoto) {
      delete_profile_photo($fotoPath);
      $fotoPath = null;
    }

    if ((isset($_FILES['foto']) && ($_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) || !empty($_POST['cropped_foto_data'])) {
      $novaFoto = save_profile_photo($_FILES['foto'] ?? [], $_POST['cropped_foto_data'] ?? '');
      delete_profile_photo($fotoPath);
      $fotoPath = $novaFoto;
    }
  } catch (RuntimeException $e) {
    header('Location: edit.php?id=' . $id . '&erro=' . urlencode($e->getMessage()));
    exit;
  }

  $stmt = $conn->prepare('UPDATE usuarios SET nome = ?, email = ?, tipo = ?, foto = ?, bio = ?, telefone = ? WHERE id = ?');
  $stmt->bind_param('ssssssi', $nome, $email, $tipo, $fotoPath, $bio, $telefone, $id);
  $stmt->execute();
  $stmt->close();

  log_user_activity($conn, $id, 'perfil_atualizado', 'Dados do perfil foram atualizados.');

  if ((int)$_SESSION['usuario_id'] === $id) {
    $_SESSION['usuario_nome'] = $nome;
    $_SESSION['usuario_tipo'] = $tipo;
    $_SESSION['usuario_foto'] = $fotoPath ?? '';
  }

  header('Location: profile.php?id=' . $id . '&msg=Perfil atualizado com sucesso');
  exit;
}

$erro = $_GET['erro'] ?? '';
?>

<div class="container-fluid px-0">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div class="h5 mb-0">Editar perfil</div>
    <a href="profile.php?id=<?= (int)$usuario['id'] ?>" class="btn btn-outline-secondary btn-sm">
      <i class="fas fa-arrow-left me-1"></i>
      Voltar ao perfil
    </a>
  </div>

  <?php if ($erro): ?>
    <div class="alert alert-danger">
      <?= htmlspecialchars($erro) ?>
    </div>
  <?php endif; ?>

  <div class="row g-3">
    <div class="col-12 col-lg-7 col-xl-8">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <form method="POST" enctype="multipart/form-data">
            <div class="row g-3">
              <div class="col-12 col-md-8">
                <label class="form-label">Nome</label>
                <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($usuario['nome']) ?>" required>
              </div>

              <div class="col-12 col-md-4">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-select" required>
                  <?php foreach ($tipos as $t): ?>
                    <option value="<?= htmlspecialchars($t) ?>" <?= ($usuario['tipo'] === $t) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($t) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($usuario['email']) ?>" required>
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label">Telefone</label>
                <input type="text" name="telefone" class="form-control" value="<?= htmlspecialchars($usuario['telefone'] ?? '') ?>">
              </div>

              <div class="col-12">
                <label class="form-label">Nova foto do perfil</label>
                <input type="file" name="foto" class="form-control js-profile-photo-input" accept="image/*">
                <input type="hidden" name="cropped_foto_data" class="js-profile-photo-data">
                <div class="form-text">Pode enviar a imagem e ajustar zoom/posição antes de salvar.</div>

                <div class="profile-photo-uploader mt-3">
                  <div class="profile-photo-preview-wrap">
                    <img src="<?= htmlspecialchars(get_avatar_url($usuario)) ?>" alt="Pré-visualização" class="profile-photo-preview js-profile-photo-preview">
                  </div>
                  <div class="d-flex flex-wrap gap-2 mt-3">
                    <button type="button" class="btn btn-outline-primary btn-sm js-open-photo-editor" disabled>
                      <i class="fas fa-crop-alt me-1"></i>Ajustar imagem
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm js-reset-photo-editor" disabled>
                      <i class="fas fa-undo me-1"></i>Voltar à original
                    </button>
                  </div>
                </div>
              </div>

              <div class="col-12">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="remover_foto" id="remover_foto">
                  <label class="form-check-label" for="remover_foto">Remover foto atual</label>
                </div>
              </div>

              <div class="col-12">
                <label class="form-label">Bio / observações</label>
                <textarea name="bio" class="form-control" rows="5"><?= htmlspecialchars($usuario['bio'] ?? '') ?></textarea>
              </div>
            </div>

            <div class="d-flex gap-2 mt-4">
              <button type="submit" class="btn btn-success" onclick="return confirm('Deseja realmente salvar as alterações?')">
                <i class="fas fa-save me-1"></i>
                Salvar
              </button>
              <a href="profile.php?id=<?= (int)$usuario['id'] ?>" class="btn btn-danger">Cancelar</a>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-5 col-xl-4">
      <div class="card border-0 shadow-sm">
        <div class="card-body text-center">
          <img src="<?= htmlspecialchars(get_avatar_url($usuario)) ?>" alt="Foto do perfil" class="rounded-circle border object-fit-cover mb-3" style="width: 132px; height: 132px; object-fit: cover;">
          <div class="fw-semibold"><?= htmlspecialchars($usuario['nome']) ?></div>
          <div class="text-muted small mb-2"><?= htmlspecialchars($usuario['email']) ?></div>
          <div class="small text-muted">Criado em <?= !empty($usuario['criado_em']) ? date('d/m/Y H:i', strtotime($usuario['criado_em'])) : '—' ?></div>
          <div class="small text-muted">Último acesso <?= !empty($usuario['ultimo_login_at']) ? date('d/m/Y H:i', strtotime($usuario['ultimo_login_at'])) : '—' ?></div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="profilePhotoEditorModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header">
        <h5 class="modal-title">Ajustar foto de perfil</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <div class="row g-4 align-items-start">
          <div class="col-12 col-lg-7">
            <div class="profile-editor-stage js-profile-editor-stage">
              <img src="" alt="Editor" class="js-profile-editor-image">
            </div>
          </div>
          <div class="col-12 col-lg-5">
            <label class="form-label">Zoom</label>
            <input type="range" min="1" max="3" step="0.01" value="1" class="form-range js-profile-editor-zoom">
            <div class="small text-muted mb-3">Arraste a imagem para reposicionar como quiser.</div>
            <div class="profile-editor-round-preview js-profile-editor-round-preview"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary js-apply-photo-crop">Aplicar ajuste</button>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    initProfilePhotoEditor();
  });

  function initProfilePhotoEditor() {
    const fileInput = document.querySelector('.js-profile-photo-input');
    const preview = document.querySelector('.js-profile-photo-preview');
    const hiddenInput = document.querySelector('.js-profile-photo-data');
    const openButton = document.querySelector('.js-open-photo-editor');
    const resetButton = document.querySelector('.js-reset-photo-editor');
    const modalEl = document.getElementById('profilePhotoEditorModal');
    if (!fileInput || !preview || !hiddenInput || !openButton || !resetButton || !modalEl) return;

    const editorImage = modalEl.querySelector('.js-profile-editor-image');
    const stage = modalEl.querySelector('.js-profile-editor-stage');
    const zoomInput = modalEl.querySelector('.js-profile-editor-zoom');
    const roundPreview = modalEl.querySelector('.js-profile-editor-round-preview');
    const applyButton = modalEl.querySelector('.js-apply-photo-crop');
    const modal = new bootstrap.Modal(modalEl);
    const state = { naturalWidth: 0, naturalHeight: 0, scale: 1, x: 0, y: 0, dragging: false, baseX: 0, baseY: 0, sourceUrl: '' };
    const defaultPreview = preview.getAttribute('src');

    function render() {
      editorImage.style.transform = `translate(${state.x}px, ${state.y}px) scale(${state.scale})`;
      roundPreview.style.backgroundImage = `url('${editorImage.src}')`;
      roundPreview.style.backgroundSize = `${state.naturalWidth * state.scale}px ${state.naturalHeight * state.scale}px`;
      roundPreview.style.backgroundPosition = `${state.x}px ${state.y}px`;
    }

    function centerImage() {
      const rect = stage.getBoundingClientRect();
      const baseScale = Math.max(rect.width / state.naturalWidth, rect.height / state.naturalHeight);
      state.scale = Math.max(baseScale, parseFloat(zoomInput.value || '1') * baseScale);
      state.x = (rect.width - state.naturalWidth * state.scale) / 2;
      state.y = (rect.height - state.naturalHeight * state.scale) / 2;
      render();
    }

    function loadEditor(url) {
      state.sourceUrl = url;
      editorImage.onload = function () {
        state.naturalWidth = editorImage.naturalWidth || 1;
        state.naturalHeight = editorImage.naturalHeight || 1;
        zoomInput.value = '1';
        centerImage();
      };
      editorImage.src = url;
    }

    fileInput.addEventListener('change', function (event) {
      const file = event.target.files && event.target.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = function (e) {
        preview.src = e.target.result;
        hiddenInput.value = '';
        openButton.disabled = false;
        resetButton.disabled = false;
        loadEditor(e.target.result);
        modal.show();
      };
      reader.readAsDataURL(file);
    });

    openButton.addEventListener('click', function () {
      if (!preview.src) return;
      loadEditor(hiddenInput.value || preview.src);
      modal.show();
    });

    resetButton.addEventListener('click', function () {
      hiddenInput.value = '';
      fileInput.value = '';
      preview.src = defaultPreview;
      openButton.disabled = false;
      resetButton.disabled = false;
    });

    zoomInput.addEventListener('input', function () {
      if (!state.naturalWidth) return;
      const rect = stage.getBoundingClientRect();
      const baseScale = Math.max(rect.width / state.naturalWidth, rect.height / state.naturalHeight);
      const prevScale = state.scale || baseScale;
      const centerX = rect.width / 2;
      const centerY = rect.height / 2;
      const ratioX = (centerX - state.x) / prevScale;
      const ratioY = (centerY - state.y) / prevScale;
      state.scale = Math.max(baseScale, parseFloat(this.value || '1') * baseScale);
      state.x = centerX - ratioX * state.scale;
      state.y = centerY - ratioY * state.scale;
      render();
    });

    stage.addEventListener('pointerdown', function (e) {
      state.dragging = true;
      state.baseX = e.clientX - state.x;
      state.baseY = e.clientY - state.y;
      stage.setPointerCapture(e.pointerId);
    });
    stage.addEventListener('pointermove', function (e) {
      if (!state.dragging) return;
      state.x = e.clientX - state.baseX;
      state.y = e.clientY - state.baseY;
      render();
    });
    ['pointerup', 'pointercancel', 'pointerleave'].forEach(function (evt) {
      stage.addEventListener(evt, function () { state.dragging = false; });
    });

    applyButton.addEventListener('click', function () {
      const rect = stage.getBoundingClientRect();
      const canvas = document.createElement('canvas');
      canvas.width = 512;
      canvas.height = 512;
      const ctx = canvas.getContext('2d');
      ctx.fillStyle = '#ffffff';
      ctx.fillRect(0, 0, 512, 512);
      const sourceX = (0 - state.x) / state.scale;
      const sourceY = (0 - state.y) / state.scale;
      const sourceW = rect.width / state.scale;
      const sourceH = rect.height / state.scale;
      ctx.drawImage(editorImage, sourceX, sourceY, sourceW, sourceH, 0, 0, 512, 512);
      const dataUrl = canvas.toDataURL('image/jpeg', 0.92);
      hiddenInput.value = dataUrl;
      preview.src = dataUrl;
      openButton.disabled = false;
      resetButton.disabled = false;
      modal.hide();
    });
  }
</script>

<?php require_once FOOTER_TEMPLATE; ?>
