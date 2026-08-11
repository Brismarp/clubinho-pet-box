<?php

declare(strict_types=1);

$activeNav = 'enviar-foto';
$pageTitle = 'Enviar foto | Clubinho Pet Box';
$metaDescription = 'Envie uma foto do seu pet feliz para a galeria Clubinho Pet Box.';
$canonicalPath = '/enviar-foto';
$error = $error ?? null;
$sent = !empty($_GET['sent']);
require BASE_PATH . '/templates/site/header.php';
?>
<section class="page-hero">
    <div class="container reveal">
        <p class="text-uppercase text-gold">Pets felizes</p>
        <h1>Envie a foto do seu pet</h1>
        <p class="lead">Fotos aprovadas podem aparecer na galeria da home. Formatos JPG, PNG ou WebP até 5&nbsp;MB.</p>
    </div>
</section>
<section class="section bg-white">
    <div class="container" style="max-width:36rem">
        <?php if ($sent): ?>
            <div class="flash reveal" style="padding:1rem;margin-bottom:1rem;border:1px solid var(--color-line);border-radius:var(--radius-md)">
                Obrigado! Sua foto foi enviada e aguarda moderação.
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error reveal" style="padding:1rem;margin-bottom:1rem;border:1px solid #f0b0b0;background:#ffefef;border-radius:var(--radius-md)">
                <?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>
        <div class="form-panel reveal" style="margin:0;max-width:none">
            <form action="/enviar-foto" method="POST" enctype="multipart/form-data" class="stack mt-lg">
                <div class="form-group">
                    <label for="pet_name">Nome do pet *</label>
                    <input type="text" id="pet_name" name="pet_name" required maxlength="120"
                           value="<?= htmlspecialchars((string) ($_POST['pet_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label for="tutor_handle">Seu nome ou @ (opcional)</label>
                    <input type="text" id="tutor_handle" name="tutor_handle" maxlength="120"
                           value="<?= htmlspecialchars((string) ($_POST['tutor_handle'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label for="media_file">Foto *</label>
                    <input type="file" id="media_file" name="media_file" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp" required>
                </div>
                <div class="form-group">
                    <label class="cluster" style="display:flex;gap:.5rem;align-items:flex-start">
                        <input type="checkbox" name="consent" value="1" style="width:auto;margin-top:.2rem" required
                            <?= !empty($_POST['consent']) ? 'checked' : '' ?>>
                        <span>Autorizo o uso desta imagem na comunicação do Clubinho Pet Box (moderação prévia). *</span>
                    </label>
                </div>
                <button type="submit" class="btn btn-primary btn-block-sm">Enviar foto</button>
            </form>
        </div>
    </div>
</section>
<?php require BASE_PATH . '/templates/site/footer.php'; ?>
