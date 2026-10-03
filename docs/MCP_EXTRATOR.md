# MCP do Extrator Papirar

Implementação inicial: 15 ferramentas correspondentes ao Schema OpenAPI 1.3.2 do Extrator. O servidor reutiliza os controllers de `/api/gpt`, sem chamadas HTTP ao próprio site. Questões individuais e em lote permanecem em `draft`. Não expõe revisão/publicação, arquivamento, fusões ou ferramentas de marketing.

## Conexão

- URL prevista após implantação: `https://www.papirar-concursos.com.br/mcp/extractor`.
- Transporte: Streamable HTTP, implementado pelo pacote oficial `laravel/mcp`.
- Autenticação para ChatGPT: OAuth/PKCE pelo Laravel Passport, com registro dinâmico de cliente (DCR).
- Escopo: `mcp:use`.
- Permissão: usuário ativo com papel `admin` ou `content`, verificado em cada chamada.
- Callback permitido: domínio `https://chatgpt.com`. Copie a URL exata apresentada pelo host na configuração da conexão. Não inclua chave API no pacote do plugin.
- As rotas REST existentes continuam usando `GPT_API_TOKEN`. A conexão OAuth não exige divulgar essa chave.

## Implantação na Hostinger

Execute na raiz do projeto, depois de revisar e integrar o PR e atualizar o código pelo processo habitual. Faça backup do banco antes das novas tabelas OAuth. Não substitua o `.env`, `APP_KEY` nem as credenciais existentes.

```bash
php -v
composer install --no-dev --prefer-dist --optimize-autoloader
php artisan optimize:clear
php artisan migrate:status
```

Execute **somente as cinco novas migrations** deste PR:

```bash
php artisan migrate --force \
  --path=database/migrations/2026_10_03_120640_create_oauth_auth_codes_table.php \
  --path=database/migrations/2026_10_03_120641_create_oauth_access_tokens_table.php \
  --path=database/migrations/2026_10_03_120642_create_oauth_refresh_tokens_table.php \
  --path=database/migrations/2026_10_03_120643_create_oauth_clients_table.php \
  --path=database/migrations/2026_10_03_120644_create_oauth_device_codes_table.php
php artisan passport:keys
php artisan config:cache
php artisan route:cache
php artisan route:list --path=mcp
```

O comando `passport:keys` é necessário na primeira instalação. Se as chaves já existirem, preserve-as; não use `--force`. As chaves ficam em `storage` e já estão excluídas pelo `.gitignore`.

Confira que o host gera URLs HTTPS com o domínio canônico. Se a descoberta anunciar HTTP ou outro domínio, corrija a configuração de URL/proxy antes de conectar. Não desative autenticação para contornar um erro de conexão.

## Conferência após implantação

1. `GET /.well-known/oauth-authorization-server` deve anunciar autorização, token, registro e PKCE `S256`.
2. `GET /.well-known/oauth-protected-resource/mcp/extractor` deve apontar para o recurso MCP e o escopo `mcp:use`.
3. Um POST MCP sem token deve receber `401` e `WWW-Authenticate`.
4. Configure o plugin com a URL MCP, escolha OAuth/DCR quando o host solicitar e entre com sua conta administrativa do Papirar. Aprovar a conexão ocorre na página do Papirar.
5. Faça uma consulta de saúde, disciplinas e tópicos antes de autorizar um cadastro real.
6. Após um cadastro autorizado, consulte o ID gravado e confirme o estado `draft`, texto, alternativas e gabarito.

Para diagnóstico, um GET comum em `/mcp/extractor` retorna `405`: o transporte usa POST, portanto esse retorno sozinho não indica falha.

## Limites e retomada

- Lotes aceitam até 20 itens. Um HTTP 200 pode conter itens criados, duplicados e inválidos. Confira `summary` e cada entrada de `results`; não reenvie todo o lote automaticamente.
- Apenas a pesquisa de questões oferece paginação neste contrato. As consultas de bancas, provas, disciplinas, tópicos e fontes são limitadas por `per_page`; resultados não representam inventário completo.
- O servidor não armazena checkpoints de extração de PDFs. A skill precisa registrar a posição na conversa ou em um arquivo persistente autorizado.
- A taxonomia e o vínculo disciplina/tópico são validados pelos controllers existentes.
- A chave Bearer antiga não autentica este endpoint OAuth.

## Validação automatizada

```bash
php artisan test --filter=ExtractorMcpTest
```

Os testes usam SQLite isolado e fixtures próprias, pois as migrations anteriores da plataforma contêm DDL específico de MySQL. Verificam o protocolo HTTP, descoberta, OAuth/PKCE, permissões, os controllers reais de cadastro, duplicidades, sucesso parcial e paginação. Não fazem chamadas à produção.

Após a implantação, ainda é necessário testar o vínculo real no ChatGPT. Testes locais não comprovam DNS, HTTPS, configuração da Hostinger ou o consentimento do host.
