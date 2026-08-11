<?php

declare(strict_types=1);

$activeNav = null;
$pageTitle = 'Privacidade | Clubinho Pet Box';
$metaDescription = 'Política de privacidade e proteção de dados (LGPD) — Clubinho Pet Box.';
$canonicalPath = '/privacidade';
require BASE_PATH . '/templates/site/header.php';
?>
<section class="page-hero">
    <div class="container reveal">
        <p class="text-uppercase text-gold">LGPD</p>
        <h1>Privacidade</h1>
        <p class="lead">Como tratamos dados pessoais e informações do pet.</p>
    </div>
</section>
<section class="section bg-white">
    <div class="container-narrow reveal">
        <div class="blog-prose">
            <p>A Brismarp Tecnologia Ltda (“Clubinho Pet Box”) trata dados pessoais para operar o clube de assinatura, personalizar recomendações de produtos e prestar atendimento.</p>
            <h2>Dados que coletamos</h2>
            <ul>
                <li>Dados de contato do tutor (nome, e-mail, telefone).</li>
                <li>Dados do pet fornecidos no quiz ou formulários (nome, espécie, porte, preferências, restrições informadas).</li>
                <li>Dados de navegação e eventos de funil (analytics first-party), quando aplicável.</li>
                <li>Dados necessários a checkout e assinatura processados também por parceiros de pagamento.</li>
            </ul>
            <h2>Finalidades</h2>
            <p>Usamos os dados para montar e revisar boxes, comunicar sobre a assinatura, melhorar o serviço e cumprir obrigações legais. Informações de restrições/alergias são fornecidas pelo tutor <strong>apenas para seleção de produtos</strong> e <strong>não constituem diagnóstico veterinário</strong>.</p>
            <h2>Base legal e consentimento</h2>
            <p>Tratamos dados com base em execução de contrato, legítimo interesse (quando cabível) e consentimento — por exemplo, no quiz (caixa LGPD) e em comunicações de marketing quando aceitas.</p>
            <h2>Compartilhamento</h2>
            <p>Podemos compartilhar dados com processadores necessários (pagamento, hospedagem, logística), sob contrato e finalidade limitada. Não vendemos dados pessoais.</p>
            <h2>Retenção e direitos</h2>
            <p>Mantemos dados pelo tempo necessário às finalidades e à legislação. Você pode solicitar acesso, correção, eliminação ou oposição pelo e-mail <a href="mailto:contato@clubinhopetbox.com.br">contato@clubinhopetbox.com.br</a>. Pedidos de exportação ou exclusão (DSAR) também podem ser atendidos pelo time de atendimento via CRM administrativo, com registro de auditoria.</p>
            <h2>Segurança</h2>
            <p>Adotamos controles de acesso ao painel administrativo, sessões autenticadas e registro de alterações relevantes. Integrações externas em ambiente de desenvolvimento podem operar em modo mock sem envio real.</p>
            <p class="muted">Última atualização: agosto/2026.</p>
        </div>
    </div>
</section>
<?php require BASE_PATH . '/templates/site/footer.php'; ?>
