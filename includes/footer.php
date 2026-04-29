<footer class="footer">
    <div class="footer-container">
        <div class="footer-col">
            <h3><i class="fas fa-paw"></i> <?= CLINIC_NAME ?></h3>
            <p>Cuidando do seu pet com amor e dedicação. <?= CLINIC_DRA ?>, <?= CLINIC_CRMV ?>.</p>
            <div class="social-links">
                <a href="https://instagram.com" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="https://facebook.com"  target="_blank" title="Facebook"><i class="fab fa-facebook"></i></a>
                <a href="https://wa.me/<?= CLINIC_WHATS ?>" target="_blank" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
            </div>
        </div>
        <div class="footer-col">
            <h4>Serviços</h4>
            <ul>
                <li><a href="/Site-pet/pages/consultas.php">Consultas Veterinárias</a></li>
                <li><a href="/Site-pet/pages/tosagem.php">Banho & Tosa</a></li>
                <li><a href="/Site-pet/pages/loja.php">Loja Pet</a></li>
                <li><a href="/Site-pet/pages/servicos.php">Vacinas & Exames</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Contato</h4>
            <p><i class="fas fa-map-marker-alt"></i> <?= CLINIC_ADDRESS ?></p>
            <p><i class="fas fa-phone"></i> <?= CLINIC_PHONE ?></p>
            <p><i class="fas fa-envelope"></i> <?= CLINIC_EMAIL ?></p>
        </div>
        <div class="footer-col">
            <h4>Horário de Atendimento</h4>
            <p><strong>Seg – Sex:</strong> 08:00 – 19:00</p>
            <p><strong>Sábado:</strong> 08:00 – 17:00</p>
            <p><strong>Domingo:</strong> 09:00 – 13:00</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> <?= CLINIC_NAME ?>. Todos os direitos reservados.</p>
    </div>
</footer>
<script src="/Site-pet/assets/js/main.js"></script>
</body>
</html>
