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
                jQuery('.meetup_group_url .ime_group_url').removeAttr( 'required' );
                jQuery('.meetup_event_id').show();
                jQuery('.meetup_event_id .ime_event_id').attr('required', 'required');
            
            }else if( jQuery(this).val() == 'group_url' ){
                jQuery('.import_type_wrapper').show();
                jQuery('.meetup_group_url').show();
                jQuery('.meetup_group_url .ime_group_url').attr('required', 'required');
                jQuery('.meetup_event_id').hide();
                jQuery('.meetup_event_id .ime_event_id').removeAttr( 'required' );
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

})(jQuery);