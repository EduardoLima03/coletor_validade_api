## ROUTE LIST
### Router User
- POST  api/login

    **Login**

    Passando no corpo da requisição o email e senha no formato *Json*
    ```json
    {
        "email": "emaildocumentacao@documentacao.api",
        "password": "password"
    }
    ```

    Retorna no formato *Json* o token e o objeto usuario(id, name, email e function)
    ```json
    {
        "token": "asdadadfewrwrewe213123eqwedas332ew",
        "user": {
            "id": 1,
            "name": "Documentação",
            "email": "emaildocumentacao@documentacao.api",
            "function": "Documentação"
        }
    }
    ```
- POST  /api/user

    **Novo Usuario**

    Recebe pelo corpo da requisição no formato *Json* os dados para cadastro.
    ```json
    {
        "name": "User01",
        "function":"Documentacao",
        "email": "user01@documentacao.api",
        "password": "documentacao"
    }
    ```

    Retorna o Objeto cadastro.

- <mark>[auth]</mark> POST /api/user-update/{id}

    Recebe no body os campos de update

    ```json
    {
        "name": "User01",
        "function":"Documentacao",
        "email": "user01@documentacao.api",
        "password": "documentacao"
    }
    ```
    Retorna o objeto salvo em fortamto de **json**

    
### Router Product 
- <mark>[auth]</mark> GET|HEAD        api/product 
    
    Lista todos os produtos cadastrado. Não precisa de parâmentros
- <mark>[auth]</mark> POST            api/product

    Receber no corpo da requisição os dados para realizar o cadastro
    ```json
    {
        "code": "0111",
        "description": "teste 1"
    }
    ```
    
- <mark>[auth]</mark> GET|HEAD        api/product-by-code/{code}
    
    Recebe o código de PLU do produto, e, retorna a descrição do produto.
    ```json
    {
        "code": "1234547",
        "description": "teste 01"
    }
    ```

- <mark>[auth]</mark> POST            api/product-save-all

    Recebe uma lista de produtos pelo corpo em formato json
- <mark>[auth]</mark> DELETE          api/product/{id} 
- <mark>[auth]</mark> GET|HEAD        api/product/{product}
- <mark>[auth]</mark> PUT|PATCH       api/product/{product} 

### Router EAN
- <mark>[auth]</mark> GET|HEAD        api/by-ean/{ean}
- <mark>[auth]</mark> GET|HEAD        api/ean  
- <mark>[auth]</mark> POST            api/ean
- <mark>[auth]</mark> POST            api/ean-save-all
- <mark>[auth]</mark> PUT|PATCH       api/ean/{ean}
- <mark>[auth]</mark> DELETE          api/ean/{ean}

