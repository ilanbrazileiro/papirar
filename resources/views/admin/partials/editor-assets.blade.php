<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
    window.PAPIRAR_EDITOR_UPLOAD_URL = "{{ route('admin.editor-images.upload') }}";
</script>

@vite(['resources/js/admin/ckeditor.js'])
