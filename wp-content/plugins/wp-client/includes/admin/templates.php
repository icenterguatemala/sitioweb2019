<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

//remove buttons for editor
//todelete?
//remove_all_filters( 'mce_external_plugins' );
$current_tab = ( empty( $_GET['tab'] ) ) ? 'portal_page' : urldecode( $_GET['tab'] );
if( WPC()->flags['easy_mode'] && 'shortcodes' == $current_tab ) {
    WPC()->redirect( admin_url( 'admin.php?page=wpclients_templates' ) );
}

if ( isset( $_GET['msg'] ) ) {
    switch( $_GET['msg'] ) {
        case 'u':
            echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Template Updated.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
            break;
        case 'hub_updated':
            echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Template and all HUB pages are Updated.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
            break;
    }
} ?>

<style type="text/css">
    .wp-editor-container {
        background-color: #fff;
    }

    .wpc_clear{
        clear: both;
        height: 0;
        visibility: hidden;
        display: block;
    }
    a{
        text-decoration: none;
    }
    /******* GENERAL RESET *******/

    /******* MENU *******/
    #container23{

        width: 99%;
    }
    #container23 ul{
        list-style: none;
        list-style-position: outside;
    }
    #container23 ul.menu li{
        float: left;
        margin-right: 5px;
        margin-bottom: -1px;
    }
    #container23 ul.menu li{
        font-weight: 700;
        display: block;
        padding: 5px 10px 5px 10px;
        background: #efefef;
        margin-bottom: -1px;
        border: 1px solid #d0ccc9;
        border-width: 1px 1px 1px 1px;
        position: relative;
        color: #898989;
        cursor: pointer;
    }
    #container23 ul.menu li.active{
        background: #fff;
        top: 1px;
        border-bottom: 0;
        color: #5f95ef;
    }
    /******* /MENU *******/

    /******* /LINKS *******/
    .db_template, .file_template {
        border:none;
        resize:none;
        height: 200px;
        width: 500px;
        margin: -14px 0 5px 0;
        overflow: scroll;
    }
    .compare_template {
        padding:5px 0;
        margin:-14px 0 5px 0;
        height: 200px;
        width: 1020px;
        overflow: scroll;
        background-color: #fff;
    }
    .update_template {
        position: absolute;
        top: 4px;
        right: 10px;
        width: 60px;
        height: 22px;
    }

    #email_tabs .wpc_templates_enable{
        margin: 0 0 0 0 !important;
    }


</style>
<script type="text/javascript" language="javascript">
    jQuery(document).ready(function() {
        <?php if ( isset( $_GET['set_tab'] ) && '' != $_GET['set_tab'] ) { ?>
            jQuery("#other").trigger('click');
        <?php } ?>
    });

</script>

<div class="wrap">
    <?php echo WPC()->admin()->get_plugin_logo_block() ?>

    <div class="wpc_clear"></div>

    <div id="wpc_container">
        <?php echo WPC()->admin()->gen_tabs_menu( 'templates' ) ?>

        <div class="wpc_clear"></div>
        <div class="wpc_tab_container_block">
            <?php switch ( $current_tab ) {
                case "portal_page":
                    include_once( WPC()->plugin_dir . 'includes/admin/templates_portal_page.php' );
                break;
                case "emails":
                    include_once( WPC()->plugin_dir . 'includes/admin/templates_emails.php' );
                break;
                case "php_templates":
                    include_once( WPC()->plugin_dir . 'includes/admin/templates_php.php' );
                break;
                default:
                    do_action( 'wpc_client_templates_tabs_' . $current_tab );
                break;
            } ?>
        </div>
    </div>
</div>