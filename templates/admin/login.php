<?php declare(strict_types=1); ?>
<div class="login-box card">
    <h1 class="brand">Admin</h1>
    <p class="muted">Entre com email ou usuário admin</p>
    <?php if (!empty($error)): ?>
        <div class="error">Credenciais inválidas.</div>
    <?php endif; ?>
    <form method="post" action="/admin/login">
        <p><label>Usuário / Email<br><input name="username" required autofocus></label></p>
        <p><label>Senha<br><input type="password" name="password" required></label></p>
        <p><button type="submit">Entrar</button></p>
    </form>
</div>
