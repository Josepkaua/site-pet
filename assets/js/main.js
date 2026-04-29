/* =============================================
   CART – localStorage
   ============================================= */
const WHATS = window.WHATS_NUMBER || '5511999999999';

const Cart = {
    _key: 'petcart_v1',

    load() {
        try { return JSON.parse(localStorage.getItem(this._key)) || []; }
        catch { return []; }
    },

    save(items) {
        localStorage.setItem(this._key, JSON.stringify(items));
    },

    add(name, price, emoji) {
        const items = this.load();
        const idx   = items.findIndex(i => i.name === name);
        if (idx >= 0) {
            items[idx].qty += 1;
        } else {
            items.push({ name, price: parseFloat(price), emoji: emoji || '🐾', qty: 1 });
        }
        this.save(items);
        this.render();
        this.updateCount();
        return items.length;
    },

    remove(name) {
        const items = this.load().filter(i => i.name !== name);
        this.save(items);
        this.render();
        this.updateCount();
    },

    setQty(name, qty) {
        const items = this.load();
        const idx   = items.findIndex(i => i.name === name);
        if (idx < 0) return;
        if (qty <= 0) { this.remove(name); return; }
        items[idx].qty = qty;
        this.save(items);
        this.render();
        this.updateCount();
    },

    clear() {
        this.save([]);
        this.render();
        this.updateCount();
    },

    total() {
        return this.load().reduce((s, i) => s + i.price * i.qty, 0);
    },

    count() {
        return this.load().reduce((s, i) => s + i.qty, 0);
    },

    render() {
        const wrap   = document.getElementById('cartItems');
        const footer = document.getElementById('cartFooter');
        const empty  = document.getElementById('cartEmpty');
        const total  = document.getElementById('cartTotal');
        if (!wrap) return;

        const items = this.load();

        // Limpa itens anteriores (mantendo o empty placeholder)
        wrap.querySelectorAll('.cart-item').forEach(el => el.remove());

        if (items.length === 0) {
            if (empty)  empty.style.display  = '';
            if (footer) footer.style.display  = 'none';
            return;
        }

        if (empty)  empty.style.display  = 'none';
        if (footer) footer.style.display = '';

        items.forEach(item => {
            const div = document.createElement('div');
            div.className = 'cart-item';
            div.innerHTML = `
                <div class="cart-item-emoji">${item.emoji}</div>
                <div class="cart-item-info">
                    <strong title="${item.name}">${item.name}</strong>
                    <span>R$ ${(item.price * item.qty).toLocaleString('pt-BR',{minimumFractionDigits:2})}</span>
                </div>
                <div class="cart-item-qty">
                    <button class="cart-qty-btn" data-action="dec" data-name="${item.name}"><i class="fas fa-minus"></i></button>
                    <span class="cart-qty-num">${item.qty}</span>
                    <button class="cart-qty-btn" data-action="inc" data-name="${item.name}"><i class="fas fa-plus"></i></button>
                </div>
                <button class="cart-item-remove" data-name="${item.name}" title="Remover"><i class="fas fa-trash-alt"></i></button>
            `;
            wrap.insertBefore(div, empty);
        });

        if (total) {
            total.textContent = 'R$ ' + this.total().toLocaleString('pt-BR', { minimumFractionDigits: 2 });
        }
    },

    updateCount() {
        const n   = this.count();
        const els = document.querySelectorAll('#cartCount');
        els.forEach(el => {
            el.textContent  = n;
            el.style.display = n > 0 ? '' : 'none';
        });
    },

    open() {
        document.getElementById('cartSidebar')?.classList.add('open');
        document.getElementById('cartOverlay')?.classList.add('open');
        document.body.style.overflow = 'hidden';
    },

    close() {
        document.getElementById('cartSidebar')?.classList.remove('open');
        document.getElementById('cartOverlay')?.classList.remove('open');
        document.body.style.overflow = '';
    },

    checkout() {
        const items = this.load();
        if (!items.length) return;
        const lines = items.map(i =>
            `• ${i.emoji} ${i.name} (x${i.qty}) – R$ ${(i.price * i.qty).toLocaleString('pt-BR',{minimumFractionDigits:2})}`
        ).join('\n');
        const total = this.total().toLocaleString('pt-BR', { minimumFractionDigits: 2 });
        const msg   = encodeURIComponent(
            `Ola! Gostaria de fazer um pedido:\n\n${lines}\n\n*Total: R$ ${total}*\n\nPode confirmar disponibilidade?`
        );
        window.open(`https://wa.me/${WHATS}?text=${msg}`, '_blank');
    },
};

/* Inicializa ao carregar */
document.addEventListener('DOMContentLoaded', () => {
    Cart.render();
    Cart.updateCount();

    /* Toggle abrir */
    document.querySelectorAll('#cartToggle').forEach(btn =>
        btn.addEventListener('click', () => Cart.open())
    );

    /* Fechar */
    document.getElementById('cartClose')?.addEventListener('click',   () => Cart.close());
    document.getElementById('cartOverlay')?.addEventListener('click', () => Cart.close());

    /* Checkout WhatsApp */
    document.getElementById('cartCheckout')?.addEventListener('click', () => Cart.checkout());

    /* Limpar */
    document.getElementById('cartClear')?.addEventListener('click', () => {
        if (confirm('Limpar todos os itens do carrinho?')) Cart.clear();
    });

    /* Delegacao: +/- e remover */
    document.getElementById('cartItems')?.addEventListener('click', e => {
        const btn  = e.target.closest('[data-action],[data-name]');
        if (!btn) return;
        const name = btn.dataset.name;
        const act  = btn.dataset.action;
        const items = Cart.load();
        const item  = items.find(i => i.name === name);
        if (!item) return;
        if (act === 'inc') Cart.setQty(name, item.qty + 1);
        if (act === 'dec') Cart.setQty(name, item.qty - 1);
        if (!act)          Cart.remove(name); // botao remover (so tem data-name)
    });

    /* Botoes "Comprar" / "Adicionar ao carrinho" */
    document.querySelectorAll('.btn-comprar').forEach(btn => {
        btn.addEventListener('click', function () {
            const name  = this.dataset.product;
            const price = this.dataset.price;
            const emoji = this.dataset.emoji || '🐾';

            Cart.add(name, price, emoji);

            const orig = this.innerHTML;
            this.innerHTML = '<i class="fas fa-check"></i> Adicionado!';
            this.disabled  = true;
            setTimeout(() => {
                this.innerHTML = orig;
                this.disabled  = false;
            }, 1800);

            Cart.open();
        });
    });
});

/* =============================================
   MOBILE NAV
   ============================================= */
document.getElementById('navToggle')?.addEventListener('click', () => {
    document.getElementById('navMenu')?.classList.toggle('open');
});

document.querySelectorAll('.nav-link').forEach(link =>
    link.addEventListener('click', () =>
        document.getElementById('navMenu')?.classList.remove('open')
    )
);

/* =============================================
   SMOOTH SCROLL
   ============================================= */
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

/* =============================================
   ALERT AUTO-DISMISS
   ============================================= */
document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
        alert.style.transition = 'opacity .5s';
        alert.style.opacity    = '0';
        setTimeout(() => alert.remove(), 500);
    }, 4000);
});

/* =============================================
   FORMULARIO DE AGENDAMENTO (API)
   ============================================= */
const agendamentoForm = document.getElementById('agendamentoForm');
if (agendamentoForm) {
    agendamentoForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn      = this.querySelector('[type="submit"]');
        const original = btn.innerHTML;
        btn.innerHTML  = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
        btn.disabled   = true;

        const data = Object.fromEntries(new FormData(this));
        const res  = await fetch('/Site-pet/api/agendamento.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data),
        });

        const json    = await res.json();
        const alertEl = document.createElement('div');
        alertEl.className = 'alert ' + (json.success ? 'alert-success' : 'alert-error');
        alertEl.innerHTML = '<i class="fas fa-' + (json.success ? 'check-circle' : 'exclamation-circle') + '"></i> ' + json.message;
        this.insertAdjacentElement('beforebegin', alertEl);

        if (json.success) this.reset();
        btn.innerHTML = original;
        btn.disabled  = false;
    });
}
