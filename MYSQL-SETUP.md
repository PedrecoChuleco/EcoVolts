# EcoVolts — banco de dados MySQL

Este projeto agora roda sobre um esquema MySQL real, baseado no modelo
enviado em `database/BD-CREATE-MYSQL.sql` (mantido no repositório como
referência). O script que efetivamente é executado é
**`database/schema.sql`** — uma versão ajustada para um cenário realista de
empresa de energia solar, com dados de referência (seed) já incluídos.

## O que mudou em relação ao modelo original

| Tabela | Ajuste | Por quê |
|---|---|---|
| `Usuario` | `login` agora é `UNIQUE`, `VARCHAR(150)` (cabe e-mails) | o `login` é o e-mail usado para entrar; precisa ser único e ter espaço |
| `Usuario` | `+ ativo`, `+ is_system`, `+ criado_em` | contas podem ser desativadas; a conta "Vendedor Online" (abaixo) precisa ficar marcada como não-login |
| `Telhado` | `+ area_estimada`, `+ direcao_estimada`, `+ comporta_placas` | registra quando o simulador teve que *assumir* um valor (cliente respondeu "Não sabe"), e guarda o resultado já calculado em vez de recalculá-lo depois |
| — | dados de referência (seed) | ver abaixo |

### Dados de referência (seed) incluídos

- **Perfil**: `Cliente`, `Vendedor`, `Administrador`
- **Estado**: os 27 estados brasileiros
- **Cidade / Bairro**: só a sede (Araras - SP / Jardim Universitário) —
  o resto é criado automaticamente conforme os clientes preenchem o perfil
  (`includes/address-helpers.php`)
- **Produto / Estoque**: catálogo básico (painel 550W, inversor 5kWp,
  estrutura de fixação) com estoque inicial
- **Permissao / Perfil_Permissao**: permissões básicas por papel (seedadas,
  mas **não aplicadas** por nenhuma tela ainda — ver "Próximos passos")
- **Usuario "Vendedor Online"**: conta de sistema (`is_system = 1`) usada
  como vendedor em todo orçamento gerado pelo simulador público, até um
  vendedor humano assumir o atendimento. Nasce com uma senha aleatória
  descartável e `login-handler.php` recusa login nela mesmo assim.

## Passo 1 — Extensão PDO MySQL

```bash
php -m | grep pdo_mysql
```

Se não aparecer nada: `sudo apt-get install php-mysql` (Ubuntu/Debian) e
reinicie o servidor PHP.

## Passo 2 — Rodar o schema

```bash
mysql -u root -p < database/schema.sql
```

Isso cria o banco `ecovolts`, as 15 tabelas e todos os dados de referência
acima. É seguro rodar mais de uma vez — tabelas e dados de referência usam
`IF NOT EXISTS` / `ON DUPLICATE KEY UPDATE`.

## Passo 3 — Usuário dedicado do banco

```sql
CREATE USER 'ecovolts_app'@'localhost' IDENTIFIED BY 'uma-senha-forte-aqui';
GRANT SELECT, INSERT, UPDATE, DELETE ON ecovolts.* TO 'ecovolts_app'@'localhost';
FLUSH PRIVILEGES;
```

## Passo 4 — Credenciais

Edite `includes/db-config.php` com os dados do Passo 3. Esse arquivo está
no `.gitignore` e nunca deve ser commitado com a senha real.

## Passo 5 — Telas que já usam o banco

| Tela | Arquivo(s) | O que faz |
|---|---|---|
| Cadastro | `auth/register-handler.php` | cria `Usuario` com papel `Cliente`, senha com `password_hash()` |
| Login | `auth/login-handler.php` | verifica `password_verify()`, recusa contas `is_system`/inativas |
| Simulação de orçamento | `orcamento-handler.php` | grava `Telhado` + `Orcamento` + `Orcamento_Prod` numa transação |
| Relatório | `relatorio.php` | lê um orçamento específico (`?id=`) ou o mais recente; **confere que pertence ao usuário logado** |
| **Histórico** *(tela nova)* | `historico.php` | lista todos os orçamentos do usuário logado, com link para cada relatório |
| **Perfil** *(tela antes em branco, agora completa)* | `perfil.php` + `perfil-handler.php` | edita nome, e-mail de contato, CPF/CNPJ, senha e endereço residencial |

`includes/address-helpers.php` tem as funções "find or create" que
normalizam o endereço digitado no perfil em `Cidade`/`Bairro`/`Endereco`
sem duplicar linhas a cada salvamento.

`includes/business-lookups.php` resolve a conta "Vendedor Online" e o
produto "Painel Solar 550W Mono" usados pelo `orcamento-handler.php` —
lança uma exceção clara se o schema não tiver sido rodado.

## Passo 6 — Testar end-to-end

```bash
php -S localhost:8000
```

1. `/register.php` → crie uma conta.
2. `/orcamento.php` → simule um orçamento (teste com e sem saber telhado/direção).
3. Confira o redirecionamento para `/relatorio.php?id=N` e os números batendo.
4. `/historico.php` → o orçamento aparece na lista.
5. `/perfil.php` → preencha o endereço, salve, recarregue a página e confira
   que os campos continuam preenchidos.
6. Em outra aba/sessão, tente abrir `/relatorio.php?id=1` logado como outro
   usuário — deve redirecionar para `/orcamento.php` em vez de mostrar o
   relatório de outra pessoa.

## Erros comuns

| Erro | Causa provável |
|---|---|
| `could not find driver` | `pdo_mysql` não instalado (Passo 1) |
| `SQLSTATE[HY000] [1045] Access denied` | credenciais erradas em `db-config.php`, ou usuário sem `GRANT` em `ecovolts` |
| `Conta "Vendedor Online" não encontrada` | `database/schema.sql` não foi rodado (ou rodou antes da seção de seed) |
| `SQLSTATE[23000]: Integrity constraint violation` ao simular orçamento | o produto "Painel Solar 550W Mono" foi renomeado/apagado — `business-lookups.php` espera esse nome exato |

## Antes de ir para produção

- [ ] Senha forte e única em `includes/db-config.php`, arquivo fora do controle de versão
- [ ] `display_errors = Off` no `php.ini` de produção
- [ ] HTTPS habilitado (senhas trafegam no POST de login/cadastro)
- [ ] Bloquear acesso direto a `includes/*.php` via `.htaccess`/Nginx (ver exemplo abaixo)
- [ ] Considerar rate-limiting em `auth/login-handler.php`

```apache
<Files "db-config.php">
    Require all denied
</Files>
```

## Próximos passos (fora do escopo desta etapa)

- **Permissões**: `Permissao`/`Perfil_Permissao` já têm dados, mas nenhuma
  tela checa permissão — hoje o controle é só "logado ou não". Dá pra
  adicionar uma função `userCan($db, $permissao)` e usá-la nas telas que
  precisarem diferenciar Cliente/Vendedor/Administrador.
- **Painel do vendedor/admin**: tela para ver/gerenciar orçamentos de
  todos os clientes (a permissão `gerenciar_orcamentos` já existe para isso).
- **Estoque**: `Produto`/`Estoque`/`MovimentacaoEstoque` estão populados,
  mas não há tela de administração nem baixa automática de estoque quando
  um orçamento vira venda — hoje a simulação só *consulta* o preço do
  painel, não reserva/baixa unidades.

## Administrador e gestão de usuários

### 1. Criar o primeiro administrador

O cadastro público (`register.php`) sempre cria **Clientes**. O primeiro
Administrador é criado pela linha de comando:

```bash
php database/criar-admin.php admin@ecovolts.com.br "Seu Nome" "SenhaForte123"
```

No XAMPP (Windows), com o caminho completo do PHP:

```bat
C:\xampp\php\php.exe database\criar-admin.php admin@ecovolts.com.br "Seu Nome" "SenhaForte123"
```

- Se o e-mail **já existe** (você já se cadastrou pelo site), o script apenas
  **promove** essa conta a Administrador (e só troca a senha se você informar uma).
- O script recusa rodar pelo navegador (HTTP 403).

Alternativa sem linha de comando (phpMyAdmin → aba SQL), para quem já se cadastrou:

```sql
UPDATE Usuario
SET id_perfil = (SELECT id_perfil FROM Perfil WHERE nome_perfil = 'Administrador'), ativo = 1
WHERE login = 'seu@email.com';
```

### 2. Entrar

Faça login normalmente em `login.php`. Para administradores aparecem:
o cartão **Administração** no dashboard e o link **Usuários** no menu lateral.

### 3. O que o CRUD de usuários faz (`usuarios.php`)

| Ação | Onde | Regras |
|---|---|---|
| Listar | `usuarios.php` | busca por nome/e-mail, filtro por perfil, 15 por página |
| Criar | `usuario-form.php` | senha obrigatória (mín. 8), e-mail de login único |
| Editar | `usuario-form.php?id=N` | senha em branco = mantém a atual |
| Ativar / desativar | botão na lista | a sessão do usuário desativado cai **na hora** |
| Excluir | botão na lista | só se a pessoa **não tem orçamentos** — senão, desative |

Proteções embutidas:

- só quem tem a permissão `administrar_usuarios` (tabela `Perfil_Permissao`) acessa; os demais recebem **403**;
- o administrador **não pode** alterar o próprio perfil, desativar ou excluir a si mesmo (evita ficar sem admin);
- contas de sistema (ex.: "Vendedor Online") não aparecem como editáveis e não podem ser alteradas;
- todas as ações de escrita são `POST` com **token CSRF**;
- a cada requisição o `config.php` confere no banco se o usuário logado continua **ativo** e com o perfil atual.

### Arquivos desta etapa

Novos: `usuarios.php`, `usuario-form.php`, `usuario-handler.php`,
`includes/auth-helpers.php`, `includes/require-admin.php`, `database/criar-admin.php`.
Alterados: `includes/config.php`, `includes/db.php`, `includes/header.php`, `dashboard.php`.

Para proteger outra tela por permissão: no topo, `require __DIR__ . '/includes/require-admin.php';`
(ou use `userCan('nome_da_permissao')` para decidir o que mostrar).
