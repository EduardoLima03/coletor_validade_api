# DataCheck - Medeiros

Sistema de validação e consulta de produtos.

---

## Stack

- **Laravel 11** (PHP 8.2)
- **MySQL 8.0**
- **Apache 2.4**
- **Docker** (produção)

---

## Deploy com Docker

### Pré-requisitos no servidor

- Docker Engine + Compose
- Apache 2.4 (para proxy reverso no host)
- Git

### 1. Clonar e configurar

```bash
git clone <url-do-repositorio> datacheck
cd datacheck
```

### 2. Build e iniciar containers

```bash
docker compose up -d --build
```

Isso sobe dois containers:

| Container        | Porta     | Função           |
|------------------|-----------|------------------|
| `datacheck-app`  | `9001:80` | Aplicação Laravel|
| `datacheck-db`   | `3306`    | Banco MySQL      |

O banco de dados usa o volume `mysql_data` para persistência — mesmo se o container for recriado, os dados são mantidos.

### 3. Verificar status

```bash
docker compose ps
docker compose logs -f app
```

### 4. Proxy reverso no Apache do host

A máquina host recebe as requisições HTTP/HTTPS e encaminha para o container na porta 9001.

**`/etc/apache2/sites-available/datacheck.conf`** (com SSL):

```apache
<VirtualHost *:80>
    ServerName datacheck.seudominio.com
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}$1 [R=301,L]
</VirtualHost>

<VirtualHost *:443>
    ServerName datacheck.seudominio.com

    SSLEngine On
    SSLCertificateFile /etc/letsencrypt/live/datacheck.seudominio.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/datacheck.seudominio.com/privkey.pem

    ProxyPreserveHost On
    ProxyPass / http://localhost:9001/
    ProxyPassReverse / http://localhost:9001/

    RequestHeader set X-Forwarded-Proto "https"
    RequestHeader set X-Forwarded-Port "443"

    ErrorLog ${APACHE_LOG_DIR}/datacheck-error.log
    CustomLog ${APACHE_LOG_DIR}/datacheck-access.log combined
</VirtualHost>
```

Ativar o site:

```bash
sudo a2enmod proxy proxy_http rewrite ssl headers
sudo a2ensite datacheck
sudo systemctl reload apache2
```

### 5. SSL com Let's Encrypt

```bash
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d datacheck.seudominio.com
```

### 6. Garantir que os containers voltem após reinicialização

```bash
sudo systemctl enable docker
```

O `restart: unless-stopped` já está configurado no `docker-compose.yml`, então os containers sobem automaticamente com o Docker.

---

## Enviar imagem para outro servidor

### Opção 1 — Docker Registry

```bash
docker tag datacheck:1.0 seuusuario/datacheck:1.0
docker push seuusuario/datacheck:1.0
```

No servidor remoto, basta copiar o `docker-compose.yml` e rodar:

```bash
docker compose up -d
```

### Opção 2 — Arquivo tar

```bash
docker save datacheck:1.0 | gzip > datacheck-1.0.tar.gz
scp datacheck-1.0.tar.gz usuario@servidor:/tmp/
```

No servidor:

```bash
docker load < /tmp/datacheck-1.0.tar.gz
docker compose up -d
```

---

## Arquitetura

```
Internet → Host:80/443 (Apache proxy reverso)
             → localhost:9001 (datacheck-app container)
                 → db:3306 (datacheck-db container)
                     └── volume mysql_data (dados persistidos)
```

---

## API

### Autenticação

#### POST /api/login

Recebe email e senha:

```json
{
    "email": "emaildocumentacao@documentacao.api",
    "password": "password"
}
```

Retorna:

```json
{
    "token": "eyJ...",
    "user": {
        "id": 1,
        "name": "Documentação",
        "email": "emaildocumentacao@documentacao.api",
        "function": "Documentação"
    }
}
```

#### POST /api/user

Cadastro de novo usuário (body JSON):

```json
{
    "name": "User01",
    "function": "Documentacao",
    "email": "user01@documentacao.api",
    "password": "documentacao"
}
```

#### POST /api/user-update/{id} [auth]

Atualiza dados do usuário.

---

### Coletas

| Método | Rota                         | Descrição                                    | Autenticação     |
|--------|------------------------------|----------------------------------------------|------------------|
| POST   | /api/coleta                  | Criar ou substituir coleta                   | auth:sanctum     |
| PUT    | /api/coleta/{id}             | Atualizar quantidade (0 = soft delete)       | auth:sanctum     |
| GET    | /api/coleta/check            | Verificar se coleta já existe                | auth:sanctum     |
| GET    | /api/coleta/trashed          | Listar coletas excluídas (gerência)          | GERENCIA,ADMIN   |
| PUT    | /api/coleta/{id}/restore     | Restaurar coleta excluída (gerência)         | GERENCIA,ADMIN   |

**Regras de soft delete:**
- `PUT /api/coleta/{id}` com `quantidade: 0` → remove a coleta (soft delete), histórico preservado
- `POST /api/coleta` com `action: replace` e `quantidade: 0` → soft delete da existente
- Gerentes/ADMIN podem ver e restaurar via `trashed` e `restore`

---

### Produtos

| Método | Rota                    | Descrição            |
|--------|-------------------------|----------------------|
| GET    | /api/product            | Lista todos          |
| POST   | /api/product            | Criar produto        |
| GET    | /api/product/{id}       | Buscar por ID        |
| PUT    | /api/product/{id}       | Atualizar            |
| DELETE | /api/product/{id}       | Remover              |
| GET    | /api/product-by-code/{code} | Buscar por código |
| POST   | /api/product-save-all   | Importar lote        |

### EANs

| Método | Rota              | Descrição         |
|--------|-------------------|-------------------|
| GET    | /api/ean           | Listar todos      |
| POST   | /api/ean           | Criar EAN         |
| GET    | /api/by-ean/{ean}  | Buscar por código |
| POST   | /api/ean-save-all  | Importar lote     |
| PUT    | /api/ean/{ean}     | Atualizar         |
| DELETE | /api/ean/{ean}     | Remover           |
