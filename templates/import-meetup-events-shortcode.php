<?php
/**
 * The template for displaying Shortcode.
 *
 * @package Import_Meetup_Events
 */

// If this file is called directly, about.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $ime_events;
?>
<div class="ime_container">
    <div class="ime_row">
    <h3 class="setting_bar"><?php esc_attr_e( 'Shortcode', 'import-meetup-events' ); ?></h3>
        <div class="shortcode_container">
            <div class="box-part">
                <div class="title">
                    <h4>Display All Events</h4>
                </div>
                <div class="text" >
                    <code>[meetup_events]</code>      
                </div>
            </div>
                
            <div class="box-part">                
                <div class="title">
                    <h4>Display with column</h4>
                </div>
                <div class="text" >
                    <code>[meetup_events col="2"]</code>        
                </div>
            </div>
            
            <div class="box-part">
                <div class="title">
                    <h4>Limit for display events</h4>
                </div>
                <div class="text">
                    <code>[meetup_events posts_per_page="12"]</code>
                </div>
            </div>
            
            <div class="box-part">
                <div class="title">
                    <h4>Display Events based on order</h4>
                </div>
                <div class="text">
                    <code>[meetup_events order="asc"]</code>
                </div>
            </div>
            
            <div class="box-part">
                <div class="title">
                    <h4>Display events based on category</h4>
                </div>
                <div class="text">
                    <code>[meetup_events category="cat1"]</code>
                </div>
            </div>
            
            <div class="box-part">
                <div class="title">
                    <h4>Display Past events</h4>
                </div>
                <div class="text">
                    <code>[meetup_events past_events="yes"]</code>
                </div>
            </div>
            
            <div class="box-part">
                <div class="title">
                    <h4>Display Events based on order</h4>
                </div>
                <div class="text">
                    <code>[meetup_events order="asc"]</code>
                </div>
            </div>
            
            <div class="box-part">
                <div class="title">
                    <h4>Display Events based on orderby</h4>
                </div>
                <div class="text">
                    <code>[meetup_events order="asc" orderby="post_title"]</code>
                </div>
            </div><br />
            
            <div class="last">
                <div class="title">
                    <h4> Full Short-code:</h4>
                </div>
                <div class="text">
                    <code>[meetup_events col="2" posts_per_page="12" category="cat1,cat2" past_events="yes" order="desc" orderby="post_title" start_date="YYYY-MM-DD" end_date="YYYY-MM-DD"]</code> 
                </div>
            </div>         
        </div>	
    </div>
</div>


