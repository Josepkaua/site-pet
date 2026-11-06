# Site Pet – Clínica Veterinária Dra. Milena Paiva

Site institucional + agendamentos + loja pet, em PHP 8.2 (Apache, Docker), com banco no **Nhost/Hasura**.

## ⚠️ Configuração obrigatória (variáveis de ambiente)

Os segredos **não ficam mais no código**. Configure no Render → *Environment*:

| Variável | Obrigatória | Para quê |
|---|---|---|
| `NHOST_ADMIN_SECRET` | sim | Acesso ao banco (Hasura). Sem ela o site funciona com dados de exemplo e não salva agendamentos. |
| `ADMIN_PASSWORD_HASH` | **muito recomendada** | Senha do painel admin. Sem ela vale a senha de demonstração e o painel mostra um aviso. |
| `ADMIN_EMAIL` | não | E-mail de login do admin (padrão `milena@clinicavet.com.br`). |
| `NHOST_SUBDOMAIN` / `NHOST_REGION` | não | Já têm o valor atual como padrão. |
| `DEMO_PATIENT` | não | `0` desativa o usuário de demonstração `joao@email.com`. |

Gerar o hash da senha do admin:

```bash
php -r "echo password_hash('SuaSenhaForteAqui', PASSWORD_DEFAULT), PHP_EOL;"
```

Para rodar localmente sem variáveis de ambiente, crie `includes/config.local.php` (ignorado pelo git):

```php
<?php
define('NHOST_ADMIN_SECRET', 'seu-secret');
```

## Estrutura

```
index.php                 Página inicial
404.php                   Página de erro
pages/                    Páginas públicas (serviços, consultas, banho & tosa, loja, contato, login)
pages/admin/              Painel da clínica (agendamentos, mensagens, horários, preços, produtos, dados)
pages/painel/             Área do paciente
api/agendamento.php       API JSON de agendamento (exige token CSRF)
api/setup_db.php          Cria/atualiza tabelas – roda só pela linha de comando (start.sh)
includes/
  bootstrap.php           Incluído em toda página: cabeçalhos de segurança, sessão, CSRF
  config.php              Configuração + dados da clínica (lê data/info.json) + gql()
  helpers.php             Funções comuns: e(), validação, rate limit, horários, preços, produtos
  auth.php                Login/sessão
  header.php / footer.php Layout público  ·  partials/cart.php  Carrinho
  admin_layout*.php       Layout do painel
data/                     JSON editados pelo painel (horários, preços, info, visitas) – bloqueado na web
assets/                   CSS, JS e imagens (WebP + JPG otimizados)
```

Horários, preços e dados da clínica editados no painel aparecem **automaticamente** em todo o site
(rodapé, páginas de serviços, banho & tosa, contato, consultas).

## Segurança implementada

- Segredo do banco e senha do admin via variáveis de ambiente
- Token CSRF automático em todos os formulários POST
- Limite de tentativas de login (5 a cada 15 min) e de envios de formulário; campo-isca anti-robô
- Validação no servidor de todos os campos (datas, telefone, e-mail, listas fechadas)
- Saída sempre escapada (`e()`), sem injeção nas consultas GraphQL (somente variáveis)
- Sessão com cookies `HttpOnly`/`SameSite`/`Secure`, expiração por inatividade, `session_regenerate_id`
- Cabeçalhos: CSP, X-Frame-Options, nosniff, Referrer-Policy, HSTS
- `includes/`, `data/`, `.sql`, `.sh` e `setup_db.php` bloqueados na web; verificação de SSL ativada

## Rodar localmente

```bash
php -S 127.0.0.1:8000
```
