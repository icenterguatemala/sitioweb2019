<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;

//check auth
if( ( ( !current_user_can( 'wpc_add_clients' ) && 'add_client' == $_GET['tab'] ) ||
      ( !current_user_can( 'wpc_edit_clients' ) && 'edit_client' == $_GET['tab']  ) ) &&
    !current_user_can( 'wpc_admin' ) &&
    !current_user_can( 'administrator' ) ) {
    WPC()->redirect( get_admin_url() . 'admin.php?page=wpclient_clients' );
}

$error = '';
if ( isset( $_REQUEST['update_user'] ) ) {
    // validate at php side

    if( empty( $_REQUEST['contact_name'] ) ) {
        // empty contact_name
        $error .= __( 'A Contact Name is required.<br/>', WPC_CLIENT_TEXT_DOMAIN );
    }

    if( empty( $_REQUEST['user_login'] ) ) {
        // empty username
        $error .= __( 'Username is required.<br/>', WPC_CLIENT_TEXT_DOMAIN );
    }

    if ( !isset( $_REQUEST['ID'] ) ) {
        if ( username_exists( $_REQUEST['user_login'] ) ) {
            // empty username
            $error .= __( 'User already exists.<br/>', WPC_CLIENT_TEXT_DOMAIN );
        }
    }

    if( empty( $_REQUEST['user_email'] ) ) {
        // empty email
        $error .= __( 'A email is required.<br/>', WPC_CLIENT_TEXT_DOMAIN );
    }

    $contact_email = apply_filters( 'pre_user_email', isset( $_REQUEST['user_email'] ) ? $_REQUEST['user_email'] : '' );
    if( email_exists( $contact_email ) ) {
        if( !isset( $_REQUEST['ID'] ) || ( $_REQUEST['ID'] != get_user_by( 'email', $contact_email )->ID ) ) {
            // email already exist
            $error .= __( 'Email address already in use.<br/>', WPC_CLIENT_TEXT_DOMAIN );
        }
    }

    if( !isset( $_REQUEST['ID'] ) || ( isset( $_REQUEST['ID'] ) && !empty( $_REQUEST['pass1'] ) ) ) {
        if( empty( $_REQUEST['pass1'] ) || empty( $_REQUEST['pass2'] ) ) {
            if( empty( $_REQUEST['pass1'] ) ) // password
                $error .= __( 'Sorry, password is required.<br/>', WPC_CLIENT_TEXT_DOMAIN );
            elseif ( empty( $_REQUEST['pass2'] ) ) // confirm password
                $error .= __( 'Sorry, confirm password is required.<br/>', WPC_CLIENT_TEXT_DOMAIN );
        } elseif( $_REQUEST['pass1'] != $_REQUEST['pass2'] ) {
            $error .= __( 'Sorry, Passwords are not matched! .<br/>', WPC_CLIENT_TEXT_DOMAIN );
        }
    }

    if( isset( $_REQUEST['ID'] ) ) {
        $all_custom_fields = WPC()->custom_fields()->get_custom_fields('admin_edit_client', $_REQUEST['ID']);
    } else {
        $all_custom_fields = WPC()->custom_fields()->get_custom_fields( 'admin_add_client' );
    }

    if( isset( $_REQUEST['custom_fields'] ) && is_array( $_REQUEST['custom_fields'] ) && is_array( $all_custom_fields ) ) {
        foreach( $all_custom_fields as $all_key=>$all_value ) {
            if( ( 'checkbox' == $all_value['type'] || 'radio' == $all_value['type'] || 'multiselectbox' == $all_value['type'] ) && !array_key_exists( $all_key, $_REQUEST['custom_fields'] ) ) {
                $_REQUEST['custom_fields'][$all_key] = '';
            }

            foreach( $_REQUEST['custom_fields'] as $key=>$value ) {
                if( $key == $all_key && isset( $all_value['required'] ) && '1' == $all_value['required'] && '' == $value ) {
                    $error .= sprintf( __( "%s is required! Please fill in the field and try again!<br/>", WPC_CLIENT_TEXT_DOMAIN), $all_custom_fields[$all_key]['title'] );
                }
            }
        }
    }

    if( isset( $_FILES['custom_fields'] ) && is_array( $_FILES['custom_fields'] ) && is_array( $all_custom_fields ) ) {
        $files_custom_fields = array();
        foreach( $_FILES['custom_fields'] as $key1 => $value1)
            foreach( $value1 as $key2 => $value2 )
                $files_custom_fields[$key2][$key1] = $value2;

        foreach( $files_custom_fields as $key=>$value ) {
            if( isset( $all_custom_fields[$key] ) && isset( $all_custom_fields[$key]['required'] ) && '1' == $all_custom_fields[$key]['required'] && '' == $value['name'] ) {
                $error .= sprintf( __( "%s is required! Please fill in the field and try again!<br/>", WPC_CLIENT_TEXT_DOMAIN), $all_custom_fields[$key]['title'] );
            }
        }
    }

    if( 'edit_client' == $_GET['tab'] ) {
        /*our_hook_
            hook_name: wpc_client_validate_edit_client_fields
            hook_title: Validate Edit Client Form
            hook_description: Hook runs before saving client data on Edit Client form.
            hook_type: filter
            hook_in: wp-client
            hook_location client_edit.php
            hook_param: string $error
            hook_since: 3.4.9
        */
        $error = apply_filters( 'wpc_client_validate_edit_client_fields', $error );
    } elseif( 'add_client' == $_GET['tab'] ) {
        /*our_hook_
            hook_name: wpc_client_validate_add_client_fields
            hook_title: Validate Add Client Form
            hook_description: Hook runs before saving client data on Add Client form.
            hook_type: filter
            hook_in: wp-client
            hook_location client_edit.php
            hook_param: string $error
            hook_since: 3.4.9
        */
        $error = apply_filters( 'wpc_client_validate_add_client_fields', $error );
    }


    if( empty( $error ) ) {
/*
        $userdata = array(
            'ID'                => esc_attr($ID),
            'user_pass'         => $contact_password,
            'user_login'        => esc_attr( get_userdata($ID)->get( 'user_login' ) ),
            'business_name'     => ( isset( $business_name ) ) ? esc_attr( trim( $business_name ) ) : esc_attr( trim( $contact_name ) ),
            'display_name'      => esc_attr( trim( $contact_name ) ),
            'user_email'        => $contact_email,
//            'first_name'        => esc_attr( trim( $business_name ) ),
            'contact_phone'     => esc_attr( $contact_phone ),
            'send_password'     => ( isset( $send_password ) && '1' == $send_password ) ? '1' : '0',
        );*/


        $userdata = array(
            'user_pass'     => WPC()->prepare_password( $_REQUEST['pass2'] ),
            'user_email'    => $_REQUEST['user_email'],
            'business_name' => ( isset( $_REQUEST['business_name'] ) ) ? esc_attr( trim( $_REQUEST['business_name'] ) ) : esc_attr( trim( $_REQUEST['contact_name'] ) ),
            'display_name'  => esc_attr( trim( $_REQUEST['contact_name'] ) ),
            'contact_phone' => esc_attr( $_REQUEST['contact_phone'] ),
            'send_password' => ( isset( $_REQUEST['send_password'] ) ) ? esc_attr( $_REQUEST['send_password'] ) : '',
            'temp_password' => !empty( $_REQUEST['contact_temp_password'] ) ? 1 : '',
            'avatar'        => esc_attr( $_REQUEST['avatar'] ),
        );

        if ( isset( $_REQUEST['ID'] ) ) {
            $userdata['ID'] = $_REQUEST['ID'];
            $userdata['user_login'] = esc_attr( get_userdata( $_REQUEST['ID'] )->get( 'user_login' ) );
            if( empty( $userdata['user_pass'] ) ) {
                unset( $userdata['user_pass'] );
            }
        } else {
            $userdata['role'] = 'wpc_client';
            $userdata['user_login'] = esc_attr( trim( $_REQUEST['user_login'] ) );
        }


        //set custom fields
        $userdata['custom_fields'] = array();
        if ( isset( $_REQUEST['custom_fields'] ) )
            $userdata['custom_fields'] = array_merge( $userdata['custom_fields'], $_REQUEST['custom_fields'] );

        //set files custom fields
        if( isset( $_FILES['custom_fields'] ) ) {
            $files_custom_fields = array();
            foreach( $_FILES['custom_fields'] as $key1 => $value1)
                foreach( $value1 as $key2 => $value2 )
                    $files_custom_fields[$key2][$key1] = $value2;

            $userdata['custom_fields'] = array_merge( $userdata['custom_fields'], $files_custom_fields );
        }

        //set manager
        if ( current_user_can( 'wpc_manager' ) && ! current_user_can( 'administrator' ) ) {
            if ( 'edit_client' == $_GET['tab'] ) {
                if ( isset( $_REQUEST['wpc_managers'] ) )
                    $userdata['admin_manager'] = esc_attr( $_REQUEST['wpc_managers'] );
            } else {
                $userdata['admin_manager'] = get_current_user_id();
            }
        } else {
            if ( isset( $_REQUEST['wpc_managers'] ) )
                $userdata['admin_manager'] = esc_attr( $_REQUEST['wpc_managers'] );
        }

        WPC()->members()->client_update_func( $userdata );      //exit;

        if( 'edit_client' == $_GET['tab'] ) {
            WPC()->redirect( 'admin.php?page=wpclient_clients&msg=u' );
        } else {
            WPC()->redirect( 'admin.php?page=wpclient_clients&msg=a' );
        }
    }
}

global $wpdb;

$client_data = array();
if ( isset( $_REQUEST['update_user'] ) ) {
    $client_data = $_REQUEST;
} elseif ( 'edit_client' == $_GET['tab'] ) {
    $client = get_userdata( $_GET['id'] );
    $client_data['ID']            = $client->data->ID;
    $client_data['user_login']    = $client->data->user_login;
    $client_data['user_email']     = $client->data->user_email;
    $client_data['contact_phone']  = get_user_meta( $client_data['ID'], $wpdb->prefix . 'contact_phone', true );
    $client_data['business_name']  = esc_attr( trim( get_user_meta( $client_data['ID'], 'wpc_cl_business_name', true ) ) );
    $client_data['contact_name']  = $client->data->display_name;
    $client_data['avatar']         = get_user_meta( $client_data['ID'], 'wpc_avatar', true );
}



if( isset( $_REQUEST['wpc_circles'] ) && count( $_REQUEST['wpc_circles'] ) ) {
    $client_groups = is_array( $_REQUEST['wpc_circles'] ) ? $_REQUEST['wpc_circles'] : array();
} else {
    if( 'add_client' == $_GET['tab'] ) {
        $client_groups = array();

        $groups = WPC()->groups()->get_groups();
        foreach ( $groups as $group ) {
            if( '1' == $group['auto_select'] && !$error ) {
                $client_groups[] = $group['group_id'];
            }
        }
    } else {
        $client_groups = WPC()->groups()->get_client_groups_id( $_GET['id'] );
    }
}

$groups = WPC()->groups()->get_groups();

//get managers
$args = array(
    'role'      => 'wpc_manager',
    'orderby'   => 'user_login',
    'order'     => 'ASC',
    'fields'    => array( 'ID', 'user_login' )
);

$managers = get_users( $args );

if( isset( $_REQUEST['wpc_managers'] ) && count( $_REQUEST['wpc_managers'] ) ) {
    $current_manager_ids = is_array( $_REQUEST['wpc_managers'] ) ? $_REQUEST['wpc_managers'] : array();
} else {
    if( 'add_client' == $_GET['tab'] ) {
        $current_manager_ids = array();
    } else {
        $current_manager_ids = WPC()->members()->get_client_managers( $_GET['id'], 'individual' );
    }
} ?>

<div class="wrap">

    <?php echo WPC()->admin()->get_plugin_logo_block() ?>

    <div class="wpc_clear"></div>

    <div id="wpc_container">

        <?php echo WPC()->admin()->gen_tabs_menu( 'clients' ) ?>

        <span class="wpc_clear"></span>
        <div class="wpc_tab_container_block">

            <h2><?php echo ( 'add_client' == $_GET['tab'] ) ? sprintf( __( 'Add new %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) : sprintf( __( 'Edit %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ); ?></h2>

            <div id="message" class="error wpc_notice fade" <?php echo ( empty( $error ) )? 'style="display: none;" ' : '' ?> ><?php echo $error; ?></div>

            <form action="" name="edit_client" id="edit_client" method="post" enctype="multipart/form-data">
                <?php if ( 'edit_client' == $_GET['tab'] ) { ?>
                    <input type="hidden" name="ID" value="<?php echo $client_data['ID'] ?>" />
                    <input type="hidden" name="user_login" value="<?php echo $client_data['user_login'] ?>" />
                    <?php if ( current_user_can( 'wpc_manager' ) && ! current_user_can( 'administrator' ) ) {
                        $client_managers = WPC()->assigns()->get_assign_data_by_assign( 'manager', 'client', $client_data['ID'] ); ?>
                        <input type="hidden" name="wpc_managers" value="<?php echo implode( ',', $client_managers ) ?>" />
                    <?php }
                } ?>

                <div class="avatar_wrapper">
                    <?php if( 'add_client' == $_GET['tab'] ) {
                        echo WPC()->members()->build_avatar_field( 'avatar' );
                    } else {
                        echo WPC()->members()->build_avatar_field( 'avatar', $client_data['avatar'], $client_data['ID'] );
                    } ?>
                </div>

                <div class="fields_wrapper">
                    <table class="form-table" style="margin-top: 0;">
                        <tr>
                            <th scope="row">
                                <label for="business_name"><?php _e( 'Business Name', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                            </th>
                            <td>
                                <input type="text" id="business_name" name="business_name" value="<?php echo isset( $client_data['business_name'] ) ? $client_data['business_name'] : '' ?>" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="contact_name"><?php _e( 'Contact Name', WPC_CLIENT_TEXT_DOMAIN ) ?> <span class="description"><?php _e( '(required)', WPC_CLIENT_TEXT_DOMAIN ) ?></span></label>
                            </th>
                            <td>
                                <input type="text" id="contact_name" name="contact_name" value="<?php echo isset( $client_data['contact_name'] ) ? $client_data['contact_name'] : '' ?>" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="user_email"><?php _e( 'Email', WPC_CLIENT_TEXT_DOMAIN ) ?> <span class="description"><?php _e( '(required)', WPC_CLIENT_TEXT_DOMAIN ) ?></span></label>
                            </th>
                            <td>
                                <input type="text" id="user_email" name="user_email" value="<?php echo isset( $client_data['user_email'] ) ? $client_data['user_email'] : '' ?>" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="contact_phone"><?php _e( 'Phone', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                            </th>
                            <td>
                                <input type="text" id="contact_phone" name="contact_phone" value="<?php echo isset( $client_data['contact_phone'] ) ? $client_data['contact_phone'] : '' ?>" />
                            </td>
                        </tr>

                        <?php $client_data['ID'] = isset( $client_data['ID'] ) ? $client_data['ID'] : '';

                        if( !WPC()->flags['easy_mode'] ) {
                            if( 'edit_client' == $_GET['tab'] ) {
                                $custom_fields = WPC()->custom_fields()->get_custom_fields('admin_edit_client', $client_data['ID']);
                            } else {
                                $custom_fields = WPC()->custom_fields()->get_custom_fields( 'admin_add_client' );
                            }

                            if ( is_array( $custom_fields ) && 0 < count( $custom_fields ) ) {
                                WPC()->custom_fields()->add_custom_fields_scripts();

                                foreach( $custom_fields as $key=>$value ) {
                                    if ( 'hidden' == $value['type'] ) {
                                        echo $value['field'];
                                    } else {
                                        echo '<tr><th>';
                                        echo ( !empty( $value['label'] ) ) ? $value['label'] : '';
                                        echo '</th><td>';
                                        echo ( !empty( $value['field'] ) ) ? $value['field'] : '';
                                        echo ( !empty( $value['description'] ) ) ? '<br /><span class="description">' . $value['description'] . '</span>' : '';
                                        echo '</td></tr>';
                                    }
                                }
                            }
                        }


                        if( 'edit_client' == $_GET['tab'] ) {
                            /*our_hook_
                              hook_name: wpc_client_edit_client_form_html
                              hook_title: Client Edit Form
                              hook_description: Can be used for adding custom fields on Client Edit Form.
                              hook_type: action
                              hook_in: wp-client
                              hook_location client_edit.php
                              hook_param: int $id
                              hook_since: 3.4.8
                            */
                            do_action( 'wpc_client_edit_client_form_html', $client_data['ID'] );
                        } else {
                            /*our_hook_
                                  hook_name: wpc_client_add_client_form_html
                                  hook_title: Client Add Form
                                  hook_description: Can be used for adding custom fields on Client Add Form.
                                  hook_type: action
                                  hook_in: wp-client
                                  hook_location client_edit.php
                                  hook_param:
                                  hook_since: 3.4.8
                              */

                            do_action( 'wpc_client_add_client_form_html' );
                        }

                        ?>

                        <tr>
                            <td colspan="2"><hr /></td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="user_login">
                                    <?php _e( 'Username', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                    <span class="description">
                                        <?php if( 'edit_client' == $_GET['tab'] ) {
                                            _e( "(can't be changed)", WPC_CLIENT_TEXT_DOMAIN );
                                        } else {
                                            _e( "(required)", WPC_CLIENT_TEXT_DOMAIN );
                                        } ?>
                                    </span>
                                </label>
                            </th>
                            <td>
                                <input type="text" id="user_login" name="user_login" <?php if( 'edit_client' == $_GET['tab'] ) { ?>disabled="disabled"<?php } ?> value="<?php echo isset( $client_data['user_login'] ) ? $client_data['user_login'] : '' ?>" />
                            </td>
                        </tr>



                        <?php

                        if( 'edit_client' == $_GET['tab'] ) {
                            /*our_hook_
                              hook_name: wpc_client_edit_client_after_username
                              hook_title: Client Edit Form
                              hook_description: Can be used for adding custom fields on Client Edit Form after username field.
                              hook_type: action
                              hook_in: wp-client
                              hook_location client_edit.php
                              hook_param: int $id
                              hook_since: 3.4.8
                            */
                            do_action( 'wpc_client_edit_client_after_username', $client_data['ID'] );
                        } else {
                            /*our_hook_
                                hook_name: wpc_client_add_client_after_username
                                hook_title: Client Add Form
                                hook_description: Can be used for adding custom fields on Client Add Form after username field.
                                hook_type: action
                                hook_in: wp-client
                                hook_location addclient.php
                                hook_param:
                                hook_since: 3.4.8
                            */

                            do_action( 'wpc_client_add_client_after_username' );
                        }


                        if( !current_user_can( 'wpc_manager' ) || current_user_can( 'administrator' ) ) {
                            if ( is_array( $managers ) && 0 < count( $managers ) ) { ?>
                                <tr <?php if( WPC()->flags['easy_mode'] ) { ?>style="display: none;" <?php } ?>>
                                    <th scope="row">
                                        <label><?php echo WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['manager']['p'] ?></label>
                                    </th>
                                    <td>
                                        <?php $link_array = array(
                                            'title'   => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['p'] ),
                                            'text'    => __( 'Select', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['manager']['p']
                                        );
                                        $input_array = array(
                                            'name'  => 'wpc_managers',
                                            'id'    => 'wpc_managers',
                                            'value' => ( is_array( $current_manager_ids ) && 0 < count( $current_manager_ids ) ) ? implode( ',', $current_manager_ids ) : ''
                                        );
                                        $additional_array = array(
                                            'counter_value' => ( is_array( $current_manager_ids ) && 0 < count( $current_manager_ids ) ) ? count( $current_manager_ids ) : 0
                                        );

                                        $current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';
                                        WPC()->assigns()->assign_popup( 'manager', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array ); ?>
                                    </td>
                                </tr>
                            <?php }
                        }

                        if ( is_array( $groups ) && 0 < count( $groups ) ) { ?>
                            <tr>
                                <th scope="row">
                                    <label><?php echo WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ?></label>
                                </th>
                                <td>
                                    <?php $link_array = array(
                                        'title'   => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'] ),
                                        'text'    => __( 'Select', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p']
                                    );
                                    $input_array = array(
                                        'name'  => 'wpc_circles',
                                        'id'    => 'wpc_circles',
                                        'value' => implode( ',', $client_groups )
                                    );
                                    $additional_array = array(
                                        'counter_value' => count( $client_groups )
                                    );

                                    $current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';
                                    WPC()->assigns()->assign_popup('circle', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array ); ?>
                                </td>
                            </tr>
                        <?php } ?>

                        <tr>
                            <th scope="row">
                                <label for="pass1"><?php _e( 'Password', WPC_CLIENT_TEXT_DOMAIN ) ?> <span class="description"><?php _e( '(twice, required)', WPC_CLIENT_TEXT_DOMAIN ) ?></span></label>
                            </th>
                            <td>
                                <input type="password" id="pass1" class="wpc_generate_password_field" name="pass1" autocomplete="off" value="" />
                                <br />
                                <input type="password" id="pass2" class="wpc_generate_password_field" name="pass2" value="" />
                                <br />
                                <br />
                                <input type="button" class="wpc_generate_password_button button" value="<?php _e( 'Generate Password', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                                <br />
                                <br />
                                <div id="pass-strength-result" style="display: block;"><?php _e( 'Strength indicator', WPC_CLIENT_TEXT_DOMAIN ) ?></div>
                                <div class="description indicator-hint" style="clear:both"><?php _e( 'Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers and symbols like ! " ? $ % ^ & ).', WPC_CLIENT_TEXT_DOMAIN ) ?>
                            </td>
                        </tr>

                        <tr <?php if( 'edit_client' == $_GET['tab'] ) { ?>style="display: none;"<?php } ?>>
                            <th scope="row"></th>
                            <td>
                                <label for="temp_password"><input type="checkbox" name="contact_temp_password" value="1" id="temp_password" <?php if( 'edit_client' == $_GET['tab'] ) { ?>disabled="disabled"<?php } ?> /> <?php _e( 'Mark password as temporary.', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                            </td>
                        </tr>
                        <tr <?php if( 'edit_client' == $_GET['tab'] ) { ?>style="display: none;"<?php } ?>>
                            <th scope="row"></th>
                            <td>
                                <label for="send_password"><input type="checkbox" checked="checked" id="send_password" name="send_password" value="1" <?php if( 'edit_client' == $_GET['tab'] ) { ?>disabled="disabled"<?php } ?> /> <?php _e( 'Send this password to the new user by email.', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                            </td>
                        </tr>

                        <tr>
                            <th>
                            </th>
                            <td>
                                <input type="submit" name="update_user" id="update_user" class="button-primary" value="<?php echo ( 'add_client' == $_GET['tab'] ) ? __( 'Save', WPC_CLIENT_TEXT_DOMAIN ) : __( 'Update', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                            </td>
                        </tr>
                    </table>
                </div>
            </form>

        </div>
    </div>
</div>

<script type="text/javascript" language="javascript">
    var site_url = '<?php echo site_url();?>';

    jQuery( document ).ready( function( $ ) {
        <?php echo ( empty( $error ) )? '$( "#message" ).hide();' : '' ?>

        wpc_generate_password( '#pass1, #pass2' );

        $( "#update_user" ).live( 'click', function() {

            var msg = '';

            var emailReg = /^([\w-'+\.]+@([\w-]+\.)+[\w-]{2,})?$/;

            if ( $( "#user_login" ).val() == '' ) {
                msg += "<?php echo esc_js( __( 'Username is required.', WPC_CLIENT_TEXT_DOMAIN ) ) ?><br/>";
            }

            if ( $( "#contact_name" ).val() == '' ) {
                msg += "<?php echo esc_js( __( 'Contact Name required.', WPC_CLIENT_TEXT_DOMAIN ) ) ?><br/>";
            }

            if ( $( "#user_email" ).val() == '' ) {
                msg += "<?php echo esc_js( __( 'Email required.', WPC_CLIENT_TEXT_DOMAIN ) ) ?><br/>";
            } else if ( !emailReg.test( $( "#user_email" ).val() ) ) {
                msg += "<?php echo esc_js( __( 'Invalid Email.', WPC_CLIENT_TEXT_DOMAIN ) ) ?><br/>";
            }

            <?php if( 'add_client' == $_GET['tab'] ) { ?>
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

        <?php if( 'edit_client' == $_GET['tab'] ) { ?>
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