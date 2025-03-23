jQuery(document).ready(function($) {
    $('.generate-template-button').on('click', function(e) {
        e.preventDefault();

        var order_id = $(this).data('order-id');
        var template_name = $(this).data('template');

        $.post(wruw_vars.ajax_url, {
            action: 'wruw_generate_template',
            nonce: wruw_vars.nonce,
            order_id: order_id,
            template: template_name
        }, function(response) {
            alert(response);
        });
    });
});
