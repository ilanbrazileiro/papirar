# Skill — Funil e Retenção Papirar

## Objetivo

Diagnosticar e melhorar a jornada do usuário no Papirar desde a primeira visita até o estudo recorrente, pagamento e renovação. A Skill deve localizar onde os usuários deixam de avançar, formular hipóteses e propor experimentos mensuráveis sem confundir aquisição com ativação, conversão ou retenção.

## Jornada principal

Usar como modelo conceitual:

**visita → cadastro → trial/acesso → primeira sessão de estudo → ativação → recorrência → pagamento → continuidade → renovação**.

Nem todas as etapas estarão instrumentadas. Quando uma etapa não puder ser medida, tratá-la como lacuna de instrumentação, não como taxa zero.

## Fontes e ferramentas

### Dados internos

Usar conforme necessário:

- `marketingHealth`
- `getMarketingFunnel`
- `getMarketingAcquisition`
- `getMarketingCourses`
- `getMarketingRevenue`

### GA4

Usar quando disponível:

- `ga4Health`
- `getGa4Overview`
- `getGa4Acquisition`
- `getGa4LandingPages`
- `getGa4Events`

### Contexto do produto

Consultar ferramentas de leitura de cursos, cobertura, questões e catálogo quando isso puder explicar o comportamento observado.

Não assumir que um usuário abandonou por UX se o curso de interesse estiver incompleto, indisponível ou inadequado.

## Conceitos

### Aquisição

O usuário chega ao Papirar.

### Cadastro

O visitante cria uma conta.

### Trial/acesso inicial

O usuário obtém acesso inicial ao produto quando aplicável.

### Ativação

O usuário realiza uma ação que demonstra que começou efetivamente a receber valor do Papirar.

A definição exata de ativação deve ser baseada em eventos disponíveis. Possíveis sinais incluem iniciar estudo, responder questões ou concluir uma primeira sessão. Não escolher uma definição sem verificar a instrumentação atual.

### Retenção

O usuário retorna e continua estudando ao longo do tempo.

### Conversão paga

O usuário passa a uma relação paga válida.

### Renovação

O usuário mantém ou renova a assinatura quando o modelo comercial aplicável permitir essa medição.

## Fluxo de diagnóstico

### 1. Definir coorte e período

Sempre que possível, analisar grupos comparáveis:

- data de cadastro;
- curso;
- concurso;
- origem de aquisição;
- trial;
- pagante/não pagante.

Não comparar usuários com tempo de exposição muito diferente sem ressalva.

### 2. Construir o funil observável

Consultar `getMarketingFunnel` e demais fontes necessárias.

Representar apenas etapas que os dados realmente sustentam.

Exemplo:

- visitantes;
- cadastros;
- trials;
- usuários que estudaram;
- pagantes.

Se “usuários que estudaram” não estiver disponível, marcar a lacuna em vez de inferi-la.

### 3. Encontrar a maior perda

Localizar onde existe a maior queda relevante entre etapas comparáveis.

A maior queda percentual não é automaticamente a maior oportunidade econômica. Considerar volume, importância da etapa e qualidade da medição.

### 4. Investigar causas possíveis

Cruzar quando necessário:

- origem do usuário;
- landing page;
- curso;
- disponibilidade de conteúdo;
- eventos;
- preço/trial;
- comportamento de estudo.

Separar causa comprovada de hipótese.

### 5. Escolher um experimento

Priorizar a etapa que limita o resultado atual.

Não tentar otimizar todo o funil simultaneamente.

## Cadastro

Quando houver muito tráfego e pouco cadastro, investigar:

- intenção do tráfego;
- coerência da landing;
- clareza do CTA;
- promessa versus oferta;
- atrito observável;
- problemas técnicos quando houver evidência.

Não assumir que formulário longo ou design são a causa sem verificar.

## Trial

O trial deve levar o usuário ao valor do produto rapidamente.

Avaliar:

- quantos usuários elegíveis iniciam;
- se encontram o curso correto;
- se começam a estudar;
- quais recursos são usados;
- quanto tempo leva até a primeira ação de valor, quando mensurável;
- conversão posterior.

Não recomendar simplesmente aumentar a duração do trial sem entender o gargalo.

## Ativação

Uma boa ativação deve representar valor recebido, não apenas clique.

Possíveis marcos a validar:

- primeira questão respondida;
- conjunto mínimo de questões;
- primeira sessão concluída;
- primeiro simulado;
- primeiro retorno ao caderno de erros.

A Skill deve recomendar instrumentação quando não for possível medir esses marcos.

## Retenção e hábito de estudo

Recursos do Papirar podem contribuir para recorrência quando estiverem realmente implementados e ativos:

- metas diárias;
- streak;
- caderno de erros;
- simulados;
- progresso;
- continuidade de sessão/estudo;
- filtros e cursos.

Não assumir que a existência de um recurso significa que ele aumenta retenção. Medir uso e retorno quando houver dados.

## Caderno de erros

Pode ser tratado como mecanismo de retorno:

**errar → registrar → revisar posteriormente → perceber evolução → continuar estudando**.

Possíveis experimentos devem avaliar se o recurso é descoberto e reutilizado, não apenas se existe.

## Metas e streak

Metas e streak devem incentivar consistência, não comportamento compulsivo.

Evitar:

- punição excessiva por perder sequência;
- mensagens de culpa;
- pressão artificial;
- mecânicas que incentivem uso sem valor de estudo.

Avaliar se usuários que utilizam esses recursos retornam mais, sem concluir causalidade apenas por correlação.

## Simulados

Podem atuar em múltiplos pontos:

- ativação;
- percepção de valor;
- retorno;
- compartilhamento;
- conversão.

Medir separadamente quando possível:

- início;
- conclusão;
- resultado;
- retorno posterior;
- compartilhamento;
- conversão.

## Conversão trial → pago

Investigar:

- uso durante trial;
- curso;
- cobertura;
- preço;
- momento da oferta;
- clareza do valor;
- origem de aquisição;
- problemas de pagamento quando observáveis.

Não recomendar desconto automaticamente. Redução de preço pode aumentar conversão e reduzir receita/margem ou percepção de valor.

Qualquer alteração real de preço exige confirmação explícita e ferramenta apropriada.

## Pagamento e abandono

Distinguir quando possível:

- usuário que nunca tentou pagar;
- usuário que iniciou pagamento;
- pagamento pendente;
- falha;
- pagamento aprovado;
- usuário que não renovou.

Esses comportamentos exigem intervenções diferentes.

Não classificar falha técnica de pagamento como falta de interesse.

## Renovação

Quando houver dados suficientes, analisar:

- usuários elegíveis;
- renovações;
- tempo de uso;
- atividade antes da renovação;
- curso;
- valor econômico.

Se o produto ainda não tiver histórico suficiente, não produzir conclusões de retenção de longo prazo.

## Segmentação

Uma taxa geral pode esconder diferenças importantes.

Quando o volume permitir, comparar:

- curso;
- concurso;
- origem;
- campanha;
- período de cadastro;
- trial/pago;
- nível de atividade.

Evitar segmentos tão pequenos que gerem conclusões instáveis.

## Experimentos

Cada experimento deve definir:

- etapa do funil;
- hipótese;
- segmento;
- mudança;
- métrica principal;
- métrica de proteção;
- janela/condição de avaliação;
- risco.

Exemplo:

**Problema:** muitos usuários iniciam trial, poucos começam a estudar.

**Hipótese:** usuários não identificam rapidamente o curso correspondente ao concurso.

**Experimento:** após iniciar trial, apresentar acesso direto ao curso escolhido no cadastro, caso esse dado exista e seja confiável.

**Métrica principal:** percentual de novos trials que iniciam uma sessão de estudo.

A implementação da mudança continua sendo uma decisão separada.

## Métricas de proteção

Não otimizar uma métrica isoladamente sacrificando outra importante.

Exemplos:

- aumentar cadastro mas reduzir qualidade dos usuários;
- aumentar início de trial mas reduzir ativação;
- aumentar conversão com desconto excessivo e reduzir receita;
- aumentar frequência de uso com notificações invasivas.

## Priorização

Ordenar oportunidades por:

1. tamanho e importância do gargalo;
2. qualidade da evidência;
3. impacto potencial;
4. esforço;
5. risco;
6. capacidade de medir o resultado.

Preferir corrigir um gargalo estrutural antes de aumentar aquisição para o mesmo funil.

## Estrutura da resposta

### Funil observado

Mostrar etapas e taxas suportadas pelos dados.

### Maior gargalo

Indicar onde a perda mais relevante ocorre.

### Evidências

Separar fatos de hipóteses.

### Segmentos afetados

Indicar se o problema é geral ou concentrado.

### Próximo experimento

Propor uma mudança mensurável.

### Instrumentação faltante

Listar somente lacunas que realmente impedem decisões relevantes.

## Integração com outras Skills

- prioridade global → **Marketing Estratégico**;
- origem e qualidade do tráfego pago → **Aquisição Paga**;
- origem orgânica e landing pages → **SEO e Conteúdo**;
- conteúdo de relacionamento → **Redes Sociais**;
- compartilhamento e indicação → **Growth e Distribuição**;
- problemas de cobertura/qualidade de questões → **Revisor/Auditoria**.

## Segurança e limites

Não executar automaticamente:

- alteração de preço;
- mudança de trial;
- publicação/desativação de curso;
- envio em massa de mensagens;
- notificações invasivas;
- mudanças estruturais de produto;
- alterações de acesso/assinatura de usuários.

Operações reais devem usar ferramenta apropriada e confirmação quando exigida.

## Exemplos de solicitações

- “Onde estamos perdendo usuários?”
- “Por que os trials não viram assinatura?”
- “Analise a retenção do Papirar.”
- “Os usuários estão realmente estudando depois de se cadastrar?”
- “Como usar o caderno de erros para aumentar retorno?”
- “Metas e streak estão ajudando?”
- “Qual etapa do onboarding devemos melhorar primeiro?”
- “Compare a conversão dos cursos.”

## Resultado esperado

A Skill deve transformar métricas de jornada em uma decisão operacional:

**coorte → etapa → queda → evidência → hipótese → experimento → métrica → aprendizado**.

O objetivo final não é manter o usuário conectado por mais tempo. É aumentar a frequência com que ele recebe valor real do estudo e, como consequência, melhorar ativação, retenção e sustentabilidade do Papirar.
