<style>
	<?php if ( isset( $args['settings']['billing'] ) ) : ?>
	    <?php foreach ( $args['settings']['billing'] as $field ) : ?>
	        <?php if ( isset( $field['required'] ) && $field['required'] == '0' ) : ?>
	            #<?php echo $field['name']; ?>_field abbr {
	                display: none !important;
	            }
	        <?php endif; ?>
	    <?php endforeach; ?>
	<?php endif; ?>

	<?php if ( isset( $args['settings']['shipping'] ) ) : ?>
	    <?php foreach ( $args['settings']['shipping'] as $field ) : ?>
	        <?php if ( $field['required'] == '0' ) : ?>
	            #<?php echo $field['name']; ?>_field abbr {
	                display: none !important;
	            }
	        <?php endif; ?>
	    <?php endforeach; ?>
	<?php endif; ?>
</style>

<script type="text/javascript">
	jQuery(window).load(function() {
		<?php if ( isset( $args['settings']['billing'] ) ) : ?>
		    <?php foreach ( $args['settings']['billing'] as $field ) : ?>
	        	<?php if ( $field['required'] == '0' ) : ?>
	        		jQuery('#<?php echo $field['name']; ?>_field').removeClass('validate-required');
	        	<?php endif; ?>
		    <?php endforeach; ?>
		<?php endif; ?>
	    <?php if ( isset( $args['settings']['shipping'] ) ) : ?>
	    	<?php foreach ( $args['settings']['shipping'] as $field ) : ?>
    	    	<?php if ( $field['required'] == '0' ) : ?>
    				jQuery('#<?php echo $field['name']; ?>_field').removeClass('validate-required');
	        	<?php endif; ?>
    		<?php endforeach; ?>
    	<?php endif; ?>
	});
	var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
	var inspire_upload_nonce = '<?php echo wp_create_nonce( 'inspire_upload_nonce' ); ?>';
</script>
