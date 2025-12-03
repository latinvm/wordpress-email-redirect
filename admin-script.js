jQuery(document).ready(function($) {
    var rowIndex = erAdmin.rowCount;

    $('#er-add-row').on('click', function() {
        var newRow = '<tr>' +
            '<td>' +
                '<input type="text" ' +
                       'name="' + erAdmin.optionName + '[' + rowIndex + '][domain]" ' +
                       'class="regular-text" ' +
                       'placeholder="' + erAdmin.placeholderDomain + '" />' +
            '</td>' +
            '<td>' +
                '<input type="url" ' +
                       'name="' + erAdmin.optionName + '[' + rowIndex + '][url]" ' +
                       'class="regular-text" ' +
                       'placeholder="' + erAdmin.placeholderUrl + '" />' +
            '</td>' +
            '<td>' +
                '<button type="button" class="button er-remove-row">' + erAdmin.removeText + '</button>' +
            '</td>' +
        '</tr>';
        $('#er-mappings-body').append(newRow);
        rowIndex++;
    });

    $(document).on('click', '.er-remove-row', function() {
        $(this).closest('tr').remove();
    });
});
