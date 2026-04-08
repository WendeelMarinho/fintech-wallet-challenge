# Fintech Wallet MVP

Projeto de desafio técnico com foco em simplicidade, clareza e funcionamento.

## Visão geral

- Registro, login e logout com Sanctum
- Consulta de saldo da carteira do usuário autenticado
- Transferência entre usuários por e-mail
- Histórico paginado com filtro por tipo e período

## Decisões técnicas

- Laravel 10 + Eloquent direto
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

Erro:

```json
{
  "success": false,
  "message": "mensagem clara"
}
```

## Como rodar

### Backend

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configurar banco no `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fintech_wallet
DB_USERNAME=root
DB_PASSWORD=secret
```

Rodar migrações e seed:

```bash
php artisan migrate --seed
php artisan serve
```

### Frontend

```bash
npm install
npm run dev
```

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

## Deploy

- Backend: `https://backend-placeholder.example.com`
- Frontend: `https://frontend-placeholder.example.com`
