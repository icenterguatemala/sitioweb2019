<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <?php
    /**
     * @see servicemaster_mikado_header_meta() - hooked with 10
     * @see mkd_user_scalable - hooked with 10
     */

    do_action('servicemaster_mikado_header_meta');
    wp_head();
    ?>
</head>

<body <?php body_class(); ?> itemscope itemtype="http://schema.org/WebPage">
<?php

servicemaster_mikado_get_side_area();

if (servicemaster_mikado_options()->getOptionValue('smooth_page_transitions') == "yes") { ?>

    <div class="mkd-smooth-transition-loader mkd-mimic-ajax">
        <div class="mkd-st-loader">
            <div class="mkd-st-loader1">
                <?php echo servicemaster_mikado_loading_spinners(true); ?>
            </div>
        </div>
    </div>
<?php } ?>
<?php if (servicemaster_mikado_is_paspartu_on()){ ?>
<div class="mkd-wrapper-paspartu">
    <?php } ?>
    <div class="mkd-wrapper">
        <div class="mkd-wrapper-inner">
            <?php servicemaster_mikado_get_header(); ?>

            <?php if (servicemaster_mikado_options()->getOptionValue('show_back_button') == "yes") { ?>
                <a id='mkd-back-to-top' href='#'>
                <span class="mkd-icon-stack">
                     <?php echo servicemaster_mikado_icon_collections()->renderIcon('arrow_carrot-up', 'font_elegant'); ?>
                </span>
                  <span class="mkd-back-to-top-inner">
                    <span class="mkd-back-to-top-text"><?php esc_html_e('Top', 'servicemaster'); ?></span>
                </span>
                </a>
            <?php } ?>
            <?php servicemaster_mikado_get_full_screen_menu(); ?>

            <div class="mkd-content" <?php servicemaster_mikado_content_elem_style_attr(); ?>>
                <div class="mkd-content-inner">