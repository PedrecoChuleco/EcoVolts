# EcoVolts — Sistema de Apoio à Sustentabilidade por Meio da Energia Limpa

Projeto Interdisciplinar II · Sistemas de Informação · 4º semestre · 2026
Plataforma Inteligente para Monitoramento e Otimização de Infraestruturas Sustentáveis
Alinhado à **ODS 9 — Indústria, Inovação e Infraestrutura**.

## Grupo

**Nome do grupo:** EcoVolts

| RA | Aluno |
|---|---|
| 119528 | Estevan Granusso Moraes |
| 118754 | Gustavo Henrique Lima |
| 119127 | Lucas Henrique Cardoso |
| 119080 | Maria Claudia Alves |
| 118918 | Pedro Henrique Palma |
| 118750 | Kauan Vinicius da Silva Rodrigues |

**Repositório:** https://github.com/PedrecoChuleco/EcoVolts

## Sobre o projeto

A EcoVolts é uma empresa fictícia de energia solar. O sistema incentiva o uso consciente de energia limpa e ajuda quem quer instalar placas solares em residências ou empresas.

O cliente informa o valor da conta de luz e alguns dados do telhado (tamanho e direção). O sistema calcula:

- a quantidade de placas necessárias;
- o investimento estimado;
- o payback (tempo de retorno, em meses);
- a conta de energia estimada depois da instalação;
- se o telhado comporta as placas.

Cada simulação gera um orçamento salvo no banco de dados. A equipe comercial consulta e gerencia esses orçamentos, os clientes e o estoque de produtos.

Tecnologias: **HTML, CSS (Tailwind via CDN), JavaScript, PHP (puro, com PDO) e MySQL/MariaDB**.

## Funcionalidades

### Visitante
- Landing page institucional com resultados, benefícios e contato.
- Cadastro de conta e login.

### Cliente
- Simulação de orçamento a partir da conta de energia, do tamanho e da direção do telhado. Quando o cliente não sabe o tamanho ou a direção, o simulador usa uma estimativa e registra isso.
- Relatório do orçamento (cada cliente só vê os próprios).
- Histórico dos próprios orçamentos.
- Perfil: dados pessoais, CPF/CNPJ, endereço residencial e troca de senha.

### Vendedor (consulta)
- Clientes: consultar, cadastrar, editar e criar orçamento para um cliente.
- Orçamentos de todos os clientes, com busca e filtros, e página de detalhes.
- Estoque (Placas / Estoque) e movimentações de estoque, somente consulta.

### Administrador
- Tudo o que o vendedor faz.
- **CRUD de usuários:** listar, criar, editar, ativar/desativar e excluir.
- **CRUD de estoque:** criar e editar produtos e quantidades; criar, editar e excluir movimentações (o saldo é recalculado na mesma transação).
- Editar orçamentos: cabeçalho, vendedor responsável e itens.

### Permissões por perfil

As permissões ficam no banco (`Permissao` e `Perfil_Permissao`) e são checadas por `userCan()`.

| Permissão | Cliente | Vendedor | Administrador |
|---|:-:|:-:|:-:|
| simular_orcamento, ver_relatorio, editar_perfil | ✔ | ✔ | ✔ |
| consultar_orcamentos, consultar_clientes | | ✔ | ✔ |
| consultar_estoque, consultar_movimentacao_estoque | | ✔ | ✔ |
| cadastrar_clientes, editar_clientes | | ✔ | ✔ |
| editar_orcamentos, gerenciar_orcamentos | | | ✔ |
| gerenciar_estoque | | | ✔ |
| administrar_usuarios | | | ✔ |

## Banco de dados

O banco `ecovolts` usa `utf8mb4` / `utf8mb4_unicode_ci` e tem 15 tabelas, todas em InnoDB:

`Perfil`, `Usuario`, `Estado`, `Cidade`, `Bairro`, `Endereco`, `usuario_endereco`, `Produto`, `Estoque`, `MovimentacaoEstoque`, `Telhado`, `Orcamento`, `Orcamento_Prod`, `Permissao`, `Perfil_Permissao`.

O modelo original está em `database/BD-CREATE-MYSQL.sql`. A versão usada pelo sistema acrescenta:

- `Usuario`: `login` único com 150 caracteres (é o e-mail), mais as colunas `ativo`, `is_system` e `criado_em`.
- `Telhado`: `area_estimada`, `direcao_estimada` e `comporta_placas`.
- Dados iniciais: perfis, os 27 estados, catálogo de produtos com estoque, permissões e a conta de sistema **Vendedor Online**, usada como vendedor nos orçamentos gerados pelo simulador. Essa conta não consegue fazer login.

## Como rodar

### Requisitos
- PHP 8.1 ou superior (o projeto usa `enum`) com a extensão `pdo_mysql`.
- MySQL ou MariaDB.
- XAMPP serve para o ambiente local no Windows.

Para conferir a extensão: `php -m | grep pdo_mysql`.

### 1. Criar o banco

Rode o script do banco, `database/bd-ecovolts.sql`:

```bash
mysql -u root -p < database/bd-ecovolts.sql
```

Ou, no phpMyAdmin: aba **Importar**, escolha o arquivo e deixe o charset em utf-8.

O script cria o banco, as tabelas e os dados iniciais. As tabelas e os dados de referência usam `IF NOT EXISTS` e `ON DUPLICATE KEY`, então podem ser reexecutados.

### 2. Configurar a conexão

Crie o arquivo `includes/db-config.php`. Ele está no `.gitignore` (tem a senha do banco) e **não vem no repositório**:

```php
<?php
return [
    'host'    => '127.0.0.1',
    'port'    => 3306,          // no XAMPP costuma ser 3306; ajuste se usar outra porta
    'name'    => 'ecovolts',
    'user'    => 'ecovolts_app',
    'pass'    => 'SUA_SENHA_AQUI',
    'charset' => 'utf8mb4',
    'debug'   => false,         // true só em desenvolvimento, para ver o erro real da conexão
];
```

O usuário `ecovolts_app` precisa existir no MySQL com permissão no banco `ecovolts`. Se ainda não existe, crie assim:

```sql
CREATE USER IF NOT EXISTS 'ecovolts_app'@'localhost' IDENTIFIED BY 'SUA_SENHA_AQUI';
GRANT SELECT, INSERT, UPDATE, DELETE ON ecovolts.* TO 'ecovolts_app'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Criar o primeiro administrador

O cadastro público só cria **Clientes**. O primeiro administrador sai da linha de comando:

```bash
php database/criar-admin.php admin@exemplo.com "Nome do Administrador" "SenhaForte123"
```

No XAMPP (Windows):

```bat
C:\xampp\php\php.exe database\criar-admin.php admin@exemplo.com "Nome do Administrador" "SenhaForte123"
```

Se o e-mail já existe, o script só promove a conta a Administrador. A senha precisa ter pelo menos 8 caracteres.

### 4. Subir o servidor

```bash
php -S localhost:8000
```

Acesse `http://localhost:8000`. No XAMPP, copie o projeto para `htdocs` e acesse pelo Apache.

### Roteiro de teste

1. `/register.php`: crie uma conta de cliente.
2. `/orcamento.php`: simule um orçamento, com e sem informar tamanho e direção do telhado.
3. `/relatorio.php?id=N`: confira o resultado.
4. `/historico.php`: o orçamento aparece na lista.
5. `/perfil.php`: preencha o endereço, salve e recarregue.
6. Entre como administrador e abra `/usuarios.php`, `/placas.php`, `/movimentacao-estoque.php`, `/clientes.php` e `/orcamentos.php`.

## Estrutura de pastas

```
├── index.php                      landing page
├── login.php / register.php       telas de acesso
├── dashboard.php                  menu da área logada
├── perfil.php / perfil-handler.php
├── orcamento.php                  simulador
├── orcamento-handler.php          cálculo e gravação (transação)
├── relatorio.php / historico.php  resultado e histórico do cliente
├── clientes*.php                  área comercial: clientes
├── orcamentos.php, orcamento-detalhe.php, orcamento-form.php,
│   orcamento-admin-handler.php    área comercial: orçamentos
├── placas.php, produto-form.php, produto-handler.php
│                                  estoque / produtos
├── movimentacao-estoque*.php      movimentações de estoque
├── usuarios.php, usuario-form.php, usuario-handler.php
│                                  CRUD de usuários (administrador)
├── auth/                          login, cadastro e logout
├── includes/
│   ├── config.php                 sessão, rotas e revalidação do usuário logado
│   ├── db.php                     conexão PDO (getDb)
│   ├── db-config.php              credenciais (fora do Git)
│   ├── auth-helpers.php           userCan() e proteção CSRF
│   ├── require-permission.php     bloqueia por permissão
│   ├── require-admin.php          bloqueia quem não é administrador
│   ├── address-helpers.php        Estado → Cidade → Bairro → Endereço
│   ├── business-lookups.php       consultas de referência (vendedor online, painel)
│   ├── RoofDirection.php          direções do telhado e eficiência
│   └── layout/                    head, topbar, sidebar e logo
├── database/
│   ├── bd-ecovolts.sql           script do banco (estrutura + dados iniciais)
│   ├── BD-CREATE-MYSQL.sql        modelo original (referência)
│   └── criar-admin.php            cria ou promove um administrador (só CLI)
└── assets/                        CSS, JS e imagens
```

## Modelo de cálculo do orçamento

As constantes ficam em `orcamento-handler.php`:

| Constante | Valor | Significado |
|---|---|---|
| `KWH_PRICE` | R$ 0,85 | preço do kWh na conta |
| `GERACAO_POR_KWP` | 120 kWh/mês | geração de 1 kWp a 100% de eficiência |
| `AREA_POR_KWP` | 7 m² | área de telhado por kWp |
| `CUSTO_POR_KWP` | R$ 4.500 | investimento por kWp instalado |
| `PANEL_WATTAGE` | 550 W | potência de cada placa |

A eficiência depende da direção do telhado: Norte 100%, Leste/Oeste 85% e Sul 70%. Se o cliente não sabe a direção, o simulador assume Sul (cenário conservador). Se não sabe o tamanho, assume que o telhado comporta as placas e deixa a confirmação para a visita técnica.

## Segurança e boas práticas aplicadas

- Conexão PDO com `ERRMODE_EXCEPTION` e `charset=utf8mb4` na DSN e nas tabelas.
- Consultas com `prepare()` / `execute()` em todos os dados externos (placeholders nomeados).
- Transações (`beginTransaction` / `commit` / `rollBack`) nas escritas que envolvem várias tabelas: simulação de orçamento, perfil, produto e estoque, movimentações, clientes, edição de orçamento e exclusão de usuário.
- Senhas com `password_hash()` / `password_verify()`; `session_regenerate_id()` no login.
- Token CSRF em todas as ações de escrita sensíveis.
- Controle de acesso por permissão, lido do banco a cada requisição. Usuário desativado perde a sessão na hora.
- Cada cliente só enxerga os próprios orçamentos (`relatorio.php` confere o dono).
- `display_errors = Off` e HTTPS em produção; credenciais fora do Git.

## Documentação e cronograma

- Cronograma do projeto (Trello): https://trello.com/invite/b/6a7d10bb976cc4bfadd6b6f1/ATTIe137299d146da352d487d230fe029489D3C0EA29/kanban

## Pendências conhecidas

- Padronizar a organização em `conexao.php` (conexão) e `protect.php` (controle de sessão), como pede o exercício.
- Padronizar o tratamento de `PDOException` (log no servidor e mensagem genérica ao usuário) no login, no cadastro e nas listagens.
- Baixa automática de estoque quando um orçamento virar venda.
- Painel de acompanhamento dos orçamentos por status (abertos, fechados, recusados).
