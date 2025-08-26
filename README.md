# Laravel Vehicle Sync

[![Tests](https://github.com/Casmei/sync-vehicles/actions/workflows/larave-teste.yml/badge.svg)](https://github.com/Casmei/sync-vehicles/actions/workflows/larave-teste.yml)

Este projeto é uma API desenvolvida em Laravel para gerenciar um CRUD de veículos,
com sincronização automática (via agendamento) com uma API externa. A autenticação
é feita utilizando Laravel Sanctum, garantindo segurança no acesso aos endpoints.

---

### ▶️ Como rodar a aplicação localmente
1. Clone o repositório:
```bash
git clone https://github.com/Casmei/sync-vehicles.git
```
2. Entre na pasta do projeto:
```bash
cd sync-vehicles
```
🔹 Pequena observação, a variável de ambiente API_ALPESONE já está adicionada ao .env.example, logo, não é necessário nenhuma alteração no .env

3. Suba os serviços com Docker
```bash
docker compose up
```
> Foi utilizado containers one-shot, responsáveis por realizarem uma tarefa específica e depois são encerrados automaticamente.

---

### 📖 Documentação da API
- Localmente
Após subir os containers, acesse:
```bash
http://localhost:8000/docs
```
*(Gerada com [Scramble](https://scramble.io/))*  

- Produção: [https://alpesone.kontact.com.br/docs/api](https://alpesone.kontact.com.br/docs/api)

🔑 Usuário padrão para login:
```makefile
Email: julio.oliveira@alpes.one
Senha: carbel123
```
---

### 🔧 Como rodar os testes

Antes, é necessário você entrar dentro do container que está rodando a aplicação com esse comando:
```bash
docker exec alpes-php bash
```
🔗 Em seguida, basta executar
```bash
php artisan test 
```

---

### 🚀 CI/CD

Este projeto utiliza GitHub Actions para automação de testes e deploy:
- Tests:
    A cada push, é executada uma pipeline que roda os testes unitários e de integração via PHPUnit, garantindo a integridade do código.
- Deploy to EC2:
  Também em cada push, a aplicação é implantada automaticamente em uma instância EC2.
  O workflow acessa o servidor via SSH, atualiza o código do repositório e executa docker compose up -d para aplicar as alterações.
  
---

### Estrutura do projeto
Um pequeno overview geral do projeto e suas camadas
```
app/
├── Services
│   ├── AuthService.php
│   ├── LoadVehicleService.php
│   └── VehicleService.php
├── Repositories
│   ├── UserRepository.php
│   ├── VehicleRepository.php
│   └── ... (Implementações concretas)
├── Http/Controller/
│   ├── AuthController.php
│   └── VehicleController.php
├── Models/
│   ├── User.php
│   └── Vehicle.php
└── Console/Commands/
    └── SyncExternalVehicles.php

tests/
├── Feature/
│   ├── AuthControllerTest.php
│   └── VehicleControllerTest.php
└── Unit/
    ├── AuthControllerTest.php
    ├── LoadVehicleServiceTest.php
    └── AuthServiceTest.php
```

---

### 📝 Observações sobre o Desenvolvimento
O teste foi iniciado em um domingo, quando a API oficial de exportação ainda não estava disponível.
Para não atrasar o desenvolvimento, criei uma API mock em Nest.js com rate limiting (Throttler), que serviu como fonte de dados temporária.

🔗 Repositório da API mock:
*([https://github.com/Casmei/vehicle-export](https://github.com/Casmei/vehicle-export))*

🌐 Deploy da API mock:
*([https://vxport.kontact.com.br/vehicles/export](https://vxport.kontact.com.br/vehicles/export))*

Estou totalmente disponível para esclarecer qualquer dúvida sobre essa decisão ou sobre a implementação.





