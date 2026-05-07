@extends('layouts.student')

@section('title', 'Histórico de assinaturas')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Histórico de assinaturas</h1>
            <p class="page-subtitle">Acompanhe assinaturas antigas, atuais e os respectivos pagamentos.</p>
        </div>
        <a href="{{ route('student.subscriptions.index') }}" class="btn btn-outline-primary">Ver planos</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card-soft p-4">
        @if($subscriptions->count())
            @foreach($subscriptions as $subscription)
                @php
                    $latestPendingTransaction = $subscription->transactions
                        ->first(fn ($transaction) => $transaction->status === \App\Models\PaymentTransaction::STATUS_PENDING);

                    $checkoutUrl = $latestPendingTransaction?->checkoutUrl();
                    $canPayAgain = ! $subscription->isActive()
                        && $subscription->plan
                        && $subscription->plan->active
                        && $subscription->plan->is_public;
                @endphp

                <div class="py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                        <div>
                            <div class="fw-semibold">{{ $subscription->plan->name ?? 'Plano removido' }}</div>
                            <div class="small-muted">
                                Status: <span class="fw-semibold">{{ strtoupper($subscription->status) }}</span>
                                @if($subscription->starts_at)
                                    · início {{ $subscription->starts_at->format('d/m/Y H:i') }}
                                @endif
                                @if($subscription->expires_at)
                                    · expira {{ $subscription->expires_at->format('d/m/Y H:i') }}
                                @endif
                            </div>

                            @if($canPayAgain)
                                <form method="POST" action="{{ route('student.subscriptions.retry', $subscription) }}" class="mt-3">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        {{ $checkoutUrl ? 'Continuar pagamento' : 'Tentar pagar novamente' }}
                                    </button>
                                </form>
                            @endif
                        </div>

                        <div class="text-lg-end">
                            <div class="fw-semibold">Transações</div>
                            @forelse($subscription->transactions as $transaction)
                                <div class="small-muted">
                                    {{ ucfirst(str_replace('_', ' ', $transaction->gateway)) }} ·
                                    {{ strtoupper($transaction->status) }} ·
                                    R$ {{ number_format((float) $transaction->amount, 2, ',', '.') }}
                                    @if($transaction->created_at)
                                        · {{ $transaction->created_at->format('d/m/Y H:i') }}
                                    @endif
                                </div>
                            @empty
                                <div class="small-muted">Sem transações vinculadas.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="mt-4">
                {{ $subscriptions->links() }}
            </div>
        @else
            <div class="small-muted">Nenhuma assinatura encontrada.</div>
        @endif
    </div>
@endsection
