<?php

declare(strict_types=1);

$activeNav = 'contato';
$pageTitle = 'Contato | Clubinho Pet Box';
$metaDescription = 'Fale com a equipe Clubinho Pet Box.';
$canonicalPath = '/contato';
require BASE_PATH . '/templates/site/header.php';
?>
<section class="page-hero">
    <div class="container reveal">
        <p class="text-uppercase text-gold">Fale conosco</p>
        <h1>Estamos aqui para ajudar</h1>
        <p class="lead">Dúvidas, sugestões ou suporte — nossa equipe responde com cuidado.</p>
    </div>
</section>
<section class="section bg-white">
    <div class="container">
        <div class="about-content">
            <div class="stack-lg reveal">
                <div>
                    <h3 class="text-gold">E-mail</h3>
                    <p><a href="mailto:contato@clubinhopetbox.com.br">contato@clubinhopetbox.com.br</a></p>
                    <p class="lead" style="font-size:var(--text-sm)">Resposta em até 24h úteis</p>
                </div>
                <div>
                    <h3 class="text-gold">Horário</h3>
                    <p>Segunda a sexta, 9h às 18h</p>
                </div>
            </div>
            <div class="form-panel reveal" style="margin:0;max-width:none">
                <h3>Envie sua mensagem</h3>
                <form action="/contact" method="POST" class="stack mt-lg">
                    <div class="form-group">
                        <label for="nome">Nome completo *</label>
                        <input type="text" id="nome" name="nome" required>
                    </div>
                    <div class="form-group">
                        <label for="email">E-mail *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="telefone">Telefone</label>
                        <input type="tel" id="telefone" name="telefone">
                    </div>
                    <div class="form-group">
                        <label for="assunto">Assunto *</label>
                        <select id="assunto" name="assunto" required>
                            <option value="">Selecione um assunto</option>
                            <option value="duvidas">Dúvidas sobre planos</option>
                            <option value="assinatura">Minha assinatura</option>
                            <option value="produto">Produtos / Box</option>
                            <option value="outro">Outro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="mensagem">Mensagem *</label>
                        <textarea id="mensagem" name="mensagem" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block-sm">Enviar mensagem</button>
                </form>
            </div>
        </div>
    </div>
</section>
<?php require BASE_PATH . '/templates/site/footer.php'; ?>
