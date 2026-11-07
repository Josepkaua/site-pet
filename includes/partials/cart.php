<div class="cart-overlay" id="cartOverlay"></div>
<aside class="cart-sidebar" id="cartSidebar" aria-label="Carrinho" aria-hidden="true">
    <div class="cart-sidebar-header">
        <h3><i class="fas fa-shopping-cart"></i> Meu Carrinho</h3>
        <button class="cart-close" id="cartClose" type="button" aria-label="Fechar carrinho"><i class="fas fa-times"></i></button>
    </div>
    <div class="cart-items" id="cartItems">
        <div class="cart-empty" id="cartEmpty">
            <i class="fas fa-shopping-basket"></i>
            <p>Seu carrinho está vazio</p>
            <small>Adicione produtos da <a href="/pages/loja.php">Loja Pet</a> para começar</small>
        </div>
    </div>
    <div class="cart-footer" id="cartFooter" hidden>
        <div class="cart-total-row">
            <span>Total</span>
            <strong id="cartTotal">R$ 0,00</strong>
        </div>
        <button class="btn btn-primary btn-block" type="button" id="cartCheckout">
            <i class="fab fa-whatsapp"></i> Finalizar pelo WhatsApp
        </button>
        <button class="btn btn-outline btn-block" type="button" id="cartClear">
            <i class="fas fa-trash"></i> Limpar carrinho
        </button>
    </div>
</aside>
