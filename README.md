# EcoVolts — Sistema de Login e CRUD

Sistema web em PHP + MySQL para cadastro/login de usuários e um CRUD de
"Registros de Energia Solar" (equipamento, potência gerada em kWh, data e
observação).

## Estrutura de pastas

```
ecovolts/
├── index.php                 # página de login (pública)
├── cadastro.php               # página de criação de conta (pública)
├── dashboard.php               # painel CRUD (protegido por login)
├── logout.php                  # encerra a sessão
├── database.sql                # script de criação do banco
├── css/
│   └── style.css
├── js/
│   ├── validacao.js             # validação client-side de login/cadastro
│   └── dashboard.js             # modal + validação do CRUD de registros
└── includes/
    ├── db.php                   # conexão PDO
    ├── auth.php                  # session_start() e exigirLogin()
    ├── functions.php              # h() (htmlspecialchars) e redirecionar()
    ├── processa_login.php
    ├── processa_cadastro.php
    ├── processa_criar_item.php
    ├── processa_editar_item.php
    └── processa_excluir_item.php
```

## Como rodar localmente

1. Instale um ambiente com PHP + MySQL (ex.: XAMPP, WAMP, Laragon ou
   `php -S` + MySQL separado).
2. Crie o banco executando o arquivo `database.sql`:
   ```
   mysql -u root -p < database.sql
   ```
3. Confira as credenciais em `includes/db.php` (host, nome do banco,
   usuário e senha) e ajuste se necessário.
4. Coloque a pasta `ecovolts/` dentro do diretório servido pelo Apache
   (ex.: `htdocs/` no XAMPP) ou rode com o servidor embutido do PHP:
   ```
   php -S localhost:8000
   ```
5. Acesse `http://localhost:8000/index.php` (ou `.../ecovolts/index.php`
   dependendo de onde a pasta ficou).
6. Adicione sua imagem de logo como `ecovolts-logo.png` na raiz do
   projeto (o `<img>` já está referenciado em `index.php`/`cadastro.php`).

## Fluxo do sistema

- **Cadastro** (`cadastro.php` → `includes/processa_cadastro.php`): valida
  os campos no JS e novamente no PHP, verifica e-mail duplicado com
  *prepared statement*, e salva a senha com `password_hash()`.
- **Login** (`index.php` → `includes/processa_login.php`): busca o
  usuário pelo e-mail e confere a senha com `password_verify()`. Se
  válido, grava os dados na `$_SESSION` e redireciona ao painel.
- **Proteção de página**: `includes/auth.php` define `exigirLogin()`,
  chamada no topo de `dashboard.php` e de todos os scripts de
  `processa_*_item.php`, redirecionando quem não estiver logado.
- **CRUD** (`dashboard.php`): lista os registros do usuário logado
  (sempre filtrando por `usuario_id`), e usa uma modal (JS puro, sem
  eventos inline) para criar/editar; a exclusão pede confirmação via
  `confirm()` antes de enviar o formulário.
- **Logout** (`logout.php`): destrói a sessão e volta para o login.

## Segurança aplicada

- Todas as consultas usam PDO com **prepared statements** (sem
  concatenar valores na query).
- Senhas nunca são salvas em texto puro (`password_hash()` /
  `password_verify()`).
- Toda saída de dados do usuário passa pela função `h()`
  (`htmlspecialchars`) antes de ser impressa no HTML.
- Cada operação de UPDATE/DELETE filtra também por `usuario_id`, para
  que um usuário não possa alterar ou apagar registros de outra conta.
- `session_regenerate_id(true)` é chamado no login para reduzir risco de
  session fixation.

---

Veja também `AI_USAGE_DISCLOSURE.md` para o registro do uso de IA neste
projeto, conforme exigido pela atividade.
