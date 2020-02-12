<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wp_roles;
$capabilities_maps = WPC()->get_capabilities_maps();


if ( isset( $_REQUEST['update_settings'] ) && isset( $_REQUEST['wpc_role'] ) && '' != $_REQUEST['wpc_role'] ) {
    global $wp_roles;

    $wpc_capabilities = WPC()->get_settings( 'capabilities' );

    foreach ( $capabilities_maps[ $_POST['wpc_role'] ]['variable'] as $cap_key => $cap_val ) {
        $cap = ( isset( $_POST['capabilities'][$cap_key] ) && 'yes' == $_POST['capabilities'][$cap_key] ) ? true : false;
        $wpc_capabilities[$_POST['wpc_role']][$cap_key] = $cap;
    }

    WPC()->settings()->update( $wpc_capabilities, 'capabilities' );

    $wpc_capabilities = apply_filters( 'wp_client_change_caps', $wpc_capabilities );

    $wpc_role = $_POST['wpc_role'] ;
    if( in_array( $wpc_role, array_keys( $capabilities_maps ) ) ) {
        WPC()->members()->added_role( $wpc_role, $wpc_capabilities );

        $args = array(
                'role'          => $wpc_role,
                'meta_key'      => 'wpc_individual_caps',
                'meta_value'    => true,
                'fields'        => 'ID',
            );


        if ( isset( $_POST['wpc_remove_individual'] ) && 'yes' == $_POST['wpc_remove_individual'] ) {

            $users_ids = get_users( $args );

            foreach ( $users_ids as $user_id ) {
                $user = new WP_User( $user_id );
                foreach ( $wpc_capabilities[ $wpc_role ] as $cap_name => $cap_val ) {
                    $user->remove_cap( $cap_name ) ;
                }
                delete_user_meta( $user_id, 'wpc_individual_caps' );
            }
        } else if ( isset( $_POST['individual'] ) ) {
            $users_ids = get_users( $args );

            foreach ( $users_ids as $user_id ) {
                $user = new WP_User( $user_id );
                foreach ( $wpc_capabilities[ $wpc_role ] as $cap_name => $cap_val ) {
                    $user->add_cap( $cap_name, $cap_val ) ;
                }
            }
        }
    }

    WPC()->redirect( WPC()->settings()->get_current_setting_url() . '&msg=u' );
}




$section_fields = array(
    array(
        'type' => 'title',
        'label' => __( 'Capabilities Settings', WPC_CLIENT_TEXT_DOMAIN ),
        'description' => sprintf( __( 'Use this section to select capabilities which will be granted to each role within %s. Individual user capabilities can be modified on a per-user basis from the "Members" menu.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ),
    ),
);

WPC()->settings()->render_settings_section( $section_fields );

?>

<table class="form-table">
    <tr>
        <th>
            <label for="roles"><?php _e( 'Assign Capabilities', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
        </th>
        <td>
            <span class="wpc_ajax_loading" style="display: none;" id="wpc_role_loading"></span>
            <select id="wpc_role" name="wpc_role">
                <?php foreach ( $capabilities_maps as $role => $map ) {
                    if( isset( $map['variable'] ) && is_array( $map['variable'] ) && count( $map['variable'] ) ) { ?>
                        <option value="<?php echo $role; ?>"><?php echo isset( $wp_roles->roles[ $role ]['name'] ) ? $wp_roles->roles[ $role ]['name'] : ''; ?></option>
                <?php }
                } ?>
            </select>
            <span class="description"><?php _e( 'Select the role you want to adjust capabilities for', WPC_CLIENT_TEXT_DOMAIN ); ?></span>

            <br /><br />

            <div id="role111_capabilities"></div>
        </td>
    </tr>
</table>


<?php

$field_data = array(
    'type' => 'checkbox',
    'id' => 'wpc_remove_individual',
    'name' => 'wpc_remove_individual',
    'description' => __( 'Remove Individual Capabilities', WPC_CLIENT_TEXT_DOMAIN ),
    'class' => 'wpc_margin_5',
);

echo WPC()->settings()->render_setting_field( $field_data );

?>

<br />
<br />

<script type="text/javascript">

    jQuery( document ).ready( function() {

        //ajax get capabilities
        jQuery.fn.get_capabilities = function () {
            var wpc_role = jQuery( '#wpc_role' ).val();
            jQuery( '#wpc_remove_individual' ).prop("checked", false);
            jQuery( '#wpc_role_loading' ).show();

            jQuery.ajax({
                type        : 'POST',
                dataType    : 'json',
                url         : '<?php echo admin_url() ?>admin-ajax.php',
                data        : 'action=wpc_get_capabilities&wpc_role=' + wpc_role,
                success     : function( response ) {

                    jQuery( '#wpc_role_loading' ).hide();
                    jQuery( '#role111_capabilities' ).html( response.caps );
                }
            });
        };

        //change role
        jQuery( '#wpc_role' ).change( function() {
            jQuery( this ).get_capabilities();
        });

        jQuery( this ).get_capabilities();

    });
</script>