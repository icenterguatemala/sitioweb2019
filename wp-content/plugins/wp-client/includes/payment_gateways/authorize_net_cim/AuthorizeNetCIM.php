<?php

class AuthorizeNetCIM {

    protected $api_login_id;
    protected $transaction_key;
    protected $sandbox;
    protected $url;
    protected $transaction_id;
    protected $responseText = '';
    protected $cardType;

    public function __construct( $api_login_id = false, $transaction_key = false, $sandbox = true ) {
        $this->api_login_id = $api_login_id;
        $this->transaction_key = $transaction_key;
        $this->sandbox = $sandbox;
        if ( $sandbox )    {
            $this->url = "https://apitest.authorize.net/xml/v1/request.api";
        } else {
            $this->url = "https://api.authorize.net/xml/v1/request.api";
        }
    }

    public function createCustomerProfileTransaction( $customerProfileId, $customerPaymentProfileId, $paymentData ) {
        $array = $this->add_auth_block();
        $array['transaction'] = array(
            'profileTransAuthOnly' => array(
                'amount' => $paymentData['amount'],
                'customerProfileId'  => $customerProfileId,
                'customerPaymentProfileId'  => $customerPaymentProfileId,
                'order'  => array(
                    'invoiceNumber' => $paymentData['invoice_num']
                ),
            )
        );

        $content = $this->create_xml( $array );
        $content = "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
            "<createCustomerProfileTransactionRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
            $content .
        "</createCustomerProfileTransactionRequest>";

        $response = $this->sendRequest( $content );

        if ( is_array( $response ) && isset( $response['body'] ) ) {
            $result = array();
            $result['code'] = wpc_auth_substring_between( $response['body'],'<code>','</code>' );
            $result['directResponse'] = wpc_auth_substring_between( $response['body'],'<directResponse>','</directResponse>' );
            $response_array = explode( ',', $result['directResponse'] );
            $this->responseText = $response_array[3];
            if ( 'I00001' === $result['code'] ) {
                $this->transaction_id = $response_array[6];
                return true;
            }
        }
        return false;
    }

    public function createCustomerPaymentProfile( $customerProfileId, $paymentProfile ) {
        $array = $this->add_auth_block();
        $array['customerProfileId'] = $customerProfileId;
        $array['paymentProfile'] = array(
            'billTo' => array(
                'firstName' => $paymentProfile['first_name'],
                'lastName'  => $paymentProfile['last_name'],
            ),
            'payment' => array(
                'creditCard' => array(
                    'cardNumber' => $paymentProfile['card'],
                    'expirationDate' => $paymentProfile['exp'],
                    'cardCode' => $paymentProfile['code']
                )
            )
        );
        $array['validationMode'] = 'none';

        $content = $this->create_xml( $array );
        $content = "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
            "<createCustomerPaymentProfileRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
            $content .
        "</createCustomerPaymentProfileRequest>";

        $response = $this->sendRequest( $content );

        if ( is_array( $response ) && isset( $response['body'] ) ) {
            $result = array();
            $result['code'] = wpc_auth_substring_between($response['body'],'<code>','</code>');
            $result['customerPaymentProfileId'] = wpc_auth_substring_between($response['body'],'<customerPaymentProfileId>','</customerPaymentProfileId>');

            $result['text'] = wpc_auth_substring_between( $response['body'],'<text>','</text>' );
            $this->responseText = $result['text'];
            if ( 'I00001' === $result['code'] ) {
                return $result['customerPaymentProfileId'];
            }
        }
        return false;
    }

    public function createCustomerProfile( $email ) {
        $array = $this->add_auth_block();
        $user = get_user_by( 'email', $email );
        if( $user->get('ID') > 0 ) {
            $array['profile'] = array(
                'merchantCustomerId' => $user->get('ID'),
                'description' => '',
                'email' => $email
            );
        } else {
            return false;
        }

        $content = $this->create_xml( $array );
        $content = "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
            "<createCustomerProfileRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
            $content .
        "</createCustomerProfileRequest>";
        $response = $this->sendRequest( $content );
        if ( is_array( $response ) && isset( $response['body'] ) ) {
            $result = array();
            $result['code'] = wpc_auth_substring_between($response['body'],'<code>','</code>');
            $result['customerProfileId'] = wpc_auth_substring_between($response['body'],'<customerProfileId>','</customerProfileId>');

            $result['text'] = wpc_auth_substring_between( $response['body'],'<text>','</text>' );
            $this->responseText = $result['text'];

            if ( 'I00001' === $result['code'] ) {
                return $result['customerProfileId'];
            }
        }
        return false;
    }

    public function getCustomerProfile( $profileId ) {
        $array = $this->add_auth_block();
        $array['customerProfileId'] = $profileId;

        $content = $this->create_xml( $array );
        $content = "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
            "<getCustomerProfileRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
            $content .
        "</getCustomerProfileRequest>";

        $response = $this->sendRequest( $content );

        if ( is_array( $response ) && isset( $response['body'] ) ) {
            $result = array();
            $result['code'] = wpc_auth_substring_between($response['body'],'<code>','</code>');
            $result['customerProfileId'] = wpc_auth_substring_between($response['body'],'<customerProfileId>','</customerProfileId>');
            $result['merchantCustomerId'] = wpc_auth_substring_between($response['body'],'<merchantCustomerId>','</merchantCustomerId>');
            if ( 'I00001' === $result['code'] ) {
                return $result['merchantCustomerId'];
            }
        }
        return false;
    }

    public function getCustomerPaymentProfile( $customerProfileId, $customerPaymentProfileId ) {
        $array = $this->add_auth_block();
        $array['customerProfileId'] = $customerProfileId;
        $array['customerPaymentProfileId'] = $customerPaymentProfileId;

        $content = $this->create_xml( $array );
        $content = "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
            "<getCustomerPaymentProfileRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
            $content .
        "</getCustomerPaymentProfileRequest>";

        $response = $this->sendRequest( $content );

        if ( is_array( $response ) && isset( $response['body'] ) ) {
            /*$result = array();
            $result['code'] = wpc_auth_substring_between($response['body'],'<code>','</code>');
            $result['customerProfileId'] = wpc_auth_substring_between($response['body'],'<customerProfileId>','</customerProfileId>');
            $result['merchantCustomerId'] = wpc_auth_substring_between($response['body'],'<merchantCustomerId>','</merchantCustomerId>');
            if ( 'I00001' === $result['code'] ) {
                return $result['merchantCustomerId'];
            }*/
        }
        return false;
    }


    function add_auth_block( $array = array() ) {
        $array['merchantAuthentication'] = array(
            'name' => $this->api_login_id,
            'transactionKey' => $this->transaction_key
        );
        return $array;
    }


    function sendRequest( $content ) {
        $args = array(
            'user-agent'    => $_SERVER['HTTP_USER_AGENT'],
            'headers'       => array(
                'Content-Type'      => 'text/xml',
                'Content-Length'    => strlen( $content ),
                'Connection'        => 'Connection',
            ),
            'body'          => $content,
            'sslverify'     => '',
            'timeout'   => 30,
        );

        return wp_remote_post( $this->url, $args );
    }


    public function create_xml( $input ) {
        $content = '';
        if( is_array( $input ) || is_object( $input ) ) {
            foreach( (array)$input as $key=>$val ) {
                $output_value = '';
                if( is_array( $val ) ) {
                    $output_value = $this->create_xml( $val );
                } else if( is_numeric( $val ) || is_string( $val ) ) {
                    $output_value = $val;
                }
                $content .= "<$key>$output_value</$key>";
            }
        }
        return $content;
    }

    function getTransactionID() {
        return $this->transaction_id;
    }

    function getResponseText() {
        return $this->responseText;
    }

    function getCardType() {
        return $this->cardType;
    }
}