{{-- Bloco comercial para inserir em resources/views/admin/courses/_form.blade.php --}}
<div class="card mb-4">
    <div class="card-header">
        <strong>Apresentação comercial do curso</strong>
        <div class="text-muted small">Esses dados aparecem nos cards do aluno e poderão ser usados futuramente na página pública de vendas.</div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="cover_image" class="form-label">Imagem/capa do curso</label>
                <input type="file" name="cover_image" id="cover_image" class="form-control" accept="image/jpeg,image/png,image/webp">
                <div class="text-muted small mt-1">Formatos: JPG, PNG ou WEBP. Recomendado: 1200x675.</div>
                @if($course->coverImageUrl())
                    <div class="mt-3">
                        <img src="{{ $course->coverImageUrl() }}" alt="Capa do curso" style="max-width: 260px; border-radius: 12px; border: 1px solid #e5e7eb;">
                        <div class="form-check mt-2">
                            <input type="checkbox" name="remove_cover_image" id="remove_cover_image" value="1" class="form-check-input">
                            <label for="remove_cover_image" class="form-check-label">Remover capa atual</label>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-md-6">
                <label for="sales_badge" class="form-label">Selo de destaque</label>
                <input type="text" name="sales_badge" id="sales_badge" class="form-control" value="{{ old('sales_badge', $course->sales_badge ?? '') }}" maxlength="80" placeholder="Ex.: Mais procurado, Novo, Teste grátis">

                <label for="workload_label" class="form-label mt-3">Resumo do conteúdo</label>
                <input type="text" name="workload_label" id="workload_label" class="form-control" value="{{ old('workload_label', $course->workload_label ?? '') }}" maxlength="80" placeholder="Ex.: 12 disciplinas • 1.200 questões">
            </div>

            <div class="col-12">
                <label for="sales_headline" class="form-label">Chamada comercial</label>
                <input type="text" name="sales_headline" id="sales_headline" class="form-control" value="{{ old('sales_headline', $course->sales_headline ?? '') }}" maxlength="180" placeholder="Ex.: Treine com questões direcionadas para o CHOAE CBMERJ.">
            </div>

            <div class="col-md-6">
                <label for="target_audience" class="form-label">Público-alvo</label>
                <input type="text" name="target_audience" id="target_audience" class="form-control" value="{{ old('target_audience', $course->target_audience ?? '') }}" maxlength="180" placeholder="Ex.: Praças do CBMERJ que vão disputar o CHOAE">
            </div>

            <div class="col-md-6">
                <label for="guarantee_text" class="form-label">Texto de confiança</label>
                <input type="text" name="guarantee_text" id="guarantee_text" class="form-control" value="{{ old('guarantee_text', $course->guarantee_text ?? '') }}" maxlength="180" placeholder="Ex.: Acesso imediato após confirmação do pagamento">
            </div>

            <div class="col-12">
                <label for="sales_bullets_text" class="form-label">Benefícios em destaque</label>
                <textarea name="sales_bullets_text" id="sales_bullets_text" class="form-control" rows="4" placeholder="Um benefício por linha">{{ old('sales_bullets_text', $course->salesBulletsText()) }}</textarea>
                <div class="text-muted small mt-1">Use um benefício por linha. Ex.: Questões comentadas; Simulados por curso; Favoritas com anotações.</div>
            </div>
        </div>
    </div>
</div>
