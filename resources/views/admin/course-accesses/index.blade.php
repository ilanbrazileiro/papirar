@extends('layouts.admin')

@section('title', 'Acessos a cursos')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0">Acessos a cursos</h1>
            <p class="text-muted mb-0">Controle manual, trial, bônus e acessos pagos dos alunos.</p>
        </div>
        <a href="{{ route('admin.course-accesses.create') }}" class="btn btn-primary">Novo acesso</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Aluno, e-mail, CPF ou curso" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="course_id" class="form-control">
                        <option value="">Todos os cursos</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" @selected((int) request('course_id') === (int) $course->id)>{{ $course->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-control">
                        <option value="">Todos os status</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="access_type" class="form-control">
                        <option value="">Todos os tipos</option>
                        @foreach($accessTypes as $value => $label)
                            <option value="{{ $value }}" @selected(request('access_type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary btn-block">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Curso</th>
                        <th>Status</th>
                        <th>Tipo</th>
                        <th>Fim</th>
                        <th>Assinatura</th>
                        <th>Último pagamento</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accesses as $access)
                        @php
                            $lastTransaction = $access->subscription?->transactions?->sortByDesc('id')->first();
                            $paymentStatusLabel = [
                                'pending' => 'Pendente',
                                'paid' => 'Pago',
                                'failed' => 'Falhou',
                                'refunded' => 'Reembolsado',
                                'canceled' => 'Cancelado',
                            ][$lastTransaction?->status] ?? ($lastTransaction?->status ?: '-');
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $access->user->name ?? 'Aluno removido' }}</strong><br>
                                <small class="text-muted">{{ $access->user->email ?? '' }}</small>
                            </td>
                            <td>{{ $access->course->title ?? 'Curso removido' }}</td>
                            <td><span class="badge badge-{{ $access->statusBadgeClass() }}">{{ $access->statusLabel() }}</span></td>
                            <td>{{ $access->accessTypeLabel() }}</td>
                            <td>
                                {{ optional($access->ends_at)->format('d/m/Y H:i') ?? 'Sem limite' }}
                                @if($access->cancel_at_period_end)
                                    <br><small class="text-muted">Cancela no fim</small>
                                @endif
                            </td>
                            <td>
                                @if($access->subscription)
                                    #{{ $access->subscription->id }}<br>
                                    <small class="text-muted">
                                        {{ $access->subscription->billing_cycle ?? '-' }} · R$ {{ number_format((float) $access->subscription->amount, 2, ',', '.') }}
                                    </small>
                                @else
                                    <span class="text-muted">Sem assinatura</span>
                                @endif
                            </td>
                            <td>
                                {{ $paymentStatusLabel }}
                                @if($lastTransaction)
                                    <br><small class="text-muted">#{{ $lastTransaction->id }} · R$ {{ number_format((float) $lastTransaction->amount, 2, ',', '.') }}</small>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.course-accesses.edit', $access) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                @if($access->status !== 'canceled')
                                    <form method="POST" action="{{ route('admin.course-accesses.cancel', $access) }}" class="d-inline" onsubmit="return confirm('Cancelar este acesso? O fim do período vigente será preservado.');">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-outline-danger">Cancelar</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Nenhum acesso encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $accesses->links() }}
    </div>
</div>
@endsection
