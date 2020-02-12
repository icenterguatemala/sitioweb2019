<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

//check auth
if ( !current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' ) ) {
    WPC()->redirect( get_admin_url( 'index.php' ) );
}

if ( isset($_POST['update_templates'] ) ) {
    if ( isset( $_POST['client_template'] ) ) {
        $client_template = htmlentities( stripslashes( $_POST['client_template'] ) );
    } else {
        $client_template = '';
    }

    WPC()->settings()->update( $client_template, 'templates_clientpage' );
    WPC()->redirect( admin_url() . 'admin.php?page=wpclients_templates&tab=portal_page&msg=u' );
}


$wpc_templates_clientpage = html_entity_decode(stripslashes( WPC()->get_settings( 'templates_clientpage', '' ) ) );
?>

<style type="text/css">
    .wrap input[type=text] {
        width:400px;
    }
    .wrap input[type=password] {
        width:400px;
    }
</style>


<div class="icon32" id="icon-link-manager"></div>
<h2><?php printf( __( '%s Template', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] ) ?></h2>
<p><?php printf( __( 'From here you can edit the template of the newly created %s.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['p'] ) ?></p>

<form action="" method="post" class="wpc_portal_page_content">
    <div class="wpc_clear"></div>

    <?php wp_editor( '', 'content', array( 'wpautop' => false, 'textarea_name' => 'client_template' ) ); ?>
    <br />

    <input type='submit' name='update_templates' id="update_templates" class='button-primary' value='<?php _e( 'Update', WPC_CLIENT_TEXT_DOMAIN ) ?>' />
</form>


<script type="text/javascript" language="javascript">
    jQuery(document).ready(function() {
            jQuery.ajax({
                type: "POST",
                url: '<?php echo WPC()->get_ajax_url() ?>',
                data: {
                    action : 'get_clientpage_template_data',
                    slug : 'templates_clientpage',
                },
                dataType: "json",
                success: function( data ) {
                    if ( data.status ) {
                        jQuery( "#content" ).val( data.template );
                        var activeEditor = tinyMCE.get('content');
                        activeEditor.setContent(data.template);
                    }
                },
                error: function(data) {
                    console.log('Something is going wrong');
                }
            });
    });
</script>
