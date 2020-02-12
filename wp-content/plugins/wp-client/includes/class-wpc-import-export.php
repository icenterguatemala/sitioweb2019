<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( "WPC_Import_Export" ) ) :

/**
 * WPC_Import_Export
 */
class WPC_Import_Export {

    /**
     * The single instance of the class.
     *
     * @var WPC_Import_Export
     * @since 4.5
     */
    protected static $_instance = null;

    var $import_fields;

    var $export_fields = array(
        'clients' => array(
            'main_fields' => array(
                'ID',
                'user_login',
                'user_email',
                'user_registered',
                'display_name',
            ),
            'meta_fields' => array(
                'wpc_cl_business_name',
                'contact_phone'
            ),
            'custom_fields' => array(
            ),

        ),
        'staffs' => array(
            'main_fields' => array(
                'ID',
                'user_login',
                'user_email',
                'user_registered',
                'display_name',
            ),
            'meta_fields' => array(
            ),
            'custom_fields' => array(
            ),

        ),

        /* in future
        'circles' => array(
            'group_name',
            'auto_select',
            'auto_add_files',
            'auto_add_pps',
            'auto_add_manual',
            'auto_add_self',
            'clients',
        ),*/

    );

    /**
     * Instance.
     *
     * Ensures only one instance of WPC_Import_Export is loaded or can be loaded.
     *
     * @since 4.5
     * @static
     * @return WPC_Import_Export - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
    * Constructor
    */
    public function __construct() {

        $this->init_fields();

        //ajax - get more message
        add_action( 'wp_ajax_nopriv_wpc_get_import_items', array( &$this, 'ajax_get_import_items' ) );
        add_action( 'wp_ajax_wpc_get_import_items', array( &$this, 'ajax_get_import_items' ) );

        add_action( 'wp_ajax_wpc_get_export_item_data', array( &$this, 'ajax_get_export_item_data' ) );

        add_filter( 'wpc_client_item_import_clients', array( &$this, 'import_clients' ), 99, 2 );
        add_filter( 'wpc_client_item_import_staffs', array( &$this, 'import_staffs' ), 99, 2 );

        add_filter( 'wpc_client_item_export_clients', array( &$this, 'export_clients' ), 99, 2 );
        add_filter( 'wpc_client_item_export_staffs', array( &$this, 'export_staffs' ), 99, 2 );


        add_filter( 'wpc_client_import_get_list_clients_meta_fields', array( &$this, 'get_list_clients_meta_fields' ), 99 );
        add_filter( 'wpc_client_import_get_list_staffs_meta_fields', array( &$this, 'get_list_staffs_meta_fields' ), 99 );
        add_filter( 'wpc_client_export_get_list_clients_meta_fields', array( &$this, 'get_list_clients_meta_fields' ), 99 );
        add_filter( 'wpc_client_export_get_list_staffs_meta_fields', array( &$this, 'get_list_staffs_meta_fields' ), 99 );

        add_action( 'wpc_client_init', array( &$this, 'init_fields' ) );
    }


    function init_fields() {

        $this->import_fields = array(
            'clients' => array(
                'main_fields' => array(
                    'user_login' => array(
                        'title'     => 'Username',
                        'desc'      => 'User Login',
                        'required'  => true,
                    ),
                    'user_pass' => array(
                        'title'     => 'Password',
                        'desc'      => '=',
                        'required'  => true,
                    ),
                    'display_name' => array(
                        'title'     => 'Display Name',
                        'desc'      => '',
                        'required'  => false,
                    ),
                    'business_name' => array(
                        'title'     => 'Business Name',
                        'desc'      => '',
                        'required'  => true,
                    ),
                    'user_email' => array(
                        'title'     => 'User Email',
                        'desc'      => '',
                        'required'  => true,
                    ),
                    'contact_phone' => array(
                        'title'     => 'Contact Phone',
                        'desc'      => '',
                        'required'  => false,
                    ),
                    'send_password' => array(
                        'title'     => 'Send Password',
                        'desc'      => '',
                        'required'  => false,
                    ),
                    'temp_password' => array(
                        'title'     => 'Temporary Password',
                        'desc'      => '',
                        'required'  => false,
                    ),
                    'client_circles' => array(
                        'title'     => sprintf( '%s %s', WPC()->custom_titles['client']['p'], WPC()->custom_titles['circle']['p'] ),
                        'desc'      => '',
                        'required'  => false,
                    )
                ),
                'custom_fields' => array(
                ),
                'meta_fields' => array(
                ),

            ),
            'staffs' => array(
                'main_fields' => array(
                    'user_login' => array(
                        'title'     => 'Username',
                        'desc'      => 'User Login',
                        'required'  => true,
                    ),
                    'user_pass' => array(
                        'title'     => 'Password',
                        'desc'      => '=',
                        'required'  => false,
                    ),
                    'user_email' => array(
                        'title'     => 'User Email',
                        'desc'      => '',
                        'required'  => true,
                    ),
                    'display_name' => array(
                        'title'     => 'Display Name',
                        'desc'      => '',
                        'required'  => false,
                    ),
                    'send_password' => array(
                        'title'     => 'Send Password',
                        'desc'      => '',
                        'required'  => false,
                    ),
                    'temp_password' => array(
                        'title'     => 'Temporary Password',
                        'desc'      => '',
                        'required'  => false,
                    ),
                    'client_username' => array(
                        'title'     => sprintf( __( '%s Username', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
                        'desc'      => '',
                        'required'  => false,
                    )
                ),
                'custom_fields' => array(
                ),
                'meta_fields' => array(
                ),

            ),

            /* in future
            'circles' => array(
                'group_name',
                'auto_select',
                'auto_add_files',
                'auto_add_pps',
                'auto_add_manual',
                'auto_add_self',
                'clients',
            ),*/
        );
    }


    /*
    * check download action
    */
    function _check_download() {

	    global $wpdb, $wp_query;
	    @ignore_user_abort(true);
	    @set_time_limit(0);

	    if ( !empty( $_GET['id'] ) ) {
	        $export_data = get_user_meta( get_current_user_id(), 'wpc_export_data', true );

	        if ( !( !empty( $export_data['export_id'] ) && $_GET['id'] == $export_data['export_id'] ) ) {
	            exit( __( 'Sorry: Wrong export ID!', WPC_CLIENT_TEXT_DOMAIN ) );
	        }
	    } else {
	        exit( __( 'Export csv does not exist', WPC_CLIENT_TEXT_DOMAIN ) );
	    }

	    $mime_types = wp_get_mime_types();

	    $type       = ( isset( $_GET['type'] ) ) ? $_GET['type'] : '';

	    $target_path = WPC()->get_upload_dir( 'wpclient/_exports/' );
	    $target_path = $target_path . $type . get_current_user_id() . '_export_file.csv';

	    if( !file_exists( $target_path ) ) {
	        exit( __( 'Export csv does not exist', WPC_CLIENT_TEXT_DOMAIN ) );
	    }

	    header("Pragma: no-cache");
	    header("Expires: 0");
	    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	    header("Robots: none");
	    header("Content-Description: File Transfer");
	    header("Content-Transfer-Encoding: binary");
	    header("Content-type: {$mime_types['csv']}");
	    header( "Content-Disposition: attachment; filename=\"" . $type . '_export.csv' . "\"" );
	    $fsize = filesize( $target_path );
	    header("Content-length: $fsize");

	    $levels = ob_get_level();
	    for ( $i=0; $i<$levels; $i++ )
	        @ob_end_clean();

	    //for files on server
	    WPC()->readfile_chunked( $target_path );
	    exit;
    }


    /**
    * get item for preview
    */
    function ajax_get_import_items() {
        $type           = ( isset( $_POST['type'] ) ) ? $_POST['type'] : '';
        $delimiter      = ( isset( $_POST['delimiter'] ) ) ? $_POST['delimiter'] : ',';
        $head           = ( !empty( $_POST['head'] ) && 'false' != $_POST['head']  ) ? true : false;
        $item_number    = ( isset( $_POST['item_number'] ) && 0 < $_POST['item_number'] ) ? $_POST['item_number'] - 1 : 0;
        $item_html      = '';
        $item_data      = array();
        $headers        = array();

        $target_path = WPC()->get_upload_dir( 'wpclient/_imports/' );
        $target_path = $target_path . $type . get_current_user_id() . '_import_file.csv';

        if ( file_exists( $target_path ) && ( $handle = fopen( $target_path, "r" ) ) !== FALSE ) {
            $row = 0;
            while ( ( $data = fgetcsv( $handle, 1000, $delimiter ) ) !== FALSE ) {

                if ( 0 == $row && $head ) {

                    foreach( $data as $key => $val ) {
                        $headers[] = $val;
                    }

                    $head = false;
                    continue;
                }


                if ( ! count( $headers ) ) {
                    foreach( $data as $key => $val ) {
                        $headers[] = 'column_' . ( $key + 1 );
                    }
                }

                if ( $item_number == $row ) {
                    $item_data = $data;
                }

                $row++;
            }

            if ( count( $item_data ) && count( $item_data ) == count( $headers ) ) {

                $item_html .= '<table class="import_items" >';
                $item_html .= '<tr>';
                $item_html .= '<th style="width: 3%;"></th>';
                $item_html .= '<th class="import_items_headers">' . __( 'Headers', WPC_CLIENT_TEXT_DOMAIN ) . '</th>';
                $item_html .= '<th>' . __( 'Values', WPC_CLIENT_TEXT_DOMAIN ) . '</th>';
                $item_html .= '</tr>';
                $fields_key = array();

                foreach( $item_data as $key => $item ) {
                    $fields_key[] = $headers[$key];
                    $item_html .= '<tr>';
                    $item_html .= '<td>' . ( $key + 1 ) . '</td>';
                    $item_html .= '<td><b class="place_holders">' . $headers[$key] . '</b></td>';
                    $item_html .= '<td>' . $item . '</td>';
                    $item_html .= '</tr>';

                }
                $item_html .= '</table>';

            } else {
                $item_html = __( 'Seems Delimiter is wrong!', WPC_CLIENT_TEXT_DOMAIN );
            }


            echo json_encode( array( 'status' =>true, 'items' => $row, 'item_html' => $item_html, 'fields_key' => $fields_key ) );
            exit;
        }

        exit;
    }


    /**
     * Getting data value for current item by key
     */
    function ajax_get_export_item_data() {

        if ( !empty( $_POST['type'] ) && !empty( $_POST['item_id'] ) && !empty( $_POST['data_key'] ) ) {
            if ( 'clients' == $_POST['type'] || 'staffs' == $_POST['type'] ) {
                $meta_value = get_user_meta( $_POST['item_id'], $_POST['data_key'], true );

                if ( is_array( $meta_value ) )
                    $meta_value = serialize( $meta_value );

                echo json_encode( array( 'status' =>true, 'data_value' => $meta_value ) );
                exit;
            }
        }

        exit;
    }


    /**
     * Ajax for save, delete and use template for export and import
     */
    function save_export_import_template() {
        $type               = $_POST['action_data_type']; //type of page (Clients or Staffs)
        $type_page          = $_POST['type_page']; //import or export
        $array_line_key     = $_POST['array_line_key'];
        $array_export_value = $_POST['array_export_value'];
        $template_name      = $_POST['template_name'];

        if ( empty( $template_name ) ) {
            wp_send_json_error( __( 'Empty template name.', WPC_CLIENT_TEXT_DOMAIN ) );
        }

        $not_empty_arrays_temp_flag = false;
        foreach ( $array_line_key as $value ) {
            if ( ! empty( $value ) ) {
                $not_empty_arrays_temp_flag = true;
                break;
            }
        }

        if ( ! $not_empty_arrays_temp_flag ) {
            foreach ( $array_export_value as $value ) {
                if ( ! empty( $value ) ) {
                    $not_empty_arrays_temp_flag = true;
                    break;
                }
            }
        }

        if ( ! $not_empty_arrays_temp_flag ) {
            wp_send_json_error( __( 'The template was empty.', WPC_CLIENT_TEXT_DOMAIN ) );
        }

        $array_link_option = get_option( 'wpc_imp_exp_template' );

        if ( ! empty( $type ) ) {
            if ( ! empty( $array_link_option[ $type_page ][ $type ] ) ) {
                foreach ( $array_link_option[ $type_page ][ $type ] as $value ) {
                    if ( $value == $template_name ) {
                        wp_send_json_error( __( 'This template already exists.', WPC_CLIENT_TEXT_DOMAIN ) );
                    }
                }
            }

            $id                                                                        = uniqid();
            $array_link_option[ $type_page ][ $type ][ 'wpc_clients_template_' . $id ] = $template_name;
            update_option( 'wpc_imp_exp_template', $array_link_option );

            $array_template   = array();
            $array_template[] = $array_line_key;
            $array_template[] = $array_export_value;
            update_option( 'wpc_clients_template_' . $id, $array_template );
            wp_send_json_success();
        }

        wp_send_json_error(__('Could not save template.',WPC_CLIENT_TEXT_DOMAIN));
    }


    function download_export_import_templates() {
        $type      = $_POST['action_data_type'];
        $type_page = $_POST['type_page'];

        if ( ! empty( $type ) ) {
            $array_link_option = get_option( 'wpc_imp_exp_template' );
            if ( ! $array_link_option ) {
                wp_send_json_error( __( 'Option wpc_imp_exp_template not exists.', WPC_CLIENT_TEXT_DOMAIN ) );
            }

            if ( empty( $array_link_option[ $type_page ][ $type ] ) ) {
                wp_send_json_error( __( 'No templates.', WPC_CLIENT_TEXT_DOMAIN ) );
            }

            ob_start();
            ?>
            <div id="use_template_form">
                <label>Select a template:
                    <select id="templates_names">
                        <?php foreach ( $array_link_option[ $type_page ][ $type ] as $key => $value ) { ?>
                            <option value="<?php echo str_replace( 'wpc_clients_template_', '', $key ); ?>"><?php echo $value ?></option>
                        <?php } ?>
                    </select>
                </label>
                <button id="use_template_name"
                        class="button-primary"><?php _e( 'Load template', WPC_CLIENT_TEXT_DOMAIN ) ?></button>
                <a id="delete_template_name"
                   class="wpc_delete_template_button"><?php _e( 'Delete Template', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
            </div>
            <?php
            $html = ob_get_clean();
            wp_send_json_success( $html );
        }

        wp_send_json_error( __( 'Failed to load template.', WPC_CLIENT_TEXT_DOMAIN ) );
    }

    function use_export_template() {
        $option_key = $_POST['template_option'];
        $template    = get_option('wpc_clients_template_' . $option_key );
        if ( $template ) {
            wp_send_json_success( $template );
        }

        wp_send_json_error( __( 'Template not exist', WPC_CLIENT_TEXT_DOMAIN ) );
    }


    function delete_export_import_template() {
        $type        = $_POST['action_data_type'];
        $type_page   = $_POST['type_page'];
        $option_name = 'wpc_clients_template_' . $_POST['template_option'];

        $array_link_option = get_option( 'wpc_imp_exp_template' );

        if ( ! empty( $type ) ) {
            if ( empty( $array_link_option[ $type_page ][ $type ][ $option_name ] ) ) {
                wp_send_json_error( _e( 'Template not exist.', WPC_CLIENT_TEXT_DOMAIN ) );
            }
            unset( $array_link_option[ $type_page ][ $type ][ $option_name ] );
            update_option( 'wpc_imp_exp_template', $array_link_option );
            $res = delete_option( $option_name );

            if ( ! $res ) {
                wp_send_json_error( _e( 'Could not delete option template.', WPC_CLIENT_TEXT_DOMAIN ) );
            }
            wp_send_json_success();
        }
        wp_send_json_error(_e('Could not delete template.' ,WPC_CLIENT_TEXT_DOMAIN));

    }




    /**
    * Render HTML
    */
    function render_import_step_2() {
        $action_data = $this->get_action_data('import');
        $type = $action_data['type'];
        $import_id = $action_data['import_id'];

        if ( isset( $_POST['step'] ) && '2' == $_POST['step'] ) {
            $delimiter = !empty( $_POST['delimiter'] ) ? $_POST['delimiter'] : ',';
            $delimiter = ( '"' == $delimiter || "'" == $delimiter ) ? ',' : $delimiter;

            $fline_head = ( !empty( $_POST['fline_head'] ) && 'true' == $_POST['fline_head'] ) ? true : false;

            $action_data = array_merge( $action_data, array(
                'delimiter'     => $delimiter,
                'fline_head'    => $fline_head
            ) );

            update_user_meta( get_current_user_id(), 'wpc_import_data', $action_data );

            WPC()->redirect( get_admin_url() . 'admin.php?page=wpclients&tab=import-export&action=import&import_id=' . $import_id . '&step=3' );
        } ?>

        <h2><?php _e( 'We found these items for Import:', WPC_CLIENT_TEXT_DOMAIN ) ?></h2>
        <div style="position: relative; float:left;width:100%;margin:0;padding:0;">
            <div class="wpc_imp_exp_navi_block">
                <a href="javascript:void(0);" id="wpc_imp_exp_navi_previous" style="display: none;">< <?php _e( 'Previous', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
                <span id="wpc_imp_exp_navi_previous_fake" >< <?php _e( 'Previous', WPC_CLIENT_TEXT_DOMAIN ) ?></span>

                <span style="margin: 0 20px;">
                    <input type="text" id="wpc_imp_exp_navi_page" value="1" style=" width: 40px;" />
                    <?php _e( 'of', WPC_CLIENT_TEXT_DOMAIN ) ?>
                    <span id="wpc_imp_exp_navi_page_total">0</span>
                </span>

                <a href="javascript:void(0);" id="wpc_imp_exp_navi_next"><?php _e( 'Next', WPC_CLIENT_TEXT_DOMAIN ) ?> ></a>
                <span id="wpc_imp_exp_navi_next_fake" style="display: none;"><?php _e( 'Next', WPC_CLIENT_TEXT_DOMAIN ) ?> ></span>

            </div>


            <div style="position: absolute; top: -2px; right: -1px; text-align: right; ">
                <form action="<?php echo get_admin_url() ?>admin.php?page=wpclients&tab=import-export&action=import&import_id=<?php echo $import_id ?>&step=2" method="post" id="wpc_import_form_step_2" >
                    <input type="hidden" name="step" value="2" />
                    <label>
                        <?php _e( 'Delimiter:', WPC_CLIENT_TEXT_DOMAIN ) ?>
                        <input type="text" style="width: 30px;" maxlength="1" id="wpc_imp_delimiter" name="delimiter" value="," />
                    </label>
                    <br>
                    <label>
                        <?php _e( 'First Line Header:', WPC_CLIENT_TEXT_DOMAIN ) ?>
                        <input type="checkbox" id="wpc_imp_fline_head" checked value="true" name="fline_head" />
                    </label>
                </form>
            </div>
        </div>


        <hr />
        <br>


        <div id="wpc_imp_exp_item_content">
            <div class="meta_ajax_loading"></div>
        </div>


        <br>

        <a href="javascript:void(0);" class="wpc_imp_exp_button wpc_imp_exp_back" >
            <span class="wpc_imp_exp_button_label">< <?php _e( 'Back', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
        </a>

        <a href="javascript:void(0);" class="wpc_imp_exp_button wpc_imp_exp_continue" >
            <span class="wpc_imp_exp_button_label"><?php _e( 'Continue', WPC_CLIENT_TEXT_DOMAIN ) ?> ></span>
        </a>

        <script type="text/javascript">
            jQuery( document ).ready( function() {

                var item_number = 1;
                var delimiter = ',';
                var fl_head = true;
                var total_items = 0;

                //ajax for get items
                jQuery.fn.wpc_load_item = function() {

                    jQuery( '#wpc_imp_exp_item_content' ).html( '<div class="meta_ajax_loading"></div>' );

                    jQuery.ajax({
                        type: 'POST',
                        url: '<?php echo admin_url( 'admin-ajax.php' ) ?>',
                        data: 'action=wpc_get_import_items&type=<?php echo $type ?>&head=' + fl_head + '&delimiter=' + delimiter + '&item_number=' + item_number,
                        dataType: 'json',
                        success: function( data ){
                            if( data.status ) {
                                total_items = data.items;
                                jQuery( '#wpc_imp_exp_item_content' ).html( data.item_html );
                                jQuery( '#wpc_imp_exp_navi_page_total' ).html( data.items );
                                jQuery( '#wpc_imp_exp_navi_page' ).val( item_number );


                                if ( item_number <= 1 ) {
                                    jQuery( '#wpc_imp_exp_navi_previous' ).hide();
                                    jQuery( '#wpc_imp_exp_navi_previous_fake' ).show();
                                } else {
                                    jQuery( '#wpc_imp_exp_navi_previous' ).show();
                                    jQuery( '#wpc_imp_exp_navi_previous_fake' ).hide();
                                }

                                if ( total_items > 0 && item_number >= total_items) {
                                    jQuery( '#wpc_imp_exp_navi_next' ).hide();
                                    jQuery( '#wpc_imp_exp_navi_next_fake' ).show();
                                } else {
                                    jQuery( '#wpc_imp_exp_navi_next' ).show();
                                    jQuery( '#wpc_imp_exp_navi_next_fake' ).hide();
                                }

                            }
                        }
                    });
                };


                //first time items load
                jQuery( this ).wpc_load_item();



                //previous page
                jQuery( '#wpc_imp_exp_navi_previous' ).click( function() {
                    if ( item_number > 1 ) {
                        item_number = item_number - 1;

                        jQuery( '#wpc_imp_exp_navi_next' ).show();
                        jQuery( '#wpc_imp_exp_navi_next_fake' ).hide();

                        jQuery( this ).wpc_load_item();

                    }

                    if ( item_number <= 1 ) {
                        jQuery( this ).hide();
                        jQuery( '#wpc_imp_exp_navi_previous_fake' ).show();
                    }
                });


                //next page
                jQuery( '#wpc_imp_exp_navi_next' ).click( function() {
                    if ( total_items > 0 && item_number < total_items ) {
                        item_number = item_number + 1;

                        jQuery( '#wpc_imp_exp_navi_previous' ).show();
                        jQuery( '#wpc_imp_exp_navi_previous_fake' ).hide();

                        jQuery( this ).wpc_load_item();
                    }

                    if ( total_items > 0 && item_number >= total_items) {
                        jQuery( this ).hide();
                        jQuery( '#wpc_imp_exp_navi_next_fake' ).show();
                    }

                });


                //changed page
                jQuery( '#wpc_imp_exp_navi_page' ).change( function() {
                    var temp_item_number = jQuery( this ).val();

                    if ( temp_item_number <= 0 ) {
                        item_number = 1;
                        jQuery( this ).val( item_number );
                    } else if ( total_items > 0 && temp_item_number > total_items ) {
                        item_number = total_items;
                        jQuery( this ).val( item_number );
                    } else {
                        item_number = temp_item_number;
                    }

                    jQuery( this ).wpc_load_item();
                });


                //changed delimiter
                jQuery( '#wpc_imp_delimiter' ).change( function() {
                    item_number = 1;

                    if ( '' == jQuery( this ).val() || '"' == jQuery( this ).val() || "'" == jQuery( this ).val() ) {
                        delimiter = ',';
                        jQuery( this ).val( ',' );
                    } else {
                        delimiter = jQuery( this ).val();
                    }

                    jQuery( this ).wpc_load_item();
                });


                //changed Header line check box
                jQuery( '#wpc_imp_fline_head' ).change( function() {
                    item_number = 1;
                    fl_head = ( 'checked' == jQuery( this ).attr( 'checked' ) ) ? true : false;

                    jQuery( this ).wpc_load_item();
                });


                //continue button
                jQuery( '.wpc_imp_exp_continue' ).click( function() {
                    jQuery( '#wpc_import_form_step_2' ).submit();
                });

                //back button
                jQuery( '.wpc_imp_exp_back' ).click( function() {
                    window.location = '<?php echo get_admin_url() . 'admin.php?page=wpclients&tab=import-export' ?>';
                });


            });
        </script>

    <?php
    }

    /**
    * Render HTML
    */
    function render_import_step_3() {
        $action_data = $this->get_action_data( 'import' );
        $type = $action_data['type'];
        $import_id = $action_data['import_id'];
        $type_page = 'import'; //for ajax method

        $delimiter = !empty( $action_data['delimiter'] ) ? $action_data['delimiter'] : ',';
        $fline_head = !empty( $action_data['fline_head'] ) ? 'true' : 'false';

        $baselink = add_query_arg( array( 'page' => 'wpclients', 'tab' => 'import-export', 'action' => 'import', 'import_id' => $import_id, 'step' => '3' ), admin_url( 'admin.php' ) );

        if ( !empty( $_POST['matched_fields'] ) ) {

            foreach ( $this->import_fields[$type]['main_fields'] as $key => $value ) {
                if ( empty( $_POST['matched_fields']['main_fields'][$key] ) && ( isset( $value['required'] ) && true == $value['required'] ) )
                    $this->trigger_error( '301', $baselink );
            }

            $action_data = array_merge( $action_data, array(
                'associations'          => $_POST['matched_fields'],
                'update_exist_clients'  => !empty( $_POST['update_exist_clients'] ) ? true : false,
            ) );

            update_user_meta( get_current_user_id(), 'wpc_import_data', $action_data );
            WPC()->redirect( get_admin_url() . 'admin.php?page=wpclients&tab=import-export&action=import&import_id=' . $import_id . '&step=4');
        } ?>

        <h2><?php _e( 'Please match fields for Import:', WPC_CLIENT_TEXT_DOMAIN ) ?></h2>

        <form action="<?php echo $baselink ?>" method="post" id="wpc_import_form_step_3" >

            <div style="float: left;width:100%;clear:both;margin: 0 0 10px 0;">
                <label style="float: right;">
                    <?php printf( __( 'Update existing %s via import:', WPC_CLIENT_TEXT_DOMAIN ), $type ) ?>
                    <input type="checkbox" id="wpc_imp_update_exist_clients" checked value="true" name="update_exist_clients" />
                </label>
                <div style="float:left; text-align: left; ">
                    <input type="button" id="save_template_fields" value="Save as Template" class="button">
                    <input type="button" id="use_template_fields" value="Load template" class="button">
                </div>
            </div>

            <div id="wpc_imp_exp_item_content" style="width: 40%; float: right;">
                <div id="wpc_imp_exp_match_fields">
                    <div id="wpc_imp_exp_match_fields_header">
                        <h4><?php _e( 'Import Fields', WPC_CLIENT_TEXT_DOMAIN ) ?></h4>
                    </div>
                    <div id="wpc_imp_exp_match_fields_content">
                        <div class="meta_ajax_loading"></div>
                    </div>
                </div>
            </div>

            <div id="wpc_imp_exp_item_holders" style="width: 40%; float: left;">
                <div id="wpc_imp_exp_item_holders_header">
                    <?php if ( isset( $this->import_fields[$type] ) ) { ?>
                        <h4><?php echo ( ( 'clients' == $type ) ? WPC()->custom_titles['client']['p'] : WPC()->custom_titles['staff']['p'] ) . ' ' . __( 'Fields', WPC_CLIENT_TEXT_DOMAIN ) ?></h4>
                    <?php } ?>
                </div>
                <div id="wpc_imp_exp_item_holders_content">
                    <?php
                    if ( isset( $this->import_fields[$type] ) ) {
                        foreach( $this->import_fields[$type] as $k => $v ) {
                            if ( 'main_fields' == $k ) {
                                echo '<table class="table_default_field">';
                                foreach( $v as $key => $value ) {
                                    $required = ( isset( $value['required'] ) && true == $value['required'] ) ? '&nbsp;<span class="required">*</span>' : '';
                                    echo '<tr class="export_field">';
                                    echo '<th class="key_custom_value" align="left"><label for="matched_fields_main_fields_' . $key . '">' . $value['title'] . $required . '</label></th>';
                                    echo '<td class="custom_value"><input type="text" class="DropTarget matched_fields_main_fields" name="matched_fields[main_fields][' . $key . ']" id="matched_fields_main_fields_' . $key . '" placeholder="' . __( 'Import Value', WPC_CLIENT_TEXT_DOMAIN ) . '" data-field_name="'. $key .'" value="" /></td>';
                                    echo '</tr>';
                                }
                                echo '</table>';
                            } elseif ( 'meta_fields' == $k ) {

                                $fields = apply_filters( 'wpc_client_import_get_list_' . $type . '_' . $k, array() );

                                if ( !empty( $fields ) ) {
                                    echo '<table id="meta_fields_list_table">';
                                    if ( !empty( $_POST['matched_fields']['meta_fields'] ) ) {
                                        foreach( $_POST['matched_fields']['meta_fields'] as $m_key => $m_val  ) {
                                            echo '<tr class="export_field"><th class="key_custom_value" align="left"><label for="matched_fields_meta_fields_' . $m_key . '">' . $m_key . '</label></th><td class="custom_value"><input type="text" class="DropTarget" name="matched_fields[meta_fields][' . $m_key . ']" placeholder="' . __( 'Import Value', WPC_CLIENT_TEXT_DOMAIN ) . '" value="' . $_POST['matched_fields']['meta_fields'][$m_key] . '" id="matched_fields_meta_fields_' . $m_key . '" /></td></td></tr>';
                                        }
                                    }

                                    if ( !empty( $_POST['matched_fields']['meta_new_fields'] ) ) {
                                        foreach( $_POST['matched_fields']['meta_new_fields'] as $m_key => $m_val  ) {
                                            echo '<tr class="export_field"><th class="key_custom_value" align="left"><input type="text" class="DropTarget" name="matched_fields[meta_new_fields][' . $m_key . '][key]" value="' . $m_val['key'] . '" /></th><td class="custom_value"><input type="text" class="DropTarget" name="matched_fields[meta_new_fields][' . $m_key . '][val]" placeholder="' . __( 'Import Value', WPC_CLIENT_TEXT_DOMAIN ) . '" value="' . $m_val['val'] . '" /></td></td></tr>';
                                        }
                                    }

                                    echo '</table>';
                                    echo '<table>';
                                    echo '<tr><td colspan="2">
                                        <input type="button" class="button" id="show_add_meta_field_button" value="' . sprintf( __( 'Add %s Meta Field', WPC_CLIENT_TEXT_DOMAIN ), ( ( 'clients' == $type ) ? WPC()->custom_titles['client']['p'] : WPC()->custom_titles['staff']['p'] ) ) . '"  />
                                        <div class="add_meta_field_button is_hidden">
                                            <select id="meta_fields_list">';
                                            echo '<option value="-1">' . __( '- Add New Meta -', WPC_CLIENT_TEXT_DOMAIN ) . '</option>';
                                            foreach( $fields as $meta_key ) {
                                                echo '<option value="' . $meta_key . '">' . $meta_key . '</option>';
                                            }
                                    echo '</select><input type="button" class="button" value="' . __( 'Add', WPC_CLIENT_TEXT_DOMAIN ) . '" id="add_meta_field_button" />
                                        </div>
                                    </td></tr>';
                                    echo '</table>';
                                }
                            } else {

                                $fields = apply_filters( 'wpc_client_import_get_list_' . $type . '_' . $k, array() );

                                if ( !empty( $fields ) ) {

                                }
                            }
                        }

                    }
                    ?>



                    <?php

                    $custom_fields = apply_filters( 'wpc_client_import_get_custom_fields_' . $type, false );

                    if ( !empty( $custom_fields ) ) {
                        echo '<table>';
                        echo '<tr><td colspan="2"><h4>' . ( ( 'clients' == $type ) ? WPC()->custom_titles['client']['p'] : WPC()->custom_titles['staff']['p'] ) . ' ' . __( 'Custom Fields', WPC_CLIENT_TEXT_DOMAIN ) . '</h4></td></tr>';
                        foreach( $custom_fields as $key => $value ) {
                            $required = ( isset( $value['required'] ) && true == $value['required'] ) ? '<span class="required">*</span>' : '';
                            echo '<tr class="export_field">';
                            echo '<th class="key_custom_value" align="left">' . $value['title'] . $required . '</th>';
                            echo '<td class="custom_value"><input type="text" class="DropTarget" name="matched_fields[' . $key .']" value="" /></td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                    }
                    ?>
                </div>
            </div>

            <div style="float:left;width:19%;padding: 60px 0 0 10px;box-sizing: border-box;">
                <div class="wpc_imp_exp_associations_arrow">&nbsp;</div>
                <div style="float:left;width:calc( 100% - 70px );margin:0;padding:10px;color:#326ec6;font-style: italic; font-size: 16px;"><?php printf( __( 'Drag & Drop Import Fields to %s Fields', WPC_CLIENT_TEXT_DOMAIN ), ( ( 'clients' == $type ) ? WPC()->custom_titles['client']['p'] : WPC()->custom_titles['staff']['p'] ) ) ?></div>
            </div>

        </form>

        <br clear="all">
        <br clear="all">
        <br clear="all">

        <a href="javascript:void(0);" class="wpc_imp_exp_button wpc_imp_exp_back" >
            <span class="wpc_imp_exp_button_label">< <?php _e( 'Back', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
        </a>

        <a href="javascript:void(0);" class="wpc_imp_exp_button wpc_imp_exp_continue" >
            <span class="wpc_imp_exp_button_label"><?php _e( 'Continue', WPC_CLIENT_TEXT_DOMAIN ) ?> ></span>
        </a>

        <?php echo $this->show_form_save_template(); ?>

        <script type="text/javascript">
            jQuery( document ).ready( function() {

                var item_number = 1;
                var delimiter = '<?php echo $delimiter ?>';
                var fl_head = <?php echo $fline_head ?>;
                var total_items = 0;
                var key_client_field_flag = true;


                //ajax for get items
                jQuery.fn.wpc_load_item = function() {

                    jQuery( '#wpc_imp_exp_match_fields_content' ).html( '<div class="meta_ajax_loading"></div>' );

                    jQuery.ajax({
                        type: 'POST',
                        url: '<?php echo admin_url( 'admin-ajax.php' ) ?>',
                        data: 'action=wpc_get_import_items&type=<?php echo $type ?>&head=' + fl_head + '&delimiter=' + delimiter + '&item_number=' + item_number,
                        dataType: 'json',
                        success: function( data ){
                            if( data.status ) {
                                total_items = data.items;
                                jQuery( '#wpc_imp_exp_match_fields_content' ).html( data.item_html );

                                if ( key_client_field_flag ) {
                                  var fieldKeyArray = data.fields_key;

                                    jQuery( '#wpc_imp_exp_item_holders_content input.matched_fields_main_fields' ).
                                      each( function (index, element) {
                                        var fieldName = jQuery(element).data('field_name');
                                        for ( var i=0; i < fieldKeyArray.length; i++) {
                                          if ( fieldName == 'display_name' ) {

                                            if ( fieldKeyArray[i] == 'display_name') {
                                              jQuery(element).val('{' + fieldKeyArray[i] + '}');
                                              break;
                                            }
                                            else if ( fieldKeyArray[i] == 'contact_name' ) {
                                              jQuery(element).val('{' + fieldKeyArray[i]+ '}');
                                            }

                                          } else if ( fieldName == 'contact_phone') {

                                            if ( fieldKeyArray[i] == 'contact_phone') {
                                              jQuery(element).val('{' + fieldKeyArray[i] + '}');
                                              break;
                                            }
                                            else if ( fieldKeyArray[i] == 'phone' ) {
                                              jQuery(element).val('{' + fieldKeyArray[i]+ '}');
                                            }

                                          } else if ( fieldName == fieldKeyArray[i] ) {
                                            jQuery(element).val('{' + fieldKeyArray[i]  + '}')
                                          }
                                        }
                                      })

                                  key_client_field_flag = false;
                                }

                                jQuery( '#wpc_imp_exp_navi_page_total' ).html( data.items );
                                jQuery( '#wpc_imp_exp_navi_page' ).val( item_number );

                                jQuery( this ).wpc_auto_match_fields();

                                jQuery(".place_holders").draggable({helper: 'clone'});
                                jQuery(".DropTarget").droppable({
                                    accept: ".place_holders",
                                    drop: function(ev, ui) {
                                        jQuery(this).insertAtCaret('{' + ui.draggable.text() + '}');
                                    }
                                });



                            } else {

                            }
                        }
                    });
                };


                //ajax for get items
                jQuery.fn.wpc_auto_match_fields = function() {

                    jQuery( '.place_holders').each( function(){

                        var id = jQuery( this ).html();

                        var el = jQuery( 'input[name="matched_fields[' + id + ']"]' );

                        if ( el.length ) {
                            el.val( '{' + id + '}' );
                        }
                    });

                };


                jQuery.fn.insertAtCaret = function (myValue) {
                    return this.each( function(){
                        //IE support
                        if (document.selection) {
                            this.focus();
                            sel = document.selection.createRange();
                            sel.text = myValue;
                            this.focus();
                        }
                        //MOZILLA / NETSCAPE support
                        else if (this.selectionStart || this.selectionStart == '0') {
                            var startPos = this.selectionStart;
                            var endPos = this.selectionEnd;
                            var scrollTop = this.scrollTop;
                            this.value = this.value.substring(0, startPos)+ myValue+ this.value.substring(endPos,this.value.length);
                            this.focus();
                            this.selectionStart = startPos + myValue.length;
                            this.selectionEnd = startPos + myValue.length;
                            this.scrollTop = scrollTop;
                        } else {
                            this.value += myValue;
                            this.focus();
                        }
                    });
                };



                //first time items load
                jQuery( this ).wpc_load_item();


                //continue button
                jQuery( '.wpc_imp_exp_continue' ).click( function() {
                    jQuery( '#wpc_import_form_step_3' ).submit();
                });

                //back button
                jQuery( '.wpc_imp_exp_back' ).click( function() {
                    window.location = '<?php echo get_admin_url() . 'admin.php?page=wpclients&tab=import-export&action=import&import_id=' . $import_id . '&step=2' ?>';
                });


                //add metan
                jQuery( '#add_meta_field_button' ).click( function() {
                    var select_key = jQuery( '#meta_fields_list');
                    var html = '';
                    var field_id = '<?php echo time() ?>';

                    if ( -1 == select_key.val() ) {
                        html = '<tr class="export_field"><th class="key_custom_value" align="left"><input type="text" class="DropTarget" placeholder="<?php _e( 'Type Meta Key here', WPC_CLIENT_TEXT_DOMAIN ) ?>" name="matched_fields[meta_new_fields][' + field_id +'][key]" value="" /></th><td class="custom_value"><input type="text" class="DropTarget" name="matched_fields[meta_new_fields][' + field_id +'][val]" placeholder="<?php _e( 'Import Value', WPC_CLIENT_TEXT_DOMAIN ) ?>" value="" /></td></tr>';
                        field_id++;
                    } else {
                        html = '<tr class="export_field"><th class="key_custom_value" align="left"><label style="word-break: break-word;" for="matched_fields_meta_fields_' + select_key.val() + '">' + select_key.val() + '</label></th><td class="custom_value"><input type="text" class="DropTarget" name="matched_fields[meta_fields]['+ select_key.val() + ']" id="matched_fields_meta_fields_' + select_key.val() + '" placeholder="<?php _e( 'Import Value', WPC_CLIENT_TEXT_DOMAIN ) ?>" value="" /></td></td></tr>';
                    }

                    jQuery( '#meta_fields_list_table').append( html );
                    select_key.val( '-1' );

                    jQuery(".DropTarget").droppable({
                        accept: ".place_holders",
                        drop: function(ev, ui) {
                            jQuery(this).insertAtCaret('{' + ui.draggable.text() + '}');
                        }
                    });

                });


                //add metan
                jQuery( '#show_add_meta_field_button' ).click( function() {
                    jQuery(this).siblings( '.add_meta_field_button').toggleClass( 'is_hidden' );

                    if ( !jQuery(this).siblings( '.add_meta_field_button').hasClass( 'is_hidden' ) ) {
                        jQuery(this).hide();
                    }
                });


                //Save import field template
                var objSaveTmp;
                var arrayLineKey = [];
                var arrayExportValue = [];
                jQuery( '#save_template_fields' ).shutter_box( {
                    view_type: 'lightbox',
                    width: '500px',
                    type: 'inline',
                    href: '#save_template_form',
                    title: '<?php echo esc_js( __( 'Save Template', WPC_CLIENT_TEXT_DOMAIN ) ); ?>',
                    inlineBeforeLoad: function () {
                        arrayLineKey = [];
                        arrayExportValue = [];

                        jQuery( '#wpc_imp_exp_item_holders_content table:first-of-type .export_field' ).each( function () {
                            arrayLineKey.push( '' );
                            arrayExportValue.push( jQuery( this ).find( '.custom_value input[type="text"]' ).val());
                        } );

                        jQuery( '#meta_fields_list_table .export_field' ).each( function () {

                            var lineKey;
                            if ( ! jQuery( this ).find( '.key_custom_value label' ).length ) {
                                lineKey=[
                                    'add_meta',
                                    jQuery( this ).find( '.key_custom_value input[type="text"]' ).val()
                                ];
                            } else {
                                lineKey = jQuery( this ).find( '.key_custom_value label' ).text();
                            }

                            arrayLineKey.push( lineKey );
                            arrayExportValue.push( jQuery( this ).find( '.custom_value input[type="text"]' ).val() );
                        } );
                        objSaveTmp = jQuery( this );
                        objSaveTmp.shutter_box( 'showBackground' );
                        jQuery('#empty_tmp_name').css({'display':'none'});
                    },
                } );


                jQuery( document.body ).on( 'click', '#save_template_name', function () {
                    var templateName = jQuery( '#save_template_form input' ).val();
                    if ( templateName ) {
                        jQuery( '#save_template_form' ).hide();
                        jQuery('.wpc_json_answer').remove();
                        objSaveTmp.shutter_box( 'showPreLoader' );

                        var data = {
                            action: 'wpc_save_export_import_template',
                            type_page: '<?php echo esc_js($type_page ) ?>',
                            template_name: templateName,
                            action_data_type: '<?php echo esc_js($type ) ?>',
                            array_line_key: arrayLineKey,
                            array_export_value: arrayExportValue,
                        };


                        jQuery.post( ajaxurl, data,function ( json ) {
                            if ( json.success ) {
                                jQuery('#empty_tmp_name').remove();
                                jQuery( '.sb_lightbox_content_body .sb_loader' ).remove();
                                jQuery( '.sb_lightbox_content_body' ).append( '<p style="text-align:center"><?php _e('The template has been saved!', WPC_CLIENT_TEXT_DOMAIN) ?></p>' );
                            }
                            else {
                                jQuery('#empty_tmp_name').remove();
                                jQuery( '.sb_lightbox_content_body .sb_loader' ).remove();
                                jQuery( '#save_template_form' ).show();
                                jQuery( '.sb_lightbox_content_body' ).append( '<p class="wpc_json_answer" style="text-align:center; color:red;">' + json.data + '</p>' );
                            }
                        })
                    } else {
                        jQuery( '#empty_tmp_name' ).css( {'display': 'block'} );
                    }
                } );


                //Use import field template
                jQuery('#use_template_fields').shutter_box({
                    view_type       : 'lightbox',
                    width           : '500px',
                    type            : 'ajax',
                    dataType        : 'json',
                    title:'<?php _e('Load Template', WPC_CLIENT_TEXT_DOMAIN) ?>',
                    href            : '<?php echo get_admin_url() ?>admin-ajax.php',
                    ajax_data:
                        {
                            action: 'wpc_download_export_import_templates',
                            type_page: '<?php echo esc_js( $type_page ) ?>',
                            action_data_type: '<?php echo esc_js( $type ) ?>',
                        }
                    ,
                    setAjaxResponse : function( json ) {
                        if ( json.success ) {
                            jQuery(this).shutter_box('showBackground');
                            jQuery( '.sb_lightbox_content_body' ).html( json.data );
                        }
                        else {
                            jQuery(this).shutter_box('showBackground');
                            jQuery( '.sb_lightbox_content_body' ).html( '<p style=\'text-align:center\'>' + json.data + '</p>' )
                        }
                    },
                });


                var stateLengthTable;
                jQuery( 'body' ).on( 'click', '#use_template_name', function () {
                    jQuery('.wpc_json_answer').remove();
                    var use_template= jQuery(this);
                    use_template.attr('disabled','disabled');
                    var template = jQuery( '#templates_names' ).val();
                    var data = {
                        action: 'wpc_use_export_template',
                        template_option: template,
                    };

                    jQuery.post( ajaxurl, data, function ( json ) {
                        if ( json.success ) {
                            use_template.removeAttr('disabled');
                            var lengthTable = json.data[0].length;

                            if ( ! stateLengthTable ) {
                                stateLengthTable = jQuery( '#wpc_imp_exp_item_holders_content  .export_field ' ).length;
                            }

                            var key = 0;
                            jQuery( '#wpc_imp_exp_item_holders_content #meta_fields_list_table ' ).empty();
                            jQuery( '#wpc_imp_exp_item_holders_content .table_default_field .export_field' ).
                                each( function ( index, element ) {
                                    jQuery( element ).find( '.custom_value  input[type="text"]' ).val(json.data[1][key]);
                                    key+=1;
                                } );

                            if ( lengthTable > stateLengthTable ){
                                var field_id = '<?php echo time() ?>';
                                var fieldHtml = jQuery( '#wpc_imp_exp_item_holders_content .table_default_field .export_field:last-child' ).html();
                                var typeMetaKeyHtml;
                                var importValueHtml;
                                for ( var i = 0; i < lengthTable - stateLengthTable; i ++ ) {

                                    if ( ! Array.isArray( json.data[0][key] ) ) {
                                        jQuery( '#wpc_imp_exp_item_holders_content #meta_fields_list_table' ).append( '<tr class="export_field">' + fieldHtml + '</tr>' );
                                        jQuery( '#wpc_imp_exp_item_holders_content #meta_fields_list_table .export_field:last-child .key_custom_value label' ).text( json.data[0][key] ).attr( 'for', 'matched_fields_main_fields_' + json.data[0][key] ).css( {'word-break': 'break-word'} );
                                        jQuery( '#wpc_imp_exp_item_holders_content #meta_fields_list_table .export_field:last-child .custom_value input' ).val( json.data[1][key] ).attr( {
                                            'id': 'matched_fields_main_fields_' + json.data[0][key],
                                            'name': 'matched_fields[meta_fields][' + json.data[0][key] + ']',
                                        } );
                                    }
                                    else if ( json.data[0][key][0] === 'add_meta' ) {
                                        typeMetaKeyHtml = '<input type="text" class="DropTarget" placeholder="<?php _e( 'Type Meta Key here', WPC_CLIENT_TEXT_DOMAIN ) ?>" name="matched_fields[meta_new_fields][' + field_id + '][key]" value="' + json.data[0][key][1] + '" >';
                                        importValueHtml = '<input type="text" class="DropTarget" name="matched_fields[meta_new_fields][' + field_id + '][val]" placeholder="<?php _e( 'Import Value', WPC_CLIENT_TEXT_DOMAIN ) ?>" value="' + json.data[1][key] + '" />';
                                        jQuery( '#wpc_imp_exp_item_holders_content #meta_fields_list_table' ).append( '<tr class="export_field">' + fieldHtml + '</tr>' );
                                        jQuery( '#wpc_imp_exp_item_holders_content #meta_fields_list_table .export_field:last-child .key_custom_value ' ).empty().append( typeMetaKeyHtml );
                                        jQuery( '#wpc_imp_exp_item_holders_content #meta_fields_list_table .export_field:last-child .custom_value ' ).empty().append( importValueHtml );
                                    }
                                    key += 1;
                                }
                            }

                            jQuery(".place_holders").draggable({helper: 'clone'});
                            jQuery(".DropTarget").droppable({
                                accept: ".place_holders",
                                drop: function(ev, ui) {
                                    jQuery(this).insertAtCaret('{' + ui.draggable.text() + '}');
                                }
                            });
                            jQuery( '.sb_lightbox_content_body' ).append( '<p class="wpc_json_answer" style=\'text-align:center\'>' + 'The template has been used.' + '</p>' );
                            jQuery('.sb_close').trigger('click');
                        }
                        else {
                            use_template.removeAttr('disabled');
                            jQuery( '.sb_lightbox_content_body' ).append( '<p class="wpc_json_answer" style=\'text-align:center; color:red;\'>' + json.data + '</p>' );
                        }
                    } )

                } );



                jQuery( 'body' ).on( 'click', '#delete_template_name', function () {
                    jQuery('.wpc_json_answer').remove();
                    var template = jQuery( '#templates_names' ).val();
                    var nameTemplate = jQuery( '#templates_names option[value="' + template + '"]' ).text();
                    jQuery( '#templates_names option[value="' + template + '"]' ).remove();
                    var data = {
                        action: 'wpc_delete_export_import_template',
                        type_page: '<?php echo $type_page ?>',
                        template_option: template,
                        action_data_type: '<?php echo $type ?>',
                    };

                    jQuery.post( ajaxurl, data, function ( json ) {
                        if ( json.success ) {
                            jQuery( '.sb_lightbox_content_body' ).append( '<p class="wpc_json_answer" style=\'text-align:center\'>' + 'The template ' + nameTemplate + ' has been removed.' + '</p>' );
                        }
                        else {
                            jQuery( '.sb_lightbox_content_body' ).append( '<p class="wpc_json_answer" style=\'text-align:center; color:red;\'>' + json.data + '</p>' );
                        }
                    })
                })

            });
        </script>

    <?php

    }


    /**
    * Render HTML
    */
    function render_import_step_4() {
        $action_data = $this->get_action_data( 'import' );
        $type = $action_data['type'];
        $import_id = $action_data['import_id'];

        if ( !empty( $_POST ) ) {
            $assigns = array();
            if ( 'clients' == $type ) {
                if ( !empty( $_POST['wpc_managers'] ) )
                    $assigns['wpc_managers'] =  $_POST['wpc_managers'];

                if ( !empty( $_POST['wpc_circles'] ) )
                    $assigns['wpc_circles'] =  $_POST['wpc_circles'];
            } elseif ( 'staffs' == $type ) {
                if ( !empty( $_POST['wpc_clients'] ) )
                    $assigns['wpc_clients'] =  $_POST['wpc_clients'];
            }

            $action_data = array_merge( $action_data, array(
                'assigns'     => $assigns,
            ) );

            update_user_meta( get_current_user_id(), 'wpc_import_data', $action_data );

            $results = $this->import_process( $type, $import_id );

            if ( $results )
                WPC()->redirect( get_admin_url() . 'admin.php?page=wpclients&tab=import-export&action=import&step=5&import_id=' . $import_id . '&count_done=' . $results['count_done'] . '&count_failed=' . $results['count_failed'] );
        } ?>

        <h2><?php
            if ( 'clients' == $type ) {
                printf( __( 'Set assignments for new %s (Optional):', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] );
            } elseif ( 'staffs' == $type ) {
                printf( __( 'Set assignments for new %s (Optional):', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['p'] );
            } ?>
        </h2>

        <form action="<?php echo get_admin_url() ?>admin.php?page=wpclients&tab=import-export&action=import&import_id=<?php echo $import_id ?>&step=4" method="post" id="wpc_import_form_step_4">
            <input type="hidden" name="wpc_imp_exp_import_step_4" value="true">
            <?php if ( 'clients' == $type ) {
                $groups = WPC()->groups()->get_groups();

                //get managers
                $managers = get_users( array(
                    'role'      => 'wpc_manager',
                    'orderby'   => 'user_login',
                    'order'     => 'ASC',
                    'fields'    => array( 'ID', 'user_login' )
                ) ); ?>

                <table style="width: 500px;margin: 0 auto;text-align: left;">
                <?php if ( ! current_user_can( 'wpc_manager' ) || current_user_can( 'administrator' ) ) {
                    if ( !empty( $managers ) ) { ?>
                    <tr>
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
                                'value' => ''
                            );
                            $additional_array = array(
                                'counter_value' => 0
                            );

                            $current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';
                            WPC()->assigns()->assign_popup( 'manager', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array ); ?>
                        </td>
                    </tr>
                    <?php }
                }

                if ( ! empty( $groups ) ) { ?>
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
                                'value' => ''
                            );
                            $additional_array = array(
                                'counter_value' => 0
                            );

                            $current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';
                            WPC()->assigns()->assign_popup('circle', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array ); ?>
                        </td>
                    </tr>
                <?php } ?>
                </table>
            <?php } elseif( 'staffs' == $type ) {

                $clients = get_users( array(
                    'role'      => 'wpc_client',
                    'orderby'   => 'user_login',
                    'order'     => 'ASC',
                    'fields'    => 'ids'
                ) ); ?>

                <table style="width: 300px;margin: 0 auto;text-align: left;">
                    <?php if ( !empty( $clients ) ) { ?>
                        <tr>
                            <th scope="row">
                                <label><?php echo WPC()->custom_titles['client']['s'] ?></label>
                            </th>
                            <td>
                                <?php $link_array = array(
                                    'title'   => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                                    'text'    => sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
                                    'data-marks' => 'radio'
                                );
                                $input_array = array(
                                    'name'  => 'wpc_clients',
                                    'id'    => 'wpc_clients',
                                    'value' => ''
                                );
                                $additional_array = array(
                                    'counter_value' => 0
                                );

                                $current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';
                                WPC()->assigns()->assign_popup('client', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array ); ?>
                            </td>
                        </tr>
                    <?php } ?>
                </table>

            <?php } ?>
        </form>

        <br clear="all">

        <a href="javascript:void(0);" class="wpc_imp_exp_button wpc_imp_exp_back" >
            <span class="wpc_imp_exp_button_label">< <?php _e( 'Back', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
        </a>

        <a href="javascript:void(0);" class="wpc_imp_exp_button wpc_imp_exp_continue" >
            <span class="wpc_imp_exp_button_label"><?php _e( 'Run', WPC_CLIENT_TEXT_DOMAIN ) ?> ></span>
        </a>

        <script type="text/javascript">
            jQuery( document ).ready( function() {
                //continue button
                jQuery( '.wpc_imp_exp_continue' ).click( function() {
                    jQuery( '#wpc_import_form_step_4' ).submit();
                });

                //back button
                jQuery( '.wpc_imp_exp_back' ).click( function() {
                    window.location = '<?php echo get_admin_url() . 'admin.php?page=wpclients&tab=import-export&action=import&import_id=' . $import_id . '&step=3' ?>';
                });
            });
        </script>

    <?php
    }


    /**
    * Render HTML
    */
    function render_import_step_5() {
        $action_data = $this->get_action_data( 'import' );
        $type = $action_data['type'];

        update_user_meta( get_current_user_id(), 'wpc_import_data', false );

        $target_path = WPC()->get_upload_dir( 'wpclient/_imports/' );
        $target_path = $target_path . $type . get_current_user_id() . '_import_file.csv';

        if ( file_exists( $target_path ) )
            unlink( $target_path );

        $done_count       = ( isset( $_GET['count_done'] ) ) ? $_GET['count_done'] : 0;
        $failed_count     = ( isset( $_GET['count_failed'] ) ) ? $_GET['count_failed'] : 0; ?>

        <h2><?php _e( 'Finished:', WPC_CLIENT_TEXT_DOMAIN ) ?></h2>
        <p><strong><?php _e( 'Imported', WPC_CLIENT_TEXT_DOMAIN ) ?>: </strong><?php echo $done_count ?></p>
        <p><strong><?php _e( 'Failed Import', WPC_CLIENT_TEXT_DOMAIN ) ?>: </strong><?php echo $failed_count ?></p>

        <br clear="all">

        <a href="javascript:void(0);" class="wpc_imp_exp_button wpc_imp_exp_continue" >
            <span class="wpc_imp_exp_button_label"><?php _e( 'Ok', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
        </a>

        <script type="text/javascript">
            jQuery( document ).ready( function() {
                //continue button
                jQuery( '.wpc_imp_exp_continue' ).click( function() {
                    <?php if ( 'clients' == $type ) { ?>
                    window.location = '<?php echo get_admin_url() . 'admin.php?page=wpclient_clients' ?>';
                    <?php } elseif ( 'staffs' == $type ) { ?>
                    window.location = '<?php echo get_admin_url() . 'admin.php?page=wpclient_clients&tab=staff' ?>';
                    <?php } ?>
                });
            });
        </script>

    <?php
    }


    /**
     * Render HTML
     */
    function render_export_step_2() {
        $action_data = $this->get_action_data('export');
        $type = $action_data['type'];

        $function_name = "render_export_{$type}_step_2";
        $this->$function_name();
    }


    function render_export_clients_step_2() {
        $action_data = $this->get_action_data('export');

        $type = $action_data['type'];
        $export_id = $action_data['export_id'];
        $baselink = add_query_arg( array( 'page' => 'wpclients', 'tab'=>'import-export', 'action'=>'export', 'export_id' => $export_id, 'step' => '2' ), admin_url( 'admin.php' ) );

        if ( !empty( $_POST ) ) {
            if ( !empty( $_POST['clients'] ) ) {
                if ( 'all' == $_POST['clients'] ) {
                    $clients = 'all';
                } elseif ( 'selected' == $_POST['clients'] ) {
                    if ( empty( $_POST['wpc_clients'] ) )
                        $this->trigger_error( '201', $baselink );

                    $clients = array_unique( explode( ',', $_POST['wpc_clients'] ) );

                    if ( empty( $clients ) )
                        $this->trigger_error( '201', $baselink );
                } else {
                    if ( empty( $_POST['wpc_circles'] ) )
                        $this->trigger_error( '201', $baselink );

                    $clients = array();
                    $circles = explode( ',', $_POST['wpc_circles'] );
                    foreach ( $circles as $circle_id ) {
                        $client_ids = WPC()->groups()->get_group_clients_id( $circle_id );
                        $clients = array_merge( $clients, $client_ids );
                    }

                    $clients = array_unique( $clients );

                    if ( empty( $clients ) )
                        $this->trigger_error( '201', $baselink );
                }

                $export_data = get_user_meta( get_current_user_id(), 'wpc_export_data', true );

                $export_data = array_merge( $export_data, array(
                    'items'          => $clients,
                ) );

                update_user_meta( get_current_user_id(), 'wpc_export_data', $export_data );

                WPC()->redirect( get_admin_url() . 'admin.php?page=wpclients&tab=import-export&action=export&export_id=' . $export_id . '&step=3' );
            }
        }

        $groups = WPC()->groups()->get_groups(); ?>

        <h2><?php printf( __( 'What %s would you like to export?', WPC_CLIENT_TEXT_DOMAIN ), $type ) ?></h2>

        <form action="<?php echo $baselink ?>" method="post" id="wpc_export_form_step_2">
            <ul>
                <li>
                    <h3>
                        <label>
                            <input type="radio" name="clients" value="all" />
                            <?php printf( __( 'All %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) ?>
                        </label>
                    </h3>
                </li>
                <li>
                    <h3>
                        <label>
                            <input type="radio" name="clients" value="selected" />
                            <?php printf( __( 'Selected %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) ?>
                        </label>
                    </h3>
                </li>

                <li class="selected_clients" style="display: none;">
                    <?php $link_array = array(
                        'title'   => sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'] ),
                        'text'    => sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] )
                    );
                    $input_array = array(
                        'name'  => 'wpc_clients',
                        'id'    => 'wpc_clients',
                        'value' => ''
                    );
                    $additional_array = array(
                        'counter_value' => 0
                    );

                    $current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';
                    WPC()->assigns()->assign_popup('client', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array ); ?>
                </li>

                <?php if ( !empty( $groups ) ) { ?>
                    <li>
                        <h3>
                            <label>
                                <input type="radio" name="clients" value="in_circles" />
                                <?php printf( __( '%s from the following %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['circle']['p'] ) ?>
                            </label>
                        </h3>
                    </li>
                    <li class="assign_in_circles" style="display: none;">
                        <?php $link_array = array(
                            'title'   => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'] ),
                            'text'    => sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] )
                        );
                        $input_array = array(
                            'name'  => 'wpc_circles',
                            'id'    => 'wpc_circles',
                            'value' => ''
                        );
                        $additional_array = array(
                            'counter_value' => 0
                        );

                        $current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';
                        WPC()->assigns()->assign_popup('circle', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array ); ?>
                    </li>
                <?php } ?>
            </ul>

            <a href="javascript:void(0);" class="wpc_imp_exp_button wpc_imp_exp_back" >
                <span class="wpc_imp_exp_button_label">< <?php _e( 'Back', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
            </a>

            <a href="javascript:void(0);" class="wpc_imp_exp_button wpc_imp_exp_continue" >
                <span class="wpc_imp_exp_button_label"><?php _e( 'Continue', WPC_CLIENT_TEXT_DOMAIN ) ?> ></span>
            </a>
        </form>

        <script type="text/javascript">
            jQuery( document ).ready( function() {

                //select export clients button
                jQuery( 'input[name="clients"]' ).change( function() {
                    if ( jQuery('input[name="clients"]:checked').val() == 'all' ) {
                        jQuery('.assign_in_circles').hide();
                        jQuery('#wpc_circles').val('');
                        jQuery('.counter_wpc_circles').html('(0)');

                        jQuery('.selected_clients').hide();
                        jQuery('#wpc_clients').val('');
                        jQuery('.counter_wpc_clients').html('(0)');
                    } else if ( jQuery('input[name="clients"]:checked').val() == 'in_circles' ) {
                        jQuery('.assign_in_circles').show();

                        jQuery('.selected_clients').hide();
                        jQuery('#wpc_clients').val('');
                        jQuery('.counter_wpc_clients').html('(0)');
                    } else if ( jQuery('input[name="clients"]:checked').val() == 'selected' ) {
                        jQuery('.selected_clients').show();

                        jQuery('.assign_in_circles').hide();
                        jQuery('#wpc_circles').val('');
                        jQuery('.counter_wpc_circles').html('(0)');
                    }
                }).trigger('change');

                //continue button
                jQuery( '.wpc_imp_exp_continue' ).click( function() {
                    jQuery( '#wpc_export_form_step_2' ).submit();
                });

                //back button
                jQuery( '.wpc_imp_exp_back' ).click( function() {
                    window.location = '<?php echo get_admin_url() . 'admin.php?page=wpclients&tab=import-export' ?>';
                });

            });
        </script>

        <?php
    }


    function render_export_staffs_step_2() {
        $action_data = $this->get_action_data('export');

        $type = $action_data['type'];
        $export_id = $action_data['export_id'];
        $baselink = add_query_arg( array( 'page' => 'wpclients', 'tab'=>'import-export', 'action'=>'export', 'export_id' => $export_id, 'step' => '2' ), admin_url( 'admin.php' ) );

        if ( !empty( $_POST ) ) {
            if ( !empty( $_POST['staffs'] ) ) {
                if ( 'all' == $_POST['staffs'] ) {
                    $staffs = 'all';
                } else {
                    if ( empty( $_POST['wpc_clients'] ) )
                        $this->trigger_error( '202', $baselink );

                    $staffs = array();

                    $clients = explode( ',', $_POST['wpc_clients'] );
                    foreach ( $clients as $client_id ) {

                        $staff_ids = get_users( array(
                            'role'         => 'wpc_client_staff',
                            'meta_key'     => 'parent_client_id',
                            'meta_value'   => $client_id,
                            'fields'       => 'ids',
                        ));

                        $staffs = array_merge( $staffs, $staff_ids );
                    }

                    $staffs = array_unique( $staffs );

                    if ( empty( $staffs ) )
                        $this->trigger_error( '202', $baselink );
                }

                $export_data = get_user_meta( get_current_user_id(), 'wpc_export_data', true );

                $export_data = array_merge( $export_data, array(
                    'items'          => $staffs,
                ) );

                update_user_meta( get_current_user_id(), 'wpc_export_data', $export_data );

                WPC()->redirect( get_admin_url() . 'admin.php?page=wpclients&tab=import-export&action=export&export_id=' . $export_id . '&step=3' );
            }
        } ?>

        <h2><?php printf( __( 'What %s would you like to export?', WPC_CLIENT_TEXT_DOMAIN ), $type ) ?></h2>

        <form action="<?php echo $baselink ?>" method="post" id="wpc_export_form_step_2">
            <ul>
                <li>
                    <h3>
                        <label>
                            <input type="radio" name="staffs" value="all" />
                            <?php printf( __( 'All %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['p'] ) ?>
                        </label>
                    </h3>
                </li>

                <li>
                    <h3>
                        <label>
                            <input type="radio" name="staffs" value="current_client" />
                            <?php printf( __( '%s from the following %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['p'], WPC()->custom_titles['client']['p'] ) ?>
                        </label>
                    </h3>
                </li>
                <li class="assign_current_client" style="display: none;">
                    <?php $link_array = array(
                        'title'   => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'] ),
                        'text'    => sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] )
                    );
                    $input_array = array(
                        'name'  => 'wpc_clients',
                        'id'    => 'wpc_clients',
                        'value' => ''
                    );
                    $additional_array = array(
                        'counter_value' => 0
                    );

                    $current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';
                    WPC()->assigns()->assign_popup( 'client', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array ); ?>
                </li>
            </ul>

            <a href="javascript:void(0);" class="wpc_imp_exp_button wpc_imp_exp_back" >
                <span class="wpc_imp_exp_button_label">< <?php _e( 'Back', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
            </a>

            <a href="javascript:void(0);" class="wpc_imp_exp_button wpc_imp_exp_continue" >
                <span class="wpc_imp_exp_button_label"><?php _e( 'Continue', WPC_CLIENT_TEXT_DOMAIN ) ?> ></span>
            </a>
        </form>

        <script type="text/javascript">
            jQuery( document ).ready( function() {

                //select export clients button
                jQuery( 'input[name="staffs"]' ).change( function() {
                    if ( jQuery('input[name="staffs"]:checked').val() == 'all' ) {
                        jQuery('.assign_current_client').hide();
                        jQuery('#wpc_clients').val('');
                        jQuery('.counter_wpc_clients').html('(0)');
                    } else if ( jQuery('input[name="staffs"]:checked').val() == 'current_client' ) {
                        jQuery('.assign_current_client').show();
                    }
                }).trigger('change');

                //continue button
                jQuery( '.wpc_imp_exp_continue' ).click( function() {
                    jQuery( '#wpc_export_form_step_2' ).submit();
                });

                //back button
                jQuery( '.wpc_imp_exp_back' ).click( function() {
                    window.location = '<?php echo get_admin_url() . 'admin.php?page=wpclients&tab=import-export' ?>';
                });

            });
        </script>

        <?php
    }


    /**
     * Render HTML
     */
    function render_export_step_3() {
        $action_data = $this->get_action_data('export');

        $type = $action_data['type'];
        $export_id = $action_data['export_id'];
        $type_page = 'export';//for ajax method

        $baselink = add_query_arg( array( 'page' => 'wpclients', 'tab'=>'import-export', 'action'=>'export', 'export_id' => $export_id, 'step' => '3' ), admin_url( 'admin.php' ) );

        if ( 'clients' == $type ) {

            $args = array(
                'role'         => 'wpc_client',
                'orderby'      => 'ID',
                'order'        => 'ASC',
                'number'       => '1',
                'fields'       => 'all',
            );
            if ( !empty( $action_data['items'] ) && 'all' != $action_data['items'] ) {
                $args['include'] = $action_data['items'];
            }

            $clients = get_users( $args );
            if ( !empty( $clients ) ) {
                $first_client = $clients[0]->data;
            }
        } elseif ( 'staffs' == $type ) {
            $args = array(
                'role'         => 'wpc_client_staff',
                'orderby'      => 'ID',
                'order'        => 'ASC',
                'number'       => '1',
                'fields'       => 'all',
            );
            if ( !empty( $action_data['items'] ) && 'all' != $action_data['items'] ) {
                $args['include'] = $action_data['items'];
            }

            $clients = get_users( $args );
            if ( !empty( $clients ) ) {
                $first_client = $clients[0]->data;
            }
        }

        if ( !empty( $_POST['export_fields'] ) ) {

            if ( empty( $_POST['delimiter'] ) || ( '"' == stripslashes( $_POST['delimiter'] ) || "'" == stripslashes( $_POST['delimiter'] ) ) )
                $this->trigger_error( '302', $baselink );

            $action_data = array_merge( $action_data, array(
                'fline_head'    => !empty( $_POST['fline_head'] ) ? true : false,
                'delimiter'     => $_POST['delimiter'],
                'export_fields' => $_POST['export_fields'],
            ) );

            update_user_meta( get_current_user_id(), 'wpc_export_data', $action_data );

            $results = $this->export_process( $type, $export_id );

            if ( $results ) {
                WPC()->redirect( get_admin_url() . 'admin.php?page=wpclients&tab=import-export&action=export&step=4&export_id=' . $export_id . '&count_done=' . $results['count_done'] );
            } else {
                $this->trigger_error( '303', $baselink );
            }
        } ?>

        <h2><?php _e( 'Please match fields for Export:', WPC_CLIENT_TEXT_DOMAIN ) ?></h2>

        <form action="<?php echo $baselink ?>" method="post" id="wpc_export_form_step_3" name="wpc_export_form_step_3" >

            <div style="float: left;width:100%;clear:both;margin: 0 0 10px 0;">
                <div style="float:left; text-align: left;">
                    <label>
                        <?php _e( 'Delimiter:', WPC_CLIENT_TEXT_DOMAIN ) ?>
                        <input type="text" style="width: 30px;" maxlength="1" id="wpc_imp_delimiter" name="delimiter" value="," />
                    </label>
                    <br>
                    <label>
                        <?php _e( 'First Line Header:', WPC_CLIENT_TEXT_DOMAIN ) ?>
                        <input type="checkbox" id="wpc_imp_fline_head" checked value="true" name="fline_head" />
                    </label>
                </div>
                <div style="float:left; text-align: left; margin-left: 30px;">
                    <input type="button" id="save_template_fields"
                           value="<?php _e( 'Save as Template', WPC_CLIENT_TEXT_DOMAIN ) ?>" class="button">
                    <input type="button" id="use_template_fields" value="<?php _e( 'Load Template', WPC_CLIENT_TEXT_DOMAIN ) ?>" class="button">
                </div>
            </div>

            <div id="wpc_imp_exp_item_content" style="width: 40%; float: right;">
                <div id="wpc_imp_exp_match_fields">
                    <div id="wpc_imp_exp_match_fields_header">
                        <h4><?php echo ( ( 'clients' == $type ) ? WPC()->custom_titles['client']['p'] : WPC()->custom_titles['staff']['p'] ) . ' ' . __( 'Fields', WPC_CLIENT_TEXT_DOMAIN ) ?></h4>
                    </div>
                    <div id="wpc_imp_exp_match_fields_content">
                        <?php

                        if ( isset( $this->export_fields[$type] ) ) {
                            echo '<table class="export_items">';
                            foreach( $this->export_fields[$type] as $k => $v ) {
                                if ( 'main_fields' == $k ) {
                                    foreach( $v as $key => $value ) {
                                        if ( !empty( $first_client->$value ) ) {
                                            echo '<tr>';
                                            echo '<td>' . $key. '</td>';
                                            echo '<td><b class="place_holders">' . $value . '</b></td>';
                                            echo '<td>' . $first_client->$value  . '</td>';
                                            echo '</tr>';
                                        }
                                    }

                                } elseif ( 'meta_fields' == $k ) {

                                    foreach( $v as $key => $value ) {
                                        $meta_value = get_user_meta( $first_client->ID, $value, true );

                                        if ( !empty( $meta_value ) ) {
                                            echo '<tr>';
                                            echo '<td>' . $key. '</td>';
                                            echo '<td><b class="place_holders">' . $value . '</b></td>';
                                            echo '<td>' . $meta_value . '</td>';
                                            echo '</tr>';
                                        }
                                    }


                                    $fields = apply_filters( 'wpc_client_export_get_list_' . $type . '_' . $k, array() );

                                    echo '<tr><td>&nbsp;</td><td colspan="2" style="border: none;padding: 0;">
                                        <input type="button" class="button" id="show_add_meta_field_button" value="' . sprintf( __( 'Add %s Meta Field', WPC_CLIENT_TEXT_DOMAIN ), ( ( 'clients' == $type ) ? WPC()->custom_titles['client']['p'] : WPC()->custom_titles['staff']['p'] ) ) . '"  />
                                        <div class="add_meta_field_button is_hidden">
                                            <select id="meta_fields_list">';
                                    foreach( $fields as $meta_key ) {
                                        echo '<option value="' . $meta_key . '">' . $meta_key . '</option>';
                                    }
                                    echo '</select><input type="button" class="button" value="' . __( 'Add', WPC_CLIENT_TEXT_DOMAIN ) . '" id="add_meta_field_button" data-item_id="' . $first_client->ID . '"/>
                                        </div>
                                    </td></tr>';
                                }
                            }
                            echo '</table>';

                        }

                        $custom_fields = apply_filters( 'wpc_client_import_get_custom_fields_' . $type, false );

                        if ( !empty( $custom_fields ) ) {
                            echo '<table>';
                            echo '<tr><td colspan="2"><h4>' . ( ( 'clients' == $type ) ? WPC()->custom_titles['client']['p'] : WPC()->custom_titles['staff']['p'] ) . ' ' . __( 'Custom Fields', WPC_CLIENT_TEXT_DOMAIN ) . '</h4></td></tr>';
                            foreach( $custom_fields as $key => $value ) {
                                $required = ( isset( $value['required'] ) && true == $value['required'] ) ? '<span class="required">*</span>' : '';
                                echo '<tr>';
                                echo '<th align="left">' . $value['title'] . $required . '</th>';
                                echo '<td><input type="text" class="DropTarget" name="matched_fields[' . $key .']" value="" /></td>';
                                echo '</tr>';
                            }
                            echo '</table>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div id="wpc_imp_exp_item_holders" style="width: 40%; float: left;">
                <div id="wpc_imp_exp_item_holders_header">
                    <?php if ( isset( $this->export_fields[$type] ) ) { ?>
                        <h4><?php _e( 'Export Fields', WPC_CLIENT_TEXT_DOMAIN ) ?></h4>
                    <?php } ?>
                </div>
                <div id="wpc_imp_exp_item_holders_content">
                    <?php
                    if ( isset( $this->export_fields[$type] ) ) {
                        echo '<table id="export_fields">';
                        $index = 0;
                        foreach( $this->export_fields[$type] as $k => $v ) {
                            if ( 'main_fields' == $k ) {
                                foreach( $v as $key => $value ) {
                                    echo '<tr class="export_field">
                                        <th class="default_header">&nbsp;</th>
                                        <th class="custom_header" align="left">
                                            <input type="text" class="DropTargetHeader" placeholder="' . __( 'Header Line Key', WPC_CLIENT_TEXT_DOMAIN ) . '" name="export_fields[' . $index . '][key]" value="" />
                                        </th>
                                        <td class="custom_value"><input type="text" class="DropTarget" placeholder="' . __( 'Export Value', WPC_CLIENT_TEXT_DOMAIN ) . '" name="export_fields[' . $index . '][value]" value="" /></td>
                                    </tr>';
                                    $index++;
                                }
                            } elseif ( 'meta_fields' == $k ) {
                                foreach( $v as $key => $value ) {
                                    echo '<tr class="export_field">
                                        <th class="default_header">&nbsp;</th>
                                        <th class="custom_header" align="left">
                                            <input type="text" class="DropTargetHeader" placeholder="' . __( 'Header Line Key', WPC_CLIENT_TEXT_DOMAIN ) . '" name="export_fields[' . $index . '][key]" value="" />
                                        </th>
                                        <td class="custom_value"><input type="text" class="DropTarget" placeholder="' . __( 'Export Value', WPC_CLIENT_TEXT_DOMAIN ) . '" name="export_fields[' . $index . '][value]" value="" /></td>
                                    </tr>';
                                    $index++;
                                }
                            } else {

                                $fields = apply_filters( 'wpc_client_export_get_list_' . $type . '_' . $k, array() );

                                if ( !empty( $fields ) ) {

                                }
                            }
                        }

                        echo '<tr class="export_field"><td colspan="2">
                                    <input type="button" class="button" id="add_export_field_button" value="' . __( 'Add Field', WPC_CLIENT_TEXT_DOMAIN ) . '"  />
                                </td></tr>';
                        echo '</table>';
                    }
                    ?>



                    <?php

                    $custom_fields = apply_filters( 'wpc_client_import_get_custom_fields_' . $type, false );

                    if ( !empty( $custom_fields ) ) {
                        echo '<table>';
                        echo '<tr  class="export_field"><td colspan="2"><h4>' . ( ( 'clients' == $type ) ? WPC()->custom_titles['client']['p'] : WPC()->custom_titles['staff']['p'] ) . ' ' . __( 'Custom Fields', WPC_CLIENT_TEXT_DOMAIN ) . '</h4></td></tr>';
                        foreach( $custom_fields as $key => $value ) {
                            $required = ( isset( $value['required'] ) && true == $value['required'] ) ? '<span class="required">*</span>' : '';
                            echo '<tr>';
                            echo '<th align="left">' . $value['title'] . $required . '</th>';
                            echo '<td class="custom_value"><input type="text" class="DropTarget" name="matched_fields[' . $key .']" value="" /></td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                    }
                    ?>
                </div>
            </div>

            <?php echo $this->show_form_save_template(); ?>

            <div style="float:left;width:19%;padding: 60px 0 0 10px;box-sizing: border-box;">
                <div class="wpc_imp_exp_associations_arrow">&nbsp;</div>
                <div style="float:left;width:calc( 100% - 70px );margin:0;padding:10px;color:#326ec6;font-style: italic; font-size: 16px;"><?php printf( __( 'Drag & Drop %s Fields to Export Fields', WPC_CLIENT_TEXT_DOMAIN ), ( ( 'clients' == $type ) ? WPC()->custom_titles['client']['p'] : WPC()->custom_titles['staff']['p'] ) ) ?></div>
            </div>

        </form>

        <br clear="all">
        <br clear="all">
        <br clear="all">

        <a href="javascript:void(0);" class="wpc_imp_exp_button wpc_imp_exp_back" >
            <span class="wpc_imp_exp_button_label">< <?php _e( 'Back', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
        </a>

        <a href="javascript:void(0);" class="wpc_imp_exp_button wpc_imp_exp_continue" >
            <span class="wpc_imp_exp_button_label"><?php _e( 'Continue', WPC_CLIENT_TEXT_DOMAIN ) ?> ></span>
        </a>

        <script type="text/javascript">

            function init_draggable() {
                jQuery(".DropTargetHeader").droppable({
                    accept: ".place_holders",
                    drop: function(ev, ui) {
                        jQuery(this).insertAtCaret(ui.draggable.text());
                        var name = jQuery(this).attr('name').replace("[key]","[value]");

                        var value_field = jQuery(this).parents('tr').find('input[name="' + name + '"]');
                        if ( value_field.val() == '' ) {
                            value_field.insertAtCaret('{' + ui.draggable.text() + '}');
                        }
                    }
                });

                jQuery(".DropTarget").droppable({
                    accept: ".place_holders",
                    drop: function(ev, ui) {
                        jQuery(this).insertAtCaret('{' + ui.draggable.text() + '}');
                    }
                });
            }


            jQuery( document ).ready( function() {

                jQuery(".place_holders").draggable({helper: 'clone'});

                init_draggable();

                jQuery.fn.insertAtCaret = function (myValue) {
                    return this.each(function(){
                        //IE support
                        if (document.selection) {
                            this.focus();
                            sel = document.selection.createRange();
                            sel.text = myValue;
                            this.focus();
                        }
                        //MOZILLA / NETSCAPE support
                        else if (this.selectionStart || this.selectionStart == '0') {
                            var startPos = this.selectionStart;
                            var endPos = this.selectionEnd;
                            var scrollTop = this.scrollTop;
                            this.value = this.value.substring(0, startPos)+ myValue+ this.value.substring(endPos,this.value.length);
                            this.focus();
                            this.selectionStart = startPos + myValue.length;
                            this.selectionEnd = startPos + myValue.length;
                            this.scrollTop = scrollTop;
                        } else {
                            this.value += myValue;
                            this.focus();
                        }
                    });
                };


                // Change First Line Header
                jQuery( '#wpc_imp_fline_head' ).change( function() {
                    if ( jQuery(this).is(':checked') ) {
                        jQuery( '#wpc_imp_exp_item_holders_content' ).find( 'table th.custom_header' ).show();
                        jQuery( '#wpc_imp_exp_item_holders_content' ).find( 'table th.default_header' ).hide();
                    } else {
                        jQuery( '#wpc_imp_exp_item_holders_content' ).find( 'table th.custom_header' ).hide();
                        jQuery( '#wpc_imp_exp_item_holders_content' ).find( 'table th.default_header' ).show();
                    }
                }).trigger('change');


                // Add Export fields
                jQuery( '#add_export_field_button' ).click( function() {

                    var count = jQuery( '#wpc_imp_exp_item_holders_content table').find('.custom_header').length * 1;

                    jQuery(this).parents('tr').before(
                        '<tr class="export_field"><th class="default_header">&nbsp;</th><th class="custom_header" align="left"><input type="text" class="DropTargetHeader" placeholder="<?php _e( 'Header Line Key', WPC_CLIENT_TEXT_DOMAIN ) ?>" name="export_fields[' + count + '][key]" value="" /></th><td class="custom_value"><input type="text" class="DropTarget" placeholder="<?php _e( 'Export Value', WPC_CLIENT_TEXT_DOMAIN ) ?>" name="export_fields[' + count + '][value]" value="" /></td></tr>' );

                    jQuery( '#wpc_imp_fline_head' ).trigger('change');

                    init_draggable();
                });


                //add metan
                jQuery( '#show_add_meta_field_button' ).click( function() {
                    jQuery(this).siblings( '.add_meta_field_button').toggleClass( 'is_hidden' );

                    if ( !jQuery(this).siblings( '.add_meta_field_button').hasClass( 'is_hidden' ) ) {
                        jQuery(this).hide();
                    }
                });

                //add Clients fields
                jQuery( '#add_meta_field_button' ).click( function() {
                    var select_key = jQuery( '#meta_fields_list').val();

                    var index = jQuery(this).parents('tr').prev().find('td:first').html()*1 + 1;
                    var obj = jQuery(this);
                    jQuery(this).parents('tr').before(
                        '<tr><td>' + index + '</td><td><b class="place_holders">' + select_key + '</b></td><td><span class="wpc_ajax_loading"></span></td></tr>' );

                    jQuery.ajax({
                        type: 'POST',
                        url: '<?php echo admin_url( 'admin-ajax.php' ) ?>',
                        data: 'action=wpc_get_export_item_data&type=<?php echo $type ?>' + '&item_id=' + obj.data('item_id') + '&data_key=' + select_key,
                        dataType: 'json',
                        success: function( data ){
                            if( data.status ) {
                                obj.parents('tr').prev().find('td:last').html( data.data_value );

                                jQuery(".place_holders").draggable({helper: 'clone'});

                                init_draggable();
                            }
                        }
                    });
                });


                //continue button
                jQuery( '.wpc_imp_exp_continue' ).click( function() {
                    jQuery( '#wpc_export_form_step_3' ).submit();
                });

                //back button
                jQuery( '.wpc_imp_exp_back' ).click( function() {
                    window.location = '<?php echo get_admin_url() . 'admin.php?page=wpclients&tab=import-export&action=export&export_id=' . $export_id . '&step=2' ?>';
                });


              //Save export field template
                var objSaveTmp;
                var arrayLineKey = [];
                var arrayExportValue = [];
                jQuery( '#save_template_fields' ).shutter_box( {
                    view_type: 'lightbox',
                    width: '500px',
                    type: 'inline',
                    href: '#save_template_form',
                    title: '<?php _e('Save Template',WPC_CLIENT_TEXT_DOMAIN) ?>',
                    inlineBeforeLoad: function () {
                        arrayLineKey = [];
                        arrayExportValue = [];

                        jQuery( '#wpc_imp_exp_item_holders_content #export_fields .export_field' ).
                            each( function ( index, element ) {
                                var lineKey = jQuery( element ).find( '.custom_header input[type="text"]' ).val();
                                var exportValue = jQuery( element ).find( '.custom_value input[type="text"]' ).val();
                                if ( lineKey !== undefined ) {
                                    arrayLineKey.push( lineKey );
                                    arrayExportValue.push( exportValue )
                                }
                            } );
                        objSaveTmp = jQuery( this );
                        objSaveTmp.shutter_box( 'showBackground' );
                        jQuery('#empty_tmp_name').css({'display':'none'});
                    },
                } );


                jQuery( 'body' ).on( 'click', '#save_template_name', function () {

                    var templateName = jQuery( '#save_template_form input' ).val();
                    if ( templateName ) {
                        jQuery('.wpc_json_answer').remove();
                        jQuery( '#save_template_form' ).hide();
                        objSaveTmp.shutter_box( 'showPreLoader' );

                        var data = {
                            action: 'wpc_save_export_import_template',
                            type_page: '<?php echo $type_page ?>',
                            template_name: templateName,
                            action_data_type: '<?php echo $type ?>',
                            array_line_key: arrayLineKey,
                            array_export_value: arrayExportValue,
                        };

                        jQuery.post( ajaxurl, data,function ( json ) {
                            if ( json.success ) {
                                jQuery('#empty_tmp_name').remove();
                                jQuery( '.sb_lightbox_content_body .sb_loader' ).remove();
                                jQuery( '.sb_lightbox_content_body' ).append( '<p class="wpc_json_answer" style="text-align:center"> <?php _e('The template has been saved!',WPC_CLIENT_TEXT_DOMAIN); ?> </p>' );
                            }
                            else {
                                jQuery('#empty_tmp_name').remove();
                                jQuery( '.sb_lightbox_content_body .sb_loader' ).remove();
                                jQuery( '#save_template_form' ).show();
                                jQuery( '.sb_lightbox_content_body' ).append( '<p class="wpc_json_answer" style="text-align:center; color:red;">' + json.data + '</p>' );
                            }
                        })
                    } else {
                        jQuery( '#empty_tmp_name' ).css( {'display': 'block'} );
                    }
                } );


                //Use export field template
                jQuery('#use_template_fields').shutter_box({
                    view_type       : 'lightbox',
                    width           : '500px',
                    type            : 'ajax',
                    dataType        : 'json',
                    href            : '<?php echo get_admin_url() ?>admin-ajax.php',
                    ajax_data:
                        {
                            action: 'wpc_download_export_import_templates',
                            type_page: '<?php echo $type_page ?>',
                            action_data_type: '<?php echo $type ?>',
                        }
                    ,
                    setAjaxResponse : function( json ) {
                        if ( json.success ) {
                            jQuery(this).shutter_box('showBackground');
                            jQuery( '.sb_lightbox_content_title' ).html( 'Load template' );
                            jQuery( '.sb_lightbox_content_body' ).html( json.data );
                        }
                        else {
                            jQuery(this).shutter_box('showBackground');
                            jQuery( '.sb_lightbox_content_title' ).html( 'Load template' );
                            jQuery( '.sb_lightbox_content_body' ).html( '<p style=\'text-align:center\'>' + json.data + '</p>' )
                        }
                    },
                });


                jQuery( 'body' ).on( 'click', '#use_template_name', function () {
                    jQuery('.wpc_json_answer').remove();
                    var use_template= jQuery(this);
                    use_template.attr('disabled','disabled');
                    var template = jQuery( '#templates_names' ).val();
                    var data = {
                        action: 'wpc_use_export_template',
                        template_option: template,
                    };

                    jQuery.post( ajaxurl, data, function ( json ) {
                        if ( json.success ) {
                            use_template.removeAttr('disabled');
                            var lengthTable = json.data[0].length;
                            var currentLengthTable = jQuery( '#wpc_imp_exp_item_holders_content .export_field .custom_header' ).length;

                            var key = 0;

                            jQuery( '#wpc_imp_exp_item_holders_content .export_field' ).
                                each( function ( index, element ) {
                                    jQuery( element ).find( '.custom_header input[type="text"]' ).val(json.data[0][key]);
                                    jQuery( element ).find( '.custom_value  input[type="text"]' ).val(json.data[1][key]);
                                    key+=1;
                                } );
                            key-=1;

                            if ( lengthTable > currentLengthTable ){
                                var fieldHtml = jQuery( '#wpc_imp_exp_item_holders_content .export_field:nth-last-child(2)' ).html();
                                for ( var i = 0; i < lengthTable - currentLengthTable; i ++ ) {
                                    jQuery('#wpc_imp_exp_item_holders_content .export_field:nth-last-child(2)').after('<tr class="export_field">'+fieldHtml+'</tr>');
                                    jQuery('#wpc_imp_exp_item_holders_content .export_field:nth-last-child(2) .custom_header input').val( json.data[0][key] ).attr("name",'export_fields[' + key + '][key]');
                                    jQuery('#wpc_imp_exp_item_holders_content .export_field:nth-last-child(2) .custom_value input').val( json.data[1][key] ).attr("name",'export_fields[' + key + '][value]');
                                    key += 1;
                                }
                            }

                            init_draggable();
                            jQuery( '.sb_lightbox_content_body' ).append( '<p class="wpc_json_answer" style=\'text-align:center\'>' + 'The template has been used.' + '</p>' );
                            jQuery('.sb_close').trigger('click');
                        }
                        else {
                            use_template.removeAttr('disabled');
                            jQuery( '.sb_lightbox_content_body' ).append( '<p class="wpc_json_answer" style=\'text-align:center; color:red;\'>' + json.data + '</p>' );
                        }
                    } )

                } );


                jQuery( 'body' ).on( 'click', '#delete_template_name', function () {
                    jQuery('.wpc_json_answer').remove();
                    var template = jQuery( '#templates_names' ).val();
                    var nameTemplate = jQuery( '#templates_names option[value="' + template + '"]' ).text();
                    jQuery( '#templates_names option[value="' + template + '"]' ).remove();
                    var data = {
                        action: 'wpc_delete_export_import_template',
                        type_page: '<?php echo $type_page ?>',
                        template_option: template,
                        action_data_type: '<?php echo $type ?>',
                    };

                    jQuery.post( ajaxurl, data, function ( json ) {
                        if ( json.success ) {
                            jQuery( '.sb_lightbox_content_body' ).append( '<p class="wpc_json_answer" style=\'text-align:center\'>' + 'The template ' + nameTemplate + ' has been removed.' + '</p>' );
                        }
                        else {
                            jQuery( '.sb_lightbox_content_body' ).append( '<p class="wpc_json_answer" style=\'text-align:center; color:red;\'>' + json.data + '</p>' );
                        }
                    })
                })


              });

        </script>

        <?php
    }


    /**
     * Render HTML
     */
    function render_export_step_4() {

        $action_data = $this->get_action_data('export');

        $type = $action_data['type'];
        $export_id = $action_data['export_id'];
        $done_count = ( isset( $_GET['count_done'] ) ) ? $_GET['count_done'] : 0; ?>

        <h2><?php _e( 'Finished:', WPC_CLIENT_TEXT_DOMAIN ) ?></h2>
        <p><strong><?php _e( 'Exported', WPC_CLIENT_TEXT_DOMAIN ) ?>: </strong><?php echo $done_count ?></p>

        <br clear="all">

        <a href="javascript:void(0);" class="wpc_imp_exp_button wpc_imp_exp_close" >
            <span class="wpc_imp_exp_button_label"><?php _e( 'Close', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
        </a>
        <a target="_blank" href="<?php echo add_query_arg( array( 'wpc_action'=>'download', 'id' => $export_id, 'module' => 'export_csv', 'type' => $type ), admin_url( 'admin.php' ) ) ?>" class="wpc_imp_exp_button wpc_imp_exp_download" >
            <span class="wpc_imp_exp_button_label"><?php _e( 'Download', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
        </a>



        <script type="text/javascript">
            jQuery( document ).ready( function() {
                //continue button
                jQuery( '.wpc_imp_exp_close' ).click( function() {
                    window.location = '<?php echo add_query_arg( array( 'page' => 'wpclients', 'tab' => 'import-export', 'action' => 'export', 'step' => '5', 'export_id' => $export_id ), admin_url( 'admin.php' ) ) ?>';
                });
            });
        </script>



        <?php

    }


    /**
     * Render HTML
     */
    function render_export_step_5() {
        $action_data = $this->get_action_data('export');
        $type = $action_data['type'];

        update_user_meta( get_current_user_id(), 'wpc_export_data', false );

        $target_path = WPC()->get_upload_dir( 'wpclient/_exports/' );
        $target_path = $target_path . $type . get_current_user_id() . '_export_file.csv';

        if ( file_exists( $target_path ) ) {
            unlink( $target_path );
        }

        /*if ( 'clients' == $type ) {
            WPC()->redirect( get_admin_url() . 'admin.php?page=wpclient_clients' );
        } elseif ( 'staffs' == $type ) {
            WPC()->redirect( get_admin_url() . 'admin.php?page=wpclient_clients&tab=staff' );
        }*/

        WPC()->redirect( get_admin_url() . 'admin.php?page=wpclients&tab=import-export' );
    }


    /**
     * Render HTML
     *
     * @return void;
     */
    function render_block() {
        $step = ( !empty( $_GET['step'] ) ) ? $_GET['step'] : '1';
        $action = ( isset( $_GET['action'] ) ) ? $_GET['action'] : '';

        if ( 1 < $step && empty( $action ) )
            $this->trigger_error( '104' );

        $error = ( !empty( $_GET['error'] ) ) ? $_GET['error'] : '';
        if ( !empty( $error ) ) { ?>
            <div id="message" class="error wpc_notice fade" ><?php echo $this->get_error_message( $error ) ?></div>
        <?php } ?>

        <div class="wpc_import_export_block <?php echo $action . '_step_' . $step ?>">

            <div class="wpc_imp_exp_step_block"><?php printf( __( 'Step %s', WPC_CLIENT_TEXT_DOMAIN ), $step ) ?></div>

            <?php if ( 1 < $step ) {

                if ( empty( $_GET["{$action}_id"] ) )
                    $this->trigger_error( '105' );

                if ( 5 < $step )
                    $this->trigger_error( '108' );

                $action_data = $this->get_action_data( $action );

                if ( empty( $action_data['type'] ) )
                    $this->trigger_error( '106' );

                if ( !( !empty( $action_data["{$action}_id"] ) && $_GET["{$action}_id"] == $action_data["{$action}_id"] ) )
                    $this->trigger_error( '107' );

                $type = $action_data['type'];

                if ( 'import' == $action ) {
                    $target_path = WPC()->get_upload_dir( 'wpclient/_imports/' );
                    $target_path = $target_path . $type . get_current_user_id() . '_import_file.csv';

                    if ( !file_exists( $target_path ) )
                        $this->trigger_error( '109' );
                } ?>

                <h1>
                    <?php printf( '%s %s "%s"', ucfirst( $action ), ( 'import' == $action ? 'to' : 'from' ), ( ( 'clients' == $type ) ? WPC()->custom_titles['client']['p'] : WPC()->custom_titles['staff']['p'] ) ) ?>
                </h1>
                <hr />

                <?php $function_name = "render_{$action}_step_{$step}";
                $this->$function_name();

            } else {
                $this->render_first_step();
            } ?>

        </div>

        <?php
    }


    /**
     * Render HTML for first Import/Export page
     *
     */
    function render_first_step() {
        $baselink = add_query_arg( array( 'page' => 'wpclients', 'tab' => 'import-export' ), admin_url( 'admin.php' ) );

        if ( isset( $_POST['imp_exp']['action'] ) && in_array( $_POST['imp_exp']['action'], array( 'import', 'export') ) ) {

            $id = uniqid();
            $action = $_POST['imp_exp']['action'];
            $type = !empty( $_POST['imp_exp']["{$action}_action"] ) ? $_POST['imp_exp']["{$action}_action"] : '';
            $next_step_link = add_query_arg( array( 'action' => $action, "{$action}_id" => $id, 'step' => '2' ), $baselink );

            if ( 'import' == $action ) {
                if ( empty( $_POST['imp_exp']['import_action'] ) )
                    $this->trigger_error( '100' );

                if ( !ini_get( 'safe_mode' ) ) {
                    @set_time_limit(0);
                }

                $ext = explode( '.', $_FILES['file']['name'] );
                $ext = strtolower( end( $ext ) );

                if ( $ext !== 'csv' )
                    $this->trigger_error( '102' );

                $target_path = WPC()->get_upload_dir( 'wpclient/_imports/' );
                $target_path = $target_path . $type . get_current_user_id() . '_import_file.csv';

                if ( !move_uploaded_file( $_FILES['file']['tmp_name'], $target_path ) )
                    $this->trigger_error( '101' );

            } else {
                if ( empty( $_POST['imp_exp']['export_action'] ) )
                    $this->trigger_error( '103' );
            }

            $action_data = array(
                "{$action}_id" => $id,
                'type' => $type,
            );
            update_user_meta( get_current_user_id(), "wpc_{$action}_data", $action_data );

            WPC()->redirect( $next_step_link );
        } ?>

        <h1><?php _e( 'Import/Export Data', WPC_CLIENT_TEXT_DOMAIN ) ?></h1>
        <hr />
        <h2><?php _e( 'What action would you like to perform?', WPC_CLIENT_TEXT_DOMAIN ) ?></h2>

        <a href="javascript:void(0);" class="wpc_imp_exp_button" id="import_button">
            <span class="wpc_imp_exp_button_label"><span class="wpc_imp_exp_import_icon">&nbsp;</span>&nbsp;&nbsp;<?php _e( 'Import', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
        </a>

        <a href="javascript:void(0);" class="wpc_imp_exp_button" id="export_button">
            <span class="wpc_imp_exp_button_label"><?php _e( 'Export', WPC_CLIENT_TEXT_DOMAIN ) ?>&nbsp;&nbsp;<span class="wpc_imp_exp_export_icon">&nbsp;</span></span>
        </a>

        <br />
        <br />

        <form action="<?php echo $baselink ?>" method="post" id="wpc_imp_exp_step_1" enctype="multipart/form-data">

            <div class="wpc_imp_exp_select_block" id="select_import">
                <h2><?php _e( 'What type of users would you like to import?', WPC_CLIENT_TEXT_DOMAIN ) ?></h2>
                <input type="hidden" name="imp_exp[action]" value="import" />
                <ul>
                    <li><h3><label><input type="radio" name="imp_exp[import_action]" value="clients" /><?php echo WPC()->custom_titles['client']['p'] ?></label></h3></li>
                    <li><h3><label><input type="radio" name="imp_exp[import_action]" value="staffs" /><?php echo WPC()->custom_titles['staff']['p'] ?></label></h3></li>
                </ul>

                <br>

                <label for="file"><?php _e( 'Import CSV List' , WPC_CLIENT_TEXT_DOMAIN ) ?> <input type="file" name="file" id="file" accept=".csv" /></label>

                <br>
                <br>
                <a href="javascript:void(0);" class="wpc_imp_exp_button wpc_imp_exp_continue">
                    <span class="wpc_imp_exp_button_label"><?php _e( 'Continue', WPC_CLIENT_TEXT_DOMAIN ) ?> ></span>
                </a>
            </div>

            <div class="wpc_imp_exp_select_block" id="select_export">
                <h2><?php _e( 'What type of users would you like to export?', WPC_CLIENT_TEXT_DOMAIN ) ?></h2>
                <input type="hidden" name="imp_exp[action]" value="export" />
                <ul>
                    <li><h3><label><input type="radio" name="imp_exp[export_action]" value="clients" /><?php echo WPC()->custom_titles['client']['p'] ?></label></h3></li>
                    <li><h3><label><input type="radio" name="imp_exp[export_action]" value="staffs" /><?php echo WPC()->custom_titles['staff']['p'] ?></label></h3></li>
                </ul>

                <a href="javascript:void(0);" class="wpc_imp_exp_button wpc_imp_exp_continue" >
                    <span class="wpc_imp_exp_button_label"><?php _e( 'Continue', WPC_CLIENT_TEXT_DOMAIN ) ?> ></span>
                </a>
            </div>
        </form>

        <script type="text/javascript">
            jQuery( document ).ready( function() {
                jQuery( '#import_button' ).click( function() {
                    jQuery( '#export_button' ).removeClass( 'wpc_imp_exp_active' );
                    jQuery( '#select_export' ).hide();
                    jQuery( '#select_import' ).show();
                    jQuery( this ).addClass( 'wpc_imp_exp_active' );
                });

                jQuery( '#export_button' ).click( function() {
                    jQuery( '#import_button' ).removeClass( 'wpc_imp_exp_active' );
                    jQuery( '#select_import' ).hide();
                    jQuery( '#select_export' ).show();
                    jQuery( this ).addClass( 'wpc_imp_exp_active' );
                });


                jQuery( '.wpc_imp_exp_continue' ).click( function() {
                    jQuery( '.wpc_imp_exp_select_block:hidden' ).remove();
                    jQuery( '#wpc_imp_exp_step_1' ).submit();
                });
            });
        </script>
        <?php
    }


    /**
     * Get error message form error code
     *
     * @param $error_code
     * @return string
     */
    function get_error_message( $error_code ) {
        /**
         * Note:
         *
         * first digit: step
         * last digit: error number
         */
        $errors = array(
            '100'   => __( 'Sorry: Empty import object!', WPC_CLIENT_TEXT_DOMAIN ),
            '101'   => __( 'Sorry: Seems was problem with upload CSV file! Try again or check that Uploads folder is writable!', WPC_CLIENT_TEXT_DOMAIN ),
            '102'   => __( 'Sorry: No CSV file selected!', WPC_CLIENT_TEXT_DOMAIN ),
            '103'   => __( 'Sorry: Empty export object!', WPC_CLIENT_TEXT_DOMAIN ),
            '104'   => __( 'Sorry: Wrong action!', WPC_CLIENT_TEXT_DOMAIN ),
            '105'   => __( 'Sorry: ID is empty!', WPC_CLIENT_TEXT_DOMAIN ),
            '106'   => __( 'Sorry: Empty action type!', WPC_CLIENT_TEXT_DOMAIN ),
            '107'   => __( 'Sorry: Wrong action ID!', WPC_CLIENT_TEXT_DOMAIN ),
            '108'   => __( 'Sorry: Seems was open wrong step!', WPC_CLIENT_TEXT_DOMAIN ),
            '109'   => __( 'Sorry: CSV file not found!', WPC_CLIENT_TEXT_DOMAIN ),
            '301'   => __( 'Sorry: Some required fields are empty!', WPC_CLIENT_TEXT_DOMAIN ),
            '302'   => __( 'Sorry: Wrong delimiter!', WPC_CLIENT_TEXT_DOMAIN ),
            '303'   => __( 'Sorry, export field keys cannot be left blank!', WPC_CLIENT_TEXT_DOMAIN ),
            '201'   => sprintf( __( 'Sorry: Empty %s count!', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
            '202'   => sprintf( __( 'Sorry: Empty %s count!', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['p'] ),
        );

        return !empty( $errors[$error_code] ) ? $errors[$error_code] : __( 'Sorry: Undefined error!', WPC_CLIENT_TEXT_DOMAIN );
    }


    /**
     * Trigger error redirect
     *
     * @param string $error_code
     * @param string $baselink
     */
    function trigger_error( $error_code, $baselink = '' ) {
        if( empty( $baselink ) )
            $baselink = add_query_arg( array( 'page' => 'wpclients', 'tab' => 'import-export' ), admin_url( 'admin.php' ) );

        WPC()->redirect( add_query_arg( array( 'error' => $error_code ), $baselink ) );
        exit;
    }


    /**
     * Get import/export data from user meta
     *
     * @param $action
     * @return mixed
     */
    function get_action_data( $action ) {
        return get_user_meta( get_current_user_id(), "wpc_{$action}_data", true );
    }


    /**
     * @param $fields
     * @return array
     */
    function get_list_clients_meta_fields( $fields ) {
        global $wpdb;

        $all_metas = $wpdb->get_col(
            "SELECT DISTINCT meta_key
            FROM {$wpdb->usermeta}
            WHERE meta_key NOT IN('wpc_import_data','wpc_export_data')"
        );

        if ( $all_metas ) {
            $fields = array_merge( $fields, $all_metas );
            $fields = array_unique( $fields );
        }

        $wpc_custom_fields = WPC()->get_settings( 'custom_fields' );
        if ( ! empty( $wpc_custom_fields ) ) {
            $keys = array();
            foreach ( $wpc_custom_fields as $k=>$custom_field ) {
                if ( isset( $custom_field['nature'] ) && ( 'client' == $custom_field['nature'] || 'both' == $custom_field['nature'] ) ) {
                    $keys[] = $k;
                }
            }

            $fields = array_merge( $fields, $keys );
            $fields = array_unique( $fields );
        }

        return $fields;
    }


    /**
     * @param $fields
     * @return array
     */
    function get_list_staffs_meta_fields( $fields ) {
        global $wpdb;

        $all_metas = $wpdb->get_col(
            "SELECT DISTINCT meta_key
            FROM {$wpdb->usermeta}
            WHERE meta_key NOT IN('wpc_import_data','wpc_export_data')"
        );

        if ( $all_metas ) {
            $fields = array_merge( $fields, $all_metas );
            $fields = array_unique( $fields );
        }

        $wpc_custom_fields = WPC()->get_settings( 'custom_fields' );
        if ( ! empty( $wpc_custom_fields ) ) {
            $keys = array();
            foreach ( $wpc_custom_fields as $k=>$custom_field ) {
                if ( isset( $custom_field['nature'] ) && ( 'staff' == $custom_field['nature'] || 'both' == $custom_field['nature'] ) ) {
                    $keys[] = $k;
                }
            }

            $fields = array_merge( $fields, $keys );
            $fields = array_unique( $fields );
        }

        return $fields;
    }


    /**
     * Function for import processing
     *
     * @param $type
     * @param int $import_id
     * @return bool|array
     */
    function import_process( $type, $import_id ) {
        if ( !ini_get( 'safe_mode' ) ) {
            @set_time_limit(0);
        }

        $headers        = array();
        $count_done     = 0;
        $count_failed   = 0;

        $import_data = get_user_meta( get_current_user_id(), 'wpc_import_data', true );
        $delimiter = !empty( $import_data['delimiter'] ) ? $import_data['delimiter'] : ',';
        $head = !empty( $import_data['fline_head'] ) ? $import_data['fline_head'] : true;

        $target_path = WPC()->get_upload_dir( 'wpclient/_imports/' );
        $target_path = $target_path . $type . get_current_user_id() . '_import_file.csv';

        if ( file_exists( $target_path ) && ( $handle = fopen( $target_path, "r" ) ) !== FALSE ) {
            $row = 0;
            while ( ( $data = fgetcsv( $handle, 1000, $delimiter ) ) !== FALSE ) {

                if ( 0 == $row && $head ) {

                    foreach( $data as $key => $val ) {
                        $headers[] = '{' . $val . '}';
                    }

                    $head = false;
                    continue;
                }


                if ( !count( $headers ) ) {
                    foreach( $data as $key => $val ) {
                        $headers[] = '{column_' . ( $key + 1 ) . '}';
                    }
                }

                if ( count( $headers ) == count( $data ) ) {
                    $data = array_combine( $headers, $data );

                    /*our_hook_
                        hook_name: wpc_client_item_import_ . $type
                        hook_title: Import one item of some type
                        hook_description: Can be used for filter item data when doing import.
                        hook_type: filter
                        hook_in: wp-client
                        hook_location
                        hook_param: bool $result, array $data
                        hook_since: 4.0.2
                    */
                    $result_import = apply_filters( 'wpc_client_item_import_' . $type, false, $data );

                    if ( $result_import ) {
                        $count_done++;
                    } else {
                        $count_failed++;
                    }
                } else {
                    $count_failed++;
                }

                $row++;
            }
        }

        return array( 'count_done' => $count_done, 'count_failed' => $count_failed );
    }


    /**
     * @param $result
     * @param $data
     * @return bool
     */
    function import_clients( $result, $data ) {
        global $wpdb;

        //set userdata
        $userdata = array(
            'role' => 'wpc_client',
        );

        $import_data = get_user_meta( get_current_user_id(), 'wpc_import_data', true );
        $associations = !empty( $import_data['associations'] ) ? $import_data['associations'] : array();
        $selected_circles_for_import = !empty( $import_data['assigns']['wpc_circles'] ) ? explode( ',', $import_data['assigns']['wpc_circles'] ) : array();
        $selected_managers_for_import = !empty( $import_data['assigns']['wpc_managers'] ) ? $import_data['assigns']['wpc_managers'] : '';

        $update_exists_users = !empty( $import_data['update_exist_clients'] ) ? true : false;

        //set userdata values by fields
        foreach( $this->import_fields['clients']['main_fields'] as $key => $value ) {

            if ( empty( $associations['main_fields'][$key] ) && ( isset( $value['required'] ) && true == $value['required'] ) ) {
                return false;
            }

            if ( empty( $associations['main_fields'][$key] ) ) {
                continue;
            }


            $str_val = $associations['main_fields'][$key];

            $userdata[$key] = WPC()->prepare_password( str_replace( array_keys( $data ), array_values( $data ), $str_val ) );

        }

        if ( empty( $userdata['user_login'] ) || empty( $userdata['user_email'] ) )
            return false;

        //already exsits user name
        if ( username_exists( $userdata['user_login'] ) ) {
            if ( ! $update_exists_users )
                return false;

            $user = get_user_by( 'login', $userdata['user_login'] );

            if ( ! user_can( $user, 'wpc_client' ) )
                return false;

            $userdata['ID'] = $user->ID;
        } else {

            if ( !isset( $userdata['user_pass'] ) || '' == $userdata['user_pass'] ) {
                $userdata['user_pass'] = WPC()->members()->generate_password();
                $userdata['send_password'] = '1';
            }

            if ( !isset( $userdata['business_name'] ) || '' == $userdata['business_name'] )
                $userdata['business_name'] = $userdata['user_login'];

            //email already exists
            $user_email = apply_filters( 'pre_user_email', isset( $userdata['user_email'] ) ? $userdata['user_email'] : '' );
            if ( email_exists( $user_email ) ) {
                return false;
            }
        }

        $userdata['display_name'] = ( isset( $userdata['display_name'] ) && '' != $userdata['display_name'] ) ? $userdata['display_name'] : '';
        $userdata['contact_phone'] = ( isset( $userdata['contact_phone'] ) && '' != $userdata['contact_phone'] ) ? $userdata['contact_phone'] : '';

        if ( ! empty( $userdata['expiration_date'] ) ) {
            $expiration_date = explode( '/', $userdata['expiration_date'] );

            if ( ! empty( $expiration_date[2] ) ) {

                $expiration_date = mktime ( 12, 0, 0, $expiration_date[0], $expiration_date[1], $expiration_date[2] );

                $userdata['expiration_date'] = $expiration_date;
            }
        }


        //assigns circles
        if ( ! empty( $userdata['client_circles'] ) ) {

            //get circles
            $circles = $wpdb->get_results(
                "SELECT group_id,
                    group_name
                FROM {$wpdb->prefix}wpc_client_groups",
            ARRAY_A );

            $circles_keys = array();
            if ( is_array( $circles ) ) {
                foreach ( $circles as $circle ) {
                    $circles_keys[strtolower( $circle['group_name'] )] = $circle['group_id'];
                }
            }

            //get circles from import file
            $import_circles = explode( '|', $userdata['client_circles'] );
            if ( is_array( $import_circles ) && 0 < count( $import_circles ) ) {
                $circles_ids = array();
                foreach ( $import_circles as $import_circle ) {
                    //check circles from import with circles in DB
                    $import_circle = trim( strtolower( $import_circle ) );
                    if ( isset( $circles_keys[$import_circle] ) ) {
                        //add correct circles in array
                        $circles_ids[] = $circles_keys[$import_circle];
                    }

                }

                //add circles to client
                if ( 0 < count( $circles_ids ) ) {
                    $userdata['client_circles'] = array_unique( $circles_ids );
                } else {
                    $userdata['client_circles'] = array();
                }
            }
        } else {
            $userdata['client_circles'] = array();
        }

        $userdata['client_circles'] = array_unique( array_merge( $userdata['client_circles'], $selected_circles_for_import ) );

        $userdata['admin_manager'] = $selected_managers_for_import;


        //add client
        $client_id = WPC()->members()->client_update_func( $userdata );

        if ( $client_id ) {
            if ( !empty( $associations['meta_fields'] ) ) {
                foreach( $associations['meta_fields'] as $key => $value ) {
                    $value = WPC()->prepare_password( str_replace( array_keys( $data ), array_values( $data ), $value ) );

                    update_user_meta( $client_id, $key, $value );

                }

            }

            if ( !empty( $associations['meta_new_fields'] ) ) {
                foreach( $associations['meta_new_fields'] as $key => $value ) {
                    $value['key'] = WPC()->prepare_password( str_replace( array_keys( $data ), array_values( $data ), $value['key'] ) );
                    $value['val'] = WPC()->prepare_password( str_replace( array_keys( $data ), array_values( $data ), $value['val'] ) );

                    update_user_meta( $client_id, $value['key'], $value['val'] );

                }

            }

        }

        return true;
    }



    /**
     * @param $result
     * @param $data
     * @return bool
     */
    function import_staffs( $result, $data ) {
        //set userdata
        $userdata = array(
            'role' => 'wpc_client_staff',
        );

        $import_data = get_user_meta( get_current_user_id(), 'wpc_import_data', true );
        $associations = !empty( $import_data['associations'] ) ? $import_data['associations'] : array();
        $selected_clients_for_import = !empty( $import_data['assigns']['wpc_clients'] ) ? $import_data['assigns']['wpc_clients'] : '';

        $update_exists_users = !empty( $import_data['update_exist_clients'] ) ? true : false;

        //set userdata values by fields
        foreach( $this->import_fields['staffs']['main_fields'] as $key => $value ) {

            if ( empty( $associations['main_fields'][$key] ) && ( isset( $value['required'] ) && true == $value['required'] ) ) {
                return false;
            }

            if ( empty( $associations['main_fields'][$key] ) ) {
                continue;
            }


            $str_val = $associations['main_fields'][$key];

            $userdata[$key] = WPC()->prepare_password( str_replace( array_keys( $data ), array_values( $data ), $str_val ) );

        }

        //already exsits user name
        if ( username_exists( $userdata['user_login'] ) ) {
            if ( !$update_exists_users )
                return false;

            $user = get_user_by( 'login', $userdata['user_login'] );

            if ( !user_can( $user, 'wpc_client_staff' ) )
                return false;

            $userdata['ID'] = $user->ID;
        } else {

            if ( !isset( $userdata['user_pass'] ) || '' == $userdata['user_pass'] )
                $userdata['user_pass'] = WPC()->members()->generate_password();

            //email already exists
            $user_email = apply_filters( 'pre_user_email', isset( $userdata['user_email'] ) ? $userdata['user_email'] : '' );
            if ( email_exists( $user_email ) ) {
                return false;
            }
        }

        $userdata['display_name'] = ( isset( $userdata['display_name'] ) && '' != $userdata['display_name'] ) ? $userdata['display_name'] : '';

        if ( !isset( $userdata['ID'] ) ) {
            //insert new Employee
            $staff_id = wp_insert_user( $userdata );

        } else {
            if( empty( $userdata['user_pass'] ) )
                unset( $userdata['user_pass'] );

            wp_update_user( $userdata );
            $staff_id = $userdata['ID'];
        }

        if ( $staff_id ) {
            //assign to clients
            if ( ! empty( $userdata['client_username'] ) ) {

                $client = get_user_by( 'login', $userdata['client_username'] );
                if ( ! empty( $client->ID ) ) {
                    update_user_meta( $staff_id, 'parent_client_id', $client->ID );
                } else {
                    if ( ! empty( $selected_clients_for_import ) )
                        update_user_meta( $staff_id, 'parent_client_id', $selected_clients_for_import );
                }

            } elseif ( ! empty( $selected_clients_for_import ) ) {
                update_user_meta( $staff_id, 'parent_client_id', $selected_clients_for_import );
            }

            if ( ! empty( $associations['meta_fields'] ) ) {
                foreach( $associations['meta_fields'] as $key => $value ) {
                    $value = WPC()->prepare_password( str_replace( array_keys( $data ), array_values( $data ), $value ) );

                    update_user_meta( $staff_id, $key, $value );

                }

            }

            if ( !empty( $associations['meta_new_fields'] ) ) {
                foreach( $associations['meta_new_fields'] as $key => $value ) {
                    $value['key'] = WPC()->prepare_password( str_replace( array_keys( $data ), array_values( $data ), $value['key'] ) );
                    $value['val'] = WPC()->prepare_password( str_replace( array_keys( $data ), array_values( $data ), $value['val'] ) );

                    update_user_meta( $staff_id, $value['key'], $value['val'] );

                }

            }

            if ( ! empty( $userdata['temp_password'] ) ) {
                global $wpdb;

                $user_pass = $wpdb->get_var( $wpdb->prepare(
                    "SELECT user_pass
                    FROM {$wpdb->users}
                    WHERE ID = %d",
                    $staff_id
                ) );
                update_user_meta( $staff_id, 'wpc_temporary_password', md5( $user_pass ) );
            }

            //send password
            if ( isset( $userdata['send_password'] ) && '' != $userdata['send_password'] ) {
                $args = array( 'client_id' => $staff_id, 'user_password' => $userdata['user_pass'] );

                //send email
                WPC()->mail( 'staff_created', $userdata['user_email'], $args, 'staff_created' );
            }

        }

        return true;
    }


    /**
     * Function for export processing
     *
     * @param $type
     * @param int $export_id
     * @return bool|array
     */
    function export_process( $type, $export_id ) {
        if ( !ini_get( 'safe_mode' ) ) {
            @set_time_limit(0);
        }

        $export_data = get_user_meta( get_current_user_id(), 'wpc_export_data', true );

        $delimiter = !empty( $export_data['delimiter'] ) ? $export_data['delimiter'] : ',';
        $head = !empty( $export_data['fline_head'] ) ? $export_data['fline_head'] : false;

        $target_path = WPC()->get_upload_dir( 'wpclient/_exports/' );
        $target_path = $target_path . $type . get_current_user_id() . '_export_file.csv';

        if ( file_exists( $target_path ) ) {
            unlink( $target_path );
        }

        $export_fields = !empty( $export_data['export_fields'] ) ? $export_data['export_fields'] : array();

        $csv_data = array();
        $headers = array();
        $placeholders = array();
        foreach ( $export_fields as $export_field ) {
            if ( $head && empty( $export_field['key'] ) )
                continue;

            if ( empty( $export_field['value'] ) )
                continue;

            if ( $head ) {
                $headers[] = $export_field['key'];
            }
            $placeholders[] = $export_field['value'];
        }

        if ( empty( $placeholders ) )
            return false;

        if ( $head ) {
            if ( empty( $headers ) )
                return false;

            $csv_data[] = $headers;
        }


        /*our_hook_
            hook_name: wpc_client_item_export_ . $type
            hook_title: Build Export CSV data
            hook_description: Can be used for filter item data when doing export.
            hook_type: filter
            hook_in: wp-client
            hook_location
            hook_param: bool|array $csv_data, array $placeholders
            hook_since: 4.0.2
        */
        $csv_data = apply_filters( 'wpc_client_item_export_' . $type, $csv_data, $placeholders );

        if ( false === $csv_data )
            return false;

        if ( count( $csv_data ) && ( $handle = fopen( $target_path, 'w' ) ) !== FALSE ) {

            foreach ( $csv_data as $fields ) {
                fputcsv( $handle, $fields, $delimiter );
            }

            fclose( $handle );

            $csv_content = file_get_contents( $target_path );
            if ( ( $handle = fopen( $target_path, 'w' ) ) !== false ) {
                fwrite ( $handle , chr(255) . chr(254) . mb_convert_encoding( $csv_content, 'UTF-16LE', 'UTF-8') );
                fclose( $handle );
            }

            $count = count( $csv_data );
            if ( $head ) {
                $count--;
            }

            return array( 'count_done' => $count );
        }

        return false;
    }


    /**
     * Extend csv data for clients
     *
     * @param array $csv_data
     * @param array $placeholders
     * @return array|bool
     */
    function export_clients( $csv_data, $placeholders ) {
        $export_data = $this->get_action_data( 'export' );

        $args = array(
            'role'         => 'wpc_client',
            'orderby'      => 'ID',
            'order'        => 'ASC',
            'fields'       => 'all',
        );

        if ( !empty( $export_data['items'] ) && 'all' != $export_data['items'] ) {
            $args['include'] = $export_data['items'];
        } else {
            $args['exclude'] = WPC()->members()->get_excluded_clients();
        }

        $clients = get_users( $args );

        if ( empty( $clients ) )
            return false;

        foreach ( $clients as $client ) {
            $client_data = array();

            foreach ( $placeholders as $placeholder ) {
                $placeholder = str_replace( array( '}','{' ), array( '','' ), $placeholder );
                if ( in_array( $placeholder, $this->export_fields['clients']['main_fields'] ) ) {
                    $client_data[] = !empty( $client->$placeholder ) ? $client->$placeholder : '';
                } else {
                    $meta_value = get_user_meta( $client->ID, $placeholder, true );
                    if ( is_array( $meta_value ) )
                        $meta_value = serialize( $meta_value );

                    $client_data[] = $meta_value;
                }
            }

            $csv_data[] = $client_data;
        }

        return $csv_data;
    }


    /**
     * Extend csv data for staffs
     *
     * @param array $csv_data
     * @param array $placeholders
     * @return array|bool
     */
    function export_staffs( $csv_data, $placeholders ) {

        $export_data = $this->get_action_data( 'export' );

        $args = array(
            'role'         => 'wpc_client_staff',
            'orderby'      => 'ID',
            'order'        => 'ASC',
            'fields'       => 'all',
        );

        if ( !empty( $export_data['items'] ) && 'all' != $export_data['items'] ) {
            $args['include'] = $export_data['items'];
        }

        $staffs = get_users( $args );

        if ( empty( $staffs ) )
            return false;

        foreach ( $staffs as $staff ) {
            $staff_data = array();

            foreach ( $placeholders as $placeholder ) {
                $placeholder = str_replace( array( '}','{' ), array( '','' ), $placeholder );
                if ( in_array( $placeholder, $this->export_fields['staffs']['main_fields'] ) ) {
                    $staff_data[] = !empty( $staff->$placeholder ) ? $staff->$placeholder : '';
                } else {
                    $meta_value = get_user_meta( $staff->ID, $placeholder, true );
                    if ( is_array( $meta_value ) )
                        $meta_value = serialize( $meta_value );

                    $staff_data[] = $meta_value;
                }
            }

            $csv_data[] = $staff_data;
        }

        return $csv_data;
    }


    function show_form_save_template(){
        ob_start(); ?>
        <div id="save_template_form" style="display: none;">
            <label>Template name: <input type="text" placeholder="<?php _e( 'Input template name', WPC_CLIENT_TEXT_DOMAIN ) ?>"></label>
            <button id="save_template_name" class="button-primary"> <?php _e( 'Save as template', WPC_CLIENT_TEXT_DOMAIN )?> </button>
            <label><p id="empty_tmp_name" style="color:red; display: none"><?php _e( 'Please, input template name.', WPC_CLIENT_TEXT_DOMAIN ) ?></p>
            </label>
        </div>
        <?php
        return ob_get_clean();
    }

}

endif;