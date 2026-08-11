<?php

declare(strict_types=1);

$activeNav = $activeNav ?? 'quiz';
$pageTitle = $pageTitle ?? 'Quiz | Clubinho Pet Box';
$metaDescription = $metaDescription ?? 'Quiz inteligente para montar a box do seu pet.';
$canonicalPath = $canonicalPath ?? '/quiz';
require BASE_PATH . '/templates/site/header.php';
?>

<section class="page-hero">
    <div class="container reveal">
        <p class="text-uppercase text-gold">Personalização</p>
        <h1>Quiz inteligente</h1>
        <p class="lead">Dez etapas para entender o perfil do pet e sugerir produtos adequados — com revisão da equipe.</p>
    </div>
</section>

<section class="section bg-white">
    <div class="quiz-container reveal" id="quizApp" data-quiz-root>
        <div class="quiz-progress">
            <div class="progress-bar-container">
                <div class="progress-bar" id="progressBar" style="width:10%"></div>
            </div>
            <p class="progress-text">Etapa <span id="currentStep">1</span> de <span id="totalSteps">10</span></p>
        </div>

        <div class="quiz-stage">
            <aside class="quiz-mood" aria-hidden="false">
                <div class="quiz-mood-frame" id="quizMoodFrame">
                    <img id="quizMoodImage" class="quiz-mood-frame__img is-active" src="/assets/images/mood/q01.jpg" alt="" width="900" height="1100">
                    <img id="quizMoodImageNext" class="quiz-mood-frame__img" src="/assets/images/mood/q01.jpg" alt="" width="900" height="1100" aria-hidden="true">
                </div>
                <p class="quiz-mood-caption" id="quizMoodCaption">Momento tutor + pet</p>
            </aside>

            <div class="quiz-panel">
                <p class="quiz-error" id="quizError" hidden></p>
                <div id="quizStepMount"></div>
                <div class="quiz-navigation">
                    <button type="button" class="btn btn-secondary btn-quiz" id="quizPrev" hidden>Voltar</button>
                    <button type="button" class="btn btn-primary btn-quiz" id="quizNext">Continuar</button>
                </div>
            </div>
        </div>

        <div class="quiz-result" id="quizResult" hidden>
            <h2>Perfil registrado</h2>
            <p class="lead" id="quizResultLead"></p>
            <p class="muted">A equipe revisa a montagem sugerida. Enquanto isso, você pode assinar o plano indicado.</p>
            <div class="hero-actions" style="justify-content:flex-start;margin-top:1.5rem">
                <a id="quizCheckout" class="btn btn-primary" href="/planos">Ver planos</a>
                <a class="btn btn-secondary" href="/">Voltar ao início</a>
            </div>
        </div>
    </div>
</section>

<script src="/assets/js/quiz-v2.js" defer></script>
<?php require BASE_PATH . '/templates/site/footer.php'; ?>
