<?php

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly


if ( ! class_exists( 'WPC_Private_Messages' ) ) :

final class WPC_Private_Messages {

    /**
     * The single instance of the class.
     *
     * @var WPC_Private_Messages
     * @since 4.5
     */
    protected static $_instance = null;

    /**
     * Instance.
     *
     * Ensures only one instance of WPC_Private_Messages is loaded or can be loaded.
     *
     * @since 4.5
     * @static
     * @return WPC_Private_Messages - Main instance.
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
     * Build "To" list for messages
     *
     */
    function private_messages_build_to_list() {
        global $wpdb;

        $wpc_private_messages = WPC()->get_settings( 'private_messages' );

        $users = array();
        $excluded_clients  = WPC()->members()->get_excluded_clients();

        //clients
        if( current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {
            $manager_clients = array();
            if( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                $manager_clients = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'client' );

                $assigned = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'circle' );
                $wpc_circles = $wpdb->get_results(
                    "SELECT wcg.*, count(wcgc.client_id) - count(um.umeta_id) as clients_count
                        FROM {$wpdb->prefix}wpc_client_groups wcg
                        LEFT JOIN {$wpdb->prefix}wpc_client_group_clients wcgc ON wcgc.group_id = wcg.group_id
                        LEFT JOIN {$wpdb->usermeta} um ON wcgc.client_id = um.user_id AND um.meta_key = 'archive' AND um.meta_value = '1'
                        WHERE wcg.group_id IN('" . implode( "','", $assigned ) . "')
                        GROUP BY wcg.group_id",
                    ARRAY_A );

                if ( !empty( $wpc_circles ) ) {
                    foreach ( $wpc_circles as $wpc_circle ) {
                        $result = WPC()->groups()->get_group_clients_id( $wpc_circle );
                        if ( !empty( $result ) ) {
                            $manager_clients = array_merge( $manager_clients, $result );
                        }
                    }
                }
                //here add assign circles
            }

            $args = array(
                'role'      => 'wpc_client',
                'include'   => $manager_clients,
                'exclude'   => $excluded_clients,
                'orderby'   => 'user_login',
                'order'     => 'ASC'
            );

            $users['wpc_client'] = get_users( $args );
        } elseif( current_user_can( 'wpc_client_staff' ) ) {

            if( !( isset( $wpc_private_messages['relate_client_staff'] ) && 'no' == $wpc_private_messages['relate_client_staff'] ) ) {
                $args = array(
                    'role'      => 'wpc_client',
                    'include'   => array( get_user_meta( get_current_user_id(), 'parent_client_id', true ) ),
                    'exclude'   => $excluded_clients,
                    'orderby'   => 'user_login',
                    'order'     => 'ASC'
                );

                $users['wpc_client'] = get_users( $args );
            }
        }

        //circles
        if( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {
            $users['wpc_circles'] = WPC()->groups()->get_groups();
        } elseif ( current_user_can( 'wpc_manager' ) ) {

            $assigned = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'circle' );
            $users['wpc_circles'] = $wpdb->get_results(
                "SELECT wcg.*, count(wcgc.client_id) - count(um.umeta_id) as clients_count
                    FROM {$wpdb->prefix}wpc_client_groups wcg
                    LEFT JOIN {$wpdb->prefix}wpc_client_group_clients wcgc ON wcgc.group_id = wcg.group_id
                    LEFT JOIN {$wpdb->usermeta} um ON wcgc.client_id = um.user_id AND um.meta_key = 'archive' AND um.meta_value = '1'
                    WHERE wcg.group_id IN('" . implode( "','", $assigned ) . "')
                    GROUP BY wcg.group_id",
                ARRAY_A );
        }


        //staff
        if( current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {
            if( !WPC()->flags['easy_mode'] ) {
                $manager_clients = array();
                if( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                    $manager_clients = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'client' );

                    //here add assign circles
                }

                $args = array(
                    'role'      => 'wpc_client',
                    'include'   => $manager_clients,
                    'exclude'   => $excluded_clients,
                    'fields'    => 'ids'
                );
                $wpc_client_ids = get_users( $args );

                $args = array(
                    'role'          => 'wpc_client_staff',
                    'meta_key'      => 'parent_client_id',
                    'meta_value'    => $wpc_client_ids,
                    'orderby'       => 'user_login',
                    'order'         => 'ASC',
                );
                $users['wpc_client_staff'] = get_users( $args );
            }

        } elseif( current_user_can( 'wpc_client' ) ) {
            if( !( isset( $wpc_private_messages['relate_client_staff'] ) && 'no' == $wpc_private_messages['relate_client_staff'] ) ) {
                $args = array(
                    'role'          => 'wpc_client_staff',
                    'meta_key'      => 'parent_client_id',
                    'meta_value'    => get_current_user_id(),
                    'orderby'       => 'user_login',
                    'order'         => 'ASC',
                );
                $users['wpc_client_staff'] = get_users( $args );
            }
        }

        //managers
        if( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {
            if( !WPC()->flags['easy_mode'] ) {
                $args = array(
                    'role'      => 'wpc_manager',
                    'orderby'   => 'user_login',
                    'order'     => 'ASC',
                );
                $users['wpc_managers'] = get_users( $args );
            }

        } elseif( current_user_can( 'wpc_client' ) ) {
            if( !( isset( $wpc_private_messages['relate_client_manager'] ) && 'no' == $wpc_private_messages['relate_client_manager'] ) ) {
                $client_managers = WPC()->assigns()->get_assign_data_by_assign( 'manager', 'client', get_current_user_id() );

                $client_circles = WPC()->groups()->get_client_groups_id( get_current_user_id() );

                $circle_managers = WPC()->assigns()->get_assign_data_by_assign( 'manager', 'circle', $client_circles );
                $client_managers = array_merge( $client_managers, $circle_managers );

                if( !empty( $client_managers ) ) {
                    $args = array(
                        'role'      => 'wpc_manager',
                        'include'   => $client_managers,
                        'orderby'   => 'user_login',
                        'order'     => 'ASC'
                    );

                    $users['wpc_managers'] = get_users( $args );
                }
            }
        } elseif( current_user_can( 'wpc_client_staff' ) ) {
            if( !( isset( $wpc_private_messages['relate_staff_manager'] ) && 'no' == $wpc_private_messages['relate_staff_manager'] ) ) {
                $client_id = get_user_meta( get_current_user_id(), 'parent_client_id', true );

                $client_managers = WPC()->assigns()->get_assign_data_by_assign( 'manager', 'client', $client_id );

                $client_circles = WPC()->groups()->get_client_groups_id( $client_id );

                $circle_managers = WPC()->assigns()->get_assign_data_by_assign( 'manager', 'circle', $client_circles );
                $client_managers = array_merge( $client_managers, $circle_managers );

                if( !empty( $client_managers ) ) {
                    $args = array(
                        'role'      => 'wpc_manager',
                        'include'   => $client_managers,
                        'orderby'   => 'user_login',
                        'order'     => 'ASC'
                    );

                    $users['wpc_managers'] = get_users( $args );
                }
            }
        } elseif( current_user_can('wpc_manager') && ! current_user_can( 'administrator' ) ) {
            if( !WPC()->flags['easy_mode'] && isset( $wpc_private_messages['relate_manager_manager'] ) && 'yes' == $wpc_private_messages['relate_manager_manager'] ) {
                $args = array(
                    'role' => 'wpc_manager',
                    'exclude' => get_current_user_id(),
                    'orderby' => 'user_login',
                    'order' => 'ASC',
                );
                $wpc_managers = get_users($args);
                $users['wpc_managers'] = $wpc_managers;
            }
        }

        //admins
        $administrators = array();
        $wpc_admins = array();
        if( current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {

            if( !WPC()->flags['easy_mode'] ) {
                $args = array(
                    'role' => 'wpc_admin',
                    'exclude' => get_current_user_id(),
                    'orderby' => 'user_login',
                    'order' => 'ASC',
                );
                $wpc_admins = get_users($args);
            }

            $args = array(
                'role' => 'administrator',
                'exclude' => get_current_user_id(),
                'orderby' => 'user_login',
                'order' => 'ASC',
            );
            $administrators = get_users($args);

        } else {

            if( isset( $wpc_private_messages['front_end_admins'] ) && !empty( $wpc_private_messages['front_end_admins'] ) ) {

                $args = array(
                    'role' => 'wpc_admin',
                    'include' => $wpc_private_messages['front_end_admins'],
                    'orderby' => 'user_login',
                    'order' => 'ASC',
                );
                $wpc_admins = get_users($args);

                $args = array(
                    'role' => 'administrator',
                    'include' => $wpc_private_messages['front_end_admins'],
                    'orderby' => 'user_login',
                    'order' => 'ASC',
                );
                $administrators = get_users($args);
            }
        }

        $users['admins'] = array_merge( $administrators, $wpc_admins );

        if( current_user_can( 'wpc_client' ) || current_user_can( 'wpc_client_staff' ) ) {
            if( !( isset( $users['admins'] ) && count( $users['admins'] ) > 0 ) &&
                !( isset( $users['wpc_managers'] ) && count( $users['wpc_managers'] ) > 0 ) &&
                !( isset( $users['wpc_client'] ) && count( $users['wpc_client'] ) > 0 ) ) {

                $args = array(
                    'role'      => 'administrator',
                    'orderby'   => 'ID',
                    'order'     => 'ASC',
                    'number'    => 1
                );

                $users['admins'] = get_users($args);
            }
        }


        return $users;
    }


    /**
     * Create message chain (first message)
     *
     */
    function private_messages_create_chain( $data ) {
        global $wpdb;

        if( empty( $data['cc'] ) ) {
            $data['cc'] = array();
        }
        $client_ids = array_merge( $data['to'], $data['cc'] );
        $assign_new = $client_ids;

        $client_ids[] = get_current_user_id();

        $wpdb->insert(
            "{$wpdb->prefix}wpc_client_chains",
            array(
                'subject'       => addslashes( htmlspecialchars( $data['subject'] ) ),
            )
        );

        $chain_id = $wpdb->insert_id;

        //create assigns to chain
        WPC()->assigns()->set_assigned_data( 'chain', $chain_id, 'client', $client_ids );

        //create new message
        $args = array(
            'chain_id'  => $chain_id,
            'content'   => addslashes( htmlspecialchars( $data['content'] ) ),
            'author_id' => get_current_user_id(),
            'date'      => time(),
            'assign_new'=> $assign_new
        );

        $this->private_messages_create_message( $args );
    }


    function private_messages_create_message( $data ) {
        global $wpdb;

        $wpdb->insert(
            "{$wpdb->prefix}wpc_client_messages",
            array(
                'chain_id'      => $data['chain_id'],
                'content'       => $data['content'],
                'author_id'     => $data['author_id'],
                'date'          => $data['date']
            )
        );

        $message_id = $wpdb->insert_id;

        $subject = $wpdb->get_var( $wpdb->prepare(
            "SELECT subject
                FROM {$wpdb->prefix}wpc_client_chains
                WHERE id = %d",
            $data['chain_id']
        ) );

        //create assigns to new messages
        WPC()->assigns()->set_assigned_data( 'new_message', $message_id, 'client', $data['assign_new'] );

        //send email notifications
        $author = get_user_by( 'id', $data['author_id'] );
        foreach( $data['assign_new'] as $user_id ) {
            //get trashed chains
            $client_trash_chains = WPC()->assigns()->get_assign_data_by_assign( 'trash_chain', 'client', $user_id );

            if( !in_array( $data['chain_id'], $client_trash_chains ) ) {
                $user = get_user_by( 'id', $user_id );
                $send_to_email = $user->user_email;

                if ( user_can( $user_id, 'wpc_client' ) || user_can( $user_id, 'wpc_client_staff' ) ) {
                    $template = 'notify_client_about_message';
                } elseif ( user_can( $user_id, 'wpc_manager' ) || user_can( $user_id, 'wpc_admin' ) || user_can( $user_id, 'administrator' ) ) {
                    $template = 'notify_admin_about_message';
                }

                $args = array(
                    'client_id' => $user_id,
                    'user_name' => $author->user_login,
                    'message' => nl2br( htmlspecialchars( stripslashes( $data['content'] ) ) ),
                    'subject' => nl2br( htmlspecialchars( stripslashes( $subject ) ) )
                );

                //send email
                WPC()->mail( $template, $send_to_email, $args, $template );
            }
        }

        //if site admin notification turned on
        $wpc_private_messages = WPC()->get_settings( 'private_messages' );
        if( isset( $wpc_private_messages['send_to_site_admin'] ) && 'yes' == $wpc_private_messages['send_to_site_admin'] ) {
            $admin_email = get_option( 'admin_email' );
            $site_admin = get_user_by( 'email', $admin_email );
            $args = array(
                'client_id' => $site_admin->ID,
                'user_name' => $author->user_login,
                'message'   => nl2br( htmlspecialchars( stripslashes( $data['content'] ) ) ),
                'subject'   => nl2br( htmlspecialchars( stripslashes( $subject ) ) ),
                'admin_message_url'   => get_admin_url() . 'admin.php?page=wpclients_content&tab=private_messages',
            );
            WPC()->mail( 'notify_admin_about_message', $admin_email, $args, 'notify_admin_about_message' );
        }

        return $message_id;
    }

}

endif;