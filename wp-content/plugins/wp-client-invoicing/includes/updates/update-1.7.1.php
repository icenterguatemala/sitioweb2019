<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$wpc_invoicing = WPC()->get_settings( 'invoicing' );

$wpc_invoicing['items_required'] = 'no';

WPC()->settings()->update( $wpc_invoicing, 'invoicing' );