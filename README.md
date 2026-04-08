# Fintech Wallet MVP

Projeto de desafio técnico com foco em simplicidade, clareza e funcionamento.

## Visão geral

- Registro, login e logout com Sanctum
- Consulta de saldo da carteira do usuário autenticado
- Transferência entre usuários por e-mail
- Histórico paginado com filtro por tipo e período

## Decisões técnicas

- Laravel 11 + Eloquent direto (middleware global em `bootstrap/app.php`; não há `app/Http/Kernel.php` como nos releases antigos)
- Controller simples: valida, orquestra e responde
- Apenas um service principal: `TransferService` (onde a regra de transferência fica)
- Não usei `AuthService`, `WalletService` ou `TransactionService` para evitar abstração sem ganho
- Sem Repository, DTO ou camadas extras
- `DB::transaction` + `lockForUpdate` para garantir consistência na transferência

## Arquitetura do backend

Estrutura usada:

- `app/Http/Controllers`: camada HTTP
- `app/Http/Requests`: validação de entrada
- `app/Models`: persistência com Eloquent
- `app/Services/TransferService.php`: regra crítica de transferência

Ponto importante: somente a transferência foi para service porque é a parte com regra de negócio sensível.  
Login, saldo e histórico ficaram no controller por serem fluxos diretos.

## Regras de transferência

No `TransferService`:

- valida valor maior que zero
- valida destinatário existente
- impede transferência para si mesmo
- valida saldo suficiente
- executa em transação única (`DB::transaction`)
- trava carteiras com `lockForUpdate`
- grava duas transações (`debit` e `credit`)

Se qualquer passo falhar, a operação inteira é revertida.

## Endpoints da API

Públicos:

- `POST /api/register`
- `POST /api/login`

Protegidos por Sanctum:

- `POST /api/logout`
- `GET /api/me`
- `GET /api/wallet`
- `POST /api/transfer`
- `GET /api/transactions`
- `GET /api/transactions/latest`

## Padrão de resposta

Sucesso:

```json
{
  "success": true,
  "data": {},
  "message": null
}
```

Erro (validação em `api/*`, HTTP 422):

```json
{
  "success": false,
  "message": "Primeira mensagem de validação",
  "errors": {
    "campo": ["mensagem"]
  }
}
```

Outros erros da API podem seguir só `success` + `message`, conforme o controller.

## Como rodar

### Requisitos

- **PHP 8.2+** (mbstring, openssl, pdo, tokenizer, xml, ctype, json, curl, **pdo_mysql**, etc.)
- **MySQL 8+** (ou compatível) rodando e acessível
- **Composer 2**
- **Node.js 18+** e npm

No **WSL/Ubuntu**, se `php -v` for **7.x**, instale PHP 8.2+ (ex.: PPA [Ondřej Surý](https://launchpad.net/~ondrej/+archive/ubuntu/php)):

```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.2-cli php8.2-mbstring php8.2-xml php8.2-curl php8.2-mysql php8.2-sqlite3 php8.2-zip -y
sudo update-alternatives --set php /usr/bin/php8.2
php -v
```

O **Vite** costuma usar a porta **5173**; o **Laravel** (`php artisan serve`), **8000**. O proxy em `vite.config.js` encaminha `/api` para o backend.

---

### Passo a passo

1. **Abra o terminal na pasta do projeto** (ex.: `cd ~/projetos/fintech-wallet` no WSL).

2. **Garanta PHP 8.2+:** `php -v` deve mostrar 8.2 ou superior.

3. **Suba o MySQL** (serviço local, Docker, etc.) e **crie o banco:**

   ```sql
   CREATE DATABASE fintech_wallet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

4. **Configure o `.env`:**
   - Se não existir: `cp .env.example .env`
   - Gere a chave da aplicação: `php artisan key:generate`
   - Ajuste **DB_HOST**, **DB_DATABASE**, **DB_USERNAME** e **DB_PASSWORD** para bater com o seu MySQL (o padrão do exemplo é `fintech_wallet`, usuário `root`).

5. **Instale dependências PHP:**

   ```bash
   composer install
   ```

6. **Crie as tabelas e dados iniciais:**

   ```bash
   php artisan migrate --seed
   ```

7. **(Opcional) Rode os testes:** usam SQLite em memória, não o MySQL do `.env`.

   ```bash
   php artisan test
   ```

8. **Inicie o backend** (deixe este terminal aberto):

   ```bash
   php artisan serve
   ```

   A API ficará em `http://127.0.0.1:8000` (ou a URL que o comando mostrar).

9. **Abra outro terminal** na mesma pasta, **instale o frontend** (só na primeira vez ou após mudar dependências):

   ```bash
   npm install
   ```

10. **Inicie o Vite:**

    ```bash
    npm run dev
    ```

11. **Abra no navegador** a URL que o Vite indicar (ex.: `http://localhost:5173`). O SPA valida e chama a API via `/api`, que o proxy envia para o Laravel.

12. **Entrar no sistema:** use as credenciais do seed (**`test@wallet.com`** / **`password`**) ou registre um novo usuário pela tela de cadastro.

## Frontend (Vue + Pinia)

- `authStore`: token + usuário
- `walletStore`: saldo
- `transactionStore`: lista, paginação e filtros
- `TransferForm`: envio de transferência com feedback
- `TransactionList`: tabela de histórico

Fluxo da tela:

- Dashboard mostra saldo e últimas 5 transações
- Transactions mostra lista paginada com filtros por tipo e período

## Testes

```bash
php artisan test
```

Cenários cobertos em `tests/Feature/WalletFlowTest.php`:

- usuário criado com carteira
- transferência com sucesso
- transferência sem saldo
- transferência com valor inválido
- rollback em falha

## Credenciais de teste

- Email: `test@wallet.com`
- Senha: `password`

## Repositório e `.gitignore`

- Sobe para o GitHub **somente** o **`README.md`** entre arquivos Markdown (`*.md` ignorados, com exceção explícita do README).
- Relatórios ou notas em `.md` (ex.: rascunhos técnicos) permanecem **apenas na sua máquina**, a menos que você mude o `.gitignore`.
- **`.env` e variantes** (`.env.local`, `.env.backup`, etc.) estão ignoradas; **`.env.example`** é o modelo versionado — copie para `.env` e preencha localmente. Confira antes do push: `git status` não deve listar `.env`.

## Variáveis do `.env` (referência rápida)

Copie de `.env.example` e ajuste no mínimo:

| Variável | Uso |
|----------|-----|
| `APP_KEY` | Gerar com `php artisan key:generate` |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Conexão MySQL |

Demais chaves seguem o `.env.example` (sessão, cache, Sanctum, etc.).

## Conformidade com o desafio (Laravel + Vue)

Checklist em relação ao escopo **Carteira Digital P2P** (Teck Soluções):

| Requisito | Status |
|-----------|--------|
| Registro (nome, e-mail, senha), login, logout | OK |
| Rotas protegidas com **Laravel Sanctum** | OK |
| Carteira com saldo inicial **R$ 1.000,00** (seeder + registro) | OK |
| Dashboard com saldo | OK |
| Transferência por e-mail + valor; saldo suficiente; valor &gt; 0; transação atômica; histórico débito/crédito | OK (`TransferService` + `DB::transaction` + `lockForUpdate`) |
| Histórico paginado; data, tipo, valor, usuário envolvido; filtro tipo e período | OK |
| Vue 3 **Composition API**, Axios, **Pinia**, **Vue Router**, feedback de erro na UI | OK |
| Laravel **10+** (projeto em **11**), API JSON, migrations + seeders, **Form Requests**, tratamento de erros HTTP | OK |
| **Service** para regra de negócio da transferência (controller fino) | OK |
| **≥ 5 testes** Feature relevantes | OK (`WalletFlowTest`) |
| Banco **MySQL** (ou Postgres; aqui MySQL) | OK (MySQL no `.env` de produção/dev) |
| README: visão geral, decisões, como rodar, **link do deploy**, credenciais seed | **Substituir placeholders** em «Deploy» pelos links reais antes da entrega |

Itens da entrega ainda por conta do processo de deploy: **repositório público GitHub**, **deploy funcional** em plataforma pública e **links no README** (o desafio pede backend + frontend acessíveis; pode ser um único domínio ou API + SPA conforme a plataforma).

## Checklist mínimo do README (desafio)

- [x] Descrição breve e decisões técnicas  
- [x] Pré-requisitos (PHP, Node, MySQL)  
- [x] Instalar dependências (`composer` / `npm`)  
- [x] Configurar `.env` (tabela acima + `.env.example`)  
- [x] Migrations e seeders  
- [x] Subir backend e frontend  
- [ ] **Link público do deploy** (preencher abaixo)  
- [x] Credenciais seed (`test@wallet.com` / `password`)  

## Frontend: desenvolvimento e atualizações

- Código em `resources/js/` (páginas, componentes, Pinia, `api.js`, estilos).
- **Local:** `npm run dev` — o proxy do Vite encaminha `/api` para o Laravel (`php artisan serve`).
- **Alterar dependências:** atualize `package.json`, rode `npm install` e faça commit de `package.json` e `package-lock.json`.
- **Produção (build estático):** `npm run build` gera a pasta **`dist/`** na raiz. Publique o conteúdo de `dist/` em Netlify, Vercel, Cloudflare Pages, S3+CDN, etc.
- **API noutro domínio:** antes do build, defina no `.env` (ou no painel da plataforma) a variável **`VITE_API_URL`** com a URL **completa** da API, **incluindo** o sufixo `/api`, por exemplo `https://api.seuprojeto.com/api`. O `resources/js/api.js` usa isso em tempo de build; sem variável, mantém `/api` (mesma origem).
- **Rotas do Vue Router:** em hospedagem estática, configure **fallback para `index.html`** (ex.: Netlify `_redirects` com `/* /index.html 200`, ou rewrites na Vercel) para `/transactions` e similares abrirem direto no browser.

## Deploy na prática (desafio)

Combinações comuns:

1. **API (Railway, Render, Fly.io, etc.)** — serviço PHP: `composer install`, `php artisan migrate --force`, variáveis `APP_KEY`, `DB_*`, `APP_URL`. Expor HTTPS; o CORS do projeto já permite origens amplas para chamadas com Bearer token.
2. **SPA (Vercel / Netlify)** — build com `VITE_API_URL` apontando para a API; fazer deploy da pasta `dist/`.
3. **Um só serviço** — possível servir `dist/` através do Laravel (rotas + `public/`), exigindo ajuste de `vite.config` e rotas web; não está pré-configurado neste MVP.

Após publicar, substitua os placeholders abaixo pelos URLs reais.

## Deploy

- **API (backend):** `https://backend-placeholder.example.com`  
- **Frontend (SPA):** `https://frontend-placeholder.example.com`  

*(Se usar um único host para API + assets, indique um link e explique numa linha.)*

## Commit e push (local)

Da raiz do repositório, no WSL ou Git Bash:

```bash
chmod +x scripts/commit-and-push.sh
./scripts/commit-and-push.sh
```

Ou manualmente: `git add -A`, desfaça stage de qualquer `.env*` exceto `.env.example`, `git status`, `git commit`, `git push`.
