(function () {
    document.addEventListener('DOMContentLoaded', function () {
        if (!window.tinymce) {
            console.error('TinyMCE não foi carregado. Verifique assets/tinymce/tinymce.min.js');
            return;
        }

        const uploadUrl = window.PAPIRAR_EDITOR_UPLOAD_URL;
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content');

        if (!uploadUrl) {
            console.warn('URL de upload do editor não configurada.');
        }

        window.tinymce.init({
            selector: 'textarea.papirar-rich-editor',

            license_key: 'gpl',

            base_url: '/assets/tinymce',
            suffix: '.min',
            skin: 'oxide',
            content_css: '/assets/tinymce/skins/content/default/content.css',

            convert_urls: false,
            relative_urls: false,
            remove_script_host: false,
            document_base_url: window.location.origin + '/',

            height: 450,
            menubar: 'file edit view insert format tools table',
            branding: false,
            promotion: false,

            plugins: 'advlist lists link image media table code preview searchreplace visualblocks fullscreen charmap autoresize',

            toolbar: [
                'undo redo | blocks | bold italic underline strikethrough | forecolor backcolor',
                'alignleft aligncenter alignright alignjustify | bullist numlist outdent indent',
                'link image media table | removeformat | preview code fullscreen'
            ].join(' | '),

            automatic_uploads: true,
            images_upload_credentials: true,
            images_file_types: 'jpeg,jpg,png,gif,webp',
            image_advtab: true,

            images_upload_handler: function (blobInfo, progress) {
                return new Promise(function (resolve, reject) {
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
                            'Accept': 'application/json'
                        },
                        body: formData,
                        credentials: 'same-origin'
                    })
                        .then(function (response) {
                            return response.json();
                        })
                        .then(function (result) {
                            if (!result || !result.location) {
                                reject('Resposta inválida do upload.');
                                return;
                            }

                            resolve(result.location);
                        })
                        .catch(function () {
                            reject('Falha ao enviar imagem.');
                        });
                });
            },

            setup: function (editor) {
                editor.on('keydown', function (event) {
                    const isSaveShortcut =
                        (event.ctrlKey || event.metaKey) &&
                        event.key.toLowerCase() === 's';

                    if (!isSaveShortcut) {
                        return;
                    }

                    event.preventDefault();
                    event.stopPropagation();

                    const form = document.getElementById('question-form');

                    if (!form) {
                        return;
                    }

                    window.tinymce.triggerSave();
                    form.requestSubmit();
                });
            }
        });
    });
})();