<?php

declare(strict_types=1);

$activeNav = $activeNav ?? 'quiz';
$pageTitle = $pageTitle ?? 'Quiz | Clubinho Pet Box';
$metaDescription = $metaDescription ?? 'Um quiz curto para montar a box do seu pet.';
$canonicalPath = $canonicalPath ?? '/quiz';
require BASE_PATH . '/templates/site/header.php';
?>

<section class="section bg-white">
    <div class="quiz-container reveal" id="quizApp" data-quiz-root>
        <p class="quiz-error" id="quizError" hidden></p>

        <div class="quiz-intro" id="quizIntro">
            <p class="text-uppercase text-gold">Quiz</p>
            <h1>Vamos montar a box do seu pet</h1>
            <p class="lead">Uma pergunta por vez, sobre o jeito de quem vai receber a box. No final, o plano que combina com vocês.</p>
            <button type="button" class="btn btn-primary" id="quizStart" disabled>Preparando…</button>
        </div>

        <div id="quizPlay" hidden>
            <div class="quiz-progress">
                <ol class="quiz-chapters" id="quizChapters" aria-label="Momentos do quiz"></ol>
                <div class="progress-bar-container">
                    <div class="progress-bar" id="progressBar" style="width:0%"></div>
                </div>
                <p class="progress-text" id="progressLabel"></p>
            </div>

            <div class="quiz-stage">
                <aside class="quiz-mood" aria-hidden="false">
                    <div class="quiz-mood-frame" id="quizMoodFrame">
                        <img id="quizMoodImage" class="quiz-mood-frame__img is-active" src="/assets/images/mood/q02.jpg" alt="" width="900" height="1100">
                        <img id="quizMoodImageNext" class="quiz-mood-frame__img" src="/assets/images/mood/q02.jpg" alt="" width="900" height="1100" aria-hidden="true">
                    </div>
                    <p class="quiz-mood-caption" id="quizMoodCaption">Quem vai receber a box</p>
                </aside>

                <div class="quiz-panel">
                    <div id="quizStepMount"></div>
                    <p class="quiz-ack" id="quizAck" hidden aria-live="polite"></p>
                    <div class="quiz-navigation" id="quizNavigation">
                        <button type="button" class="btn btn-secondary btn-quiz" id="quizPrev" hidden>Voltar</button>
                        <button type="button" class="btn btn-primary btn-quiz" id="quizNext">Continuar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="quiz-result" id="quizResult" hidden>
            <div class="result-icon" id="resultIcon"></div>
            <p class="result-warm" id="resultWarm"></p>
            <h2 class="result-title" id="resultTitle"></h2>
            <p class="result-subtitle" id="resultSubtitle"></p>
            <ul class="quiz-traits" id="resultTraits"></ul>
            <p class="lead" id="quizResultLead"></p>
            <p class="muted quiz-result-note">A equipe revisa a montagem sugerida. Enquanto isso, você já pode assinar.</p>
            <div class="result-actions">
                <a id="quizCheckout" class="btn btn-primary" href="/planos">Assinar</a>
                <a class="btn btn-secondary" href="/">Voltar ao início</a>
            </div>
        </div>
    </div>
</section>

<script src="/assets/js/quiz-v2.js" defer></script>
<?php require BASE_PATH . '/templates/site/footer.php'; ?>
