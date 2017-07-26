<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<script type="text/javascript">
	var wpdesk_track_deactivation_plugins = <?php echo json_encode( $plugins ); ?>;
	console.log(wpdesk_track_deactivation_plugins);
	jQuery("span.deactivate a").click(function(e){
	    var is_tracked = false;
	    console.log(jQuery(this).closest('tr'));
	    var data_plugin = jQuery(this).closest('tr').attr('data-plugin');
	    console.log(data_plugin);
	    var href = jQuery(this).attr('href');
	    console.log(href);
        jQuery.each( wpdesk_track_deactivation_plugins, function( key, value ) {
            console.log( key + ": " + value );
            if ( value == data_plugin ) {
                console.log('match');
                is_tracked = true;
            }
        });
        if ( is_tracked ) {
            e.preventDefault();
            window.location.href = '<?php echo admin_url( 'admin.php?page=wpdesk_tracker_deactivate&plugin=' ); ?>' + '&plugin=' + data_plugin;
        }
	})
</script>
