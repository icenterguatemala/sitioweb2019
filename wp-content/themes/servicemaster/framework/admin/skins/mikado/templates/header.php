<div class="mkd-page-header page-header clearfix">

    <div class="mkd-theme-name pull-left" >
        <img src="<?php echo esc_url(servicemaster_mikado_get_skin_uri() . '/assets/img/logo.png'); ?>"
             alt="mkd_logo" class="mkd-header-logo pull-left"/>
        <h1 class="pull-left">
            <?php echo esc_html($themeName); ?>
            <small><?php echo esc_html($themeVersion); ?></small>
        </h1>
    </div>
    <div class="mkd-top-section-holder">
        <div class="mkd-top-section-holder-inner">
            <div class="mkd-notification-holder">
                <div class="mkd-input-change"><i class="fa fa-exclamation-circle"></i>You should save your changes</div>
                <div class="mkd-changes-saved"><i class="fa fa-check-circle"></i>All your changes are successfully saved</div>
            </div>
            <div class="mkd-top-buttons-holder">
                <?php if($showSaveButton) { ?>
                    <input type="button" id="mkd_top_save_button" class="btn btn-info btn-sm" value="<?php esc_html_e('Save Changes', 'servicemaster'); ?>"/>
                <?php } ?>
            </div>
        </div>
    </div>

</div> <!-- close div.mkd-page-header -->