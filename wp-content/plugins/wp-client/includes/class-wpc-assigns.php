<?php

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly


if ( ! class_exists( 'WPC_Assigns' ) ) :

final class WPC_Assigns {

    var $popup_flags = array(
        'client' => false,
        'circle' => false,
        'manager' => false,
    );

    /**
     * The single instance of the class.
     *
     * @var WPC_Assigns
     * @since 4.5
     */
    protected static $_instance = null;


    /**
     * Instance.
     *
     * Ensures only one instance of WPC_Assigns is loaded or can be loaded.
     *
     * @since 4.5
     * @static
     * @return WPC_Assigns - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {

        /*our_hook_
            hook_name: wpc_client_assigns_popup_flags
            hook_title: Array of assigns popup flags
            hook_description:
            hook_type: filter
            hook_in: wp-client
            hook_location
            hook_param: array $popup_flags
            hook_since: 4.5.0
        */
        $this->popup_flags = apply_filters( 'wpc_client_assigns_popup_flags', $this->popup_flags );

    }



    /**
     * Function to set assigned data
     */
    function set_assigned_data( $object_type, $object_id, $assign_type = 'client', $assign_data = array() ) {
        global $wpdb;


        if( isset( $object_type ) && !empty( $object_type ) && isset( $object_id ) && !empty( $object_id ) ) {

            if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {

                $all_assigned_to_manager = $this->get_assign_data_by_object( 'manager', get_current_user_id(), $assign_type );


                if( 'client' == $assign_type ) {
                    $all_circles_assigned_to_manager = $this->get_assign_data_by_object( 'manager', get_current_user_id(), 'circle' );
                    $clients_from_groups = array();
                    if( is_array( $all_circles_assigned_to_manager ) && 0 < count( $all_circles_assigned_to_manager ) ) {
                        foreach( $all_circles_assigned_to_manager as $circle_id ) {
                            $clients_from_groups = array_merge( $clients_from_groups, WPC()->groups()->get_group_clients_id( $circle_id ) );
                        }
                    }
                    $all_assigned_to_manager = array_merge( $all_assigned_to_manager, $clients_from_groups );
                    $all_assigned_to_manager = array_unique( $all_assigned_to_manager );
                    $excluded_clients = WPC()->members()->get_excluded_clients( 'archive' );
                    $all_assigned_to_manager = array_diff( $all_assigned_to_manager, $excluded_clients );
                }


                if( is_array( $all_assigned_to_manager ) && 0 < count( $all_assigned_to_manager ) ) {
                    $in = implode( "','", $all_assigned_to_manager );

                    $wpdb->query(
                        "DELETE
                            FROM {$wpdb->prefix}wpc_client_objects_assigns
                            WHERE object_type='{$object_type}' AND
                                object_id='{$object_id}' AND
                                assign_type='{$assign_type}' AND
                                assign_id IN ('{$in}')"
                    );
                }

            } else {
                if( 'client' == $assign_type ) {
                    $excluded_clients = WPC()->members()->get_excluded_clients( 'archive' );
                    $excluded_ids = implode( "','", $excluded_clients );

                    $not_in = '';
                    if ( !empty( $excluded_ids ) ) {
                        $not_in  = " AND assign_id NOT IN ('{$excluded_ids}')";
                    }

                    $wpdb->query(
                        "DELETE
                            FROM {$wpdb->prefix}wpc_client_objects_assigns
                            WHERE object_type='{$object_type}' AND
                                object_id='{$object_id}' AND
                                assign_type='{$assign_type}' {$not_in}"
                    );
                } else {
                    $wpdb->delete(
                        "{$wpdb->prefix}wpc_client_objects_assigns",
                        array(
                            'object_type'   => $object_type,
                            'object_id'     => $object_id,
                            'assign_type'   => $assign_type,
                        )
                    );
                }
            }

            $assign_data = is_array( $assign_data ) ? $assign_data : array( $assign_data );

            if( 0 < count( $assign_data ) ) {
                $values = '';

                foreach( $assign_data as $assign_id ) {
                    if (  0 != $assign_id ) {
                        $values .= "( '$object_type', '$object_id', '$assign_type', '$assign_id' ),";
                    }
                }

                if ( !empty( $values ) ) {
                    $values = substr( $values, 0, -1 );
                    $wpdb->query( "INSERT INTO `{$wpdb->prefix}wpc_client_objects_assigns`(`object_type`,`object_id`,`assign_type`,`assign_id`) VALUES $values" );
                }
            }

        }

    }


    function set_reverse_assigned_data( $object_type, $object_data = array(), $assign_type = 'client', $assign_id ) {
        global $wpdb;

        if( isset( $object_type ) && !empty( $object_type ) && isset( $assign_id ) && !empty( $assign_id ) ) {

            $wpdb->delete(
                "{$wpdb->prefix}wpc_client_objects_assigns",
                array(
                    'object_type'   => $object_type,
                    'assign_type'   => $assign_type,
                    'assign_id'     => $assign_id,
                )
            );
            if( is_array( $object_data ) && 0 < count( $object_data ) ) {
                $values = '';
                foreach( $object_data as $object_id ) {
                    $values .= "( '$object_type', '$object_id', '$assign_type', '$assign_id' ),";
                }
                $values = substr( $values, 0, -1 );
                $wpdb->query( "INSERT INTO `{$wpdb->prefix}wpc_client_objects_assigns`(`object_type`,`object_id`,`assign_type`,`assign_id`) VALUES $values" );
            }

        }

    }


    /**
     * @param $object_type
     * @param $object_id
     * @param string $assign_type
     * @return array
     */
    function get_assign_data_by_object( $object_type, $object_id, $assign_type = 'client' ) {
        global $wpdb;

        if( isset( $object_type ) && !empty( $object_type ) && isset( $object_id ) && !empty( $object_id ) ) {

            $response = $wpdb->get_col( $wpdb->prepare(
                "SELECT assign_id
                    FROM {$wpdb->prefix}wpc_client_objects_assigns
                    WHERE object_type='%s' AND
                        object_id=%d AND
                        assign_type='%s'",
                $object_type,
                $object_id,
                $assign_type
            ) );

            if( 'client' == $assign_type && 0 < count( $response ) ) {
                $excluded_clients = WPC()->members()->get_excluded_clients( 'archive' );
                $response = array_diff( $response, $excluded_clients );
            }

            return !empty( $response ) ? $response : array();
        }
        return array();
    }


    function get_assign_data_by_assign( $object_type, $assign_type = 'client', $assign_id ) {
        global $wpdb;
        $assign_id = is_array( $assign_id ) ? $assign_id : array( $assign_id );

        if( 'client' == $assign_type && 0 < count( $assign_id ) ) {
            $excluded_clients = WPC()->members()->get_excluded_clients( 'archive' );
            $assign_id = array_diff( $assign_id, $excluded_clients );
        }

        $response = array();
        if( isset( $object_type ) && !empty( $object_type ) ) {

            if( 0 < count( $assign_id ) ) {
                $assign_id = implode( ',', $assign_id );

                $response = $wpdb->get_col( $wpdb->prepare(
                    "SELECT DISTINCT object_id
                        FROM {$wpdb->prefix}wpc_client_objects_assigns
                        WHERE object_type='%s' AND
                            assign_id IN(" . $assign_id . ") AND
                            assign_type='%s'",
                    $object_type,
                    $assign_type
                ) );
            }

            $response = array_unique( $response );

            return $response;
        }
        return array();
    }


    function get_assign_data_by_object_assign( $object_type, $assign_type = 'client' ) {
        global $wpdb;

        if( isset( $object_type ) && !empty( $object_type ) && isset( $assign_type ) && !empty( $assign_type ) ) {
            $response = $wpdb->get_col( $wpdb->prepare(
                "SELECT DISTINCT assign_id
                    FROM {$wpdb->prefix}wpc_client_objects_assigns
                    WHERE object_type='%s' AND
                        assign_type='%s'",
                $object_type,
                $assign_type
            ) );

            if( 'client' == $assign_type && 0 < count( $response ) ) {
                $excluded_clients = WPC()->members()->get_excluded_clients( 'archive' );
                $response = array_diff( $response, $excluded_clients );
            }

            return $response;
        }
        return array();
    }


    function delete_all_object_assigns( $object_type, $object_id ) {
        global $wpdb;

        if( isset( $object_type ) && !empty( $object_type ) && isset( $object_id ) && !empty( $object_id ) ) {

            $wpdb->delete(
                "{$wpdb->prefix}wpc_client_objects_assigns",
                array(
                    'object_type'   => $object_type,
                    'object_id'     => $object_id
                )
            );
        }
    }


    function delete_all_assign_assigns( $assign_type, $assign_id ) {
        global $wpdb;

        if( isset( $assign_type ) && !empty( $assign_type ) && isset( $assign_id ) && !empty( $assign_id ) ) {

            $wpdb->delete(
                "{$wpdb->prefix}wpc_client_objects_assigns",
                array(
                    'assign_type' => $assign_type,
                    'assign_id' => $assign_id
                )
            );
        }
    }


    function delete_assign_data_by_assign( $object_type, $assign_type, $assign_id ) {
        global $wpdb;

        if( isset( $assign_type ) && !empty( $assign_type ) && isset( $assign_id ) && !empty( $assign_id ) ) {

            $wpdb->delete(
                "{$wpdb->prefix}wpc_client_objects_assigns",
                array(
                    'object_type' => $object_type,
                    'assign_type' => $assign_type,
                    'assign_id' => $assign_id
                )
            );
        }
    }


    function delete_object_by_assign( $object_type, $object_id, $assign_type, $assign_id ) {
        global $wpdb;

        if( !empty( $object_id ) && !empty( $assign_id ) ) {

            $wpdb->delete(
                "{$wpdb->prefix}wpc_client_objects_assigns",
                array(
                    'object_type'   => $object_type,
                    'object_id'     => $object_id,
                    'assign_type'   => $assign_type,
                    'assign_id'     => $assign_id
                )
            );
        }
    }


    /**
     * Get Assign popup HTML
     *
     * @param $object
     * @param string $current_page
     * @param array $link_params
     * @param bool|false $input_params
     * @param array $additional_params
     * @param bool|true $echo
     *
     * @return string
     */
    function assign_popup( $object, $current_page = '', $link_params = array(), $input_params = false, $additional_params = array(), $echo = true ) {
        global $wpdb;

        $link_params_string = '';
        $input_params_string = '';
        if( !is_array( $link_params ) ) {
            if( empty( $link_params ) ) {
                $link_params = array();
            } else {
                $link_params = array( $link_params );
            }
        }
        if( !is_array( $input_params ) ) {
            if( empty( $input_params ) ) {
                $input_params = array();
            } else {
                $input_params = array( $input_params );
            }
        }
        if( !is_array( $additional_params ) ) {
            if( empty( $additional_params ) ) {
                $additional_params = array();
            } else {
                $additional_params = array( $additional_params );
            }
        }

        if( empty( $current_page ) ) {
            $current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';
        }

        if( isset( $input_params['value'] ) ) {
            if( is_string( $input_params['value'] ) ) {
                $input_value = array();
                if( !empty( $input_params['value'] ) ) {
                    $input_value = explode( ',', $input_params['value'] );
                }
                $input_params['value'] = $input_value;
            }
            $input_params['value'] = array_unique( $input_params['value'] );
        } else {
            $input_params['value'] = array();
        }

        if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) && ( $object == 'client' || $object == 'circle' ) ) {
            $all_assigned_to_manager = $this->get_assign_data_by_object( 'manager', get_current_user_id(), $object );

            if( 'client' == $object ) {
                $all_circles_assigned_to_manager = $this->get_assign_data_by_object( 'manager', get_current_user_id(), 'circle' );
                $clients_from_groups = array();
                if( is_array( $all_circles_assigned_to_manager ) && 0 < count( $all_circles_assigned_to_manager ) ) {
                    foreach( $all_circles_assigned_to_manager as $circle_id ) {
                        $clients_from_groups = array_merge( $clients_from_groups, WPC()->groups()->get_group_clients_id( $circle_id ) );
                    }
                }
                $all_assigned_to_manager = array_merge( $all_assigned_to_manager, $clients_from_groups );
                $all_assigned_to_manager = array_unique( $all_assigned_to_manager );
                $excluded_clients = WPC()->members()->get_excluded_clients( 'archive' );
                $all_assigned_to_manager = array_diff( $all_assigned_to_manager, $excluded_clients );
            }
            $input_params['value'] = array_intersect( $input_params['value'], $all_assigned_to_manager );
        }

        switch( $object ) {
            case 'client':
                $default_link_params = array(
                    'href'       => '#client_popup_block',
                    'data-input' => 'wpc_clients',
                    'class'      => 'wpc_fancybox_link',
                    'data-type'  => 'wpc_clients',
                    'data-ajax'  => 0
                );
                $link_params = array_merge( $default_link_params, $link_params );

                $default_additional_params = array(
                    'counter_value' => 0,
                    'only_link'     => 0,
                    'readonly'      => false
                );
                $additional_params = array_merge( $default_additional_params, $additional_params );
                $additional_params = array_merge( $additional_params, $link_params );

                $default_input_params = array(
                    'type'      => 'hidden',
                    'name'      => 'wpc_clients',
                    'id'        => 'wpc_clients',
                    'class'     => 'clients_field'
                );
                $input_params = array_merge( $default_input_params, $input_params );

                foreach( $link_params as $key=>$val ) {
                    if( $key != 'text' ) {
                        $link_params_string .= "$key=\"$val\"";
                    }
                }

                if( isset( $additional_params['readonly'] ) && $additional_params['readonly'] ) {
                    $input_params['readonly'] = 'readonly';
                }

                if( !empty( $input_params['value'] ) ) {
                    $input_params['value'] = $wpdb->get_col(
                        "SELECT u.ID
                                FROM {$wpdb->users} u, {$wpdb->usermeta} um
                                WHERE u.ID IN(" . implode( ',', $input_params['value'] ) . ") AND
                                    um.user_id = u.ID AND
                                    um.meta_key = '{$wpdb->prefix}capabilities' AND
                                    um.meta_value LIKE '%s:10:\"wpc_client\"%'"
                    );
                } else {
                    $input_params['value'] = array();
                }

                if( $this->get_assign_popup_input_type( $current_page ) == 'checkbox' && !( isset( $link_params['data-marks'] ) && $link_params['data-marks'] == 'radio' ) ) {
                    $additional_params['counter_value'] = count( $input_params['value'] );
                }

                $input_params['value'] = implode( ',', $input_params['value'] );

                foreach( $input_params as $key=>$val ) {
                    $input_params_string .= "$key=\"$val\"";
                }

                $html = '<span class="edit"><a ' . $link_params_string . '>' . ( isset( $link_params['text'] ) ? $link_params['text'] : __( 'Assign', WPC_CLIENT_TEXT_DOMAIN ) ) . '</a></span>';
                $html .= '<input ' . $input_params_string . ' />';
                $html .= '&nbsp;<span class="edit counter_' . ( isset( $input_params['id'] ) ? $input_params['id'] : ( isset( $additional_params['input_ref'] ) ? $additional_params['input_ref'] : '' ) ) . '">(' . ( isset( $additional_params['counter_value'] ) ? $additional_params['counter_value'] : '' ) . ')</span>';

                if( !$additional_params['only_link'] ) {
                    if( $echo ) {
                        echo $html;
                        if( !$this->popup_flags['client'] ) {
                            $this->get_assign_clients_popup( $current_page, $echo, $additional_params );
                            $this->popup_flags['client'] = true;
                        }
                    } else {
                        if( !$this->popup_flags['client'] ) {
                            $html .= $this->get_assign_clients_popup( $current_page, $echo, $additional_params );
                            $this->popup_flags['client'] = true;
                        }
                        return $html;
                    }
                } else {
                    if( $echo ) {
                        echo $html;
                    } else {
                        return $html;
                    }
                }

                break;
            case 'circle':
                $default_link_params = array(
                    'href'       => '#circle_popup_block',
                    'data-input' => 'wpc_circles',
                    'class'      => 'wpc_fancybox_link',
                    'data-type'  => 'wpc_circles',
                    'data-ajax'  => 0
                );
                $link_params = array_merge( $default_link_params, $link_params );

                $default_input_params = array(
                    'type'      => 'hidden',
                    'name'      => 'wpc_circles',
                    'id'        => 'wpc_circles',
                    'class'     => 'circles_field'
                );
                $input_params = array_merge( $default_input_params, $input_params );

                $default_additional_params = array(
                    'counter_value' => 0,
                    'only_link'     => 0,
                    'readonly'      => false
                );
                $additional_params = array_merge( $default_additional_params, $additional_params );
                $additional_params = array_merge( $additional_params, $link_params );

                if( isset( $additional_params['readonly'] ) && $additional_params['readonly'] ) {
                    $input_params['readonly'] = 'readonly';
                }

                $link_params_string = '';
                foreach( $link_params as $key=>$val ) {
                    if( $key != 'text' ) {
                        $link_params_string .= "$key=\"$val\" ";
                    }
                }

                if( $this->get_assign_popup_input_type( $current_page ) == 'checkbox' ) {
                    $additional_params['counter_value'] = count( $input_params['value'] );
                }

                $input_params['value'] = implode( ',', $input_params['value'] );

                $input_params_string = '';
                foreach( $input_params as $key=>$val ) {
                    $input_params_string .= "$key=\"$val\" ";
                }

                $html = '<span class="edit"><a ' . $link_params_string . '>' . ( isset( $link_params['text'] ) ? $link_params['text'] : __( 'Assign', WPC_CLIENT_TEXT_DOMAIN ) ) . '</a></span>';
                $html .= '<input ' . $input_params_string . ' />';
                $html .= '&nbsp;<span class="edit counter_' . ( isset( $input_params['id'] ) ? $input_params['id'] : ( isset( $additional_params['input_ref'] ) ? $additional_params['input_ref'] : '' ) ) . '">(' . ( isset( $additional_params['counter_value'] ) ? $additional_params['counter_value'] : '' ) . ')</span>';

                if( !$additional_params['only_link'] ) {
                    if( $echo ) {
                        echo $html;
                        if( !$this->popup_flags['circle'] ) {
                            $this->get_assign_circles_popup( $current_page, $echo, $additional_params );
                            $this->popup_flags['circle'] = true;
                        }
                    } else {
                        if( !$this->popup_flags['circle'] ) {
                            $html .= $this->get_assign_circles_popup( $current_page, $echo, $additional_params );
                            $this->popup_flags['circle'] = true;
                        }
                        return $html;
                    }
                } else {
                    if( $echo ) {
                        echo $html;
                    } else {
                        return $html;
                    }
                }

                break;
            case 'manager':
                $default_link_params = array(
                    'href'       => '#manager_popup_block',
                    'data-input' => 'wpc_managers',
                    'class'      => 'wpc_fancybox_link',
                    'data-type'  => 'wpc_managers',
                    'data-ajax'  => 0
                );
                $link_params = array_merge( $default_link_params, $link_params );

                $default_input_params = array(
                    'type'       => 'hidden',
                    'name'       => 'wpc_managers',
                    'id'         => 'wpc_managers',
                    'class'      => 'managers_field'
                );
                $input_params = array_merge( $default_input_params, $input_params );

                $default_additional_params = array(
                    'counter_value' => 0,
                    'only_link'     => 0,
                    'readonly'      => false
                );
                $additional_params = array_merge( $default_additional_params, $additional_params );
                $additional_params = array_merge( $additional_params, $link_params );

                if( !empty( $input_params['value'] ) ) {
                    $input_params['value'] = $wpdb->get_col(
                        "SELECT u.ID
                            FROM {$wpdb->users} u, {$wpdb->usermeta} um
                            WHERE u.ID IN(" . implode( ',', $input_params['value'] ) . ") AND
                                um.user_id = u.ID AND
                                um.meta_key = '{$wpdb->prefix}capabilities' AND
                                um.meta_value LIKE '%s:11:\"wpc_manager\"%'"
                    );
                } else {
                    $input_params['value'] = array();
                }

                $link_params_string = '';
                foreach( $link_params as $key=>$val ) {
                    if( $key != 'text' ) {
                        $link_params_string .= "$key=\"$val\"";
                    }
                }

                if( $this->get_assign_popup_input_type( $current_page ) == 'checkbox' ) {
                    $additional_params['counter_value'] = count( $input_params['value'] );
                }

                $input_params['value'] = implode( ',', $input_params['value'] );

                $input_params_string = '';
                foreach( $input_params as $key=>$val ) {
                    $input_params_string .= "$key=\"$val\"";
                }

                $html = '<span class="edit"><a ' . $link_params_string . '>' . ( isset( $link_params['text'] ) ? $link_params['text'] : __( 'Assign', WPC_CLIENT_TEXT_DOMAIN ) ) . '</a></span>';
                $html .= '<input ' . $input_params_string . ' />';
                $html .= '&nbsp;<span class="edit counter_' . ( isset( $input_params['id'] ) ? $input_params['id'] : ( isset( $additional_params['input_ref'] ) ? $additional_params['input_ref'] : '' ) ) . '">(' . ( isset( $additional_params['counter_value'] ) ? $additional_params['counter_value'] : '' ) . ')</span>';

                if( !$additional_params['only_link'] ) {
                    if( $echo ) {
                        echo $html;
                        if( !$this->popup_flags['manager'] ) {
                            $this->get_assign_managers_popup( $current_page, $echo, $additional_params );
                            $this->popup_flags['manager'] = true;
                        }
                    } else {
                        if( !$this->popup_flags['manager'] ) {
                            $html .= $this->get_assign_managers_popup( $current_page, $echo, $additional_params );
                            $this->popup_flags['manager'] = true;
                        }
                        return $html;
                    }
                } else {
                    if( $echo ) {
                        echo $html;
                    } else {
                        return $html;
                    }
                }
                break;
            default:
                if( is_array( $input_params['value'] ) ) {
                    $input_value = '';
                    if( count( $input_params['value'] ) ) {
                        $input_value = implode( ',', $input_params['value'] );
                    }
                    $input_params['value'] = $input_value;
                }

                do_action( 'wpc_assign_' . $object . '_popup', $current_page, $link_params, $input_params, $additional_params, $echo );
                break;
        }

        return '';
    }


    /**
     * Display assign circles popup
     **/
    function get_assign_circles_popup( $current_page = '', $echo = true, $params = array() ) {
        //shutterbox init
        wp_enqueue_script('wpc-shutter-box-script');
        wp_enqueue_style('wpc-shutter-box-style');

        wp_enqueue_script( 'wpc-new-assign-popup-js', false, array(), WPC_CLIENT_VER, true );

        switch( $current_page ) {
            case 'add_client':
                $title = sprintf( __( 'Assign %s To %s:', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'], WPC()->custom_titles['client']['s'] );
                break;
            case 'add_client_page':
                $title = sprintf( __( 'Assign %s To $s:', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'], WPC()->custom_titles['portal_page']['s'] );
                break;
            case 'wpclients_galleries':
                $title = sprintf( __( 'Assign %s To Gallery:', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'] );
                break;
            case 'wpclients_gallery_categories':
                $title = sprintf( __( 'Assign %s To Gallery Category:', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'] );
                break;
            case 'wpclients_files':
                $title = sprintf( __( 'Assign %s To File:', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] );
                break;
            case 'wpclients_filescat':
                $title = sprintf( __( 'Assign %s To File Category:', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] );
                break;
            case 'wpclientspage_categories':
                $title = sprintf( __( 'Assign %s To %s Category:', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'], WPC()->custom_titles['portal_page']['s'] );
                break;
            default:
                $title = '';
                $title = apply_filters( 'wpc_assign_circle_popup_custom_title', $title, $current_page, $params['data-type'] );
                if( empty( $title ) ) {
                    if (isset($params['readonly']) && $params['readonly']) {
                        $title = sprintf(__('View %s:', WPC_CLIENT_TEXT_DOMAIN), WPC()->custom_titles['circle']['p']);
                    } else {
                        $title = sprintf(__('Assign %s:', WPC_CLIENT_TEXT_DOMAIN), WPC()->custom_titles['circle']['p']);
                    }
                }
                break;
        }

        $localize_array = array(
            'l10n_print_after' => "
                    if( typeof wpc_popup_title == 'undefined' ) {
                        var wpc_popup_title = new Array();
                    }
                    if( typeof wpc_popup_title.circle_popup_block == 'undefined' ) {
                        wpc_popup_title.circle_popup_block = new Array();
                    }
                    wpc_popup_title.circle_popup_block." . $params['data-type'] . " = wpc_popup_var.data",
            'data'         => $title,
            'current_page' => $current_page,
            'search_text'  => __( 'Search', WPC_CLIENT_TEXT_DOMAIN ),
            'admin_url'    => get_admin_url()
        );
        if( isset( $params['wpc_ajax_prefix'] ) && !empty( $params['wpc_ajax_prefix'] ) ) {
            $localize_array['wpc_ajax_prefix'] = $params['wpc_ajax_prefix'];
        }
        wp_localize_script( "wpc-new-assign-popup-js", 'wpc_popup_var', $localize_array );

        ob_start(); ?>

        <div style="display: none;">
            <div id="circle_popup_block" class="wpc_assign_popup <?php echo ( !empty( $params['additional_classes'] ) ) ? $params['additional_classes'] : '' ?>" style="clear: both;float:left;width:100%;">
                <div class="wpc_assign_popup_nav">
                    <div class="wpc_assign_popup_filters">
                        <input type="text" class="wpc_search_field" name="wpc_search_circles" value="<?php _e( 'Search', WPC_CLIENT_TEXT_DOMAIN ) ?>" onfocus="if (this.value=='<?php _e( 'Search', WPC_CLIENT_TEXT_DOMAIN ) ?>') this.value='';" onblur="if (this.value==''){this.value='<?php _e( 'Search', WPC_CLIENT_TEXT_DOMAIN ) ?>'}" style="float:right;width:calc( 50% - 10px );margin:0 0 0 10px;padding:0;height: 28px;" />
                        <select name="wpc_order_circles" class="wpc_order" style="float:right;width:50%;margin:0;padding:0;">
                            <option value="show_asc"><?php _e( 'A to Z', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="show_desc"><?php _e( 'Z to A', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="date_asc"><?php _e( 'Date Added (Recent to Earlier)', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="date_desc"><?php _e( 'Date Added (Earlier to Recent)', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="first_asc"><?php _e( 'Assigned show first', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        </select>
                    </div>
                    <div class="wpc_assign_popup_bulk_select">
                            <span class="description" style="float:left;width:50%;margin:0;padding:0;line-height: 28px;">
                                <label>
                                    <input type="checkbox" <?php if( isset( $params['readonly'] ) && $params['readonly'] ) { ?>disabled="disabled"<?php } ?> class="wpc_select_all_at_page" name="wpc_select_all_at_page_circles" id="wpc_select_all_at_page_circles" value="1" />
                                    <?php _e( 'Select all on this page.', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                </label>
                            </span>
                            <span class="description" style="float:left;width:50%;margin:0;padding:0;line-height: 28px;">
                                <label>
                                    <input type="checkbox" <?php if( isset( $params['readonly'] ) && $params['readonly'] ) { ?>disabled="disabled"<?php } ?> class="wpc_select_all" name="wpc_select_all_circles" id="select_all_circles" value="1" />
                                    <?php _e( 'Select All.', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                </label>
                            </span>
                    </div>
                </div>
                <div class="wpc_inside"></div>
                <div class="wpc_assign_popup_after_list"></div>
                <div class="wpc_assign_popup_pagination">
                    <a href="javascript:void(0);" rel="first" class="wpc_pagination_links"><<</a>&nbsp;&nbsp;
                    <a href="javascript:void(0);" rel="prev" class="wpc_pagination_links"><</a>&nbsp;&nbsp;
                    <span class="wpc_page_num">1</span>&nbsp;&nbsp;
                    <a href="javascript:void(0);" rel="next" class="wpc_pagination_links">></a>&nbsp;&nbsp;
                    <a href="javascript:void(0);" rel="last" class="wpc_pagination_links">>></a>
                    <div class="wpc_popup_statistic">
                        <span class="wpc_total_count">0</span> <?php _e( 'item(s).', WPC_CLIENT_TEXT_DOMAIN ) ?> <span class="wpc_selected_count">0</span> <?php _e( 'item(s) was selected.', WPC_CLIENT_TEXT_DOMAIN ) ?>
                    </div>
                </div>
                <div class="wpc_assign_popup_buttons">
                    <div style="width:150px; margin:0 auto;">
                        <?php if( isset( $params['readonly'] ) && $params['readonly'] ) { ?>
                            <input type="button" name="wpc_cancel" class="wpc_cancel_popup button" value="<?php _e( 'Close', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                        <?php } else { ?>
                            <input type="button" name="wpc_ok" value="<?php _e( 'Ok', WPC_CLIENT_TEXT_DOMAIN ) ?>" class="wpc_ok_popup button-primary" />
                            <input type="button" name="wpc_cancel" class="wpc_cancel_popup button" value="<?php _e( 'Cancel', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $out = ob_get_contents();

        ob_end_clean();
        if( $echo ) {
            echo $out;
        } else {
            return $out;
        }

        return '';
    }


    /**
     * Display assign circles popup
     **/
    function get_assign_managers_popup( $current_page = '', $echo = true, $params = array() ) {
        //shutterbox init
        wp_enqueue_script('wpc-shutter-box-script');
        wp_enqueue_style('wpc-shutter-box-style');

        wp_enqueue_script( 'wpc-new-assign-popup-js', false, array(), WPC_CLIENT_VER, true );

        switch( $current_page ) {
            case 'add_client':
                $title = sprintf( __( 'Assign %s To %s:', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'], WPC()->custom_titles['client']['s'] );
                break;
            case 'add_client_page':
                $title = sprintf( __( 'Assign %s To %s:', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'], WPC()->custom_titles['portal_page']['s'] );
                break;
            case 'wpclients_files':
                $title = sprintf( __( 'Assign %s To File:', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] );
                break;
            case 'wpclients_filescat':
                $title = sprintf( __( 'Assign %s To File Category:', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] );
                break;
            case 'wpclientspage_categories':
                $title = sprintf( __( 'Assign %s To %s Category:', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'], WPC()->custom_titles['portal_page']['s'] );
                break;
            default:
                $title = '';
                $title = apply_filters( 'wpc_assign_manager_popup_custom_title', $title, $current_page, $params['data-type'] );

                if( empty( $title ) ) {
                    $title = sprintf(__('Assign %s:', WPC_CLIENT_TEXT_DOMAIN), WPC()->custom_titles['manager']['p']);
                }
                break;
        }
        $localize_array = array(
            'l10n_print_after' => "
                    if( typeof wpc_popup_title == 'undefined' ) {
                        var wpc_popup_title = new Array();
                    }
                    if( typeof wpc_popup_title.manager_popup_block == 'undefined' ) {
                        wpc_popup_title.manager_popup_block = new Array();
                    }
                    wpc_popup_title.manager_popup_block." . $params['data-type'] . " = wpc_popup_var.data",
            'data'         => $title,
            'current_page' => $current_page,
            'search_text'  => __( 'Search', WPC_CLIENT_TEXT_DOMAIN ),
            'admin_url'    => get_admin_url()
        );
        if( isset( $params['wpc_ajax_prefix'] ) && !empty( $params['wpc_ajax_prefix'] ) ) {
            $localize_array['wpc_ajax_prefix'] = $params['wpc_ajax_prefix'];
        }
        wp_localize_script( "wpc-new-assign-popup-js", 'wpc_popup_var', $localize_array );
        ob_start(); ?>

        <div style="display: none;">
            <div id="manager_popup_block" class="wpc_assign_popup <?php echo ( !empty( $params['additional_classes'] ) ) ? $params['additional_classes'] : '' ?>" style="clear: both;float:left;width:100%;">
                <div class="wpc_assign_popup_nav">
                    <div class="wpc_assign_popup_filters">
                        <select name="wpc_show_managers" class="wpc_show" style="float:left;width:33%;margin:0;padding:0;">
                            <option value="user_login"><?php _e( 'Username', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="user_nicename"><?php _e( 'Contact Name', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        </select>
                        <select name="wpc_order_managers" class="wpc_order" style="float:left;width:calc( 33% - 10px );margin:0 0 0 10px;padding:0;">
                            <option value="show_asc"><?php _e( 'A to Z', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="show_desc"><?php _e( 'Z to A', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="date_asc"><?php _e( 'Date Added (Recent to Earlier)', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="date_desc"><?php _e( 'Date Added (Earlier to Recent)', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="first_asc"><?php _e( 'Assigned show first', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        </select>
                        <input type="text" class="wpc_search_field" name="wpc_search_managers" value="<?php _e( 'Search', WPC_CLIENT_TEXT_DOMAIN ) ?>" onfocus="if (this.value=='<?php _e( 'Search', WPC_CLIENT_TEXT_DOMAIN ) ?>') this.value='';" onblur="if (this.value==''){this.value='<?php _e( 'Search', WPC_CLIENT_TEXT_DOMAIN ) ?>'}" style="float:left;width:calc( 33% - 10px );margin:0 0 0 10px;padding:0;height:28px;"/>
                    </div>
                    <div class="wpc_assign_popup_bulk_select">
                            <span class="description" style="float:left;width:50%;margin:0;padding:0;line-height: 28px;">
                                <label>
                                    <input type="checkbox" <?php if( isset( $params['readonly'] ) && $params['readonly'] ) { ?>disabled="disabled"<?php } ?> class="wpc_select_all_at_page" name="wpc_select_all_at_page_circles" id="wpc_select_all_at_page_circles" value="1" />
                                    <?php _e( 'Select all on this page.', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                </label>
                            </span>
                            <span class="description" style="float:left;width:50%;margin:0;padding:0;line-height: 28px;">
                                <label>
                                    <input type="checkbox" <?php if( isset( $params['readonly'] ) && $params['readonly'] ) { ?>disabled="disabled"<?php } ?> class="wpc_select_all" name="wpc_select_all_circles" id="select_all_circles" value="1" />
                                    <?php _e( 'Select All.', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                </label>
                            </span>
                    </div>
                </div>
                <div class="wpc_inside"></div>
                <div class="wpc_assign_popup_after_list"></div>
                <div class="wpc_assign_popup_pagination">
                    <a href="javascript:void(0);" rel="first" class="wpc_pagination_links"><<</a>&nbsp;&nbsp;
                    <a href="javascript:void(0);" rel="prev" class="wpc_pagination_links"><</a>&nbsp;&nbsp;
                    <span class="wpc_page_num">1</span>&nbsp;&nbsp;
                    <a href="javascript:void(0);" rel="next" class="wpc_pagination_links">></a>&nbsp;&nbsp;
                    <a href="javascript:void(0);" rel="last" class="wpc_pagination_links">>></a>
                    <div class="wpc_popup_statistic">
                        <span class="wpc_total_count">0</span> <?php _e( 'item(s).', WPC_CLIENT_TEXT_DOMAIN ) ?> <span class="wpc_selected_count">0</span> <?php _e( 'item(s) was selected.', WPC_CLIENT_TEXT_DOMAIN ) ?>
                    </div>
                </div>
                <div class="wpc_assign_popup_buttons">
                    <div style="width:150px; margin:0 auto;">
                        <input type="button" name="wpc_ok" value="<?php _e( 'Ok', WPC_CLIENT_TEXT_DOMAIN ) ?>" class="wpc_ok_popup button-primary" />
                        <input type="button" name="wpc_cancel" class="wpc_cancel_popup button" value="<?php _e( 'Cancel', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                    </div>
                </div>
            </div>
        </div>

        <?php $out = ob_get_contents();

        ob_end_clean();
        if( $echo ) {
            echo $out;
        } else {
            return $out;
        }

        return '';
    }



    function get_assign_popup_input_type( $current_page ) {
        if( $current_page == 'wpclients_staff_edit' ) {
            $input_type = 'radio';
        } else {
            $input_type = 'checkbox';
        }
        return $input_type;
    }


    /**
     * Display assign client popup
     **/
    function get_assign_clients_popup( $current_page = '', $echo = true, $params = array() ) {
        //shutterbox init
        wp_enqueue_script('wpc-shutter-box-script');
        wp_enqueue_style('wpc-shutter-box-style');

        wp_enqueue_script( 'wpc-new-assign-popup-js', false, array('jquery'), WPC_CLIENT_VER, true );

        $input_type = $this->get_assign_popup_input_type( $current_page );

        switch( $current_page ) {
            case 'wpclients_managers':
                $title = sprintf( __( 'Assign %s To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['manager']['s'] );
                break;
            case 'wpclients_groups':
                $title = sprintf( __( 'Assign %s To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['circle']['s'] );
                break;
            case 'add_client_page':
                $title = sprintf( __( 'Assign %s To %s:', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['portal_page']['s'] );
                break;
            case 'wpclients_galleries':
                $title = sprintf( __( 'Assign %s To Gallery:', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] );
                break;
            case 'wpclients_gallery_categories':
                $title = sprintf( __( 'Assign %s To Gallery Category:', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] );
                break;
            case 'wpclients_files':
                $title = sprintf( __( 'Assign %s To File:', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] );
                break;
            case 'wpclients_filescat':
                $title = sprintf( __( 'Assign %s To File Category:', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] );
                break;
            case 'wpclientspage_categories':
                $title = sprintf( __( 'Assign %s To %s Category:', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['portal_page']['s'] );
                break;
            default:
                $title = '';
                $title = apply_filters( 'wpc_assign_client_popup_custom_title', $title, $current_page );

                if( empty( $title ) ) {
                    if( isset( $params['readonly'] ) && $params['readonly'] ) {
                        $title = sprintf( __( 'View %s:', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] );
                    } else {
                        $title = sprintf( __( 'Assign %s:', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] );
                    }
                }
                break;
        }

        $localize_array = array(
            'l10n_print_after' => "
                    if( typeof wpc_popup_title == 'undefined' ) {
                        var wpc_popup_title = new Array();
                    }
                    if( typeof wpc_popup_title.client_popup_block == 'undefined' ) {
                        wpc_popup_title.client_popup_block = new Array();
                    }
                    wpc_popup_title.client_popup_block." . $params['data-type'] . " = wpc_popup_var.data",
            'data'         => $title,
            'current_page' => $current_page,
            'search_text'  => __( 'Search', WPC_CLIENT_TEXT_DOMAIN ),
            'admin_url'    => get_admin_url()
        );
        if( isset( $params['wpc_ajax_prefix'] ) && !empty( $params['wpc_ajax_prefix'] ) ) {
            $localize_array['wpc_ajax_prefix'] = $params['wpc_ajax_prefix'];
        }
        wp_localize_script( "wpc-new-assign-popup-js", 'wpc_popup_var', $localize_array );

        ob_start(); ?>

        <div style="display: none;">
            <div id="client_popup_block" class="wpc_assign_popup <?php echo ( !empty( $params['additional_classes'] ) ) ? $params['additional_classes'] : '' ?>" style="clear: both;float:left;width:100%;">
                <div class="wpc_assign_popup_nav">
                    <div class="wpc_assign_popup_filters">
                        <select name="wpc_show" class="wpc_show" style="float:left;width:33%;margin:0;padding:0;">
                            <option value="user_login"><?php _e( 'Username', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="display_name"><?php _e( 'Contact Name', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="wpc_cl_business_name"><?php _e( 'Business Name', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        </select>
                        <select name="wpc_order_clients" class="wpc_order" style="float:left;width:calc( 33% - 10px );margin:0 0 0 10px;padding:0;">
                            <option value="show_asc"><?php _e( 'A to Z', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="show_desc"><?php _e( 'Z to A', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="date_asc"><?php _e( 'Date Added (Recent to Earlier)', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="date_desc"><?php _e( 'Date Added (Earlier to Recent)', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="first_asc"><?php _e( 'Assigned show first', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        </select>
                        <input type="text" class="wpc_search_field" name="wpc_search_clients" value="<?php _e( 'Search', WPC_CLIENT_TEXT_DOMAIN ) ?>" onfocus="if (this.value=='<?php _e( 'Search', WPC_CLIENT_TEXT_DOMAIN ) ?>') this.value='';" onblur="if (this.value==''){this.value='<?php _e( 'Search', WPC_CLIENT_TEXT_DOMAIN ) ?>'}" style="float:left;width:calc( 33% - 10px );margin:0 0 0 10px;padding:0;height: 28px;"/>
                    </div>
                    <div class="wpc_assign_popup_bulk_select">
                        <?php if( $input_type == 'checkbox' ) { ?>
                            <span class="description" style="float:left;width:50%;margin:0;padding:0;line-height: 28px;">
                                    <label>
                                        <input type="checkbox" <?php if( isset( $params['readonly'] ) && $params['readonly'] ) { ?>disabled="disabled"<?php } ?> class="wpc_select_all_at_page" name="wpc_select_all_at_page_clients" id="wpc_select_all_at_page_clients" value="1" />
                                        <?php _e( 'Select all on this page.', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                    </label>
                                </span>
                            <span class="description" style="float:left;width:50%;margin:0;padding:0;line-height: 28px;">
                                    <label>
                                        <input type="checkbox" <?php if( isset( $params['readonly'] ) && $params['readonly'] ) { ?>disabled="disabled"<?php } ?> class="wpc_select_all" name="wpc_select_all_clients" id="wpc_select_all_clients" value="1" />
                                        <?php _e( 'Select All.', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                    </label>
                                </span>
                        <?php } ?>
                    </div>
                </div>
                <div class="wpc_inside"></div>
                <div class="wpc_assign_popup_after_list"></div>
                <div class="wpc_assign_popup_pagination">
                    <a href="javascript:void(0);" rel="first" class="wpc_pagination_links"><<</a>&nbsp;&nbsp;
                    <a href="javascript:void(0);" rel="prev" class="wpc_pagination_links"><</a>&nbsp;&nbsp;
                    <span class="wpc_page_num">1</span>&nbsp;&nbsp;
                    <a href="javascript:void(0);" rel="next" class="wpc_pagination_links">></a>&nbsp;&nbsp;
                    <a href="javascript:void(0);" rel="last" class="wpc_pagination_links">>></a>

                    <?php if( $input_type == 'checkbox' ) { ?>
                        <div class="wpc_popup_statistic">
                            <span class="wpc_total_count">0</span> <?php _e( 'item(s).', WPC_CLIENT_TEXT_DOMAIN ) ?> <span class="wpc_selected_count">0</span> <?php _e( 'item(s) was selected.', WPC_CLIENT_TEXT_DOMAIN ) ?>
                        </div>
                    <?php } ?>
                </div>
                <div class="wpc_assign_popup_buttons">
                    <div style="width:150px; margin:0 auto;">
                        <?php if( isset( $params['readonly'] ) && $params['readonly'] ) { ?>
                            <input type="button" name="wpc_cancel" class="wpc_cancel_popup button" value="<?php _e( 'Close', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                        <?php } else { ?>
                            <input type="button" name="wpc_ok" value="<?php _e( 'Ok', WPC_CLIENT_TEXT_DOMAIN ) ?>" class="wpc_ok_popup button-primary" />
                            <input type="button" name="wpc_cancel" class="wpc_cancel_popup button" value="<?php _e( 'Cancel', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <?php $out = ob_get_contents();

        ob_end_clean();
        if( $echo ) {
            echo $out;
        } else {
            return $out;
        }

        return '';
    }


    /**
     * AJAX popup pagination
     **/
    //todo
    function ajax_get_popup_pagination_data( $datatype = '', $cur_page = '1', $goto = 'first' ) {
        global $wpdb;
        $per_page = 50;
        $new_page = 1;
        $limit = '';
        $buttons = array('first' => true, 'prev' => true, 'next' => true, 'last' =>true);
        $open_popup = isset( $_POST['open_popup'] ) && $_POST['open_popup'];

        if ( !empty( $_POST['data_type'] ) || !empty( $datatype ) ) {
            $type = ( isset( $_POST['data_type'] ) && !empty( $_POST['data_type'] ) ) ? $_POST['data_type'] : $datatype;
            $cur_page = ( isset( $_POST['page'] ) && !empty( $_POST['page'] ) ) ? $_POST['page'] : $cur_page;
            $marks_type = ( isset( $_POST['marks_type'] ) && in_array( $_POST['marks_type'], array( 'checkbox', 'radio' ) ) ) ? $_POST['marks_type'] : 'checkbox';

            $send_ajax = ( isset( $_POST['send_ajax'] ) && !empty( $_POST['send_ajax'] ) ) ? $_POST['send_ajax'] : 0;
            $input_ref = ( isset( $_POST['input_ref'] ) && !empty( $_POST['input_ref'] ) ) ? $_POST['input_ref'] : '';
            $current_page = ( isset( $_POST['current_page'] ) && !empty( $_POST['current_page'] ) ) ? $_POST['current_page'] : '';
            $readonly = ( isset( $_POST['readonly'] ) && $_POST['readonly'] );

            if ( 'wpc_clients' != $type && false !== strpos( $type, 'wpc_clients' ) ) {
                $type = "wpc_clients";
            } else if( 'wpc_circles' != $type && false !== strpos( $type, 'wpc_circles' ) ) {
                $type = "wpc_circles";
            } else if( 'wpc_managers' != $type && false !== strpos( $type, 'wpc_managers' ) ) {
                $type = "wpc_managers";
            }

            $display_meta = get_user_meta( get_current_user_id(), 'wpc_assign_popup_' . $type . '_display', true );

            //get order meta data and update if it was changed
            $order_meta = get_user_meta( get_current_user_id(), 'wpc_assign_popup_' . $type . '_order', true );
            $order_meta = empty( $order_meta ) ? 'show_asc' : $order_meta;
            if ( !empty( $_POST['order'] ) && $_POST['order'] != $order_meta ) {
                $order_meta = $_POST['order'];
                update_user_meta( get_current_user_id(), 'wpc_assign_popup_' . $type . '_order', $order_meta );
            }

            if ( $open_popup ) {
                $block_array = apply_filters( 'wpc_assign_popup_add_blocks', array(), array(
                    'data_type'     => $type,
                    'page'          => $cur_page,
                    'marks_type'    => $marks_type,
                    'send_ajax'     => $send_ajax,
                    'input_ref'     => $input_ref,
                    'current_page'  => $current_page
                ) );
            } else {
                $block_array = array();
            }

            $wpdb->query( "SET SESSION SQL_BIG_SELECTS=1" );

            switch( $type ) {
                case 'wpc_clients': case 'send_wpc_clients': case 'wpc_clients_return': {
                //update display meta if it was changed
                $display_meta = empty( $display_meta ) ? 'user_login' : $display_meta;
                if( !empty( $_POST['display'] ) && $_POST['display'] != $display_meta ) {
                    $display_meta = $_POST['display'];
                    update_user_meta( get_current_user_id(), 'wpc_assign_popup_' . $type . '_display', $display_meta );
                }

                //default values for displaying
                $display = $display_meta;
                $display_meta_key = '';
                if ( !in_array( $display, array( 'user_login', 'display_name' ) ) ) {
                    $display_meta_key = $display;
                    $display = 'um.meta_value';
                } else {
                    $display = "u." . $display;
                }

                //default values for order
                $order_type = 'u.user_login';
                $order = "ASC";

                $temp_order_array = explode( "_", $order_meta );
                if ( 2 == count( $temp_order_array ) ) {
                    if ( in_array( strtolower( $temp_order_array[1] ), array( 'asc', 'desc' ) ) && in_array( strtolower( $temp_order_array[0] ), array( 'show', 'date', 'first' ) ) ) {
                        switch ( $temp_order_array[0] ) {
                            case 'show':
                                $order_type = $display;
                                break;
                            case 'date':
                                $order_type = 'u.user_registered';
                                break;
                            case 'first':
                                $order_type = 'u.user_login';
                                break;
                        }

                        $order = $temp_order_array[1];
                    }
                }

                $order_by_sql = $order_type . " " . $order;

                $included_ids = '';
                if ( isset( $_POST['included_ids'] ) && !empty( $_POST['included_ids'] ) ) {
                    $included_ids = explode( ',', $_POST['included_ids'] );
                    $included_ids = " AND u.ID IN ('" . implode( "','", $included_ids ) . "')";
                }

                $where = '';
                $excluded_clients = WPC()->members()->get_excluded_clients();
                if ( is_array( $excluded_clients ) && count( $excluded_clients ) ) {
                    $where .= " AND u.ID NOT IN (" . implode( ",", $excluded_clients ) . ")";
                }

                if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                    $clients = WPC()->members()->get_all_clients_manager();
                    $where .= " AND u.ID IN ('" . implode( "','", $clients ) . "')";
                }

                $prepared_search = '';
                if ( isset( $_POST['search'] ) && !empty( $_POST['search'] ) ) {
                    $prepared_search = WPC()->admin()->get_prepared_search( $_POST['search'], array(
                        'u.ID',
                        'u.user_login',
                        'u.user_email',
                        'u.user_nicename',
                        'u.display_name',
                        array(
                            'table' => 'um2',
                            'meta_key' => 'wpc_cl_business_name',
                            'meta_value' => 'CAST( um2.meta_value AS CHAR )'
                        ),
                        array(
                            'table' => 'um2',
                            'meta_key' => 'nickname',
                            'meta_value' => 'CAST( um2.meta_value AS CHAR )'
                        )
                    ) );
                }

                //get clients count
                if ( !empty( $_POST['search'] ) ) {
                    $sql = "SELECT DISTINCT u.ID
                                    FROM {$wpdb->users} u
                                    LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
                                    LEFT JOIN {$wpdb->usermeta} um2 ON u.ID = um2.user_id
                                    WHERE um.meta_key = '{$wpdb->prefix}capabilities' AND
                                          CAST( um.meta_value AS CHAR ) LIKE '%\"wpc_client\"%'
                                          $prepared_search
                                          $included_ids
                                          $where";
                } else {
                    $sql = "SELECT DISTINCT u.ID
                                    FROM {$wpdb->users} u
                                    LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
                                    WHERE um.meta_key = '{$wpdb->prefix}capabilities' AND
                                          CAST( um.meta_value AS CHAR ) LIKE '%\"wpc_client\"%'
                                          $included_ids
                                          $where";
                }

                $wpc_ids_array = $wpdb->get_col( $sql );
                $clients_count = count( $wpc_ids_array );


                if ( $clients_count > 0 ) {
                    //init pagination data
                    if ( $clients_count > $per_page ) {
                        $goto = !empty( $_POST['goto'] ) ? $_POST['goto'] : $goto;

                        switch ( $goto ) {
                            case 'first':
                                $offset = 0;
                                $new_page = 1;
                                $buttons['first'] = false;
                                $buttons['prev'] = false;
                                break;
                            case 'prev':
                                $offset = ( $cur_page - 2 ) * $per_page;
                                $new_page = $cur_page - 1;
                                if ( $new_page <= 1 ) {
                                    $buttons['first'] = false;
                                    $buttons['prev'] = false;
                                    $new_page = 1;
                                }
                                break;
                            case 'next':
                                $last_page = ceil( $clients_count / $per_page );
                                $offset = $cur_page * $per_page;
                                $new_page = (int) $cur_page + 1;
                                if ( $new_page >= $last_page ) {
                                    $buttons['next'] = false;
                                    $buttons['last'] = false;
                                    $new_page = $last_page;
                                }
                                break;
                            case 'last':
                                $last_page = ceil( $clients_count / $per_page );
                                $offset = ( $last_page - 1 ) * $per_page;
                                $new_page = $last_page;
                                $buttons['next'] = false;
                                $buttons['last'] = false;
                                break;
                            default:
                                $offset = 0;
                                $new_page = 1;
                                $buttons['first'] = false;
                                $buttons['prev'] = false;
                                break;
                        }

                        $limit = "LIMIT $offset, $per_page";
                    } else {
                        $buttons = array( 'first' => false, 'prev' => false, 'next' => false, 'last' =>false );
                    }

                    //left join meta value field if need to display it
                    $sql_inner_part = '';
                    if ( 'um.meta_value' == $display )
                        $sql_inner_part = "LEFT JOIN {$wpdb->usermeta} um ON ( u.ID = um.user_id AND um.meta_key = '$display_meta_key' )";

                    $data_name = '';
                    if ( 'radio' == $marks_type ) {
                        $data_name = ',
                                u.user_login AS data_name';
                    }

                    if ( !empty( $_POST['search'] ) ) {

                        if( isset( $order_meta ) && 'first_asc' == $order_meta && !empty( $_POST['already_assinged'] ) && $_POST['already_assinged'] != 'all' ) {
                            $sql = "(
                                                SELECT DISTINCT u.ID,
                                                       $display AS user_login $data_name
                                                FROM {$wpdb->users} u
                                                $sql_inner_part
                                                LEFT JOIN {$wpdb->usermeta} AS um2 ON ( u.ID = um2.user_id )
                                                LEFT JOIN {$wpdb->usermeta} AS um4 ON ( u.ID = um4.user_id AND um4.meta_key = '{$wpdb->prefix}capabilities' )
                                                WHERE CAST( um4.meta_value AS CHAR ) LIKE '%\"wpc_client\"%' AND
                                                      u.ID IN ({$_POST['already_assinged']})
                                                      $prepared_search
                                                      $included_ids
                                                      $where
                                                ORDER BY $order_by_sql
                                            ) UNION (
                                                SELECT DISTINCT u.ID,
                                                       $display AS user_login $data_name
                                                FROM {$wpdb->users} u
                                                $sql_inner_part
                                                LEFT JOIN {$wpdb->usermeta} AS um2 ON u.ID = um2.user_id
                                                LEFT JOIN {$wpdb->usermeta} AS um4 ON ( u.ID = um4.user_id AND um4.meta_key = '{$wpdb->prefix}capabilities' )
                                                WHERE CAST( um4.meta_value AS CHAR ) LIKE '%\"wpc_client\"%' AND
                                                      u.ID NOT IN ({$_POST['already_assinged']})
                                                      $prepared_search
                                                      $included_ids
                                                      $where
                                                ORDER BY $order_by_sql
                                            )
                                            $limit";
                        } else {
                            $sql = "SELECT DISTINCT u.ID,
                                                   $display AS user_login $data_name
                                            FROM {$wpdb->users} u
                                            $sql_inner_part
                                            LEFT JOIN {$wpdb->usermeta} AS um2 ON u.ID = um2.user_id
                                            LEFT JOIN {$wpdb->usermeta} AS um4 ON ( u.ID = um4.user_id AND um4.meta_key = '{$wpdb->prefix}capabilities' )
                                            WHERE CAST( um4.meta_value AS CHAR ) LIKE '%\"wpc_client\"%'
                                                  $prepared_search
                                                  $included_ids
                                                  $where
                                            ORDER BY $order_by_sql
                                            $limit";
                        }

                    } else {
                        if( isset( $order_meta ) && 'first_asc' == $order_meta && !empty( $_POST['already_assinged'] ) && $_POST['already_assinged'] != 'all' ) {
                            $sql = "(
                                                SELECT DISTINCT u.ID,
                                                       $display AS user_login $data_name
                                                FROM {$wpdb->users} u
                                                $sql_inner_part
                                                LEFT JOIN {$wpdb->usermeta} AS um2 ON (u.ID = um2.user_id AND um2.meta_key = '{$wpdb->prefix}capabilities' )
                                                WHERE CAST( um2.meta_value AS CHAR ) LIKE '%\"wpc_client\"%' AND
                                                      u.ID IN ({$_POST['already_assinged']})
                                                      $included_ids
                                                      $where
                                                ORDER BY $order_by_sql
                                            ) UNION (
                                                SELECT DISTINCT u.ID,
                                                       $display AS user_login $data_name
                                                FROM {$wpdb->users} u
                                                $sql_inner_part
                                                LEFT JOIN {$wpdb->usermeta} AS um2 ON ( u.ID = um2.user_id AND um2.meta_key = '{$wpdb->prefix}capabilities' )
                                                WHERE CAST( um2.meta_value AS CHAR ) LIKE '%\"wpc_client\"%' AND
                                                      u.ID NOT IN ({$_POST['already_assinged']})
                                                      $included_ids
                                                      $where
                                                ORDER BY $order_by_sql
                                            )
                                            $limit";
                        } else {
                            $sql = "SELECT DISTINCT u.ID,
                                                   $display AS user_login $data_name
                                            FROM {$wpdb->users} u
                                            $sql_inner_part
                                            LEFT JOIN {$wpdb->usermeta} AS um2 ON ( u.ID = um2.user_id AND um2.meta_key = '{$wpdb->prefix}capabilities' )
                                            WHERE CAST( um2.meta_value AS CHAR ) LIKE '%\"wpc_client\"%'
                                                  $included_ids
                                                  $where
                                            ORDER BY $order_by_sql
                                            $limit";
                        }
                    }

                    $clients = $wpdb->get_results( $sql );

                    $i = 0;
                    $count = count( $clients );
                    $n = floor( $count / 5 );
                    $counts_array = array( $n,$n,$n,$n,$n );
                    $ost = $count - ($n*5);
                    $ii = 0;
                    while ( $ost > 0 ) {
                        $counts_array[$ii]++;
                        $ii++;
                        $ost--;
                    }

                    $temp_count = $counts_array;
                    $br_index = array();
                    foreach ( $counts_array as $k=>$clients_in_row ) {
                        $br_index[] = array_sum( array_slice( $temp_count, 0, $k ) );
                    }

                    $html = '';
                    $html .= '<ul class="clients_list">';

                    foreach ( $clients as $k=>$client ) {
                        if ( 0 != $i && in_array( $i, $br_index ) )
                            $html .= '</ul><ul class="clients_list">';

                        $html .= '<li><label title="' . addslashes( $client->user_login ) . '">';
                        if( $readonly ) {
                            $html .= '<input type="' . $marks_type . '" disabled="disabled" name="wpc_client_temp[]" value="' . $client->ID . '" /> ';
                        } else {
                            $html .= '<input type="' . $marks_type . '" name="nfile_client_id[]" value="' . $client->ID . '" ' . ( $marks_type == 'radio' ? 'data-name="' . $client->data_name .'"' : '' ) . ' /> ';
                        }
                        $html .= $client->user_login;
                        $html .= '</label></li>';

                        $i++;
                    }
                    $html .= '</ul>';
                } else {
                    $html = __( 'No Clients For Assign.', WPC_CLIENT_TEXT_DOMAIN );
                    $buttons = array( 'first' => false, 'prev' => false, 'next' => false, 'last' => false );
                }

                $result_array = array(
                    'html'      => $html,
                    'page'      => $new_page,
                    'buttons'   => $buttons,
                    'per_page'  => $per_page,
                    'count'     => $clients_count,
                    'blocks'    => $block_array,
                    'display'   => $display_meta,
                    'order'     => $order_meta
                );

                if ( $open_popup )
                    $result_array['ids_list'] = $wpc_ids_array;

                if ( $type == 'wpc_clients_return' )
                    return json_encode( $result_array );
                else
                    echo json_encode( $result_array );

                break;
            }
                case 'wpc_circles': case 'send_wpc_circles': case 'wpc_circles_return':
            {
                $display_meta = empty( $display_meta ) ? 'group_name' : $display_meta;
                if( !empty( $_POST['display'] ) && $_POST['display'] != $display_meta ) {
                    $display_meta = $_POST['display'];
                    update_user_meta( get_current_user_id(), 'wpc_assign_popup_' . $type . '_display', $display_meta );
                }
                $display = $display_meta;
                if ( isset( $_POST['included_ids'] ) && !empty( $_POST['included_ids'] ) ) {
                    $included_ids = explode( ',', $_POST['included_ids'] );
                    $included_ids = " AND group_id IN ('" . implode( "','", $included_ids ) . "')";
                } else {
                    $included_ids = '';
                }

                $temp_order_array = explode("_", $order_meta);
                if( 2 == count($temp_order_array) ) {
                    if( ( 'asc' == strtolower($temp_order_array[1]) || 'desc' == strtolower($temp_order_array[1]) ) && ( 'show' == strtolower($temp_order_array[0]) || 'date' == strtolower($temp_order_array[0]) || 'first' == strtolower($temp_order_array[0]) ) ) {
                        switch($temp_order_array[0]) {
                            case 'show':
                                $order_type = "group_name";
                                break;
                            case 'date':
                                $order_type = 'group_id';
                                break;
                            case 'first':
                                $order_type = 'group_name';
                                break;
                        }
                        $order = $temp_order_array[1];
                    } else {
                        $order_type = 'group_name';
                        $order = "ASC";
                    }
                } else {
                    $order_type = 'group_name';
                    $order = "ASC";
                }

                $where = '';
                if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                    $clients = $this->get_assign_data_by_object( 'manager', get_current_user_id(), 'circle' );
                    $where .= " AND group_id IN ('" . implode( "','", $clients ) . "')";
                }
                if( isset($_POST['search']) && !empty($_POST['search']) ) {
                    $where .= " AND group_name LIKE '%".$_POST['search']."%'";
                }
                $sql = "SELECT * FROM {$wpdb->prefix}wpc_client_groups WHERE 1=1 $where $included_ids ORDER BY $order_type $order";
                if( $open_popup ) {
                    $wpc_ids_array = $wpdb->get_col( $sql );
                    $circles_count = count( $wpc_ids_array );
                } else {
                    $wpdb->query($sql);
                    $sql_count = "SELECT FOUND_ROWS()";
                    $circles_count = $wpdb->get_var($sql_count);
                }

                if( isset( $order_meta ) && 'first_asc' == $order_meta ) {
                    if( isset( $_POST['already_assinged'] ) && !empty( $_POST['already_assinged'] ) ) {
                        $assigned_users_str = $_POST['already_assinged'];
                        if( $assigned_users_str != 'all' ) {
                            $assigned_users = " AND group_id IN ($assigned_users_str) ";
                            $not_assigned_users = " AND group_id NOT IN ($assigned_users_str) ";
                        } else {
                            $assigned_users = '';
                            $not_assigned_users = '';
                        }
                    } else {
                        $assigned_users = '';
                        $not_assigned_users = '';
                    }
                    if( isset( $assigned_users ) && !empty( $assigned_users ) ) {
                        $sql = "(SELECT * FROM {$wpdb->prefix}wpc_client_groups WHERE 1=1 $assigned_users $where $included_ids ORDER BY $order_type $order)
                                        UNION
                                        (SELECT * FROM {$wpdb->prefix}wpc_client_groups WHERE 1=1 $not_assigned_users $where $included_ids ORDER BY $order_type $order)";
                    }
                }

                if ( $circles_count > 0 ) {
                    if($circles_count > $per_page) {
                        $goto = ( isset($_POST['goto']) && !empty($_POST['goto']) ) ? $_POST['goto'] : $goto;
                        switch($goto) {
                            case 'first':
                                $offset = 0;
                                $new_page = 1;
                                $buttons['first'] = false;
                                $buttons['prev'] = false;
                                break;
                            case 'prev':
                                $offset = ($cur_page-2)*$per_page;
                                $new_page = $cur_page - 1;
                                if($new_page <= 1) {
                                    $buttons['first'] = false;
                                    $buttons['prev'] = false;
                                    $new_page = 1;
                                }
                                break;
                            case 'next':
                                $last_page = ceil($circles_count/$per_page);
                                $offset = $cur_page*$per_page;
                                $new_page = (int) $cur_page + 1;
                                if($new_page >= $last_page) {
                                    $buttons['next'] = false;
                                    $buttons['last'] = false;
                                    $new_page = $last_page;
                                }
                                break;
                            case 'last':
                                $last_page = ceil($circles_count/$per_page);
                                $offset = ($last_page - 1)*$per_page;
                                $new_page = $last_page;
                                $buttons['next'] = false;
                                $buttons['last'] = false;
                                break;
                            default:
                                $offset = 0;
                                $new_page = 1;
                                $buttons['first'] = false;
                                $buttons['prev'] = false;
                                break;
                        }
                        $limit = " LIMIT $offset, $per_page";
                    } else {
                        $buttons = array('first' => false, 'prev' => false, 'next' => false, 'last' =>false);
                    }
                } else {
                    $buttons = array('first' => false, 'prev' => false, 'next' => false, 'last' =>false);
                }

                $sql = $sql.$limit;
                $groups = $wpdb->get_results( $sql, "ARRAY_A");

                if ( is_array( $groups ) && 0 < count( $groups ) ) {

                    $i = 0;
                    $count = count( $groups );
                    $n = floor( $count / 5 );
                    $counts_array = array( $n,$n,$n,$n,$n );
                    $ost = $count - ($n*5);
                    $ii = 0;
                    while ( $ost > 0 ) {
                        $counts_array[$ii]++;
                        $ii++;
                        $ost--;
                    }

                    $temp_count = $counts_array;
                    $br_index = array();
                    foreach ( $counts_array as $k=>$clients_in_row ) {
                        $br_index[] = array_sum( array_slice( $temp_count, 0, $k ) );
                    }
                    $html = '';
                    $html .= '<ul class="clients_list">';

                    foreach ( $groups as $group ) {
                        if ( 0 != $i && in_array( $i, $br_index ) )
                            $html .= '</ul><ul class="clients_list">';

                        $html .= '<li><label title="' . addslashes( $group['group_name'] ) . '">';
                        if( $readonly ) {
                            $html .= '<input type="' . $marks_type . '" disabled="disabled" name="wpc_circle_temp[]" value="' . $group['group_id'] . '" /> ';
                        } else {
                            $html .= '<input type="' . $marks_type . '" name="nfile_groups_id[]" value="' . $group['group_id'] . '" ' . ( $marks_type == 'radio' ? 'data-name="' . $group['group_name'] .'"' : '' ) . ' /> ';
                        }
                        $html .= $group['group_name'];
                        $html .= '</label></li>';

                        $i++;
                    }

                    $html .= '</ul>';
                } else {
                    $html = __( 'No Client Circles For Assign.', WPC_CLIENT_TEXT_DOMAIN );
                }

                $result_array = array(
                    'html' => $html,
                    'page' => $new_page,
                    'buttons' => $buttons,
                    'per_page' => $per_page,
                    'count' => $circles_count,
                    'blocks' => $block_array,
                    'display' => $display_meta,
                    'order' => $order_meta
                );
                if( $open_popup ) {
                    $result_array['ids_list'] = $wpc_ids_array;
                }
                if($type == 'wpc_circles_return')
                    return json_encode( $result_array );
                else
                    echo json_encode( $result_array );
                break;
            }
                case 'wpc_managers': case 'wpc_managers_return': {

                $display_meta = empty( $display_meta ) ? 'user_login' : $display_meta;
                if( !empty( $_POST['display'] ) && $_POST['display'] != $display_meta ) {
                    $display_meta = $_POST['display'];
                    update_user_meta( get_current_user_id(), 'wpc_assign_popup_' . $type . '_display', $display_meta );
                }

                //default values for displaying
                $display = "u." . $display_meta;

                //default values for order
                $order_type = 'u.user_login';
                $order = "ASC";

                $temp_order_array = explode( "_", $order_meta );
                if ( 2 == count( $temp_order_array ) ) {
                    if ( in_array( strtolower( $temp_order_array[1] ), array( 'asc', 'desc' ) ) && in_array( strtolower( $temp_order_array[0] ), array( 'show', 'date', 'first' ) ) ) {
                        switch ( $temp_order_array[0] ) {
                            case 'show':
                                $order_type = $display;
                                break;
                            case 'date':
                                $order_type = 'u.user_registered';
                                break;
                            case 'first':
                                $order_type = 'u.user_login';
                                break;
                        }

                        $order = $temp_order_array[1];
                    }
                }

                $order_by_sql = $order_type . " " . $order;


                $prepared_search = '';
                if ( !empty( $_POST['search'] ) ) {
                    $prepared_search = WPC()->admin()->get_prepared_search( $_POST['search'], array(
                        'u.ID',
                        'u.user_login',
                        'u.user_email',
                        'u.user_nicename',
                        array(
                            'table' => 'um',
                            'meta_key' => 'first_name',
                            'meta_value' => 'CAST( um.meta_value AS CHAR )'
                        ),
                        array(
                            'table' => 'um',
                            'meta_key' => 'last_name',
                            'meta_value' => 'CAST( um.meta_value AS CHAR )'
                        )
                    ) );
                }

                if ( !empty( $_POST['search'] ) ) {
                    $sql = "SELECT DISTINCT u.ID
                                    FROM {$wpdb->users} u
                                    LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
                                    LEFT JOIN {$wpdb->usermeta} AS um3 ON ( u.ID = um3.user_id AND um3.meta_key = '{$wpdb->prefix}capabilities' )
                                    WHERE CAST( um3.meta_value AS CHAR ) LIKE '%\"wpc_manager\"%'
                                          $prepared_search";
                } else {
                    $sql = "SELECT DISTINCT u.ID
                                    FROM {$wpdb->users} u
                                    LEFT JOIN {$wpdb->usermeta} um ON ( u.ID = um.user_id AND um.meta_key = '{$wpdb->prefix}capabilities' )
                                    WHERE CAST( um.meta_value AS CHAR ) LIKE '%\"wpc_manager\"%'";
                }

                $wpc_ids_array = $wpdb->get_col( $sql );
                $managers_count = count( $wpc_ids_array );

                if ( $managers_count > 0 ) {
                    if ( $managers_count > $per_page ) {
                        $goto = !empty( $_POST['goto'] ) ? $_POST['goto'] : $goto;

                        switch ( $goto ) {
                            case 'first':
                                $offset = 0;
                                $new_page = 1;
                                $buttons['first'] = false;
                                $buttons['prev'] = false;
                                break;
                            case 'prev':
                                $offset = ( $cur_page - 2 )*$per_page;
                                $new_page = $cur_page - 1;
                                if ( $new_page <= 1 ) {
                                    $buttons['first'] = false;
                                    $buttons['prev'] = false;
                                    $new_page = 1;
                                }
                                break;
                            case 'next':
                                $last_page = ceil( $managers_count/$per_page );
                                $offset = $cur_page*$per_page;
                                $new_page = (int) $cur_page + 1;
                                if ( $new_page >= $last_page ) {
                                    $buttons['next'] = false;
                                    $buttons['last'] = false;
                                    $new_page = $last_page;
                                }
                                break;
                            case 'last':
                                $last_page = ceil( $managers_count/$per_page );
                                $offset = ( $last_page - 1 )*$per_page;
                                $new_page = $last_page;
                                $buttons['next'] = false;
                                $buttons['last'] = false;
                                break;
                            default:
                                $offset = 0;
                                $new_page = 1;
                                $buttons['first'] = false;
                                $buttons['prev'] = false;
                                break;
                        }

                        $limit = "LIMIT $offset, $per_page";
                    } else {
                        $buttons = array( 'first' => false, 'prev' => false, 'next' => false, 'last' =>false );
                    }

                    $data_name = '';
                    if ( 'radio' == $marks_type ) {
                        $data_name = ',
                                u.user_login AS data_name';
                    }

                    if ( !empty( $_POST['search'] ) ) {

                        if ( 'first_asc' == $order_meta && !empty( $_POST['already_assinged'] ) && $_POST['already_assinged'] != 'all' ) {
                            $sql = "(
                                                SELECT DISTINCT u.ID,
                                                       $display AS user_login $data_name
                                                FROM {$wpdb->users} u
                                                LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
                                                LEFT JOIN {$wpdb->usermeta} AS um3 ON ( u.ID = um3.user_id AND um3.meta_key = '{$wpdb->prefix}capabilities' )
                                                WHERE CAST( um3.meta_value AS CHAR ) LIKE '%\"wpc_manager\"%' AND
                                                      u.ID IN ({$_POST['already_assinged']})
                                                      $prepared_search
                                                ORDER BY $order_by_sql
                                            ) UNION (
                                                SELECT DISTINCT u.ID,
                                                       $display AS user_login $data_name
                                                FROM {$wpdb->users} u
                                                LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
                                                LEFT JOIN {$wpdb->usermeta} AS um3 ON (u.ID = um3.user_id AND um3.meta_key = '{$wpdb->prefix}capabilities' )
                                                WHERE CAST( um3.meta_value AS CHAR ) LIKE '%\"wpc_manager\"%' AND
                                                      u.ID NOT IN ({$_POST['already_assinged']})
                                                      $prepared_search
                                                ORDER BY $order_by_sql
                                            )
                                            $limit";
                        } else {
                            $sql = "SELECT DISTINCT u.ID,
                                                   $display AS user_login $data_name
                                            FROM {$wpdb->users} u
                                            LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
                                            LEFT JOIN {$wpdb->usermeta} AS um3 ON (u.ID = um3.user_id AND um3.meta_key = '{$wpdb->prefix}capabilities' )
                                            WHERE CAST( um3.meta_value AS CHAR ) LIKE '%\"wpc_manager\"%'
                                                  $prepared_search
                                            ORDER BY $order_by_sql
                                            $limit";
                        }
                    } else {
                        if( 'first_asc' == $order_meta && !empty( $_POST['already_assinged'] ) && $_POST['already_assinged'] != 'all' ) {
                            $sql = "(
                                                SELECT DISTINCT u.ID,
                                                       $display AS user_login $data_name
                                                FROM {$wpdb->users} u
                                                LEFT JOIN {$wpdb->usermeta} um ON ( u.ID = um.user_id AND um.meta_key = '{$wpdb->prefix}capabilities')
                                                WHERE CAST( um.meta_value AS CHAR ) LIKE '%\"wpc_manager\"%' AND
                                                      u.ID IN ( {$_POST['already_assinged']} )
                                                ORDER BY $order_by_sql
                                            ) UNION (
                                                SELECT DISTINCT u.ID,
                                                       $display AS user_login $data_name
                                                FROM {$wpdb->users} u
                                                LEFT JOIN {$wpdb->usermeta} um ON ( u.ID = um.user_id AND um.meta_key = '{$wpdb->prefix}capabilities')
                                                WHERE CAST( um.meta_value AS CHAR ) LIKE '%\"wpc_manager\"%' AND
                                                      u.ID NOT IN ( {$_POST['already_assinged']} )
                                                ORDER BY $order_by_sql
                                            )
                                            $limit";
                        } else {
                            $sql = "SELECT DISTINCT u.ID,
                                                   $display AS user_login $data_name
                                            FROM {$wpdb->users} u
                                            LEFT JOIN {$wpdb->usermeta} um ON ( u.ID = um.user_id AND um.meta_key = '{$wpdb->prefix}capabilities')
                                            WHERE CAST( um.meta_value AS CHAR ) LIKE '%\"wpc_manager\"%'
                                            ORDER BY $order_by_sql
                                            $limit";
                        }
                    }

                    $clients = $wpdb->get_results($sql);

                    $i = 0;
                    $count = count( $clients );
                    $n = floor( $count / 5 );
                    $counts_array = array( $n,$n,$n,$n,$n );
                    $ost = $count - ($n*5);
                    $ii = 0;
                    while ( $ost > 0 ) {
                        $counts_array[$ii]++;
                        $ii++;
                        $ost--;
                    }

                    $temp_count = $counts_array;
                    $br_index = array();
                    foreach ( $counts_array as $k=>$clients_in_row ) {
                        $br_index[] = array_sum( array_slice( $temp_count, 0, $k ) );
                    }

                    $html = '';
                    $html .= '<ul class="clients_list">';

                    foreach ( $clients as $client ) {
                        if ( 0 != $i && in_array( $i, $br_index ) )
                            $html .= '</ul><ul class="clients_list">';

                        $html .= '<li><label title="' . addslashes( $client->user_login ) . '">';
                        if( $readonly ) {
                            $html .= '<input type="' . $marks_type . '" disabled="disabled" name="wpc_manager_temp[]" value="' . $client->ID . '" /> ';
                        } else {
                            $html .= '<input type="' . $marks_type . '" name="nfile_client_id[]" value="' . $client->ID . '" ' . ( $marks_type == 'radio' ? 'data-name="' . $client->data_name .'"' : '' ) . ' /> ';
                        }
                        $html .= $client->user_login;
                        $html .= '</label></li>';

                        $i++;
                    }
                    $html .= '</ul>';
                } else {
                    $html = __( 'No Managers For Assign.', WPC_CLIENT_TEXT_DOMAIN );
                    $buttons = array('first' => false, 'prev' => false, 'next' => false, 'last' =>false);
                }

                $result_array = array(
                    'html' => $html,
                    'page' => $new_page,
                    'buttons' => $buttons,
                    'per_page' => $per_page,
                    'count' => $managers_count,
                    'blocks' => $block_array,
                    'display' => $display_meta,
                    'order' => $order_meta
                );
                if( $open_popup ) {
                    $result_array['ids_list'] = $wpc_ids_array;
                }
                if($type == 'wpc_managers_return')
                    return json_encode( $result_array );
                else
                    echo json_encode( $result_array );
                break;
            }
            }
        }
        exit;
    }


    function assign_popup_send_notification( $data, $params ) {
        global $wpdb;
        if( isset( $params['current_page'] ) && 'wpclients_files' == $params['current_page'] ) {
            if( isset( $data['send_file_notification'] ) && '1' == $data['send_file_notification'] ) {
                if( isset( $data['id'] ) && is_numeric( $data['id'] ) ) {
                    $file_id = $data['id'];
                } else {
                    return;
                }

                $cat_id = $wpdb->get_var( $wpdb->prepare( "SELECT cat_id FROM {$wpdb->prefix}wpc_client_files WHERE id = %d", $file_id ) );

                $send_client_ids = array();
                if( isset( $data['data_type'] ) && 'wpc_clients' == $data['data_type'] && strlen( $data['data'] ) ) {
                    $send_client_ids = explode( ',', $data['data'] );
                }
                if( isset( $data['data_type'] ) && 'wpc_circles' == $data['data_type'] && strlen( $data['data'] ) ) {
                    $send_group_ids = explode( ',', $data['data'] );
                    foreach( $send_group_ids as $group_id ) {
                        $send_client_ids = array_merge( $send_client_ids, WPC()->groups()->get_group_clients_id( $group_id ) );
                    }
                }

                $send_client_ids = array_unique( $send_client_ids );

                $old_users = $this->get_assign_data_by_object( 'file', $file_id, 'client' );
                $old_users = array_merge( $old_users, $this->get_assign_data_by_object( 'file_category', $cat_id, 'client' ) );

                $old_circles = $this->get_assign_data_by_object( 'file', $file_id, 'circle' );
                $old_circles = array_merge( $old_circles, $this->get_assign_data_by_object( 'file_category', $cat_id, 'circle' ) );
                $old_circles = array_unique( $old_circles );
                foreach( $old_circles as $group_id ) {
                    $old_users = array_merge( $old_users, WPC()->groups()->get_group_clients_id( $group_id ) );
                }
                $send_client_ids = array_diff( $send_client_ids, $old_users );

                if ( is_array( $send_client_ids ) && 0 < count( $send_client_ids ) ) {
                    wp_schedule_single_event( time() - 1, 'send_email_notification_cron', array( $data, $params, $send_client_ids, uniqid() ) );
                }
            }
        }
    }


    function admin_footer_popup() {
        wp_enqueue_script( 'wpc-new-assign-popup-js', false, array('jquery'), WPC_CLIENT_VER, true );

        $localize_array = array(
            'l10n_print_after' => "
                    if( typeof wpc_popup_title == 'undefined' ) {
                        var wpc_popup_title = new Array();
                    }
                    if( typeof wpc_popup_title.client_popup_block == 'undefined' ) {
                        wpc_popup_title.client_popup_block = new Array();
                    }
                    wpc_popup_title.client_popup_block.wpc_clients = wpc_popup_var.data",
            'data'         => sprintf( __( 'Assign %s:', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
            'current_page' => isset( $current_page ) ? $current_page : '',
            'search_text'  => __( 'Search', WPC_CLIENT_TEXT_DOMAIN ),
            'admin_url'    => get_admin_url()
        );
        if( isset( $params['wpc_ajax_prefix'] ) && !empty( $params['wpc_ajax_prefix'] ) ) {
            $localize_array['wpc_ajax_prefix'] = $params['wpc_ajax_prefix'];
        }
        wp_localize_script( "wpc-new-assign-popup-js", 'wpc_popup_var', $localize_array );

        $localize_array = array(
            'l10n_print_after' => "
                    if( typeof wpc_popup_title == 'undefined' ) {
                        var wpc_popup_title = new Array();
                    }
                    if( typeof wpc_popup_title.circle_popup_block == 'undefined' ) {
                        wpc_popup_title.circle_popup_block = new Array();
                    }
                    wpc_popup_title.circle_popup_block.wpc_circles = wpc_popup_var.data",
            'data'         => sprintf(__('Assign %s:', WPC_CLIENT_TEXT_DOMAIN), WPC()->custom_titles['circle']['p']),
            'current_page' => isset( $current_page ) ? $current_page : '',
            'search_text'  => __( 'Search', WPC_CLIENT_TEXT_DOMAIN ),
            'admin_url'    => get_admin_url()
        );
        if( isset( $params['wpc_ajax_prefix'] ) && !empty( $params['wpc_ajax_prefix'] ) ) {
            $localize_array['wpc_ajax_prefix'] = $params['wpc_ajax_prefix'];
        }
        wp_localize_script( "wpc-new-assign-popup-js", 'wpc_popup_var', $localize_array );
    }


    /**
     * Assign Clients to Client Circle
     **/
    function assign_clients_group( $group_id, $clients_id = array() ) {
        global $wpdb;

        /*our_hook_
        hook_name: wpc_before_assign_clients_group
        hook_title: Clients assign to Circle
        hook_description: Hook runs when Clients assign to Circle.
        hook_type: action
        hook_in: wp-client
        hook_location class.admin.php
        hook_param: int $group_id, array $clients_id
        hook_since: 4.1.6
        */
        do_action('wpc_before_assign_clients_group', $group_id, $clients_id);

        //delete clients from Client Circle
        $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wpc_client_group_clients WHERE group_id = %d", $group_id ) );

        //Add clients to the Client Circle
        if ( is_array( $clients_id ) && 0 < count( $clients_id ) ) {
            foreach ( $clients_id as $client_id ) {
                $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}wpc_client_group_clients SET group_id = %d, client_id = '%d'", $group_id,  $client_id ) );
            }
        }

        /*our_hook_
        hook_name: wpc_assigned_clients_group
        hook_title: Clients assign to Circle
        hook_description: Hook runs when Clients assign to Circle.
        hook_type: action
        hook_in: wp-client
        hook_location class.admin.php
        hook_param: int $group_id, array $clients_id
        hook_since: 4.1.6
        */
        do_action('wpc_assigned_clients_group', $group_id, $clients_id);
    }




}

endif;