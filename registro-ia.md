# Registro de uso de IA

> Regra: "A IA pode escrever o boilerplate — mas a segurança da aplicação é assinada por você."
> Cole abaixo os **3 prompts exatos** usados. Se não usou IA, escreva apenas: **não utilizei IA**.

**Prompt 1:**
usando estes arquivos do nosso projeto interdisciplinar como base, faça o enunciado

**Prompt 2:**
está tela tem que ser standalone(ou usar o minimo de arquivos possivel), não sera reeimplementada no projeto existente e tera seu proprio folder, e deve funcionar sem um banco de dados(salvar dados apenas na memoria do navegador).

**Prompt 3:**
não é necessario mostrar ao usuario quais "contas" estão logadas, o objetivo é que seja uma simulação inconfundivel com uma pagina de registro real mas que usa os mesmos principios. Com isso, crie mais um arquivo de dashboard para uso como exemplo, que o cadastro ira redirecionar para. nada precisa funcionar, apenas deve parecer como uma dashboard real, a unica coisa que deve ser real é um botão para "sair" da conta que volta para o cadastro

## Revisão do grupo (apoio à defesa oral de 30 s)
- O que a IA gerou e o grupo revisou linha a linha: cadastro.php, dashboard.php
- O que o grupo alterou: separação do CSS de dentro de uma tag <style> para um arquivo externo, referenciado por uma tag <link>. revisão das tags CSS duplicados e junção das regras relacionadas aos mesmos componentes. análise do uso de POST e reconhecimento de que esse método sozinho não implementa autenticação segura.