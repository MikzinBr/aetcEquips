<?php
require_once '../config.php';
require_once DBAPI;

$page_title = 'Novo usuário';
$page_subtitle = 'Criar conta';

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
    <div class="h5 mb-0">Criar usuário</div>
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
    <div class="col-12 col-lg-7 col-xl-6">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <form action="process_signup.php" method="POST" onsubmit="return confirmarUsuario()">
            <div class="mb-3">
              <label class="form-label">Nome</label>
              <input type="text" name="nome" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" required>
            </div>

            <div class="row g-2">
              <div class="col-12 col-md-6">
                <div class="mb-3">
                  <label class="form-label">Senha</label>
                  <input type="password" name="senha" class="form-control" required>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="mb-3">
                  <label class="form-label">Confirmar senha</label>
                  <input type="password" name="confirmar_senha" class="form-control" required>
                </div>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Tipo de usuário</label>
              <select name="tipo" class="form-select" required>
                <option value="">Selecione</option>
                <option value="Professor">Professor</option>
                <option value="Técnico">Técnico</option>
                <option value="Direção">Direção</option>
              </select>
              <div class="form-text">Apenas usuários do tipo <strong>Direção</strong> conseguem gerir contas.</div>
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-success">
                <i class="fas fa-check me-1"></i>
                Criar usuário
              </button>
              <a href="index.php" class="btn btn-danger">Cancelar</a>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-5 col-xl-6">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <div class="fw-semibold mb-2"><i class="fas fa-shield-alt me-2 text-muted"></i>Confirmação de segurança</div>
          <div class="text-muted small">
            Ao criar um novo usuário, o sistema vai pedir a <strong>sua senha</strong> (Direção) para confirmar a ação.
          </div>
        </div>
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
</script>

<?php require_once FOOTER_TEMPLATE; ?>
