<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Widget for Client Portal Pages list
class wpc_client_widget_pp extends WP_Widget {
    //constructor
    function __construct() {
        $widget_ops = array( 'classname' => 'wpc_widget_pp', 'description' => sprintf( __( 'Display %s %s list.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['portal_page']['p'] ) );
        parent::__construct( 'wpc_client_widget_pp', WPC()->plugin['title'] . sprintf( __( ': %s list', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['p'] ), $widget_ops );
    }

    /** @see WP_Widget::widget */
    function widget( $args, $instance ) {
        global $wpdb;

        //fix for storm
        $before_widget = '';
        $before_title = '';
        $after_title = '';
        $after_widget = '';

        extract( $args );

        $title                  = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '' );
        $sort_by                = apply_filters( 'widget_sort_by', isset( $instance['sort_by'] ) ? $instance['sort_by'] : '' );
        $sort                   = apply_filters( 'widget_sort', isset( $instance['sort'] ) ? $instance['sort'] : '' );

        echo $before_widget;
        if ( $title )
            echo $before_title . $title . $after_title;
        ?>

    <div class="wpclient_portal_pages_block">

        <?php
        if ( is_user_logged_in() ) {

            if( current_user_can( 'wpc_client' ) ) {
                $user_id = get_current_user_id();
            } elseif( current_user_can( 'wpc_client_staff' ) ) {
                $user_id = get_user_meta( get_current_user_id(), 'parent_client_id', true );
            } else {
                //$user_id = get_current_user_id();
                $user_id = WPC()->current_plugin_page['client_id'];
            }

            $mypages_id = array();
            //Portal pages in categories with clients access
            $client_portal_page_category_ids = WPC()->assigns()->get_assign_data_by_assign( 'portal_page_category', 'client', $user_id );

            $results = $wpdb->get_col(
                "SELECT $wpdb->posts.ID
                FROM $wpdb->posts
                    INNER JOIN $wpdb->postmeta
                    ON $wpdb->postmeta.post_id = $wpdb->posts.ID
                WHERE $wpdb->posts.post_type = 'clientspage' AND
                    $wpdb->posts.post_status = 'publish' AND
                    $wpdb->postmeta.meta_key = '_wpc_category_id' AND
                    $wpdb->postmeta.meta_value IN('" . implode( "','", $client_portal_page_category_ids ) . "')"
            );

            if( isset( $results ) && 0 < count( $results ) ) {
                $mypages_id = array_merge( $mypages_id, $results );
            }

            //Portal pages with clients access
            $client_portal_page_ids = WPC()->assigns()->get_assign_data_by_assign( 'portal_page', 'client', $user_id );

            if( isset( $client_portal_page_ids ) && 0 < count( $client_portal_page_ids ) ) {
                $mypages_id = array_merge( $mypages_id, $client_portal_page_ids );
            }

            $client_groups_id = WPC()->groups()->get_client_groups_id( $user_id );

            if ( is_array( $client_groups_id ) && 0 < count( $client_groups_id ) ) {
                foreach( $client_groups_id as $group_id ) {

                    //Portal pages in categories with group access
                    $group_portal_page_category_ids = WPC()->assigns()->get_assign_data_by_assign( 'portal_page_category', 'circle', $group_id );

                    $results = $wpdb->get_col(
                        "SELECT $wpdb->posts.ID
                        FROM $wpdb->posts
                            INNER JOIN $wpdb->postmeta
                            ON $wpdb->postmeta.post_id = $wpdb->posts.ID
                        WHERE $wpdb->posts.post_type = 'clientspage' AND
                            $wpdb->posts.post_status = 'publish' AND
                            $wpdb->postmeta.meta_key = '_wpc_category_id' AND
                            $wpdb->postmeta.meta_value IN('" . implode( "','", $group_portal_page_category_ids ) . "')"
                    );

                    if ( 0 < count( $results ) ) {
                        $mypages_id = array_merge( $mypages_id, $results );
                    }

                    //Portal pages with group access
                    $group_portal_page_ids = WPC()->assigns()->get_assign_data_by_assign( 'portal_page', 'circle', $group_id );

                    if( isset( $group_portal_page_ids ) && 0 < count( $group_portal_page_ids ) ) {
                        $mypages_id = array_merge( $mypages_id, $group_portal_page_ids );
                    }

                }
            }
            $mypages_id = array_unique( $mypages_id );
            $in = "('" . implode( "','", $mypages_id ) . "')";

            if( $sort_by != 'order_id' ) {
                $client_portal_page = $wpdb->get_results(
                    "SELECT ID,
                        post_title,
                        post_name
                    FROM {$wpdb->posts}
                    WHERE ID IN $in AND post_status = 'publish'
                    ORDER BY $sort_by $sort",
                ARRAY_A );
            } else {

                $client_portal_page = $wpdb->get_results(
                    "SELECT p.ID,
                        p.post_title,
                        p.post_name,
                        pm1.meta_value
                    FROM {$wpdb->posts} p
                    LEFT JOIN {$wpdb->postmeta} pm1 ON( p.ID = pm1.post_id AND pm1.meta_key = '_wpc_order_id' )
                    WHERE p.ID IN $in AND p.post_status = 'publish'
                    ORDER BY CAST( pm1.meta_value as unsigned ) = 0 OR ISNULL(pm1.meta_value), CAST( pm1.meta_value as unsigned ) $sort",
                ARRAY_A );

            } ?>

            <ul style="list-style: none;">
            <?php
                foreach( $client_portal_page as $page ) {
                    echo '<li><a href="' . get_permalink( $page['ID'] ) . '">' . $page['post_title'] . '</a></li>';
                }

             ?>
            </ul>
            <?php
        }
        ?>
    </div><!--//wpc_client-widget_pp  -->


        <?php echo $after_widget; ?>

    <?php

    }

    /** @see WP_Widget::update */
    function update( $new_instance, $old_instance ) {
        $instance                       = $old_instance;
        $instance['title']              = strip_tags( $new_instance['title'] );
        $instance['sort_by']            = $new_instance['sort_by'];
        $instance['sort']               = $new_instance['sort'];
        return $instance;
    }

    /** @see WP_Widget::form */
    function form( $instance ) {
        if ( isset( $instance['title'] ) )
            $title = esc_attr( $instance['title'] );
        else
            $title = sprintf( __( '%s list', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['p'] );

        if ( isset( $instance['sort_by'] ) )
            $sort_by = esc_attr( $instance['sort_by'] );
        else
            $sort_by = 'post_date';

        if ( isset( $instance['sort'] ) )
            $sort = esc_attr( $instance['sort'] );
        else
            $sort = 'desc';

        ?>
            <p>
                <label for="<?php echo $this->get_field_name( 'title' ); ?>"><?php _e( 'Title:', WPC_CLIENT_TEXT_DOMAIN) ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />

            </p>

            <p>
                <label for="<?php echo $this->get_field_name( 'sort_by' ); ?>"><?php _e( 'Sort by:', WPC_CLIENT_TEXT_DOMAIN) ?></label>
                <select class="widefat" id="<?php echo $this->get_field_id( 'sort_by' ); ?>" name="<?php echo $this->get_field_name( 'sort_by' ); ?>">
                    <option value="post_date" <?php selected( $sort_by, 'post_date' ) ?>><?php _e( 'Date', WPC_CLIENT_TEXT_DOMAIN) ?></option>
                    <option value="post_title" <?php selected( $sort_by, 'post_title' ) ?>><?php _e( 'Title', WPC_CLIENT_TEXT_DOMAIN) ?></option>
                    <option value="order_id" <?php selected( $sort_by, 'order_id' ) ?>><?php _e( 'Order ID', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                </select>
<!--                <input class="widefat" id="<?php echo $this->get_field_id( 'sort_by' ); ?>" name="<?php echo $this->get_field_name( 'sort_by' ); ?>" type="text" value="<?php echo $sort_by; ?>" />
-->            </p>

            <p>
                <label for="<?php echo $this->get_field_name( 'sort' ); ?>"><?php _e( 'Sort:', WPC_CLIENT_TEXT_DOMAIN) ?></label>

                <select class="widefat" id="<?php echo $this->get_field_id( 'sort' ); ?>" name="<?php echo $this->get_field_name( 'sort' ); ?>">
                    <option value="asc" <?php selected( $sort, 'asc' ) ?>><?php _e( 'ASC', WPC_CLIENT_TEXT_DOMAIN) ?></option>
                    <option value="desc" <?php selected( $sort, 'desc' ) ?>><?php _e( 'DESC', WPC_CLIENT_TEXT_DOMAIN) ?></option>
                </select>
<!--                <input class="widefat" id="<?php echo $this->get_field_id( 'sort' ); ?>" name="<?php echo $this->get_field_name( 'sort' ); ?>" type="text" value="<?php echo $sort; ?>" />
-->            </p>
        <?php
    }

} // class wpc_client_widget_pp


add_action( 'widgets_init', function() { return register_widget( "wpc_client_widget_pp" ); } );