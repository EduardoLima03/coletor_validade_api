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

- [auth] POST /api/user-update/{id}

    
### Router Product 
- GET|HEAD        api/product 
    
    Lista todos os produtos cadastrado. Não precisa de parâmentros
- POST            api/product

    Receber no corpo da requisição os dados para realizar o cadastro
    ```json
    {
        "code": "0111",
        "description": "teste 1"
    }
    ```
    
- GET|HEAD        api/product-by-code/{code}
- POST            api/product-save-all
- DELETE          api/product/{id} 
- GET|HEAD        api/product/{product}
- PUT|PATCH       api/product/{product} 

- GET|HEAD        api/by-ean/{ean}
- GET|HEAD        api/ean  
- POST            api/ean
- POST            api/ean-save-all
- PUT|PATCH       api/ean/{ean}
- DELETE          api/ean/{ean}

