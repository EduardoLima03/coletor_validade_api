# ValiCheck - Medeiros — Documentação de Rotas

---

## Sumário

1. [Autenticação (Web)](#1-autenticação-web)
2. [Administrativo (Web)](#2-administrativo-web)
3. [API Pública (Sem Token)](#3-api-pública-sem-token)
4. [API Restrita (Sanctum + Role)](#4-api-restrita-sanctum--role)

---

## 1. Autenticação (Web)

| Método | Rota | Nome | Controller | Middleware |
|--------|------|------|-----------|------------|
| GET | `/login` | `admin.login.form` | `AuthController@showLoginForm` | — |
| POST | `/login` | `admin.login` | `AuthController@login` | — |
| POST | `/admin/logout` | `admin.logout` | `AuthController@logout` | `auth`, `role:GERENCIA,ADMIN` |

### GET `/login`
Exibe o formulário de login.

### POST `/login`
Autentica o usuário via credenciais `email` + `password`.

| Campo | Tipo | Obrigatório | Validação |
|-------|------|-------------|-----------|
| `email` | string | sim | email, max:255 |
| `password` | string | sim | min:6 |

**Resposta:** redireciona para `/admin/produtos` em caso de sucesso, ou retorna `back()` com erro `Credenciais inválidas.`.

### POST `/admin/logout`
Remove a sessão do usuário autenticado e redireciona para `/login`.

---

## 2. Administrativo (Web)

Todas as rotas abaixo estão no prefixo `/admin` e exigem `auth` + `role:GERENCIA,ADMIN`.

### 2.1 Perfil

| Método | Rota | Nome | Controller |
|--------|------|------|-----------|
| GET | `/admin/perfil` | `admin.profile.edit` | `ProfileController@edit` |
| PUT | `/admin/perfil` | `admin.profile.update` | `ProfileController@update` |

#### PUT `/admin/perfil`
| Campo | Tipo | Obrigatório | Validação |
|-------|------|-------------|-----------|
| `name` | string | sim | max:255 |
| `password` | string | não | min:6, confirmed |

**Resposta:** redireciona com `success`.

### 2.2 Produtos

| Método | Rota | Nome | Controller |
|--------|------|------|-----------|
| GET | `/admin/produtos` | `admin.products.index` | `Web\ProductController@index` |
| GET | `/admin/produtos/create` | `admin.products.create` | `Web\ProductController@create` |
| POST | `/admin/produtos` | `admin.products.store` | `Web\ProductController@store` |
| GET | `/admin/produtos/{id}` | `admin.products.show` | `Web\ProductController@show` |
| GET | `/admin/produtos/{id}/edit` | `admin.products.edit` | `Web\ProductController@edit` |
| PUT | `/admin/produtos/{id}` | `admin.products.update` | `Web\ProductController@update` |
| DELETE | `/admin/produtos/{id}` | `admin.products.destroy` | `Web\ProductController@destroy` |

**Parâmetros de filtro (index):**
- `?search=` — busca por `code`, `description` ou `barcodes.ean`

**Validação (store/update):**
| Campo | Tipo | Obrigatório | Validação |
|-------|------|-------------|-----------|
| `code` | integer | sim | unique:products,code |
| `description` | string | sim | max:255 |

Registra auditoria em todas as ações.

### 2.3 Códigos de Barras (EAN)

| Método | Rota | Nome | Controller |
|--------|------|------|-----------|
| GET | `/admin/barcodes` | `admin.barcodes.index` | `Web\BarcodeController@index` |
| GET | `/admin/barcodes/create` | `admin.barcodes.create` | `Web\BarcodeController@create` |
| POST | `/admin/barcodes` | `admin.barcodes.store` | `Web\BarcodeController@store` |
| GET | `/admin/barcodes/{id}` | `admin.barcodes.show` | `Web\BarcodeController@show` |
| GET | `/admin/barcodes/{id}/edit` | `admin.barcodes.edit` | `Web\BarcodeController@edit` |
| PUT | `/admin/barcodes/{id}` | `admin.barcodes.update` | `Web\BarcodeController@update` |
| DELETE | `/admin/barcodes/{id}` | `admin.barcodes.destroy` | `Web\BarcodeController@destroy` |

**Parâmetros de filtro (index):**
- `?search=` — busca por `ean`, `product.code` ou `product.description`

**Validação (store/update):**
| Campo | Tipo | Obrigatório | Validação |
|-------|------|-------------|-----------|
| `ean` | integer | sim | unique:barcodes,ean |
| `product_id` | integer | sim | exists:products,id |

Registra auditoria em todas as ações.

### 2.4 Importação CSV

| Método | Rota | Nome | Controller |
|--------|------|------|-----------|
| GET | `/admin/importar` | `admin.import.form` | `Web\ImportController@showForm` |
| POST | `/admin/importar/processar` | `admin.import.process` | `Web\ImportController@processFile` |

#### POST `/admin/importar/processar`
Lê o arquivo `VALIDADE.csv` (ou faz upload) com formato `COD,DESCRICAO,EAN`.

- Processa em lotes de 200 registros com transação e `keepAlive` para evitar queda de conexão MySQL
- `Product::updateOrCreate(['code' => $cod], ['description' => $desc])`
- `Barcode::firstOrNew(['ean' => $ean])` — vincula ao produto; se já existe, relink se produto mudou
- Retorna estatísticas de criados/atualizados/pulados/erros

Registra auditoria.

### 2.5 Lojas

| Método | Rota | Nome | Controller |
|--------|------|------|-----------|
| GET | `/admin/lojas` | `admin.lojas.index` | `Web\LojaController@index` |
| GET | `/admin/lojas/create` | `admin.lojas.create` | `Web\LojaController@create` |
| POST | `/admin/lojas` | `admin.lojas.store` | `Web\LojaController@store` |
| GET | `/admin/lojas/{loja}/edit` | `admin.lojas.edit` | `Web\LojaController@edit` |
| PUT | `/admin/lojas/{loja}` | `admin.lojas.update` | `Web\LojaController@update` |
| DELETE | `/admin/lojas/{loja}` | `admin.lojas.destroy` | `Web\LojaController@destroy` |

**Validação:**
| Campo | Tipo | Obrigatório | Validação |
|-------|------|-------------|-----------|
| `nome` | string | sim | max:255, unique:lojas,nome |

### 2.6 Coletas

| Método | Rota | Nome | Controller |
|--------|------|------|-----------|
| GET | `/admin/coletas` | `admin.coletas.index` | `Web\ColetaController@index` |
| GET | `/admin/coletas/{coleta}/edit` | `admin.coletas.edit` | `Web\ColetaController@edit` |
| PUT | `/admin/coletas/{coleta}` | `admin.coletas.update` | `Web\ColetaController@update` |
| DELETE | `/admin/coletas/{coleta}` | `admin.coletas.destroy` | `Web\ColetaController@destroy` |

**Parâmetros de filtro (index):**
- `?loja_id=` — filtrar por loja
- `?dias=` — coletas com vencimento até N dias (5/7/12/15/20)
- `?data_inicio=` — data inicial (Y-m-d)
- `?data_fim=` — data final (Y-m-d)

**Validação (update):**
| Campo | Tipo | Obrigatório | Validação |
|-------|------|-------------|-----------|
| `quantidade` | integer | sim | min:1 |
| `data_validade` | date | sim | formato Y-m-d |

### 2.7 Usuários (somente ADMIN)

| Método | Rota | Nome | Controller |
|--------|------|------|-----------|
| GET | `/admin/users` | `admin.users.index` | `Web\UserController@index` |
| GET | `/admin/users/create` | `admin.users.create` | `Web\UserController@create` |
| POST | `/admin/users` | `admin.users.store` | `Web\UserController@store` |
| GET | `/admin/users/{id}` | `admin.users.show` | `Web\UserController@show` |
| GET | `/admin/users/{id}/edit` | `admin.users.edit` | `Web\UserController@edit` |
| PUT | `/admin/users/{id}` | `admin.users.update` | `Web\UserController@update` |
| DELETE | `/admin/users/{id}` | `admin.users.destroy` | `Web\UserController@destroy` |

**Validação (store):**
| Campo | Tipo | Obrigatório | Validação |
|-------|------|-------------|-----------|
| `name` | string | sim | max:255 |
| `email` | email | sim | unique:users,email |
| `position` | string | sim | in:ADMIN,GERENCIA,COLETOR |
| `password` | string | sim | min:6, confirmed |

**Validação (update):**
| Campo | Tipo | Obrigatório | Validação |
|-------|------|-------------|-----------|
| `name` | string | sim | max:255 |
| `email` | email | sim | unique:users,email,{id} |
| `position` | string | sim | in:ADMIN,GERENCIA,COLETOR |
| `password` | string | não | min:6, confirmed |

### 2.8 Auditoria (somente ADMIN)

| Método | Rota | Nome | Controller |
|--------|------|------|-----------|
| GET | `/admin/auditoria` | `admin.audit.index` | `Web\AuditController@index` |
| GET | `/admin/auditoria/{id}` | `admin.audit.show` | `Web\AuditController@show` |

**Parâmetros de filtro (index):**
- `?search=` — busca em `description`, `entity_type`, `action`
- `?user_id=` — filtrar por usuário
- `?action=` — filtrar por ação (create, update, delete, login, etc.)
- `?date_from=` — data inicial (Y-m-d)
- `?date_to=` — data final (Y-m-d)

---

## 3. API Pública (Sem Token)

| Método | Rota | Controller |
|--------|------|-----------|
| POST | `/api/login` | `UserController@login` |
| POST | `/api/user` | `UserController@store` |

### POST `/api/login`
Autentica o usuário e retorna um token Sanctum.

**Request Body:**
```json
{
  "email": "carlos@medeiros.api",
  "password": "3012api"
}
```

**Response 200:**
```json
{
  "token": "1|abc123...",
  "user": {
    "id": 2,
    "name": "Carlos Lima",
    "email": "carlos@medeiros.api",
    "position": "ADMIN"
  }
}
```

**Response 401:**
```
Usuario invalido
```

### POST `/api/user`
Cria um novo usuário. Não requer autenticação.

**Request Body:**
```json
{
  "name": "Nome",
  "email": "email@exemplo.com",
  "password": "123456",
  "position": "COLETOR"
}
```

**Response 200:**
```json
[{ "id": 5, "name": "Nome", "email": "email@exemplo.com", ... }]
```

---

## 4. API Restrita (Sanctum + Role)

Todas as rotas abaixo exigem header `Authorization: Bearer {token}`.

### 4.1 Usuário Autenticado

#### GET `/api/user`
Retorna os dados do usuário autenticado.

**Response 200:**
```json
{
  "id": 2,
  "name": "Carlos Lima",
  "email": "carlos@medeiros.api",
  "position": "ADMIN"
}
```

#### POST `/api/profile/update`
Atualiza nome e/ou senha do próprio perfil. (qualquer papel autenticado)

**Request Body:**
```json
{
  "name": "Novo Nome",
  "password": "nova123",
  "password_confirmation": "nova123"
}
```

**Response 200:**
```json
{
  "success": "Perfil atualizado com sucesso.",
  "user": {
    "id": 2,
    "name": "Novo Nome",
    "email": "carlos@medeiros.api",
    "position": "ADMIN"
  }
}
```

### 4.2 Coletas (qualquer papel autenticado — `COLETOR`, `GERENCIA`, `ADMIN`)

#### POST `/api/coleta`
Registra uma nova coleta. O campo `user_id` é preenchido automaticamente com `auth()->id()`.

**Request Body:**
```json
{
  "loja_id": 1,
  "setor": "Frios",
  "ean": "7891234567890",
  "quantidade": 10,
  "validade": "2026-12-31"
}
```

**Response 201:**
```json
{
  "id": 2,
  "loja_id": 1,
  "user_id": 2,
  "setor": "Frios",
  "descricao": "Produto não encontrado",
  "ean": "7891234567890",
  "quantidade": 10,
  "data_validade": "2026-12-31T00:00:00.000000Z",
  "datahora": "2026-05-29T00:00:00.000000Z",
  "loja": { "id": 1, "nome": "Loja Matriz" },
  "user": { "id": 2, "name": "Carlos Lima", "email": "carlos@medeiros.api", "position": "ADMIN" }
}
```

**Response 409 (duplicado):**
```json
{
  "message": "Já existe uma coleta com este EAN nesta loja e data de validade.",
  "existing": { ... }
}
```

#### PUT `/api/coleta/{id}`
Atualiza quantidade e/ou validade de uma coleta.

**Request Body:**
```json
{
  "quantidade": 20,
  "validade": "2026-12-31"
}
```

**Response 200:** objeto `Coleta` com `loja` e `user`.

#### GET `/api/coleta/check`
Verifica se uma coleta já existe para a combinação loja + EAN + validade.

**Query Params:**
```
?loja_id=1&ean=7891234567890&validade=2026-12-31
```

**Response 200:**
```json
{
  "exists": true,
  "coleta": { ... }
}
```

### 4.3 Produtos (busca — qualquer papel autenticado)

#### GET `/api/product-by-code`
Busca produto por `code` via query param.

**Query Params:**
```
?code=123
```

**Response 200:**
```json
{
  "id": 1,
  "code": 123,
  "description": "Produto Exemplo",
  "barcodes": [
    { "id": 1, "ean": 7891234567890, "product_id": 1 }
  ]
}
```

**Response 404:**
```json
{ "Erro": "Produto nao encontrado" }
```

#### GET `/api/product-by-code/{code}`
Busca produto pelo `code` na URL.

**Response:** mesmo formato do endpoint acima.

#### GET `/api/by-ean/{ean}`
Busca produto pelo código de barras (EAN).

**Response 200:**
```json
{
  "code": 123,
  "description": "Produto Exemplo",
  "ean": 7891234567890
}
```

**Response 404:**
```json
{ "Erro": "Produto nao encontrado" }
```

### 4.4 Produtos CRUD (`GERENCIA`, `ADMIN`)

| Método | Rota | Ação |
|--------|------|------|
| GET | `/api/product` | Lista todos os produtos com barcodes |
| POST | `/api/product` | Cria um produto |
| GET | `/api/product/{id}` | Exibe um produto com barcodes |
| PUT | `/api/product/{id}` | Atualiza um produto |
| DELETE | `/api/product/{id}` | Remove um produto e seus barcodes |
| POST | `/api/product-save-all` | Cria múltiplos produtos de uma vez |

#### POST `/api/product`
| Campo | Tipo | Validação |
|-------|------|-----------|
| `code` | integer | required, unique:products,code |
| `description` | string | required, max:255 |

**Response 201:**
```json
{
  "success": "Produto cadastrado com sucesso.",
  "data": { "id": 1, "code": 123, "description": "Produto" }
}
```

#### PUT `/api/product/{id}`
| Campo | Tipo | Validação |
|-------|------|-----------|
| `code` | integer | required, unique:products,code,{id} |
| `description` | string | required, max:255 |

#### DELETE `/api/product/{id}`
Remove o produto e todos os barcodes associados. Retorna 200 com `{"message": "Produto deletado com sucesso."}`.

#### POST `/api/product-save-all`
**Request Body (array):**
```json
[
  { "code": 1, "description": "Produto A" },
  { "code": 2, "description": "Produto B" }
]
```

**Response 201:**
```json
{
  "success": "Produtos cadastrados com sucesso.",
  "data": [ ... ]
}
```

### 4.5 Códigos de Barras (EAN) CRUD (`GERENCIA`, `ADMIN`)

| Método | Rota | Ação |
|--------|------|------|
| GET | `/api/ean` | Lista todos os EANs com produto |
| POST | `/api/ean` | Cria um EAN |
| GET | `/api/ean/{id}` | Exibe um EAN com produto |
| PUT | `/api/ean/{id}` | Atualiza um EAN |
| DELETE | `/api/ean/{id}` | Remove um EAN |
| POST | `/api/ean-save-all` | Cria múltiplos EANs de uma vez |

#### POST `/api/ean`
| Campo | Tipo | Validação |
|-------|------|-----------|
| `ean` | integer | required, unique:barcodes,ean |
| `product_id` | integer | required, exists:products,id |

#### PUT `/api/ean/{id}`
| Campo | Tipo | Validação |
|-------|------|-----------|
| `ean` | integer | required, unique:barcodes,ean,{id} |
| `product_id` | integer | required, exists:products,id |

#### POST `/api/ean-save-all`
**Request Body (array):**
```json
[
  { "ean": 7891111111111, "product_id": 1 },
  { "ean": 7892222222222, "product_id": 2 }
]
```

### 4.6 Atualizar usuário (`ADMIN`)

#### POST `/api/user-update/{id}`
Atualiza nome, email, cargo e senha de um usuário.

**Request Body:**
```json
{
  "name": "Nome",
  "email": "email@exemplo.com",
  "function": "ADMIN",
  "new_password": "nova123"
}
```

**Response 200:**
```json
{ "success": "Update realizado" }
```

---

## Papéis e Permissões

| Papel | Acesso Web | Acesso API | Observação |
|-------|-----------|-----------|-----------|
| `COLETOR` | ❌ | Login, profile/update, product-by-code, by-ean, coleta CRUD | API‑only |
| `GERENCIA` | ✅ Produtos, Barcodes, Importação, Lojas, Coletas, Perfil | Tudo que COLETOR tem + product CRUD, ean CRUD | |
| `ADMIN` | Tudo (incluindo Usuários e Auditoria) | Tudo que GERENCIA tem + user-update | Controle total |

## Modelo de Dados

### Tabela `products`
| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | bigint (PK) | |
| code | bigint (unique) | Código interno do produto |
| description | string | Nome/descrição |
| created_at | timestamp | |
| updated_at | timestamp | |

### Tabela `barcodes`
| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | bigint (PK) | |
| ean | bigint (unique) | Código de barras EAN |
| product_id | bigint (FK) | Referência ao produto |
| created_at | timestamp | |
| updated_at | timestamp | |

### Tabela `lojas`
| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | bigint (PK) | |
| nome | string | Nome da loja |
| created_at | timestamp | |
| updated_at | timestamp | |

### Tabela `coletas`
| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | bigint (PK) | |
| loja_id | bigint (FK) | Referência à loja |
| user_id | bigint (FK) | Referência ao usuário coletor |
| setor | string (nullable) | Setor da loja |
| descricao | string | Descrição do produto |
| ean | string(20) | Código EAN |
| quantidade | integer | Quantidade coletada |
| data_validade | date | Data de validade |
| datahora | timestamp | Momento da coleta (default: now) |
| created_at | timestamp | |
| updated_at | timestamp | |

**Unique:** `(loja_id, ean, data_validade)`

### Tabela `users`
| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | bigint (PK) | |
| name | string | Nome completo |
| email | string (unique) | Email de login |
| password | string (hash) | Senha |
| position | string | ADMIN, GERENCIA ou COLETOR |
| created_at | timestamp | |
| updated_at | timestamp | |

### Tabela `audit_logs`
| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | bigint (PK) | |
| user_id | bigint (FK) | Quem executou a ação |
| action | string | create, update, delete, login, logout, import |
| entity_type | string | product, barcode, user, loja, coleta, auth, csv, profile |
| entity_id | bigint (nullable) | ID da entidade afetada |
| description | text (nullable) | Detalhes da ação |
| ip_address | string (nullable) | IP de origem |
| created_at | timestamp | |
| updated_at | timestamp | |