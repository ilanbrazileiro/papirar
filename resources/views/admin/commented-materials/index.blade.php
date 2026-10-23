@extends('layouts.admin')
@section('title','PDFs comentados | Papirar')
@section('content')
<div class="container-fluid py-3"><div class="d-flex justify-content-between align-items-center mb-3"><div><h2>Biblioteca acadêmica de PDFs</h2><p class="text-muted mb-0">Materiais reutilizáveis por disciplina e tópicos.</p></div><a class="btn btn-primary" href="{{ route('admin.library.materials.create') }}"><i class="fas fa-plus"></i> Novo PDF</a></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>
@endif
<div class="card"><div class="card-body table-responsive"><table class="table table-hover"><thead><tr><th>ID</th><th>Documento</th><th>Disciplina</th><th>Tópicos</th><th>Status</th><th>Ações</th></tr></thead><tbody>
@forelse($documents as $doc)<tr><td>#{{ $doc->id }}</td><td><strong>{{ $doc->title }}</strong><br><small>{{ $doc->year ?: 'Sem edição' }}</small></td><td>{{ $doc->subject?->name ?? 'Pendente de classificação' }}</td><td class="small">{{ $doc->topics->pluck('name')->join(' • ') }}</td><td><span class="badge badge-{{ $doc->is_published?'success':'secondary' }}">{{ $doc->is_published?'Publicado':'Oculto' }}</span></td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.library.materials.edit',$doc) }}">Editar</a> 
@if($doc->is_published)<a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener" href="{{ route('library.materials.show',[$doc->id,\Illuminate\Support\Str::slug($doc->title) ?: 'material-comentado']) }}">Ver página</a>
@endif</td></tr>
@empty<tr><td colspan="6">Nenhum PDF cadastrado.</td></tr>
@endforelse</tbody></table>{{ $documents->links() }}</div></div></div>
@endsection
