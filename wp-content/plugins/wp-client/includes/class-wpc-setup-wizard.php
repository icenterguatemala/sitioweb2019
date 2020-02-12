<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WPC_Setup_Wizard' ) ) :

class WPC_Setup_Wizard {

    /**
     *
     * @var string Current step
     */
    private $step = '';

    /**
     *
     * @var int id fo user
     */
    private $user_id;

    /**
     *
     * @var array Steps for the setup wizard
     */
    private $steps = array();

    public function __construct() {

        $this->user_id = get_current_user_id();
    }


    public function wpc_setup_wizard() {

        $this->steps = array(
            'start' => array(
                'name' => '<div style="width: 100%; text-align: center">'
                    . __('Welcome to WP-Client!', WPC_CLIENT_TEXT_DOMAIN) . '</div>',
                'view' => array($this, 'wpc_setup_start'),
            ),
            'business_information' => array(
                'name' => __('Business Information', WPC_CLIENT_TEXT_DOMAIN),
                'view' => array($this, 'wpc_business_information'),
            ),
            'file_sharing' => array(
                'name' => __('File Sharing', WPC_CLIENT_TEXT_DOMAIN),
                'view' => array($this, 'wpc_file_sharing'),
            ),
            'client_staff' => array(
                'name' => __('Client/Staff', WPC_CLIENT_TEXT_DOMAIN),
                'view' => array($this, 'wpc_client_staff'),
            ),
            'security' => array(
                'name' => __('Security', WPC_CLIENT_TEXT_DOMAIN),
                'view' => array($this, 'wpc_security'),
            ),
            'custom_titles' => array(
                'name' => __('Custom Titles', WPC_CLIENT_TEXT_DOMAIN),
                'view' => array($this, 'wpc_custom_titles'),
                'handler' => array($this, 'wpc_settings_save_finish'),
            ),
        );
        $this->step = isset($_GET['step']) ? sanitize_key($_GET['step']) : current(array_keys($this->steps));


        remove_all_actions('admin_footer');

        wp_enqueue_script('jquery');
        wp_enqueue_media();


        wp_enqueue_style('wpc-wizard_setup' );
        wp_enqueue_style( 'wp-client-style' );

        if (!empty($_POST['save_step'])) {
            if (!empty($this->steps[$this->step]['handler'])) {
                call_user_func($this->steps[$this->step]['handler'], $this->step);
            } else {
                $this->wpc_settings_save();
            }
        }

        ob_start();
        $this->setup_wizard_header();
        $this->setup_wizard_steps();
        $this->setup_wizard_content();
        $this->setup_wizard_footer();
        exit;
    }


    public function get_next_step_link() {
        $keys = array_keys($this->steps);
        return add_query_arg('step', $keys[array_search($this->step, array_keys($this->steps)) + 1]);
    }

    public function setup_wizard_header() {

        //fix to not break current_screen functions
        set_current_screen();

        ?>
        <!DOCTYPE html>
        <html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
            <head>
                <meta name="viewport" content="width=device-width" />
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <title><?php _e('WP-Client &rsaquo; Setup Wizard', WPC_CLIENT_TEXT_DOMAIN); ?></title>
                <?php do_action( 'admin_print_scripts' ); ?>
                <?php do_action('admin_print_styles'); ?>
                <?php do_action('admin_head'); ?>
            </head>
            <body>
                <div  id="wpc_body_setup">
                    <div id="wpc-logo">
                        <img src="<?php echo WPC()->plugin_url . 'images/setup_wizard.jpg' ?>" alt="WP-Client">
                    </div>
                    <div class="wpc_clear"></div>
        <?php
    }

    public function setup_wizard_footer() {
        ?>
                </div>
        <?php /*if ('next_steps' === $this->step) : ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=wpclients')); ?>"><?php _e('Return to the WP-Client Dashboard', WPC_CLIENT_TEXT_DOMAIN); ?></a>
        <?php endif;*/ ?>
            <?php
                do_action('admin_footer');
                do_action( 'admin_print_footer_scripts' );
            ?>
        </body>
        </html>
        <?php
    }

    /**
     * Output the steps
     */
    public function setup_wizard_steps() {
        $ouput_steps = $this->steps;
        array_shift($ouput_steps);
        ?>
        <ol class="wpc-wizard-steps">
            <?php foreach ($ouput_steps as $step_key => $step) { ?>
                <li class="<?php
                if ($step_key === $this->step) {
                    echo 'active';
                } elseif (array_search($this->step, array_keys($this->steps)) > array_search($step_key, array_keys($this->steps))) {
                    echo 'done';
                }
                ?>"><?php echo esc_html($step['name']); ?></li>
            <?php } ?>
        </ol>
        <?php
    }

    /**
     * Output the content for the current step
     */
    public function setup_wizard_content() {
        echo '<div id="wpc_content">';
        echo '<h1>' . $this->steps[$this->step]['name'] . '</h1>';
        call_user_func($this->steps[$this->step]['view']);
        echo '</div>';
    }

    /**
     * Introduction step
     */
    public function wpc_setup_start() {
        ?>
        <p><?php _e('Thank you for choosing WP-Client! This quick startup wizard will allow you to configure the basic settings and functions of the plugin. The whole process should only take a few minutes. You have the option to skip this setup wizard and go straight into the plugin menu using the "Skip" button below.', WPC_CLIENT_TEXT_DOMAIN); ?></p>
        <p class="wpc-setup-actions step">
            <a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button-primary button wpc_button button-next"><?php _e('Let\'s Go!', WPC_CLIENT_TEXT_DOMAIN); ?></a>
            <a href="<?php echo add_query_arg( array( 'page'=>'wpclients', 'tab' => 'get_started', 'wpc_setup_wizard_default_settings'=>'true' ), get_admin_url() . 'admin.php' ); ?>" class="button wpc_button"><?php _e('Skip', WPC_CLIENT_TEXT_DOMAIN); ?></a>
        </p>
        <?php
    }


    public function wpc_settings_save() {
        check_admin_referer('wpc-setup' . $this->user_id);

        if ( !empty( $_POST['wpc_settings'] ) && is_array( $_POST['wpc_settings'] )) {
            if ( isset( $_POST['wpc_settings']['wpc_file_sharing']['file_size_limit'] ) ) {
                $file_size_limit = (int)$_POST['wpc_settings']['wpc_file_sharing']['file_size_limit'];
                $_POST['wpc_settings']['wpc_file_sharing']['file_size_limit'] =
                        empty( $file_size_limit ) ? '' : $file_size_limit;
            }

            $name = 'wpc_temp_settings';
            $temp_settings = get_option( $name, array());
            foreach ( $_POST['wpc_settings'] as $key => $val ) {
                if ( isset( $temp_settings[ $key ] ) ) {
                    $val = array_merge( $temp_settings[ $key ], $val );
                }
                $temp_settings[ $key ] = $val;
            }

            update_option( $name, $temp_settings );
        }

        WPC()->redirect(esc_url_raw($this->get_next_step_link()));
    }


    public function wpc_settings_save_finish() {
        check_admin_referer('wpc-setup' . $this->user_id);

        if ( empty( $_POST['wpc_skip_finish_step'] ) && !empty( $_POST['wpc_settings'] ) && is_array( $_POST['wpc_settings'] )) {
            $key = 'wpc_temp_settings';
            $temp_settings = get_option( $key, array());
            $temp_settings = array_merge( $temp_settings, $_POST['wpc_settings'] );
            update_option( $key, $temp_settings );
        }

        //update default settings
        $key = 'wpc_temp_settings';
        $temp_settings = get_option( $key, array());

        //only for default currency
        $wpc_currency = get_option( 'wpc_currency', array() );
        $default = !empty( $temp_settings['wpc_currency_default'] )
                ? $temp_settings['wpc_currency_default'] : '';
        if ( !empty( $default )
                && in_array( $default, array_keys($wpc_currency) ) ) {
            foreach ( $wpc_currency as $key => $val ) {
                if ( !empty($val['default']) && 1 == $val['default'] ) {
                    $wpc_currency[ $key ]['default'] = 0;
                }
                if ( $key == $default ) {
                    $wpc_currency[ $key ]['default'] = 1;
                }
            }
            update_option( 'wpc_currency', $wpc_currency );

            unset($temp_settings['wpc_currency_default']);
        }

        foreach ( $temp_settings as $name => $val ) {
            $default_version = get_option( $name, array());
            $new_version = array_merge( $default_version, $val );
            update_option( $name, $new_version );
        }

        //for disable wizard setup
        update_option( 'wpc_wizard_setup', 'false' );

        //delete temp option
        delete_option( 'wpc_temp_settings' );
        WPC()->redirect( add_query_arg( array( 'page' => 'wpclients', 'tab' => 'get_started' ), 'admin.php' ));
    }


    /**
     * Business Information
     */
    public function wpc_business_information() {
        if ( !empty( $_POST['wpc_settings']['wpc_business_info'] ) ) {
            $wpc_business_info = $_POST['wpc_settings']['wpc_business_info'];
        } elseif (( $data = get_option( 'wpc_temp_settings', array() ) )
                && !empty( $data['wpc_business_info'] ) ) {
            $wpc_business_info = $data['wpc_business_info'];
        } else {
            $wpc_business_info = WPC()->get_settings( 'business_info' );
        }

        ?>
        <form action="" method="post" name="wpc_settings" id="wpc_settings" >

            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="wpc_business_info_business_logo_url">
                            <?php _e( 'Logo URL', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                        <!--span class="wpc_description">{business_logo_url}</span-->
                    </th>
                    <td>
                        <input type="text" name="wpc_settings[wpc_business_info][business_logo_url]" id="wpc_business_info_business_logo_url" value="<?php echo ( isset( $wpc_business_info['business_logo_url'] ) ) ? $wpc_business_info['business_logo_url'] : '' ?>" />
                        <input id="upload_image_button" class="button" type="button" value="<?php _e( 'Select Image', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="wpc_business_info_business_name"><?php _e( 'Official Business Name', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                        <!--span class="wpc_description">{business_name}</span-->
                    </th>
                    <td>
                        <input type="text" name="wpc_settings[wpc_business_info][business_name]" id="wpc_business_info_business_name" value="<?php echo ( isset( $wpc_business_info['business_name'] ) ) ? $wpc_business_info['business_name'] : '' ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="wpc_business_info_business_address"><?php _e( 'Business Address', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                        <!--span class="wpc_description">{business_address}</span-->
                    </th>
                    <td>
                        <textarea cols="50" rows="3" name="wpc_settings[wpc_business_info][business_address]" id="wpc_business_info_business_address" ><?php echo ( isset( $wpc_business_info['business_address'] ) ) ? $wpc_business_info['business_address'] : '' ?></textarea>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="wpc_business_info_business_website"><?php _e( 'Website', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                        <!--span class="wpc_description">{business_website}</span-->
                    </th>
                    <td>
                        <input type="text" name="wpc_settings[wpc_business_info][business_website]" id="wpc_business_info_business_website" value="<?php echo ( isset( $wpc_business_info['business_website'] ) ) ? $wpc_business_info['business_website'] : '' ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="wpc_business_info_business_email"><?php _e( 'Email', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                        <!--span class="wpc_description">{business_email}</span-->
                    </th>
                    <td>
                        <input type="text" name="wpc_settings[wpc_business_info][business_email]" id="wpc_business_info_business_email" value="<?php echo ( isset( $wpc_business_info['business_email'] ) ) ? $wpc_business_info['business_email'] : '' ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="wpc_business_info_business_phone"><?php _e( 'Phone', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                        <!--span class="wpc_description">{business_phone}</span-->
                    </th>
                    <td>
                        <input type="text" name="wpc_settings[wpc_business_info][business_phone]" id="wpc_business_info_business_phone" value="<?php echo ( isset( $wpc_business_info['business_phone'] ) ) ? $wpc_business_info['business_phone'] : '' ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="wpc_business_info_business_fax"><?php _e( 'Fax', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                        <!--span class="wpc_description">{business_fax}</span-->
                    </th>
                    <td>
                        <input type="text" name="wpc_settings[wpc_business_info][business_fax]" id="wpc_business_info_business_fax" value="<?php echo ( isset( $wpc_business_info['business_fax'] ) ) ? $wpc_business_info['business_fax'] : '' ?>" />
                    </td>
                </tr>
            </table>

            <p>
                <?php
                    printf( __( 'You can always change these settings via <b>%s&nbsp;>&nbsp;%s</b>', WPC_CLIENT_TEXT_DOMAIN )
                            , __( 'Settings', WPC_CLIENT_TEXT_DOMAIN )
                            , __( 'Business Info', WPC_CLIENT_TEXT_DOMAIN )
                    );

                    $choose_image = __( 'Choose Image', WPC_CLIENT_TEXT_DOMAIN );
                ?>
            </p>
            <p class="wpc-setup-actions step">
                <input type="submit" class="button-primary button wpc_button button-next" value="<?php esc_attr_e('Continue', WPC_CLIENT_TEXT_DOMAIN); ?>" name="save_step" />
                <a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button wpc_button button-next"><?php _e('Skip this step', WPC_CLIENT_TEXT_DOMAIN); ?></a>
                <?php wp_nonce_field('wpc-setup' . $this->user_id ); ?>
            </p>
        </form>
        <script type="text/javascript">
            var custom_uploader;

            jQuery('#upload_image_button').on( 'click', function(e) {
                e.preventDefault();

                //If the uploader object has already been created, reopen the dialogue
                if (custom_uploader) {
                    custom_uploader.open();
                    return;
                }

                //Extend the wp.media object
                custom_uploader = wp.media({
                    title: '<?php echo esc_js( $choose_image ); ?>',
                    button: {
                        text: '<?php echo esc_js( $choose_image ); ?>'
                    },
                    multiple: true
                });

                //When a file is selected, grab the URL and set it as the text field's value
                custom_uploader.on('select', function() {
                    attachment = custom_uploader.state().get('selection').first().toJSON();
                    jQuery('#wpc_business_info_business_logo_url').val(attachment.url);
                });

                //Open the uploader dialogue
                custom_uploader.open();

            });
        </script>
        <?php
    }


    /**
     * File Sharing
     */
    public function wpc_file_sharing() {
        if ( !empty( $_POST['wpc_settings']['wpc_file_sharing'] ) ) {
            $wpc_file_sharing = $_POST['wpc_settings']['wpc_file_sharing'];
        } elseif (( $data = get_option( 'wpc_temp_settings', array() ) )
                && !empty( $data['wpc_file_sharing'] ) ) {
            $wpc_file_sharing = $data['wpc_file_sharing'];
        } else {
            $wpc_file_sharing = WPC()->get_settings( 'file_sharing' );
        }

        ?>

<form action="" method="post" name="wpc_settings" id="wpc_settings" >

    <table class="form-table">

        <tr valign="top">
            <th scope="row">
                <label for="wpc_file_sharing_admin_uploader_type"><?php _e( 'Uploader in Admin area', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
            </th>
            <td>
                <select name="wpc_settings[wpc_file_sharing][admin_uploader_type]" id="wpc_file_sharing_admin_uploader_type">
                    <option value="regular" <?php echo ( isset( $wpc_file_sharing['admin_uploader_type'] ) && 'regular' == $wpc_file_sharing['admin_uploader_type'] ) ? 'selected' : '' ?> ><?php _e( 'Regular', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                    <option value="html5" <?php echo ( isset( $wpc_file_sharing['admin_uploader_type'] ) && 'html5' == $wpc_file_sharing['admin_uploader_type'] ) ? 'selected' : '' ?> ><?php _e( 'HTML5', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                    <option value="plupload" <?php echo ( isset( $wpc_file_sharing['admin_uploader_type'] ) && 'plupload' == $wpc_file_sharing['admin_uploader_type'] ) ? 'selected' : '' ?> ><?php _e( 'uberLOADER', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                </select>
                <input type="hidden" id="wpc_descr_regular" value="<?php _e( 'Standard browser upload form', WPC_CLIENT_TEXT_DOMAIN ) ?>">
                <input type="hidden" id="wpc_descr_html5" value="<?php _e( 'Uploader with progress bar, multiple files uploading', WPC_CLIENT_TEXT_DOMAIN ) ?>">
                <input type="hidden" id="wpc_descr_plupload" value="<?php _e( 'Uploader with progress bar, multiple files uploading, chunking upload for big files', WPC_CLIENT_TEXT_DOMAIN ) ?>">
                <span class="wpc_description" id="wpc_uplader_admin_descr"></span>
                <div id="wpc_uplader_admin_image"></div>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="wpc_file_sharing_client_uploader_type"><?php _e( 'Uploader in Client area', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
            </th>
            <td>
                <select name="wpc_settings[wpc_file_sharing][client_uploader_type]" id="wpc_file_sharing_client_uploader_type">
                    <option value="regular" <?php echo ( isset( $wpc_file_sharing['client_uploader_type'] ) && 'regular' == $wpc_file_sharing['client_uploader_type'] ) ? 'selected' : '' ?> ><?php _e( 'Regular', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                    <option value="html5" <?php echo ( isset( $wpc_file_sharing['client_uploader_type'] ) && 'html5' == $wpc_file_sharing['client_uploader_type'] ) ? 'selected' : '' ?> ><?php _e( 'HTML5', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                    <option value="plupload" <?php echo ( isset( $wpc_file_sharing['client_uploader_type'] ) && 'plupload' == $wpc_file_sharing['client_uploader_type'] ) ? 'selected' : '' ?> ><?php _e( 'uberLOADER', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                </select>
                <span class="wpc_description" id="wpc_uplader_client_descr"></span>
                <div id="wpc_uplader_client_image"></div>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="wpc_file_sharing_file_size_limit"><?php _e( 'Max File Size For Upload (Kb)', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
            </th>
            <td>
                <input type="text" name="wpc_settings[wpc_file_sharing][file_size_limit]" id="wpc_file_sharing_file_size_limit" value="<?php echo ( isset( $wpc_file_sharing['file_size_limit'] ) ) ? $wpc_file_sharing['file_size_limit'] : '' ?>" />
                <span class="wpc_description"><?php _e( 'Remember: 1M = 1024Kb', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                <span class="wpc_description">
                    <?php _e( 'Value must be numeric!', WPC_CLIENT_TEXT_DOMAIN ) ?>
                    <?php _e( 'Leave blank to allow unlimited file size.<br>NOTE: This setting does not change your server settings. You should change your server settings if you are experiencing issues.<br>Your server settings are:', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                <?php
                echo '<span class="wpc_description"><b>' . __ ( 'upload_max_filesize', WPC_CLIENT_TEXT_DOMAIN )
                        . '</b> = ' . ini_get( 'upload_max_filesize' ) . '</span>';
                echo '<span class="wpc_description"><b>' . __ ( 'post_max_size', WPC_CLIENT_TEXT_DOMAIN )
                        . '</b> = ' . ini_get( 'post_max_size' ) . '</span>';
                 ?>
            </td>
        </tr>

    </table>

    <p>
        <?php
            printf( __( 'You can always change these settings via <b>%s&nbsp;>&nbsp;%s</b>', WPC_CLIENT_TEXT_DOMAIN )
                    , __( 'Settings', WPC_CLIENT_TEXT_DOMAIN )
                    , __( 'File Sharing', WPC_CLIENT_TEXT_DOMAIN )
            );
        ?>
    </p>

    <p class="wpc-setup-actions step">
        <input type="submit" class="button-primary button wpc_button button-next" value="<?php esc_attr_e('Continue', WPC_CLIENT_TEXT_DOMAIN); ?>" name="save_step" />
        <a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button wpc_button button-next"><?php _e('Skip this step', WPC_CLIENT_TEXT_DOMAIN); ?></a>
        <?php wp_nonce_field('wpc-setup' . $this->user_id ); ?>
    </p>

</form>

<script type="text/javascript">
    jQuery(document).ready( function() {

        jQuery('#wpc_file_sharing_admin_uploader_type').change( function() {
            var val = jQuery( this ).val();
            jQuery('#wpc_uplader_admin_descr').html( jQuery('#wpc_descr_' + val ).val() );
            jQuery('#wpc_uplader_admin_image').html( '<img src="<?php echo WPC()->plugin_url . 'images/setup_wizard/'?>' + val + '.png">' );
        });

        jQuery('#wpc_file_sharing_client_uploader_type').on( 'change', function() {
            //return true;
            var val = jQuery( this ).val();
            jQuery('#wpc_uplader_client_descr').html( jQuery('#wpc_descr_' + val ).val() );
            jQuery('#wpc_uplader_client_image').html( '<img src="<?php echo WPC()->plugin_url . 'images/setup_wizard/'?>' + val + '.png">' );
        });

        jQuery('#wpc_file_sharing_admin_uploader_type').trigger('change');
        jQuery('#wpc_file_sharing_client_uploader_type').trigger('change');
    });
</script>

        <?php
    }


    /**
     * Client/Staff
     */
    public function wpc_client_staff() {
        if ( !empty( $_POST['wpc_settings']['wpc_clients_staff'] ) ) {
            $wpc_clients_staff = $_POST['wpc_settings']['wpc_clients_staff'];
        } elseif (( $data = get_option( 'wpc_temp_settings', array() ) )
                && !empty( $data['wpc_clients_staff'] ) ) {
            $wpc_clients_staff = $data['wpc_clients_staff'];
        } else {
            $wpc_clients_staff = WPC()->get_settings( 'clients_staff' );
        }

        $wpc_currency = WPC()->get_settings( 'currency' );

        if ( !empty( $_POST['wpc_settings']['wpc_currency_default'] ) ) {
            $wpc_currency_default = $_POST['wpc_settings']['wpc_currency_default'];
        } elseif (( $data = get_option( 'wpc_temp_settings', array() ) )
                && !empty( $data['wpc_currency_default'] ) ) {
            $wpc_currency_default = $data['wpc_currency_default'];
        }

        ?>

<form action="" method="post" name="wpc_settings" id="wpc_settings" >

    <table class="form-table">

        <tr valign="top">
            <th scope="row">
                <label for="wpc_clients_staff_client_registration"><?php _e( 'Open Client Registration', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
            </th>
            <td>
                <select name="wpc_settings[wpc_clients_staff][client_registration]" id="wpc_clients_staff_client_registration">
                    <option value="yes" <?php echo ( isset( $wpc_clients_staff['client_registration'] ) && 'yes' == $wpc_clients_staff['client_registration'] ) ? 'selected' : '' ?> ><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                    <option value="no" <?php echo ( isset( $wpc_clients_staff['client_registration'] ) && 'no' == $wpc_clients_staff['client_registration'] ) ? 'selected' : '' ?> ><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                </select>
                <span class="wpc_description"><?php _e( 'Allows Clients to self-register using Client Registration Form. By default, self-registered Clients require Admin approval before their account is active.', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="wpc_clients_staff_staff_registration"><?php _e( 'Open Staff Registration', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
            </th>
            <td>
                <select name="wpc_settings[wpc_clients_staff][staff_registration]" id="wpc_clients_staff_staff_registration">
                    <option value="yes" <?php echo ( isset( $wpc_clients_staff['staff_registration'] ) && 'yes' == $wpc_clients_staff['staff_registration'] ) ? 'selected' : '' ?> ><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                    <option value="no" <?php echo ( isset( $wpc_clients_staff['staff_registration'] ) && 'no' == $wpc_clients_staff['staff_registration'] ) ? 'selected' : '' ?> ><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                </select>
                <span class="wpc_description"><?php _e( 'Allows Client to register their own Staff users. By default, Staff users require Admin approval before their account is active.', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="wpc_clients_staff_lost_password"><?php _e( 'Allow "Lost your password"', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
            </th>
            <td>
                <select name="wpc_settings[wpc_clients_staff][lost_password]" id="wpc_clients_staff_lost_password">
                    <option value="no" <?php echo ( isset( $wpc_clients_staff['lost_password'] ) && 'no' == $wpc_clients_staff['lost_password'] ) ? 'selected' : '' ?> ><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                    <option value="yes" <?php echo ( isset( $wpc_clients_staff['lost_password'] ) && 'yes' == $wpc_clients_staff['lost_password'] ) ? 'selected' : '' ?> ><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                </select>
                <span class="wpc_description"><?php _e( 'Displays "Lost your password" link on login form.', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="wpc_settings_wpc_currency_default"><?php _e( 'Currency Settings', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
            </th>
            <td>

                <select name="wpc_settings[wpc_currency_default]" id="wpc_settings_wpc_currency_default">
                    <?php foreach( $wpc_currency as $key => $val ) {
                            if ( !empty($wpc_currency_default) ) {
                                $selected = selected($wpc_currency_default, $key, false);
                            } else {
                                $selected = selected($val['default'], 1);
                            }

                            echo '<option value="' . $key . '" ' . $selected . '>' . $val['symbol'] . ' - ' . $val['code']
                                . ' (' . ucfirst( $val['align'] ) . ')</option>';
                        }
                    ?>
                </select>
            </td>
        </tr>

    </table>

    <p>
        <?php
            printf( __( 'You can always change these settings via <b>%s&nbsp;>&nbsp;%s</b> and <b>%1$s&nbsp;>&nbsp;%s</b>', WPC_CLIENT_TEXT_DOMAIN )
                    , __( 'Settings', WPC_CLIENT_TEXT_DOMAIN )
                    , __( 'Client/Staff', WPC_CLIENT_TEXT_DOMAIN )
                    , __( 'General', WPC_CLIENT_TEXT_DOMAIN )
            );
        ?>
    </p>

    <p class="wpc-setup-actions step">
        <input type="submit" class="button-primary button wpc_button button-next" value="<?php esc_attr_e('Continue', WPC_CLIENT_TEXT_DOMAIN); ?>" name="save_step" />
        <a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button wpc_button button-next"><?php _e('Skip this step', WPC_CLIENT_TEXT_DOMAIN); ?></a>
        <?php wp_nonce_field('wpc-setup' . $this->user_id ); ?>
    </p>

</form>
        <?php
    }


    /**
     * Security
     */
    public function wpc_security() {
        if ( !empty( $_POST['wpc_settings']['wpc_clients_staff'] ) ) {
            $wpc_clients_staff = $_POST['wpc_settings']['wpc_clients_staff'];
        } elseif (( $data = get_option( 'wpc_temp_settings', array() ) )
                && !empty( $data['wpc_clients_staff'] ) ) {
            $wpc_clients_staff = $data['wpc_clients_staff'];
        } else {
            $wpc_clients_staff = WPC()->get_settings( 'clients_staff' );
        }

        if ( !empty( $_POST['wpc_settings']['wpc_common_secure'] ) ) {
            $wpc_common_secure = $_POST['wpc_settings']['wpc_common_secure'];
        } elseif (( $data = get_option( 'wpc_temp_settings', array() ) )
                && !empty( $data['wpc_common_secure'] ) ) {
            $wpc_common_secure = $data['wpc_common_secure'];
        } else {
            $wpc_common_secure = WPC()->get_settings( 'common_secure' );
        }


        ?>

<form action="" method="post" name="wpc_settings" id="wpc_settings" >

    <table class="form-table">

        <tr valign="top">
            <th scope="row">
                <label for="wpc_clients_staff_hide_dashboard"><?php _e( 'Hide dashboard/backend', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
            </th>
            <td>
                <select name="wpc_settings[wpc_clients_staff][hide_dashboard]" id="wpc_clients_staff_hide_dashboard">
                    <option value="yes" <?php echo ( isset( $wpc_clients_staff['hide_dashboard'] ) && 'yes' == $wpc_clients_staff['hide_dashboard'] ) ? 'selected' : '' ?> ><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                    <option value="no" <?php echo ( !isset( $wpc_clients_staff['hide_dashboard'] ) || 'no' == $wpc_clients_staff['hide_dashboard'] ) ? 'selected' : '' ?> ><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                </select>
                <span class="wpc_description"><?php _e( 'Hides WordPress admin dashboard/backend from Clients and Staff.', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="wpc_clients_staff_hide_admin_bar"><?php _e( 'Hide Admin Bar', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
            </th>
            <td>
                <select name="wpc_settings[wpc_clients_staff][hide_admin_bar]" id="wpc_clients_staff_hide_admin_bar">
                    <option value="yes" <?php echo ( !isset( $wpc_clients_staff['hide_admin_bar'] ) || 'yes' == $wpc_clients_staff['hide_admin_bar'] ) ? 'selected' : '' ?> ><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                    <option value="no" <?php echo ( isset( $wpc_clients_staff['hide_admin_bar'] ) && 'no' == $wpc_clients_staff['hide_admin_bar'] ) ? 'selected' : '' ?> ><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                </select>
                <span class="wpc_description"><?php _e( 'Hides top WordPress Admin Bar from Clients and Staff.', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="hide_site"><?php _e( 'Hide Site for not logged in Users', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
            </th>
            <td>
                <select name="wpc_settings[wpc_common_secure][hide_site]" id="hide_site">
                    <option value="yes" <?php echo ( isset( $wpc_common_secure['hide_site'] ) && 'yes' == $wpc_common_secure['hide_site'] ) ? 'selected' : '' ?> ><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                    <option value="no" <?php echo ( !isset( $wpc_common_secure['hide_site'] ) || 'yes' != $wpc_common_secure['hide_site'] ) ? 'selected' : '' ?> ><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                </select>
                <span class="wpc_description">
                    <?php _e( 'Non-logged in site visitors will be automatically redirected to login page', WPC_CLIENT_TEXT_DOMAIN ) ?>
                </span>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="hide_admin"><?php _e( 'Hide WP Admin', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
            </th>
            <td>
                <select name="wpc_settings[wpc_common_secure][hide_admin]" id="hide_admin">
                    <option value="yes" <?php echo ( isset( $wpc_common_secure['hide_admin'] ) && 'yes' == $wpc_common_secure['hide_admin'] ) ? 'selected' : '' ?> ><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                    <option value="no" <?php echo ( isset( $wpc_common_secure['hide_admin'] ) && 'no' == $wpc_common_secure['hide_admin'] ) ? 'selected' : '' ?> ><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                </select>
                <span class="wpc_description">
                    <?php _e( 'Non-logged in users will receive a 404 when they try to access /wp-admin', WPC_CLIENT_TEXT_DOMAIN ) ?>
                </span>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="login_url"><?php _e( 'Custom Login URL', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
            </th>
            <td>
                <label for="login_url"><b><?php echo home_url() . '/'; ?></b>&nbsp;&nbsp;</label>
                <input type="text" name="wpc_settings[wpc_common_secure][login_url]" id="login_url" value="<?php echo ( isset( $wpc_common_secure['login_url'] ) ) ? $wpc_common_secure['login_url'] : '' ?>" style="width: 150px;" />
                <span class="wpc_description">
                    <?php echo __( 'This will change it from ', WPC_CLIENT_TEXT_DOMAIN ) . '<b>' . wp_guess_url() . '/wp-login.php' . '</b>' .
                        __( ' to whatever you put in this box ', WPC_CLIENT_TEXT_DOMAIN ) . '. <br />' .
                        __('Say if you put "login" into the box, your new login URL will be ' , WPC_CLIENT_TEXT_DOMAIN ) . home_url() . '/login/.'
                    ?>
                </span>
            </td>
        </tr>
    </table>

    <p>
        <?php
            printf( __( 'You can always change these settings via <b>%s&nbsp;>&nbsp;%s</b> and <b>%1$s&nbsp;>&nbsp;%s</b>', WPC_CLIENT_TEXT_DOMAIN )
                    , __( 'Settings', WPC_CLIENT_TEXT_DOMAIN )
                    , __( 'Client/Staff', WPC_CLIENT_TEXT_DOMAIN )
                    , __( 'Custom Login', WPC_CLIENT_TEXT_DOMAIN )
            );
        ?>
    </p>

    <p class="wpc-setup-actions step">
        <input type="submit" class="button-primary button wpc_button button-next" value="<?php esc_attr_e('Continue', WPC_CLIENT_TEXT_DOMAIN); ?>" name="save_step" />
        <a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button wpc_button button-next"><?php _e('Skip this step', WPC_CLIENT_TEXT_DOMAIN); ?></a>
        <?php wp_nonce_field('wpc-setup' . $this->user_id ); ?>
    </p>

</form>
        <?php
    }


    /**
     * Custom Titles
     */
    public function wpc_custom_titles() {
        if ( !empty( $_POST['wpc_settings']['wpc_custom_titles'] ) ) {
            $settings = $_POST['wpc_settings']['wpc_custom_titles'];
        } elseif (( $data = get_option( 'wpc_temp_settings', array() ) )
                && !empty( $data['wpc_custom_titles'] ) ) {
            $settings = $data['wpc_custom_titles'];
        } else {
            $settings = WPC()->get_settings( 'custom_titles' );
        }

        $all_custom_titles = ( is_array( $settings ) ) ? array_merge( WPC()->custom_titles, $settings ) : WPC()->custom_titles;

        ?>
        <form action="" method="post" name="wpc_settings" id="wpc_settings" >

            <span class="wpc_description"><?php _e( "Use the fields below to change the default text that is used for various aspects of the plugin, such as user role titles.", WPC_CLIENT_TEXT_DOMAIN ) ?></span>
            <hr />

            <table class="form-table">
            <?php foreach( $all_custom_titles as $key => $values ) { ?>
                <tr valign="top">

                    <th scope="row">
                        <b><?php echo ucwords( str_replace( array('_'), ' ', $key ) ) ?></b>
                    </th>
                    <td>&nbsp;</td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="<?php echo $key ?>_s"><span class="wpc_description"><?php _e( 'Singular', WPC_CLIENT_TEXT_DOMAIN ) ?></span></label>
                    </th>
                    <td>
                        <input type="text" name="wpc_settings[wpc_custom_titles][<?php echo $key ?>][s]" id="<?php echo $key ?>_s" value="<?php echo $values['s'] ?>" />
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">
                        <label for="<?php echo $key ?>_p"><span class="wpc_description"><?php _e( 'Plural', WPC_CLIENT_TEXT_DOMAIN ) ?></span></label>
                    </th>
                    <td>
                        <input type="text" name="wpc_settings[wpc_custom_titles][<?php echo $key ?>][p]" id="<?php echo $key ?>_p" value="<?php echo $values['p'] ?>" />
                    </td>
                </tr>
            <?php } ?>
            </table>

            <p>
                <?php
                    printf( __( 'You can always change these settings via <b>%s&nbsp;>&nbsp;%s</b>', WPC_CLIENT_TEXT_DOMAIN )
                            , __( 'Settings', WPC_CLIENT_TEXT_DOMAIN )
                            , __( 'Custom Titles', WPC_CLIENT_TEXT_DOMAIN )
                    );
                ?>
            </p>

            <p class="wpc-setup-actions step">
                <input type="submit" id="wpc_save_step" class="button-primary button wpc_button button-next" value="<?php esc_attr_e('Finish', WPC_CLIENT_TEXT_DOMAIN); ?>" name="save_step" />
                <input type="hidden" value="" name="wpc_skip_finish_step" id="wpc_skip">
                <a id="wpc_skip_finish_step" class="button wpc_button button-next" id="wpc_wizard_finish_skip_step"><?php _e('Skip this step', WPC_CLIENT_TEXT_DOMAIN); ?></a>
                <?php wp_nonce_field('wpc-setup' . $this->user_id ); ?>
            </p>
        </form>
        <script type="text/javascript">

            jQuery(document).ready( function() {
                jQuery('#wpc_skip_finish_step').click( function() {
                    jQuery('#wpc_skip').val('yes');
                    jQuery('#wpc_save_step').trigger('click');
                });
            });
        </script>

        <?php
    }

}

endif;