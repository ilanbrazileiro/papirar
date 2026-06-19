<div class="card mb-4 border-primary" id="course-marketing-card">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <div>
            <strong>Card comercial do curso</strong>
            <div class="small opacity-75">Capa, descrição curta e textos usados nos cards de venda do aluno.</div>
        </div>
        <span class="badge badge-light bg-light text-primary">Área comercial</span>
    </div>

    <div class="card-body">
        <div class="alert alert-light border mb-4">
            <strong>Onde aparece:</strong> estes campos alimentam os cards em <code>/aluno/cursos</code>, <code>/aluno/assinaturas</code> e a vitrine da dashboard quando o aluno ainda não comprou curso.
        </div>

        <div class="row g-3">
            <div class="col-md-5">
                <label for="cover_image" class="form-label font-weight-bold">Imagem/capa do card do curso</label>
                <input type="file" name="cover_image" id="cover_image" class="form-control" accept="image/jpeg,image/png,image/webp">
                <div class="text-muted small mt-1">Formatos: JPG, PNG ou WEBP. Recomendado: 1200x675 ou 16:9. Tamanho máximo: 4 MB.</div>

                @if($course->coverImageUrl())
                    <div class="mt-3">
                        <div class="text-muted small mb-2">Capa atual:</div>
                        <img src="{{ $course->coverImageUrl() }}" alt="Capa do curso" style="width: 100%; max-width: 360px; aspect-ratio: 16/9; object-fit: cover; border-radius: 14px; border: 1px solid #e5e7eb;">
                        <div class="form-check mt-2">
                            <input type="hidden" name="remove_cover_image" value="0">
                            <input type="checkbox" name="remove_cover_image" id="remove_cover_image" value="1" class="form-check-input">
                            <label for="remove_cover_image" class="form-check-label">Remover capa atual</label>
                        </div>
                    </div>
                @else
                    <div class="mt-3 p-4 rounded border text-center bg-light">
                        <div class="font-weight-bold">Sem capa cadastrada</div>
                        <div class="text-muted small">Enquanto não houver imagem, o card exibirá um placeholder do Papirar.</div>
                    </div>
                @endif
            </div>

            <div class="col-md-7">
                <div class="mb-3">
                    <label for="short_description" class="form-label font-weight-bold">Descrição curta do card</label>
                    <textarea name="short_description" id="short_description" class="form-control" rows="3" maxlength="255" placeholder="Ex.: Curso completo para treinar questões do CHOAE CBMERJ com simulados, comentários e acompanhamento de desempenho.">{{ old('short_description', $course->short_description ?? '') }}</textarea>
                    <div class="text-muted small mt-1">Texto principal do card. Use até 255 caracteres.</div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="sales_badge" class="form-label">Selo de destaque</label>
                        <input type="text" name="sales_badge" id="sales_badge" class="form-control" value="{{ old('sales_badge', $course->sales_badge ?? '') }}" maxlength="80" placeholder="Ex.: Mais procurado">
                    </div>

                    <div class="col-md-6">
                        <label for="workload_label" class="form-label">Resumo do conteúdo</label>
                        <input type="text" name="workload_label" id="workload_label" class="form-control" value="{{ old('workload_label', $course->workload_label ?? '') }}" maxlength="80" placeholder="Ex.: 12 disciplinas • 1.200 questões">
                    </div>
                </div>

                <div class="mt-3">
                    <label for="sales_headline" class="form-label">Chamada comercial</label>
                    <input type="text" name="sales_headline" id="sales_headline" class="form-control" value="{{ old('sales_headline', $course->sales_headline ?? '') }}" maxlength="180" placeholder="Ex.: Treine com foco no seu concurso interno.">
                    <div class="text-muted small mt-1">Quando preenchida, pode aparecer com mais destaque que a descrição curta.</div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <label for="target_audience" class="form-label">Público-alvo</label>
                        <input type="text" name="target_audience" id="target_audience" class="form-control" value="{{ old('target_audience', $course->target_audience ?? '') }}" maxlength="180" placeholder="Ex.: Praças do CBMERJ que vão disputar o CHOAE">
                    </div>

                    <div class="col-md-6">
                        <label for="guarantee_text" class="form-label">Texto de confiança</label>
                        <input type="text" name="guarantee_text" id="guarantee_text" class="form-control" value="{{ old('guarantee_text', $course->guarantee_text ?? '') }}" maxlength="180" placeholder="Ex.: Acesso imediato após confirmação do pagamento">
                    </div>
                </div>
            </div>

            <div class="col-12">
                <label for="sales_bullets_text" class="form-label">Benefícios em destaque</label>
                <textarea name="sales_bullets_text" id="sales_bullets_text" class="form-control" rows="5" placeholder="Um benefício por linha">{{ old('sales_bullets_text', $course->salesBulletsText()) }}</textarea>
                <div class="text-muted small mt-1">Use um benefício por linha. Ex.: Questões comentadas; Simulados por curso; Favoritas com anotações; Aulas por questão quando houver.</div>
            </div>
        </div>
    </div>
</div>
