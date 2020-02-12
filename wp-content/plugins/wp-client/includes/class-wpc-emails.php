<?php

/**
 * Email Class
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPC_Emails' ) ) :

/**
 * WPC_Emails
 */
class WPC_Emails {

    /**
     * Core settings for the email.
     *
     * @var array
     */
    public $core_settings;

    /**
     * All email profiles
     *
     * @var array
     */
    public $email_profiles;

    /**
     * settings for the email.
     *
     * @var array
     */
    public $settings;

    /**
     * mail_sender for the email.
     *
     * @var array
     */
    public $mail_sender;

    /**
     * Email method title.
     *
     * @var string
     */
    public $test_email_sender = false;

    /**
     * List of sending methods.
     *
     * @var array
     */
    public $senders = array();

    /**
     */
    public static $_instance;

    /**
     */
    public $sender_objects = array();


    /**
     * Instance.
     *
     * Ensures only one instance of WPC_Emails is loaded or can be loaded.
     *
     * @since 4.5
     * @static
     * @return WPC_Emails - Main instance.
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
        $profile_id = WPC()->get_settings( 'email_sending_profile_for_core' );

        $this->email_profiles = WPC()->get_settings( 'email_sending_profiles' );

        $settings = !empty( $profile_id ) && isset( $this->email_profiles[ $profile_id ] )
                ? $this->email_profiles[ $profile_id ] : array();

        $this->core_settings = $settings;
        $this->set_sender_settings( $settings );

        //add smtp server if its enable
        add_action( 'phpmailer_init', array( &$this, 'phpmailer_init' ), 99 );


    }


    /**
     * Reset sender settings.
     */
    function reset_sender_settings() {
        $this->settings = $this->core_settings;
    }

    /**
     * Set sender settings.
     *
     * @param array $data
     */
    function set_sender_settings( $data ) {
        $this->settings = $data;
    }

	/**
	 * Get from name for email.
	 *
	 * @return string
	 */
	public function get_from_name() {
		return !empty( $this->settings['sender_name'] )
                    ? wp_specialchars_decode( esc_html( $this->settings['sender_name'] ), ENT_QUOTES ) : '';
	}

	/**
	 * Get from email address.
	 *
	 * @return string
	 */
	public function get_from_address( $email ) {
		return !empty( $this->settings['sender_email'] )
                    ? sanitize_email( $this->settings['sender_email'] ) : $email;
	}

    /**
     * Prepare sending settings.
     *
     * @return array or string if error
     */
    function prepare_profile_settings( $settings ) {

        $type = isset( $settings['type'] ) ? $settings['type'] : '';

        $prepared = array();

        $prepared['sender_name'] = !empty( $settings['sender_name'] )
                ? $settings['sender_name'] : '';

        if ( empty( $settings['item_id'] ) ) {
            return __( 'Profile Id is Empty', WPC_CLIENT_TEXT_DOMAIN);
        } else {
            $item_id = $settings['item_id'];
        }

        if ( empty( $settings['profile_name'] ) ) {
            return __( 'Profile Name is Required', WPC_CLIENT_TEXT_DOMAIN);
        } else {
            $prepared['profile_name'] = $settings['profile_name'];
        }

        if ( !empty( $settings['sender_email'] ) && !is_email( $settings['sender_email'] ) ) {
            return __( 'Invalid Sender Email', WPC_CLIENT_TEXT_DOMAIN);
        } else {
            $prepared['sender_email'] = !empty( $settings['sender_email'] )
                ? $settings['sender_email'] : '';
        }

        if ( !empty( $settings['reply_email'] ) && !is_email( $settings['reply_email'] ) ) {
            return __( 'Invalid Reply Email', WPC_CLIENT_TEXT_DOMAIN);
        } else {
            $prepared['reply_email'] = !empty( $settings['reply_email'] )
                    ? $settings['reply_email'] : '';
        }

        if( isset( $settings[ $type ] ) ) {
            $old_data = isset( $this->email_profiles[ $item_id ][ $type ] )
                    ? $this->email_profiles[ $item_id ][ $type ]
                    : array();
            $data = $settings[ $type ];
            if( 'smtp' == $type ) {

                if ( empty( $data['password'] )
                    && !empty( $old_data['password'] ) ) {

                    $data['password'] = $old_data['password'];
                    $encripted = true;
                }

                if ( empty( $data['username'] ) ) {
                    $data['error'] = __( 'Username is Required', WPC_CLIENT_TEXT_DOMAIN) ;
                } elseif ( empty( $data['password'] ) ) {
                    $data['error'] = __( 'Password is Required', WPC_CLIENT_TEXT_DOMAIN) ;
                } elseif ( empty( $encripted )) {
                    $data['password'] = $this->_smtp_encrypt( $data['password'] );
                }
            } else {
                /*our_hook_
                hook_name: wpc_client_email_sending_data
                hook_title: Change email settings
                hook_description: Hook filtered email settings before save it to database.
                hook_type: filter
                hook_in: wp-client
                hook_location settings_email_sending.php
                hook_param: string $type, array $data, array $old_data
                hook_since: 3.7.5.2
                */
                $this->load_email_senders();
                $data = apply_filters( 'wpc_client_email_sending_data', $data, $old_data, $type );

                //encrypt some field
                if ( isset( $data['encrypt'] ) ) {
                    if ( !empty( $data[ $data['encrypt'] ] ) ) {
                        $temp = $data[ $data['encrypt'] ];
                        unset( $data[ $data['encrypt'] ] );
                        $data[ $data['encrypt'] ] = $this->_smtp_encrypt( $temp );
                    }
                    unset( $data['encrypt'], $temp );
                }
            }

            if ( !empty( $data['error'] ) ) {
                return $data['error'];
            }

            $prepared[ $type ] = $data;

        }

        $prepared['type'] = $type;

        return $prepared;
    }



    /**
     * Send TEST email.
     *
     * @param string $to
     * @param string $subject
     * @param string $message
     * @param string $headers
     * @param string $attachments
     * @return bool
     */
    public function send_test( $to, $subject, $message, $headers = '', $attachments = '', $key = '' ) {

        $this->test_email_sender = true;

        $this->settings = $_POST['feilds']['wpc_email_settings'];

        $smtp_password = ( isset( $_POST['feilds']['wpc_email_settings']['smtp']['password'] ) ) ? $_POST['feilds']['wpc_email_settings']['smtp']['password'] : '';
        if ( '' == $smtp_password ) {
            if ( !empty( $_POST['feilds']['wpc_email_settings']['item_id'] ) &&
                    !empty( $this->email_profiles[ $_POST['feilds']['wpc_email_settings']['item_id'] ]['smtp']['password'] ) ) {

                $this->settings['smtp']['password'] = $this->email_profiles[ $_POST['feilds']['wpc_email_settings']['item_id'] ]['smtp']['password'];
            }
        } else {
            $this->settings['smtp']['password'] = $this->_smtp_encrypt( $smtp_password );
        }

        $sendgrid_password = ( isset( $_POST['feilds']['wpc_email_settings']['sendgrid']['password'] ) ) ? $_POST['feilds']['wpc_email_settings']['sendgrid']['password'] : '';
        if ( '' == $sendgrid_password ) {
            if ( !empty( $_POST['feilds']['wpc_email_settings']['item_id'] ) &&
                    !empty( $this->email_profiles[ $_POST['feilds']['wpc_email_settings']['item_id'] ]['sendgrid']['password'] ) ) {

                $this->settings['sendgrid']['password'] = $this->email_profiles[ $_POST['feilds']['wpc_email_settings']['item_id'] ]['sendgrid']['password'];
            }
        } else {
            $this->settings['sendgrid']['password'] = $sendgrid_password;
        }

        return $this->send( $to, $subject, $message, $headers, $attachments );

    }



    function load_email_senders() {
        $dir = WPC()->plugin_dir . 'includes/email_senders/';

        //search the dir for files
        $email_sender_plugins = array();
        if ( !is_dir( $dir ) )
            return;
        if ( ! $dh = opendir( $dir ) )
            return;
        while ( ( $plugin = readdir( $dh ) ) !== false ) {
            if ( substr( $plugin, -4 ) == '.php' )
                $email_sender_plugins[] = $dir . $plugin;
        }
        closedir( $dh );

        sort( $email_sender_plugins );

        //include them suppressing errors
        foreach ( $email_sender_plugins as $file )
            include_once( $file );

        foreach ( (array)$this->senders as $code => $plugin ) {
            $class = $plugin[0];
            $this->sender_objects[ $code ] = new $class;
        }
    }


    /**
     * Send the email.
     *
     * @param string $to
     * @param string $subject
     * @param string $message
     * @param string $headers
     * @param string $attachments
     * @return bool
     */
    public function send( $to, $subject, $message, $headers = '', $attachments = '' ) {

        $debug = '';
		$subject = wp_kses_stripslashes( $subject );
        $message = wp_kses_stripslashes( $message );
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            ( !empty( $this->settings['reply_email'] ) )
                ? 'Reply-To: ' .  $this->settings['reply_email'] : '',
        );

        $subject = wp_specialchars_decode( $subject );

        add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
        add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );


        //send email via API
        if( isset( $this->settings['type'] ) && 'smtp' != $this->settings['type'] && '' != $this->settings['type'] ) {

            $this->load_email_senders();

            if( is_object( $this->sender_objects[$this->settings['type']] ) && method_exists( $this->sender_objects[$this->settings['type']], 'send_email' ) ) {
                //send mail
                $result = $this->sender_objects[$this->settings['type']]->send_email( $to, $subject, $message, $headers, $attachments );

                if ( true !== $result ) {
                    if ( $this->test_email_sender ) {
                        $debug = $result;
                    }

                    $result = false;
                }
            } else {
                $result = false;
            }
        }
        //send email via SMTP
        elseif( isset( $this->settings['type'] ) && 'smtp' == $this->settings['type'] ) {

            //SMTP flag
            $this->mail_sender = true;

            //send test mail
            if ( $this->test_email_sender ) {

                global $phpmailer;

                if ( !is_object( $phpmailer ) || !is_a( $phpmailer, 'PHPMailer' ) ) {
                    if ( !class_exists( 'PHPMailer' ) ) {
                        require_once ABSPATH . WPINC . '/class-phpmailer.php';
                    }
                    if ( !class_exists( 'SMTP' ) ) {
                        require_once ABSPATH . WPINC . '/class-smtp.php';
                    }
                    $phpmailer = new PHPMailer( true );
                }

                //set SMTPDebug to true
                $phpmailer->SMTPDebug = true;

                ob_start();

                $result = wp_mail( $to, $subject, $message, $headers, $attachments );

                if ( ob_get_length() ) {
                    $debug = ob_get_contents();
                }

                if ( empty( $debug ) ) {
                    $debug = $phpmailer->ErrorInfo;
                }


                //commented (v3.9.0+) - because after that test emails not break JSON... maybe it's right
//                if ( false === $result ) {
                    $phpmailer->smtpClose();
//                }

                if ( 1 < ob_get_level() ) {
                    while ( ob_get_level() > 1 ) {
                        ob_end_clean();
                    }
                }


            } else {
                //send mail
                $result = wp_mail( $to, $subject, $message, $headers, $attachments );
            }

        }
        //send email via wpmail function
        else {
            //send test mail
            if ( $this->test_email_sender ) {

                global $phpmailer;

                if ( !is_object( $phpmailer ) || !is_a( $phpmailer, 'PHPMailer' ) ) {
                    if ( !class_exists( 'PHPMailer' ) ) {
                        require_once ABSPATH . WPINC . '/class-phpmailer.php';
                    }
                    if ( !class_exists( 'SMTP' ) ) {
                        require_once ABSPATH . WPINC . '/class-smtp.php';
                    }
                    $phpmailer = new PHPMailer( true );
                }

                ob_start();

                $result = wp_mail( $to, $subject, $message, $headers, $attachments );

                if( ob_get_length() ) {
                    $debug = ob_get_contents();
                }

                if( empty( $debug ) ) {
                    $debug = $phpmailer->ErrorInfo;
                }

                if ( 1 < ob_get_level() ) {
                    while ( ob_get_level() > 1 ) {
                        ob_end_clean();
                    }
                }

            } else {
                //send mail
                $result = wp_mail( $to, $subject, $message, $headers, $attachments );
            }
        }


        remove_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
        remove_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );


        if( !$result && $this->test_email_sender ) {
            return $debug;
        } else {
            return $result;
        }
    }


    public function phpmailer_init( $phpmailer ) {

        // Don't configure for SMTP if no host is provided.
        if ( $this->mail_sender ) {
            if ( isset( $this->settings['type'] ) && 'smtp' == $this->settings['type'] && !empty( $this->settings['smtp']['host'] ) ) {
                $this->settings['smtp']['password'] = !empty( $this->settings['smtp']['password'] )
                    ? $this->_smtp_decrypt( $this->settings['smtp']['password'] ) : '';

                $phpmailer->IsSMTP();
                $phpmailer->SMTPAuth = true;
                $phpmailer->SMTPKeepAlive = ( !isset( $this->settings['smtp']['keep_alive'] ) || 'yes' == $this->settings['smtp']['keep_alive'] ) ? true : false;

                if ( !empty( $this->settings['smtp']['secure'] ) )
                    $phpmailer->SMTPSecure = $this->settings['smtp']['secure'];

                if ( !empty( $this->settings['smtp']['auth_type'] ) ) {
                    $phpmailer->AuthType = $this->settings['smtp']['auth_type'];
                    if ( 'NTLM' == $this->settings['smtp']['auth_type'] ) {
                        $phpmailer->Realm = ( isset( $this->settings['smtp']['auth_realm'] ) ) ? $this->settings['smtp']['auth_realm'] : '';
                        $phpmailer->Workstation = ( isset( $this->settings['smtp']['auth_workstation'] ) ) ? $this->settings['smtp']['auth_workstation'] : '';
                    }
                }

                $phpmailer->Host = $this->settings['smtp']['host'];
                $phpmailer->Mailer = "smtp";
                $phpmailer->Port = ( isset( $this->settings['smtp']['port'] ) ) ? $this->settings['smtp']['port'] : 25;
                $phpmailer->FromName = ( isset( $this->settings['sender_name'] ) ) ? $this->settings['sender_name'] : '';
                $phpmailer->From = $this->settings['sender_email'];
                $phpmailer->Username = $this->settings['smtp']['username'];
                $phpmailer->Password = $this->settings['smtp']['password'];
            }

            $phpmailer->Sender = $this->settings['sender_email'];

            $this->mail_sender = false;
        }

    }


    /**
    * Encrypt text (SMTP password)
    **/
    protected function _smtp_encrypt( $text ) {
        return base64_encode( strrev($text) );
    }


    /**
    * Decrypt password (SMTP password)
    **/
    protected function _smtp_decrypt( $text ) {
        return strrev( base64_decode( $text ) );
    }

}

endif;