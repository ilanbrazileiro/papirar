@extends('admin.layout')
@section('content')
    <div class="mb-4">
        <h1 class="page-title">Dashboard</h1>
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card-soft p-4">
                <div class="small-muted">Questões publicadas</div>
                <div class="display-6 fw-bold">{{ $totalPublishedQuestions }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-soft p-4">
                <div class="small-muted">Clientes ativos</div>
                <div class="display-6 fw-bold">{{ $activeCustomers }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-soft p-4">
                <div class="small-muted">Tickets abertos</div>
                <div class="display-6 fw-bold">{{ $openTickets }}</div>
            </div>
        </div>
    </div>
@endsection
