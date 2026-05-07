@extends('admin.layout')

@section('content')
<div class="card-soft p-4">
    <h1 class="page-title mb-4">Editar plano</h1>

    <form method="POST" action="{{ route('admin.plans.update', $plan) }}">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nome</label>
                <input class="form-control" name="name" value="{{ old('name', $plan->name) }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Slug</label>
                <input class="form-control" name="slug" value="{{ old('slug', $plan->slug) }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Preço</label>
                <input class="form-control" name="price" value="{{ old('price', $plan->price) }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Duração (dias)</label>
                <input class="form-control" name="duration_days" value="{{ old('duration_days', $plan->duration_days) }}" required>
            </div>

            <div class="col-md-6">
                <div class="form-check mt-2">
                    <input type="hidden" name="active" value="0">
                    <input class="form-check-input" type="checkbox" name="active" id="active" value="1" {{ old('active', $plan->active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="active">Plano ativo</label>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-check mt-2">
                    <input type="hidden" name="is_public" value="0">
                    <input class="form-check-input" type="checkbox" name="is_public" id="is_public" value="1" {{ old('is_public', $plan->is_public) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_public">Exibir para o aluno</label>
                </div>
                <small class="text-muted">Desmarque para planos internos, como teste grátis e liberação manual.</small>
            </div>
        </div>

        <button class="btn btn-primary mt-4">Salvar</button>
    </form>
</div>
@endsection
