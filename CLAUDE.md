# Site Pet – instruções para o Claude

- Site em PHP 8.2 (sem framework), deploy no Render via Docker. Estrutura e variáveis de ambiente: veja `README.md`.
- Toda página pública começa com `includes/bootstrap.php` e usa `includes/header.php` / `includes/footer.php`.
- Escape toda saída com `e()`; nunca concatene entrada do usuário em GraphQL (use variáveis em `gql()`).
- **Conforto do usuário é prioridade:** ao criar ou mudar qualquer página ou estilo, siga a skill
  `.claude/skills/conforto-do-usuario/SKILL.md` – páginas curtas, principalmente no celular, sem rolagem horizontal.
- Responda ao dono do projeto em português do Brasil.
