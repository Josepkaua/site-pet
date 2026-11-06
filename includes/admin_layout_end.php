    </div><!-- /page-content -->
</div><!-- /admin-main -->

<script>
(function () {
    const sb = document.getElementById('sidebar');
    const ov = document.getElementById('sidebarOverlay');
    const toggle = (open) => { sb.classList.toggle('open', open); ov.classList.toggle('open', open); };
    document.getElementById('sidebarToggle')?.addEventListener('click', () => toggle(!sb.classList.contains('open')));
    ov?.addEventListener('click', () => toggle(false));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') toggle(false); });

    // Alertas de sucesso somem sozinhos
    document.querySelectorAll('.alert-success').forEach(a => setTimeout(() => {
        a.style.transition = 'opacity .5s, transform .5s'; a.style.opacity = '0'; a.style.transform = 'translateY(-6px)';
        setTimeout(() => a.remove(), 500);
    }, 4000));

    // Confirmação para ações destrutivas: <button data-confirm="...">
    document.querySelectorAll('[data-confirm]').forEach(b => b.addEventListener('click', (e) => {
        if (!confirm(b.dataset.confirm)) e.preventDefault();
    }));
})();
</script>
</body>
</html>
