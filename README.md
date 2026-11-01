# EcoVolts — módulo de vendedores

Este pacote adiciona contas de Vendedor com acesso de consulta a:

- Placas / Estoque (`placas.php`)
- Movimentações do estoque (`movimentacao-estoque.php`)
- Clientes (`clientes.php`)
- Histórico de orçamentos (`orcamentos.php`)

O Administrador também pode abrir cada orçamento e editar cabeçalho, vendedor responsável e itens. Na área de estoque, ele pode criar/editar produtos e quantidade, além de criar, editar e excluir movimentações; cada alteração de movimentação recalcula o saldo do estoque dentro da mesma transação.

## Instalação

### Banco já existente
1. Faça backup da base `ecovolts`.
2. Execute `database/migration-vendedores.sql`.
3. Substitua os arquivos PHP deste pacote mantendo a estrutura `includes/`.

### Banco novo
Use `database/schema.sql` deste pacote no lugar do schema anterior.

## Contas de vendedores

O cadastro de vendedor continua sendo feito pela área administrativa:
`Usuários` → `Novo vendedor`.

O cadastro público (`register.php`) não cria contas de vendedor automaticamente; o usuário público continua entrando como Cliente.

## Segurança

As telas comerciais usam permissões do banco:
`consultar_estoque`, `consultar_movimentacao_estoque`, `consultar_clientes`, `consultar_orcamentos` e `gerenciar_estoque` para o administrador.

A edição do histórico usa `editar_orcamentos` e permanece protegida para Administrador.

O Vendedor não recebe `gerenciar_estoque`. A migração também remove `gerenciar_orcamentos` do perfil Vendedor antigo para deixar a área de orçamentos como consulta.

## Observação

Os nomes `Orcamento`, `Orcamento_Prod`, `Estoque` e `MovimentacaoEstoque` seguem exatamente o schema existente. A tela chamada “Placas / Estoque” usa o arquivo `placas.php`, conforme solicitado.
