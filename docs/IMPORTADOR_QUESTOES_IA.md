# Importador de questões por IA

## Escopo deste lote

- upload de PDF, DOCX, JPG e PNG;
- arquivo da prova obrigatório;
- arquivo separado do gabarito opcional quando o gabarito já estiver dentro do documento da prova;
- processamento conjunto de prova e gabarito quando enviados em dois arquivos;
- corporação e vínculo com prova cadastrada opcionais;
- origem selecionável: prova oficial, autoral ou adaptada;
- banca sempre opcional;
- ano e cargo/concurso obrigatórios somente para prova oficial;
- Gemini 3.5 Flash com nova tentativa imediata e fallback para Gemini 3.5 Flash-Lite em `429`/`RESOURCE_EXHAUSTED` ou `503`/`UNAVAILABLE`;
- se ambos falharem com `503`, o job é reagendado até três vezes (aproximadamente 1, 3 e 7 minutos, mais alguns segundos aleatórios); depois da quarta execução, o lote é marcado como falha;
- retorno JSON estruturado;
- conferência inicial da numeração da prova e extração em blocos de até 12 questões;
- uma nova tentativa para números não retornados; resposta interrompida ou numeração incompleta não gera prévia parcial;
- classificação restrita às disciplinas e tópicos existentes;
- referência gerada pelo Laravel;
- validação, duplicidade, conferência e importação como `draft`;
- histórico de modelo, tentativas, erro e uso informado pela API.

Os revisores automáticos não fazem parte deste lote.

## Implantação

1. Fazer backup do banco.
2. Aplicar `database/sql/2026_09_18_question_ai_import.sql` em produção.
3. Aplicar `database/sql/2026_09_18_queue_tables.sql` se as tabelas `jobs` e `failed_jobs` ainda não existirem.
4. Publicar os arquivos da aplicação.
5. Configurar no `.env`:

```dotenv
GEMINI_API_KEY=chave_do_projeto
GEMINI_PRIMARY_MODEL=gemini-3.5-flash
GEMINI_FALLBACK_MODEL=gemini-3.5-flash-lite
GEMINI_RETRY_DELAY=10
GEMINI_CLASSIFICATION_CONFIDENCE=0.75
GEMINI_TIMEOUT=600
DB_QUEUE_RETRY_AFTER=1200
```

6. Limpar cache de configuração:

```bash
php artisan optimize:clear
```

7. Manter um worker de fila ativo, pois a extração não deve ocorrer dentro da requisição web:

```bash
php artisan queue:work --queue=default --stop-when-empty --tries=1 --timeout=900
```

## Retenção dos arquivos enviados

- Durante as tentativas automáticas por `503`, o lote permanece em processamento e os arquivos são preservados. Com worker executado pelo cron a cada minuto, a tentativa reagendada entra na próxima execução disponível.
- Uma prova maior exige várias chamadas ao Gemini e pode demorar mais que uma prova curta; o consumo da API também cresce.
- Se a extração terminar incompleta, o lote recebe a lista dos números faltantes como erro e os arquivos seguem a retenção de falhas de 24 horas. A contagem depende da numeração que a IA identifica na prova e deve ser conferida pelo usuário.
- Após a extração ser concluída e a prévia ficar gravada no banco, os arquivos da prova e do gabarito são excluídos imediatamente.
- Os nomes originais permanecem no lote para rastreabilidade.
- Em caso de falha, os arquivos ficam disponíveis por 24 horas para diagnóstico; a exclusão é agendada na própria fila.
- Lotes cancelados têm seus arquivos excluídos imediatamente.
- Arquivos antigos remanescentes podem ser limpos manualmente com `questions:cleanup-import-files --hours=24`.

## Regras relevantes

- Os arquivos ficam no disco privado `local`, em `question-imports/{batch_id}`.
- A soma da prova e do gabarito está limitada a 18 MB porque esta versão usa dados inline na requisição Gemini.
- DOCX é transformado em texto no servidor. Imagens incorporadas não são enviadas; nesses casos, usar PDF.
- Prova oficial: referência `BANCA (quando houver) - ANO - CARGO/CONCURSO`.
- Autoral: referência `Papirar Concursos - Questão autoral`.
- Para importar questões autorais, envie um PDF ou DOCX contendo as questões e o respectivo gabarito (no mesmo arquivo ou separado) e selecione `Questões autorais`. Banca, prova vinculada, ano e cargo/concurso são ignorados nessa origem.
- Este lote extrai e classifica questões existentes no arquivo; ele não gera novas questões dentro da tela de importação. Questões criadas previamente podem ser reunidas em DOCX/PDF e importadas pelo fluxo autoral.
- Adaptada: usa os dados de origem informados e acrescenta `Questão adaptada`.
- A questão criada mantém `question_import_batch_id`, permitindo rastrear o lote, os arquivos e as tentativas que a originaram.
- A IA nunca cria disciplina ou tópico.
- Classificação abaixo do limite configurado fica bloqueada como pendência até a conferência humana.
- Questões anuladas entram como erro de conferência e não são importadas automaticamente.
- Questões que dependem de imagem, gráfico, tabela ou diagrama ficam bloqueadas para conferência; este lote não recorta imagens automaticamente.
- A estrutura atual do Papirar exige cinco alternativas. Questões com outra quantidade ficam pendentes, sem criação de texto artificial.
