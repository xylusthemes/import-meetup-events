(function($) {
    'use strict';

    jQuery(document).ready(function() {
        jQuery(document).on("click", ".ime_datepicker", function() {
            jQuery(this).datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy-mm-dd',
                showOn: 'focus'
            }).focus();
        });
    });

    jQuery(document).ready(function() {
        jQuery(document).on('change', '#import_type', function() {
            if (jQuery(this).val() != 'onetime') {
                jQuery('.hide_frequency .import_frequency').show();
            } else {
                jQuery('.hide_frequency .import_frequency').hide();
            }
        });

        jQuery("#import_type").trigger('change');
    });

    // Render Dynamic Terms.
    jQuery(document).ready(function() {
        jQuery(document).on('change', '.event_import_plugin', function() {

            var event_plugin = jQuery(this).val();
            var data = {
                'action': 'ime_render_terms_by_plugin',
                'event_plugin': event_plugin
            };

            var terms_space = jQuery('.event_taxo_terms_wraper');
            terms_space.html('<span class="spinner is-active" style="float: none;"></span>');
            // send ajax request.
            jQuery.post(ajaxurl, data, function(response) {
                if (response != '') {
                    terms_space.html(response);
                } else {
                    terms_space.html('');
                }
            });
        });
        jQuery(".event_import_plugin").trigger('change');
    });

    // Color Picker
    jQuery(document).ready(function($) {
        $('.ime_color_field').each(function() {
            $(this).wpColorPicker();
        });
    });

    //Shortcode Copy Text
	jQuery(document).ready(function($){
		$(document).on("click", ".ime-btn-copy-shortcode", function() { 
			var trigger = $(this);
			$(".ime-btn-copy-shortcode").removeClass("text-success");
			var $tempElement = $("<input>");
			$("body").append($tempElement);
			var copyType = $(this).data("value");
			$tempElement.val(copyType).select();
			document.execCommand("Copy");
			$tempElement.remove();
			$(trigger).addClass("text-success");
			var $this = $(this),
			oldText = $this.text();
			$this.attr("disabled", "disabled");
			$this.text("Copied!");
			setTimeout(function(){
				$this.text("Copy");
				$this.removeAttr("disabled");
			}, 800);
	  
		});

	});

})(jQuery);