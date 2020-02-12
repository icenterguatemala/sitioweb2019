<?php

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

if ( ! class_exists( 'WPC_Settings' ) ) :

class WPC_Settings {

    /**
     * The single instance of the class.
     *
     * @var WPC_Settings
     * @since 4.5
     */
    protected static $_instance = null;

    /**
     * Instance.
     *
     * Ensures only one instance of WPC_Settings is loaded or can be loaded.
     *
     * @since 4.5
     * @static
     * @return WPC_Settings - Main instance.
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
     * Render settings section
     *
     * @param $section_fields
     */
    function render_settings_section( $section_fields ) {
        ob_start(); ?>

        <table class="form-table wpc-settings-section">
            <tbody>
            <?php foreach ( $section_fields as $field_data ) {

                if ( empty( $field_data['type'] ) )
                    continue;

                if ( 'title' == $field_data['type'] ) {
                    $html = '<tr class="wpc-settings-title">
                        <td colspan="2">';
                } elseif ( 'hidden' != $field_data['type'] ) {
                    $conditional = !empty( $field_data['conditional'] ) ? 'data-conditional="' . esc_attr( json_encode( $field_data['conditional'] ) ) . '"' : '';

                    $html = '<tr class="wpc-settings-line" ' . $conditional . '><th><label for="wpc_settings_' . $field_data['id'] . '">' . $field_data['label'] . '</label></th><td>';
                } else {
                    $html = '';
                }

                $html .= $this->render_setting_field( $field_data );

                $html .= '</td></tr>';

                echo $html;

            }
            ?>
            </tbody>
        </table>

        <?php ob_get_flush();
    }


    /**
     * Render HTML for settings field
     *
     * @param $data
     * @return string
     */
    function render_setting_field( $data ) {
        if ( empty( $data['type'] ) )
            return '';

        $html = '';

        if ( !empty( $data['before_field'] ) ) {
            $html .= $data['before_field'];
        }

        if ( !empty( $data['before_field'] ) ) {
            $html .= $data['before_field'];
        }

        $value = !empty( $data['value'] ) ? $data['value'] : '';
        $data['id'] = !empty( $data['id'] ) ? $data['id'] : 'settings_id';
        $name = !empty( $data['name'] ) ? $data['name'] : 'wpc_settings[' . $data['id'] . ']';
        $data['class'] = !empty( $data['class'] ) ? 'wpc-option-field ' . $data['class'] : 'wpc-option-field';

        switch ( $data['type'] ) {
            case 'hidden':

                if ( empty( $data['is_option'] ) )
                    $html .= '<input type="hidden" id="' . $data['id'] . '" name="' . $data['id'] . '" value="' . $value . '" />';
                else
                    $html .= '<input type="hidden" id="wpc_settings_' . $data['id'] . '" name="' . $name . '" value="' . $value . '" class="' . $data['class'] . '" data-field_id="' . $data['id'] . '" />';

                return $html;
                break;

            case 'title':

                $html = '<h2>' . $data['label'] . '</h2>';

                if ( !empty( $data['description'] ) )
                    $html .= '<span class="description">' . $data['description'] . '</span><hr />';

                return $html;
                break;

            case 'text':
                $field_length = !empty( $data['size'] ) ? 'wpc-' . $data['size'] . '-field' : 'wpc-long-field';
                $placeholder = !empty( $data['placeholder'] ) ? 'placeholder="' .  $data['placeholder'] . '"' : '';

                $custom_attributes = array();
                if ( ! empty( $data['custom_attributes'] ) && is_array( $data['custom_attributes'] ) ) {
                    foreach ( $data['custom_attributes'] as $attribute => $attribute_value ) {
                        $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '" ';
                    }
                }

                $html .= '<input type="text" id="wpc_settings_' . $data['id'] . '" name="' . $name . '" value="' . $value . '" ' . $placeholder .
                    ' class="' . $data['class'] . ' ' . $field_length . '" data-field_id="' . $data['id'] . '" ' . implode( ' ', $custom_attributes ) . ' />';
                break;

            case 'number':
                $field_length = !empty( $data['size'] ) ? 'wpc-' . $data['size'] . '-field' : 'wpc-long-field';

                $custom_attributes = array();
                if ( ! empty( $data['custom_attributes'] ) && is_array( $data['custom_attributes'] ) ) {
                    foreach ( $data['custom_attributes'] as $attribute => $attribute_value ) {
                        $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '" ';
                    }
                }

                $html .= '<input type="number" id="wpc_settings_' . $data['id'] . '" name="' . $name . '" value="' . $value .
                    '" class="' . $data['class'] . ' ' . $field_length . '" data-field_id="' . $data['id'] . '" ' . implode( ' ', $custom_attributes ) . ' />';
                break;

            case 'multi-text':
                $html .= '<ul class="wpc-multi-text-list" data-field_id="' . $data['id'] . '">';

                if ( !empty( $values ) ) {
                    foreach ( $values as $k => $value ) {
                        $html .= '<li class="wpc-multi-text-option-line"><input type="text" id="wpc_settings_' . $data['id'] . '-' . $k . '" name="' . $name . '[]" value="' . $value . '" class="' . $data['class'] . '" data-field_id="' . $data['id'] . '" />
                            <a href="javascript:void(0);" class="wpc-option-delete">' . __( 'Remove', WPC_CLIENT_TEXT_DOMAIN ) . '</a></li>';
                    }
                }

                $html .= '</ul><a href="javascript:void(0);" class="button button-primary wpc-multi-text-add-option" data-name="' . $name . '[]">' . $data['add_text'] . '</a>';
                break;

            case 'textarea':
                $field_length = !empty( $data['size'] ) ? 'wpc-' . $data['size'] . '-field' : 'wpc-long-field';

                $custom_attributes = array();
                if ( ! empty( $data['custom_attributes'] ) && is_array( $data['custom_attributes'] ) ) {
                    foreach ( $data['custom_attributes'] as $attribute => $attribute_value ) {
                        $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '" ';
                    }
                }

                $html .= '<textarea id="wpc_settings_' . $data['id'] . '" name="' . $name . '" rows="6" class="' . $data['class'] . ' ' .
                    $field_length . '" data-field_id="' . $data['id'] . '" ' . implode( ' ', $custom_attributes ) . '>' . $value . '</textarea>';
                break;

            case 'wp_editor':

                ob_start();
                wp_editor( $value,
                    'wpc_settings_' . $data['id'],
                    array(
                        'textarea_name' => $name,
                        'textarea_rows' => 12,
                        'wpautop' => false,
                        'media_buttons' => false,
                        'editor_class' => $data['class']
                    )
                );

                $html .= ob_get_clean();

                //$html .= '<textarea id="wpc_settings_' . $data['id'] . '" name="wpc_settings[' . $data['id'] . ']" rows="6" class="' . $data['class'] . '" data-field_id="' . $data['id'] . '">' . $value . '</textarea>';
                break;

            case 'checkbox':
            case 'checkbox_list':
                $description = !empty( $data['description'] ) ? $data['description'] : '';
                $title = !empty( $data['title'] ) ? 'title="' . $data['title'] . '"' : '';
                $default_value = !empty( $data['default_value'] ) ? $data['default_value'] : 'yes';
                $always_send = isset( $data['always_send'] ) ? $data['always_send'] : true;
                $checked = isset( $data['checked'] ) ? $data['checked'] : checked( $value, 'yes', false );
                $data['description'] = ''; //clear it for do not display it below

                $custom_attributes = array();
                if ( ! empty( $data['custom_attributes'] ) && is_array( $data['custom_attributes'] ) ) {
                    foreach ( $data['custom_attributes'] as $attribute => $attribute_value ) {
                        $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '" ';
                    }
                }

                ob_start();

                if ( 'checkbox_list' == $data['type'] ) {
                    echo '<div class="wpc_settings_switch_block">';
                }

                ?>


                <label class="wpc_settings_switch" <?php echo $title ?> >

                    <?php if ( $always_send ) { ?>

                        <input type="hidden" id="wpc_settings_<?php echo $data['id'] ?>_hidden" name="<?php echo $name ?>" value="no" />

                    <?php } ?>

                    <input type="checkbox" <?php echo $checked ?>
                           id="wpc_settings_<?php echo $data['id'] ?>"
                           name="<?php echo $name ?>"
                           value="<?php echo $default_value ?>"
                           class="<?php echo $data['class'] ?>" data-field_id="<?php echo $data['id'] ?>"
                            <?php echo implode( ' ', $custom_attributes ) ?> />

                    <span class="wpc_settings_switch_slider wpc_settings_switch_slider_round"></span>

                </label>

                <?php if ( $description ) { ?>

                <label for="wpc_settings_<?php echo $data['id'] ?>" class="wpc_switch_description">
                    <?php echo $description ?>
                </label>

                <?php

                }

                if ( 'checkbox_list' == $data['type'] ) {
                    echo '</div>';
                }


                $html .= ob_get_clean();

                break;

            case 'multi-checkbox':
                $html .= '<input type="hidden" id="wpc_settings_' . $data['id'] . '_hidden" name="' . $name . '" value="" />';

                foreach ( $data['options'] as $key => $option ) {

                    $field_data = $data;
                    $field_data['type'] = 'checkbox_list';
                    $field_data['id'] = $data['id'] . '_' . $key;
                    $field_data['name'] = $name . '[]';
                    $field_data['default_value'] = $key;
                    $field_data['description'] = $option;
                    $field_data['always_send'] = false;
                    $field_data['checked'] = checked( is_array( $value ) && in_array( $key, $value ), true, false );

                    $html .= $this->render_setting_field( $field_data );
                }

                break;

            case 'selectbox':
                $html .= '<select ' . ( !empty( $data['multi'] ) ? 'multiple' : '' ) . ' id="wpc_settings_' . $data['id'] . '" name="' . $name . '" class="' . $data['class'] . '" data-field_id="' . $data['id'] . '">';
                foreach ( $data['options'] as $key => $option ) {
                    $html .= '<option value="' . $key . '" ' . selected( $key == $value, true, false ) . '>' . $option . '</option>';
                }
                $html .= '</select>';

                break;

            case 'multi-selectbox':
                $html .= '<select multiple id="wpc_settings_' . $data['id'] . '" name="' . $name . '[]" class="' . $data['class'] . '" data-field_id="' . $data['id'] . '">';
                $value = ( is_array( $value ) ) ? $value : array();

                if ( !empty( $data['optgroups'] ) ) {
                    foreach ( $data['optgroups'] as $group ) {
                        $html .= '<optgroup label="' . $group['label'] . '" ' . $group['attrs'] . ' >';

                        if ( !empty( $group['options'] ) ) {
                            foreach ( $group['options'] as $key => $option ) {
                                $selected = ( in_array( $key, $value ) ) ? 'selected' : '';
                                $html .= '<option value="' . $key . '" ' . $selected . '>' . $option . '</option>';
                            }
                        }

                        $html .= '</optgroup>';
                    }
                } elseif ( !empty( $data['options'] ) ) {
                    foreach ( $data['options'] as $key => $option ) {
                        $selected = ( in_array( $key, $value ) ) ? 'selected' : '';
                        $html .= '<option value="' . $key . '" ' . $selected . '>' . $option . '</option>';
                    }
                }

                $html .= '</select>';

                break;

            case 'media':
                $upload_frame_title = !empty( $data['upload_frame_title'] ) ? $data['upload_frame_title'] : __( 'Select media', WPC_CLIENT_TEXT_DOMAIN );

                $image_id = !empty( $value['id'] ) ? $value['id'] : '';
                $image_width = !empty( $value['width'] ) ? $value['width'] : '';
                $image_height = !empty( $value['height'] ) ? $value['height'] : '';
                $image_thumbnail = !empty( $value['thumbnail'] ) ? $value['thumbnail'] : '';
                $image_url = !empty( $value['url'] ) ? $value['url'] : '';

                $default = !empty( $data['default'] ) ? 'data-default="' . esc_attr( $data['default']['url'] ) . '"' : '';

                $html .= '<div class="wpc-media-upload">' .
                    '<input type="hidden" class="wpc-media-upload-data-id" name="' . $name . '[id]" id="wpc_settings_' . $data['id'] . '_id" value="' . $image_id . '">' .
                    '<input type="hidden" class="wpc-media-upload-data-width" name="' . $name . '[width]" id="wpc_settings_' . $data['id'] . '_width" value="' . $image_width . '">' .
                    '<input type="hidden" class="wpc-media-upload-data-height" name="' . $name . '[height]" id="wpc_settings_' . $data['id'] . '_height" value="' . $image_height . '">' .
                    '<input type="hidden" class="wpc-media-upload-data-thumbnail" name="' . $name . '[thumbnail]" id="wpc_settings_' . $data['id'] . '_thumbnail" value="' . $image_thumbnail . '">' .
                    '<input type="hidden" class="' . $data['class'] . ' wpc-media-upload-data-url" name="' . $name . '[url]" id="wpc_settings_' . $data['id'] . '_url" value="' . $image_url . '" data-field_id="' . $data['id'] . '" ' . $default . '>';

                if ( !isset( $data['preview'] ) || $data['preview'] !== false ) {
                    $html .= '<img src="' . ( !empty( $value['url'] ) ? $value['url'] : '' ) . '" alt="" class="icon_preview"><div style="clear:both;"></div>';
                }

                if ( !empty( $data['url'] ) ) {
                    $html .= '<input type="text" class="wpc-media-upload-url" readonly value="' . $image_url . '" /><div style="clear:both;"></div>';
                }

                $html .= '<input type="button" class="wpc-set-image button button-primary" value="' . __( 'Select', WPC_CLIENT_TEXT_DOMAIN ) . '" data-upload_frame="' . $upload_frame_title . '" />
            <input type="button" class="wpc-clear-image button" value="' . __( 'Clear', WPC_CLIENT_TEXT_DOMAIN ) . '" /></div>';
                break;

            case 'custom':
                $html .= !empty( $data['custom_html'] ) ? $data['custom_html'] : '';

                break;
        }

        if ( !empty( $data['after_field'] ) ) {
            $html .= $data['after_field'];
        }

        if ( !empty( $data['description'] ) && 'hidden' != $data['type'] )
            $html .= '<br /><span class="description">' . $data['description'] . '</span>';

        return $html;
    }

    /**
     * Get URL of current setting page
     *
     * @return string
     */
    function get_current_setting_url() {
        $current_tab = ( empty( $_GET['tab'] ) ) ? 'general' : urldecode( $_GET['tab'] );
        $current_subtab = !empty( $_GET['subtab'] ) ? urldecode( $_GET['subtab'] ) : '';

        $url = get_admin_url() . 'admin.php?page=wpclients_settings&tab=' . $current_tab;
        if ( $current_subtab ) {
            $url .= '&subtab=' . $current_subtab;
        }

        return $url;
    }

    /**
     * Update Setting
     *
     * @param mixed $value, string $key
     * @param string $key
     *
     * @return bool
     */
    function update( $value, $key ) {

        if ( empty( $key ) )
            return false;

        //reset cache
        if ( isset( WPC()->cache_settings[$key] ) )
            unset(  WPC()->cache_settings[$key] );

        update_option( 'wpc_' . $key, $value );


        /*our_hook_
           hook_name: wpc_client_setting_updated
           hook_title: Plugin setting was updated
           hook_description: Can be used for check new setting of plugin.
           hook_type: action
           hook_in: wp-client
           hook_location:
           hook_param: mixed $value, string $key
           hook_since: 4.5.0
       */
        do_action( 'wpc_client_setting_updated', $key, $value );

        /*our_hook_
           hook_name: wpc_client_setting_updated_{$column_name}
           hook_title: Plugin setting was updated
           hook_description: Can be used for check new setting of plugin.
           hook_type: action
           hook_in: wp-client
           hook_location:
           hook_param: mixed $value, string $key
           hook_since: 4.5.0
       */
        do_action( 'wpc_client_setting_updated_' . $key, $value );

        return true;
    }


}

endif;