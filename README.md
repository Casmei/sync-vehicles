#Laravel Vehicle Sync

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

---

### 🔧 Como rodar os testes

✅ Testes Unitários
Executa apenas os testes unitários, responsáveis por validar regras de negócio e lógicas internas do sistema:
```bash
php artisan test --testsuite=Unit
```
🔗 Testes de Integração
Executa apenas os testes de integração, validando endpoints, autenticação e respostas da API:
```bash
php artisan test --testsuite=Feature
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


