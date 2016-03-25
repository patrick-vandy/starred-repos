/**
 * ajaxTarget plugin.
 *
 * Simple plugin to populate a container element with data from an
 * ajax call when a link or button is clicked.
 *
 * The clickable element that results in the container being populated
 * should contain a data-ajax-target="|CONTAINER|" attribute where
 * |CONTAINER| is the css selector for the container element.
 *
 * The container should have child elements with a data-ajax-field="|FIELD|"
 * attribute where |FIELD| corresponds to a property name in the json data
 * object returned via the ajax call.
 *
 * Example HTML:
 *
 * <a href="/some/ajax/call" data-ajax-target=".container">Info</a>
 * <div class="container">
 *     <span data-ajax-field="field_1"></span><br>
 *     <span data-ajax-field="field_2"></span><br>
 *     ...
 * </div>
 *
 * Example ajax response:
 *
 * {
 *     "error": 0,
 *     "data": {
 *         "field_1": "Value 1",
 *         "field_2": "Value 2"
 *     }
 * }
 */
(function($)
{

    $.fn.ajaxTarget = function(options)
    {
        var opts = $.extend({}, $.fn.ajaxTarget.settings, options);

        $(this).each(function()
        {
            var data = {
                obj: $(this),
                options: opts
            };

            init(data);
        });

        return this;
    };

    $.fn.ajaxTarget.settings = {
        urlAttr: 'href'
    };


    function init(data)
    {
        data.obj.on('click', function()
        {
            var url = $(this).attr(data.options.urlAttr);
            var modal = $($(this).data('ajax-target'));
            $.ajax({
                url: url,
                success: function(resp)
                {
                    var val;
                    var regex = /^((https?:\/\/)?[\w-]+(\.[\w-]+)+\.?(:\d+)?(\/\S*)?)$/i;
                    for (var field in resp.data)
                    {
                        if (resp.data.hasOwnProperty(field))
                        {
                            val = resp.data[field].trim();
                            if (val.match(regex))
                            {
                                val = val.link(val);
                            }
                            modal.find('[data-ajax-field="' + field + '"]').html(val);
                        }
                    }
                }
            });
        });
    }

})(jQuery);