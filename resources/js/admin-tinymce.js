import tinymce from 'tinymce/tinymce';

import 'tinymce/icons/default';
import 'tinymce/themes/silver';
import 'tinymce/models/dom';

import 'tinymce/plugins/advlist';
import 'tinymce/plugins/autolink';
import 'tinymce/plugins/autoresize';
import 'tinymce/plugins/charmap';
import 'tinymce/plugins/code';
import 'tinymce/plugins/fullscreen';
import 'tinymce/plugins/image';
import 'tinymce/plugins/link';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/media';
import 'tinymce/plugins/preview';
import 'tinymce/plugins/searchreplace';
import 'tinymce/plugins/table';
import 'tinymce/plugins/visualblocks';

const initPapirarTinyMCE = () => {
    const uploadUrl = window.PAPIRAR_EDITOR_UPLOAD_URL || '';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const fields = document.querySelectorAll('textarea.papirar-rich-editor');

    if (!fields.length) {
        return;
    }

    if (!uploadUrl) {
        console.warn('TinyMCE Papirar: rota de upload não configurada.');
    }

    tinymce.remove('textarea.papirar-rich-editor');

    tinymce.init({
        selector: 'textarea.papirar-rich-editor',

        license_key: 'gpl',

        base_url: '/vendor/tinymce',
        suffix: '.min',
        skin: 'oxide',
        content_css: '/vendor/tinymce/skins/content/default/content.css',

        language: 'pt_BR',
        height: 460,
        menubar: 'file edit view insert format tools table',
        branding: false,
        promotion: false,
        convert_urls: false,
        relative_urls: false,
        remove_script_host: false,

        plugins: [
            'advlist', 'autolink', 'autoresize', 'charmap', 'code', 'fullscreen',
            'image', 'link', 'lists', 'media', 'preview', 'searchreplace',
            'table', 'visualblocks'
        ].join(' '),

        toolbar: [
            'undo redo | blocks | bold italic underline strikethrough | forecolor backcolor',
            'alignleft aligncenter alignright alignjustify | bullist numlist outdent indent',
            'link image media table | removeformat | preview code fullscreen'
        ].join(' | '),

        image_title: true,
        image_caption: true,
        image_advtab: true,
        image_dimensions: true,
        image_class_list: [
            { title: 'Imagem responsiva', value: 'img-fluid' },
            { title: 'Centralizada', value: 'img-fluid d-block mx-auto' }
        ],

        automatic_uploads: true,
        images_upload_credentials: true,
        images_reuse_filename: false,
        images_file_types: 'jpeg,jpg,png,gif,webp',

        images_upload_handler: (blobInfo, progress) => new Promise((resolve, reject) => {
            if (!uploadUrl) {
                reject('URL de upload do editor não configurada.');
                return;
            }

            const formData = new FormData();
            formData.append('upload', blobInfo.blob(), blobInfo.filename());

            fetch(uploadUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData,
                credentials: 'same-origin',
            })
                .then(async (response) => {
                    const data = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        reject(data.message || 'Falha no upload da imagem.');
                        return;
                    }

                    if (!data.location) {
                        reject('Resposta inválida do upload: campo location ausente.');
                        return;
                    }

                    resolve(data.location);
                })
                .catch(() => reject('Falha ao enviar imagem.'));
        }),

        content_style: `
            body { font-family: Arial, Helvetica, sans-serif; font-size: 15px; line-height: 1.6; }
            img { max-width: 100%; height: auto; }
            figure.image { margin: 1rem auto; text-align: center; }
            figure.image img { max-width: 100%; height: auto; }
            figcaption { color: #64748b; font-size: .875rem; }
        `,
    });
};

document.addEventListener('DOMContentLoaded', initPapirarTinyMCE);
