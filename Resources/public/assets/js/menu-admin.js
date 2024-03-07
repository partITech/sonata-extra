$(document).ready(function() {
    var updateOrderUrl = '/admin/menu-item/update-order'; // Change this to your endpoint

    $("#sonata-ba-list").sortable({
        items: ".sonata-ba-list-field-number:has(div)",
        handle: ".sonata-move",
        update: function(event, ui) {
            var orderedIds = $(this).sortable('toArray', { attribute: 'data-id' });

            $.post(updateOrderUrl, { orderedIds: orderedIds }, function(response) {
                if (response.success) {
                    // Optionally: Show a notification of success
                } else {
                    // Handle errors (e.g., show an error notification)
                }
            });
        }
    });
});