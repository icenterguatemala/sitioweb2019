<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$members_entity = array(
    'client' => WPC()->custom_titles['client']['s'],
    'circle' => WPC()->custom_titles['circle']['s']
);

$members_entity = apply_filters( 'wpc_permission_reports_members_entity', $members_entity );

$content_entity = array(
    'file'                  => __( 'File', WPC_CLIENT_TEXT_DOMAIN ),
    'portal_page'           => WPC()->custom_titles['portal_page']['s'],
    'file_category'         => __( 'File Category', WPC_CLIENT_TEXT_DOMAIN ),
    'portal_page_category'  => sprintf( __( '%s Category', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] )
);
$content_entity = apply_filters( 'wpc_permission_reports_content_entity', $content_entity ); ?>

<div class="wrap">
    <?php echo WPC()->admin()->get_plugin_logo_block() ?>
    <div class="wpc_clear"></div>
        <h2><?php _e( 'Permissions Report', WPC_CLIENT_TEXT_DOMAIN ) ?></h2>
        <br />
        <div id="left_select" style="float: left;">
            <label>
                <select name="left_select_first" id="left_select_first" style="float: left;" class="select_report left_select">
                    <?php foreach( $members_entity as $value=>$title ) { ?>
                        <option value="<?php echo $value ?>"><?php echo $title ?></option>
                    <?php } ?>
                </select>
            </label>

            <label>
                <select name="left_select_second" id="left_select_second" style="display: none; float: left;" class="select_report left_select" >
                    <?php foreach( $content_entity as $value=>$title ) { ?>
                        <option value="<?php echo $value ?>"><?php echo $title ?></option>
                    <?php } ?>
                </select>
            </label>
            <br />

            <span id="load_select_filter" style="float: left;"></span>
            <br />
            <div id="for_selectbox"></div>
        </div>

        <div id="right_select" style="float: left;">
            <div id="reverse" style="float: left; cursor: pointer;" class="jfk-button-img" data-course="there"></div>
            <label>
                <select name="right_select_first" id="right_select_first" style="float: left; " class="select_report" >
                    <option value="all" selected="selected"><?php _e( 'Show All', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                    <?php foreach( $content_entity as $value=>$title ) { ?>
                        <option value="<?php echo $value ?>"><?php echo $title ?></option>
                    <?php } ?>
                </select>
            </label>
            <label>
                <select name="right_select_second" id="right_select_second" style="float: left; display: none;"  class="select_report" >
                    <option value="all" selected="selected"><?php _e( 'Show All', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                    <?php foreach( $members_entity as $value=>$title ) { ?>
                        <option value="<?php echo $value ?>"><?php echo $title ?></option>
                    <?php } ?>
                </select>
            </label>
        </div>
        <input type="button" style="float: left; margin-left: 15px;" value="<?php _e( 'Report', WPC_CLIENT_TEXT_DOMAIN ) ?>" class="button-primary" id="report" name="" />
        <span id="load_text_report" style="float: left;"></span>
    <br />
    <div class="wpc_clear"></div>
    <div id="text_report"></div>

    <script type="text/javascript">
        var site_url = '<?php echo site_url() ?>';

        jQuery(document).ready(function(){
            //change left select first
            jQuery( '.left_select' ).change( function(){
                var left_select = jQuery( this ).val();
                jQuery( '#for_selectbox' ).html( '<select name="select_filter" id="select_filter" style="display: none;"  class="select_report chzn-select">' );
                jQuery( '#select_filter' ).html( '' );
                jQuery( '#load_select_filter' ).addClass( 'wpc_ajax_loading' );
                jQuery.ajax({
                    type: 'POST',
                    url: '<?php echo get_admin_url() ?>admin-ajax.php',
                    data: 'action=wpc_get_options_filter_for_permissions&left_select=' + left_select,
                    dataType: 'html',
                    success: function( data ){
                        jQuery( '#select_filter' ).html( data ).css( 'display', 'block' );
                        jQuery( '#load_select_filter' ).removeClass( 'wpc_ajax_loading' );
                        jQuery( '.chzn-select' ).chosen({
                            no_results_text: '<?php echo esc_js( __( 'No results matched', WPC_CLIENT_TEXT_DOMAIN ) ) ?>',
                            allow_single_deselect: true
                        });
                    }
                });
                return false;
            } );

            jQuery( '#left_select_first' ).trigger('change');

            //click reverse
            jQuery( '#reverse' ).click( function() {
                if ( 'there' == jQuery(this).attr( 'data-course' ) ) {
                    jQuery( '#left_select_first' ).hide();
                    jQuery( '#left_select_second' ).show().trigger('change');
                    jQuery( '#right_select_first' ).hide();
                    jQuery( '#right_select_second' ).show();
                    jQuery( '#select_filter' ).hide().html( '' );
                    jQuery(this).attr('data-course', 'back');
                    jQuery( '#text_report' ).html( '' );
                } else if ( 'back' == jQuery(this).attr( 'data-course' ) ) {
                    jQuery( '#left_select_first' ).show().trigger('change');
                    jQuery( '#left_select_second' ).hide();
                    jQuery( '#right_select_first' ).show();
                    jQuery( '#right_select_second' ).hide();
                    jQuery( '#select_filter' ).hide().html( '' );
                    jQuery(this).attr('data-course', 'there');
                    jQuery( '#text_report' ).html( '' );
                }
            });

            //click report
            jQuery( '#report' ).click( function() {
                var select_filter = jQuery( '#select_filter' );
                var left_value = select_filter.val();

                if ( left_value && 'all' != left_value  ) {
                    select_filter.parent().removeClass( 'wpc_error' );
                    var course = jQuery( '#reverse' ).attr('data-course');

                    var right_key = jQuery( '#right_select_first' ).val();
                    var left_key = jQuery( '#left_select_first' ).val();
                    if ( 'back' == course ) {
                        right_key = jQuery( '#right_select_second' ).val();
                        left_key = jQuery( '#left_select_second' ).val();
                    }

                    jQuery( '#text_report' ).css( 'display', 'none' ).html( '' );
                    jQuery( '#load_text_report' ).addClass( 'wpc_ajax_loading' );
                    jQuery.ajax({
                        type: 'POST',
                        url: '<?php echo get_admin_url() ?>admin-ajax.php',
                        data: 'action=wpc_get_report_for_permissions&left_key=' + left_key + '&left_value=' + left_value + '&right_key=' + right_key + '&course=' + course,
                        dataType: 'html',
                        success: function( data ){
                            jQuery( '#text_report' ).html( data ).css( 'display', 'block' );
                            jQuery( '#load_text_report' ).removeClass( 'wpc_ajax_loading' );
                        }
                    });
                } else {
                    select_filter.parent().addClass( 'wpc_error' );
                }

                return false;
            });

        });

    </script>
</div>