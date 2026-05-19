import {
    ClassicEditor,
    Alignment,
    AutoImage,
    Autosave,
    BlockQuote,
    Bold,
    Essentials,
    FileRepository,
    Heading,
    Image,
    ImageCaption,
    ImageInsert,
    ImageResize,
    ImageStyle,
    ImageToolbar,
    ImageUpload,
    Italic,
    Link,
    List,
    Paragraph,
    Table,
    TableToolbar,
    Undo
} from 'ckeditor5';

import 'ckeditor5/ckeditor5.css';
import '../css/admin-editor.css';

class PapirarUploadAdapter {
    constructor(loader) {
        this.loader = loader;
        this.xhr = null;
    }

    upload() {
        return this.loader.file.then((file) => new Promise((resolve, reject) => {
            const uploadUrl = window.PAPIRAR_EDITOR_UPLOAD_URL;
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (!uploadUrl) {
                reject('Rota de upload do editor não configurada.');
                return;
            }

            this.xhr = new XMLHttpRequest();
            this.xhr.open('POST', uploadUrl, true);
            this.xhr.setRequestHeader('Accept', 'application/json');

            if (token) {
                this.xhr.setRequestHeader('X-CSRF-TOKEN', token);
            }

            this.xhr.upload.addEventListener('progress', (event) => {
                if (event.lengthComputable) {
                    this.loader.uploadTotal = event.total;
                    this.loader.uploaded = event.loaded;
                }
            });

            this.xhr.addEventListener('error', () => reject('Erro ao enviar a imagem.'));
            this.xhr.addEventListener('abort', () => reject('Upload cancelado.'));
            this.xhr.addEventListener('load', () => {
                const response = this.xhr.response ? JSON.parse(this.xhr.response) : null;

                if (!response || !response.url) {
                    reject(response?.message || 'Resposta inválida do servidor ao enviar imagem.');
                    return;
                }

                resolve({ default: response.url });
            });

            const data = new FormData();
            data.append('upload', file);

            this.xhr.send(data);
        }));
    }

    abort() {
        if (this.xhr) {
            this.xhr.abort();
        }
    }
}

function PapirarUploadAdapterPlugin(editor) {
    editor.plugins.get(FileRepository).createUploadAdapter = (loader) => new PapirarUploadAdapter(loader);
}

const editorConfig = {
    plugins: [
        Alignment,
        AutoImage,
        Autosave,
        BlockQuote,
        Bold,
        Essentials,
        FileRepository,
        Heading,
        Image,
        ImageCaption,
        ImageInsert,
        ImageResize,
        ImageStyle,
        ImageToolbar,
        ImageUpload,
        Italic,
        Link,
        List,
        Paragraph,
        PapirarUploadAdapterPlugin,
        Table,
        TableToolbar,
        Undo
    ],
    toolbar: {
        items: [
            'undo', 'redo', '|',
            'heading', '|',
            'bold', 'italic', 'link', '|',
            'bulletedList', 'numberedList', 'blockQuote', '|',
            'insertImage', 'insertTable', '|',
            'alignment'
        ],
        shouldNotGroupWhenFull: true
    },
    image: {
        resizeUnit: '%',
        resizeOptions: [
            { name: 'resizeImage:original', label: 'Original', value: null },
            { name: 'resizeImage:25', label: '25%', value: '25' },
            { name: 'resizeImage:50', label: '50%', value: '50' },
            { name: 'resizeImage:75', label: '75%', value: '75' }
        ],
        toolbar: [
            'imageTextAlternative',
            'toggleImageCaption',
            '|',
            'imageStyle:inline',
            'imageStyle:block',
            'imageStyle:side',
            '|',
            'resizeImage'
        ]
    },
    table: {
        contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
    }
};

function initializePapirarEditors() {
    document.querySelectorAll('textarea.papirar-rich-editor').forEach((textarea) => {
        if (textarea.dataset.ckeditorReady === '1') {
            return;
        }

        textarea.dataset.ckeditorReady = '1';

        ClassicEditor
            .create(textarea, editorConfig)
            .then((editor) => {
                textarea.closest('form')?.addEventListener('submit', () => {
                    textarea.value = editor.getData();
                });
            })
            .catch((error) => {
                console.error('Erro ao iniciar CKEditor Papirar:', error);
            });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializePapirarEditors);
} else {
    initializePapirarEditors();
}
