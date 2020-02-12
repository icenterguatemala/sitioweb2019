<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$output = '';
$error = '';
$msg = '';

if ( isset( $_GET['msg'] ) ) {
  $msg = $_GET['msg'];
}


/*
* get extensions list
*/
function wpc_get_extensions() {
    global $wpdb;

    $extensions = WPC()->get_wpc_transient_option( 'wpc_extensions' );

    if ( empty( $extensions ) ) {

        $extensions = array();

        if ( defined('DOMAIN_MAPPING' ) ) {
            $url = $wpdb->get_var("SELECT option_value FROM {$wpdb->prefix}options WHERE option_name = 'siteurl'" );
        } else {
            $url = get_bloginfo( 'url' );
        }

        $license_key = get_option( 'WP-Client_license_key' );
        $license_salt = get_option( 'WP-Client_license_salt' );

        if ( !empty( $license_key ) && !empty( $license_salt ) ) {

            $postfields["action"] = 'lm_extensions_response';
            $postfields["domain"] = strtolower( urlencode( rtrim( $url, "/" ) ) );
            $postfields['license_key'] = $license_key;
            $postfields['license_salt'] = $license_salt;
            $postfields = http_build_query( $postfields );

            $response = wp_remote_post( 'https://wp-client.com/wp-admin/admin-ajax.php', array(
                    'method' => 'POST',
                    'timeout' => 45,
                    'redirection' => 5,
                    'httpversion' => '1.0',
                    'blocking' => true,
                    'sslverify' => false,
                    'headers' => array(),
                    'body' => $postfields,
                    'cookies' => array()
                )
            );

        }

        if ( is_wp_error( $response ) ) {
            echo $response->get_error_message();
            exit;
        } else {
            $data = $response;
        }

        if ( !$data['body'] ) {
            die( 'Error 1010101' );
        }

        $answer = json_decode( $data['body'] );

        if ( isset( $answer->success ) && $answer->success ) {
            $extensions = unserialize( $answer->extensions );
        }

        WPC()->set_wpc_transient_option( 'wpc_extensions', $extensions, 3600*24 );
    }

    return $extensions;
}


/*
* make extensions actions
*/
function wpc_extensions_actions( $extensions ) {
    $n      = '';
    $extension  = $_GET['extension'];

    if ( wp_verify_nonce( $_GET['_wpnonce'], 'wpc_extension_' . $_GET['action'] . $extension . get_current_user_id() ) ) {
        switch( $_GET['action'] ) {
            case 'activate':
                if ( !is_plugin_active( $extension ) ) {
                    $result = activate_plugin( $extension );
                    if ( is_wp_error( $result ) ) {
                        if ( 'unexpected_output' == $result->get_error_code() ) {
                            $error = $result->get_error_data();
                        } else {
                            $error = $result;
                        }
                        echo $error;
                    } else {
                        WPC()->redirect( get_admin_url(). 'admin.php?page=wpclients_extensions&msg=a' );
                    }
                } else {
                    $n = 'na';
                }
                break;

            case 'deactivate':
                if ( is_plugin_active( $extension ) ) {
                    deactivate_plugins( $extension );
                    WPC()->redirect( get_admin_url(). 'admin.php?page=wpclients_extensions&msg=d' );
                    exit;
                } else {
                    $n = 'nd';
                }
                break;


            case 'install':
                if ( isset( $extensions[$extension]['download_link'] ) ) {
                    include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';


                    $args = array(
                        'license' => $extensions[$extension]['api_key'],
                        'salt' => '',
                        'item_name' => $extensions[$extension]['item_name'],
                    );

                    $extensions[$extension]['download_link'] = WPC_License::extend_download_url( $extensions[$extension]['download_link'], $args ) ;

                    if ( empty( $extensions[$extension]['download_link'] ) ) {
                        die( 'Error 101010122' );
                        exit;
                    }


                    ?>

                    <div id="message3" class="updated wpc_notice fade">
                    <?php
                        $upgrader = new Plugin_Upgrader();
                        $result = $upgrader->install( $extensions[$extension]['download_link'] );
                    ?>
                    </div>

                    <script type="text/javascript">
                        jQuery( document ).ready( function() {
                            jQuery( '#message3 .icon32' ).remove();
                            jQuery( '#message3 p:first' ).remove();

                        });
                    </script>

                    <?php
                    if ( $result ) {
                        $result = activate_plugin( $extension );
                        if ( is_wp_error( $result ) ) {
                            if ( 'unexpected_output' == $result->get_error_code() ) {
                                $error = $result->get_error_data();
                            } else {
                                $error = $result;
                            }
                            echo $error;
                        } else {
                            WPC()->redirect( get_admin_url(). 'admin.php?page=wpclients_extensions&msg=a' );
                        }
                    }

                    //exit for show form of FTP access or errors
                    exit;

                }
                break;

            case 'update':
                $current = get_site_transient( 'update_plugins' );
                if ( isset( $current->response[ $extension ] ) ) {

                    $activate = ( is_plugin_active( $extension ) ) ? true : false;

                    deactivate_plugins( $extension );

                    include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

                    ?>

                    <div id="message3" class="updated wpc_notice fade">
                    <?php
                        $upgrader = new Plugin_Upgrader();
                        $upgrader->upgrade( $extension );
                    ?>
                    </div>

                    <script type="text/javascript">
                        jQuery( document ).ready( function() {
                            jQuery( '#message3 .icon32' ).remove();
                            jQuery( '#message3 p:first' ).remove();
                        });
                    </script>

                    <?php

                }

                if ( $activate ) {
                    $result = activate_plugin( $extension );
                    if ( is_wp_error( $result ) ) {
                        if ( 'unexpected_output' == $result->get_error_code() ) {
                            $n = $result->get_error_data();
                        } else {
                            $n = $result;
                        }
                    }
                }

                break;
        }

        WPC()->redirect( get_admin_url(). 'admin.php?page=wpclients_extensions&msg=' . $n );
    } else {
        WPC()->redirect( get_admin_url(). 'admin.php?page=wpclients_extensions' );
    }
}


//Get Extensions from Site/DB
$extensions = wpc_get_extensions();


//set old keys to mark new extensions
$old_extensions_keys = get_option( 'wpc_extensions_old_keys', array() );
update_option( 'wpc_extensions_old_keys', array_keys( $extensions ) );


if ( isset( $_GET['action'] ) && isset( $_GET['extension'] ) && '' != $_GET['extension'] ) {
    wpc_extensions_actions( $extensions );
}

?>

<div class='wrap'>

    <?php echo WPC()->admin()->get_plugin_logo_block() ?>

    <div class="wpc_clear"></div>

    <div id="message" class="updated wpc_notice fade" <?php echo ( isset( $_GET['msg'] ) && ( 't' == $_GET['msg'] || 'f' == $_GET['msg'] ) ) ? '' : ' style="display: none;"'; ?>>
        <p>
            <?php
                if( isset( $_GET['msg'] ) && 't' == $_GET['msg'] ) {
                    _e( 'Import was successful', WPC_CLIENT_TEXT_DOMAIN );
                } elseif( isset( $_GET['msg'] ) && 'f' == $_GET['msg'] ){
                    _e( 'Invalid *.xml file', WPC_CLIENT_TEXT_DOMAIN );
                }
            ?>
        </p>
    </div>

    <div class="icon32" id="icon-options-general"></div>
    <h2><?php printf( __( '%s Extensions', WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ) ?></h2>

    <p>
        <?php printf( __( '%s uses Extensions to expand the functionality of the plugin. These can be installed/activated as you have the need for them.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ) ?>
        <?php _e( "To begin the installation, click Install. You will then need to Activate the Extension, and enter the Extension's unique API Key.", WPC_CLIENT_TEXT_DOMAIN ) ?>
        <b><?php _e( 'Note: New extensions may display in 24 hours after purchases!', WPC_CLIENT_TEXT_DOMAIN ) ?></b>
    </p>



<?php if ( '' != $error) { ?>
    <div id="message" class="error wpc_notice fade"><p><b><?php _e( 'The Extension generated unexpected output:', WPC_CLIENT_TEXT_DOMAIN ) ?></b></p><p><?php echo $error ?></p></div>
<?php } ?>


<?php if ( '' != $msg ) { ?>
    <div id="message" class="updated wpc_notice fade">
        <p>
        <?php
            switch( $msg ) {
                case 'a':
                    echo  __( 'Extension activated.', WPC_CLIENT_TEXT_DOMAIN );
                    break;
                case 'na':
                    echo __( 'Extension not activated.', WPC_CLIENT_TEXT_DOMAIN );
                    break;
                case 'd':
                    echo __( 'Extension deactivated.', WPC_CLIENT_TEXT_DOMAIN );
                    break;
                case 'nd':
                    echo __( 'Extension not deactivated', WPC_CLIENT_TEXT_DOMAIN );
                    break;
            }
        ?>
        </p>
    </div>
<?php } ?>

    <form method="post" action="" class="wpc_extensions">
        <table cellspacing="0" class="widefat fixed">
            <thead>
            <tr>
                <th class="manage-column column-c" scope="col" width="10">&nbsp;</th>
                <th class="manage-column column-name" scope="col"><?php _e( 'Extension Name',  WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                <th class="manage-column column-name" scope="col" width="700"><?php _e( 'Description',  WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                <th class="manage-column column-active" scope="col"><?php _e( 'Active',  WPC_CLIENT_TEXT_DOMAIN ) ?></th>
            </tr>
            </thead>

            <tfoot>
            <tr>
                <th class="manage-column column-c" scope="col">&nbsp;</th>
                <th class="manage-column column-name" scope="col"><?php _e( 'Extension Name',  WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                <th class="manage-column column-name" scope="col"><?php _e( 'Description',  WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                <th class="manage-column column-active" scope="col"><?php _e( 'Active',  WPC_CLIENT_TEXT_DOMAIN ) ?></th>
            </tr>
            </tfoot>
            <tbody>
                <?php
                if ( isset( $extensions ) && count( $extensions ) ) {
                    $update_plugins = get_site_transient( 'update_plugins' );

                    foreach( $extensions as $key => $extension ) {

                        if ( empty( $extension['title'] ) ) {
                            continue;
                        }

                        $active = ( is_plugin_active( $key ) ) ? true : false;
                        $download = ( !file_exists( WP_PLUGIN_DIR . '/' . $key ) ) ? true : false;
                        $update = ( isset( $update_plugins->response[$key] ) ) ? true : false;

                        $paid = $extension['can_install']; ?>

                        <tr valign="middle" class="alternate" id="plugin-<?php echo $key ?>">
                            <td class="column-c" valign="bottom">
                                <input type="checkbox" value="" disabled <?php echo ( $active ) ? 'checked' : '' ?>  />
                            </td>
                            <td class="column-name">
                                <?php if( empty( $old_extensions_keys ) || !in_array( $key, $old_extensions_keys ) ) {
                                    echo '<span style="color:#d54e21;font-weight: bold;margin-right: 5px;float:left;display:block;">' . __( 'NEW', WPC_CLIENT_TEXT_DOMAIN ). '</span>';
                                } ?>

                                <?php echo '<strong style="float:left;display:block;">' . esc_html( $extension['title'] ) . '</strong>' ?>

                                <div class="actions" style="float: left;width:100%;">
                                <?php if ( $paid && $download ) { ?>
                                    <span class="edit install">
                                        <a href="admin.php?page=wpclients_extensions&action=install&extension=<?php echo $key ?>&_wpnonce=<?php echo wp_create_nonce( 'wpc_extension_install' .  $key . get_current_user_id() ) ?>"> <?php _e( 'Install',  WPC_CLIENT_TEXT_DOMAIN ) ?></a>
                                    </span>
                                <?php } elseif( !$paid && isset( $extension['details_link'] ) && !empty( $extension['details_link'] ) ) { ?>
                                    <span class="edit details">
                                        <a target="_blank" href="<?php echo $extension['details_link'] ?>"> <?php _e( 'Details', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
                                    </span>
                                <?php } elseif( $paid ) { ?>

                                    <?php if ( $active ) { ?>
                                        <span class="edit deactivate">
                                            <a href="admin.php?page=wpclients_extensions&action=deactivate&extension=<?php echo $key ?>&_wpnonce=<?php echo wp_create_nonce( 'wpc_extension_deactivate' .  $key . get_current_user_id() ) ?>"> <?php _e( 'Deactivate',  WPC_CLIENT_TEXT_DOMAIN ) ?></a>
                                        </span>
                                    <?php } else { ?>
                                        <span class="edit activate">
                                            <a href="admin.php?page=wpclients_extensions&action=activate&extension=<?php echo $key ?>&_wpnonce=<?php echo wp_create_nonce( 'wpc_extension_activate' .  $key . get_current_user_id() ) ?>"> <?php _e( 'Activate',  WPC_CLIENT_TEXT_DOMAIN ) ?></a>
                                        </span>
                                    <?php } ?>

                                    <?php if ( $update ) { ?>
                                        <span class="edit update">
                                            | <a href="admin.php?page=wpclients_extensions&action=update&extension=<?php echo $key ?>&_wpnonce=<?php echo wp_create_nonce( 'wpc_extension_update' .  $key . get_current_user_id() ) ?>"> <?php _e( 'Update',  WPC_CLIENT_TEXT_DOMAIN ) ?></a>
                                        </span>
                                    <?php } ?>

                                <?php } ?>
                                </div>

                            </td>
                            <td class="column-c" valign="bottom" align="justify">
                                <div class="wpc_extension_description">
                                    <?php
                                    if ( !empty( $extension['description'] ) ) {
                                        echo esc_html( $extension['description'] );
                                    }
                                    if( $extension['can_install'] ) { ?>
                                    <br />
                                    <br />
                                    <strong><?php _e( 'API Key:', WPC_CLIENT_TEXT_DOMAIN ) ?></strong>
                                    <?php echo $extension['api_key'] ?>
                                    <br />
                                    <br />
                                    <?php } ?>
                                </div>
                            </td>

                            <td class="column-active">
                                <?php
                                    if ( $active ) {
                                        echo "<strong>" . __( 'Active',  WPC_CLIENT_TEXT_DOMAIN ) . "</strong>";
                                    } else {
                                        _e( 'Inactive',  WPC_CLIENT_TEXT_DOMAIN );
                                    }
                                ?>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr valign="middle" class="alternate" >
                        <td colspan="4" scope="row" align="center"><?php _e( 'No Extensions were found for this install.', WPC_CLIENT_TEXT_DOMAIN ); ?></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </form>

</div>