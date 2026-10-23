@extends('layouts.admin')
@section('title','Biblioteca de provas | Papirar')
@section('content')
<div class="container-fluid py-3"><div class="d-flex justify-content-between align-items-center mb-3"><div><h2>Biblioteca de provas</h2><p class="text-muted mb-0">Provas oficiais vinculadas aos cursos/concursos, com prova e gabarito no mesmo ZIP quando necessário.</p></div><a href="{{ route('admin.library.exams.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nova prova</a></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>
@endif
<div class="card"><div class="card-body table-responsive"><table class="table table-hover"><thead><tr><th>ID</th><th>Prova</th><th>Curso/concurso</th><th>Arquivo</th><th>Status</th><th>Ações</th></tr></thead><tbody>
@forelse($documents as $doc)<tr><td>#{{ $doc->id }}</td><td><strong>{{ $doc->title }}</strong><br><small>{{ $doc->year ?: 'Sem ano' }} · {{ $doc->exam_board ?: 'Banca não informada' }}</small></td><td>{{ $doc->course?->title ?: 'Curso indisponível' }}</td><td>{{ strtoupper(pathinfo($doc->file_name,PATHINFO_EXTENSION)) }}</td><td><span class="badge badge-{{ $doc->is_published?'success':'secondary' }}">{{ $doc->is_published?'Publicada':'Oculta' }}</span></td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.library.exams.edit',$doc) }}">Editar</a> 
@if($doc->is_published)<a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener" href="{{ $doc->publicUrl() }}">Ver página</a>
@endif</td></tr>
@empty<tr><td colspan="6">Nenhuma prova cadastrada.</td></tr>
@endforelse</tbody></table>{{ $documents->links() }}</div></div></div>
@endsection
