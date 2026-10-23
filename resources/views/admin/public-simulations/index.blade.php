@extends('layouts.admin')
@section('title','Simulados públicos')
@section('content')
<div class="container-fluid py-3"><div class="d-flex justify-content-between align-items-center mb-3"><div><h2>Simulados gratuitos</h2><p class="text-muted mb-0">Links compartilháveis para aquisição e ativação.</p></div><a class="btn btn-primary" href="{{ route('admin.public-simulations.create') }}"><i class="fas fa-plus"></i> Novo simulado</a></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<div class="card"><div class="card-body table-responsive"><table class="table table-hover"><thead><tr><th>Simulado</th><th>Curso</th><th>Questões</th><th>Inícios</th><th>Conclusões</th><th>Status</th><th>Ações</th></tr></thead><tbody>
@forelse($simulations as $sim)<tr><td><strong>{{ $sim->title }}</strong><br><small>{{ $sim->slug }}</small></td><td>{{ $sim->course?->title }}</td><td>{{ $sim->questions_count }}</td><td>{{ $sim->attempts_count }}</td><td>{{ $sim->completed_attempts_count }}</td><td><span class="badge badge-{{ $sim->is_published?'success':'secondary' }}">{{ $sim->is_published?'Publicado':'Rascunho' }}</span></td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.public-simulations.edit',$sim) }}">Editar</a> 
@if($sim->is_published)<a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener" href="{{ route('public-simulations.landing',$sim->slug) }}">Link público</a>
@endif
<form method="POST" action="{{ route('admin.public-simulations.toggle-publication',$sim) }}" class="d-inline">@csrf @method('PATCH')
<button type="submit" class="btn btn-sm {{ $sim->is_published ? 'btn-outline-warning' : 'btn-outline-success' }}" onclick="return confirm(@js($sim->is_published ? 'Tirar este simulado do ar? As tentativas anteriores serão preservadas.' : 'Publicar este simulado novamente?'))">{{ $sim->is_published ? 'Tirar do ar' : 'Publicar' }}</button></form>
<form method="POST" action="{{ route('admin.public-simulations.destroy',$sim) }}" class="d-inline js-delete-public-simulation" data-title="{{ $sim->title }}" data-attempts="{{ $sim->attempts_count }}">
@csrf @method('DELETE')
<input type="hidden" name="confirmation" value="EXCLUIR">
<button type="submit" class="btn btn-sm btn-outline-danger">Excluir definitivamente</button>
</form></td></tr>
@empty<tr><td colspan="7">Nenhum simulado cadastrado.</td></tr>
@endforelse
</tbody></table>{{ $simulations->links() }}</div></div></div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.js-delete-public-simulation').forEach(function(form){
 form.addEventListener('submit',function(e){
  const title=form.dataset.title||'este simulado';
  const attempts=form.dataset.attempts||'0';
  if(!confirm('EXCLUSÃO IRREVERSÍVEL\n\nSimulado: '+title+'\nTentativas registradas: '+attempts+'\n\nSerão apagados o simulado, todas as tentativas, respostas e resultados. As questões do banco principal NÃO serão excluídas.\n\nDeseja continuar?'))e.preventDefault();
 });
});
</script>
@endpush
