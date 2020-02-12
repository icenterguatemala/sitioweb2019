<?php

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly


if ( ! class_exists( 'WPC_Captcha' ) ) :


class WPC_Captcha {
    private $publickey;
    private $privatekey;
    private $theme;
    private $settings;
    private $js = '';
    private $version = 0;


    /**
     * The single instance of the class.
     *
     * @var WPC_Captcha
     * @since 4.5
     */
    protected static $_instance = null;

    /**
     * Instance.
     *
     * Ensures only one instance of WPC_Captcha is loaded or can be loaded.
     *
     * @since 4.5
     * @static
     * @return WPC_Captcha - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    function __construct() {
        $this->settings = WPC()->get_settings( 'captcha' );
        if ( isset( $this->settings['enabled'] ) && 'yes' == $this->settings['enabled'] ) {
            if ( isset( $this->settings['version'] ) && 'recaptcha_3' == $this->settings['version'] ) {
                if ( ! empty( $this->settings['publickey_3'] ) && ! empty( $this->settings['privatekey_3'] ) ) {
                    $this->publickey  = $this->settings['publickey_3'];
                    $this->privatekey = $this->settings['privatekey_3'];
                    $this->version    = 3;
                    // include recaptcha_v3 in header
                    add_action( 'wp_footer', array( &$this, 'add_js_recaptcha_3' ) );
                    add_action( 'login_footer', array( &$this, 'add_js_recaptcha_3' ) );
                } else {
                    return;
                }
            } elseif ( isset( $this->settings['version'] ) && 'recaptcha_2' == $this->settings['version'] ) {
                if ( ! empty( $this->settings['publickey_2'] ) && ! empty( $this->settings['privatekey_2'] ) ) {
                    $this->publickey  = $this->settings['publickey_2'];
                    $this->privatekey = $this->settings['privatekey_2'];
                    $this->theme      = $this->settings['theme'];
                    $this->version    = 2;
                    add_action( 'wp_footer', array( &$this, 'add_js' ) );
                    add_action( 'login_footer', array( &$this, 'add_js' ) );
                } else {
                    return;
                }
            } else {
                if ( ! empty( $this->settings['publickey'] ) && ! empty( $this->settings['privatekey'] ) ) {
                    $this->publickey  = $this->settings['publickey'];
                    $this->privatekey = $this->settings['_privatekey'];
                } else {
                    $this->publickey  = "6LepaeMSAAAAAJppWl-CnHrjUntX25aXSmM1gqbx"; // you got this from the signup page
                    $this->privatekey = '6LepaeMSAAAAAO2oP2rq-CZ_e8kwZRgJ6i69v0Gd';
                }
                $this->version = 1;
            }
        }
    }


    function add_js() {
        ?>

        <script type="text/javascript">
            var CaptchaCallback = function() {

                var captchas = document.getElementsByClassName( "wpc_recaptcha_wrapper" );

                for ( var i = 0; i < captchas.length; i++ ) {
                    var data = captchas.item(i).dataset;

                    grecaptcha.render( captchas.item(i).id, {
                        'sitekey' : data.sitekey,
                        'theme'   : data.theme
                    });
                }
            };
        </script>

        <?php if ( empty( WPC()->flags['captcha_inited'] ) ) {

            WPC()->flags['captcha_inited'] = true; ?>

            <script src="//www.google.com/recaptcha/api.js?onload=CaptchaCallback&render=explicit" async defer></script>

        <?php }
    }


    function add_js_recaptcha_3(){
        $src = "https://www.google.com/recaptcha/api.js?render=$this->publickey"

        ?>
        <script src='<?php echo $src ?>'></script>
        <script>
            let captcha_action = 'login';

            grecaptcha.ready(function() {
                grecaptcha.execute('<?php echo $this->publickey ?>', {action: captcha_action})
                           .then(function(token) {
                    if (token) {
                        document.getElementById('token').value = token;
                        document.getElementById('action').value = captcha_action;
                    }
                });
            });
        </script>
        <?php
    }

    function generate() {
        if ( $this->version == 3 ) {
            return '<input type="hidden" name="token" id="token">
                    <input type="hidden" name="action" id="action">';

        }
        elseif ( $this->version == 2 ) {
            if ( !class_exists( '\WPC_Client\ReCaptcha' ) )
                include_once WPC()->plugin_dir . '/includes/libs/recaptchalib_2.php';

            $field_id = uniqid('wpc_recaptcha_');

            return '<div style="transform:scale(0.77);transform-origin:0 0" id="' . $field_id . '" class="wpc_recaptcha_wrapper" data-theme="' . $this->theme . '" data-sitekey="' . $this->publickey . '"></div>';
        } else {
            ?>
            <script type="text/javascript">
                var theme_name = '<?php echo !empty($this->settings['theme']) ? $this->settings['theme'] : 'red' ?>',
                    RecaptchaOptions = {
                        theme: theme_name
                    };
            </script>
            <?php
            if ( !function_exists( '_recaptcha_qsencode' ) )
                include WPC()->plugin_dir . '/includes/libs/recaptchalib.php';

            return recaptcha_get_html( $this->publickey, null, is_ssl() );
        }
    }


    function validate() {
        if ( $this->version == 1 ) {
            if ( !empty( $_POST["recaptcha_response_field"] ) ) {
                $resp = recaptcha_check_answer ( $this->privatekey, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"] );
                if ( !$resp->is_valid ) {
                    // What happens when the CAPTCHA was entered incorrectly
                    return new WP_Error( 'incorrect_captcha', __( "Sorry, incorrect Captcha!", WPC_CLIENT_TEXT_DOMAIN ) );
                }
            } else {
                return new WP_Error( 'required_captcha', __( "Captcha is required.", WPC_CLIENT_TEXT_DOMAIN ) );
            }
        } elseif ( $this->version == 3  ) {
                if ( ! empty( $_POST['token'] ) && ! empty( $_POST['action'] ) ) {

                    $url_captcha = 'https://www.google.com/recaptcha/api/siteverify';
                    $params      = array(
                        'secret'   => $this->privatekey,
                        'response' => $_POST['token'],
                        'remoteip' => $_SERVER['REMOTE_ADDR']
                    );
                    $return      = wp_remote_post( $url_captcha, array( 'body' => $params ) )['body'];
                    if ( empty( $return ) ) {
                        return new WP_Error( 'incorrect_captcha', __( "Sorry, incorrect Captcha!", WPC_CLIENT_TEXT_DOMAIN ) );
                    } else {
                        $decoded_response = json_decode( $return );
                        if ( $decoded_response && $decoded_response->success && $decoded_response->action == $_POST['action'] && $decoded_response->score > 0 ) {
                        } else {
                            return new WP_Error( 'incorrect_captcha', __( "Sorry, incorrect Captcha!", WPC_CLIENT_TEXT_DOMAIN ) );
                        }
                    }
                } else {
                    return new WP_Error( 'required_captcha', __( "Captcha is required.", WPC_CLIENT_TEXT_DOMAIN ) );
                }
        } else {
            $reCaptcha = new \WPC_Client\ReCaptcha($this->privatekey);
            // Was there a reCAPTCHA response?
            if ( !empty( $_POST["g-recaptcha-response"] ) ) {
                $resp = $reCaptcha->verifyResponse(
                    $_SERVER["REMOTE_ADDR"],
                    $_POST["g-recaptcha-response"]
                );
                if( !$resp->success ) {
                    return new WP_Error( 'incorrect_captcha', __( "Sorry, incorrect Captcha!", WPC_CLIENT_TEXT_DOMAIN ) );
                }
            } else {
                return new WP_Error( 'required_captcha', __( "Captcha is required.", WPC_CLIENT_TEXT_DOMAIN ) );
            }
        }
    }


    function check_login_tools() {
        if ( ! count( $_POST ) ) {
            return true;
        }

        $wpc_captcha = WPC()->get_settings( 'captcha' );
        if ( isset( $wpc_captcha['enabled'] ) && 'yes' == $wpc_captcha['enabled'] &&
             ! empty( $wpc_captcha['use_on'] ) && in_array( 'login', $wpc_captcha['use_on'] ) && WPC()->flags['login_form'] ) {
            if ( $this->version == 3 ) {
                if ( ! empty( $_POST['token'] ) && ! empty( $_POST['action'] ) ) {

                    $url_captcha = 'https://www.google.com/recaptcha/api/siteverify';
                    $params      = [
                        'secret'   => $wpc_captcha['privatekey_3'],
                        'response' => $_POST['token'],
                        'remoteip' => $_SERVER['REMOTE_ADDR']
                    ];
                    $return      = wp_remote_post( $url_captcha, array( 'body' => $params ) )['body'];
                    if ( empty( $return ) ) {
                        $url = WPC()->get_current_url( array( 'wpc_login_error' => 'captcha' ) );
                    } else {
                        $decoded_response = json_decode( $return );
                        if ( $decoded_response && $decoded_response->success && $decoded_response->action == $_POST['action'] && $decoded_response->score > 0 ) {
                        } else {
                            $url = WPC()->get_current_url( array( 'wpc_login_error' => 'captcha' ) );
                        }
                    }
                } else {
                    $url = WPC()->get_current_url( array( 'wpc_login_error' => 'captcha' ) );
                }
            } else {
                if ( isset( $_POST["g-recaptcha-response"] ) ) {

                    if ( ! class_exists( '\WPC_Client\ReCaptcha' ) ) {
                        include_once WPC()->plugin_dir . '/includes/libs/recaptchalib_2.php';
                    }

                    $privatekey = $wpc_captcha['privatekey_2'];
                    $reCaptcha  = new \WPC_Client\ReCaptcha( $privatekey );
                    // Was there a reCAPTCHA response?

                    $resp = $reCaptcha->verifyResponse(
                        $_SERVER["REMOTE_ADDR"],
                        $_POST["g-recaptcha-response"]
                    );

                    if ( $resp == null || ! $resp->success ) {
                        $url = WPC()->get_current_url( array( 'wpc_login_error' => 'captcha' ) );
                    }
                } else {
                    $url = WPC()->get_current_url( array( 'wpc_login_error' => 'captcha' ) );
                }
            }
        }


        /* terms */
        $wpc_terms = WPC()->get_settings( 'terms' );
        if ( isset( $wpc_terms['using_terms'] ) && 'yes' == $wpc_terms['using_terms'] &&
            !empty( $wpc_terms['using_terms_form'] ) && in_array( 'login', $wpc_terms['using_terms_form'] ) &&
            isset( $_REQUEST['terms_agree'] ) && $_REQUEST['terms_agree'] != '1' ) {

            $url = WPC()->get_current_url( array( 'wpc_login_error' => 'terms' ) );
        }

        /* privacy */
        $wpc_privacy = WPC()->get_settings( 'privacy' );
        if ( isset( $wpc_privacy['using_privacy'] ) && 'yes' == $wpc_privacy['using_privacy'] &&
            !empty( $wpc_privacy['using_privacy_form'] ) && in_array( 'login', $wpc_privacy['using_privacy_form'] ) &&
            isset( $_REQUEST['privacy_agree'] ) && $_REQUEST['privacy_agree'] != '1' ) {

            $url = WPC()->get_current_url( array( 'wpc_login_error' => 'privacy' ) );
        }


        if( !empty( $url ) ) {
            WPC()->redirect(  $url );
        }

        return '';
    }


    function init_captcha() {
        $wpc_captcha = WPC()->get_settings( 'captcha' );
        if( isset( $wpc_captcha['enabled'] ) && 'yes' == $wpc_captcha['enabled'] &&
            !empty( $wpc_captcha['use_on'] ) && in_array( 'login', $wpc_captcha['use_on'] ) ) {
            echo WPC()->captcha()->generate();
        }
    }



}

endif;