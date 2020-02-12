<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( version_compare( $current_version, '3.9.0-alpha1', '>=' ) ) {
    //merge chains from old private messages transfer
    global $wpdb;

    $unique_chains_client_ids = $wpdb->get_col( $wpdb->prepare(
        "SELECT client_ids
        FROM {$wpdb->prefix}wpc_client_chains
        WHERE subject=%s
        GROUP BY client_ids",
        addslashes( htmlspecialchars( __( 'No Subject', WPC_CLIENT_TEXT_DOMAIN ) ) )
    ) );

    if( isset( $unique_chains_client_ids ) && !empty( $unique_chains_client_ids ) ) {
        $temp = array();
        foreach( $unique_chains_client_ids as $client_ids ) {
            $explode = explode( ',', $client_ids );
            $explode = array_reverse( $explode );
            $client_ids_invert = implode( ',', $explode );

            if( !in_array( $client_ids, $temp ) && !in_array( $client_ids_invert, $temp )  ) {
                $temp[] = $client_ids;
            }
        }

        $unique_chains_client_ids = $temp;

        foreach( $unique_chains_client_ids as $client_ids ) {
            $explode = explode( ',', $client_ids );
            $explode = array_reverse( $explode );
            $client_ids_invert = implode( ',', $explode );

            $chains_for_merge = $wpdb->get_col( $wpdb->prepare(
                "SELECT id
                FROM {$wpdb->prefix}wpc_client_chains
                WHERE subject=%s AND
                  ( client_ids=%s OR client_ids=%s )",
                addslashes( htmlspecialchars( __( 'No Subject', WPC_CLIENT_TEXT_DOMAIN ) ) ),
                $client_ids,
                $client_ids_invert
            ) );

            if( !empty( $chains_for_merge ) ) {
                if( count( $chains_for_merge ) == 1 ) {
                    continue;
                }

                $first_chain_id = $chains_for_merge[0];
                $another_chains = array_slice( $chains_for_merge, 1 );

                $wpdb->query(
                    "UPDATE {$wpdb->prefix}wpc_client_messages
                    SET chain_id=$first_chain_id
                    WHERE chain_id IN('" . implode( "','", $another_chains ) . "')"
                );

                $wpdb->query(
                    "DELETE
                    FROM {$wpdb->prefix}wpc_client_chains
                    WHERE id IN('" . implode( "','", $another_chains ) . "')"
                );
            }
        }
    }
}