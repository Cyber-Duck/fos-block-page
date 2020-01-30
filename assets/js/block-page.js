(function($) {
    $.entwine('ss', function($) {
        $('input[name="BlockType"]').entwine({
            onmatch: function() {
                $('input[name="BlockType"]:first').attr('checked',true);
            }
        });
    });

    var $autocompleteHtml = ''

    function readjustBlockpageAutoComplateSearchboxPosition()
    {
        if ($('#Root_ContentBlocks') == null || $('#Root_ContentBlocks') == undefined) {
            return
        }

        if ($autocompleteHtml == '') {
            $autocompleteHtml = "<div class='add-existing-autocompleter'>" + $('#Root_ContentBlocks .add-existing-autocompleter').html() + "</div>";
        }
        var $existingToolbarRightHtml = $("#Root_ContentBlocks .ss-gridfield-buttonrow").find('.pull-xs-right').html()
        $("#Root_ContentBlocks .ss-gridfield-buttonrow").find('.pull-xs-right').html('')
        $('#Root_ContentBlocks .add-existing-autocompleter').html('')
        $("#Root_ContentBlocks .ss-gridfield-buttonrow").find('.pull-xs-right').html($autocompleteHtml + $existingToolbarRightHtml)
    }

    $('body').on('click', '.grid-field__filter-open', function () {
        setTimeout(readjustBlockpageAutoComplateSearchboxPosition, 2000)
    })

    $('body').on('click', '.search-box__cancel', function () {
        setTimeout(readjustBlockpageAutoComplateSearchboxPosition, 2000)
    })

    readjustBlockpageAutoComplateSearchboxPosition()
})(jQuery);
