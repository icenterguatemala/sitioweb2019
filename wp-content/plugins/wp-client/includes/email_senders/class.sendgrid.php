<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( !class_exists( 'WPC_Email_Sender_SendGrid' ) ) {
    class WPC_Email_Sender_SendGrid {
        private $api_key = '';
        private $send_from = '';
        var $settings = array();


        function __construct() {

            $this->settings = &WPC()->mailer()->settings;

            //validation before save email profile
            add_filter( 'wpc_client_email_sending_data', array( &$this, 'email_sending_data' ), 10, 3 );

            //add some fields if type of email profile is sendgrid
            add_action( 'wpc_client_email_settings_for_sendgrid', array( &$this, 'email_settings_content' ) );
        }


        function set_api_key( $value = '' ) {
            $this->api_key = $value;
            return $this;
        }


        function set_from( $value = '' ) {
            $this->send_from = $value;
            return $this;
        }


        /**
         * Validation before save email profile
         *
         * @param array $data
         * @param array $old_data
         * @param string $type
         * @return array
         */
        function email_sending_data( $data, $old_data, $type ) {
            if( 'sendgrid' == $type ) {
                if ( empty( $data['api_key'] ) ) {
                    $data['error'] = __( 'API Key is Required', WPC_CLIENT_TEXT_DOMAIN) ;
                }
            }
            return $data;
        }


        /**
         * Add some fields if type of email profile is sendgrid
         *
         * @param array $wpc_email
         */
        function email_settings_content( $wpc_email ) {
            ?>
            <tr>
                <th><label for="wpc_sendgrid_api_key"><?php _e( 'API Key', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label></th>
                <td><input type="text" id="wpc_sendgrid_api_key" name="wpc_email_settings[sendgrid][api_key]" value="<?php echo ( !empty( $wpc_email['sendgrid']['api_key'] ) ? $wpc_email['sendgrid']['api_key'] : '' ); ?>" /></td>
            </tr>
            <?php
        }


        function send_email( $to, $subject, $message, $headers = '', $attachments = array() ) {
            $this->set_api_key( isset( $this->settings['sendgrid']['api_key'] ) ? $this->settings[ 'sendgrid' ]['api_key'] : '' );

            include_once( WPC()->plugin_dir . 'includes/email_senders/sendgrid/SendGrid.php');
            include_once( WPC()->plugin_dir . 'includes/email_senders/sendgrid/helpers/Mail.php');
            include_once( WPC()->plugin_dir . 'includes/email_senders/sendgrid/php-http-client/Client.php');
            include_once( WPC()->plugin_dir . 'includes/email_senders/sendgrid/php-http-client/Response.php');

            /*$from = new SendGrid\Email(null, "test@wpplugins.org.ua");
            $subject = "Hello World from the SendGrid PHP Library!";
            $to = new SendGrid\Email(null, "nalivaikoyura@gmail.com");
            $content = new SendGrid\Content("text/plain", "Hello, Email!");
            $mail = new SendGrid\Mail($from, $subject, $to, $content);
            $sg = new \SendGrid($this->api_key);

            $response = $sg->client->mail()->send()->post($mail);
            echo $response->statusCode();
            echo $response->headers();
            echo $response->body();
            exit;*/

            // Compact the input, apply the filters, and extract them back out
            extract( apply_filters( 'wp_mail', compact( 'to', 'subject', 'message', 'headers', 'attachments' ) ) );

            // prepare attachments
            $attached_files = array();
            if( ! empty( $attachments ) ) {
                if( ! is_array( $attachments ) ) {
                    $pos = strpos( ',', $attachments );
                    if ( false !== $pos ) {
                        $attachments = preg_split( '/,\s*/', $attachments );
                    } else {
                        $attachments = explode( "\n", str_replace( "\r\n", "\n", $attachments ) );
                    }
                }

                if( is_array( $attachments ) ) {
                    foreach( $attachments as $attachment ) {
                        if( file_exists( $attachment ) ) {
                            $attached_files[] = $attachment;
                            $info = pathinfo($attachment);
                            $attachment = new WP_Client_SendGrid\Attachment();
                            $attachment->setContent( file_get_contents ( $attachment ) );
                            $attachment->setType("application/pdf");
                            $attachment->setFilename( $info['basename'] );
                            $attachment->setDisposition("attachment");
                            $attachment->setContentId("Attachment");
                            $attached_files[] = $attachment;
                        }
                    }
                }
            }

            // Headers
            $cc  = array();
            $bcc = array();
            if( empty( $headers ) ) {
                $headers = array();
            } else {
                if( ! is_array( $headers ) ) {
                    // Explode the headers out, so this function can take both
                    // string headers and an array of headers.
                    $tempheaders = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
                } else {
                    $tempheaders = $headers;
                }
                $headers = array();

                // If it's actually got contents
                if( ! empty( $tempheaders ) ) {
                    // Iterate through the raw headers
                    foreach( (array) $tempheaders as $header ) {
                        if( false === strpos($header, ':') ) {
                            if( false !== stripos( $header, 'boundary=' ) ) {
                                $parts = preg_split( '/boundary=/i', trim( $header ) );
                                $boundary = trim( str_replace( array( "'", '"' ), '', $parts[1] ) );
                            }
                            continue;
                        }
                        // Explode them out
                        list( $name, $content ) = explode( ':', trim( $header ), 2 );

                        // Cleanup crew
                        $name    = trim( $name );
                        $content = trim( $content );

                        switch( strtolower( $name ) ) {
                            // Mainly for legacy -- process a From: header if it's there
                            case 'from':
                                if ( false !== strpos( $content, '<' ) ) {
                                    // So... making my life hard again?
                                    $from_name = substr( $content, 0, strpos( $content, '<' ) - 1 );
                                    $from_name = str_replace( '"', '', $from_name );
                                    $from_name = trim( $from_name );

                                    $from_email = substr( $content, strpos( $content, '<' ) + 1 );
                                    $from_email = str_replace( '>', '', $from_email );
                                    $from_email = trim( $from_email );
                                } else {
                                    $from_email = trim( $content );
                                }
                                break;
                            case 'content-type':
                                if ( false !== strpos( $content, ';' ) ) {
                                    list( $type, $charset ) = explode( ';', $content );
                                    $content_type = trim( $type );
                                    if ( false !== stripos( $charset, 'charset=' ) ) {
                                      $charset = trim( str_replace( array( 'charset=', '"' ), '', $charset ) );
                                    } elseif ( false !== stripos( $charset, 'boundary=' ) ) {
                                      $boundary = trim( str_replace( array( 'BOUNDARY=', 'boundary=', '"' ), '', $charset ) );
                                      $charset = '';
                                    }
                                } else {
                                    $content_type = trim( $content );
                                }
                                break;
                                case 'cc':
                                    $cc = array_merge( (array) $cc, explode( ',', $content ) );
                                    foreach ( $cc as $key => $recipient ) {
                                        $cc[ $key ] = trim( $recipient );
                                    }
                                    break;
                                case 'bcc':
                                    $bcc = array_merge( (array) $bcc, explode( ',', $content ) );
                                    foreach ( $bcc as $key => $recipient ) {
                                        $bcc[ $key ] = trim( $recipient );
                                    }
                                    break;
                                case 'reply-to':
                                    $replyto = $content;
                                    break;
                                default:
                                    // Add it to our grand headers array
                                    $headers[trim( $name )] = trim( $content );
                                    break;
                        }
                    }
                }
            }

            // From email and name
            // If we don't have a name from the input headers
            if ( empty( $from_name ) )
                $from_name = $this->send_from;

            /* If we don't have an email from the input headers default to wordpress@$sitename
             * Some hosts will block outgoing mail from this address if it doesn't exist but
             * there's no easy alternative. Defaulting to admin_email might appear to be another
             * option but some hosts may refuse to relay mail from an unknown domain. See
             * http://trac.wordpress.org/ticket/5007.
             */

            if ( empty( $from_email ) ) {
                $from_email = trim( $this->send_from );
                if( !$from_email ) {
                    // Get the site domain and get rid of www.
                    $sitename = strtolower( $_SERVER['SERVER_NAME'] );
                    if ( 'www.' == substr( $sitename, 0, 4 ) ) {
                      $sitename = substr( $sitename, 4 );
                    }

                    $from_email = "wordpress@$sitename";
                }
            }

            // Plugin authors can override the potentially troublesome default
            $from_email = apply_filters( 'wp_mail_from'     , $from_email );
            $from_name  = apply_filters( 'wp_mail_from_name', $from_name  );

            // Add any CC and BCC recipients
            if( ! empty( $cc ) ) {
                foreach ( (array) $cc as $key => $recipient ) {
                    // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
                    if ( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
                        if ( count( $matches ) == 3 ) {
                            $cc[ $key ] = trim( $matches[2] );
                        }
                    }
                    $cc[ $key ] = new WP_Client_SendGrid\Email( null, $cc[ $key ] );
                }
            }

            if ( ! empty( $bcc ) ) {
                foreach ( (array) $bcc as $key => $recipient ) {
                    // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
                    if( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
                        if ( 3 == count( $matches ) ) {
                            $bcc[ $key ] = trim( $matches[2] );
                        }
                    }
                }
                $bcc[ $key ] = new WP_Client_SendGrid\Email( null, $bcc[ $key ] );
            }

            // Set destination addresses
            if ( !is_array( $to ) )
                $to = explode( ',', $to );

            foreach ( (array) $to as $key => $recipient ) {
                // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
                if ( preg_match(  '/(.*)<(.+)>/', $recipient, $matches ) ) {
                    if ( 3 == count( $matches ) ) {
                        $to[ $key ] = trim( $matches[2] );
                    }
                }
                $to[ $key ] = new WP_Client_SendGrid\Email(null, $to[ $key ]);
            }

            if ( ! isset( $content_type ) )
                $content_type = 'text/plain';

            $content_type = apply_filters( 'wp_mail_content_type', $content_type );

            $from = new WP_Client_SendGrid\Email( $from_name, $from_email );
            $content = new WP_Client_SendGrid\Content( $content_type, $message);
            $mail = new WP_Client_SendGrid\Mail($from, $subject, $to, $content);
            $clients = $mail->getPersonalizations();
            if( !isset( $clients[0] ) ) return false;
            $client = $clients[0];

            // set from cc
            if ( count( $cc ) ) {
                foreach( $cc as $email ) {
                    $client->setCcs( $email );
                }
            }
            // set from bcc
            if ( count( $bcc ) ) {
                foreach( $bcc as $email ) {
                    $client->addBcc( $email );
                }
            }

            if( !isset( $replyto ) ) {
              $replyto = trim( get_option('admin_email') );
            }
            if ( preg_match( '/.*<(.*)>.*/i', $replyto, $result ) ) {
                $replyto = $result[1];
            }
            $reply_to = new WP_Client_SendGrid\ReplyTo( $replyto );
            $mail->setReplyTo( $reply_to );

            // add attachemnts
            if( count( $attached_files ) ) {
                foreach( $attached_files as $attachment ) {
                    $mail->addAttachment( $attachment );
                }
            }

            $sg = new \SendGrid( $this->api_key );

            $response = $sg->client->mail()->send()->post($mail);

            if( $response->statusCode() == 202 ) {
                return true;
            } else {
                $body = $response->body();
                if( isset( $body->errors[0]->message ) ) {
                    return $body->errors[0]->message;
                }
                return false;
            }
        }
    }

    $this->senders['sendgrid'] = array( 'WPC_Email_Sender_SendGrid', __( 'SendGrid', WPC_CLIENT_TEXT_DOMAIN ) );
}