<?php declare(strict_types=1); ?>
    </main>
    <footer class="footer">
        <div class="container">
            <div class="footer-content reveal-stagger">
                <div class="footer-section reveal">
                    <h4>Clubinho Pet Box</h4>
                    <p>Assinatura premium de produtos selecionados para o seu pet.</p>
                </div>
                <div class="footer-section reveal">
                    <h4>Links</h4>
                    <p><a href="/">Início</a></p>
                    <p><a href="/planos">Planos</a></p>
                    <p><a href="/blog">Blog</a></p>
                    <p><a href="/sobre">Sobre</a></p>
                    <p><a href="/quiz">Quiz</a></p>
                    <p><a href="/contato">Contato</a></p>
                    <p><a href="/assinatura">Assinatura</a></p>
                </div>
                <div class="footer-section reveal">
                    <h4>Atendimento</h4>
                    <p><a href="mailto:contato@clubinhopetbox.com.br">contato@clubinhopetbox.com.br</a></p>
                    <p>Seg a Sex: 9h às 18h</p>
                </div>
                <div class="footer-section reveal">
                    <h4>Assine</h4>
                    <p><a href="/planos">Ver planos</a></p>
                    <p><a href="/quiz">Descobrir no quiz</a></p>
                    <p><a href="/privacidade">Privacidade</a></p>
                    <p><a href="/termos">Termos</a></p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Clubinho Pet Box. Todos os direitos reservados.</p>
                <p class="footer-company">Brismarp Tecnologia Ltda | CNPJ: 63.304.598/0001-22</p>
            </div>
        </div>
    </footer>
    <script src="/assets/js/tracker.js" defer></script>
    <script src="/assets/js/motion.js" defer></script>
    <script>
    (function () {
      var meta = document.querySelector('meta[name="csrf-token"]');
      var token = meta && meta.getAttribute('content');
      if (!token) return;
      window.__CSRF_TOKEN__ = token;
      document.querySelectorAll('form').forEach(function (form) {
        if (form.querySelector('input[name="_csrf"]')) return;
        var method = (form.getAttribute('method') || 'get').toLowerCase();
        if (method !== 'post') return;
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = '_csrf';
        input.value = token;
        form.appendChild(input);
      });
    })();
    </script>
</body>
</html>
