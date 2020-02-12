<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( !class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPC_Messages_List_Table extends WP_List_Table {
    var $no_items_message = '';
    var $sortable_columns = array();
    var $default_sorting_field = '';
    var $actions = array();
    var $bulk_actions = array();
    var $columns = array();
    var $list_type = 'all';
    var $archive = array();
    var $trash = array();
    var $inbox = array();
    var $sent = array();

    function __construct( $args = array() ){
        $args = wp_parse_args( $args, array(
            'singular'  => __( 'item', WPC_CLIENT_TEXT_DOMAIN ),
            'plural'    => __( 'items', WPC_CLIENT_TEXT_DOMAIN ),
            'ajax'      => false
        ) );

        $this->no_items_message = sprintf( __( 'No %s found.', WPC_CLIENT_TEXT_DOMAIN ), strtolower( $args['plural'] ) );

        parent::__construct( $args );


    }

    function __call( $name, $arguments ) {
        return call_user_func_array( array( $this, $name ), $arguments );
    }

    function prepare_items() {
        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );
    }

    function column_default( $item, $column_name ) {
        if( isset( $item[ $column_name ] ) ) {
            return $item[ $column_name ];
        } else {
            return '';
        }
    }

    function get_table_classes() {
        return array( 'widefat', 'fixed', 'striped', 'messages' );
    }

    function no_items() {
        echo $this->no_items_message;
    }

    function set_sortable_columns( $args = array() ) {
        $return_args = array();
        foreach( $args as $k=>$val ) {
            if( is_numeric( $k ) ) {
                $return_args[ $val ] = array( $val, $val == $this->default_sorting_field );
            } else if( is_string( $k ) ) {
                $return_args[ $k ] = array( $val, $k == $this->default_sorting_field );
            } else {
                continue;
            }
        }
        $this->sortable_columns = $return_args;
        return $this;
    }

    function get_sortable_columns() {
        return $this->sortable_columns;
    }

    function set_columns( $args = array() ) {
        if( count( $this->bulk_actions ) ) {
            $args = array_merge( array( 'cb' => '<input type="checkbox" />' ), $args );
        }
        $this->columns = $args;
        return $this;
    }

    function get_columns() {
        return $this->columns;
    }

    function set_actions( $args = array() ) {
        $this->actions = $args;
        return $this;
    }

    function get_actions() {
        return $this->actions;
    }

    function set_bulk_actions( $args = array() ) {
        $this->bulk_actions = $args;
        return $this;
    }

    function get_bulk_actions() {
        return $this->bulk_actions;
    }

    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" class="wpc_msg_item" name="item[]" value="%s" data-new="' . ( $item['is_new'] ? 'true' : 'false' ) . '" />', $item['c_id']
        );
    }

    function column_date( $item ) {
        return '<span style="' . ( $item['is_new'] ? 'font-weight:bold;' : '' ) . '">' . WPC()->date_format( $item['date'] ) . '</span>';
    }

    function column_client_ids( $item ) {
        $html = '';
        $client_ids = WPC()->assigns()->get_assign_data_by_object( 'chain', $item['c_id'], 'client' );

        if( !empty( $client_ids ) ) {
            $wpc_private_messages = WPC()->get_settings( 'private_messages' );
            $display_name = ( isset( $wpc_private_messages['display_name'] ) && !empty( $wpc_private_messages['display_name'] ) ) ? $wpc_private_messages['display_name'] : 'user_login';

            $members = get_users( array(
                'include' => $client_ids,
                'order_by'=>$display_name,
                'order' => 'ASC'
            ) );

            foreach( $members as $key=>$member ) {
                if( $member->ID == get_current_user_id() ) {
                    $me_key = $key;
                }
            }

            if( isset( $me_key ) ) {
                $buf = $members[0];
                $members[0] = $members[$me_key];
                $members[$me_key] = $buf;
            }

            $title = '';
            foreach( $members as $member ) {
                if( $member->ID == get_current_user_id() ) {
                    $html .= __( 'Me', WPC_CLIENT_TEXT_DOMAIN ) . ', ';
                    $title .= __( 'Me', WPC_CLIENT_TEXT_DOMAIN ) . ', ';
                } else {
                    $html .= ( !empty( $member->$display_name ) ? $member->$display_name : $member->user_login ) . ', ';
                    $title .= ( !empty( $member->$display_name ) ? $member->$display_name : $member->user_login ) . ', ';
                }
            }

            $html = '<span class="wpc_messages_count" style="' . ( $item['is_new'] ? 'font-weight:bold;' : '' ) . '" title="' . __( 'Messages in chain', WPC_CLIENT_TEXT_DOMAIN ) . '">' .
                ( $item['messages_count'] > 1 ? ' (' . $item['messages_count'] . ')' : '' ) .
            '</span>'.
            '<span class="wpc_chain_members" title="' . substr( $title, 0, -2 ) . '" style="' . ( $item['is_new'] ? 'font-weight:bold;' : '' ) . '">' .
                substr( $html, 0, -2 ) .
            '</span>';
        }

        return $html;
    }

    function column_message_text( $item ) {
        if( $this->list_type == 'all' ) {
            $in_trash = in_array( $item['c_id'], $this->trash ) ? '<div class="trash_marker">' . __( 'Trash', WPC_CLIENT_TEXT_DOMAIN ) . '</div>' : '';
            $in_archive = in_array( $item['c_id'], $this->archive ) ? '<div class="archive_marker">' . __( 'Archive', WPC_CLIENT_TEXT_DOMAIN ) . '</div>' : '';
            $in_inbox = in_array( $item['c_id'], $this->inbox ) ? '<div class="inbox_marker">' . __( 'Inbox', WPC_CLIENT_TEXT_DOMAIN ) . '</div>' : '';
            $in_sent = in_array( $item['c_id'], $this->sent ) ? '<div class="sent_marker">' . __( 'Sent', WPC_CLIENT_TEXT_DOMAIN ) . '</div>' : '';

            return '<span class="wpc_chain_subject" style="' . ( $item['is_new'] ? 'font-weight:bold;' : '' ) . '">' . stripslashes( $item['subject'] ) . '</span>' .
            '<span class="wpc_chain_last_message">- ' . make_clickable( stripslashes( $item['content'] ) ) . '</span><span class="wpc_chain_markers">' . $in_trash . $in_archive . $in_inbox . $in_sent . '</span>';
        } else {
            return '<span class="wpc_chain_subject" style="' . ( $item['is_new'] ? 'font-weight:bold;' : '' ) . '">' . stripslashes( $item['subject'] ) . '</span>' .
            '<span class="wpc_chain_last_message">- ' . make_clickable( stripslashes( $item['content'] ) ) . '</span>';
        }
    }


    function wpc_set_pagination_args( $attr = array() ) {
        $this->set_pagination_args( $attr );
    }

}