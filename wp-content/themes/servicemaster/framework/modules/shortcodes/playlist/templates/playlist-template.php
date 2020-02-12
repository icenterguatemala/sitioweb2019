
<div class="mkd-playlist" <?php servicemaster_mikado_inline_style($styles) ?>>
    <?php if($subtitle!='') echo "<h4 class='mkd-playlist-subtitle'>".esc_html($subtitle)."</h4>"; ?>
    <?php if($title!='') echo "<h2 class='mkd-playlist-title'>".esc_html($title)."</h2>"; ?>
    <?php echo do_shortcode($content); ?>
</div>