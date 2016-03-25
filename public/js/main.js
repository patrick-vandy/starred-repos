
$(document).ready(function()
{

    $('[data-ajax-target]').ajaxTarget();

    $('select[data-auto-submit]').on('change', function()
    {
        var name = encodeURIComponent($(this).attr('name'));
        var val = encodeURIComponent($(this).val());
        window.location = $(this).closest('form').attr('action') + '/' + name + ':' + val;
    });

});