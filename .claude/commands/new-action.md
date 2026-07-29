---
description: Faz o scaffold de uma nova Action seguindo o padrão do Drafto (Action + DTO + teste Pest).
argument-hint: <Domínio>/<NomeAction> [descrição curta do que a Action faz]
---

Você vai criar uma Action nova no padrão do projeto (ver Skill `drafto-conventions`
para os detalhes completos). Argumento recebido: `$ARGUMENTS`.

1. **Interprete o argumento** no formato `<Domínio>/<NomeAction> [descrição]`
   (ex.: `Posts/ArchiveOldDraftsAction arquiva rascunhos com mais de 90 dias`).
   Se o domínio não existir em `app/Actions/`, pergunte ao usuário se deve criar um
   novo ou se é um typo de um domínio existente antes de prosseguir.

2. **Verifique se já existe** algo com nome igual/parecido em `app/Actions/<Domínio>/`
   e em `app/DTOs/<Domínio>/` antes de criar — evite duplicar.

3. **Crie a Action** em `app/Actions/<Domínio>/<NomeAction>.php`:
   - `declare(strict_types=1);`
   - Classe final, método público `exec(...)` com o tipo de retorno explícito.
   - Recebe/retorna DTO (não array associativo) quando há mais de 1-2 parâmetros
     primitivos.
   - Sem lógica de apresentação/HTTP — isso é responsabilidade do
     Controller/Livewire que chama a Action.

4. **Crie o DTO**, se necessário, em `app/DTOs/<Domínio>/` usando
   `spatie/laravel-data` (`readonly`, tipagem estrita). Não crie DTO para casos
   triviais de 1 parâmetro primitivo — use bom senso.

5. **Crie o teste Pest** em `tests/Feature/Actions/<Domínio>/<NomeAction>Test.php`
   com `describe()`/`it()`, cobrindo pelo menos o caminho feliz e um caso de
   falha/validação plausível. Rode `vendor/bin/pest --filter=<NomeAction>` para
   confirmar que passa.

6. Ao final, rode `vendor/bin/pint` nos arquivos criados (o hook de pós-edição já
   deve ter feito isso automaticamente) e resuma os 3 arquivos criados/alterados.

Não implemente a lógica de negócio inventando requisitos — se a descrição for vaga
demais para saber o que a Action deve fazer, pergunte antes de escrever código.
