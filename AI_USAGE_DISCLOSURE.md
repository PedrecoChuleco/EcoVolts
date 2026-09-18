# Divulgação de Uso de IA (AI Usage Disclosure)

> ⚠️ **Este arquivo é um modelo.** A Claude (Anthropic) gerou a estrutura
> inicial do código a partir do prompt abaixo. Antes da entrega, cada
> integrante do grupo deve preencher as seções marcadas com
> `PREENCHER:` explicando, com suas próprias palavras, o que entendeu e
> testou do código — isso é o que será cobrado na defesa oral.

## Ferramenta de IA utilizada

- **Ferramenta:** Claude (Anthropic), modelo Claude Sonnet 5, via
  interface de chat (claude.ai).
- **Data de uso:** PREENCHER: (data em que o grupo gerou/ajustou o código)

## Prompt original enviado à IA

```
implemente um sistema de registrar, login e crud usando apenas arquivos
php, html, css, js. siga as instruções abaixo.

Estrutura HTML com tags semânticas (header, nav, main, footer, form, label)
CSS externo — nenhum estilo inline
Box Model aplicado corretamente (padding, margin, border-box)
Layout responsivo — funciona no celular E no desktop (media queries)
Flexbox ou Grid utilizado no layout principal
JavaScript em arquivo .js separado — sem eventos inline
Validação de formulários com JavaScript no client-side
PHP conectado ao MySQL com PDO + prepared statements
Senhas criptografadas com password_hash() / password_verify()
htmlspecialchars() em todos os dados exibidos do usuário
session_start() e verificação de login em páginas protegidas
Diretórios organizados (/css, /js, /includes)
AI Usage Disclosure completo com prompts, código e explicações
Cada membro do grupo sabe explicar TODO o código que escreveu
Projeto testado e funcionando antes da defesa oral
```

O grupo partiu de um protótipo de login puramente front-end (localStorage,
sem PHP) e pediu à IA para reescrevê-lo como um sistema real com backend
PHP/MySQL, mantendo o visual original (`index.php`, `script.js`,
`style.css` enviados como referência de estilo).

## O que a IA gerou

- Estrutura de pastas `/css`, `/js`, `/includes`.
- Páginas `index.php` (login), `cadastro.php` (registro) e
  `dashboard.php` (painel protegido com CRUD de "Registros de Energia
  Solar").
- Scripts de processamento em `/includes` (`processa_login.php`,
  `processa_cadastro.php`, `processa_criar_item.php`,
  `processa_editar_item.php`, `processa_excluir_item.php`) usando PDO
  com prepared statements.
- `includes/db.php` (conexão PDO), `includes/auth.php`
  (`session_start()` + `exigirLogin()`), `includes/functions.php`
  (`h()` para `htmlspecialchars` e `redirecionar()`).
- `js/validacao.js` e `js/dashboard.js` para validação client-side e
  controle da modal de novo/editar registro, sem eventos inline.
- `database.sql` com o schema das tabelas `usuarios` e `registros`.

## PREENCHER: Explicação por integrante

Cada membro deve descrever, em algumas frases, a(s) parte(s) do código
que ficou(aram) responsável por revisar/entender a fundo, pois será
preciso explicar isso oralmente.

### Integrante 1 — Nome: PREENCHER:
- Arquivo(s) estudado(s): PREENCHER:
- O que esse código faz, em minhas palavras: PREENCHER:
- Testes que fiz para confirmar que funciona: PREENCHER:

### Integrante 2 — Nome: PREENCHER:
- Arquivo(s) estudado(s): PREENCHER:
- O que esse código faz, em minhas palavras: PREENCHER:
- Testes que fiz para confirmar que funciona: PREENCHER:

### Integrante 3 — Nome: PREENCHER:
- Arquivo(s) estudado(s): PREENCHER:
- O que esse código faz, em minhas palavras: PREENCHER:
- Testes que fiz para confirmar que funciona: PREENCHER:

## Modificações feitas pelo grupo após a geração pela IA

PREENCHER: (liste aqui qualquer ajuste que o grupo fez manualmente —
mudança de nomes de campos, textos, cores, regras de negócio extras,
correção de bugs encontrados durante o teste, etc. Se nenhuma mudança
foi feita, declare isso explicitamente.)

## Testes realizados antes da entrega

- [ ] Cadastro de novo usuário funciona e bloqueia e-mail duplicado.
- [ ] Login com credenciais corretas entra no painel; com credenciais
      erradas mostra mensagem de erro.
- [ ] Usuário não logado é redirecionado ao tentar acessar
      `dashboard.php` diretamente pela URL.
- [ ] Criar, editar e excluir um registro funciona e reflete no banco
      de dados (conferido no phpMyAdmin/MySQL Workbench).
- [ ] Um usuário não consegue editar/excluir registros de outro usuário
      (testado logando com duas contas diferentes).
- [ ] Layout testado em tela de celular (largura pequena) e em desktop.
- [ ] Nenhuma senha aparece em texto puro no banco de dados.
