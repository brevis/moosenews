jQuery(document).ready(function($) {

    function disableFormControls() {
        jQuery('.moosenews-form *').prop('disabled', true);
    }

    function enableFormControls() {
        jQuery('.moosenews-form *').prop('disabled', false);
    }

    function submitForm(submitType, el) {
        var $textarea = $(el).parents('.moosenews-form').find('textarea');
        $textarea.removeClass('error');
        var content = $.trim($textarea.val());
        var maxlength = parseInt($textarea.data('maxlength'), 10);
        if (content.length < 1 || content.length > maxlength) {
            $textarea.addClass('error');
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

    // create new theme
    $('.moosenews-form button.submit').on('click', function(event) {
        submitForm('submit', this);
        event.preventDefault();
    });

    // preview
    $('.moosenews-form button.preview').on('click', function() {
        submitForm('preview', this);
    });

    // close form
    $('.moosenews-form button.close').on('click', function() {
        $(this).parents('.moosenews-form').hide();
        $(this).parents('.moosenews-theme').find('.moosenews-content').show();
    });

    // edit theme
    $('.moosenews-theme .admin .edittheme').on('click', function(event) {
        $(this).parents('.moosenews-theme').find('.moosenews-content').hide();
        $(this).parents('.moosenews-theme').find('.moosenews-form').show();
        event.preventDefault();
    });

    $('.moosenews-form button.update').on('click', function() {
        var id = $(this).data('id');
        var $textarea = $(this).parents('.moosenews-form').find('textarea');
        $textarea.removeClass('error');
        var content = $.trim($textarea.val());
        var maxlength = parseInt($textarea.data('maxlength'), 10);
        if (content.length < 1 || content.length > maxlength) {
            $textarea.addClass('error');
            return false;
        }
        var ajaxurl = moosenewsvars.ajaxurl_updatetheme;
        var $error = $(this).parents('.moosenews-form').find('.error');
        $error.html('');
        var $newsContainer = $(this).parents('.moosenews-theme');
        $.post(ajaxurl, {"id": id, "content": content}, function(response) {
            if ( response.status == 'ok' ) {
                $newsContainer.find('.moosenews-form').hide();
                $newsContainer.find('.moosenews-content .content').html(response.content);
                $newsContainer.find('.moosenews-content').show();
            } else {
                $error.html(response.errors);
            }
        }, "json");
    });

    // textarea maxlength
    function calculateTextareaChars(el) {
        var content = $.trim($(el).val());
        var limit = parseInt($(el).data('maxlength'), 10);
        var chars = content.length;
        if (chars > limit){
            var new_content = content.substr(0, limit);
            $(el).val(new_content);
            chars = limit;
        }
        $(el).parent().find('.chars-counter span').text(limit - chars);
    }
    $('.moosenews-form .chars-counter span').text($('.moosenews-form textarea').data('maxlength'));
    $('.moosenews-form textarea').on('keydown keyup change', function() {
        calculateTextareaChars(this);
    }).each(function() {
        calculateTextareaChars(this);
    });

    // rating
    $('.moosenews-theme .moosenews-rating a').on('click', function(event) {
        if (!moosenewsvars.id_admin_logged && $(this).hasClass('disabled')) return false;

        var id = $(this).data('id');
        var type = $(this).data('type');
        $.post(moosenewsvars.ajaxurl_vote, {"id": id, "type": type}, function(response) {
            if ( response.status == 'ok' ) {
                if (!moosenewsvars.id_admin_logged) {
                    $('#moosenewstheme' + id + ' .moosenews-rating a').addClass('disabled');
                    $('#moosenewstheme' + id + ' .moosenews-rating a.' + type).addClass('voted');
                }

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