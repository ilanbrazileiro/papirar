@extends('layouts.admin')
@section('title','Simulados públicos')
@section('content')
<div class="container-fluid py-3"><div class="d-flex justify-content-between align-items-center mb-3"><div><h2>Simulados gratuitos</h2><p class="text-muted mb-0">Links compartilháveis para aquisição e ativação.</p></div><a class="btn btn-primary" href="{{ route('admin.public-simulations.create') }}"><i class="fas fa-plus"></i> Novo simulado</a></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>
@endif
<div class="card"><div class="card-body table-responsive"><table class="table table-hover"><thead><tr><th>Simulado</th><th>Curso</th><th>Questões</th><th>Inícios</th><th>Conclusões</th><th>Status</th><th>Ações</th></tr></thead><tbody>
@forelse($simulations as $sim)<tr><td><strong>{{ $sim->title }}</strong><br><small>{{ $sim->slug }}</small></td><td>{{ $sim->course?->title }}</td><td>{{ $sim->questions_count }}</td><td>{{ $sim->attempts_count }}</td><td>{{ $sim->completed_attempts_count }}</td><td><span class="badge badge-{{ $sim->is_published?'success':'secondary' }}">{{ $sim->is_published?'Publicado':'Rascunho' }}</span></td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.public-simulations.edit',$sim) }}">Editar</a> 
@if($sim->is_published)<a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener" href="{{ route('public-simulations.landing',$sim->slug) }}">Link público</a>
@endif</td></tr>
@empty<tr><td colspan="7">Nenhum simulado cadastrado.</td></tr>
@endforelse
</tbody></table>{{ $simulations->links() }}</div></div></div>
@endsection
