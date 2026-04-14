<?php
require_once '../config.php';
require_once '../inc/helpers.php';
require_once DBAPI;

$page_title = 'Novo perfil';
$page_subtitle = 'Criar conta com dados completos';

require_once HEADER_TEMPLATE;
require_once NAVBAR_TEMPLATE;

if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../index.php");
  exit;
}

if (($_SESSION['usuario_tipo'] ?? '') !== 'Direção') {
  header("Location: index.php?erro=Você não tem permissão para criar usuários");
  exit;
}

$erro = $_GET['erro'] ?? '';
?>

<div class="container-fluid px-0">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div class="h5 mb-0">Criar perfil</div>
    <a href="index.php" class="btn btn-outline-secondary btn-sm">
      <i class="fas fa-arrow-left me-1"></i>
      Voltar
    </a>
  </div>

  <?php if ($erro): ?>
    <div class="alert alert-danger">
      <?= htmlspecialchars($erro) ?>
    </div>
  <?php endif; ?>

  <div class="row g-3">
    <div class="col-12 col-lg-8">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <form action="process_signup.php" method="POST" enctype="multipart/form-data" onsubmit="return confirmarUsuario()">
            <div class="row g-3">
              <div class="col-12 col-md-8">
                <label class="form-label">Nome</label>
                <input type="text" name="nome" class="form-control" required>
              </div>

              <div class="col-12 col-md-4">
                <label class="form-label">Tipo de usuário</label>
                <select name="tipo" class="form-select" required>
                  <option value="">Selecione</option>
                  <option value="Professor">Professor</option>
                  <option value="Técnico">Técnico</option>
                  <option value="Direção">Direção</option>
                </select>
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label">Telefone</label>
                <input type="text" name="telefone" class="form-control" placeholder="Ex.: 912 345 678">
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label">Senha</label>
                <input type="password" name="senha" class="form-control" required>
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label">Confirmar senha</label>
                <input type="password" name="confirmar_senha" class="form-control" required>
              </div>

              <div class="col-12">
                <label class="form-label">Foto do perfil</label>
                <input type="file" name="foto" class="form-control js-profile-photo-input" accept="image/*">
                <input type="hidden" name="cropped_foto_data" class="js-profile-photo-data">
                <div class="form-text">Pode enviar a imagem e ajustar zoom/posição antes de salvar.</div>

                <div class="profile-photo-uploader mt-3">
                  <div class="profile-photo-preview-wrap">
                    <img src="<?= htmlspecialchars(BASEURL . 'images/profile-default.svg') ?>" alt="Pré-visualização" class="profile-photo-preview js-profile-photo-preview">
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
                <label class="form-label">Bio / observações</label>
                <textarea name="bio" class="form-control" rows="4" placeholder="Função, notas rápidas, responsabilidades, etc."></textarea>
              </div>
            </div>

            <div class="mt-4 d-flex gap-2">
              <button type="submit" class="btn btn-success">
                <i class="fas fa-check me-1"></i>
                Criar perfil
              </button>
              <a href="index.php" class="btn btn-danger">Cancelar</a>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="fw-semibold mb-2"><i class="fas fa-id-badge me-2 text-muted"></i>Agora cada utilizador tem perfil</div>
          <div class="text-muted small mb-3">
            O perfil suporta foto, telefone, bio, último acesso e histórico de atividades dentro do sistema.
          </div>
          <div class="fw-semibold mb-2"><i class="fas fa-shield-alt me-2 text-muted"></i>Confirmação de segurança</div>
          <div class="text-muted small">
            Ao criar um novo utilizador, o sistema vai pedir a <strong>sua senha</strong> para confirmar a ação.
          </div>
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
  function confirmarUsuario() {
    let senha = prompt("Digite sua senha para confirmar esta ação:");

    if (!senha) {
      alert("Ação cancelada.");
      return false;
    }

    let input = document.createElement("input");
    input.type = "hidden";
    input.name = "senha_confirmacao";
    input.value = senha;

    document.querySelector("form").appendChild(input);
    return true;
  }

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
      openButton.disabled = true;
      resetButton.disabled = true;
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
