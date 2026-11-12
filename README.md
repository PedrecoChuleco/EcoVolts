# EcoVolts

Sistema de orcamento de energia solar em PHP puro + MySQL/MariaDB, para rodar no XAMPP.

## Estrutura

- `index.php` - pagina inicial (landing)
- `auth/` - login, cadastro e logout
- `conta/` - menu (dashboard) e perfil do usuario logado
- `simulador/` - fluxo do cliente: simular orcamento, relatorio e historico
- `orcamentos/` - area comercial: lista, detalhe, edicao, status e PDF
- `clientes/` - area comercial: cadastro e consulta de clientes
- `estoque/` - area comercial: placas/produtos e movimentacoes
- `usuarios/` - administracao: CRUD de usuarios
- `relatorios/` - indicadores (dashboards) e exportacao CSV
- `includes/` - codigo compartilhado (nunca acessado direto pelo navegador):
  - `config.php` (sessao, `BASE_URL`, `route()`, `asset()`), `db.php`, `db-config.php`, `header.php`, `footer.php`
  - `layout/` - head, topbar, sidebar, brand, site-footer
  - `enums/` - RoofDirection, StatusOrcamento, PadraoLigacao
  - `helpers/` - address-helpers, auth-helpers (`userCan`, CSRF), business-lookups, relatorios, PdfSimples
  - `guards/` - require-admin, require-permission (protegem paginas por permissao)
- `assets/` - css, js e imagens
- `database/` - scripts SQL e `criar-admin.php` (linha de comando)
- `docs/` - documentacao

## Rodando no XAMPP

1. Coloque a pasta em `C:\xampp\htdocs\ecovolts` e inicie Apache e MySQL.
2. Importe o banco (phpMyAdmin) e ajuste `includes/db-config.php`.
3. Abra `http://localhost/ecovolts/`.

As URLs sao montadas por `BASE_URL`, entao o projeto funciona em qualquer
subpasta do htdocs ou em um VirtualHost, sem configurar nada.

## Como adicionar uma pagina

1. Crie o arquivo na pasta da area (ex.: `estoque/fornecedores.php`).
2. Comece com `require __DIR__ . '/../includes/guards/require-permission.php';`
   (ou `require-admin.php`), definindo antes `$requiredPermission = '...';`.
3. Registre em `route()` (`includes/config.php`): a chave e o nome do arquivo com `_`.
4. Link: `<a href="<?= route('fornecedores') ?>">`. Redirect: `header('Location: ' . route('fornecedores'));`
5. Assets: `<img src="<?= asset('images/logo.png') ?>">`.
