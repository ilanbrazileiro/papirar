import {
    ClassicEditor,
    AccessibilityHelp,
    Alignment,
    AutoImage,
    Autosave,
    BlockQuote,
    Bold,
    Essentials,
    Heading,
    Image,
    ImageCaption,
    ImageInsert,
    ImageResize,
    ImageStyle,
    ImageTextAlternative,
    ImageToolbar,
    ImageUpload,
    Indent,
    IndentBlock,
    Italic,
    Link,
    LinkImage,
    List,
    Paragraph,
    SelectAll,
    Table,
    TableCaption,
    TableCellProperties,
    TableColumnResize,
    TableProperties,
    TableToolbar,
    Undo
} from 'ckeditor5';

import 'ckeditor5/ckeditor5.css';

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

            if (!token) {
                reject('Token CSRF não encontrado.');
                return;
            }

            this.xhr = new XMLHttpRequest();
            this.xhr.open('POST', uploadUrl, true);
            this.xhr.setRequestHeader('X-CSRF-TOKEN', token);
            this.xhr.setRequestHeader('Accept', 'application/json');
            this.xhr.responseType = 'json';

            this.xhr.addEventListener('error', () => reject('Falha na comunicação com o servidor.'));
            this.xhr.addEventListener('abort', () => reject('Upload cancelado.'));
            this.xhr.addEventListener('load', () => {
                const response = this.xhr.response;

                if (!response || this.xhr.status < 200 || this.xhr.status >= 300) {
                    reject(response?.message || 'Falha ao enviar a imagem.');
                    return;
                }

                if (!response.url) {
                    reject('Resposta inválida do servidor: URL da imagem ausente.');
                    return;
                }

                resolve({ default: response.url });
            });

            if (this.xhr.upload) {
                this.xhr.upload.addEventListener('progress', (event) => {
                    if (event.lengthComputable) {
                        this.loader.uploadTotal = event.total;
                        this.loader.uploaded = event.loaded;
                    }
                });
            }

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
    editor.plugins.get('FileRepository').createUploadAdapter = (loader) => new PapirarUploadAdapter(loader);
}

const editorConfig = {
    licenseKey: 'GPL',
    plugins: [
        AccessibilityHelp,
        Alignment,
        AutoImage,
        Autosave,
        BlockQuote,
        Bold,
        Essentials,
        Heading,
        Image,
        ImageCaption,
        ImageInsert,
        ImageResize,
        ImageStyle,
        ImageTextAlternative,
        ImageToolbar,
        ImageUpload,
        Indent,
        IndentBlock,
        Italic,
        Link,
        LinkImage,
        List,
        Paragraph,
        SelectAll,
        Table,
        TableCaption,
        TableCellProperties,
        TableColumnResize,
        TableProperties,
        TableToolbar,
        Undo,
        PapirarUploadAdapterPlugin
    ],
    toolbar: {
        items: [
            'undo', 'redo', '|',
            'heading', '|',
            'bold', 'italic', '|',
            'link', 'bulletedList', 'numberedList', '|',
            'alignment', 'outdent', 'indent', '|',
            'insertImage', 'insertTable', 'blockQuote', '|',
            'accessibilityHelp'
        ],
        shouldNotGroupWhenFull: true
    },
    image: {
        resizeUnit: '%',
        resizeOptions: [
            { name: 'resizeImage:original', value: null, label: 'Original' },
            { name: 'resizeImage:25', value: '25', label: '25%' },
            { name: 'resizeImage:50', value: '50', label: '50%' },
            { name: 'resizeImage:75', value: '75', label: '75%' },
            { name: 'resizeImage:100', value: '100', label: '100%' }
        ],
        toolbar: [
            'imageTextAlternative',
            'toggleImageCaption', '|',
            'imageStyle:inline',
            'imageStyle:block',
            'imageStyle:side', '|',
            'resizeImage'
        ],
        insert: {
            integrations: ['upload']
        }
    },
    table: {
        contentToolbar: [
            'tableColumn',
            'tableRow',
            'mergeTableCells',
            'tableProperties',
            'tableCellProperties'
        ]
    },
    link: {
        addTargetToExternalLinks: true,
        defaultProtocol: 'https://'
    }
};

function bootPapirarEditors() {
    document.querySelectorAll('textarea.rich-editor').forEach((textarea) => {
        if (textarea.dataset.ckeditorReady === '1') {
            return;
        }

        textarea.dataset.ckeditorReady = '1';

        ClassicEditor
            .create(textarea, editorConfig)
            .then((editor) => {
                textarea._papirarEditor = editor;
            })
            .catch((error) => {
                textarea.dataset.ckeditorReady = '0';
                console.error('Erro ao inicializar CKEditor:', error);
            });
    });
}

document.addEventListener('DOMContentLoaded', bootPapirarEditors);
window.bootPapirarEditors = bootPapirarEditors;
