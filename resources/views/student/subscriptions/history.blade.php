@extends('layouts.student')

@section('title', 'Histórico de assinaturas')

@section('content')
    <div class="mb-4">
        <h1 class="page-title">Histórico de assinaturas</h1>
        <p class="page-subtitle">Acompanhe assinaturas antigas, atuais e os respectivos pagamentos.</p>
    </div>

    <div class="card-soft p-4">
        @if($subscriptions->count())
            @foreach($subscriptions as $subscription)
                <div class="py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                        <div>
                            <div class="fw-semibold">{{ $subscription->plan->name ?? 'Plano removido' }}</div>
                            <div class="small-muted">
                                Status: {{ strtoupper($subscription->status) }}
                                @if($subscription->starts_at)
                                    · início {{ $subscription->starts_at->format('d/m/Y H:i') }}
                                @endif
                                @if($subscription->expires_at)
                                    · expira {{ $subscription->expires_at->format('d/m/Y H:i') }}
                                @endif
                            </div>
                        </div>
                        <div class="text-lg-end">
                            <div class="fw-semibold">Transações</div>
                            @forelse($subscription->transactions as $transaction)
                                <div class="small-muted">
                                    {{ ucfirst(str_replace('_', ' ', $transaction->gateway)) }} ·
                                    {{ strtoupper($transaction->status) }} ·
                                    R$ {{ number_format((float) $transaction->amount, 2, ',', '.') }}
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
