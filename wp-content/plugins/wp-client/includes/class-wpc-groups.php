<?php

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly


if ( ! class_exists( 'WPC_Groups' ) ) :

final class WPC_Groups {

    /**
     * The single instance of the class.
     *
     * @var WPC_Groups
     * @since 4.5
     */
    protected static $_instance = null;


    /**
     * Instance.
     *
     * Ensures only one instance of WPC_Groups is loaded or can be loaded.
     *
     * @since 4.5
     * @static
     * @return WPC_Groups - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {

    }


    /**
     *  Get all Client Circles for client
     *
     * @param int $client_id
     * @return array
     */
    function get_client_groups_id( $client_id ) {
        global $wpdb;

        $client_groups_id = $wpdb->get_col( $wpdb->prepare(
            "SELECT group_id
                FROM {$wpdb->prefix}wpc_client_group_clients
                WHERE client_id = %d",
            $client_id
        ) );

        if ( ! is_array( $client_groups_id ) )
            $client_groups_id = array();

        return $client_groups_id;
    }

    /**
     * Get all clients for Client Circle
     **/
    function get_group_clients_id( $group_id ) {
        global $wpdb;

        if ( 0 >= $group_id )
            return array();

        $excluded_clients  = WPC()->members()->get_excluded_clients();

        $group_clients      = $wpdb->get_results( $wpdb->prepare( "SELECT client_id FROM {$wpdb->prefix}wpc_client_group_clients WHERE group_id = %d", $group_id ), "ARRAY_A" );
        $group_clients_id   = array();

        if ( $group_clients ) {
            foreach( $group_clients as $group_client )
                $group_clients_id[] = $group_client['client_id'];
        }

        $group_clients_id = array_diff( $group_clients_id, $excluded_clients, array(0) );

        return $group_clients_id;
    }


    /**
     * Get all data of all Client Circles
     **/
    function get_groups() {
        global $wpdb;
        $groups = $wpdb->get_results( "SELECT wcg.*, count(wcgc.client_id) - count(um.umeta_id) as clients_count FROM {$wpdb->prefix}wpc_client_groups wcg LEFT JOIN {$wpdb->prefix}wpc_client_group_clients wcgc ON wcgc.group_id = wcg.group_id LEFT JOIN {$wpdb->usermeta} um ON wcgc.client_id = um.user_id AND um.meta_key = 'archive' AND um.meta_value = '1' GROUP BY wcg.group_id", "ARRAY_A");
        return $groups;
    }

    /**
     * Get Client Circle by ID
     **/
    function get_group( $id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wpc_client_groups WHERE group_id = %d", $id ), "ARRAY_A");
    }

    /**
     * get all circles IDs
     **/
    function get_group_ids() {
        global $wpdb;
        $groups = $wpdb->get_col( "SELECT group_id FROM {$wpdb->prefix}wpc_client_groups");
        return $groups;
    }


    /**
     * Create new Client Circle
     **/
    function create_circle( $args ) {
        global $wpdb;

        if ( !isset( $args['group_name'] ) || empty( $args['group_name'] ) ) {
            return false;
        }

        //checking that Client Circle not exist
        $result = $wpdb->get_row( $wpdb->prepare(
            "SELECT group_id
                FROM {$wpdb->prefix}wpc_client_groups
                WHERE LOWER(group_name) = '%s'",
            strtolower( $args['group_name'] )
        ), ARRAY_A );

        if ( !$result ) {

            $default_args = array(
                'auto_select'       => '0',
                'auto_add_files'    => '0',
                'auto_add_pps'      => '0',
                'auto_add_manual'   => '0',
                'auto_add_self'     => '0',
                'assign'            => '',
            );

            $args = array_merge( $default_args, $args );


            //create new Client Circle
            $wpdb->query( $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}wpc_client_groups
                    SET group_name = '%s',
                        auto_select = '%s',
                        auto_add_files = '%s',
                        auto_add_pps = '%s',
                        auto_add_manual = '%s',
                        auto_add_self = '%s'
                        ",
                trim( $args['group_name'] ),
                $args['auto_select'],
                $args['auto_add_files'],
                $args['auto_add_pps'],
                $args['auto_add_manual'],
                $args['auto_add_self']
            ) );

            $new_circle_id = $wpdb->insert_id;

            if( isset( $args['assign'] ) ) {
                $clients = explode( ',', $args['assign'] );
                WPC()->assigns()->assign_clients_group( $new_circle_id, $clients );
            }

            /*our_hook_
            hook_name: wpc_circle_created
            hook_title: Circle created
            hook_description: Hook runs when Circle created.
            hook_type: action
            hook_in: wp-client
            hook_location class.admin.php
            hook_param: int $new_circle_id, array $args
            hook_since: 4.1.6
            */
            do_action('wpc_circle_created', $new_circle_id, $args);

            return $new_circle_id;
        }

        return false;
    }


    /**
     * Update Client Circle
     **/
    function update_circle( $args ) {
        global $wpdb;

        if ( empty( $args['group_name'] ) || empty( $args['id'] ) ) {
            return false;
        }

        //checking that Client Circle not exist
        $result = $wpdb->get_row( $wpdb->prepare(
            "SELECT group_id
                FROM {$wpdb->prefix}wpc_client_groups
                WHERE LOWER(group_name) = '%s'",
            strtolower( $args['group_name'] )
        ), ARRAY_A );


        if ( $result ) {
            if ( "0" != $args['id'] && $result['group_id'] == $args['id'] ) {

            } else {
                return false;

            }
        }

        $default_args = array(
            'auto_select'       => '0',
            'auto_add_files'    => '0',
            'auto_add_pps'      => '0',
            'auto_add_manual'   => '0',
            'auto_add_self'     => '0',
            'assign'            => '',
        );

        $args = array_merge( $default_args, $args );

        //update when edit Client Circle
        $wpdb->query( $wpdb->prepare(
            "UPDATE {$wpdb->prefix}wpc_client_groups SET
                    group_name = '%s',
                    auto_select = '%s',
                    auto_add_files = '%s',
                    auto_add_pps = '%s',
                    auto_add_manual = '%s',
                    auto_add_self = '%s'
                WHERE group_id = %d",
            trim( $args['group_name'] ),
            $args['auto_select'],
            $args['auto_add_files'],
            $args['auto_add_pps'],
            $args['auto_add_manual'],
            $args['auto_add_self'],
            $args['id'] )
        );

        if( isset( $args['assign'] ) ) {
            $clients = explode( ',', $args['assign'] );

            WPC()->assigns()->assign_clients_group( $args['id'], $clients );
        }

        /*our_hook_
        hook_name: wpc_circle_updated
        hook_title: Circle updated
        hook_description: Hook runs when Circle updated.
        hook_type: action
        hook_in: wp-client
        hook_location class.admin.php
        hook_param: array $args
        hook_since: 4.1.6
        */
        do_action('wpc_circle_updated', $args);

        return true;
    }


    /**
     * Delete Client Circle
     **/
    function delete_group( $group_id ) {
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
        do_action('wpc_before_circle_delete', $group_id );
        //delete Client Circle
        $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wpc_client_groups WHERE group_id = %d", $group_id ) );

        //delete all clients from Client Circle
        $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wpc_client_group_clients WHERE group_id = %d", $group_id ) );

        //for delete assigns
        WPC()->assigns()->delete_all_assign_assigns( 'circle', $group_id );
    }


    function assign( $group_ids, $client_ids, $clear_group_clients = false, $clear_client_groups = false ) {
		global $wpdb;
    	$group_ids = is_array( $group_ids ) ? $group_ids : array( $group_ids );
		$client_ids = is_array( $client_ids ) ? $client_ids : array( $client_ids );

		if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
            $available_group_ids = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'circle' );
			$group_ids = array_intersect( $group_ids, $available_group_ids );
        } else {
            $available_group_ids = $this->get_group_ids();
        }

    	if( $clear_group_clients ) {
    		$group_ids = array_map('intval', $group_ids);
			$wpdb->query( "DELETE FROM {$wpdb->prefix}wpc_client_group_clients WHERE group_id IN(" . implode( ',', $group_ids ) . ")" );
		}
    	if( $clear_client_groups ) {
    		$client_ids = array_map('intval', $client_ids);
			$wpdb->query( "DELETE FROM {$wpdb->prefix}wpc_client_group_clients WHERE client_id IN(" . implode( ',', $client_ids ) . ")" );
		}

		if( !$clear_group_clients && !$clear_client_groups ) {
    		$wpdb->query( "DELETE FROM {$wpdb->prefix}wpc_client_group_clients WHERE group_id IN(" . implode( ',', $group_ids ) . ") AND client_id IN(" . implode( ',', $client_ids ) . ")" );
		}

		if( empty( $group_ids ) || empty( $client_ids ) ) {
    		return true;
		}

		$query_parts = array();
		foreach( $group_ids as $group_id ) {
			foreach( $client_ids as $client_id ) {
				$query_parts[] = "($group_id, $client_id)";
			}
		}
		$wpdb->query( "INSERT INTO {$wpdb->prefix}wpc_client_group_clients ( group_id, client_id ) 
			VALUES " . implode(', ', $query_parts) );

		return true;
    }


}

endif;