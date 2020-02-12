
<div class="mkd-playlist-item clearfix">
    <?php if($audio_file !== ''){ ?>
    <div class="mkd-playlist-control">
        <span class="arrow_triangle-right mkd-playlist-play" aria-hidden="true"></span>
        <span class="icon_pause mkd-playlist-pause" aria-hidden="true"></span>
        <audio id="mkd-playlist-track-<?php echo esc_attr($random_id); ?>" src="<?php echo esc_url($audio_file); ?>"></audio>
    </div>
    <?php } ?>
    <?php if($title !== ''){ ?>
        <div class="mkd-playlist-item-title"><h5><?php echo esc_html($title); ?></h5></div>
    <?php } ?>

</div>