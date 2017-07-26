<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<h2><?php echo sprintf( __( 'You are deactivating %s plugin.', 'wpdesk-tracker' ), $plugin_name ); ?></h2>

<div class="wpdesk_tracker_deactivate">
	<div class="body">
		<div class="panel" data-panel-id="confirm"><p></p></div>
		<div class="panel active" data-panel-id="reasons">
			<h4><strong><?php _e(' If you have a moment, please let us know why you are deactivating plugin (anonymous feedback):', 'wpdesk-tracker' ); ?></strong></h4>
			<ul id="reasons-list">
                <li class="reason">
                    <label>
	            	<span>
	            		<input type="radio" name="selected-reason" value="plugin_stopped_working">
                    </span>
                        <span><?php _e( 'The plugin suddenly stopped working', 'wpdesk-tracker' ); ?></span>
                    </label>
                </li>
                <li class="reason">
                    <label>
	            	<span>
	            		<input type="radio" name="selected-reason" value="broke_my_site">
                    </span>
                        <span><?php _e( 'The plugin broke my site', 'wpdesk-tracker' ); ?></span>
                    </label>
                </li>
				<li class="reason has-input">
					<label>
		                <span>
		                    <input type="radio" name="selected-reason" value="found_better_plugin">
	                    </span>
						<span><?php _e( 'I found a better plugin', 'wpdesk-tracker' ); ?></span>
					</label>
					<div id="found_better_plugin" class="reason-input">
						<input type="text" name="better_plugin_name" placeholder="<?php _e( 'What\'s the plugin\'s name?', 'wpdesk-tracker' ); ?>">
					</div>
				</li>
				<li class="reason">
					<label>
	            	<span>
	            		<input type="radio" name="selected-reason" value="plugin_for_short_period">
                    </span>
						<span><?php _e('I only needed the plugin for a short period', 'wpdesk-tracker' ); ?></span>
					</label>
				</li>
				<li class="reason">
					<label>
	            	<span>
	            		<input type="radio" name="selected-reason" value="no_longer_need">
                    </span>
						<span><?php _e( 'I no longer need the plugin', 'wpdesk-tracker' ); ?></span>
					</label>
				</li>
				<li class="reason">
					<label>
	            	<span>
	            		<input type="radio" name="selected-reason" value="temporary_deactivation">
                    </span>
						<span><?php _e( 'It\'s a temporary deactivation. I\'m just debugging an issue.', 'wpdesk-tracker' ); ?></span>
					</label>
				</li>
				<li class="reason has-input">
					<label>
	            	<span>
	            		<input type="radio" name="selected-reason" value="other">
                    </span>
						<span><?php _e( 'Other', 'wpdesk-tracker' ); ?></span>
					</label>
					<div id="other" class="reason-input">
						<input type="text" name="other" placeholder="<?php _e( 'Kindly tell us the reason so we can improve', 'wpdesk-tracker' ); ?>">
					</div>
				</li>
			</ul>
		</div>
	</div>
	<div class="footer">
		<a href="#" class="button button-secondary button-deactivate allow-deactivate"><?php _e( 'Skip &amp; Deactivate', 'wpdesk-tracker' ); ?></a>
		<a href="#" class="button button-primary button-close"><?php _e( 'Cancel', 'wpdesk-tracker' ); ?></a>
	</div>
</div>
<script type="text/javascript">
	jQuery('input[type=radio]').click(function(){
        var reason = jQuery('input[name=selected-reason]:checked').val();
        console.log(reason);
        jQuery('.reason-input').hide();
        if ( reason == 'found_better_plugin' ) {
            jQuery('#found_better_plugin').show();
        }
        if ( reason == 'other' ) {
            jQuery('#other').show();
        }
        jQuery('.wpdesk_tracker_deactivate .button-deactivate').html( '<?php _e( 'Submit &amp; Deactivate', 'wpdesk-tracker' ); ?>' );
	})
	jQuery('.button-deactivate').click(function(e){
	    e.preventDefault();
	    console.log('deactivate');
        var reason = jQuery('input[name=selected-reason]:checked').val();
        var plugin = '<?php echo $plugin; ?>';
        var plugin_name = '<?php echo $plugin_name; ?>';
        var additional_info = '';
        if ( reason == 'found_better_plugin' ) {
            additional_info = jQuery('#found_better_plugin input').val();
        }
        if ( reason == 'other' ) {
            additional_info = jQuery('#other input').val();
        }
        console.log(reason);
        if ( typeof reason != 'undefined' ) {
            console.log('not undefined');
            jQuery('.button').attr('disabled',true);
            jQuery.ajax( '<?php echo admin_url('admin-ajax.php'); ?>',
                {
                    type: 'POST',
                    data: {
                        action: 'wpdesk_tracker_deactivation_handler',
	                    reason: reason,
	                    plugin: plugin,
	                    plugin_name: plugin_name,
                        additional_info: additional_info,
                    }
                }
            ).always(function() {
                var url = '<?php echo str_replace( '&amp;', '&', admin_url( wp_nonce_url( 'plugins.php?action=deactivate&plugin=' . $plugin . '&plugin_status=all&', 'deactivate-plugin_' . $plugin ) ) ); ?>';
                window.location.href = url;
            });
        }
        else {
            var url = '<?php echo str_replace( '&amp;', '&', admin_url( wp_nonce_url( 'plugins.php?action=deactivate&plugin=' . $plugin . '&plugin_status=all&', 'deactivate-plugin_' . $plugin ) ) ); ?>';
            window.location.href = url;
        }
	})
    jQuery('.button-close').click(function(e){
        e.preventDefault();
        window.history.back();
    })
</script>
