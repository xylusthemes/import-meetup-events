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

    jQuery(document).ready(function(){
        jQuery('#meetup_import_by').on('change', function(){
    
            if( jQuery(this).val() == 'event_id' ){
                jQuery('.import_type_wrapper').hide();
                jQuery('.meetup_group_url').hide();
                jQuery('.meetup_group_url .meetup_url').removeAttr( 'required' );
                jQuery('.meetup_event_id').show();
                jQuery('.meetup_event_id .ime_event_ids').attr('required', 'required');
            
            }else if( jQuery(this).val() == 'group_url' ){
                jQuery('.import_type_wrapper').show();
                jQuery('.meetup_group_url').show();
                jQuery('.meetup_group_url .meetup_url').attr('required', 'required');
                jQuery('.meetup_event_id').hide();
                jQuery('.meetup_event_id .ime_event_ids').removeAttr( 'required' );
            }
        });
    
        jQuery('#import_type').on('change', function(){
            if( jQuery(this).val() != 'onetime' ){
                jQuery('.hide_frequency .import_frequency').show();
            }else{
                jQuery('.hide_frequency .import_frequency').hide();
            }
        });
    
        jQuery("#import_type").trigger('change');
        jQuery("#meetup_import_by").trigger('change');
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

jQuery(document).ready(function($){

    const ime_tab_link = document.querySelectorAll('.ime_tab_link');
    const ime_tabcontents = document.querySelectorAll('.ime_tab_content');

    ime_tab_link.forEach(function(link) {
        link.addEventListener('click', function() {
        const ime_tabId = this.dataset.tab;

        ime_tab_link.forEach(function(link) {
            link.classList.remove('active');
        });

        ime_tabcontents.forEach(function(content) {
            content.classList.remove('active');
        });

        this.classList.add('active');
        document.getElementById(ime_tabId).classList.add('active');
        });
    });

    const ime_gm_apikey_input = document.querySelector('.ime_google_maps_api_key');
	if ( ime_gm_apikey_input ) { 
		ime_gm_apikey_input.addEventListener('input', function() { 
			const ime_check_key = document.querySelector('.ime_check_key'); 
			if (ime_gm_apikey_input.value.trim() !== '') { 
				ime_check_key.style.display = 'contents'; 
			} else { 
				ime_check_key.style.display = 'none'; 
			} 
		}); 
	}

    const ime_checkkeylink = document.querySelector('.ime_check_key a');
    if ( ime_checkkeylink ) { 
		ime_checkkeylink.addEventListener('click', function(event) { 
			event.preventDefault(); 
			const ime_gm_apikey = ime_gm_apikey_input.value.trim();
			if ( ime_gm_apikey !== '' ) { 
				ime_check_gmap_apikey(ime_gm_apikey); 
			} 
		}); 
	}

    function ime_check_gmap_apikey(ime_gm_apikey) {
        const ime_xhr = new XMLHttpRequest();
        ime_xhr.open('GET', 'https://www.google.com/maps/embed/v1/place?q=New+York&key=' + encodeURIComponent(ime_gm_apikey), true);
        const ime_loader = document.getElementById('ime_loader');
        ime_loader.style.display = 'inline-block';
        ime_xhr.onreadystatechange = function() {
            if ( ime_xhr.readyState === XMLHttpRequest.DONE ) {
                ime_loader.style.display = 'none';
                if (ime_xhr.status === 200) {
                    const response = ime_xhr.responseText;
                    var ime_gm_success_notice = jQuery("#ime_gmap_success_message");
                        ime_gm_success_notice.html('<span class="ime_gmap_success_message">Valid Google Maps License Key</span>');
                        setTimeout(function(){ ime_gm_success_notice.empty(); }, 2000);
                } else {
                    var ime_gm_error_notice = jQuery("#ime_gmap_error_message");
                    ime_gm_error_notice.html( '<span class="ime_gmap_error_message" >Inalid Google Maps License Key</span>' );
                        setTimeout(function(){ ime_gm_error_notice.empty(); }, 2000);
                }
            }
        };

        ime_xhr.send();
    }

});