<?php require_once 'config.php'; ?>
<?php $page_title = 'Login'; $body_class = 'app-login'; ?>
<?php require_once HEADER_TEMPLATE ?>
<?php $erro = $_GET['erro'] ?? ''; ?>
<?php session_start(); ?>
<?php
if (isset($_SESSION['usuario_id'])) {
  header('location: dashboard.php');
  exit;
}
?>

<div class="login-v3-page">
  <div class="login-v3-shell">
    <div class="login-v3-backdrop">
      <img src="images/login-hero.gif" class="login-v3-gif" alt="">
    </div>
    <section class="login-v3-panel">
        <a class="navbar-brand d-flex align-items-center gap-3" href="<?= BASEURL ?>dashboard.php">
            <img src="<?= BASEURL ?>/images/logotipo-agr-tc-pt-vertical.png" height="70" alt="AETC">
        </a>
        
        <h1>Entrar no sistema</h1>

        <?php if ($erro) : ?>
          <div class="alert alert-danger mb-4"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form action="<?php echo BASEURL; ?>process_login.php" method="POST" class="login-v3-form">
          <label class="login-v3-field">
            <span>Email</span>
            <div class="login-v3-input-wrap">
              <i class="fas fa-envelope"></i>
              <input type="email" name="email" placeholder="seuemail@escola.pt" required>
            </div>
          </label>

          <label class="login-v3-field">
            <span>Palavra-passe</span>
            <div class="login-v3-input-wrap">
              <i class="fas fa-lock"></i>
              <input id="loginSenha" type="password" name="senha" placeholder="Digite a sua palavra-passe" required>
              <button class="login-v3-eye" type="button" aria-label="Mostrar ou ocultar palavra-passe" onclick="const i=document.getElementById('loginSenha'); this.querySelector('i').classList.toggle('fa-eye'); this.querySelector('i').classList.toggle('fa-eye-slash'); i.type = i.type === 'password' ? 'text' : 'password';">
                <i class="far fa-eye-slash"></i>
              </button>
            </div>
          </label>

          <button class="login-v3-submit" type="submit">Entrar</button>
        </form>
    </section>
  </div>
</div>

</body></body>
</html>
