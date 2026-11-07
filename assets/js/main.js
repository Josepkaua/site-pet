/* =============================================================
   Clínica Vet – scripts do site
   ============================================================= */
(function () {
    'use strict';

    const $  = (sel, ctx = document) => ctx.querySelector(sel);
    const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const WHATS = (document.currentScript && document.currentScript.dataset.whats) || '5511999999999';
    const brl = (v) => 'R$ ' + Number(v).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    /* ---------------------------------------------------------
       TOASTS
       --------------------------------------------------------- */
    function toast(html, opts = {}) {
        const stack = $('#toastStack');
        if (!stack) return;
        const el = document.createElement('div');
        el.className = 'toast' + (opts.type ? ' toast-' + opts.type : '');
        el.innerHTML = html;
        stack.appendChild(el);
        requestAnimationFrame(() => el.classList.add('show'));
        const close = () => { el.classList.remove('show'); setTimeout(() => el.remove(), 350); };
        el.addEventListener('click', (e) => { if (!e.target.closest('a,button')) close(); });
        setTimeout(close, opts.duration || 3200);
        return el;
    }

    /* ---------------------------------------------------------
       CARRINHO (localStorage)
       --------------------------------------------------------- */
    const Cart = {
        key: 'petcart_v1',

        load() {
            try {
                const raw = JSON.parse(localStorage.getItem(this.key)) || [];
                // Sanitiza o que vem do storage (pode ter sido adulterado)
                return raw.filter((i) => i && typeof i.name === 'string' && isFinite(i.price) && i.qty > 0)
                          .map((i) => ({ name: i.name.slice(0, 120), price: +i.price, emoji: String(i.emoji || '🐾').slice(0, 8), qty: Math.min(99, i.qty | 0) }));
            } catch { return []; }
        },
        save(items) { try { localStorage.setItem(this.key, JSON.stringify(items)); } catch { /* modo privado */ } this.refresh(); },
        add(name, price, emoji) {
            const items = this.load();
            const it = items.find((i) => i.name === name);
            if (it) it.qty = Math.min(99, it.qty + 1);
            else items.push({ name, price: parseFloat(price), emoji: emoji || '🐾', qty: 1 });
            this.save(items);
        },
        setQty(name, qty) {
            let items = this.load();
            if (qty <= 0) items = items.filter((i) => i.name !== name);
            else items.forEach((i) => { if (i.name === name) i.qty = Math.min(99, qty); });
            this.save(items);
        },
        remove(name) { this.save(this.load().filter((i) => i.name !== name)); },
        clear() { this.save([]); },
        total() { return this.load().reduce((s, i) => s + i.price * i.qty, 0); },
        count() { return this.load().reduce((s, i) => s + i.qty, 0); },

        refresh() { this.render(); this.updateCount(); },

        render() {
            const wrap = $('#cartItems');
            if (!wrap) return;
            const items = this.load();
            const empty = $('#cartEmpty');
            const footer = $('#cartFooter');
            $$('.cart-item', wrap).forEach((el) => el.remove());
            if (empty) empty.hidden = items.length > 0;
            if (footer) footer.hidden = items.length === 0;

            items.forEach((item) => {
                const div = document.createElement('div');
                div.className = 'cart-item';
                div.dataset.name = item.name;
                div.innerHTML = `
                    <div class="cart-item-emoji">${esc(item.emoji)}</div>
                    <div class="cart-item-info">
                        <strong title="${esc(item.name)}">${esc(item.name)}</strong>
                        <span>${brl(item.price * item.qty)}</span>
                    </div>
                    <div class="cart-item-qty">
                        <button class="cart-qty-btn" type="button" data-action="dec" aria-label="Diminuir"><i class="fas fa-minus"></i></button>
                        <span class="cart-qty-num">${item.qty}</span>
                        <button class="cart-qty-btn" type="button" data-action="inc" aria-label="Aumentar"><i class="fas fa-plus"></i></button>
                    </div>
                    <button class="cart-item-remove" type="button" data-action="remove" aria-label="Remover"><i class="fas fa-trash-alt"></i></button>`;
                wrap.insertBefore(div, empty);
            });
            const total = $('#cartTotal');
            if (total) total.textContent = brl(this.total());
        },

        updateCount() {
            const n = this.count();
            $$('#cartCount').forEach((el) => {
                const changed = el.textContent !== String(n);
                el.textContent = n;
                el.hidden = n === 0;
                if (changed && n > 0 && !reduceMotion) {
                    el.classList.remove('bump'); void el.offsetWidth; el.classList.add('bump');
                }
            });
        },

        open() {
            $('#cartSidebar')?.classList.add('open');
            $('#cartSidebar')?.setAttribute('aria-hidden', 'false');
            $('#cartOverlay')?.classList.add('open');
            document.body.classList.add('no-scroll');
            setTimeout(() => $('#cartClose')?.focus(), 50);
        },
        close() {
            $('#cartSidebar')?.classList.remove('open');
            $('#cartSidebar')?.setAttribute('aria-hidden', 'true');
            $('#cartOverlay')?.classList.remove('open');
            document.body.classList.remove('no-scroll');
        },

        checkout() {
            const items = this.load();
            if (!items.length) return;
            const lines = items.map((i) => `• ${i.emoji} ${i.name} (x${i.qty}) – ${brl(i.price * i.qty)}`).join('\n');
            const msg = `Olá! Gostaria de fazer um pedido:\n\n${lines}\n\n*Total: ${brl(this.total())}*\n\nPode confirmar a disponibilidade?`;
            window.open(`https://wa.me/${WHATS}?text=${encodeURIComponent(msg)}`, '_blank', 'noopener');
        },
    };

    function initCart() {
        Cart.refresh();
        $$('#cartToggle').forEach((b) => b.addEventListener('click', () => Cart.open()));
        $('#cartClose')?.addEventListener('click', () => Cart.close());
        $('#cartOverlay')?.addEventListener('click', () => Cart.close());
        $('#cartCheckout')?.addEventListener('click', () => Cart.checkout());
        $('#cartClear')?.addEventListener('click', () => { if (confirm('Limpar todos os itens do carrinho?')) Cart.clear(); });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') Cart.close(); });

        $('#cartItems')?.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-action]');
            const row = e.target.closest('.cart-item');
            if (!btn || !row) return;
            const name = row.dataset.name;
            const item = Cart.load().find((i) => i.name === name);
            if (!item) return;
            if (btn.dataset.action === 'inc') Cart.setQty(name, item.qty + 1);
            if (btn.dataset.action === 'dec') Cart.setQty(name, item.qty - 1);
            if (btn.dataset.action === 'remove') Cart.remove(name);
        });

        $$('.btn-comprar').forEach((btn) => btn.addEventListener('click', function () {
            const { product, price, emoji } = this.dataset;
            Cart.add(product, price, emoji);

            this.classList.add('added');
            setTimeout(() => this.classList.remove('added'), 900);
            flyToCart(this.closest('.product-card, .dest-card'), emoji);

            const t = toast(`<span class="toast-emoji">${esc(emoji || '🐾')}</span>
                <span><strong>${esc(product)}</strong><br><small>adicionado ao carrinho</small></span>
                <button type="button" class="toast-btn">Ver carrinho</button>`, { type: 'success' });
            t?.querySelector('.toast-btn')?.addEventListener('click', () => Cart.open());
        }));
    }

    /* Emoji "voando" até o carrinho */
    function flyToCart(card, emoji) {
        const target = $('#cartToggle');
        if (!card || !target || reduceMotion) return;
        const from = card.getBoundingClientRect();
        const to = target.getBoundingClientRect();
        const el = document.createElement('div');
        el.className = 'fly-emoji';
        el.textContent = emoji || '🐾';
        el.style.left = from.left + from.width / 2 + 'px';
        el.style.top = from.top + from.height / 3 + 'px';
        document.body.appendChild(el);
        requestAnimationFrame(() => {
            el.style.transform = `translate(${to.left + to.width / 2 - (from.left + from.width / 2)}px, ${to.top - (from.top + from.height / 3)}px) scale(.3)`;
            el.style.opacity = '.2';
        });
        setTimeout(() => el.remove(), 750);
    }

    /* ---------------------------------------------------------
       NAVEGAÇÃO
       --------------------------------------------------------- */
    function initNav() {
        const toggle = $('#navToggle');
        const menu = $('#navMenu');
        const navbar = $('#navbar');
        const setOpen = (open) => {
            menu?.classList.toggle('open', open);
            toggle?.classList.toggle('open', open);
            toggle?.setAttribute('aria-expanded', String(open));
        };
        toggle?.addEventListener('click', () => setOpen(!menu.classList.contains('open')));
        $$('.nav-link').forEach((l) => l.addEventListener('click', () => setOpen(false)));
        document.addEventListener('click', (e) => { if (menu?.classList.contains('open') && !e.target.closest('#navMenu, #navToggle')) setOpen(false); });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') setOpen(false); });

        const backTop = $('#backToTop');
        let ticking = false;
        const onScroll = () => {
            const y = window.scrollY;
            navbar?.classList.toggle('scrolled', y > 10);
            backTop?.classList.toggle('show', y > 600);
            ticking = false;
        };
        window.addEventListener('scroll', () => { if (!ticking) { requestAnimationFrame(onScroll); ticking = true; } }, { passive: true });
        onScroll();
        backTop?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' }));
    }

    /* ---------------------------------------------------------
       ANIMAÇÕES AO ROLAR + CONTADORES
       --------------------------------------------------------- */
    function initReveal() {
        // Escalonamento: filhos .reveal dentro de [data-reveal-group] entram em sequência
        $$('[data-reveal-group]').forEach((g) => $$('.reveal', g).forEach((el, i) => el.style.setProperty('--d', Math.min(i, 8) * 0.07 + 's')));

        const els = $$('.reveal');
        if (reduceMotion || !('IntersectionObserver' in window)) { els.forEach((el) => el.classList.add('in')); return; }
        document.documentElement.classList.add('js-reveal');
        const io = new IntersectionObserver((entries) => {
            entries.forEach((en) => { if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); } });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
        els.forEach((el) => io.observe(el));
    }

    function initCounters() {
        const els = $$('[data-count]');
        if (!els.length) return;
        const run = (el) => {
            const end = +el.dataset.count, pre = el.dataset.prefix || '', suf = el.dataset.suffix || '';
            if (reduceMotion) { el.textContent = pre + end + suf; return; }
            const t0 = performance.now(), dur = 1400;
            const step = (t) => {
                const p = Math.min(1, (t - t0) / dur);
                el.textContent = pre + Math.round(end * (1 - Math.pow(1 - p, 3))) + suf;
                if (p < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        };
        if (!('IntersectionObserver' in window)) return els.forEach(run);
        const io = new IntersectionObserver((en) => en.forEach((e) => { if (e.isIntersecting) { run(e.target); io.unobserve(e.target); } }), { threshold: 0.6 });
        els.forEach((el) => io.observe(el));
    }

    /* ---------------------------------------------------------
       FORMULÁRIOS
       --------------------------------------------------------- */
    function initForms() {
        // Botão com "carregando" e bloqueio de duplo envio
        $$('form.js-submit-loading').forEach((f) => f.addEventListener('submit', () => {
            if (!f.checkValidity()) return;
            const btn = $('[type="submit"]', f);
            if (!btn || btn.disabled) return;
            btn.dataset.original = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
            setTimeout(() => { btn.disabled = true; }, 0);
        }));

        // Máscara de telefone brasileira
        $$('input[data-mask="phone"]').forEach((inp) => inp.addEventListener('input', () => {
            let d = inp.value.replace(/\D/g, '').slice(0, 11);
            if (d.length > 10) d = d.replace(/^(\d{2})(\d{5})(\d{0,4}).*/, '($1) $2-$3');
            else if (d.length > 6) d = d.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
            else if (d.length > 2) d = d.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
            else if (d.length) d = '(' + d;
            inp.value = d;
        }));

        // Contador de caracteres
        $$('textarea[data-counter]').forEach((ta) => {
            const c = document.createElement('small');
            c.className = 'char-counter';
            ta.after(c);
            const upd = () => { c.textContent = ta.value.length + ' / ' + ta.maxLength; };
            ta.addEventListener('input', upd); upd();
        });

        // Mostrar/ocultar senha
        $$('[data-toggle-pass]').forEach((b) => b.addEventListener('click', () => {
            const inp = document.getElementById(b.dataset.togglePass);
            if (!inp) return;
            const show = inp.type === 'password';
            inp.type = show ? 'text' : 'password';
            b.innerHTML = `<i class="fas fa-eye${show ? '-slash' : ''}"></i>`;
            b.setAttribute('aria-label', show ? 'Ocultar senha' : 'Mostrar senha');
        }));

        // Alertas de sucesso: rolam até a vista e somem depois
        $$('.alert').forEach((a) => {
            if (a.closest('form, .form-card')) a.scrollIntoView({ block: 'center', behavior: reduceMotion ? 'auto' : 'smooth' });
            if (a.classList.contains('alert-success')) setTimeout(() => { a.classList.add('fade-out'); setTimeout(() => a.remove(), 500); }, 6000);
        });

        // Formulário via API (opcional): <form id="agendamentoForm">
        const apiForm = $('#agendamentoForm');
        apiForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = $('[type="submit"]', apiForm);
            const original = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
            btn.disabled = true;
            try {
                const res = await fetch('/api/agendamento.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': $('meta[name="csrf-token"]')?.content || '' },
                    body: JSON.stringify(Object.fromEntries(new FormData(apiForm))),
                });
                const json = await res.json();
                toast(esc(json.message), { type: json.success ? 'success' : 'error', duration: 5000 });
                if (json.success) apiForm.reset();
            } catch {
                toast('Não foi possível enviar. Tente pelo WhatsApp.', { type: 'error' });
            } finally {
                btn.innerHTML = original;
                btn.disabled = false;
            }
        });
    }

    /* ---------------------------------------------------------
       INIT
       --------------------------------------------------------- */
    /* ---------------------------------------------------------
       BLOCOS RECOLHÍVEIS: no celular começam fechados
       (o visitante abre só o que quer ver, sem rolar a página toda)
       --------------------------------------------------------- */
    function initAccordions() {
        if (!window.matchMedia('(max-width: 640px)').matches) return;
        $$('details.acc').forEach((d) => { if (!d.hasAttribute('data-keep-open')) d.open = false; });
    }

    const init = () => { initAccordions(); initNav(); initCart(); initReveal(); initCounters(); initForms(); };
    document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', init) : init();
})();
