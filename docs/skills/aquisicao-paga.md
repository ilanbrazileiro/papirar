# Skill — Aquisição Paga Papirar

## Objetivo

Planejar e avaliar aquisição paga do Papirar com base em evidências do produto, do funil, do GA4 e, quando conectado, do Ads Manager. A Skill deve decidir onde investigar, testar, reduzir desperdício ou ampliar investimento sem tratar tráfego pago como solução automática para problemas de oferta, ativação ou conversão.

## Princípio central

Antes de aumentar mídia, verificar se o caminho após o clique está preparado:

**campanha → anúncio → landing page → cadastro → trial/ativação → pagamento → retenção**.

Uma campanha não deve ser considerada boa apenas por gerar impressões, cliques ou CTR. A avaliação deve usar o objetivo real e a melhor evidência disponível de conversão e valor.

## Fontes de dados

### Papirar MCP

Usar conforme a pergunta:

- `marketingHealth`
- `getMarketingFunnel`
- `getMarketingAcquisition`
- `getMarketingCourses`
- `getMarketingRevenue`
- `ga4Health`
- `getGa4Overview`
- `getGa4Acquisition`
- `getGa4LandingPages`
- `getGa4Events`
- ferramentas de leitura de cursos e cobertura.

### Ads Manager

Quando o Ads Manager estiver conectado e a solicitação envolver campanhas reais, usar as capacidades especializadas do Ads Manager em vez de tentar reproduzir sua lógica dentro do MCP Papirar.

Separar responsabilidades:

- métricas e comparações observadas → fluxo de insights do Ads Manager;
- revisão e recomendações de performance → fluxo de review do Ads Manager;
- problema de entrega → fluxo de delivery recovery;
- criação de nova campanha/anúncio → fluxo de criação de anúncios;
- alterações em campanha existente → fluxo de gerenciamento de entidades;
- revisão recorrente → fluxo de agente/review recorrente.

Esta Skill coordena a estratégia do Papirar e fornece contexto de negócio; não deve contornar as regras de confirmação e segurança do Ads Manager.

## Fluxo de diagnóstico

### 1. Definir o objetivo

Identificar a métrica de negócio antes de otimizar.

Possíveis objetivos:

- gerar cadastros;
- iniciar trials;
- gerar assinaturas pagas;
- promover um curso específico;
- validar uma nova oferta;
- recuperar eficiência de campanha existente.

Não escolher automaticamente ROAS, CPA, CTR ou CPC como KPI principal sem contexto.

### 2. Verificar o produto e a oferta

Antes de recomendar escala para um curso, verificar quando relevante:

- curso ativo e disponível;
- preço e trial atuais;
- landing page;
- cobertura de questões;
- estágio do concurso;
- sinais de conversão existentes.

Se houver um gargalo evidente depois do cadastro, aumentar tráfego pode apenas ampliar desperdício.

### 3. Ler o funil interno

Usar dados do Papirar para entender:

- cadastros;
- trials;
- pagantes;
- receita;
- conversão por curso quando disponível.

Distinguir problema de aquisição de problema de ativação/conversão.

### 4. Ler tráfego e landing pages

Usar GA4 quando disponível para observar:

- canais;
- sessões/comportamento;
- páginas de entrada;
- eventos relevantes.

Não usar GA4 como substituto automático dos dados atribuídos da plataforma de anúncios.

### 5. Ler mídia paga

Quando houver conta Ads Manager conectada, consultar dados atuais no escopo solicitado.

Para comparação de performance, usar períodos alinhados e dados completos. Não comparar campanhas com janelas diferentes como se fossem equivalentes.

### 6. Cruzar evidências

Organizar o diagnóstico em três camadas:

**Mídia:** entrega, gasto, impressões, cliques, CTR/CPC e conversões atribuídas quando disponíveis.

**Site:** landing pages, comportamento e eventos.

**Negócio:** cadastro, trial, pagamento, receita e retenção.

Não declarar causalidade quando as fontes não permitirem ligação confiável entre as camadas.

## Avaliação de campanhas

Evitar limites universais como “CTR abaixo de X é ruim” ou “CPA acima de Y deve pausar”.

Avaliar considerando:

- objetivo configurado;
- histórico da própria campanha;
- comparação com campanhas realmente comparáveis;
- volume de evidência;
- qualidade da mensuração;
- estágio do concurso/oferta;
- margem e valor econômico quando conhecidos.

## Orçamento

Não recomendar aumento apenas porque uma campanha teve alguns resultados positivos.

Antes de ampliar orçamento, verificar:

- evidência suficiente;
- estabilidade de mensuração;
- capacidade do funil de absorver tráfego;
- existência de curso/oferta adequada;
- ausência de gargalo crítico de conversão;
- comparação com alternativas de investimento.

Mudanças de orçamento em campanhas existentes devem seguir o fluxo de revisão/alteração do Ads Manager e suas confirmações.

## Redução ou pausa

Não pausar automaticamente por desempenho aparente ruim.

Primeiro investigar:

- entrega;
- rastreamento/conversões;
- landing page;
- objetivo da campanha;
- janela de análise;
- volume da amostra;
- mudanças recentes.

Uma recomendação de pausa deve ser baseada em evidência suficiente e executada somente após autorização no fluxo apropriado.

## Criativos

Analisar criativos dentro do contexto da campanha.

Possíveis testes:

- questão/desafio;
- benefício do estudo por questões;
- demonstração do produto;
- dor específica do candidato;
- preparação para concurso específico;
- prova social real quando disponível e autorizada.

Não inventar depoimentos, aprovações, número de alunos ou resultados.

Não afirmar “essa questão vai cair” ou aprovação garantida.

## Landing pages

Verificar coerência entre anúncio e página.

O usuário deve encontrar na landing aquilo que o anúncio prometeu.

Possíveis problemas:

- anúncio fala de um concurso e página é genérica;
- CTA pouco claro;
- curso sem cobertura suficiente;
- promessa do anúncio não aparece na página;
- excesso de etapas até cadastro;
- tráfego chegando a página inadequada.

Não atribuir baixa conversão à landing sem evidência suficiente.

## Públicos e segmentação

Não assumir que um público é bom apenas por parecer intuitivamente relacionado a militares ou concursos.

Usar evidência disponível e testes controlados.

Mudanças de geografia, plataforma, público ou outros parâmetros devem respeitar as capacidades e regras do Ads Manager conectado.

## Experimentos

Cada recomendação de teste deve especificar:

- hipótese;
- variável alterada;
- elemento mantido constante quando possível;
- público/curso/campanha;
- KPI principal;
- métrica de proteção;
- condição de avaliação;
- principal risco.

Evitar mudar criativo, público, landing e orçamento simultaneamente quando o objetivo é aprender qual fator produziu a diferença.

## Priorização

Priorizar nesta ordem quando aplicável:

1. bloqueios de entrega;
2. problemas de mensuração;
3. desperdício evidente sustentado por dados;
4. desalinhamento anúncio → landing → oferta;
5. oportunidades de orçamento/bid;
6. segmentação;
7. criativos.

Um problema anterior no funil pode invalidar recomendações posteriores.

## Estrutura da resposta

### Situação

Resumo do objetivo, escopo e período.

### Evidências

Separar dados de Ads Manager, GA4 e Papirar quando necessário.

### Diagnóstico

Indicar o principal gargalo observável e o nível de confiança.

### Recomendação

No máximo algumas ações prioritárias, evitando lista extensa de mudanças simultâneas.

### Experimento

Quando apropriado, definir o próximo teste mensurável.

### Limitações

Explicitar ausência de atribuição, amostra pequena, dados incompletos ou mensuração duvidosa.

## Operações no Ads Manager

Uma análise não autoriza escrita.

Se o usuário pedir apenas análise, permanecer read-only.

Se o usuário pedir uma alteração real, usar a Skill/ferramenta proprietária correspondente do Ads Manager e seguir seu fluxo de preview/confirmação.

Nunca interpretar “otimize”, “melhore” ou “revise” como autorização automática para editar campanhas.

## Integração com outras Skills

- decisão sobre prioridade entre canais → **Marketing Estratégico**;
- landing e conteúdo orgânico → **SEO e Conteúdo**;
- criativos orgânicos e pautas → **Redes Sociais**;
- compartilhamento e aquisição não paga → **Growth e Distribuição**;
- cadastro, trial e renovação → **Funil e Retenção**.

## Exemplos de solicitações

- “Analise a campanha paga do Papirar.”
- “Estamos gastando bem?”
- “Vale aumentar o orçamento?”
- “Qual curso faz mais sentido anunciar?”
- “Por que temos clique mas pouco cadastro?”
- “Compare as campanhas com os resultados do Papirar.”
- “Que criativo devemos testar agora?”
- “Monte um plano de aquisição paga para SD PMERJ.”

## Resultado esperado

A Skill deve conectar mídia a resultado de negócio:

**objetivo → evidência de mídia → comportamento no site → resultado no Papirar → gargalo → experimento/recomendação → medição**.

O foco não é gastar mais ou reduzir gasto por padrão. É usar mídia paga somente quando ela contribui de forma mensurável para o objetivo do Papirar.
