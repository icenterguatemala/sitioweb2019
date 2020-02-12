<?php

if ( !class_exists( 'WPC_PPT_AJAX' ) ) {

    class WPC_PPT_AJAX extends WPC_PPT_Admin_Common {

        /**
        * PHP 5 constructor
        **/
        function __construct() {

            $this->ppt_common_construct();
            $this->ppt_admin_common_construct();

            add_action( 'wp_ajax_update_assigned_data', array( &$this, 'ppt_update_assigned_data' ), 9 );

        }

        /**
         * AJAX update assigned clients\circles
         **/
        function ppt_update_assigned_data() {
            if ( ! empty( $_POST['data_type'] ) && ! empty( $_POST['current_page'] ) ) {
                $datatype = $_POST['data_type'];

                switch ( $_POST['current_page'] ) {
                    case 'wpc_ppt_categories':
                        switch( $datatype ) {
                            case 'wpc_clients':
                                if ( ! empty( $_POST['id'] ) && isset( $_POST['data'] ) ) {
                                    $id = $_POST['id'];
                                    $assign_data = array();
                                    if ( ! empty( $_POST['data'] ) ) {
                                        $assign_data = explode( ',', $_POST['data'] );
                                    }

                                    WPC()->assigns()->set_assigned_data( 'post_category', $id, 'client', $assign_data );

                                    exit( json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                                } else {
                                    exit( json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                                }
                                break;
                            case 'wpc_circles':
                                if ( ! empty( $_POST['id'] ) && isset( $_POST['data'] ) ) {
                                    $id = $_POST['id'];
                                    $assign_data = array();
                                    if ( ! empty( $_POST['data'] ) ) {
                                        $assign_data = explode( ',', $_POST['data'] );
                                    }

                                    WPC()->assigns()->set_assigned_data( 'post_category', $id, 'circle', $assign_data );

                                    exit( json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                                } else {
                                    exit( json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                                }
                                break;
                        }
                    break;
                    case 'wpc_private_post_types':
                        switch( $datatype ) {
                            case 'wpc_clients':
                                if ( ! empty( $_POST['id'] ) && isset( $_POST['data'] ) ) {
                                    $id = $_POST['id'];

                                    if ( ! empty( $_POST['data'] ) ) {
                                        $assign_data = explode( ',', $_POST['data'] );

                                        if ( is_array( $assign_data ) && count( $assign_data ) ) {
                                            WPC()->assigns()->set_assigned_data( 'private_post', $id, 'client', $assign_data );
                                        } else {
                                            WPC()->assigns()->set_assigned_data( 'private_post', $id, 'client', array() );
                                        }
                                    } else {
                                        WPC()->assigns()->set_assigned_data( 'private_post', $id, 'client', array() );
                                    }

                                    exit( json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                                } else {
                                    exit( json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                                }
                                break;
                            case 'wpc_circles':
                                if ( ! empty( $_POST['id'] ) && isset( $_POST['data'] ) ) {
                                    $id = $_POST['id'];

                                    if ( ! empty( $_POST['data'] ) ) {
                                        $assign_data = explode( ',', $_POST['data'] );

                                        if ( is_array( $assign_data ) && count( $assign_data ) ) {
                                            WPC()->assigns()->set_assigned_data( 'private_post', $id, 'circle', $assign_data );
                                        } else {
                                            WPC()->assigns()->set_assigned_data( 'private_post', $id, 'circle', array() );
                                        }
                                    } else {
                                        WPC()->assigns()->set_assigned_data( 'private_post', $id, 'circle', array() );
                                    }

                                    exit( json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                                } else {
                                    exit( json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                                }
                                break;
                        }
                        break;
                }
            }
        }


    //end class
    }

}