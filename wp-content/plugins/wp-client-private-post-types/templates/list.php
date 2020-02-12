<?php
/**
 * Template Name: Private Post Types: Page List
 * Template Description: This template for [wpc_client_private_post_types] shortcode
 * Template Tags: Private Post Types, List View
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/private_post_types/list.php.
 *
 * HOWEVER, on occasion WP-Client will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author 	WP-Client
 */

//needs for translation
__( 'Private Post Types: Page List', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_private_post_types] shortcode', WPC_CLIENT_TEXT_DOMAIN );
__( 'Private Post Types', WPC_CLIENT_TEXT_DOMAIN );
__( 'List View', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<div class="wpc_client_private_posts">

    <?php if ( $hide_content ) {
        echo $no_redirect_text;
    } else {
        if ( ! empty( $pages ) ) { ?>

            <div class="wpc_client_portal_page_category" id="category_general">

                <?php foreach ( $pages as $page ) { ?>

                    <span class="wpc_page_item">
                        <a href="<?php echo $page['url']; ?>"><?php echo $page['title']; ?></a>

                        <?php if ( $show_date ) { ?>
                            [<?php echo $page['date'] . ' ' . $page['time']; ?>]
                        <?php } ?>

                    </span>

                <?php } ?>

            </div>

        <?php }
    } ?>

</div>