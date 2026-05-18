import {
    ClassicEditor,
    Essentials,
    Paragraph,
    Heading,
    Bold,
    Italic,
    Underline,
    Strikethrough,
    Link,
    List,
    BlockQuote,
    Alignment,
    Image,
    ImageToolbar,
    ImageUpload,
    ImageResize,
    ImageStyle,
    ImageCaption,
    ImageTextAlternative,
    Table,
    TableToolbar,
    MediaEmbed,
    Undo
} from 'ckeditor5';

import 'ckeditor5/ckeditor5.css';
import '../../css/admin/editor-content.css';

class PapirarUploadAdapter {
    constructor(loader) {
        this.loader = loader;
        this.xhr = null;
    }

    upload() {
        return this.loader.file.then(file => new Promise((resolve, reject) => {
            const uploadUrl = window.PAPIRAR_EDITOR_UPLOAD_URL;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (!uploadUrl) {
                reject('Rota de upload do editor não configurada.');
                return;
            }

            if (!csrfToken) {
                reject('Token CSRF não encontrado.');
                return;
            }

            const data = new FormData();
            data.append('upload', file);

            this.xhr = new XMLHttpRequest();
            this.xhr.open('POST', uploadUrl, true);
            this.xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
            this.xhr.setRequestHeader('Accept', 'application/json');

            this.xhr.upload.addEventListener('progress', event => {
                if (event.lengthComputable) {
                    this.loader.uploadTotal = event.total;
                    this.loader.uploaded = event.loaded;
                }
            });

            this.xhr.addEventListener('error', () => reject('Erro ao enviar imagem.'));
            this.xhr.addEventListener('abort', () => reject('Upload cancelado.'));

            this.xhr.addEventListener('load', () => {
                const response = JSON.parse(this.xhr.responseText || '{}');

                if (this.xhr.status < 200 || this.xhr.status >= 300 || !response.url) {
                    reject(response.message || 'Erro ao processar imagem.');
                    return;
                }

                resolve({ default: response.url });
            });

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
    editor.plugins.get('FileRepository').createUploadAdapter = loader => new PapirarUploadAdapter(loader);
}

const defaultConfig = {
    licenseKey: 'GPL',
    plugins: [
        Essentials,
        Paragraph,
        Heading,
        Bold,
        Italic,
        Underline,
        Strikethrough,
        Link,
        List,
        BlockQuote,
        Alignment,
        Image,
        ImageToolbar,
        ImageUpload,
        ImageResize,
        ImageStyle,
        ImageCaption,
        ImageTextAlternative,
        Table,
        TableToolbar,
        MediaEmbed,
        Undo,
        PapirarUploadAdapterPlugin
    ],
    toolbar: {
        items: [
            'undo', 'redo', '|',
            'heading', '|',
            'bold', 'italic', 'underline', 'strikethrough', '|',
            'link', 'bulletedList', 'numberedList', 'blockQuote', '|',
            'alignment', '|',
            'insertTable', 'mediaEmbed', 'uploadImage'
        ],
        shouldNotGroupWhenFull: true
    },
    image: {
        toolbar: [
            'imageTextAlternative',
            'toggleImageCaption',
            '|',
            'imageStyle:inline',
            'imageStyle:block',
            'imageStyle:side',
            '|',
            'resizeImage'
        ],
        resizeOptions: [
            { name: 'resizeImage:original', label: 'Original', value: null },
            { name: 'resizeImage:25', label: '25%', value: '25' },
            { name: 'resizeImage:50', label: '50%', value: '50' },
            { name: 'resizeImage:75', label: '75%', value: '75' },
            { name: 'resizeImage:100', label: '100%', value: '100' }
        ]
    },
    table: {
        contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
    }
};

window.PapirarEditors = [];

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('textarea.rich-editor').forEach(element => {
        ClassicEditor.create(element, defaultConfig)
            .then(editor => {
                window.PapirarEditors.push(editor);
            })
            .catch(error => {
                console.error('Erro ao iniciar CKEditor:', error);
            });
    });
});
