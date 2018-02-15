(function ($) {


    var eoModalTriggers = $('#eo_edit_btn, #eo_create_btn');

    $(document).ready(function() {

        eoModalTriggers.on('click', function(e) {
            e.preventDefault();
            // We create the modal :
            var eoModal = $.eonetModal();
            eoModal.create();
            var eoModalBg = $('div.eo_modal_backdrop');
            // We add the loader :
            eoModalBg.eonetLoader();
            // We show it :
            eoModal.show();
            // We get the form :
            var method = ($(this).attr('id') == 'eo_edit_btn') ? 'manage' : 'create';
            eoFrontGet(method);

        });

    });

    /**
     * We get the form using AJAX
     * @param method
     * @returns {boolean}
     */
    function eoFrontGet(method) {
        if(method == 'manage'){
            var trigger = $('#eo_edit_btn'),
                postID = trigger.data('eo-post-id'),
                postType = '';
        } else {
            var trigger = $('#eo_create_btn'),
                postID = '',
                postType = trigger.data('eo-post-type');
        }
        // We prepare the query
        var data = {
            'action' : EONET_FRONTEND.frontend_action,
            'security' : EONET_FRONTEND.frontend_nonce,
            'method' : method,
            'id' : postID,
            'type' : postType,
        };
        jQuery.post(EONET_FRONTEND.ajax_url, data, function(response) {
            // Render the content :
            var eoModalCurrent = $('#eonet_modal'),
                eoModalBg = $('.eo_modal_backdrop');
            eoModalBg.find('.eonet_loader').remove();
            eoModalBg.removeClass('has_loader');
            var eoModal = $.eonetModal();
            eoModal.feed(response);
            // Editor :
            var eoEditor = eoModalCurrent.find('.wp-editor-wrap');
            if(eoEditor.length != 0) {
                var eoTextarea = 'eo_field_post_content';
                tinymce.execCommand( 'mceRemoveEditor', true, eoTextarea );
                tinymce.execCommand( 'mceAddEditor', true, eoTextarea );
            }
            // Upload :
            $.eonetMedia();
            // Tags :
            $.eonetTags();
            // Screens :
            eoFrontScreens();
            // On save :
            eoModalCurrent.find('#eo_modal_kickstart').on('click', function (e) {
                e.preventDefault();
                eoFrontSave();
            });

            // On delete :
            $('#eo_confirm_go').on('click', function () {
                eoFrontDelete();
            });

        });
        return false;

    }

    function eoFrontDelete() {

        var eoModalCurrent = $('#eonet_modal');
        if(eoModalCurrent.length == 0) {
            return null;
        }

        eoModalCurrent.find('.eo_modal_dialog').eonetLoader({ 'colored' : true });

        var data = eoModalCurrent.find('#eo_frontend_delete').serialize();

        jQuery.post(EONET_FRONTEND.ajax_url, data, function(response) {

            // Let's get rid of that loader :
            eoModalCurrent.find('.eo_modal_dialog').find('.eonet_loader').remove();
            eoModalCurrent.find('.eo_modal_dialog').removeClass('has_loader');

            // Cretae the alert :
            var object = JSON.parse(response);
            if(object.length != 0) {
                var alertClass = (object.status == 'success') ? 'fa-check-circle' : 'fa-times';
                $.eonetNotification(alertClass, object.title, object.content);
                setTimeout(function () {
                    window.location = object.permalink
                }, 2000);
            }

        });

        return false;
    }

    function eoFrontScreens() {

        var wrapper = $('.eo_modal_dialog'),
            showTrigger = wrapper.find('#eo_modal_delete'),
            backTrigger = wrapper.find('#eo_confirm_back');

        // We hide the second one :
        wrapper.find('.eo_screen_2').hide();

        // On click to display it :
        showTrigger.on('click', function () {

            // We add the loader :
            wrapper.find('.eo_screen_1').eonetLoader({ 'colored' : true });

            // We wait until fire :
            setTimeout(function () {
                // We remove the loader :
                wrapper.find('.eo_screen_1').find('.eonet_loader').remove();
                wrapper.find('.eo_screen_1').removeClass('has_loader');
                // We switch :
                wrapper.find('.eo_screen_1').fadeOut();
                wrapper.find('.eo_screen_2').fadeIn();
                wrapper.find('#eo_modal_kickstart').fadeOut();
                showTrigger.fadeOut();
            }, 300);

        });

        // We handle the close button :
        backTrigger.on('click', function () {

            // We add the loader :
            wrapper.find('.eo_screen_2').eonetLoader({ 'colored' : true });

            // We wait until fire :
            setTimeout(function () {
                // We remove the loader :
                wrapper.find('.eo_screen_2').find('.eonet_loader').remove();
                wrapper.find('.eo_screen_2').removeClass('has_loader');
                // We switch :
                wrapper.find('.eo_screen_2').fadeOut();
                wrapper.find('.eo_screen_1').fadeIn();
                wrapper.find('#eo_modal_kickstart').fadeIn();
                showTrigger.fadeIn();
            }, 300);

        });


    }

    function eoFrontSave() {

        var eoModalCurrent = $('#eonet_modal');
        if(eoModalCurrent.length == 0) {
            return null;
        }

        eoModalCurrent.find('.eo_modal_dialog').eonetLoader({ 'colored' : true });

        var data = eoModalCurrent.find('#eo_frontend_form').serialize();

        var tinyEditorContent = tinyMCE.activeEditor.getContent();

        data += "&wp_editor_content=" + encodeURIComponent(tinyEditorContent);

        jQuery.post(EONET_FRONTEND.ajax_url, data, function(response) {

            // Let's get rid of that loader :
            eoModalCurrent.find('.eo_modal_dialog').find('.eonet_loader').remove();
            eoModalCurrent.find('.eo_modal_dialog').removeClass('has_loader');

            // Create the alert :
            var object = JSON.parse(response);
            if(object.length != 0) {
                var alertClass = (object.status == 'success') ? 'fa-check-circle' : 'fa-times';
                $.eonetNotification(alertClass, object.title, object.content);
                // Live change :
                var postTitleWrapper = $('.entry-header .entry-title');
                if(postTitleWrapper.length != 0 && object.post_title.length != 0) {
                    postTitleWrapper.text( object.post_title );
                }
                // if redirection needed :
                if(object.method == 'create') {
                    setTimeout(function () {
                        window.location = object.permalink
                    }, 2000);
                }
            }

        });

    }

})(jQuery);