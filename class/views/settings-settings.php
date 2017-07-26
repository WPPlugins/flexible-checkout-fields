<?php
	global $woocommerce;
?>
<form action="" method="post">
	<?php settings_fields( 'inspire_checkout_fields_settings' ); ?>

 	<?php if (!empty($_POST['option_page']) && $_POST['option_page'] === 'inspire_checkout_fields_settings'): ?>
		<div id="message" class="updated fade"><p><strong><?php _e( 'Settings saved.', 'flexible-checkout-fields' ); ?></strong></p></div>
	<?php endif; ?>

	<h3><?php _e( 'Settings', 'flexible-checkout-fields' ); ?></h3>

	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th class="titledesc" scope="row">
					<label for="woocommerce_checkout_fields_css"><?php _e( 'CSS jQuery UI', 'flexible-checkout-fields' ); ?></label>
				</th>

				<td class="forminp forminp-text">
    				<input value="0" id="woocommerce_checkout_fields_css" name="inspire_checkout_fields[css_disable]" type="hidden" />

					<label><input class="regular-checkbox" value="1" id="woocommerce_checkout_fields_css" name="inspire_checkout_fields[css_disable]" type="checkbox" <?php if( $this->getSettingValue('css_disable') == 1) echo('checked'); ?> /> <?php _e( 'Disable jquery-ui.css on the frontend', 'flexible-checkout-fields' ); ?></label>

					<p class="description"><?php _e( 'Remember that some fields, i.e. datepicker use jQuery UI CSS. The plugin adds a default CSS but sometimes it can create some visual glitches.', 'flexible-checkout-fields' ); ?></p>

				</td>
			</tr>
			<?php do_action( 'flexible_checkout_fields_settings' ); ?>
	</table>

	<?php do_action('woocommerce_checkout_fields_after_display_tab_settings'); ?>

	<p class="submit">
		<input type="submit" value="<?php _e( 'Save Changes', 'flexible-checkout-fields' ); ?>" class="button button-primary" id="submit" name="">		
	</p>
</form>
