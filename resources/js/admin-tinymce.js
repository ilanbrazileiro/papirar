import tinymce from 'tinymce/tinymce';

import 'tinymce/icons/default';
import 'tinymce/themes/silver';
import 'tinymce/models/dom';

import 'tinymce/plugins/advlist';
import 'tinymce/plugins/autolink';
import 'tinymce/plugins/charmap';
import 'tinymce/plugins/code';
import 'tinymce/plugins/fullscreen';
import 'tinymce/plugins/image';
import 'tinymce/plugins/link';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/media';
import 'tinymce/plugins/preview';
import 'tinymce/plugins/table';
import 'tinymce/plugins/wordcount';

import 'tinymce/skins/ui/oxide/skin.css';
import 'tinymce/skins/content/default/content.css';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

function uploadImageToLaravel(blobInfo) {
    return new Promise((resolve, reject) => {
        if (!window.PAPIRAR_TINYMCE_UPLOAD_URL) {
            reject('Rota de upload do editor não configurada.');
            return;
        }

        if (!csrfToken) {
            reject('Token CSRF não encontrado.');
            return;
        }

        const formData = new FormData();
        formData.append('file', blobInfo.blob(), blobInfo.filename());

        fetch(window.PAPIRAR_TINYMCE_UPLOAD_URL, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
        })
            .then(async (response) => {
                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const message = data.message || 'Falha ao enviar imagem.';
                    throw new Error(message);
                }

                if (!data.location) {
                    throw new Error('Resposta inválida do upload: location ausente.');
                }

                resolve(data.location);
            })
            .catch((error) => {
                reject(error.message || 'Erro inesperado ao enviar imagem.');
            });
    });
}

function initPapirarTinyMCE() {
    const selectors = 'textarea.papirar-rich-editor';

    if (!document.querySelector(selectors)) {
        return;
    }

    tinymce.remove(selectors);

    tinymce.init({
        selector: selectors,
        license_key: 'gpl',
        promotion: false,
        branding: false,
        height: 420,
        menubar: 'file edit view insert format tools table',
        plugins: 'advlist autolink lists link image media table charmap preview fullscreen code wordcount',
        toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table | removeformat code fullscreen',
        image_title: true,
        automatic_uploads: true,
        paste_data_images: false,
        images_upload_handler: uploadImageToLaravel,
        file_picker_types: 'image',
        image_advtab: true,
        image_dimensions: true,
        object_resizing: 'img,table',
        relative_urls: false,
        remove_script_host: false,
        convert_urls: false,
        content_style: `
            body { font-family: Arial, Helvetica, sans-serif; font-size: 16px; line-height: 1.65; }
            img { max-width: 100%; height: auto; }
            figure { margin: 1rem 0; }
        `,
        setup(editor) {
            editor.on('change keyup', () => {
                editor.save();
            });
        },
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPapirarTinyMCE);
} else {
    initPapirarTinyMCE();
}
