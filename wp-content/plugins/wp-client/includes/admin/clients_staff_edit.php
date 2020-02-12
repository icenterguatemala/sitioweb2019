<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( WPC()->flags['easy_mode'] ) {
    WPC()->redirect( admin_url( 'admin.php?page=wpclient_clients' ) );
}

//check auth
if ( !current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' ) && !current_user_can( 'wpc_add_staff' ) ) {
    WPC()->redirect( get_admin_url() . 'admin.php?page=wpclient_clients' );
}

global $wpdb;

$error = "";
$id_client = ( isset(  $_GET['id'] ) && 0 < $_GET['id'] ) ? $_GET['id'] : 0 ;

if ( $id_client > 0 ) {
    $all_custom_fields = WPC()->custom_fields()->get_custom_fields( 'admin_edit_staff', $id_client );
} else {
    $all_custom_fields = WPC()->custom_fields()->get_custom_fields( 'admin_add_staff', $id_client );
}

//save user data
if ( isset( $_REQUEST['update_user'] ) ) {

    // validate at php side

    //empty username
    if ( empty( $_REQUEST['user_data']['user_login'] ) )
        $error .= __( 'A username is required.<br/>', WPC_CLIENT_TEXT_DOMAIN );

    if ( !isset( $_REQUEST['user_data']['ID'] ) ) {
    //already exsits user name
    if ( username_exists( $_REQUEST['user_data']['user_login'] ) )
        $error .= __( 'Sorry, that username already exists!<br/>', WPC_CLIENT_TEXT_DOMAIN );
    }

    // email already exists
    $user_email = apply_filters( 'pre_user_email', isset( $_REQUEST['user_data']['email'] ) ? $_REQUEST['user_data']['email'] : '' );
    if ( email_exists( $user_email ) ) {
        if ( !isset( $_REQUEST['user_data']['ID'] ) || $_REQUEST['user_data']['ID'] != get_user_by( 'email', $user_email )->ID ) {
            // email already exist
            $error .= __( 'Email address already in use.<br/>', WPC_CLIENT_TEXT_DOMAIN );
        }
    }


    if( !isset( $_REQUEST['user_data']['ID'] ) || ( isset( $_REQUEST['user_data']['ID'] ) && !empty( $_REQUEST['user_data']['pass1'] ) ) ) {
        if( empty( $_REQUEST['user_data']['pass1'] ) || empty( $_REQUEST['user_data']['pass2'] ) ) {
            if( empty( $_REQUEST['user_data']['pass1'] ) ) // password
                $error .= __( 'Sorry, password is required.<br/>', WPC_CLIENT_TEXT_DOMAIN );
            elseif( empty( $_REQUEST['user_data']['pass2'] ) ) // confirm password
                $error .= __( 'Sorry, confirm password is required.<br/>', WPC_CLIENT_TEXT_DOMAIN );
        } elseif( $_REQUEST['user_data']['pass1'] != $_REQUEST['user_data']['pass2'] ) {
            $error .= __('Sorry, Passwords are not matched! .<br/>', WPC_CLIENT_TEXT_DOMAIN);
        }
    }

    $custom_fields = array();
    if ( isset( $_REQUEST['custom_fields'] ) )
        $custom_fields = $_REQUEST['custom_fields'] ;

    if( isset( $_FILES['custom_fields'] ) ) {
        $files_custom_fields = array();
        foreach( $_FILES['custom_fields'] as $key1 => $value1)
            foreach( $value1 as $key2 => $value2 )
                $files_custom_fields[$key2][$key1] = $value2;

        $custom_fields = array_merge( $custom_fields, $files_custom_fields );
    }

    if ( isset( $custom_fields ) && is_array( $custom_fields ) && is_array( $all_custom_fields ) ) {

        foreach ( $all_custom_fields as $all_key=>$all_value ) {
            if ( ( 'checkbox' == $all_value['type'] || 'radio' == $all_value['type'] || 'multiselectbox' == $all_value['type'] ) && !array_key_exists( $all_key, $custom_fields ) ) {
                $custom_fields[$all_key] = '';
            }

            foreach ( $custom_fields as $key=>$value ) {
                //if( $key == $all_key )var_dump( 111,$key, $all_key, $all_value, $value, '<br/>' );
                if ( 'file' == $all_value['type']  ) {
                    if ( $key == $all_key && isset( $all_value['required'] ) && '1' == $all_value['required'] && '' == $value['name'] ) {
                        $error .= sprintf( __( "%s is required! Please fill in the field and try again!<br/>", WPC_CLIENT_TEXT_DOMAIN), $all_custom_fields[$all_key]['title']);
                    }
                } else {
                    if ( $key == $all_key && isset( $all_value['required'] ) && '1' == $all_value['required'] && '' == $value ) {
                        $error .= sprintf( __( "%s is required! Please fill in the field and try again!<br/>", WPC_CLIENT_TEXT_DOMAIN), $all_custom_fields[$all_key]['title']);
                    }
                }
            }
        }
    }


    if ( empty( $error ) ) {


        $userdata = array(
            'user_pass'         => WPC()->prepare_password( $_REQUEST['user_data']['pass2'] ),
            'user_login'        => esc_attr( $_REQUEST['user_data']['user_login'] ),
            'user_email'        => $_REQUEST['user_data']['email'],
            'first_name'        => esc_attr( $_REQUEST['user_data']['first_name'] ),
            'last_name'         => esc_attr( $_REQUEST['user_data']['last_name'] ),
            'send_password'     => ( isset( $_REQUEST['user_data']['send_password'] ) ) ? esc_attr( $_REQUEST['user_data']['send_password'] ) : '',
            'temp_password'     => !empty( $_REQUEST['user_data']['temp_password'] ) ? 1 : '',
            'parent_client_id'  => esc_attr( $_REQUEST['wpc_clients'] ),
            'avatar'            => esc_attr( $_REQUEST['user_data']['avatar'] ),
        );
        if ( isset( $_REQUEST['user_data']['ID'] ) ) {
            $userdata['ID'] = $_REQUEST['user_data']['ID'];
        } else {
            $userdata['role'] = 'wpc_client_staff';
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
            //insert new Employee
            $user_id = wp_insert_user( $userdata );

            if ( '1' == $userdata['send_password'] ) {

                $args = array( 'client_id' => $user_id, 'user_password' => $userdata['user_pass'] );

                //send email
                WPC()->mail( 'staff_created', $userdata['user_email'], $args, 'staff_created' );
            }

        } else {
            if( empty( $userdata['user_pass'] ) ) {
                unset( $userdata['user_pass'] );
            }

            add_filter( 'send_password_change_email', function() { return false; } );

            //update  data
            wp_update_user( $userdata );
            $user_id = $userdata['ID'];

            if ( '1' == $userdata['send_password'] ) {

                $args = array( 'client_id' => $user_id, 'user_password' => $userdata['user_pass'] );

                //send email
                WPC()->mail( 'client_updated', $userdata['user_email'], $args, 'client_updated' );
            }
        }

        if( $temp_password ) {
            WPC()->members()->set_temp_password( $user_id );
        }

        //save custom fileds
        if ( isset( $custom_fields ) && 0 < count( $custom_fields ) ) {
            $wpc_custom_fields = WPC()->get_settings( 'custom_fields' );

            foreach( $custom_fields as $key => $value ) {
                if ( isset( $wpc_custom_fields[$key]['type'] ) && 'file' == $wpc_custom_fields[$key]['type'] ) {
                    //for file custom field
                    if( !empty( $value['name'] ) ) {
                        $new_name = basename(rand(0000, 9999) . $value['name']);
                        $filepath = WPC()->get_upload_dir('wpclient/_custom_field_files/' . $key . '/') . $new_name;

                        if ( move_uploaded_file($value['tmp_name'], $filepath ) ) {
                            $filedata = get_user_meta( $user_id, $key, true );
                            if( !empty( $filedata ) && isset( $filedata['filename'] ) ) {
                                $filepath = WPC()->get_upload_dir('wpclient/_custom_field_files/' . $key . '/') . $filedata['filename'];
                                if( file_exists( $filepath ) ) {
                                    unlink( $filepath );
                                }
                            }
                            update_user_meta( $user_id, $key, array( 'origin_name' => $value['name'], 'filename' => $new_name ) );
                        }
                    } else {
                        $filedata = get_user_meta( $user_id, $key, true );
                        if( !empty( $filedata ) && isset( $filedata['filename'] ) ) {
                            $filepath = WPC()->get_upload_dir('wpclient/_custom_field_files/' . $key . '/') . $filedata['filename'];
                            if( file_exists( $filepath ) ) {
                                unlink( $filepath );
                            }

                            delete_user_meta( $user_id, $key );
                        }
                    }
                } else {
                    update_user_meta( $user_id, $key, $value );
                }
                //set value to related user_meta with this custom feild
                if ( isset( $wpc_custom_fields[$key]['relate_to'] ) && '' != trim( $wpc_custom_fields[$key]['relate_to'] ) ) {
                    update_user_meta( $user_id, trim( $wpc_custom_fields[$key]['relate_to'] ), $value );
                }
            }
        }


        //assign Employee to client
        update_user_meta( $user_id, 'parent_client_id', $userdata['parent_client_id'] );

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

                            if( strpos( $file, md5( $user_id . 'wpc_avatar' ) ) === 0 ) {
                                unlink( $avatars_dir . DIRECTORY_SEPARATOR . $file );
                            }
                        }
                    }
                }

                //rename avatar from temp and save in user meta
                $fileinfo = pathinfo( $avatars_dir . $userdata['avatar'] );

                $avatar_file = md5( $user_id . 'wpc_avatar' ) . time() . '.' . $fileinfo['extension'];
                rename( $avatars_dir . $userdata['avatar'] , $avatars_dir . $avatar_file );
                update_user_meta( $user_id, 'wpc_avatar', $avatar_file );
            }
        }

        /*our_hook_
            hook_name: wpc_client_staff_data_saved
            hook_title: Staff Data Saved
            hook_description: Can be used for saving custom fields on Staff Add/Edit Form.
            hook_type: action
            hook_in: wp-client
            hook_location clients_staff_edit.php
            hook_param: int $user_id, array $userdata
            hook_since: 4.5.4.2
        */
        do_action( 'wpc_client_staff_data_saved', $user_id, $_REQUEST['user_data'] );

        //redirect
        if ( isset( $_REQUEST['user_data']['ID'] ) )
            WPC()->redirect( 'admin.php?page=wpclient_clients&tab=staff&msg=u' );
        else
            WPC()->redirect( 'admin.php?page=wpclient_clients&tab=staff&msg=a' );

    }
}


//get Employee data
if ( isset( $_REQUEST['user_data'] ) ) {
    $user_data = $_REQUEST['user_data'];
} elseif ( 'staff_edit' == $_GET['tab'] ) {
    $user = get_userdata( $_GET['id'] );
    $user_data['ID']                = $user->data->ID;
    $user_data['user_login']        = $user->data->user_login;
    $user_data['email']             = $user->data->user_email;
    $user_data['first_name']        = get_user_meta( $user->data->ID, 'first_name', true );
    $user_data['last_name']         = get_user_meta( $user->data->ID, 'last_name', true );
    $user_data['parent_client_id']  = get_user_meta( $user->data->ID, 'parent_client_id', true );
    $user_data['avatar']            = get_user_meta( $user->data->ID, 'wpc_avatar', true );
}

$not_approved_clients = get_users( array( 'role' => 'wpc_client', 'meta_key' => 'to_approve', 'fields' => 'ID', ) );
//get all clients
$args = array(
    'role'      => 'wpc_client',
    'orderby'   => 'user_login',
    'order'     => 'ASC',
    'exclude'   => $not_approved_clients,
    'fields'    => array( 'ID', 'user_login' ),

);

$clients = get_users( $args ); ?>

<div class="wrap">

    <?php echo WPC()->admin()->get_plugin_logo_block() ?>

    <div class="wpc_clear"></div>

    <div id="wpc_container">

        <?php echo WPC()->admin()->gen_tabs_menu( 'clients' ) ?>

        <span class="wpc_clear"></span>
        <div class="wpc_tab_container_block add_staff">
            <h2><?php echo ( 'staff_add' == $_GET['tab'] ) ? sprintf( __( 'Add new %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ) : sprintf( __( 'Edit %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ); ?></h2>
            <div id="message" class="error wpc_notice fade" <?php echo ( empty( $error ) )? 'style="display: none;" ' : '' ?> ><?php echo $error; ?></div>

            <form name="edit_employee" id="edit_employee" method="post" enctype="multipart/form-data">
                <?php if ( 'staff_edit' == $_GET['tab'] ) { ?>
                    <input type="hidden" name="user_data[ID]" value="<?php echo ( isset( $user_data['ID'] ) ) ? $user_data['ID'] : ''  ?>" />
                    <input type="hidden" name="user_data[user_login]" value="<?php echo ( isset( $user_data['user_login'] ) ) ? $user_data['user_login'] : ''  ?>" />
                <?php } ?>
                <div class="avatar_wrapper">
                    <?php if( 'staff_add' == $_GET['tab'] ) {
                        echo WPC()->members()->build_avatar_field( 'user_data[avatar]' );
                    } else {
                        echo WPC()->members()->build_avatar_field( 'user_data[avatar]', $user_data['avatar'], $user_data['ID'] );
                    } ?>
                </div>
                <div class="fields_wrapper">
                    <table class="form-table" style="margin-top: 0;">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="user_login"><?php printf( __( '%s Login', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ) ?> <span class="description"><?php _e( '(required)', WPC_CLIENT_TEXT_DOMAIN ) ?></span></label>
                                </th>
                                <td>
                                    <?php if ( 'staff_add' == $_GET['tab'] ): ?>
                                        <input type="text" name="user_data[user_login]" id="user_login" value="<?php echo ( isset( $user_data['user_login'] ) ) ? $user_data['user_login'] : ''  ?>" />
                                    <?php else: ?>
                                        <input type="text" disabled id="user_login" value="<?php echo ( isset( $user_data['user_login'] ) ) ? $user_data['user_login'] : ''  ?>" />
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="email"><?php _e( 'E-mail', WPC_CLIENT_TEXT_DOMAIN ) ?> <span class="description"><?php _e( '(required)', WPC_CLIENT_TEXT_DOMAIN ) ?></span></label>
                                </th>
                                <td>
                                    <input type="text" name="user_data[email]" id="email" value="<?php echo ( isset( $user_data['email'] ) ) ? $user_data['email'] : ''  ?>" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="first_name"><?php _e( 'First Name', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </th>
                                <td>
                                    <input type="text" name="user_data[first_name]" id="first_name" value="<?php echo ( isset( $user_data['first_name'] ) ) ? $user_data['first_name'] : ''  ?>" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="last_name"><?php _e( 'Last Name', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </th>
                                <td>
                                    <input type="text" name="user_data[last_name]" id="last_name" value="<?php echo ( isset( $user_data['last_name'] ) ) ? $user_data['last_name'] : ''  ?>" />
                                </td>
                            </tr>

                            <?php
                                if ( is_array( $all_custom_fields ) && 0 < count( $all_custom_fields ) ) {
                                    WPC()->custom_fields()->add_custom_fields_scripts();

                                    foreach( $all_custom_fields as $key => $value ) {
                                        if ( 'hidden' == $value['type'] ) {
                                            echo $value['field'];
                                        } else {
                                            echo '<tr><th>';
                                            echo ( !empty( $value['label'] ) ) ? $value['label'] : '';
                                            echo '</th><td>';
                                            echo ( !empty( $value['field'] ) ) ? $value['field'] : '';
                                            echo ( !empty( $value['description'] ) ) ? '<br />' . $value['description']: '';
                                            echo '</td></tr>';
                                        }
                                    }
                                }


                                if( 'staff_edit' == $_GET['tab'] ) {
                                    /*our_hook_
                                      hook_name: wpc_client_edit_staff_form_html
                                      hook_title: Staff Edit Form
                                      hook_description: Can be used for adding custom fields on Staff Edit Form.
                                      hook_type: action
                                      hook_in: wp-client
                                      hook_location clients_staff_edit.php
                                      hook_param: int $id
                                      hook_since: 3.9.0
                                    */
                                    do_action( 'wpc_client_edit_staff_form_html', $id_client );
                                } else {
                                    /*our_hook_
                                          hook_name: wpc_client_add_staff_form_html
                                          hook_title: Staff Add Form
                                          hook_description: Can be used for adding custom fields on Staff Add Form.
                                          hook_type: action
                                          hook_in: wp-client
                                          hook_location clients_staff_edit.php
                                          hook_param:
                                          hook_since: 3.9.0
                                      */

                                    do_action( 'wpc_client_add_staff_form_html' );
                                }


                            ?>

                            <?php if ( is_array( $clients ) && 0 < count( $clients ) ) { ?>
                                <tr>
                                    <th scope="row">
                                        <label><?php printf( '%s %s', WPC()->custom_titles['staff']['p'], WPC()->custom_titles['client']['s'] ) ?></label>
                                    </th>
                                    <td>
                                        <?php $link_array = array(
                                            'title'   => sprintf( __( 'Assign to %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
                                            'text'    => sprintf( __( 'Assign to %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
                                            'data-marks' => 'radio'
                                        );
                                        $input_array = array(
                                            'name'  => 'wpc_clients',
                                            'id'    => 'wpc_clients',
                                            'value' => ( isset( $user_data['parent_client_id'] ) && !empty( $user_data['parent_client_id'] ) ) ? $user_data['parent_client_id'] : ''
                                        );
                                        $additional_array = array(
                                            'counter_value' => ( isset( $user_data['parent_client_id'] ) && !empty( $user_data['parent_client_id'] ) ) ? get_userdata( $user_data['parent_client_id'] )->get( 'user_login' ) : ''
                                        );

                                        WPC()->assigns()->assign_popup( 'client', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array ); ?>

                                        <a href="javascript: void(0);" class="remove_assign" title="<?php _e( 'Remove Assign', WPC_CLIENT_TEXT_DOMAIN ) ?>" style="color:red;font-size: 16px;font-weight: bold; <?php if( !( isset( $user_data['parent_client_id'] ) && !empty( $user_data['parent_client_id'] ) ) ) { ?>display:none;<?php } ?>">&times;</a>
                                    </td>
                                </tr>
                            <?php } ?>

                            <tr>
                                <th scope="row">
                                    <label for="pass1"><?php _e( 'Password', WPC_CLIENT_TEXT_DOMAIN ) ?> <span class="description"><?php _e( '(twice, required)', WPC_CLIENT_TEXT_DOMAIN ) ?></span></label>
                                </th>
                                <td>
                                    <input type="password" name="user_data[pass1]" autocomplete="off" id="pass1" value="" />
                                    <br />
                                    <input type="password" name="user_data[pass2]" autocomplete="off" id="pass2" value="" />
                                    <br />
                                    <br />
                                    <input type="button" class="wpc_generate_password_button button" value="<?php _e( 'Generate Password', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                                    <br />
                                    <br />
                                    <div id="pass-strength-result" style="display: block;"><?php _e( 'Strength indicator', WPC_CLIENT_TEXT_DOMAIN ) ?></div>
                                    <p class="description indicator-hint"><?php _e( 'Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers and symbols like ! " ? $ % ^ &amp; ).', WPC_CLIENT_TEXT_DOMAIN ) ?></p>
                                </td>
                            </tr>

                            <tr <?php if( 'staff_edit' == $_GET['tab'] ) { ?>style="display: none;"<?php } ?>>
                                <th scope="row"></th>
                                <td>
                                    <label for="temp_password"><input type="checkbox" name="user_data[temp_password]" value="1" id="temp_password" /> <?php _e( 'Mark password as temporary.', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                            </tr>

                            <tr <?php if( 'staff_edit' == $_GET['tab'] ) { ?>style="display: none;"<?php } ?>>
                                <th scope="row"></th>
                                <td>
                                    <label for="send_password"><input type="checkbox" name="user_data[send_password]" id="send_password" value="1" checked="checked" <?php if( 'staff_edit' == $_GET['tab'] ) { ?>disabled="disabled"<?php } ?>/> <?php _e( 'Send this password to the new user by email.', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <p class="submit">
                        <input type="submit" value="<?php echo ( 'staff_add' == $_GET['tab'] ) ? __( 'Save', WPC_CLIENT_TEXT_DOMAIN ) : __( 'Update', WPC_CLIENT_TEXT_DOMAIN ) ?>" class="button-primary" id="update_user" name="update_user">
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript" language="javascript">
    jQuery( document ).ready( function( $ ) {
        <?php echo ( empty( $error ) )? '$( "#message" ).hide();' : '' ?>

        <?php if( 'staff_edit' == $_GET['tab'] ) { ?>
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

        jQuery( '#wpc_clients' ).on( 'wpc_change_assign_value', function() {
            if( jQuery(this).val() == '' ) {
                jQuery('.remove_assign').hide();
            } else {
                jQuery('.remove_assign').show();
            }
        });
        jQuery( document ).on( 'click', '.remove_assign', function() {
            jQuery( '#wpc_clients' ).val('');
            jQuery( '.edit.counter_wpc_clients' ).html('()');
            jQuery( this ).hide();
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

            <?php if( 'staff_add' == $_GET['tab'] ) { ?>
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