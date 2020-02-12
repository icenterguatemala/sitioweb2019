<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$current_tab = ( empty( $_GET['tab'] ) ) ? 'general' : urldecode( $_GET['tab'] );
$current_subtab = !empty( $_GET['subtab'] ) ? urldecode( $_GET['subtab'] ) : '';
//
//$hide_array = array(
//    'capabilities',
//    'default_redirects',
//    'custom_style',
//    'custom_titles',
//    'limit_ips',
//    'gateways',
//    'login_alerts'
//);
//
//if( WPC()->flags['easy_mode'] && in_array( $current_tab, $hide_array ) ) {
//    WPC()->redirect( admin_url( 'admin.php?page=wpclients_settings' ) );
//    exit;
//}

?>

<style type="text/css">
    #captcha_warning,
    #filesize_warning {
        background-color: #FFFFE0;
        border-color: #E6DB55;
        border-radius: 3px 3px 3px 3px;
        border-style: solid;
        border-width: 1px;
        color: #000000;
        font-family: sans-serif;
        font-size: 12px;
        line-height: 1.4em;
        padding: 12px;
    }

</style>

<div class="wrap">

    <?php echo WPC()->admin()->get_plugin_logo_block() ?>

    <div class="wpc_clear"></div>
    <?php if ( WPC()->flags['easy_mode'] ) { ?>
    <div class="notice notice-warning wpc_notice fade"><p>
        <?php printf( __( 'For more settings, you can disable "Easy Mode" in <a href="%1$s" target="_blank">%2$s</a>', WPC_CLIENT_TEXT_DOMAIN ),
                admin_url( 'admin.php?page=wpclients_settings' ), __( 'Settings > General', WPC_CLIENT_TEXT_DOMAIN ) ) ?>
    </p></div>
    <?php } ?>

    <div class="icon32" id="icon-options-general"></div>

    <h2 id="tab-headers2" class="nav-tab-wrapper wpc-nav-tab-wrapper">
        <?php
            $tabs = WPC()->admin()->get_tabs_of_settings();

            if ( $tabs ) {
                foreach ( $tabs as $key => $tab ) {
                    $active = ( $current_tab == $key ) ? ' nav-tab-active' : '';
                    echo '<a class="nav-tab  ' . $active . '" href="' . admin_url( 'admin.php?page=wpclients_settings&tab=' . $key ) . '" >' . $tab['title'] . '</a>';
                }
            }
        ?>
    </h2>

    <?php
    if ( !empty( $tabs[$current_tab]['subtabs'] ) ) {
        $subtabs_html = '<div><ul class="subsubsub wpc-nav-tab-subsubsub">';

        $default_subtab = array_keys( $tabs[$current_tab]['subtabs'] );
        $current_subtab = !empty( $current_subtab ) ? $current_subtab : $default_subtab[0];

        foreach ( $tabs[$current_tab]['subtabs']  as $key => $label ) {
            $active = ( $current_subtab == $key ) ? 'current' : '';
            $subtabs_html .= '<a href="' . admin_url( 'admin.php?page=wpclients_settings&tab=' . $current_tab ) . '&subtab=' . $key . '" class="' . $active . '">'
                . $label .
                '</a> | ';
        }

        $subtabs_html = rtrim( $subtabs_html, ' | ' );
        echo $subtabs_html . '</ul></div>';

    }
    ?>

    <?php if ( !empty( $_GET['msg'] ) ) { ?>
        <div id="message" class="<?php echo ( 't' == $_GET['msg'] || 'pc' == $_GET['msg'] || 'pu' == $_GET['msg'] || 'u' == $_GET['msg'] || 'ps' == $_GET['msg'] ) ? 'updated' : 'error' ?> wpc_notice fade inline">
            <p>
                <?php
                switch( $_GET['msg'] ) {
                    case 'u':
                        _e( 'Settings Updated.', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'cl_url':
                        _e( 'Login URL used default names of Wordpress. Settings are not updated.', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'pu':
                        _e( 'Pages Updated Successfully.', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'pc':
                        _e( 'Pages Re-created Successfully', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'ps':
                        _e( 'You are skipped auto-install pages - please do it manually.', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 't':
                        _e( 'Import was successful', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'f':
                        _e( 'Invalid *.xml file', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'pr_ng':
                        _e( 'Note: The registration will not work until you select "Payment Gateways". Clients will see a message that "Registration temporarily unavailable".', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'pr_f':
                        _e( 'Invalid settings', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'pr_na':
                        _e( 'Not all registration levels was saved', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'nk':
                        _e( 'Public or(and) Privat Key is empty.', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    default:
                        echo stripslashes(urldecode($_GET['msg']));
                }
                ?>
            </p>
        </div>
    <?php } ?>

    <script type="text/javascript">
        jQuery(document).ready(function() {


            /**
             * On option fields change
             */
            jQuery( '.wpc-option-field' ).change( function() {
                if ( jQuery('.wpc-settings-line[data-conditional*=\'"' + jQuery(this).data('field_id') + '",\']').length > 0 ) {
                    run_check_conditions();
                }
            });


            //first load hide unconditional fields
            run_check_conditions();


            /**
             * Run conditional logic
             */
            function run_check_conditions() {
                jQuery( '.wpc-settings-line' ).removeClass('wpc-setting-conditioned').each( function() {
                    if ( typeof jQuery(this).data('conditional') === 'undefined' || jQuery(this).hasClass('wpc-setting-conditioned') )
                        return;

                    if ( check_condition( jQuery(this) ) )
                        jQuery(this).show();
                    else
                        jQuery(this).hide();
                });
            }


            /**
             * Conditional logic
             *
             * true - show field
             * false - hide field
             *
             * @returns {boolean}
             */
            function check_condition( settings_line ) {

                settings_line.addClass('wpc-setting-conditioned');

                var conditional = settings_line.data('conditional');
                var condition = conditional[1];
                var value = conditional[2];

                var condition_field = jQuery( '#wpc_settings_' + conditional[0] );
                var parent_condition = true;
                if ( typeof condition_field.parents('.wpc-settings-line').data('conditional') !== 'undefined' ) {
                    parent_condition = check_condition( condition_field.parents('.wpc-settings-line') );
                }

                var own_condition = false;
                if ( condition == '=' ) {
                    var tagName = condition_field.prop("tagName").toLowerCase();

                    if ( tagName == 'input' ) {
                        var input_type = condition_field.attr('type');
                        if ( input_type == 'checkbox' ) {
                            own_condition = ( value == 'yes' ) ? condition_field.is(':checked') : ! condition_field.is(':checked');
                        } else {
                            own_condition = ( condition_field.val() == value );
                        }
                    } else if ( tagName == 'select' ) {
                        own_condition = ( condition_field.val() == value );
                    }
                }

                return ( own_condition && parent_condition );
            }

        });

    </script>

    <div id="wpc_settings-container">
            <form action="" method="post" name="wpc_settings" id="wpc_settings" >
            <?php

                $file_name = 'settings_' . $current_tab;
                $file_name .= !empty( $current_subtab ) ? "_{$current_subtab}.php" : '.php';


                if ( file_exists( WPC()->plugin_dir . 'includes/admin/' . $file_name ) ) {
                    include_once( WPC()->plugin_dir . 'includes/admin/' . $file_name );
                } else {
                    do_action( 'wpc_client_settings_tab_' . $current_tab );

                    if ( !empty( $current_subtab ) ) {
                        do_action( "wpc_client_settings_tab_{$current_tab}_{$current_subtab}" );
                    }
                }

            ?>

                <input type='submit' name='update_settings' id="wpc_update_settings" class='button-primary' value='<?php _e( 'Update Settings', WPC_CLIENT_TEXT_DOMAIN ) ?>' />
            </form>
        </div>
</div>
