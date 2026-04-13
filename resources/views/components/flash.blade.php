@if(session('success'))
    <div class="alert alert-success rounded-4">{{ session('success') }}</div>
@endif

@if(session('status'))
    <div class="alert alert-info rounded-4">{{ session('status') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger rounded-4">{{ session('error') }}</div>
@endif