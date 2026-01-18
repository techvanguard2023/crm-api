# CRM API

API Restful desenvolvida em Laravel 11 para gerenciamento de clientes, serviços, domínios e contratos.

## 🚀 Tecnologias

-   **PHP 8.2+**
-   **Laravel 11**
-   **MySQL / MariaDB**
-   **Sanctum** (Autenticação)

## 📋 Funcionalidades Principais

-   **Gerenciamento de Clientes**: CRUD completo de clientes.
-   **Catálogo de Serviços**: Cadastro de serviços (ex: Hospedagem, Manutenção).
-   **Associação N:N**:
    -   Clientes podem contratar múltiplos serviços.
    -   Cada contrato (Cliente-Serviço) tem seu **Preço** e **Recorrência** personalizados.
-   **Domínios**: Gestão de domínios vinculados aos clientes.
-   **Autenticação**: Rotas protegidas via tokens Sanctum.

## 🛠️ Instalação

1.  **Clone o repositório**
    ```bash
    git clone https://github.com/seu-usuario/crm-api.git
    cd crm-api
    ```

2.  **Instale as dependências**
    ```bash
    composer install
    ```

3.  **Configure o ambiente**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    *Edite o arquivo `.env` com as credenciais do seu banco de dados.*

4.  **Rode as migrações**
    ```bash
    php artisan migrate
    ```

5.  **Inicie o servidor**
    ```bash
    php artisan serve
    ```

## 📚 Documentação da API

### Autenticação
Todas as rotas abaixo requerem o header `Authorization: Bearer <token>`, exceto Login/Register.

### Clientes (`/api/v1/customers`)
-   `GET /` - Lista todos os clientes (com serviços e domínios).
-   `POST /` - Cria um novo cliente.
-   `GET /{id}` - Exibe detalhes de um cliente.
-   `PUT /{id}` - Atualiza um cliente.
-   `DELETE /{id}` - Remove um cliente.

#### Associar Serviço a Cliente (Contrato)
-   `POST /api/v1/customers/{id}/services`
    -   Vincula um serviço do catálogo ao cliente com condições específicas.
    -   **Payload:**
        ```json
        {
            "service_id": 1,
            "price": 100.00,
            "recurrence": "mensal"
        }
        ```

### Serviços (`/api/v1/services`)
-   `GET /` - Lista o catálogo de serviços.
-   `POST /` - Cria um novo serviço no catálogo.

### Domínios (`/api/v1/domains`)
-   Gerenciamento de domínios dos clientes.

## 🗄️ Modelagem de Dados (Destaque)

O sistema utiliza uma relação **Muitos-para-Muitos** entre `Customer` e `Service`:

-   **Tabela `services`**: Define O QUE é o serviço (Nome, Descrição).
-   **Tabela `customer_service` (Pivô)**: Define O CONTRATO (Preço, Recorrência que aquele cliente paga).

Isso permite que o serviço "Hospedagem VPS" exista uma única vez no sistema, mas tenha preços diferentes para o "Cliente A" e "Cliente B".
