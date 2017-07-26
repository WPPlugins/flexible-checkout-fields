<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<?php if ( $coupon_avaliable ) : ?>
    <div id="wpdesk_tracker_notice_coupon" class="updated notice wpdesk_tracker_notice is-dismissible">
        <p>
            <?php printf ( __( 'Hey %s,', 'wpdesk-tracker'), $username ); ?><br/>
            <?php _e( 'Allow WP Desk to collect plugin usage information and <strong>get discount coupon</strong> in our shop. No sensitive data is tracked.', 'wpdesk-tracker'); ?>
            <a href="<?php echo $terms_url; ?>" target="_blank"><?php _e( 'Find out more &raquo;', 'wpdesk-tracker' ); ?></a>
        </p>
        <p>
            <button id="wpdesk_tracker_allow_coupon_button_notice" class="button button-primary"><?php _e( 'Allow', 'wpdesk-tracker' ); ?></button>
        </p>
    </div>
<?php else : ?>
    <div id="wpdesk_tracker_notice" class="updated notice wpdesk_tracker_notice is-dismissible">
        <p>
            <?php printf ( __( 'Hey %s,', 'wpdesk-tracker'), $username ); ?><br/>
            <?php _e( 'Please help us improve our plugins! If you opt-in, we will collect some non-sensitive data and usage information. If you skip this, that\'s okay! All plugins will work just fine.', 'wpdesk-tracker'); ?>
            <a href="<?php echo $terms_url; ?>" target="_blank"><?php _e( 'Find out more &raquo;', 'wpdesk-tracker' ); ?></a>
        </p>
        <p>
            <button id="wpdesk_tracker_allow_button_notice" class="button button-primary"><?php _e( 'Allow', 'wpdesk-tracker' ); ?></button>
        </p>
    </div>
<?php endif; ?>

<script type="text/javascript">
    jQuery(document).on('click', '#wpdesk_tracker_notice_coupon .notice-dismiss',function(e){
        e.preventDefault();
        console.log('dismiss');
        jQuery.ajax( '<?php echo admin_url('admin-ajax.php'); ?>',
            {
                type: 'POST',
                data: {
                    action: 'wpdesk_tracker_notice_handler',
                    type: 'dismiss_coupon',
                }
            }
        );
    })
    jQuery(document).on('click', '#wpdesk_tracker_allow_coupon_button_notice',function(e){
        e.preventDefault();
        console.log('allow');
        jQuery.ajax( '<?php echo admin_url('admin-ajax.php'); ?>',
            {
                type: 'POST',
                data: {
                    action: 'wpdesk_tracker_notice_handler',
                    type: 'allow_coupon',
                }
            }
        );
        jQuery('#wpdesk_tracker_notice_coupon').hide();
    });
    jQuery(document).on('click', '#wpdesk_tracker_notice .notice-dismiss',function(e){
        e.preventDefault();
        console.log('dismiss');
        jQuery.ajax( '<?php echo admin_url('admin-ajax.php'); ?>',
            {
                type: 'POST',
                data: {
                    action: 'wpdesk_tracker_notice_handler',
                    type: 'dismiss',
                }
            }
        );
    })
    jQuery(document).on('click', '#wpdesk_tracker_allow_button_notice',function(e){
        e.preventDefault();
        console.log('allow');
        jQuery.ajax( '<?php echo admin_url('admin-ajax.php'); ?>',
            {
                type: 'POST',
                data: {
                    action: 'wpdesk_tracker_notice_handler',
                    type: 'allow',
                }
            }
        );
        jQuery('#wpdesk_tracker_notice').hide();
    });
</script>
