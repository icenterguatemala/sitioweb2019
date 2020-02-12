<?php

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly


if ( ! class_exists( 'WPC_Custom_Fields' ) ) :

final class WPC_Custom_Fields {

    /**
     * The single instance of the class.
     *
     * @var WPC_Custom_Fields
     * @since 4.5
     */
    protected static $_instance = null;

    /**
     * Instance.
     *
     * Ensures only one instance of WPC_Custom_Fields is loaded or can be loaded.
     *
     * @since 4.5
     * @static
     * @return WPC_Custom_Fields - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {

    }


    function render_custom_field( $field, $form, $user_id = 0, $readonly = false ) {
        if ( current_user_can( 'administrator' ) ) {
            $current_role = 'administrator';
        } elseif ( current_user_can( 'wpc_admin' ) ) {
            $current_role = 'admin';
        } elseif ( current_user_can( 'wpc_manager' ) ) {
            $current_role = 'manager';
        } elseif ( current_user_can( 'wpc_client_staff' ) ) {
            $current_role = 'staff';
        } else {
            $current_role = 'client';
        }

        if ( ! defined( 'DOING_AJAX' ) ) {
            if ( ( ! isset($field['view'][ $form ][ $current_role ] ) && $field['type'] != 'hidden' ) ||
                ( isset( $field['view'][ $form ][ $current_role ] ) && $field['view'][ $form ][ $current_role ] == 'hide' )
            ) return '';

            if ( $user_id > 0 ) {
                $user_role = user_can( $user_id, 'wpc_client_staff' ) ? 'staff' : 'client';
            } else {
                $temp_array = explode( '_', $form );
                $user_role = $temp_array[ count( $temp_array ) - 1 ];
            }

            if ( isset( $field['nature'] ) && $field['nature'] != 'both' && $field['nature'] != $user_role ) return '';
        }

        $value_exists = $user_id > 0 ? metadata_exists( 'user', $user_id, $field['name'] ) : false;
        $cf_value = isset( $_REQUEST['custom_fields'][ $field['name'] ] ) ?
            $_REQUEST['custom_fields'][ $field['name'] ] :
            ( $value_exists ? get_user_meta( $user_id, $field['name'], true ) : null );

        $disabled_field = isset( $field['view'][ $form ][ $current_role ] ) &&
        'view' == $field['view'][ $form ][ $current_role ] &&
        !( '' == $cf_value && isset( $field['required'] ) && '1' == $field['required'] ) ?
            "disabled readonly" : "";
        $attrs = $readonly ? 'disabled readonly' : $disabled_field;

        $custom_class = 'wpc_cf_' . $field['type'];
        $custom_class .= isset( $field['required'] ) && '1' == $field['required'] ? " wpc_" . $field['type'] . "_required" : "";
        $custom_data = isset( $field['required'] ) && '1' == $field['required'] ? 'data-required_field="1"' : '';
        $field['title'] = isset( $field['title'] ) ? $field['title'] : '';
        $field['description'] = isset( $field['description'] ) && '' != $field['description'] ? $field['description'] : '';
        $field['required'] = isset( $field['required'] ) && '1' == $field['required'] ? '1' : '0';
        $field['field'] = '';

        if ( isset( $field['options'] ) && is_array( $field['options'] ) ) {
            if ( isset( $field['sort_order'] ) ) {
                switch( $field['sort_order'] ) {
                    case 'asc':
                        uasort( $field['options'], function( $a, $b ) {
                            return strnatcmp($a['label'], $b['label']);
                        } );
                        break;
                    case 'desc':
                        uasort ( $field['options'], function( $a, $b ) {
                            return strnatcmp($a['label'], $b['label']) * -1;
                        } );
                        break;
                }
            }
        }

        $field['label'] = $field['title'];
        if ( 'hidden' !== $field['type'] && '1' == $field['required'] && !$readonly ) {
            $field['label'] .= ' <span style="color:red;" title="' . __( 'This field is marked as required by the administrator.', WPC_CLIENT_TEXT_DOMAIN ) . '">*</span>';
        }
        $field['label'] = '' != $field['title'] ?
            '<label data-title="' . $field['title'] . '" for="cf_' . $field['name'] .  '">' . $field['label'] . '</label>' : '';

        $field_name = 'custom_fields[' . esc_attr( $field['name'] ) . ']';
        switch ( $field['type'] ) {
            case 'text':
                $custom_class .= !empty( $field['mask_type'] ) ? ' wpc_field_mask_' . $field['mask_type'] : '';
                $custom_class .= ( !empty( $field['mask_reverse'] ) && isset( $field['mask_type'] ) && 'custom' == $field['mask_type'] ) ? ' wpc_field_mask_reverse' : '';
                $field['field'] = '<input type="text" class="' . $custom_class . '" ' . $custom_data . ' id="cf_' . $field['name'] . '" name="' . $field_name . '" value="' . $cf_value . '" '. $attrs . ( ( isset( $field['mask_type'] ) && 'custom' == $field['mask_type'] ) ? ' data-mask-value="' . $field['mask'] . '"' : '' ) . ' />';
                break;
            case 'password':
                $custom_class .= !empty( $field['mask_type'] ) ? ' wpc_field_mask_' . $field['mask_type'] : '';
                $custom_class .= ( !empty( $field['mask_reverse'] ) && isset( $field['mask_type'] ) && 'custom' == $field['mask_type'] ) ? ' wpc_field_mask_reverse' : '';
                $field['field'] = '<input type="password" class="' . $custom_class . '" ' . $custom_data . ' id="cf_' . $field['name'] . '" name="' . $field_name . '" value="'.$cf_value.'" '. $attrs . ( ( isset( $field['mask_type'] ) && 'custom' == $field['mask_type'] ) ? ' data-mask-value="' . $field['mask'] . '"' : '' ) . ' />';
                break;
            case 'datepicker':
                $field['field'] = '<input type="text" class="' . $custom_class . ' custom_datepicker_field" id="cf_' . $field['name'] . '" name="fake_' . $field_name . '" value="" ' . $attrs . ' />';
                $field['field'] .= '<input type="hidden" ' . $custom_data . ' name="' . $field_name . '" value="' . $cf_value . '" />';
                break;
            case 'cost':
                $field['field'] = '<input type="text" class="' . $custom_class . '" ' . $custom_data . ' id="cf_' . $field['name'] . '" name="' . $field_name . '[]" value="' . ( isset( $cf_value[0] ) ? $cf_value[0] : '' ) . '" '. $attrs .' /> ';

                $field['field'] .= '<select class="' . $custom_class . '" id="cf_' . $field['name'] . '_second" name="' . $field_name . '[]" '. $attrs .'> ';

                $wpc_currency = WPC()->get_settings( 'currency' );
                $wpc_custom_field = WPC()->get_settings( 'custom_fields' );
                $currency_type = ! empty( $wpc_custom_field[$field['name']]['currency_type'] )
                                        ? $wpc_custom_field[$field['name']]['currency_type']
                                        : 'code';

                foreach( $wpc_currency as $k=>$val ) {
                    $field['field']  .= '<option value="' . $k . '" ' . selected( isset( $cf_value[1] ) ? $cf_value[1] : '', $k, false ) . '>' . $val[$currency_type] . '</option>';
                }
                $field['field']      .= '</select>';
                break;
            case 'textarea':
                $field['field'] = '<textarea class="' . $custom_class . '" ' . $custom_data . ' id="cf_' . $field['name'] . '" name="' . $field_name . '" '. $attrs .' >' . $cf_value . '</textarea>';
                break;
            case 'radio':
                if( $readonly && !$value_exists ) {  //for view form
                    $field['field'] = '<span class="description">' . __('undefined', WPC_CLIENT_TEXT_DOMAIN ) . '</span>';
                } else if ( is_array( $field['options'] ) ) {
                    $field['field'] .= '<input type="hidden" name="custom_fields[' . esc_attr( $field['name'] ) . ']" value="" />';
                    foreach( $field['options'] as $key=>$option ) {
                        $checked = '';
                        $num = isset( $option['value'] ) ? $option['value'] : '';
                        if ( $cf_value == $num && $cf_value !== null ) {
                            $checked = 'checked';
                        } elseif ( $cf_value === null && isset( $option['default'] ) && $option['default'] == 1 ) {
                            $checked = 'checked';
                        }

                        $field['field'] .= '<label class="wpc_opt"><input type="radio" class="wpc_cf_radio" id="cf_' . esc_attr( $field['name'] . '_' . $key ) . '" name="' . $field_name . '" value="' . esc_attr( $num ) . '" ' . $checked . ' ' . $attrs . ' /> ' . $option['label'] . '</label><br />';
                    }
                }
                break;

            case 'checkbox':
                $cf_value = isset( $_REQUEST['custom_fields'] ) && !isset( $_REQUEST['custom_fields'][ $field['name'] ] ) ? '' : $cf_value;

                if( $readonly && !$value_exists ) {  //for view form
                    $field['field'] = '<span class="description">' . __('undefined', WPC_CLIENT_TEXT_DOMAIN ) . '</span>';
                } else if ( is_array( $field['options'] ) ) {
                    $field['field'] .= '<input type="hidden" name="custom_fields[' . esc_attr( $field['name'] ) . ']" value="" />';
                    foreach( $field['options'] as $key=>$option ) {
                        $checked = '';
                        $num = isset( $option['value'] ) ? $option['value'] : '';
                        if ( is_array( $cf_value ) ) {
                            $checked = in_array( $num, $cf_value ) ? 'checked' : '';
                        } elseif ( isset( $option['default'] ) && $option['default'] == 1 ) {
                            $checked = 'checked';
                        }

                        $field['field'] .= '<label class="wpc_opt">
                                <input type="checkbox" class="wpc_cf_checkbox" id="cf_' . esc_attr( $field['name'] . '_' . $key ) . '" name="' . $field_name . '[]" value="' . esc_attr( $option['value'] ) . '" ' . $checked . ' ' . $attrs . ' /> ' .
                            esc_html( $option['label'] ) .
                            '</label><br />';
                    }
                }
                break;

            case 'selectbox':
                if( $readonly && !$value_exists ) {  //for view form
                    $field['field'] = '<span class="description">' . __('undefined', WPC_CLIENT_TEXT_DOMAIN ) . '</span>';
                } else if ( is_array( $field['options'] ) ) {
                    $field['field'] = '<select class="wpc_cf_selectbox" id="cf_' . $field['name'] . '" name="' . $field_name . '" ' . $attrs . ' >';
                    foreach( $field['options'] as $option ) {
                        $num = isset( $option['value'] ) ? $option['value'] : '';
                        $selected = '';
                        if ( $cf_value == $num && $cf_value !== null ) {
                            $selected = 'selected';
                        } elseif ( $cf_value === null && isset( $option['default'] ) && $option['default'] == 1 ) {
                            $selected = 'selected';
                        }

                        $field['field'] .= '<option value="' . esc_attr( $option['value'] ) . '" ' . $selected . ' > ' . esc_html( $option['label'] ) . ' </option>';
                    }
                    $field['field'] .= '</select>';
                }
                break;
            case 'multiselectbox':
                if( $readonly && !$value_exists ) {  //for view form
                    $field['field'] = '<span class="description">' . __('undefined', WPC_CLIENT_TEXT_DOMAIN ) . '</span>';
                } else if ( is_array( $field['options'] ) ) {
                    $field['field'] .= '<input type="hidden" name="custom_fields[' . esc_attr( $field['name'] ) . ']" value="" />';
                    $field['field'] .= '<select multiple class="wpc_cf_multiselectbox" id="cf_' . $field['name'] . '" name="' . $field_name . '[]" ' . $attrs . ' >';
                    foreach( $field['options'] as $option ) {
                        $num = isset( $option['value'] ) ? $option['value'] : '';
                        $selected = '';
                        if ( is_array( $cf_value ) ) {
                            $selected = in_array( $num, $cf_value ) ? 'selected' : '';
                        } elseif ( isset( $option['default'] ) && $option['default'] == 1 ) {
                            $selected = 'selected';
                        }

                        $field['field'] .= '<option value="' . $option['value'] . '" ' . $selected . ' > ' . $option['label'] . ' </option>';
                    }
                    $field['field'] .= '</select>';
                }
                break;
            case 'hidden':
                $text = $value_exists ? $cf_value : $field['default_value'];
                $field['label']       = '';
                $field['field']       = '<input type="hidden" id="cf_' . $field['name'] . '" name="' . $field_name . '" value="' . $text . '" />';
                $field['description'] = '';
                break;
            case 'file':
                if ( $user_id > 0 ) { //file field for edit/profile forms
                    if ( $value_exists ) { //if user logged in

                        if( is_admin() ) {
                            $download_link = 'admin.php?module=custom_fields&wpc_action=download&nonce=' . wp_create_nonce( $user_id . AUTH_KEY ) . '&id=' . $user_id . '&key=' . $field['name'];
                        } else {
                            $home_url = get_home_url( get_current_blog_id() );

                            if ( WPC()->permalinks ) {
                                $download_link = WPC()->make_url( '/wpc_downloader/custom_fields/?wpc_action=download&id=' . $user_id . '&key=' . $field['name'], $home_url );
                            } else {
                                $download_link = add_query_arg( array( 'wpc_page' => 'wpc_downloader', 'wpc_page_value' => 'custom_fields', 'wpc_action' => 'download', 'id' => $user_id, 'key' => $field['name'] ), $home_url );
                            }
                        }

                        if( !is_admin() ) {
                            $field['field'] .= '<span style="width:60%;float:right;margin:0;padding:0;">';
                        }

                        if ( $readonly ) {
                            $field['field'] .= '<a href="'. $download_link . '" title="' . sprintf( __( 'Download %s', WPC_CLIENT_TEXT_DOMAIN ), $cf_value['origin_name'] ) . '">' . $cf_value['origin_name'] . '</a>';
                        } else {
                            $field['field'] .= '<a href="'. $download_link . '" title="' . sprintf( __( 'Download %s', WPC_CLIENT_TEXT_DOMAIN ), $cf_value['origin_name'] ) . '">' . $cf_value['origin_name'] . '</a><a href="javascript:void(0);" style="color:red;margin-left:10px;" class="wpc_cf_reset_field">&times;</a>';
                            $field['field'] .= '<script type="text/javascript">
                                    jQuery(document).ready( function() {
                                        jQuery(".wpc_cf_reset_field").click( function(){
                                            jQuery(this).after(\'<input type="file" class="' . $custom_class . '" id="cf_' . $field['name'] . '" ' . $custom_data . ' name="custom_fields[' . $field['name'] . ']" />\');
                                            jQuery(this).prev().hide();
                                            jQuery(this).hide();
                                        });
                                    });
                                </script>';
                        }

                        if( !is_admin() ) {
                            $field['field'] .= '</span>';
                        }

                    } else {
                        //if user value is empty
                        $field['field'] = '';

                        if ( $readonly ) {
                            $field['field'] .= __( 'No file', WPC_CLIENT_TEXT_DOMAIN );
                        } else {
                            $field['field'] .= '<input type="file" class="' . $custom_class . '" id="cf_' . $field['name'] . '" ' . $custom_data . ' name="' . $field_name . '" />';
                        }
                    }
                } else {
                    $field['field']       = '<input type="file" class="' . $custom_class . '" id="cf_' . $field['name'] . '" ' . $custom_data . ' name="' . $field_name . '" />';
                }
                break;

        }
        return $field;
    }


    function render_custom_field_value( $current_custom_field, $args = array() ) {
        $cf_value = isset( $args['value'] ) ? $args['value'] : '';
        $user_id = isset( $args['user_id'] ) ? $args['user_id'] : 0;
        $empty_value = isset( $args['empty_value'] ) ? $args['empty_value'] : '';
        $metadata_exists = isset( $args['metadata_exists'] ) ? $args['metadata_exists'] : '';
        $atts = isset( $args['atts'] ) ? $args['atts'] : array();
        $custom_field_html = $metadata_exists ? '' : $empty_value;
        if( isset( $current_custom_field['type'] )  ) {
            switch( $current_custom_field['type'] ) {
                case 'text': case 'textarea':
                if( $metadata_exists ) {
                    $custom_field_html = nl2br( $cf_value );
                }
                break;
                case 'datepicker':
                if( $metadata_exists ) {
                    $custom_field_html = WPC()->date_format( $cf_value, 'date', '', false );
                }
                break;
                case 'cost':
                    if( $metadata_exists ) {
                        $custom_field_html = is_array( $cf_value ) ? implode( ' ', $cf_value ) : '';
                    }
                    break;
                case 'radio':
                    if ( isset( $current_custom_field['options'] ) &&
                        is_array( $current_custom_field['options'] ) && $metadata_exists ) {
                        foreach( $current_custom_field['options'] as $option ) {
                            if( !isset( $option['value'] ) || $option['value'] != $cf_value ) continue;
                            $custom_field_html = $option['label'];
                            break;
                        }
                    }
                    break;
                case 'checkbox':
                    if ( isset( $current_custom_field['options'] ) &&
                        is_array( $current_custom_field['options'] ) && $metadata_exists ) {

                        $delimiter = isset( $atts['delimiter'] ) ? $atts['delimiter'] : ',';

                        $values_array = array();
                        foreach( $current_custom_field['options'] as $option ) {
                            if( is_array( $cf_value ) && in_array( $option['value'], $cf_value ) ) {
                                $values_array[] = $option['label'];
                            }
                        }

                        if( count( $values_array ) ) {
                            $custom_field_html = implode( $delimiter, $values_array );
                        }
                    }
                    break;

                case 'selectbox':
                    if ( isset( $current_custom_field['options'] ) &&
                        is_array( $current_custom_field['options'] ) && $metadata_exists ) {
                        foreach( $current_custom_field['options'] as $option ) {
                            if( !isset( $option['value'] ) || $option['value'] != $cf_value ) continue;
                            $custom_field_html = $option['label'];
                            break;
                        }
                    }
                    break;
                case 'multiselectbox':
                    if ( isset( $current_custom_field['options'] ) &&
                        is_array( $current_custom_field['options'] ) && $metadata_exists ) {

                        $delimiter = isset( $atts['delimiter'] ) ? $atts['delimiter'] : ',';

                        $values_array = array();
                        foreach( $current_custom_field['options'] as $option ) {
                            if( is_array( $cf_value ) && in_array( $option['value'], $cf_value ) ) {
                                $values_array[] = $option['label'];
                            }
                        }

                        if( count( $values_array ) ) {
                            $custom_field_html = implode( $delimiter, $values_array );
                        }
                    }
                    break;
                case 'file':
                    if( isset( $atts['only_link'] ) && ( '1' == $atts['only_link'] || '2' == $atts['only_link'] ) ) {
                        $action = 'view';
                    } else {
                        $action = 'download';
                    }
                    $home_url = get_home_url( get_current_blog_id() );

                    if (WPC()->permalinks) {
                        $download_url = WPC()->make_url('wpc_downloader/custom_fields/?wpc_action=' . $action . '&id=' . $user_id . '&key=' . $current_custom_field['name'], $home_url);
                    } else {
                        $download_url = add_query_arg(array('wpc_page' => 'wpc_downloader', 'wpc_page_value' => 'custom_fields', 'wpc_action' => $action, 'id' => $user_id, 'key' => $current_custom_field['name']), $home_url);
                    }

                    if( $metadata_exists ) {
                        if( isset( $atts['only_link'] ) && '1' == $atts['only_link'] ) {
                            $custom_field_html = $download_url;
                        } elseif( isset( $atts['only_link'] ) && '2' == $atts['only_link'] ) {
                            $custom_field_html = '<img src="' . $download_url . '" title="' . sprintf( __( '%s', WPC_CLIENT_TEXT_DOMAIN ), $cf_value['origin_name'] ) . '" alt="' . sprintf( __( '%s', WPC_CLIENT_TEXT_DOMAIN ), $cf_value['origin_name'] ) . '">';
                        } else {
                            $custom_field_html = '<a href="' . $download_url . '" title="' . sprintf(__('Download %s', WPC_CLIENT_TEXT_DOMAIN), $cf_value['origin_name']) . '">' . $cf_value['origin_name'] . '</a>';
                        }
                    }
                    break;
            }
        }
        return $custom_field_html;
    }



    function add_custom_fields_scripts() {
        $this->add_custom_datepicker_scripts();
        wp_enqueue_script( 'wpc_validation_custom_field' );
        wp_enqueue_script( 'wpc_mask' );
    }


    function add_custom_datepicker_scripts() {
        wp_enqueue_style( 'wpc-ui-style' );

        wp_enqueue_script( 'wpc_custom_datepicker' );

        wp_localize_script('wpc_custom_datepicker', 'wpc_custom_fields', array(
            'buttonText' => __( 'Select date', WPC_CLIENT_TEXT_DOMAIN ),
            'plugin_url' => WPC()->plugin_url,
            'regional' => array(
                'closeText' => __( "Done", WPC_CLIENT_TEXT_DOMAIN ),
                'prevText' => __( "Prev", WPC_CLIENT_TEXT_DOMAIN ),
                'nextText' => __( "Next", WPC_CLIENT_TEXT_DOMAIN ),
                'currentText' => __( "Today", WPC_CLIENT_TEXT_DOMAIN ),
                'monthNames' => array(
                    __( "January", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "February", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "March", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "April", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "May", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "June", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "July", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "August", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "September", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "October", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "November", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "December", WPC_CLIENT_TEXT_DOMAIN )
                ),
                'monthNamesShort' => array(
                    __( "Jan", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "Feb", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "Mar", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "Apr", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "May", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "Jun", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "Jul", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "Aug", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "Sep", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "Oct", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "Nov", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "Dec", WPC_CLIENT_TEXT_DOMAIN )
                ),
                'dayNames' => array(
                    __( "Sunday", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "Monday", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "Tuesday", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "Wednesday", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "Thursday", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "Friday", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "Saturday", WPC_CLIENT_TEXT_DOMAIN )
                ),
                'dayNamesShort' => array(
                    __( "Sun", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "Mon", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "Tue", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "Wed", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "Thu", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "Fri", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "Sat", WPC_CLIENT_TEXT_DOMAIN )
                ),
                'dayNamesMin' => array(
                    __( "Su", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "Mo", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "Tu", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "We", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "Th", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "Fr", WPC_CLIENT_TEXT_DOMAIN ),
                    __( "Sa", WPC_CLIENT_TEXT_DOMAIN )
                ),
                'weekHeader' => __( "Wk", WPC_CLIENT_TEXT_DOMAIN ),
                'dateFormat' => $this->datetimeformat_php_to_js( get_option( 'date_format', 'm/d/Y' ) ),
                'firstDay' => get_option( 'start_of_week', 0 ),
                'isRTL' => false,
                'showMonthAfterYear' => false,
                'yearSuffix' => ""
            )
        ));
    }


    function datetimeformat_php_to_js( $php_format ) {
        $SYMBOLS_MATCHING = array(
            // Day
            'd' => 'dd',
            'D' => 'D',
            'j' => 'd',
            'l' => 'DD',
            'N' => '',
            'S' => '',
            'w' => '',
            'z' => 'o',
            // Week
            'W' => '',
            // Month
            'F' => 'MM',
            'm' => 'mm',
            'M' => 'M',
            'n' => 'm',
            't' => '',
            // Year
            'L' => '',
            'o' => '',
            'Y' => 'yy',
            'y' => 'y',
            // Time
            'a' => 'tt',
            'A' => 'TT',
            'B' => '',
            'g' => 'h',
            'G' => 'H',
            'h' => 'hh',
            'H' => 'HH',
            'i' => 'mm',
            's' => 'ss',
            'u' => 'c'
        );
        $jqueryui_format = "";
        $escaping = false;
        for($i = 0; $i < strlen($php_format); $i++) {
            $char = $php_format[$i];
            if($char === '\\') { // PHP date format escaping character
                $i++;
                if($escaping) $jqueryui_format .= $php_format[$i];
                else $jqueryui_format .= '\'' . $php_format[$i];
                $escaping = true;
            } else {
                if($escaping) { $jqueryui_format .= "'"; $escaping = false; }
                if(isset($SYMBOLS_MATCHING[$char]))
                    $jqueryui_format .= $SYMBOLS_MATCHING[$char];
                else
                    $jqueryui_format .= $char;
            }
        }
        return $jqueryui_format;
    }



    function validate_custom_fields_post_data( $key, $custom_fields ) {
        $custom_field_values = ( isset( $_REQUEST[ $key ] ) && is_array( $_REQUEST[ $key ] ) ) ? $_REQUEST[ $key ] : array();
        $files_data = array();
        if( isset( $_FILES[ $key ] ) && is_array( $_FILES[ $key ] ) ) {
            foreach( $_FILES[ $key ] as $k1=>$val1 ) {
                foreach( $val1 as $k2=>$val2 ) {
                    $files_data[$k2][$k1] = $val2;
                }
            }
        }

        foreach( $custom_fields as $cf_key=>$custom_field ) {
            if( isset( $custom_field['required'] ) && $custom_field['required'] == '1' ) {

                if( $custom_field['type'] != 'file' && !empty( $custom_field_values[ $cf_key ] ) ) continue;

                if( $custom_field['type'] == 'file' && !empty( $files_data[ $cf_key ]['name'] ) ) continue;

                $error_code = 'custom_field_required' . uniqid();
                $error_message = sprintf( __( "%s is required.<br/>", WPC_CLIENT_TEXT_DOMAIN ), $custom_field['title'] );
                if( isset( $errors ) && $errors instanceof WP_Error ) {
                    $errors->add( $error_code, $error_message );
                } else {
                    $errors = new WP_Error( $error_code, $error_message );
                }
            }
        }

        if( isset( $errors ) ) {
            return $errors;
        }

        return $custom_field_values;
    }

    /*
    * Get Custom Fields
    */
    function get_custom_fields( $form, $client_id = 0, $readonly = false ) {
        if( !isset( $form ) || '' == $form ) return false;

        $wpc_custom_fields = WPC()->get_settings( 'custom_fields' );

        $custom_fields = array();
        if ( is_array( $wpc_custom_fields ) ) {
            foreach( $wpc_custom_fields as $key => $value ) {
                $value['name'] = $key;
                $field = $this->render_custom_field( $value, $form, $client_id, $readonly );
                if( is_array( $field ) ) {
                    $custom_fields[ $key ] = $field;
                }

            }
        }

        /*our_hook_
            hook_name: wpc_client_custom_fields_data
            hook_title: Custom Fields Data
            hook_description: Can be used for change custom fields data.
            hook_type: filter
            hook_in: wp-client
            hook_location class.common.php
            hook_param: array $custom_fields
            hook_since: 4.0.6
        */
        return apply_filters( 'wpc_client_custom_fields_data', $custom_fields );
    }


    /*
    * Get Custom Fields for users
    */
    function get_custom_fields_for_users( $form = 'admin_screen', $users = 'client' ) {
        $wpc_custom_fields = WPC()->get_settings( 'custom_fields' );
        $return_custom_fields = array();
        if( is_array( $wpc_custom_fields ) ) {
            $current_role = $users;
            if ( current_user_can( 'administrator' ) ) {
                $current_role = 'administrator';
            } elseif ( current_user_can( 'wpc_admin' ) ) {
                $current_role = 'admin';
            } elseif ( current_user_can( 'wpc_manager' ) ) {
                $current_role = 'manager';
            }

            if ( 'client' == $current_role ) {
                return array();
            }
            foreach ( $wpc_custom_fields as $key => $value ) {
                if ( !isset( $value['nature'] ) || 'client' == $value['nature'] ) {
                    $for_users = 'client';
                } elseif ( 'both' == $value['nature'] ) {
                    $for_users = $users;
                } else {
                    $for_users = 'staff';
                }

                if ( $for_users == $users && isset( $value['view'][ $form ][ $current_role ] )
                    && 'edit' == $value['view'][ $form ][ $current_role ] ) {
                    $return_custom_fields[ $key ] = $value;
                }
            }
        }
        return $return_custom_fields;

    }

}

endif;