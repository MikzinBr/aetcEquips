<?php
require_once '../config.php';
require_once HEADER_TEMPLATE;

$erro = $_GET['erro'] ?? '';
?>

<style>
  html,
  body {
    height: 100%;
    margin: 0;
  }

  body.index-login-signup {
    overflow: hidden;

    background-image:
      linear-gradient(rgba(0, 0, 0, 0.5),
        rgba(0, 0, 0, 0.5)),
      url('<?php echo BASEURL; ?>images/aetc.jpg');

    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
  }
</style>

<div class="d-flex justify-content-center align-items-center vh-100">
  <div class="card p-4 shadow" style="width: 380px;">
    <h4 class="text-center mb-3">Criar Conta</h4>

    <?php if ($erro): ?>
      <div class="alert alert-danger">
        <?= htmlspecialchars($erro) ?>
      </div>
    <?php endif; ?>

    <form action="process_signup.php" method="POST" onsubmit="return confirmarUsuario()">
      <div class="mb-2">
        <label>Nome</label>
        <input type="text" name="nome" class="form-control" required>
      </div>

      <div class="mb-2">
        <label>Email</label>
        <input type="email" name="email" class="form-control" required>
      </div>

      <div class="mb-2">
        <label>Senha</label>
        <input type="password" name="senha" class="form-control" required>
      </div>

      <div class="mb-2">
        <label>Confirmar Senha</label>
        <input type="password" name="confirmar_senha" class="form-control" required>
      </div>

      <div class="mb-3">
        <label>Tipo de Usuário</label>
        <select name="tipo" class="form-select" required>
          <option value="">Selecione</option>
          <option value="professor">Professor</option>
          <option value="tecnico">Técnico</option>
          <option value="direcao">Direção</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary w-100">
        Criar usuário
      </button>
    </form>
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

</body>

</html>
