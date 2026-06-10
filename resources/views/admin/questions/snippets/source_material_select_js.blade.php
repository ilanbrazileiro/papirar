<script>
$(function () {
    const $sourceMaterial = $('#source_material_id');

    if ($sourceMaterial.length && $.fn.select2) {
        $sourceMaterial.select2({
            theme: 'bootstrap4',
            width: '100%',
            allowClear: true,
            placeholder: $sourceMaterial.data('placeholder') || 'Selecione a fonte/material',
            ajax: {
                url: '{{ route('admin.questions.ajax-source-materials') }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term,
                        corporation_id: $('#corporation_id').val(),
                        exam_id: $('#exam_id').val(),
                        subject_id: $('#subject_id').val()
                    };
                },
                processResults: function (data) {
                    return data;
                }
            }
        });
    }

    $('#corporation_id, #exam_id, #subject_id').on('change', function () {
        $sourceMaterial.val(null).trigger('change');
    });
});
</script>
