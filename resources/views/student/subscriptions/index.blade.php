@extends('layouts.student')

@section('title', 'Assinaturas')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Assinaturas</h1>
            <p class="page-subtitle">Escolha seu plano e acompanhe o status da sua assinatura atual.</p>
        </div>
        <a href="{{ route('student.subscriptions.history') }}" class="btn btn-outline-primary">Ver histórico</a>
    </div>

    @if ($paymentStatus === 'success')
        <div class="alert alert-success">Pagamento enviado ao Mercado Pago. Assim que o webhook confirmar a aprovação, seu acesso será liberado automaticamente.</div>
    @elseif ($paymentStatus === 'pending')
        <div class="alert alert-warning">Seu pagamento está pendente. Assim que houver confirmação, a assinatura será ativada.</div>
    @elseif ($paymentStatus === 'failure')
        <div class="alert alert-danger">O pagamento não foi concluído. Você pode tentar novamente.</div>
    @endif

    @if($currentSubscription)
        <div class="card-soft p-4 mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <div class="small-muted mb-1">Assinatura atual</div>
                    <div class="fw-bold fs-5">{{ $currentSubscription->plan->name ?? 'Plano atual' }}</div>
                    <div class="small-muted">
                        Status: <span class="fw-semibold">{{ strtoupper($currentSubscription->status) }}</span>
                        @if($currentSubscription->expires_at)
                            · expira em {{ $currentSubscription->expires_at->format('d/m/Y H:i') }}
                        @endif
                    </div>
                </div>
                <div class="text-md-end small-muted">
                    O acesso às questões depende de uma assinatura ativa.
                </div>
            </div>
        </div>
    @endif

    <div class="row g-4">
        @forelse($plans as $plan)
            <div class="col-md-6 col-xl-4">
                <div class="card-soft p-4 h-100 d-flex flex-column">
                    <div class="small-muted text-uppercase mb-2">Plano</div>
                    <div class="fs-4 fw-bold">{{ $plan->name }}</div>
                    @if(!empty($plan->description))
                        <div class="small-muted mt-2">{{ $plan->description }}</div>
                    @endif
                    <div class="display-6 fw-bold my-3">R$ {{ number_format((float) $plan->price, 2, ',', '.') }}</div>
                    <div class="small-muted mb-4">Duração: {{ $plan->duration_days }} dias</div>

                    <form method="POST" action="{{ route('student.subscriptions.checkout') }}" class="mt-auto">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                        <button class="btn btn-primary w-100">Pagar com Mercado Pago</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card-soft p-4">Nenhum plano ativo encontrado.</div>
            </div>
        @endforelse
    </div>
@endsection
