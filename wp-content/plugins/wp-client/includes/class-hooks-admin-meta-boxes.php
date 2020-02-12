<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( !class_exists( "WPC_Hooks_Admin_Meta_Boxes" ) ) :

class WPC_Hooks_Admin_Meta_Boxes {


    /**
    * Meta constructor
    **/
    function __construct() {

        add_action( 'save_post', array( &$this, 'save_meta' ), 10, 2 );
        add_action( 'admin_init', array( &$this, 'meta_init' ) );

        add_action( 'load-post-new.php', array( &$this, 'meta_init_only_add' ) );
        add_action( 'load-post.php', array( &$this, 'meta_init_only_edit' ) );

    }


    /**
     * Add meta boxes
     */
    function meta_init() {
        //meta box for hubpage
        add_meta_box( 'wpc_client_portalhub', __( 'HUB Settings', WPC_CLIENT_TEXT_DOMAIN ),  array( &$this, 'portalhub_meta' ), 'portalhub', 'side', 'default' );
    }


    /**
     * Add meta boxes only for add post type
     */
    function meta_init_only_add() {

        //meta boxes for Portal Page (clientpage)
        add_meta_box( 'wpc_client_paste_content_portalpage', sprintf( __( 'Paste %s Content', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] ),  array( &$this, 'paste_content_portalpage_meta' ), 'clientspage', 'side', 'default' );
        add_meta_box( 'wpc_client_portalpage', sprintf( __( '%s Settings', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] ),  array( &$this, 'portalpage_meta' ), 'clientspage', 'side', 'default' );

        //add help tab at top right corner
        get_current_screen()->add_help_tab( array(
            'id'      => 'dr-main',
            'title'   => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
            'content' => '<p>' . sprintf( __( 'You can create a new %1$s from this menu. You can assign the %1$s to %2$s/%3$s, and using the Template select-box, you can choose to apply a "template" to the new %1$s. You can choose to apply the default %1$s Template, use an already existing %1$s, or if you have created a separate WordPress Page you would like to use as a Template, you can choose that from the select-box. It\'s important to note that once a template is assigned to a %1$s, making changes to the master template will not affect the %1$s\'s template. You are essentially creating a "copy" of the Template when you create a new %1$s.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'], WPC()->custom_titles['client']['p'], WPC()->custom_titles['circle']['p'] ) . '</p>' .
                '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000028674-portal" target="_blank">' . sprintf( __( '%s Basics', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] ) . '</a></p>' .
                '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000002916-hub-page-vs-portal" target="_blank">' . sprintf( __( 'HUB Page VS. %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] ) . '</a></p>',
        ) );
    }


    /**
     * Add meta boxes only for edit post type
     */
    function meta_init_only_edit() {
        //meta box for Portal Page (clientpage)
        add_meta_box( 'wpc_client_portalpage', sprintf( __( '%s Settings', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] ),  array( &$this, 'portalpage_meta' ), 'clientspage', 'side', 'default' );
    }


    //show metabox for hubpage
    function portalhub_meta( $post, $box ) {
        $current_page = 'wpc_client_portalhubs';

        $parent_post_id = apply_filters( 'wpc_change_portalhub_id', $post->ID );

        $order = get_post_meta( $parent_post_id, 'wpc_template_priority', true );
        $admin_label = get_post_meta( $post->ID, 'wpc_admin_label', true );
        $user_ids = WPC()->assigns()->get_assign_data_by_object( 'portalhub', $parent_post_id, 'client' );
        $groups_id = WPC()->assigns()->get_assign_data_by_object( 'portalhub', $parent_post_id, 'circle' );


        ?>

        <p>
            <label for="portalhub_admin_label"><?php _e( 'Admin Label', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
            <input type="text" name="portalhub_admin_label" id="portalhub_admin_label" class="wpc_metabox_field" value="<?php echo ! empty( $admin_label ) ? $admin_label : '' ?>" />
        </p>

        <?php if ( 0 != count( get_page_templates() ) ) {
            $template = get_post_meta( $post->ID, '_wp_page_template', true ); ?>
        <p>
            <label for="portalhub_template"><?php _e( 'Template', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
            <select name="portalhub_template" id="portalhub_template" class="wpc_metabox_field">
                <option value='default' <?php echo ( isset( $template ) || 'default' == $template ) ? 'selected' : '' ?><?php echo ( !isset( $template ) || '__use_same_as_hub_page' == $template ) ? 'selected' : '' ?> ><?php _e( 'Default Template', WPC_CLIENT_TEXT_DOMAIN ); ?></option>
                <?php page_template_dropdown( $template ); ?>
            </select>
        </p>
        <?php } ?>

        <?php if ( ! WPC()->flags['easy_mode'] ) { ?>
            <?php $wpc_style_schemes = WPC()->get_settings( 'style_schemes_settings' );
            $current_style_scheme = get_post_meta( $post->ID, '_wpc_style_scheme', true ); ?>
            <p>
                <label for="portalhub_style_scheme"><?php _e( 'Style Scheme', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                <select name="portalhub_style_scheme" id="portalhub_style_scheme" class="wpc_metabox_field">
                    <?php if ( count( $wpc_style_schemes ) ) {
                        foreach( $wpc_style_schemes as $key => $settings ) {
                            $selected = ( isset( $current_style_scheme ) && $key == $current_style_scheme ) ? 'selected' : '';
                            echo '<option value="' . $key . '" ' . $selected . ' >' . $settings['title'] . '</option>';
                        }
                    } ?>
                </select>
                <span class="description"><?php _e( 'you can update or create your scheme', WPC_CLIENT_TEXT_DOMAIN ) ?> <a href="<?php echo admin_url('/') ?>admin.php?page=wpclients_customize"><?php _e( 'here', WPC_CLIENT_TEXT_DOMAIN ) ?></a></span>
            </p>
        <?php } ?>

        <p>
            <label for="portalhub_order"><?php _e( 'Order', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label><br />
            <input type="number" name="portalhub_order" id="portalhub_order" size="4" class="wpc_metabox_field_order" value="<?php echo ( isset( $order ) ) ? $order : 0 ?>" />
        </p>

        <p>
            <?php
            $link_array = array(
                'title'   => sprintf( __( 'Assign %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                'text'    => sprintf( __( 'Allowed %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] )
            );
            $input_array = array(
                'name'  => 'wpc_clients',
                'id'    => 'wpc_clients',
                'value' => implode( ',', $user_ids )
            );
            $additional_array = array(
                'counter_value' => count( $user_ids )
            );
            WPC()->assigns()->assign_popup('client', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
            ?>
        </p>

        <p>
            <?php
            $link_array = array(
                'title'   => sprintf( __( 'assign %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ),
                'text'    => sprintf( __( 'Allowed %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] )
            );
            $input_array = array(
                'name'  => 'wpc_circles',
                'id'    => 'wpc_circles',
                'value' => implode( ',', $groups_id )
            );
            $additional_array = array(
                'counter_value' => count( $groups_id )
            );
            WPC()->assigns()->assign_popup('circle', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
            ?>
        </p>
        <script type="text/javascript">
            jQuery('#preview-action').hide();
        </script>
        <?php
    }


    function paste_content_portalpage_meta() {
        $current_screen = get_current_screen();
        $action = ! empty( $current_screen->action ) ? $current_screen->action : 'edit'; ?>

        <?php if ( 'add' == $action ) {
            $all_filter_page = array(
                '' => __( 'No Paste', WPC_CLIENT_TEXT_DOMAIN ),
                'wpc_portal_page_template' => sprintf( __( 'Default %s Template', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] ),
                'page' => __( 'Page', WPC_CLIENT_TEXT_DOMAIN ),
                'portal_page' => WPC()->custom_titles['portal_page']['s']
            );

            if ( current_user_can( 'wpc_manager' ) && ! current_user_can( 'administrator' ) ) {
                unset( $all_filter_page['page'] );
            } ?>

            <p>
                <label for="wpc_paste_content_from"><?php _e( 'Paste Content From', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label><br>
                <select name="wpc_paste_content[from]" id="wpc_paste_content_from" class="wpc_metabox_field">
                    <?php foreach ( $all_filter_page as $type_key=>$type_filter ) { ?>
                        <option value="<?php echo $type_key ?>"><?php echo $type_filter ?></option>
                    <?php } ?>
                </select>
            </p>

            <?php if ( ! ( current_user_can( 'wpc_manager' ) && ! current_user_can( 'administrator' ) ) ) { ?>
                <p id="wpc_paste_content_page_wrapper" class="wpc_paste_content_from" style="display: none;">
                    <label for="wpc_paste_content_page"><?php _e( 'Get Content From', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label><br>
                    <select name="wpc_paste_content[post_id]" class="wpc_chzn-select wpc_metabox_field" id="wpc_paste_content_page">
                        <?php
                        $args = array(
                            'post_type'         => 'page',
                            'posts_per_page'    => -1,
                        );

                        $myposts = get_posts( $args );
                        foreach( $myposts as $mypost ) {
                            setup_postdata( $mypost ); ?>
                            <option value="<?php echo $mypost->ID ?>"><?php echo ucwords( $mypost->post_name ); ?></option>
                        <?php } ?>
                    </select>
                </p>
            <?php } ?>

            <p id="wpc_paste_content_portal_page_wrapper" class="wpc_paste_content_from" style="display: none;">
                <label for="wpc_paste_content_portal_page"><?php _e( 'Get Content From', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label><br>
                <select name="wpc_paste_content[post_id]" class="wpc_chzn-select wpc_metabox_field" id="wpc_paste_content_portal_page">
                    <?php
                    $args = array(
                        'post_type'         => 'clientspage',
                        'posts_per_page'    => -1,
                    );

                    if ( current_user_can( 'wpc_manager' ) && ! current_user_can( 'administrator' ) ) {
                        global $wpdb;

                        $manager_id = get_current_user_id();

                        $manager_clients = WPC()->assigns()->get_assign_data_by_object( 'manager', $manager_id, 'client' );
                        $manager_groups = WPC()->assigns()->get_assign_data_by_object( 'manager', $manager_id, 'circle' );

                        $clients_groups = array();
                        foreach ( $manager_groups as $group_id ) {
                            $clients_groups = array_merge( $clients_groups, WPC()->groups()->get_group_clients_id( $group_id ) );
                        }
                        $manager_all_clients = array_unique( array_merge( $manager_clients, $clients_groups ) );

                        $groups_clients = array();
                        foreach ( $manager_clients as $client_id ) {
                            $groups_clients = array_merge( $groups_clients, WPC()->groups()->get_client_groups_id( $client_id ) );
                        }
                        $manager_all_groups = array_unique( array_merge( $manager_groups, $groups_clients ) );

                        $post_of_clients = WPC()->assigns()->get_assign_data_by_assign( 'portal_page', 'client', $manager_all_clients );
                        $post_of_groups = WPC()->assigns()->get_assign_data_by_assign( 'portal_page', 'circle', $manager_all_groups );
                        $cat_of_clients = WPC()->assigns()->get_assign_data_by_assign( 'portal_page_category', 'client', $manager_all_clients );
                        $cat_of_groups = WPC()->assigns()->get_assign_data_by_assign( 'portal_page_category', 'circle', $manager_all_groups );
                        $all_cats = array_unique( array_merge( $cat_of_clients, $cat_of_groups ) );
                        $posts_of_cats = $wpdb->get_col( "SELECT p.ID FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} pm ON ( p.ID = pm.post_id AND pm.meta_key = '_wpc_category_id' AND pm.meta_value IN ('" . implode( "','", $all_cats ) . "') ) WHERE p.post_type = 'clientspage'" );
                        $manager_all_post = array_unique( array_merge( $post_of_clients, $post_of_groups, $posts_of_cats ) );

                        $args['author'] = get_current_user_id();
                        $args['fields'] = 'ids';
                        $managers_posts = get_posts( $args );

                        $args['include'] = array_merge( $manager_all_post, $managers_posts );
                        unset( $args['author'], $args['fields'] );
                    }

                    $myposts = get_posts( $args );
                    foreach( $myposts as $mypost ) {
                        setup_postdata( $mypost ); ?>
                        <option value="<?php echo $mypost->ID ?>"><?php echo ucwords( $mypost->post_title ); ?></option>
                    <?php } ?>
                </select>
            </p>

            <p id="wpc_paste_content_apply" style="display: none;">
                <input type="button" id="wpc_paste_content_apply_button" class="button" value="<?php _e( 'Apply Paste', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                <span style="display: inline;" class="wpc_paste_info"></span>
            </p>
        <?php } ?>


        <script type="text/javascript">
            jQuery( document ).ready(function(){
                //change filter
                jQuery( '#wpc_paste_content_from' ).change( function() {
                    jQuery( '.wpc_paste_content_from' ).hide();

                    var paste_selector = jQuery( '#wpc_paste_content_' + jQuery(this).val() + '_wrapper' );

                    if ( paste_selector.length )
                        paste_selector.show();

                    if ( jQuery(this).val() !== '' )
                        jQuery( '#wpc_paste_content_apply' ).show();
                    else
                        jQuery( '#wpc_paste_content_apply' ).hide();
                });

                jQuery( '.wpc_chzn-select' ).chosen({
                    no_results_text: '<?php echo esc_js( __( 'No results matched', WPC_CLIENT_TEXT_DOMAIN ) ) ?>',
                    allow_single_deselect: true
                });

                jQuery('#wpc_paste_content_apply_button').click( function() {

                    if ( !confirm('<?php _e( 'Are you sure, to paste selected content to current content? Current content will be rewritten.', WPC_CLIENT_TEXT_DOMAIN ) ?>') )
                        return;

                    var from = jQuery('#wpc_paste_content_from').val();
                    var from_id = 'wpc_portal_page_template';
                    if ( from == 'wpc_portal_page_template' ) {
                        from_id = 'wpc_portal_page_template';
                    } else if ( from == 'page' || from == 'portal_page' ) {
                        from_id = jQuery('#wpc_paste_content_' + from ).val();
                    } else {
                        return;
                    }

                    jQuery('.wpc_paste_info').show().html( '<span class="wpc_ajax_loading"></span>' );
                    jQuery.ajax({
                        type: "POST",
                        url: '<?php echo get_admin_url() ?>admin-ajax.php',
                        data : {
                            action : 'wpc_paste_portal_page_content',
                            from_id : from_id,
                            security: '<?php echo wp_create_nonce( 'wpc_paste_portal_page_content_security_' . WPC()->members()->get_client_id() ) ?>'
                        },
                        dataType: "json",
                        success: function( data ) {
                            jQuery(".wpc_ajax_loading").hide('fast');

                            if ( data.status ) {

                                if ( document.body.classList.contains( 'block-editor-page' ) ) {

                                     //insert content to Gutenberg

                                    //remove current editor content
                                    wp.data.dispatch( 'core/editor' ).resetBlocks([]);
                                    //convert new content to blocks
                                    var blocks = wp.blocks.parse(data.content);
                                    //add new blocks to editor
                                    wp.data.dispatch( 'core/editor' ).insertBlocks( blocks );

                                } else if ( tinymce.activeEditor !== null ) {
                                    tinymce.activeEditor.setContent(data.content);
                                } else {
                                    jQuery('#content').html( data.content );
                                }

                                jQuery('#title').focus();
                            } else {
                                jQuery('.wpc_paste_info').html('<?php echo esc_js( __( 'Wrong content', WPC_CLIENT_TEXT_DOMAIN ) ) ?>');
                            }

                            setTimeout( function() {
                                jQuery('.wpc_paste_info').fadeOut( 1500 );
                            }, 2500 );
                        },
                        error: function( data ) {
                            jQuery('.wpc_paste_info').html('<?php echo esc_js( __( 'Something went wrong', WPC_CLIENT_TEXT_DOMAIN ) ) ?>');

                            setTimeout( function() {
                                jQuery('.wpc_paste_info').fadeOut( 1500 );
                            }, 2500 );
                        }
                    });
                });
            });
        </script>
    <?php }


    //show metabox for select template for clientpage
    function portalpage_meta( $post, $box ) {
        $current_page = 'add_client_page';
        $categories = WPC()->categories()->get_clientspage_categories();
        $category = get_post_meta( $post->ID, '_wpc_category_id', true );

        $order = get_post_meta( $post->ID, '_wpc_order_id', true );

        $user_ids = WPC()->assigns()->get_assign_data_by_object( 'portal_page', $post->ID, 'client' );

        $groups_id = WPC()->assigns()->get_assign_data_by_object( 'portal_page', $post->ID, 'circle' );

        $allow_edit_clientpage = get_post_meta( $post->ID, 'allow_edit_clientpage', true );

        $tags_array = wp_get_object_terms( $post->ID, 'wpc_tags' );
        $tags = array();
        foreach( $tags_array as $tag ) {
            $tags[] = "'" . $tag->name . "'";
        }
        $tags = implode( ',', $tags );
        ?>

        <p>
            <?php if ( 0 != count( get_page_templates() ) ) {
                $template = get_post_meta( $post->ID, '_wp_page_template', true ); ?>
                <label for="clientpage_template"><?php _e( 'Template', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                <select name="clientpage_template" id="clientpage_template" class="wpc_metabox_field">
                    <option value='default' <?php selected( !isset( $template ) || 'default' == $template ) ?>><?php _e( 'Default Template', WPC_CLIENT_TEXT_DOMAIN ); ?></option>
                    <?php page_template_dropdown( $template ); ?>
                </select>
            <?php } ?>
        </p>
        <p>
            <label for="clientpage_category"><?php _e( 'Category', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
            <select name="clientpage_category" id="clientpage_category" class="wpc_metabox_field">
                <option value="" <?php echo ( isset( $category ) && '' ==  $category ) ? 'selected' : '' ?> >(<?php _e( 'None' , WPC_CLIENT_TEXT_DOMAIN ); ?>)</option>
                <?php if ( 0 != count( $categories ) ) {
                    foreach( $categories as $value ) { ?>
                        <option value="<?php echo $value['id'] ?>" <?php echo ( isset( $value['id'] ) && $value['id'] ==  $category ) ? 'selected' : '' ?> ><?php echo $value['name']; ?></option>
                    <?php }
                } ?>
            </select>
        </p>

        <?php if( !WPC()->flags['easy_mode'] ) { ?>
            <?php $wpc_style_schemes = WPC()->get_settings( 'style_schemes_settings' );
            $current_style_scheme = get_post_meta( $post->ID, '_wpc_style_scheme', true ); ?>
            <p>
                <label for="clientpage_style_scheme"><?php _e( 'Style Scheme', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                <select name="clientpage_style_scheme" id="clientpage_style_scheme" class="wpc_metabox_field">
                    <?php if ( count( $wpc_style_schemes ) ) {
                        foreach( $wpc_style_schemes as $key => $settings ) {
                            $selected = ( isset( $current_style_scheme ) && $key == $current_style_scheme ) ? 'selected' : '';
                            echo '<option value="' . $key . '" ' . $selected . ' >' . $settings['title'] . '</option>';
                        }
                    } ?>
                </select>
                <span class="description"><?php _e( 'you can update or create your scheme', WPC_CLIENT_TEXT_DOMAIN ) ?> <a href="<?php echo admin_url('/') ?>admin.php?page=wpclients_customize"><?php _e( 'here', WPC_CLIENT_TEXT_DOMAIN ) ?></a></span>
            </p>

            <p>
                <label for="client_page_tags"><?php printf( __( '%s Tags', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] ) ?>:</label> <br/>
                <textarea id="client_page_tags" name="client_page_tags" rows="1" style="width: 254px;"></textarea>
                <span class="description"><?php _e( 'Note: Press Enter for add tag.', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
            </p>
        <?php } ?>

        <p>
            <label for="clientpage_order"><?php _e( 'Order', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label><br />
            <input type="number" name="clientpage_order" id="clientpage_order" size="4" class="wpc_metabox_field_order" value="<?php echo ( isset( $order ) ) ? $order : 0 ?>" />
        </p>

        <p>
            <?php
                $link_array = array(
                    'title'   => sprintf( __( 'Assign %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                    'text'    => sprintf( __( 'Allowed %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] )
                );
                $input_array = array(
                    'name'  => 'wpc_clients',
                    'id'    => 'wpc_clients',
                    'value' => implode( ',', $user_ids )
                );
                $additional_array = array(
                    'counter_value' => count( $user_ids )
                );
                WPC()->assigns()->assign_popup('client', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
            ?>
        </p>

        <p>
            <?php
                $link_array = array(
                    'title'   => sprintf( __( 'assign %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ),
                    'text'    => sprintf( __( 'Allowed %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] )
                );
                $input_array = array(
                    'name'  => 'wpc_circles',
                    'id'    => 'wpc_circles',
                    'value' => implode( ',', $groups_id )
                );
                $additional_array = array(
                    'counter_value' => count( $groups_id )
                );
                WPC()->assigns()->assign_popup('circle', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
            ?>
        </p>

        <p>
            <input type='checkbox' name='allow_edit_clientpage' id='allow_edit_clientpage' value='1' <?php echo ( 1 == $allow_edit_clientpage ) ? 'checked' : '' ?> />
            <b>
                <label for='allow_edit_clientpage'><?php printf( __( 'Allow the %s to edit this page', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) ?></label>
            </b>
        </p>

        <p>
            <input name="send_update" id="send_update" type="checkbox" value="1" />
            <b>
                <label for="send_update"><?php printf( __( 'Send Update to selected %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) ?></label>
            </b>
        </p>

        <p id="block_send" style="display: none;" >
            <?php
                $link_array = array(
                    'data-input' => 'send_wpc_clients',
                    'id'      => 'send_a_wpc_clients',
                    'title'   => sprintf( __( 'Send to %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                    'text'    => sprintf( __( 'Send %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] )
                );
                $input_array = array(
                    'name'  => 'send_wpc_clients',
                    'id'    => 'send_wpc_clients',
                    'value' => implode( ',', $user_ids ),
                    'data-include' => implode( ',', $user_ids )
                );
                $additional_array = array(
                    'counter_value' => count( $user_ids )
                );

                WPC()->assigns()->assign_popup('client', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
            ?>

            <br />

            <?php
                $link_array = array(
                    'data-input' => 'send_wpc_circles',
                    'title'   => sprintf( __( 'Send to %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ),
                    'text'    => sprintf( __( 'Send %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] )
                );
                $input_array = array(
                    'name'  => 'send_wpc_circles',
                    'id'    => 'send_wpc_circles',
                    'value' => implode( ',', $groups_id ),
                    'data-include' => implode( ',', $groups_id )
                );
                $additional_array = array(
                    'counter_value' => count( $groups_id )
                );
                WPC()->assigns()->assign_popup('circle', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
            ?>

        </p>
        <script type="text/javascript">
            jQuery('#preview-action').hide();

            jQuery( document ).ready(function(){
                jQuery( "#send_update" ).change( function() {
                    if(jQuery(this).attr("checked")){
                        jQuery( "#block_send" ).css( 'display', 'block' );
                    } else {
                        jQuery( "#block_send" ).css( 'display', 'none' );
                    }
                    return true;
                });

                jQuery('#client_page_tags').textext({
                    plugins : 'tags prompt focus autocomplete ajax arrow',
                    prompt : 'Add tag...',
                    tagsItems : [<?php echo( $tags ); ?>],
                    ajax : {
                        url : '<?php echo get_admin_url() ?>admin-ajax.php?action=wpc_get_all_tags',
                        dataType : 'json',
                        cacheResults : true
                    }
                });

                jQuery('#wpc_clients').change(function() {
                    var value = jQuery(this).val();
                    if ( '' == value )
                        value = -1;
                    jQuery('#send_wpc_clients').data('include', value);
                    jQuery('#send_wpc_clients').val( jQuery(this).val() );
                    var count = jQuery(this).val().split(",");
                    if( '' != count)
                        count = count.length;
                    else
                        count = 0;
                    jQuery('#send_wpc_clients').next().text( '(' + count + ')' );
                });

                jQuery('#wpc_circles').change(function() {
                    var value = jQuery(this).val();
                    if ( '' == value )
                        value = -1;
                    jQuery('#send_wpc_circles').data('include', value);
                    jQuery('#send_wpc_circles').val( jQuery(this).val() );
                    var count = jQuery(this).val().split(",");
                    if( '' != count)
                        count = count.length;
                    else
                        count = 0;
                    jQuery('#send_wpc_circles').next().text( '(' + count + ')' );
                });
            });
        </script>
    <?php }


    /**
     * Save meta when post save
     *
     * @param $post_id
     * @return string
     */
    function save_meta( $post_id, $post ) {
        //for quick edit
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            return $post_id;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }

        if ( defined( 'WPC_CLIENT_NOT_SAVE_META' ) && WPC_CLIENT_NOT_SAVE_META ) {
            return $post_id;
        }

        if ( empty ( $_REQUEST['_wpnonce'] ) ) {
            return $post_id;
        }

        if ( 'clientspage' == $post->post_type ) {
            if ( ! empty( $_POST ) ) {

                //save Client for Portal Page
                if ( isset( $_POST['wpc_clients'] ) ) {
                    if ( '' != $_POST['wpc_clients'] ) {
                        $selected_clients = explode( ',', $_POST['wpc_clients'] );

                        if ( is_array( $selected_clients ) && count( $selected_clients ) ) {
                            WPC()->assigns()->set_assigned_data( 'portal_page', $post_id, 'client', $selected_clients );
                        }
                    } else {
                        WPC()->assigns()->set_assigned_data( 'portal_page', $post_id, 'client', array() );
                    }
                }

                //save Client Circles for Portal Page
                if ( isset( $_POST['wpc_circles'] ) ) {
                    if ( '' != $_POST['wpc_circles'] ) {
                        $selected_circles = explode( ',', $_POST['wpc_circles'] );

                        if ( is_array( $selected_circles ) && count( $selected_circles ) ) {
                            WPC()->assigns()->set_assigned_data( 'portal_page', $post_id, 'circle', $selected_circles );
                        }
                    } else {
                        WPC()->assigns()->set_assigned_data( 'portal_page', $post_id, 'circle', array() );
                    }
                }


                //update clientpage file template
                if ( isset( $_POST['clientpage_template'] ) && '' != $_POST['clientpage_template'] ) {
                    update_post_meta( $post_id, '_wp_page_template', $_POST['clientpage_template'] );
                } else {
                    delete_post_meta( $post_id, '_wp_page_template' );
                }

                //update clientpage style scheme
                if ( !WPC()->flags['easy_mode'] ) {
                    if ( isset( $_POST['clientpage_style_scheme'] ) && '' != $_POST['clientpage_style_scheme'] ) {
                        update_post_meta( $post_id, '_wpc_style_scheme', $_POST['clientpage_style_scheme'] );
                    } else {
                        delete_post_meta( $post_id, '_wpc_style_scheme' );
                    }
                }

                //update clientpage file category
                if ( isset( $_POST['clientpage_category'] ) && '' != $_POST['clientpage_category'] ) {
                    update_post_meta( $post_id, '_wpc_category_id', $_POST['clientpage_category'] );
                } else {
                    delete_post_meta( $post_id, '_wpc_category_id' );
                }

                //update clientpage file category
                if ( !WPC()->flags['easy_mode'] ) {
                    if ( isset( $_POST['client_page_tags'] ) && '' != $_POST['client_page_tags'] ) {
                        $tags = preg_replace( '/^\[|\]$/', '', stripcslashes( $_POST['client_page_tags'] ) ) ;
                        $tags = explode( ",", $tags ) ;
                        foreach ( $tags as $key => $tag ) {
                            $temp_tag = preg_replace( '/^\"|\"$/', '', $tag );
                            $tags[ $key ] = stripcslashes( $temp_tag ) ;
                        }
                        wp_set_object_terms( $post_id, $tags, 'wpc_tags' );
                    } else {
                        wp_set_object_terms( $post_id, null, 'wpc_tags' );
                    }
                }

                //update clientpage file order
                if ( isset( $_POST['clientpage_order'] ) && '' != (int) $_POST['clientpage_order'] && 0 <= (int) $_POST['clientpage_order'] ) {
                    update_post_meta( $post_id, '_wpc_order_id', $_POST['clientpage_order'] );
                } else {
                    update_post_meta( $post_id, '_wpc_order_id', 0 );
                }

                //save option Allow Edit Portal Page
                if ( isset( $_POST['allow_edit_clientpage'] ) && '1' == $_POST['allow_edit_clientpage'] )
                    update_post_meta( $post_id, 'allow_edit_clientpage', 1 );
                else
                    update_post_meta( $post_id, 'allow_edit_clientpage', 0 );


                // send updates to client
                if ( isset( $_POST['send_update'] ) && '1' == $_POST['send_update'] ) {

                    $user_ids = ( !empty( $_POST['send_wpc_clients'] ) ) ? explode( ',', $_POST['send_wpc_clients'] ) : array();

                    $groups_id = ( !empty( $_POST['send_wpc_circles'] ) ) ? explode( ',', $_POST['send_wpc_circles'] ) : array();

                    //get clients from Client Circles
                    if ( is_array( $groups_id ) && 0 < count( $groups_id ) )
                        foreach( $groups_id as $group_id ) {
                            $user_ids = array_merge( $user_ids, WPC()->groups()->get_group_clients_id( $group_id ) );
                        }

                    $user_ids = array_unique( $user_ids );
                } else {
                    $user_ids = array();
                }

                //add clients staff to list
                if ( is_array( $user_ids ) && 0 < count( $user_ids ) ) {
                    $not_approved_staff = get_users( array( 'role' => 'wpc_client_staff', 'meta_key' => 'to_approve', 'fields' => 'ID', ) );
                    foreach( $user_ids as $user_id ) {

                        $args = array(
                            'role'          => 'wpc_client_staff',
                            'orderby'       => 'ID',
                            'order'         => 'ASC',
                            'meta_key'      => 'parent_client_id',
                            'meta_value'    => $user_id,
                            'exclude'       => $not_approved_staff,
                            'fields'        => 'ID',
                        );

                        $user_ids = array_merge( $user_ids, get_users( $args ) );

                    }
                }

                $user_ids = array_unique( $user_ids );

                //send update email to selected clients
                foreach ( $user_ids as $user_id ) {

                    $userdata   = (array) get_userdata( $user_id );
                    if ( !$userdata )
                        continue;

                    $link       = get_permalink( $post_id );

                    $args = array(
                        'client_id' => $user_id,
                        'page_id' => $link,
                        'page_title' => get_the_title( $post_id )
                    );

                    //send email
                    WPC()->mail( 'client_page_updated', $userdata['data']->user_email, $args, 'portal_page_updated' );
                }

                /*our_hook_
                    hook_name: wpc_client_insert_portal_page
                    hook_title: Add New Portal Page
                    hook_description: Hook runs when Admin/Manager added Portal Page.
                    hook_type: action
                    hook_in: wp-client
                    hook_location class.admin_meta_boxes.php
                    hook_param: int $post_id
                    hook_since: 4.2.9
                */
                do_action( 'wpc_client_insert_portal_page', $post_id );

            }
        }

        //query only from meta box then edit
        if ( 'portalhub' == $post->post_type ) {
            if ( ! empty( $_POST ) ) {

                $parent_post_id = apply_filters( 'wpc_change_portalhub_id', $post_id );

                //save Client for Portal HUB
                if ( ! empty( $_POST['wpc_clients'] ) ) {
                    $selected_clients = explode( ',', $_POST['wpc_clients'] );

                    if ( is_array( $selected_clients ) && count( $selected_clients ) ) {
                        WPC()->assigns()->set_assigned_data( 'portalhub', $parent_post_id, 'client', $selected_clients );
                    }
                } else {
                    WPC()->assigns()->set_assigned_data( 'portalhub', $parent_post_id, 'client', array() );
                }

                //save Client Circles for Portal HUB
                if ( ! empty( $_POST['wpc_circles'] ) ) {
                    $selected_circles = explode( ',', $_POST['wpc_circles'] );

                    if ( is_array( $selected_circles ) && count( $selected_circles ) ) {
                        WPC()->assigns()->set_assigned_data( 'portalhub', $parent_post_id, 'circle', $selected_circles );
                    }
                } else {
                    WPC()->assigns()->set_assigned_data( 'portalhub', $parent_post_id, 'circle', array() );
                }


                //update portalhub file template
                if ( ! empty( $_POST['portalhub_template'] ) ) {
                    update_post_meta( $post_id, '_wp_page_template', $_POST['portalhub_template'] );
                } else {
                    delete_post_meta( $post_id, '_wp_page_template' );
                }

                //update portalhub style scheme
                if ( ! WPC()->flags['easy_mode'] ) {
                    if ( ! empty( $_POST['portalhub_style_scheme'] ) ) {
                        update_post_meta( $post_id, '_wpc_style_scheme', $_POST['portalhub_style_scheme'] );
                    } else {
                        delete_post_meta( $post_id, '_wpc_style_scheme' );
                    }
                }


                //update portalhub file template
                if ( ! empty( $_POST['portalhub_order'] ) ) {
                    update_post_meta( $parent_post_id, 'wpc_template_priority', $_POST['portalhub_order'] );
                } else {
                    delete_post_meta( $parent_post_id, 'wpc_template_priority' );
                }


                //update portalhub admin label
                if ( ! empty( $_POST['portalhub_admin_label'] ) ) {
                    update_post_meta( $post_id, 'wpc_admin_label', $_POST['portalhub_admin_label'] );
                } else {
                    delete_post_meta( $post_id, 'wpc_admin_label' );
                }

                $request = new WP_Query;
                $posts = $request->query(array(
                    'name' => $post->post_name,
                    'post_type' => 'any',
                    'post__not_in' => array( $post_id )
                ));

                if( count( $posts ) ) {
                    global $wpdb;
                    $wpdb->update( $wpdb->posts,
                        array(
                            'post_name' => uniqid( $post->post_name )
                        ),
                        array(
                            'ID' => $post_id
                        )
                    );
                }

            }
        }

        return '';
    }


}

endif;


new WPC_Hooks_Admin_Meta_Boxes();