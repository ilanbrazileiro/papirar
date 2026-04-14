@extends('layouts.student')

@section('title', 'Abrir ticket')

@section('content')
    <div class="mb-4">
        <h1 class="page-title">Abrir ticket</h1>
        <p class="page-subtitle">Escolha a categoria, escreva a mensagem inicial e envie anexos, se necessário. O assunto será definido automaticamente.</p>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card-soft p-4 p-md-5">
                <form method="POST" action="{{ route('student.tickets.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Categoria</label>
                            <select name="category" class="form-select" required>
                                <option value="">Selecione</option>
                                <option value="suggestion" @selected(old('category') === 'suggestion')>Sugestão</option>
                                <option value="technical" @selected(old('category') === 'technical')>Problema técnico</option>
                                <option value="financial" @selected(old('category') === 'financial')>Problema financeiro</option>
                                <option value="question_submission" @selected(old('category') === 'question_submission')>Enviar questões para avaliação</option>
                            </select>
                            @error('category')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Mensagem inicial</label>
                            <textarea name="message" rows="8" class="form-control" required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Anexos</label>
                            <input
                                type="file"
                                name="attachments[]"
                                class="form-control"
                                accept=".jpg,.jpeg,.png,.pdf"
                                multiple
                            >
                            <div class="form-text">
                                Você pode enviar imagens JPG/PNG e arquivos PDF. Limite de 5 MB por arquivo.
                            </div>
                            @error('attachments.*')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button class="btn btn-primary">Abrir ticket</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-soft p-4">
                <div class="section-title">Dicas</div>
                <div class="small-muted mb-2">Para problema técnico, informe:</div>
                <ul class="small-muted mb-3">
                    <li>qual tela apresentou erro</li>
                    <li>o que você tentou fazer</li>
                    <li>mensagem exibida, se houver</li>
                    <li>prints da tela, se possível</li>
                </ul>

                <div class="small-muted mb-2">Para envio de questões, informe:</div>
                <ul class="small-muted mb-0">
                    <li>origem da questão</li>
                    <li>disciplina</li>
                    <li>enunciado e alternativas</li>
                    <li>gabarito, se souber</li>
                    <li>PDF ou imagens, se necessário</li>
                </ul>
            </div>
        </div>
    </div>
@endsection
