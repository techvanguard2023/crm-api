# Documentação da API CRM (v1)

Esta documentação resume as rotas e os formatos JSON da API para auxiliar no desenvolvimento do frontend e integrações (como n8n).

## 🔒 Autenticação
Todos os endpoints (exceto Status, Login e Register) exigem o header:
`Authorization: Bearer {token}`

---

## 🛣️ Rotas Disponíveis

### Autenticação e Sistema
| Método | Rota | Descrição |
| :--- | :--- | :--- |
| `GET` | `/status` | Verifica se a API está online |
| `POST` | `/login` | Autentica o usuário e retorna o token |
| `POST` | `/register` | Registra um novo usuário |
| `POST` | `/logout` | Invalida o token atual |
| `GET` | `/me` | Retorna os dados do usuário logado |

### Clientes (`Customers`)
| Método | Rota | Descrição |
| :--- | :--- | :--- |
| `GET` | `/customers` | Lista todos os clientes (com domains e services) |
| `POST` | `/customers` | Cria um novo cliente |
| `GET` | `/customers/{id}` | Detalhes de um cliente específico |
| `PUT` | `/customers/{id}` | Atualiza os dados de um cliente |
| `DELETE` | `/customers/{id}` | Remove um cliente |
| `GET` | `/customers/with-services` | Filtra clientes que possuem serviços ativos |
| `GET` | `/customers/by-service/{service_id}` | Filtra clientes por um tipo de serviço |
| `POST` | `/customers/{id}/services` | Vincula um novo serviço a um cliente (pivot) |

### Domínios e Serviços
| Método | Rota | Descrição |
| :--- | :--- | :--- |
| `GET` | `/domains` | Lista todos os domínios registrados |
| `POST` | `/domains` | Registra um novo domínio |
| `GET` | `/services` | Lista o catálogo de serviços (Hospedagem, Suporte, etc) |

### Cobrança e Renovação (`Billing`)
| Método | Rota | Descrição |
| :--- | :--- | :--- |
| **`GET`** | **`/customer-services/ready-to-bill`** | **Serviços que vencem hoje (agrupados por cliente com totais)** |
| `POST` | `/customer-services/{id}/renew` | Registra a renovação manual de um serviço |
| `POST` | `/customer-services/{id}/payment-request` | Gera uma nova solicitação de pagamento (Pix/Boleto) |
| `GET` | `/payments/request/{request_id}` | Consulta status de um pagamento pelo ID |

### Dashboard
| Método | Rota | Descrição |
| :--- | :--- | :--- |
| `GET` | `/dashboard/metrics` | Retorna contagens totais e projeções financeiras |

---

## 📊 Formatos JSON Principais

### Métricas do Dashboard (`/dashboard/metrics`)
```json
{
  "counts": {
    "total_customers": 15,
    "total_domains": 40,
    "total_active_services": 25
  },
  "financial": {
    "to_receive_current_month": "1500.00",
    "to_receive_current_year": "18000.00"
  },
  "annual_projection": [
    { "month_number": 1, "month_name": "Janeiro", "total": "1200.00" },
    ...
  ]
}
```

### Cliente (`CustomerResource`)
Retornado na maioria das rotas de clientes.

```json
{
  "id": 1,
  "name": "Nome do Cliente",
  "customer_name": "Nome do Cliente",
  "company_name": "Empresa Ltda",
  "email": "cliente@email.com",
  "phone": "+5521999999999",
  "document": "12345678000100",
  "services": [
    {
      "id": 7, // ID do vínculo (Use este para renovação/pagamento)
      "service_name": "Hospedagem",
      "price": "279.00",
      "recurrence": "monthly",
      "domain_name": "exemplo.com.br",
      "next_due_date": "2026-03-01"
    }
  ],
  "totals": {
    "services_total": "279.00"
  }
}
```

### Cobrança Pronta (`ready-to-bill`)
Agrupa múltiplos serviços do mesmo cliente vencendo na mesma data.

```json
[
  {
    "id": 11,
    "customer_name": "Véra Lucia Harcar",
    "services": [
      { "id": 12, "service_name": "Hospedagem", "price": "240.00", "domain_name": "site1.com.br" },
      { "id": 13, "service_name": "Hospedagem", "price": "310.00", "domain_name": "site2.com.br" }
    ],
    "totals": {
      "services_total": "550.00"
    }
  }
]
```

---

## 💡 Dicas de Integração
- **Campos de Valor**: Todos os preços vêm como strings (ex: `"400.00"`) para precisão.
- **Datas**: Formato padrão é `YYYY-MM-DD`.
- **IDs**: Sempre utilize o `id` interno do objeto dentro de `services` para disparar renovações ou solicitações de pagamento.
