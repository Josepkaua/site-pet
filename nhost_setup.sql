-- ================================================================
-- NHOST / HASURA – Setup de Tabelas
-- Clínica Veterinária Dra. Milena Paiva
--
-- COMO USAR:
--  1. Acesse seu projeto em app.nhost.io
--  2. Vá em: Database → SQL Editor
--  3. Cole este script inteiro e clique em "Run"
--  4. Depois vá em: Hasura Console → Data → Track all tables
-- ================================================================

-- ---------------------------------------------------------------
-- 1. AGENDAMENTOS DE CONSULTA
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS public.agendamentos (
    id          BIGSERIAL PRIMARY KEY,
    nome        TEXT        NOT NULL,
    telefone    TEXT        NOT NULL,
    email       TEXT,
    pet_nome    TEXT        NOT NULL,
    pet_especie TEXT,
    pet_raca    TEXT,
    servico     TEXT,
    data        DATE        NOT NULL,
    horario     TEXT,
    obs         TEXT,
    status      TEXT        NOT NULL DEFAULT 'pendente',
    criado_em   TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

COMMENT ON TABLE public.agendamentos IS 'Agendamentos de consultas veterinárias';
COMMENT ON COLUMN public.agendamentos.status IS 'pendente | confirmado | cancelado | concluido';

-- ---------------------------------------------------------------
-- 2. AGENDAMENTOS DE BANHO & TOSA
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS public.tosagem_agendamentos (
    id          BIGSERIAL PRIMARY KEY,
    nome        TEXT        NOT NULL,
    telefone    TEXT        NOT NULL,
    pet_nome    TEXT        NOT NULL,
    pet_raca    TEXT,
    pet_porte   TEXT,
    servico     TEXT,
    data        DATE        NOT NULL,
    horario     TEXT,
    status      TEXT        NOT NULL DEFAULT 'pendente',
    criado_em   TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

COMMENT ON TABLE public.tosagem_agendamentos IS 'Agendamentos de banho e tosa';

-- ---------------------------------------------------------------
-- 3. MENSAGENS DE CONTATO
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS public.contatos (
    id          BIGSERIAL PRIMARY KEY,
    nome        TEXT        NOT NULL,
    email       TEXT        NOT NULL,
    assunto     TEXT,
    mensagem    TEXT        NOT NULL,
    lido        BOOLEAN     NOT NULL DEFAULT FALSE,
    criado_em   TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

COMMENT ON TABLE public.contatos IS 'Mensagens enviadas pelo formulário de contato';

-- ---------------------------------------------------------------
-- 4. PRODUTOS DA LOJA
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS public.produtos (
    id          BIGSERIAL PRIMARY KEY,
    nome        TEXT           NOT NULL,
    descricao   TEXT,
    preco       NUMERIC(10,2)  NOT NULL,
    categoria   TEXT,
    emoji       TEXT,
    estoque     INT            NOT NULL DEFAULT 0,
    ativo       BOOLEAN        NOT NULL DEFAULT TRUE,
    criado_em   TIMESTAMPTZ    NOT NULL DEFAULT NOW()
);

COMMENT ON TABLE public.produtos IS 'Produtos disponíveis na loja pet';

-- ---------------------------------------------------------------
-- 5. DADOS INICIAIS – PRODUTOS
-- ---------------------------------------------------------------
INSERT INTO public.produtos (nome, descricao, preco, categoria, emoji, estoque) VALUES
('Ração Premium Cão Adulto 15kg', 'Alta digestibilidade, fórmula completa e balanceada.',      189.90, 'racao',    '🥩', 50),
('Ração Premium Gato 3kg',        'Rico em ômega-3, pelo brilhante e digestão saudável.',      79.90,  'racao',    '🐟', 30),
('Petisco Natural Cão 200g',      'Snack natural sem aditivos, ideal para treinos.',            24.90,  'petisco',  '🦴', 100),
('Petisco Gato Atum 60g',         'Lanche nutritivo para felinos exigentes.',                  12.90,  'petisco',  '🐠', 80),
('Escova de Remoção de Pelos',    'Reduz a queda de pelos em até 90%. Ergonômica.',            54.90,  'acessorio','🪮', 25),
('Bola Interativa com Apito',     'Brinquedo resistente para cães de todos os portes.',        34.90,  'brinquedo','🎾', 40),
('Shampoo Neutro Pet 500ml',      'Fórmula suave para banhos frequentes.',                    29.90,  'higiene',  '🛁', 60),
('Vermífugo Comprimido (4cp)',     'Eficaz contra lombrigas e outros parasitas internos.',     38.90,  'saude',    '💊', 45),
('Caminha Pet Tamanho M',         'Confortável e lavável, perfeita para cães e gatos.',       119.90, 'acessorio','🏠', 20),
('Colônia Pet Floral 100ml',      'Fragrância suave que dura até 48h.',                       22.90,  'higiene',  '🧴', 35),
('Anti-pulgas Pipeta Mensal',     'Proteção de 30 dias contra pulgas e carrapatos.',           45.90,  'saude',    '🐾', 55),
('Kit Lacinhos & Gravatas',       'Acessórios fashion para deixar o pet ainda mais fofo.',    19.90,  'acessorio','🎀', 70)
ON CONFLICT DO NOTHING;

-- ================================================================
-- APÓS RODAR O SQL:
--  Hasura Console (app.nhost.io → seu projeto → Hasura)
--    → Data → public → clique em cada tabela → "Track"
--    OU: Data → clique "Track all" no schema public
-- ================================================================
