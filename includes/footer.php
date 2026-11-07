</main>

<footer class="footer">
    <div class="footer-wave" aria-hidden="true">
        <svg viewBox="0 0 1440 80" preserveAspectRatio="none"><path d="M0,40 C240,80 480,0 720,30 C960,60 1200,10 1440,40 L1440,80 L0,80 Z"/></svg>
    </div>
    <div class="footer-container">
        <div class="footer-col">
            <h3><i class="fas fa-paw"></i> <?= e(CLINIC_NAME) ?></h3>
            <p>Cuidando do seu pet com amor e dedicação. <?= e(CLINIC_DRA) ?>, <?= e(CLINIC_CRMV) ?>.</p>
            <div class="social-links">
                <a href="<?= e(safe_url(CLINIC_INSTAGRAM)) ?>" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="<?= e(safe_url(CLINIC_FACEBOOK)) ?>"  target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                <a href="<?= e(whats_link()) ?>" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
            </div>
        </div>
        <div class="footer-col">
            <h4>Serviços</h4>
            <ul>
                <li><a href="/pages/consultas.php">Consultas Veterinárias</a></li>
                <li><a href="/pages/tosagem.php">Banho &amp; Tosa</a></li>
                <li><a href="/pages/loja.php">Loja Pet</a></li>
                <li><a href="/pages/servicos.php#precos">Vacinas &amp; Exames</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Contato</h4>
            <p><i class="fas fa-map-marker-alt"></i> <?= e(CLINIC_ADDRESS) ?></p>
            <p><i class="fas fa-phone"></i> <a href="tel:+<?= e(CLINIC_WHATS) ?>"><?= e(CLINIC_PHONE) ?></a></p>
            <p><i class="fas fa-envelope"></i> <a href="mailto:<?= e(CLINIC_EMAIL) ?>"><?= e(CLINIC_EMAIL) ?></a></p>
        </div>
        <div class="footer-col">
            <h4>Horário de Consultas</h4>
            <?php foreach (horario_resumo('consultas') as $dia => $hr): ?>
                <p><strong><?= e($dia) ?>:</strong> <?= e($hr) ?></p>
            <?php endforeach; ?>
            <p class="footer-status <?= clinica_aberta_agora() ? 'is-open' : 'is-closed' ?>">
                <span class="dot"></span> <?= clinica_aberta_agora() ? 'Aberto agora' : 'Fechado agora' ?>
            </p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> <?= e(CLINIC_NAME) ?>. Todos os direitos reservados.</p>
    </div>
</footer>

<a href="<?= e(whats_link('Olá! Vim pelo site e gostaria de mais informações.')) ?>" target="_blank" rel="noopener noreferrer"
   class="whatsapp-float" aria-label="Falar pelo WhatsApp"><i class="fab fa-whatsapp"></i></a>
<button class="back-to-top" id="backToTop" type="button" aria-label="Voltar ao topo"><i class="fas fa-arrow-up"></i></button>
<div class="toast-stack" id="toastStack" aria-live="polite"></div>

<script src="/assets/js/main.js?v=2" data-whats="<?= e(CLINIC_WHATS) ?>"></script>
</body>
</html>
