$(document).ready(function() {
    let updateOrderUrl = '/admin/menu-item/update-order'; // Change this to your endpoint

    $("#sonata-ba-list").sortable({
        items: ".sonata-ba-list-field-number:has(div)",
        handle: ".sonata-move",
        update: function(event, ui) {
            let orderedIds = $(this).sortable('toArray', { attribute: 'data-id' });

            $.post(updateOrderUrl, { orderedIds: orderedIds }, function(response) {
                if (response.success) {
                    // Optionally: Show a notification of success
                } else {
                    // Handle errors (e.g., show an error notification)
                }
            });
        }
    });

    // default_filed_button_action.html.twig confirm action
    $('a[data-confirm]').click(function(ev) {
        let href = $(this).attr('href');
        const data_confirm_modal = $('#dataConfirmModal');
        if (!data_confirm_modal.length) {
            $('body').append('' +
                '<div id="dataConfirmModal" class="modal fade" role="dialog" aria-labelledby="dataConfirmLabel" aria-hidden="true">' +
                '<div class="modal-dialog modal-lg">' +
                '<div class="modal-content">' +
                '<div class="modal-header">' +
                '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
                '<h4 class="modal-title"></h4>' +
                '</div>' +
                '' +
                '<div class="modal-body">' +
                '</div>' +
                '<div class="modal-footer">' +
                '<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>' +
                '<a class="btn btn-primary" id="dataConfirmOK">OK</a>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>');
        }
        data_confirm_modal.find('.modal-body').text($(this).attr('data-confirm'));
        $('#dataConfirmOK').attr('href', href);
        data_confirm_modal.modal({show:true});
        return false;
    });
});