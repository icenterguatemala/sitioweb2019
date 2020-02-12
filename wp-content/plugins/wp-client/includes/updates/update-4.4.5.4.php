<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$query = new WP_Query;
$portal_pages = $query->query( array(
    'post_type' => 'clientspage',
    'fields'      => 'ids',
    'posts_per_page' => -1,
    'meta_query' => array(
        array(
            'key'   => '_wp_page_template',
            'value' => '__use_same_as_portal_page'
        )
    )
) );

$portalhubs = $query->query( array(
    'post_type' => 'portalhub',
    'fields'      => 'ids',
    'posts_per_page' => -1,
    'meta_query' => array(
        array(
            'key'   => '_wp_page_template',
            'value' => '__use_same_as_hub_page'
        )
    )
) );

$post_ids_update = array_merge( $portal_pages, $portalhubs );

if ( ! empty( $post_ids_update ) ) {
    global $wpdb;
    $wpdb->query(
        "UPDATE {$wpdb->postmeta}
        SET meta_value = ''
        WHERE meta_key = '_wp_page_template' AND 
              post_id IN('" . implode( ',', $post_ids_update ) . "')"
    );
}