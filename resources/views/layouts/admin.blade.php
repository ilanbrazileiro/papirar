<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin | Papirar')</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <style>
        :root {
            --papirar-navy: #16233d;
            --papirar-navy-2: #0f1a2f;
            --papirar-gold: #e7b84b;
            --papirar-soft: #f4f6f9;
        }

        .brand-link {
            background: var(--papirar-navy-2);
            border-bottom: 1px solid rgba(255,255,255,.08) !important;
        }

        .brand-link .brand-image {
            opacity: 1;
            box-shadow: none;
            background: #fff;
            border-radius: 999px;
            padding: 2px;
        }

        .main-sidebar { background: var(--papirar-navy); }

        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active,
        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link:hover {
            background: rgba(231, 184, 75, .16);
            color: #fff;
        }

        .nav-sidebar .nav-link p,
        .nav-sidebar .nav-link i { color: rgba(255,255,255,.9); }

        .content-wrapper { background: var(--papirar-soft); }
        .content-header h1 { font-weight: 800; color: #111827; }

        .card {
            border-radius: 14px;
            border: 1px solid rgba(17, 24, 39, .08);
        }

        .card-header {
            border-top-left-radius: 14px !important;
            border-top-right-radius: 14px !important;
        }

        .btn-primary { background-color: #1f6feb; border-color: #1f6feb; }
        .btn-warning { background-color: var(--papirar-gold); border-color: var(--papirar-gold); color: #111827; }

        .ck-editor__editable_inline { min-height: 220px; }
        .ck-content img { max-width: 100%; height: auto; }

        @media (max-width: 767.98px) {
            .content-header h1 { font-size: 1.45rem; }
            .main-footer { font-size: .85rem; }
        }
    </style>

    @stack('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    @include('admin.partials.navbar')
    @include('admin.partials.sidebar')

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-sm-4 text-sm-right mt-0 mt-sm-0">
                        @yield('page_actions')
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>Verifique os campos abaixo.</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </section>
    </div>

    @include('admin.partials.footer')
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>

<script>
    window.PAPIRAR_EDITOR_UPLOAD_URL = "{{ \Illuminate\Support\Facades\Route::has('admin.editor.images.upload') ? route('admin.editor.images.upload') : '' }}";
    window.PAPIRAR_CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    class PapirarUploadAdapter {
        constructor(loader) {
            this.loader = loader;
            this.xhr = null;
        }

        upload() {
            return this.loader.file.then(file => new Promise((resolve, reject) => {
                if (!window.PAPIRAR_EDITOR_UPLOAD_URL) {
                    reject('Rota de upload do editor não configurada.');
                    return;
                }

                this._initRequest();
                this._initListeners(resolve, reject, file);
                this._sendRequest(file);
            }));
        }

        abort() {
            if (this.xhr) {
                this.xhr.abort();
            }
        }

        _initRequest() {
            const xhr = this.xhr = new XMLHttpRequest();
            xhr.open('POST', window.PAPIRAR_EDITOR_UPLOAD_URL, true);
            xhr.setRequestHeader('X-CSRF-TOKEN', window.PAPIRAR_CSRF_TOKEN);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.responseType = 'json';
        }

        _initListeners(resolve, reject, file) {
            const xhr = this.xhr;
            const loader = this.loader;
            const genericErrorText = `Não foi possível enviar a imagem: ${file.name}.`;

            xhr.addEventListener('error', () => reject(genericErrorText));
            xhr.addEventListener('abort', () => reject());
            xhr.addEventListener('load', () => {
                const response = xhr.response;

                if (!response || xhr.status < 200 || xhr.status >= 300) {
                    const message = response?.message || response?.error?.message || genericErrorText;
                    reject(message);
                    return;
                }

                if (!response.url) {
                    reject('O servidor não retornou a URL da imagem.');
                    return;
                }

                resolve({ default: response.url });
            });

            if (xhr.upload) {
                xhr.upload.addEventListener('progress', evt => {
                    if (evt.lengthComputable) {
                        loader.uploadTotal = evt.total;
                        loader.uploaded = evt.loaded;
                    }
                });
            }
        }

        _sendRequest(file) {
            const data = new FormData();
            data.append('upload', file);
            this.xhr.send(data);
        }
    }

    function PapirarUploadAdapterPlugin(editor) {
        editor.plugins.get('FileRepository').createUploadAdapter = loader => new PapirarUploadAdapter(loader);
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('textarea.rich-editor').forEach(function (textarea) {
            ClassicEditor.create(textarea, {
                extraPlugins: [PapirarUploadAdapterPlugin],
                toolbar: [
                    'heading', '|',
                    'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|',
                    'imageUpload', 'blockQuote', 'insertTable', '|',
                    'undo', 'redo'
                ],
                image: {
                    toolbar: [
                        'imageTextAlternative',
                        'toggleImageCaption',
                        'imageStyle:inline',
                        'imageStyle:block',
                        'imageStyle:side'
                    ]
                }
            }).catch(function (error) {
                console.error(error);
            });
        });
    });
</script>

@stack('scripts')
</body>
</html>
