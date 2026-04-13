@extends('admin.layout')
@section('content')
<div class="mb-4"><h1 class="page-title">Comentários</h1></div>
<div class="card-soft p-4"><table class="table"><thead><tr><th>Questão</th><th>Usuário</th><th>Comentário</th><th></th></tr></thead><tbody>@foreach($comments as $comment)<tr><td>#{{ $comment->question_id }}</td><td>{{ $comment->user->name ?? '-' }}</td><td>{{ $comment->comment }}</td><td><form method="POST" action="{{ route('admin.comments.approve',$comment) }}" class="d-inline">@csrf @method('PATCH')<button class="btn btn-sm btn-success">Aprovar</button></form> <form method="POST" action="{{ route('admin.comments.reject',$comment) }}" class="d-inline">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-danger">Rejeitar</button></form></td></tr>@endforeach</tbody></table>{{ $comments->links() }}</div>
@endsection
