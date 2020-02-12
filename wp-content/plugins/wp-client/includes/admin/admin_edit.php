<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( WPC()->flags['easy_mode'] ) {
    WPC()->redirect( admin_url( 'admin.php?page=wpclient_clients' ) );
}

//check auth
if ( ! current_user_can( 'administrator' ) ) {
    WPC()->redirect( get_admin_url() . 'admin.php?page=wpclient_clients&tab=admins' );
}

global $wpdb;
$error = "";
//save user data
if ( isset( $_REQUEST['update_user'] ) ) {
    // validate at php side

    //empty username
    if ( empty( $_REQUEST['admin_data']['user_login'] ) )
        $error .= __( 'A username is required.<br/>', WPC_CLIENT_TEXT_DOMAIN );

    if ( !isset( $_REQUEST['admin_data']['ID'] ) ) {
        //already exsits user name
        if ( username_exists( $_REQUEST['admin_data']['user_login'] ) )
            $error .= __( 'Sorry, that username already exists!<br/>', WPC_CLIENT_TEXT_DOMAIN );
    }

    $user_email = apply_filters( 'pre_user_email', isset( $_REQUEST['admin_data']['email'] ) ? $_REQUEST['admin_data']['email'] : '' );
    // email already exists
    if ( email_exists( $user_email ) ) {
        if ( !isset( $_REQUEST['admin_data']['ID'] ) || $_REQUEST['admin_data']['ID'] != get_user_by( 'email',  $user_email )->ID ) {
            // email already exist
            $error .= __( 'Email address already in use.<br/>', WPC_CLIENT_TEXT_DOMAIN );
        }
    }


    if( !isset( $_REQUEST['admin_data']['ID'] ) || ( isset( $_REQUEST['admin_data']['ID'] ) && !empty( $_REQUEST['admin_data']['pass1'] ) ) ) {
        if( empty( $_REQUEST['admin_data']['pass1'] ) || empty( $_REQUEST['admin_data']['pass2'] ) ) {
            if( empty( $_REQUEST['admin_data']['pass1'] ) ) // password
                $error .= __( 'Sorry, password is required.<br/>', WPC_CLIENT_TEXT_DOMAIN );
            elseif( empty( $_REQUEST['admin_data']['pass2'] ) ) // confirm password
                $error .= __( 'Sorry, confirm password is required.<br/>', WPC_CLIENT_TEXT_DOMAIN );
        } elseif( $_REQUEST['admin_data']['pass1'] != $_REQUEST['admin_data']['pass2'] ) {
            $error .= __('Sorry, Passwords are not matched! .<br/>', WPC_CLIENT_TEXT_DOMAIN);
        }
    }


    if ( empty( $error ) ) {

        $userdata = array(
            'user_pass'     => WPC()->prepare_password( $_REQUEST['admin_data']['pass2'] ),
            'user_login'    => esc_attr( $_REQUEST['admin_data']['user_login'] ),
            'user_email'    => $_REQUEST['admin_data']['email'],
            'first_name'    => esc_attr( $_REQUEST['admin_data']['first_name'] ),
            'last_name'     => esc_attr( $_REQUEST['admin_data']['last_name'] ),
            'send_password' => ( isset( $_REQUEST['admin_data']['send_password'] ) ) ? esc_attr( $_REQUEST['admin_data']['send_password'] ) : '',
            'temp_password' => !empty( $_REQUEST['admin_data']['temp_password'] ) ? 1 : '',
            'avatar'        => esc_attr( $_REQUEST['admin_data']['avatar'] ),
        );

        //unset avatar if it not changed
        if( 'admins_edit' == $_GET['tab'] ) {
            $admin          = get_userdata( $_GET['id'] );
            $current_avatar = get_user_meta( $admin->data->ID, 'wpc_avatar', true );

            if( $current_avatar == $userdata['avatar'] ) {
                unset( $userdata['avatar'] );
            }
        }

        if ( isset( $_REQUEST['admin_data']['ID'] ) ) {
            $userdata['ID'] = $_REQUEST['admin_data']['ID'];
        } else {
            $userdata['role'] = 'wpc_admin';
        }

        //temporary password
        $temp_password = false;
        if( !empty( $userdata['user_pass'] ) ) {
            if ( isset( $userdata['ID'] ) ) {
                delete_user_meta( $userdata['ID'], 'wpc_temporary_password');
            }
            if( !empty( $userdata['temp_password'] ) ) {
                $temp_password = true;
            }
            unset( $userdata['temp_password'] );
        }

        if ( !isset( $userdata['ID'] ) ) {
            //insert new admin
            $admin_id = wp_insert_user( $userdata );

            if ( '1' == $userdata['send_password'] ) {

                $args = array( 'client_id' => $admin_id, 'user_password' => $userdata['user_pass'] );

                //send email
                WPC()->mail( 'admin_created', $userdata['user_email'], $args, 'admin_created' );
            }

        } else {
            if( empty( $userdata['user_pass'] ) ) {
                unset( $userdata['user_pass'] );
            }

            add_filter( 'send_password_change_email', function() { return false; } );

            //update admin data
            wp_update_user( $userdata );
            $admin_id = $userdata['ID'];

            if ( '1' == $userdata['send_password'] ) {
                $args = array(
                    'client_id' => $admin_id,
                    'user_password' => $userdata['user_pass']
                );

                //send email
                WPC()->mail( 'manager_updated', $userdata['user_email'], $args, 'manager_updated' );
            }
        }

        if( $temp_password ) {
            WPC()->members()->set_temp_password( $admin_id );
        }

        //for assign avatar
        if( isset( $userdata['avatar'] ) && !empty( $userdata['avatar'] ) ) {
            $avatars_dir = WPC()->get_upload_dir( 'wpclient/_avatars/', 'allow' );

            if( file_exists( $avatars_dir . $userdata['avatar'] ) && strpos( $userdata['avatar'], 'temp_' ) === 0 ) {

                //delete temp files
                $files = scandir( $avatars_dir );
                $current_time = time();
                foreach( $files as $file ) {
                    if( $file != "." && $file != ".." ) {
                        if( file_exists( $avatars_dir . DIRECTORY_SEPARATOR . $file ) ) {
                            if( strpos( $file, 'temp_' ) === 0 ) {
                                $name_array = explode( '_', $file );
                                if( isset( $name_array[1] ) && is_numeric( $name_array[1] ) && ( $current_time - $name_array[1] ) > 60*60*24 ) {
                                    unlink( $avatars_dir . DIRECTORY_SEPARATOR . $file );
                                }
                            }

                            if( strpos( $file, md5( $admin_id . 'wpc_avatar' ) ) === 0 ) {
                                unlink( $avatars_dir . DIRECTORY_SEPARATOR . $file );
                            }
                        }
                    }
                }

                //rename avatar from temp and save in user meta
                $fileinfo = pathinfo( $avatars_dir . $userdata['avatar'] );

                $avatar_file = md5( $admin_id . 'wpc_avatar' ) . time() . '.' . $fileinfo['extension'];
                rename( $avatars_dir . $userdata['avatar'] , $avatars_dir . $avatar_file );
                update_user_meta( $admin_id, 'wpc_avatar', $avatar_file );
            }
        }

        //redirect
        if ( isset( $_REQUEST['admin_data']['ID'] ) ) {
            WPC()->redirect( 'admin.php?page=wpclient_clients&tab=admins&msg=u');
        } else {
            WPC()->redirect( 'admin.php?page=wpclient_clients&tab=admins&msg=a');
        }

    }
}


//get admin data
if( isset( $_REQUEST['admin_data'] ) ) {
    $admin_data = $_REQUEST['admin_data'];
} elseif ( 'admins_edit' == $_GET['tab'] ) {
    $admin = get_userdata( $_GET['id'] );

    $admin_data['ID']         = $admin->data->ID;
    $admin_data['user_login'] = $admin->data->user_login;
    $admin_data['email']      = $admin->data->user_email;
    $admin_data['first_name'] = get_user_meta( $admin->data->ID, 'first_name', true );
    $admin_data['last_name']  = get_user_meta( $admin->data->ID, 'last_name', true );
    $admin_data['avatar']     = get_user_meta( $admin->data->ID, 'wpc_avatar', true );
}

$upload_dir = wp_upload_dir();?>

<div class="wrap">

    <?php echo WPC()->admin()->get_plugin_logo_block() ?>

    <div class="wpc_clear"></div>

    <div id="wpc_container">

        <?php echo WPC()->admin()->gen_tabs_menu( 'clients' ) ?>

        <span class="wpc_clear"></span>

        <div class="wpc_tab_container_block">

            <h2><?php echo ( 'admins_add' == $_GET['tab'] ) ? sprintf( __( 'Add new %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['admin']['s'] ) : sprintf( __( 'Edit %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['admin']['s'] ); ?></h2>

            <div id="message" class="updated wpc_notice fade" <?php echo ( empty( $error ) )? 'style="display: none;" ' : '' ?> ><?php echo $error; ?></div>

            <form name="edit_admin" id="edit_admin" enctype="multipart/form-data" method="post">
                <?php if ( 'admins_edit' == $_GET['tab'] ) { ?>
                    <input type="hidden" class="admin_data_id" name="admin_data[ID]" value="<?php echo ( isset( $admin_data['ID'] ) ) ? $admin_data['ID'] : ''  ?>" />
                    <input type="hidden" name="admin_data[user_login]" value="<?php echo ( isset( $admin_data['user_login'] ) ) ? $admin_data['user_login'] : ''  ?>" />
                <?php } ?>

                <div class="avatar_wrapper">
                    <?php if( 'admins_add' == $_GET['tab'] ) {
                        echo WPC()->members()->build_avatar_field( 'admin_data[avatar]' );
                    } else {
                        echo WPC()->members()->build_avatar_field( 'admin_data[avatar]', $admin_data['avatar'], $admin_data['ID'] );
                    } ?>
                </div>
                <div class="fields_wrapper">
                    <table class="form-table" style="margin-top: 0;">
                        <tbody>
                        <tr>
                            <th scope="row">
                                <label for="user_login"><?php _e( 'Username', WPC_CLIENT_TEXT_DOMAIN ) ?> <span class="description"><?php _e( '(required)', WPC_CLIENT_TEXT_DOMAIN ) ?></span></label>
                            </th>
                            <td>
                                <?php if ( 'admins_add' == $_GET['tab'] ) { ?>
                                    <input type="text" name="admin_data[user_login]" id="user_login" value="<?php echo ( isset( $admin_data['user_login'] ) ) ? $admin_data['user_login'] : ''  ?>" />
                                <?php } else { ?>
                                    <input type="text" disabled id="user_login" value="<?php echo ( isset( $admin_data['user_login'] ) ) ? $admin_data['user_login'] : ''  ?>" />
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="email"><?php _e( 'E-mail', WPC_CLIENT_TEXT_DOMAIN ) ?> <span class="description"><?php _e( '(required)', WPC_CLIENT_TEXT_DOMAIN ) ?></span></label>
                            </th>
                            <td>
                                <input type="text" name="admin_data[email]" id="email" value="<?php echo ( isset( $admin_data['email'] ) ) ? $admin_data['email'] : ''  ?>" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="first_name"><?php _e( 'First Name', WPC_CLIENT_TEXT_DOMAIN ) ?> </label>
                            </th>
                            <td>
                                <input type="text" name="admin_data[first_name]" id="first_name" value="<?php echo ( isset( $admin_data['first_name'] ) ) ? $admin_data['first_name'] : ''  ?>" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="last_name"><?php _e( 'Last Name', WPC_CLIENT_TEXT_DOMAIN ) ?> </label>
                            </th>
                            <td>
                                <input type="text" name="admin_data[last_name]" id="last_name" value="<?php echo ( isset( $admin_data['last_name'] ) ) ? $admin_data['last_name'] : ''  ?>" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="pass1"><?php _e( 'Password', WPC_CLIENT_TEXT_DOMAIN ) ?> <span class="description"><?php _e( '(twice, required)', WPC_CLIENT_TEXT_DOMAIN ) ?></span></label>
                            </th>
                            <td>
                                <input type="password" name="admin_data[pass1]" autocomplete="off" id="pass1" value="" />
                                <br />
                                <input type="password" name="admin_data[pass2]" autocomplete="off" id="pass2" value="" />
                                <br />
                                <br />
                                <input type="button" class="wpc_generate_password_button button" value="<?php _e( 'Generate Password', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                                <br />
                                <br />
                                <div id="pass-strength-result" style="display: block;"><?php _e( 'Strength indicator', WPC_CLIENT_TEXT_DOMAIN ) ?></div>
                                <p class="description indicator-hint"><?php _e( 'Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers and symbols like ! " ? $ % ^ &amp; ).', WPC_CLIENT_TEXT_DOMAIN ) ?></p>
                            </td>
                        </tr>
                        <tr <?php if( 'admins_edit' == $_GET['tab'] ) { ?>style="display: none;"<?php } ?>>
                            <th scope="row">
                                <label for="temp_password"><?php _e( 'Temporary Password?', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                            </th>
                            <td>
                                <label for="temp_password"><input type="checkbox" name="admin_data[temp_password]" value="1" id="temp_password" <?php if( 'admins_edit' == $_GET['tab'] ) { ?>disabled="disabled"<?php } ?> /> <?php _e( 'Mark password as temporary.', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                            </td>
                        </tr>
                        <tr <?php if( 'admins_edit' == $_GET['tab'] ) { ?>style="display: none;"<?php } ?>>
                            <th scope="row">
                                <label for="send_password"><?php _e( 'Send Password?', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                            </th>
                            <td>
                                <label for="send_password"><input type="checkbox" value="1" name="admin_data[send_password]" id="send_password" checked="checked" <?php if( 'admins_edit' == $_GET['tab'] ) { ?>disabled="disabled"<?php } ?> /> <?php _e( 'Send this password to the new user by email.', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <p class="submit">
                        <input type="submit" value="<?php echo ( 'admins_add' == $_GET['tab'] ) ? __( 'Save', WPC_CLIENT_TEXT_DOMAIN ) : __( 'Update', WPC_CLIENT_TEXT_DOMAIN ) ?>" class="button-primary" id="update_user" name="update_user">
                    </p>
                </div>
            </form>
        </div>

    </div>

</div>

<script type="text/javascript" language="javascript">
    var site_url = '<?php echo site_url();?>';

    jQuery( document ).ready( function( $ ) {

        <?php echo ( empty( $error ) )? '$( "#message" ).hide();' : '' ?>

        <?php if( 'admins_edit' == $_GET['tab'] ) { ?>
            $( '#pass1' ).change( function() {
                if( $( '#pass1' ).val() != '' ) {
                    $( '#send_password' ).attr( 'disabled', false ).parents('tr').show();
                    jQuery( '#temp_password' ).attr( 'disabled', false ).parents('tr').show();
                }  else {
                    $( '#send_password' ).attr( 'disabled', true ).parents('tr').hide();
                    jQuery( '#temp_password' ).attr( 'disabled', true ).parents('tr').hide();
                }
            });
        <?php } ?>

        wpc_generate_password( '#pass1', '#pass2' );

        $( "#update_user" ).live ( 'click', function() {

            var msg = '';

            var emailReg = /^([\w-'+\.]+@([\w-]+\.)+[\w-]{2,})?$/;

            if ( $( "#user_login" ).val() == '' ) {
                msg += "<?php echo esc_js( __( 'A username is required.', WPC_CLIENT_TEXT_DOMAIN ) ) ?><br/>";
            }

            if ( $( "#email" ).val() == '' ) {
                msg += "<?php echo esc_js( __( 'Email required.', WPC_CLIENT_TEXT_DOMAIN ) ) ?><br/>";
            } else if ( !emailReg.test( $( "#email" ).val() ) ) {
                msg += "<?php echo esc_js( __( 'Invalid Email.', WPC_CLIENT_TEXT_DOMAIN ) ) ?><br/>";
            }

            <?php if( 'admins_add' == $_GET['tab'] ) { ?>
                if ( $( "#pass1" ).val() == '' ) {
                    msg += "<?php echo esc_js( __( 'Password required.', WPC_CLIENT_TEXT_DOMAIN ) ) ?><br/>";
                } else if ( $( "#pass2" ).val() == '' ) {
                    msg += "<?php echo esc_js( __( 'Confirm Password required.', WPC_CLIENT_TEXT_DOMAIN ) ) ?><br/>";
                } else if ( $( "#pass1" ).val() != $( "#pass2" ).val() ) {
                    msg += "<?php echo esc_js( __( 'Passwords are not matched.', WPC_CLIENT_TEXT_DOMAIN ) ) ?><br/>";
                }
            <?php } ?>

            if ( msg != '' ) {
                $( "#message" ).html( msg );
                $( "#message" ).show();
                return false;
            }
        });
    });

</script>

<script type="text/javascript">

    jQuery(document).ready( function( $ ) {
        $( '.indicator-hint' ).html( wpc_password_protect.hint_message );

        $( 'body' ).on( 'keyup', '#pass1, #pass2',
            function( event ) {
                $('.wpc_show_password').remove();
                checkPasswordStrength(
                    $('#pass1'),
                    $('#pass2'),
                    $('#pass-strength-result'),
                    $('#update_user'),
                    wpc_password_protect.blackList
                );
            }
        );
    });

</script>