<?php


if ( !class_exists( "WPC_FW_Ajax" ) ) {

    class WPC_FW_Ajax extends WPC_FW_Admin_Common {

        /**
        * PHP 5 constructor
        **/
        function __construct() {

            $this->fw_common_construct();
            $this->fw_admin_common_construct();

            //get item file
            add_action( 'wp_ajax_wpc_get_fw_item_file', array( &$this, 'get_item_file' ) );

            //AJAX
            add_action( 'wp_ajax_fw_get_popup_pagination_data', array( WPC()->hooks(), 'WPC_Assigns->ajax_get_popup_pagination_data' ) );
            //AJAX update assigned clients/circles
            add_action( 'wp_ajax_fw_update_assigned_data', array( &$this, 'fw_update_assigned_data' ) );

            //get options filter for wizards
            add_action( 'wp_ajax_fw_filter_for_wizards', array( &$this, 'ajax_fw_filter_for_wizards' ) );
        }


        /**
         * AJAX update assigned clients\cicles
         **/
        function fw_update_assigned_data() {
            global $wpdb;
            if( isset( $_POST['data_type'] ) && !empty( $_POST['data_type'] ) && isset( $_POST['current_page'] ) && 'wpclients_feedback_wizard' == $_POST['current_page'] ) {
                $datatype = $_POST['data_type'];
                switch($datatype) {
                    case 'wpc_clients':
                        if( isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['data']) ) {
                            $id = $_POST['id'];

                            $assign_data = array();
                            if( 'all' == $_POST['data'] ) {
                                if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                                    //manager's clients
                                    $assign_data = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'client' );
                                    if ( count( $assign_data) ) {
                                        $excluded_clients = WPC()->members()->get_excluded_clients();
                                        $assign_data = array_diff( $assign_data, $excluded_clients );
                                    }

                                } else {
                                    $excluded_clients = WPC()->members()->get_excluded_clients();
                                    //all clients
                                    $args = array(
                                        'role'      => 'wpc_client',
                                        'exclude'   => $excluded_clients,
                                        'fields'    => array( 'ID' ),
                                    );
                                    $clients = get_users( $args );

                                    foreach ( $clients as $client ) {
                                        $assign_data[] = $client->ID;
                                    }
                                }
                            } else {
                                if( !empty( $_POST['data'] ) ) {
                                    $assign_data = explode( ',', $_POST['data'] );
                                }
                            }

                            WPC()->assigns()->set_assigned_data( 'feedback_wizard', $id, 'client', $assign_data );

                            echo json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
                        } else {
                            echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                        }
                        break;
                    case 'wpc_circles':
                        if( isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['data']) ) {
                            $id = $_POST['id'];

                            $assign_data = array();
                            if( 'all' == $_POST['data'] ) {

                                $circles = $wpdb->get_col(
                                    "SELECT group_id
                                    FROM {$wpdb->prefix}wpc_client_groups"
                                );

                                $assign_data = ( is_array( $circles ) && 0 < count( $circles ) ) ? $circles : array();

                            } else {
                                if( !empty( $_POST['data'] ) ) {
                                    $assign_data = explode( ',', $_POST['data'] );
                                }
                            }

                            WPC()->assigns()->set_assigned_data( 'feedback_wizard', $id, 'circle', $assign_data );

                            echo json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
                        } else {
                            echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                        }
                        break;
                }
            } else {
                echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
            }
            exit;
        }

         /**
         * AJAX get options filter for wizards
         **/
         function ajax_fw_filter_for_wizards() {
             global $wpdb;
             if ( isset( $_POST['filter'] ) ) {
                 switch( $_POST['filter'] ) {
                    case 'client':

                        $unique_client = WPC()->assigns()->get_assign_data_by_object_assign( 'feedback_wizard', 'client' );

                        ?>
                        <option value="-1" selected="selected"><?php printf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ?></option>
                        <?php
                        if ( is_array( $unique_client ) && 0 < count( $unique_client ) )
                        foreach( $unique_client as $key => $client_id ) {
                            if ( '' != $client_id ) {
                                echo '<option value="' . $client_id . '" >' . get_userdata( $client_id )->user_login . '</option>';
                            }
                        }
                        break;

                    case 'circle':

                        $unique_circle = WPC()->assigns()->get_assign_data_by_object_assign( 'feedback_wizard', 'circle' );

                        $unique_circle = $wpdb->get_results( "SELECT group_id, group_name FROM {$wpdb->prefix}wpc_client_groups WHERE group_id IN('" . implode( "','", $unique_circle ) . "')", ARRAY_A );
                        ?>
                        <option value="-1" selected="selected"><?php printf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['s'] ) ?></option>
                        <?php
                        if ( is_array( $unique_circle ) && 0 < count( $unique_circle ) )
                        foreach( $unique_circle as $circle_id ) {
                            if ( '' != $circle_id['group_id'] ) {
                                echo '<option value="' . $circle_id['group_id'] . '">' . $circle_id['group_name'] . '</option>';
                            }
                        }
                        break;
                 }
             }
             exit;
         }


        /**
        * Get Item file
        */
        function get_item_file( ) {
            global $wpdb;

            $access = false;

            $item_id = $_GET['id'];
            $download = ( isset( $_GET['d'] ) && true == $_GET['d'] ) ? true : false;

            $code = ( isset( $_GET['c'] ) && '' != $_GET['c'] ) ? $_GET['c'] : '';
            $type = ( isset( $_GET['t'] ) && '' != $_GET['t'] ) ? $_GET['t'] : 'img';

            if ( is_user_logged_in() ) {

                if  ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {
                    //admin access
                     $access = true;
                }  else {
                    //user access
                    $user_id = get_current_user_id();

                    if ( user_can( $user_id, 'wpc_client_staff' ) ) {
                        $user_id = get_user_meta( $user_id, 'parent_client_id', true );
                    }

                    //checking access for file
                    if( md5( $user_id . 'item_file' . $item_id ) == $code ) {
                        //access for client
                        $access = true;
                    }
                }
            }

            if ( $access ) {
                $item = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wpc_client_feedback_items WHERE item_id = %d", $item_id  ), ARRAY_A );
                $uploads        = wp_upload_dir();
                $file           = ( isset( $_GET['thumbnail'] ) ) ? 't_' . $item['file'] : $item['file'];
                $target_path    = $uploads['basedir'] . "/wpclient/items/$file";

                if ( $download ) {
                    header("Content-type: application/octet-stream");
                    header("Content-Disposition: attachment; filename=$item[file_name]");
                    ob_clean();
                    flush();
                    readfile( $target_path );
                } else {

                    if ( 'pdf' == $type ) {
                        header("Content-type: application/pdf");
                    } elseif( 'img' == $type ) {

                        $filedata_array = explode( ".", $file );
                        if( 1 < count( $filedata_array ) ) {
                            $ext = strtolower( $filedata_array[ count( $filedata_array ) - 1 ] );
                        } else {
                            $ext = '';
                        }

                        switch( $ext ) {
                            case 'gif':
                                header("Content-type: image/gif");
                                break;
                            case 'jpg';
                            case 'jpeg':
                                header("Content-type: image/jpeg");
                                break;
                            case 'png':
                                header("Content-type: image/png");
                                break;
                            case 'pdf':
                                header("Content-type: application/pdf");
                                break;
                            default:
                                header("Content-type: text/html");
                                break;
                        }

                    } else {
                        header("Content-type: " . urldecode( $type ) );
                        header("Content-Disposition: attachment; filename=$item[file_name]");
                    }

                    echo readfile( $target_path );
                }

            }
            exit;

        }

    //end class
    }

}