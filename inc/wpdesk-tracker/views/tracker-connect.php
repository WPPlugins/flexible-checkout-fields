<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<div id="wpdesk_tracker_connect" class="plugin-card">
	<div class="message plugin-card-top">
        <span class="wpdesk-logo"></span>

		<p>
			<?php printf ( __( 'Hey %s,', 'wpdesk-tracker'), $username ); ?><br/>
			<?php _e( 'Please help us improve our plugins! If you opt-in, we will collect some non-sensitive data and usage information. If you skip this, that\'s okay! All plugins will work just fine.', 'wpdesk-tracker' ); ?>
		</p>
	</div>

	<div class="actions plugin-card-bottom">
		<a id="wpdesk_tracker_allow_button" href="<?php echo $allow_url; ?>" class="button button-primary button-allow button-large"><?php _e( 'Allow & Continue &rarr;', 'wpdesk-tracker' ); ?></a>
		<a href="<?php echo $skip_url; ?>" class="button button-secondary"><?php _e( 'Skip', 'wpdesk-tracker' ); ?></a>
		<div class="clear"></div>
	</div>

	<div class="permissions">
		<a class="trigger" href="#"><?php _e( 'What permissions are being granted?', 'wpdesk-tracker' ); ?></a>

		<div class="permissions-details">
		    <ul>
		    	<li id="permission-site" class="permission site">
		    		<i class="dashicons dashicons-admin-settings"></i>
		    		<div>
		    			<span><?php _e( 'Your Site Overview', 'wpdesk-tracker' ); ?></span>
		    			<p><?php _e( 'Site URL, WP version, PHP info, admin email', 'wpdesk-tracker' ); ?></p>
		    		</div>
		    	</li>
		    	<li id="permission-events" class="permission events">
		    		<i class="dashicons dashicons-admin-plugins"></i>
		    		<div>
		    			<span><?php _e( 'Plugin Usage', 'wpdesk-tracker' ); ?></span>
		    			<p><?php _e( 'Current settings and usage information of WP Desk plugins', 'wpdesk-tracker' ); ?></p>
		    		</div>
		    	</li>
		    	<li id="permission-store" class="permission store">
		    		<i class="dashicons dashicons-store"></i>
		    		<div>
		    			<span><?php _e( 'Your Store Overview', 'wpdesk-tracker' ); ?></span>
		    			<p><?php _e( 'Non-sensitive store usage information', 'wpdesk-tracker' ); ?></p>
		    		</div>
		    	</li>
		    	<li id="permission-notices" class="permission notices">
		    		<i class="dashicons dashicons-testimonial"></i>
		    		<div>
		    			<span><?php _e( 'Admin Updates', 'wpdesk-tracker' ); ?></span>
		    			<p><?php _e( 'Announcements, updates, marketing, no spam and easy opt-out', 'wpdesk-tracker' ); ?></p>
		    		</div>
		    	</li>
		    </ul>

            <div class="terms">
                <a href="<?php echo $terms_url; ?>" target="_blank"><?php _e( 'Find out more &raquo;', 'wpdesk-tracker' ); ?></a>
            </div>
		</div>
	</div>
</div>
<script type="text/javascript">
	jQuery('.trigger').click(function(e) {
	    e.preventDefault();
	    if (jQuery(this).parent().hasClass('open')) {
            jQuery(this).parent().removeClass('open')
        }
        else {
            jQuery(this).parent().addClass('open');
        }
	});
    jQuery(document).on('click', '#wpdesk_tracker_allow_button',function(e){
        e.preventDefault();
        var href = jQuery(this).attr('href');
        jQuery.ajax( '<?php echo admin_url('admin-ajax.php'); ?>',
            {
                type: 'POST',
                data: {
                    action: 'wpdesk_tracker_notice_handler',
                    type: 'allow',
                }
            }
        ).always(function() {
            window.location.href = href;
        });
    });
</script>
