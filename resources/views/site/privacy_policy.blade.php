@extends('site.site_layout')

@section('title')
Política de Privacidade | Papirar Concursos
@endsection

@section('meta_description')
Política de Privacidade do Papirar Concursos e informações sobre tratamento de dados pessoais, segurança, integrações e direitos dos titulares.
@endsection

@section('canonical')
{{ url('/politica-de-privacidade') }}
@endsection

@section('content')
<section class="section">
    <div class="site-container">
        <div style="max-width:920px;margin:0 auto;background:#fff;border-radius:18px;padding:36px;box-shadow:0 12px 34px rgba(11,31,58,.08)">
            <span class="eyebrow">Privacidade e proteção de dados</span>
            <h1>Política de Privacidade</h1>
            <p><strong>Última atualização:</strong> {{ date('d/m/Y') }}</p>

            <p>O Papirar Concursos respeita a privacidade de seus usuários e busca tratar dados pessoais de forma transparente, segura e compatível com a Lei Geral de Proteção de Dados Pessoais (Lei nº 13.709/2018 — LGPD).</p>

            <h2>1. Responsável pelo tratamento</h2>
            <p>Para fins desta Política, o Papirar Concursos atua como controlador dos dados pessoais tratados diretamente em sua plataforma, observadas as hipóteses em que terceiros atuem como controladores independentes ou operadores.</p>

            <h2>2. Dados que podem ser tratados</h2>
            <ul>
                <li>dados de cadastro, como nome e e-mail;</li>
                <li>dados de autenticação e segurança;</li>
                <li>informações de assinatura, compra, pagamento e situação de acesso;</li>
                <li>dados de uso, como questões respondidas, desempenho, favoritos, comentários e progresso;</li>
                <li>registros técnicos, como IP, data e hora, navegador, dispositivo e logs;</li>
                <li>informações fornecidas voluntariamente em suporte ou contato.</li>
            </ul>

            <h2>3. Finalidades</h2>
            <ul>
                <li>criar e administrar contas;</li>
                <li>fornecer cursos, questões, simulados e demais funcionalidades;</li>
                <li>registrar desempenho e progresso;</li>
                <li>processar compras, assinaturas e pagamentos;</li>
                <li>prestar suporte;</li>
                <li>prevenir fraude, uso indevido e acessos não autorizados;</li>
                <li>cumprir obrigações legais e regulatórias;</li>
                <li>melhorar segurança, estabilidade e experiência;</li>
                <li>permitir integrações técnicas autorizadas, inclusive ferramentas de inteligência artificial.</li>
            </ul>

            <h2>4. Bases legais</h2>
            <p>O tratamento poderá ocorrer, conforme o caso, com fundamento na execução de contrato, cumprimento de obrigação legal ou regulatória, legítimo interesse, exercício regular de direitos, consentimento ou outras hipóteses previstas na LGPD.</p>

            <h2>5. Pagamentos</h2>
            <p>O Papirar pode utilizar prestadores de pagamento para processar compras e assinaturas. Quando dados financeiros forem processados diretamente por esses prestadores, também estarão sujeitos às respectivas políticas e medidas de segurança.</p>

            <h2>6. Integrações, APIs e inteligência artificial</h2>
            <p>O Papirar pode disponibilizar APIs e integrações autorizadas para criação, consulta, revisão ou administração de conteúdos. Quando uma funcionalidade utilizar serviços de terceiros, inclusive inteligência artificial, as informações necessárias à execução poderão ser transmitidas ao respectivo fornecedor.</p>
            <p>Recomenda-se não incluir dados pessoais desnecessários, sigilosos ou sensíveis em conteúdos destinados a essas integrações. As integrações do Papirar destinam-se principalmente ao tratamento de conteúdo educacional, como questões, alternativas, disciplinas, tópicos, comentários e referências.</p>

            <h2>7. Compartilhamento</h2>
            <p>Dados podem ser compartilhados, na medida necessária, com prestadores de hospedagem, infraestrutura, autenticação, pagamentos, suporte, comunicação, segurança e integrações tecnológicas, bem como quando exigido por lei, ordem judicial ou autoridade competente.</p>

            <h2>8. Cookies</h2>
            <p>O site pode utilizar cookies necessários para autenticação, sessão, segurança, preferências e funcionamento da plataforma.</p>

            <h2>9. Retenção</h2>
            <p>Os dados são mantidos pelo período necessário ao cumprimento das finalidades descritas, da relação com o usuário e de obrigações legais, regulatórias, contratuais ou de defesa de direitos.</p>

            <h2>10. Segurança</h2>
            <p>O Papirar adota medidas técnicas e administrativas destinadas a reduzir riscos de acesso não autorizado, alteração, perda, destruição ou divulgação indevida. Nenhum sistema conectado à internet pode garantir segurança absoluta.</p>

            <h2>11. Direitos dos titulares</h2>
            <p>Nos termos da LGPD, o titular pode exercer, quando aplicável, direitos como confirmação da existência de tratamento, acesso, correção, anonimização, bloqueio ou eliminação de dados inadequados, portabilidade, informação sobre compartilhamentos, revogação do consentimento, oposição e revisão de decisões automatizadas.</p>

            <h2>12. Exercício de direitos</h2>
            <p>Solicitações relacionadas à privacidade e proteção de dados podem ser encaminhadas pelos canais oficiais de atendimento disponibilizados pelo Papirar Concursos. Poderá ser solicitada confirmação de identidade para proteção do titular.</p>

            <h2>13. Menores</h2>
            <p>Quando houver tratamento de dados de crianças ou adolescentes, serão observadas as exigências legais aplicáveis e o melhor interesse do menor.</p>

            <h2>14. Serviços de terceiros</h2>
            <p>Links e integrações externas possuem políticas próprias. O Papirar não controla as práticas de privacidade desses terceiros.</p>

            <h2>15. Alterações</h2>
            <p>Esta Política poderá ser atualizada para refletir mudanças legais, técnicas, operacionais ou de funcionalidades. A versão vigente será disponibilizada nesta página.</p>

            <h2>16. Contato</h2>
            <p>Para dúvidas sobre esta Política ou solicitações relacionadas à proteção de dados, utilize os canais oficiais de atendimento do Papirar Concursos disponíveis na plataforma.</p>
        </div>
    </div>
</section>
@endsection
