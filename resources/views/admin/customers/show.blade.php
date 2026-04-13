@extends('admin.layout')
@section('content')<div class="card-soft p-4"><h1 class="page-title">{{ $customer->name }}</h1><div>{{ $customer->email }}</div><a class="btn btn-primary mt-3" href="{{ route('admin.customers.edit',$customer) }}">Editar</a></div>@endsection
