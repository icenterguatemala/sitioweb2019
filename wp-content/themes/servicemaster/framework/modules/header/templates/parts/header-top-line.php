<?php if($show_header_top_line) : ?>

	<div class="mkd-top-line-holder columns-<?php echo esc_html($number_of_colors); ?>">
        <?php
        $params = array(
            'colors' => $colors
        );

        for ($i = 1; $i <= $number_of_colors; $i++) {
            $params['color_number'] = $i;
            servicemaster_mikado_get_module_template_part('templates/parts/header-top-line-color', 'header','',$params);
        }
        ?>
    </div>

<?php endif; ?>