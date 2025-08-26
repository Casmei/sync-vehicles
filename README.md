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
3. Suba os serviços com Docker
```bash
docker compose up
```
> Foi utilizado containers one-shot, responsáveis por realizarem uma tarefa específica e depois são encerrados automaticamente.

---

📖 Documentação da API
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
