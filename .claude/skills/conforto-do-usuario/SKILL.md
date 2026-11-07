---
name: conforto-do-usuario
description: Regras de conforto e tamanho de página do site da clínica. Use SEMPRE que criar ou alterar qualquer página, seção, lista, tabela ou estilo do site (público ou admin), principalmente pensando no celular – o visitante não deve precisar rolar a página inteira.
---

# Conforto do usuário – páginas curtas e fáceis no celular

O dono do site pediu: **"lembre de manter o conforto da pessoa — imagina a pessoa precisar
fazer a rolagem da página todinha? Muito grande, ainda mais pro celular."**
Toda mudança no site deve respeitar as regras abaixo.

## Metas de tamanho (medir antes de entregar)

| Página | Celular (iPhone 13, 390×664) | Desktop (1366×800) |
|---|---|---|
| Início | até ~4 telas | até ~3,5 telas |
| Serviços, Loja | até ~4 telas | até ~4,5 telas |
| Consultas, Banho & Tosa, Contato | até ~3 telas | até ~3,5 telas |

"Telas" = `document.documentElement.scrollHeight / innerHeight`. Se uma mudança passar da
meta, compacte antes de entregar. Também nunca pode haver rolagem horizontal (testar 320, 375,
414, 768 e 1024 px).

## Como compactar (padrões que já existem no projeto)

1. **Tabelas longas (preços, horários, dicas) → bloco recolhível**
   Use `<details class="acc" open>` com `<summary>` como cabeçalho e a seta
   `<i class="fas fa-chevron-down acc-arrow"></i>`. No celular o `main.js` fecha todos
   automaticamente (`initAccordions`); no desktop ficam abertos.
   Adicione `data-keep-open` só se o bloco for essencial para a tarefa da página.
   Mostre a quantidade no título com `<small class="acc-count">N itens</small>`.
2. **Listas de cartões → 2 colunas no celular**, só ícone + título; esconda descrições longas
   (`display:none` dentro do `@media (max-width: 640px)`).
3. **Muitos itens lado a lado → faixa deslizante** com a classe `scroll-strip`
   (scroll horizontal com snap) em vez de empilhar tudo.
4. **Filtros/categorias → linha que desliza** (`.chip-row` sem quebra de linha).
5. **Atalhos no topo** de páginas longas (`.page-jump` / `.hero-jump`) para ir direto a
   "Horários", "Preços" ou ao formulário.
6. **Espaçamento no celular**: seções `36px`, títulos `~1.35rem`, cabeçalho de página curto.
   Não repita informação (ex.: foto que já aparece no topo não aparece de novo mais abaixo
   no celular; rodapé sem a lista de links que já está no menu).
7. **Formulários**: 2 colunas no celular a partir de 360 px, campos com `font-size: 16px`
   (evita o zoom automático do iPhone), textarea baixa.

## Conforto além do tamanho

- Toque fácil: botões e links com pelo menos ~40 px de altura.
- Texto legível: nada abaixo de ~11 px; contraste bom.
- Animações suaves e curtas; sempre respeitar `prefers-reduced-motion` (já está no CSS).
- Nada de pop-up que bloqueie a tela; avisos usam o toast (`toast()` em `main.js`).
- O botão de WhatsApp flutuante não pode cobrir campo de formulário ou botão importante.

## Como verificar

Rode o servidor (`php -S 127.0.0.1:8099`) e meça com Playwright, rolando a página antes do
print para disparar as animações de entrada (`.reveal`). Compare a altura com a tabela de
metas e confira as capturas no celular antes de enviar.
