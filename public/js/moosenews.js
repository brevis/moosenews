jQuery(document).ready(function($) {

    var data = {
        "content": "bla-bla-bla"
    };

    function disableFormControls() {
        jQuery('.moosenews-form *').prop('disabled', true);
    }

    function enableFormControls() {
        jQuery('.moosenews-form *').prop('disabled', false);
    }

    function submitForm(submitType) {
        $('.moosenews-form textarea').removeClass('error');
        var content = $.trim($('.moosenews-form textarea').val());
        var maxlength = parseInt($('.moosenews-form textarea').data('maxlength'), 10);
        if (content.length < 1 || content.length > maxlength) {
            $('.moosenews-form textarea').addClass('error');
            return false;
        }

        var ajaxurl = moosenewsvars.ajaxurl_createtheme;
        if (submitType == 'preview') ajaxurl = moosenewsvars.ajaxurl_previewtheme;
        disableFormControls();
        $('.moosenews-form-preview').html('');
        $.post(ajaxurl, {"content": content}, function(response) {
            if ( response.status == 'ok' ) {
                if (submitType == 'preview') {
                    $('.moosenews-form-preview').html('<div>' + response.content + '</div>');
                } else {
                    // refresh page
                    document.location.href = document.location.href;
                }
            } else {
                $('.moosenews-form-preview').html('<div class="errors">' + response.errors + '</div>');
            }
            enableFormControls();
        }, "json");
    }

    $('.moosenews-form').on('submit', function(event) {
        submitForm('submit');
        event.preventDefault();
    });

    $('.moosenews-form button.preview').on('click', function() {
        submitForm('preview');
    });

    // textarea maxlength
    $('.moosenews-form .chars-counter span').text($('.moosenews-form textarea').data('maxlength'));
    $('.moosenews-form textarea').on('keydown keyup change', function() {
        var content = $.trim($(this).val());
        var limit = parseInt($(this).data('maxlength'), 10);
        var chars = content.length;
        if (chars > limit){
            var new_content = content.substr(0, limit);
            $(this).val(new_content);
            chars = limit;
        }
        $('.moosenews-form .chars-counter span').text(limit - chars);
    });

    // rating
    $('.moosenews-theme .moosenews-rating a').on('click', function(event) {
        if ($(this).hasClass('disabled')) return false;

        var id = $(this).data('id');
        var type = $(this).data('type');
        $.post(moosenewsvars.ajaxurl_vote, {"id": id, "type": type}, function(response) {
            if ( response.status == 'ok' ) {
                $('#moosenewstheme' + id + ' .moosenews-rating a').addClass('disabled');
                $('#moosenewstheme' + id + ' .moosenews-rating a.' + type).addClass('voted');

                var rating = parseInt(response.content, 10);
                var $ratingEl = $('#moosenewstheme' + id + ' .moosenews-rating span.counter');
                $ratingEl.text(rating);

                $ratingEl.removeClass('good').removeClass('bad');
                if (rating > 0) {
                    $ratingEl.removeClass('bad').addClass('good');
                } else if (rating < 0) {
                    $ratingEl.removeClass('good').addClass('bad');
                }
            }
        }, "json");
        event.preventDefault();
    });

    // delete theme
    $('.moosenews-theme .admin .deletetheme').on('click', function(event) {
        var id = $(this).data('id');
        var $el = $('#moosenewstheme' + $(this).data('id'));
        $el.fadeOut();
        $.post(moosenewsvars.ajaxurl_deletetheme, {"id": id}, function() {
            setTimeout(function() {
                $el.remove();
            }, 500);
        });
        event.preventDefault();
    });
});