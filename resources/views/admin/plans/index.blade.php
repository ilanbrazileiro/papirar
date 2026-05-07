@extends('admin.layout')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="page-title">Planos</h1>
    <a class="btn btn-primary" href="{{ route('admin.plans.create') }}">Novo plano</a>
</div>

<div class="card-soft p-4">
    <table class="table align-middle">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Preço</th>
                <th>Duração</th>
                <th>Status</th>
                <th>Visibilidade</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>
                        <strong>{{ $item->name }}</strong><br>
                        <small class="text-muted">{{ $item->slug }}</small>
                    </td>
                    <td>R$ {{ number_format((float) $item->price, 2, ',', '.') }}</td>
                    <td>{{ $item->duration_days }} dias</td>
                    <td>
                        @if($item->active)
                            <span class="badge bg-success">Ativo</span>
                        @else
                            <span class="badge bg-secondary">Inativo</span>
                        @endif
                    </td>
                    <td>
                        @if($item->is_public)
                            <span class="badge bg-primary">Público</span>
                        @else
                            <span class="badge bg-warning text-dark">Interno</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.plans.edit', $item) }}">Editar</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $items->links() }}
</div>
@endsection
