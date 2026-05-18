@extends('layouts.admin')

@section('title', 'Dashboard Admin | Papirar')
@section('page_title', 'Dashboard administrativo')

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $questionsCount ?? 0 }}</h3>
                <p>Questões</p>
            </div>
            <div class="icon"><i class="fas fa-circle-question"></i></div>
            <a href="{{ url('/admin/questions') }}" class="small-box-footer">Gerenciar <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $customersCount ?? 0 }}</h3>
                <p>Clientes</p>
            </div>
            <div class="icon"><i class="fas fa-users"></i></div>
            <a href="{{ url('/admin/customers') }}" class="small-box-footer">Ver clientes <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $examsCount ?? 0 }}</h3>
                <p>Concursos</p>
            </div>
            <div class="icon"><i class="fas fa-landmark"></i></div>
            <a href="{{ url('/admin/exams') }}" class="small-box-footer">Gerenciar <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $ticketsCount ?? 0 }}</h3>
                <p>Tickets abertos</p>
            </div>
            <div class="icon"><i class="fas fa-headset"></i></div>
            <a href="{{ url('/admin/support') }}" class="small-box-footer">Atender <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Próximo passo recomendado</h3>
    </div>
    <div class="card-body">
        <p class="mb-2">O painel AdminLTE já está pronto para receber o CRUD de questões com editor rico.</p>
        <p class="mb-0">Na próxima etapa, adicione a classe <code>rich-editor</code> nos campos <code>statement</code> e <code>commented_answer</code> do formulário de questões.</p>
    </div>
</div>
@endsection
