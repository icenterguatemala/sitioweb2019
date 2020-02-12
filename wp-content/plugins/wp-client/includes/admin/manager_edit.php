<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( WPC()->flags['easy_mode'] ) {
    WPC()->redirect( admin_url( 'admin.php?page=wpclient_clients' ) );
}

// No caps
if ( !( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ) {
    WPC()->redirect( get_admin_url() . 'admin.php?page=wpclient_clients&tab=managers' );
}

global $wpdb;
$error = "";
//save user data
if ( isset( $_REQUEST['update_user'] ) ) {
	// validate at php side

    //empty username
    if ( empty( $_REQUEST['manager_data']['user_login'] ) )
            $error .= __( 'A username is required.<br/>', WPC_CLIENT_TEXT_DOMAIN );

    if ( !isset( $_REQUEST['manager_data']['ID'] ) ) {
    //already exsits user name
	if ( username_exists( $_REQUEST['manager_data']['user_login'] ) )
		$error .= __( 'Sorry, that username already exists!<br/>', WPC_CLIENT_TEXT_DOMAIN );
    }

    // email already exists
    $contact_email = apply_filters( 'pre_user_email', isset( $_REQUEST['manager_data']['email'] ) ? $_REQUEST['manager_data']['email'] : '' );
    if ( email_exists( $contact_email ) ) {
        if ( !isset( $_REQUEST['manager_data']['ID'] ) || $_REQUEST['manager_data']['ID'] != get_user_by( 'email',  $contact_email )->ID ) {
            // email already exist
            $error .= __( 'Email address already in use.<br/>', WPC_CLIENT_TEXT_DOMAIN );
        }
    }

    if ( !isset( $_REQUEST['manager_data']['ID'] ) ) {
        if ( empty( $_REQUEST['manager_data']['pass1'] ) || empty( $_REQUEST['manager_data']['pass2'] ) ) {
        if ( empty( $_REQUEST['manager_data']['pass1'] ) ) // password
            $error .= __( 'Sorry, password is required.<br/>', WPC_CLIENT_TEXT_DOMAIN );
        elseif ( empty( $_REQUEST['manager_data']['pass2'] ) ) // confirm password
            $error .= __( 'Sorry, confirm password is required.<br/>', WPC_CLIENT_TEXT_DOMAIN );
        elseif ( $_REQUEST['manager_data']['pass1'] != $_REQUEST['manager_data']['pass2'] )
            $error .= __( 'Sorry, Passwords are not matched! .<br/>', WPC_CLIENT_TEXT_DOMAIN );
        }
    }


    if ( empty( $error ) ) {

        $userdata = array(
            'user_pass'     => WPC()->prepare_password( $_REQUEST['manager_data']['pass2'] ),
            'user_login'    => esc_attr( $_REQUEST['manager_data']['user_login'] ),
            'user_email'    => $_REQUEST['manager_data']['email'],
            'first_name'    => esc_attr( $_REQUEST['manager_data']['first_name'] ),
            'last_name'     => esc_attr( $_REQUEST['manager_data']['last_name'] ),
            'display_name'  => esc_attr( $_REQUEST['manager_data']['first_name'] . ' ' . $_REQUEST['manager_data']['last_name'] ),
            'send_password' => ( isset( $_REQUEST['manager_data']['send_password'] ) ) ? esc_attr( $_REQUEST['manager_data']['send_password'] ) : '',
            'temp_password' => !empty( $_REQUEST['manager_data']['temp_password'] ) ? 1 : '',
            'avatar'        => esc_attr( $_REQUEST['manager_data']['avatar'] ),
        );

        if ( isset( $_REQUEST['manager_data']['ID'] ) ) {
            $userdata['ID'] = $_REQUEST['manager_data']['ID'];
        } else {
            $userdata['role'] = 'wpc_manager';
        }

        $auto_assigned = ( isset( $_REQUEST['manager_data']['auto_assign'] ) && 'on' == $_REQUEST['manager_data']['auto_assign'] ) ? true : false;

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
            //insert new manager
            $manager_id = wp_insert_user( $userdata );

            if ( '1' == $userdata['send_password'] ) {

                $args = array(
                    'client_id' => $manager_id,
                    'user_password' => $userdata['user_pass']
                );

                //send email
                WPC()->mail( 'manager_created', $userdata['user_email'], $args, 'manager_created' );
            }

            update_user_meta( $manager_id, 'wpc_auto_assigned_clients', $auto_assigned );
            update_user_meta( $manager_id, 'contact_phone', $_POST['manager_data']['contact_phone'] );
            update_user_meta( $manager_id, 'address', $_POST['manager_data']['address'] );

        } else {
            //update manager data
            if( empty( $userdata['user_pass'] ) ) {
                unset( $userdata['user_pass'] );
            }

            add_filter( 'send_password_change_email', function() { return false; } );

            wp_update_user( $userdata );
            $manager_id = $userdata['ID'];

            if ( '1' == $userdata['send_password'] ) {
                $args = array(
                    'client_id' => $manager_id,
                    'user_password' => $userdata['user_pass']
                );

                //send email
                WPC()->mail( 'manager_updated', $userdata['user_email'], $args, 'manager_updated' );
            }

            update_user_meta( $manager_id, 'wpc_auto_assigned_clients', $auto_assigned );
            update_user_meta( $manager_id, 'contact_phone', $_POST['manager_data']['contact_phone'] );
            update_user_meta( $manager_id, 'address', $_POST['manager_data']['address'] );
        }


        if( $temp_password ) {
            WPC()->members()->set_temp_password( $manager_id );
        }

        //assign process
        $client_ids = array();
        if ( isset( $_REQUEST['wpc_clients'] ) && ( 'managers_add' == $_GET['tab'] || 'managers_edit' == $_GET['tab'] ) ) {
            if( $_REQUEST['wpc_clients'] == 'all' ) {
                $client_ids = WPC()->members()->get_client_ids();
            } elseif( '' != $_REQUEST['wpc_clients'] ) {
                $client_ids = explode( ',', $_REQUEST['wpc_clients'] );
            }

        }

        WPC()->assigns()->set_assigned_data( 'manager', $manager_id, 'client', $client_ids );

        $circle_ids = array();
        if ( isset( $_REQUEST['wpc_circles'] ) && ( 'managers_add' == $_GET['tab'] || 'managers_edit' == $_GET['tab'] ) ) {
            if( $_REQUEST['wpc_circles'] == 'all' ) {
                $circle_ids = WPC()->groups()->get_group_ids();
            } elseif ('' != $_REQUEST['wpc_circles'] ) {
                $circle_ids = explode( ',', $_REQUEST['wpc_circles'] );
            }
        }

        WPC()->assigns()->set_assigned_data( 'manager', $manager_id, 'circle', $circle_ids );

        //for assign avatar
        if( isset( $userdata['avatar'] ) && !empty( $userdata['avatar'] ) && strpos( $userdata['avatar'], 'temp_' ) === 0 ) {
            $avatars_dir = WPC()->get_upload_dir( 'wpclient/_avatars/', 'allow' );

            if( file_exists( $avatars_dir . $userdata['avatar'] ) ) {

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

                            if( strpos( $file, md5( $manager_id . 'wpc_avatar' ) ) === 0 ) {
                                unlink( $avatars_dir . DIRECTORY_SEPARATOR . $file );
                            }
                        }
                    }
                }

                //rename avatar from temp and save in user meta
                $fileinfo = pathinfo( $avatars_dir . $userdata['avatar'] );

                $avatar_file = md5( $manager_id . 'wpc_avatar' ) . time() . '.' . $fileinfo['extension'];
                rename( $avatars_dir . $userdata['avatar'] , $avatars_dir . $avatar_file );
                update_user_meta( $manager_id, 'wpc_avatar', $avatar_file );
            }
        }

        /*our_hook_
            hook_name: wpc_client_manager_data_saved
            hook_title: Manager Data Saved
            hook_description: Can be used for saving custom fields on Manager Add/Edit Form.
            hook_type: action
            hook_in: wp-client
            hook_location manager_edit.php
            hook_param: int $manager_id, array $manager_data
            hook_since: 3.4.8
        */
        do_action( 'wpc_client_manager_data_saved', $manager_id, $userdata );


        //redirects
        if ( isset( $_REQUEST['manager_data']['ID'] ) ) {

            /*our_hook_
                hook_name: wpc_client_manager_updated
                hook_title: Manager Updated
                hook_description: Hook runs when Manager account is updated.
                hook_type: action
                hook_in: wp-client
                hook_location manager_edit.php
                hook_param: int $manager_id, array $manager_data
                hook_since: 3.4.8
            */
            do_action( 'wpc_client_manager_updated', $manager_id, $userdata );

            //redirect
            WPC()->redirect( 'admin.php?page=wpclient_clients&tab=managers&msg=u' );
        } else {
            /*our_hook_
                hook_name: wpc_client_manager_created
                hook_title: Manager Created
                hook_description: Hook runs when Manager account is created.
                hook_type: action
                hook_in: wp-client
                hook_location manager_edit.php
                hook_param: int $manager_id
                hook_since: 3.4.8
            */
            do_action( 'wpc_client_manager_created', $manager_id );

            //redirect
            WPC()->redirect( 'admin.php?page=wpclient_clients&tab=managers&msg=a' );
        }

	}
}

$manager_data = array();

//get manager data
if ( isset( $_REQUEST['manager_data'] ) ) {
    $manager_data = $_REQUEST['manager_data'];
} elseif ( 'managers_edit' == $_GET['tab'] ) {
    $manager = get_userdata( $_GET['id'] );
    $manager_data['ID']             = $manager->data->ID;
    $manager_data['user_login']     = $manager->data->user_login;
    $manager_data['email']          = $manager->data->user_email;
    $manager_data['first_name']     = get_user_meta( $manager->data->ID, 'first_name', true );
    $manager_data['last_name']      = get_user_meta( $manager->data->ID, 'last_name', true );
    $manager_data['contact_phone']  = get_user_meta( $manager->data->ID, 'contact_phone', true );
    $manager_data['address']        = get_user_meta( $manager->data->ID, 'address', true );
    $manager_data['auto_assign']    = get_user_meta( $manager->data->ID, 'wpc_auto_assigned_clients', true );
    $manager_data['avatar']         = get_user_meta( $manager->data->ID, 'wpc_avatar', true );
}

//get assigned clients for manager
$clients_this_manager = ( isset( $manager_data['ID'] ) ) ? WPC()->assigns()->get_assign_data_by_object( 'manager', $manager_data['ID'], 'client' ) : array();
$circles_this_manager = ( isset( $manager_data['ID'] ) ) ? WPC()->assigns()->get_assign_data_by_object( 'manager', $manager_data['ID'], 'circle' ) : array();

//get all clients
$args = array(
    'role'      => 'wpc_client',
    'orderby'   => 'user_login',
    'order'     => 'ASC',
    'fields'    => array( 'ID', 'user_login' ),

);

$clients = get_users( $args ); ?>

<div class="wrap">

    <?php echo WPC()->admin()->get_plugin_logo_block() ?>

    <div class="wpc_clear"></div>

    <div id="wpc_container">

        <?php echo WPC()->admin()->gen_tabs_menu( 'clients' ) ?>

        <span class="wpc_clear"></span>

        <div class="wpc_tab_container_block">

            <h2><?php echo ( 'managers_add' == $_GET['tab'] ) ? sprintf( __( 'Add new %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['s'] ) : sprintf( __( 'Edit %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['s'] ); ?></h2>

            <div id="message" class="updated wpc_notice fade" <?php echo ( empty( $error ) ) ? 'style="display: none;" ' : '' ?> ><?php echo $error; ?></div>

            <form name="edit_manager" id="edit_manager" method="post" >
                <?php if( 'managers_edit' == $_GET['tab'] ) { ?>
                    <input type="hidden" name="manager_data[ID]" value="<?php echo ( isset( $manager_data['ID'] ) ) ? $manager_data['ID'] : ''  ?>" />
                    <input type="hidden" name="manager_data[user_login]" value="<?php echo ( isset( $manager_data['user_login'] ) ) ? $manager_data['user_login'] : ''  ?>" />
                <?php } ?>

                <div class="avatar_wrapper">
                    <?php if( 'managers_add' == $_GET['tab'] ) {
                        echo WPC()->members()->build_avatar_field( 'manager_data[avatar]' );
                    } else {
                        echo WPC()->members()->build_avatar_field( 'manager_data[avatar]', $manager_data['avatar'], $manager_data['ID'] );
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
                                    <?php if ( 'managers_add' == $_GET['tab'] ) { ?>
                                        <input type="text" name="manager_data[user_login]" id="user_login" value="<?php echo ( isset( $manager_data['user_login'] ) ) ? $manager_data['user_login'] : ''  ?>" />
                                    <?php } else { ?>
                                        <input type="text" disabled id="user_login" value="<?php echo ( isset( $manager_data['user_login'] ) ) ? $manager_data['user_login'] : ''  ?>" />
                                    <?php } ?>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="email"><?php _e( 'E-mail', WPC_CLIENT_TEXT_DOMAIN ) ?> <span class="description"><?php _e( '(required)', WPC_CLIENT_TEXT_DOMAIN ) ?></span></label>
                                </th>
                                <td>
                                    <input type="text" name="manager_data[email]" id="email" value="<?php echo ( isset( $manager_data['email'] ) ) ? $manager_data['email'] : ''  ?>" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="first_name"><?php _e( 'First Name', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </th>
                                <td>
                                    <input type="text" name="manager_data[first_name]" id="first_name" value="<?php echo ( isset( $manager_data['first_name'] ) ) ? $manager_data['first_name'] : ''  ?>" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="last_name"><?php _e( 'Last Name', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </th>
                                <td>
                                    <input type="text" name="manager_data[last_name]" id="last_name" value="<?php echo ( isset( $manager_data['last_name'] ) ) ? $manager_data['last_name'] : ''  ?>" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="contact_phone"><?php _e( 'Phone', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </th>
                                <td>
                                    <input type="text" name="manager_data[contact_phone]" id="contact_phone" value="<?php echo ( isset( $manager_data['contact_phone'] ) ) ? $manager_data['contact_phone'] : '' ; ?>" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="address"><?php _e( 'Address', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </th>
                                <td>
                                    <input type="text" name="manager_data[address]" id="address" value="<?php echo ( isset( $manager_data['address'] ) ) ? $manager_data['address'] : '' ; ?>" />
                                </td>
                            </tr>

                            <?php
                            /*our_hook_
                                hook_name: wpc_client_manager_add_edit_form_html
                                hook_title: Manager Add/Edit Form
                                hook_description: Can be used for adding custom fields on Manager Add/Edit Form.
                                hook_type: action
                                hook_in: wp-client
                                hook_location manager_edit.php
                                hook_param: array $manager_data
                                hook_since: 3.4.8
                            */
                            do_action( 'wpc_client_manager_add_edit_form_html', $manager_data ); ?>

                            <tr>
                                <th scope="row">
                                    <label for="wpclients_managers"><?php printf( '%s %s', WPC()->custom_titles['manager']['s'], WPC()->custom_titles['client']['p'] ) ?></label>
                                </th>
                                <td>
                                    <?php if ( 'managers_add' == $_GET['tab'] ) {
                                        $link_array = array(
                                            'title'   => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                                            'text'    => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] )
                                        );
                                        $input_array = array(
                                            'name'  => 'wpc_clients',
                                            'id'    => 'wpc_clients',
                                            'value' => ''
                                        );
                                        $additional_array = array(
                                            'counter_value' => 0
                                        );
                                        WPC()->assigns()->assign_popup('client', 'wpclients_managers', $link_array, $input_array, $additional_array );
                                    } elseif( isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) ) {
                                        $link_array = array(
                                            'title'   => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                                            'text'    => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] )
                                        );
                                        $input_array = array(
                                            'name'  => 'wpc_clients',
                                            'id'    => 'wpc_clients',
                                            'value' => ( ( isset( $clients_this_manager ) && is_array( $clients_this_manager ) ) ) ? implode( ',', $clients_this_manager ) : ''
                                        );
                                        $additional_array = array(
                                            'counter_value' => count( $clients_this_manager )
                                        );
                                        WPC()->assigns()->assign_popup('client', 'wpclients_managers', $link_array, $input_array, $additional_array );
                                    } ?>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="wpclients_managers"><?php printf( '%s %s %s', WPC()->custom_titles['manager']['s'], WPC()->custom_titles['client']['s'], WPC()->custom_titles['circle']['p'] ) ?>: </label>
                                </th>
                                <td>
                                    <?php if ( 'managers_add' == $_GET['tab'] ) {
                                        $link_array = array(
                                            'title'   => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'] ),
                                            'text'    => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] )
                                        );
                                        $input_array = array(
                                            'name'  => 'wpc_circles',
                                            'id'    => 'wpc_circles',
                                            'value' => ''
                                        );
                                        $additional_array = array(
                                            'counter_value' => 0
                                        );
                                        WPC()->assigns()->assign_popup('circle', 'wpclients_managers', $link_array, $input_array, $additional_array );
                                    } elseif( isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) ) {
                                        $link_array = array(
                                            'title'   => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'] ),
                                            'text'    => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] )
                                        );
                                        $input_array = array(
                                            'name'  => 'wpc_circles',
                                            'id'    => 'wpc_circles',
                                            'value' => implode( ',', $circles_this_manager )
                                        );
                                        $additional_array = array(
                                            'counter_value' => count( $circles_this_manager )
                                        );
                                        WPC()->assigns()->assign_popup('circle', 'wpclients_managers', $link_array, $input_array, $additional_array );
                                    } ?>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"></th>
                                <td>
                                    <label for="auto_assign"><input type="checkbox" name="manager_data[auto_assign]" id="auto_assign" <?php if( isset( $manager_data['auto_assign'] ) && '1' == $manager_data['auto_assign'] ) {?>checked="checked"<?php } ?>/>
                                        <?php printf( __( 'Auto-assign new %s to this WPC-Manager', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) ?></label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="pass1"><?php _e( 'Password', WPC_CLIENT_TEXT_DOMAIN ) ?> <span class="description"><?php _e( '(twice, required)', WPC_CLIENT_TEXT_DOMAIN ) ?></span></label>
                                </th>
                                <td>
                                    <input type="password" name="manager_data[pass1]" autocomplete="off" id="pass1" value="" />
                                    <br />
                                    <input type="password" name="manager_data[pass2]" autocomplete="off" id="pass2" value="" />
                                    <br />
                                    <br />
                                    <input type="button" class="wpc_generate_password_button button" value="<?php _e( 'Generate Password', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                                    <br />
                                    <br />
                                    <div id="pass-strength-result" style="display: block;"><?php _e( 'Strength indicator', WPC_CLIENT_TEXT_DOMAIN ) ?></div>
                                    <p class="description indicator-hint"><?php _e( 'Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers and symbols like ! " ? $ % ^ &amp; ).', WPC_CLIENT_TEXT_DOMAIN ) ?></p>
                                </td>
                            </tr>
                            <tr <?php if( 'managers_edit' == $_GET['tab'] ) { ?>style="display: none;"<?php } ?>>
                                <th scope="row"></th>
                                <td>
                                    <label for="temp_password"><input type="checkbox" name="manager_data[temp_password]" value="1" id="temp_password" <?php if( 'managers_edit' == $_GET['tab'] ) { ?>disabled="disabled"<?php } ?> /> <?php _e( 'Mark password as temporary.', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                            </tr>
                            <tr <?php if( 'managers_edit' == $_GET['tab'] ) { ?>style="display: none;"<?php } ?>>
                                <th scope="row"></th>
                                <td>
                                    <label for="send_password"><input type="checkbox" name="manager_data[send_password]" id="send_password" value="1" checked="checked" <?php if( 'managers_edit' == $_GET['tab'] ) { ?>disabled="disabled"<?php } ?> /> <?php _e( 'Send this password to the new user by email.', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <p class="submit">
                        <input type="submit" value="<?php echo ( 'managers_add' == $_GET['tab'] ) ? __( 'Save', WPC_CLIENT_TEXT_DOMAIN ) : __( 'Update', WPC_CLIENT_TEXT_DOMAIN ) ?>" class="button-primary" id="update_user" name="update_user">
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript" language="javascript">
    jQuery( document ).ready( function( $ ) {
	    <?php echo ( empty( $error ) )? '$( "#message" ).hide();' : '' ?>

        <?php if( 'managers_edit' == $_GET['tab'] ) { ?>
            jQuery( '#pass1' ).change( function() {
                if( jQuery( '#pass1' ).val() != '' ) {
                    jQuery( '#send_password' ).attr( 'disabled', false ).parents('tr').show();
                    jQuery( '#temp_password' ).attr( 'disabled', false ).parents('tr').show();
                }  else {
                    jQuery( '#send_password' ).attr( 'disabled', true ).parents('tr').hide();
                    jQuery( '#temp_password' ).attr( 'disabled', true ).parents('tr').hide();
                }
            });
        <?php } ?>

        wpc_generate_password( '#pass1', '#pass2' );

        //Select/Un-select all clients
        jQuery( "#select_all" ).change( function() {
            if ( 'checked' == jQuery( this ).attr( 'checked' ) ) {
                jQuery( '#edit_manager input[name="manager_data[clients_id][]"]' ).attr( 'checked', true );
            } else {
                jQuery( '#edit_manager input[name="manager_data[clients_id][]"]' ).attr( 'checked', false );
            }
        });

        $( "#update_user" ).live( 'click', function() {

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

            <?php if( 'managers_add' == $_GET['tab'] ) { ?>
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