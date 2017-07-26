<?php
global $woocommerce;

$checkout_fields = $args['checkout_fields'];
$settings        = get_option( 'inspire_checkout_fields_settings' );
/*
	$checkout_field_type_name = array(
	    'billing'  => __( 'Billing Fields', 'flexible-checkout-fields' ),
        'shipping' => __( 'Shipping Fields', 'flexible-checkout-fields' ),
        'order'    => __( 'Order Fields', 'flexible-checkout-fields' )
    );

	$checkout_field_type_name = array();

	foreach ( $this->plugin->sections as $custom_section => $custom_section_data ) {
		$checkout_field_type_name[$custom_section_data['section']] = $custom_section_data['title'];
	}
*/

$checkout_field_type = $args['plugin']->get_fields();
?>

<div class="wrap">
	<?php if ( ! empty( $_POST['option_page'] ) && $_POST['option_page'] === 'inspire_checkout_fields_settings' ): ?>
		<?php if ( isset( $_POST['reset_settings'] ) ) : ?>
            <div id="message" class="updated fade">
                <p><strong><?php _e( 'Settings resetted.', 'flexible-checkout-fields' ); ?></strong></p>
            </div>
		<?php endif; ?>
        <div id="message" class="updated fade">
            <p><strong><?php _e( 'Settings saved.', 'flexible-checkout-fields' ); ?></strong></p>
        </div>
	<?php endif; ?>

    <div id="nav-menus-frame" class="wp-clearfix">
        <div id="menu-settings-column" class="metabox-holder add-new-field-box">
            <div id="side-sortables" class="accordion-container">
                <form method="post" action="" id="add-new-field">
                    <h3><?php _e( 'Add New Field', 'flexible-checkout-fields' ); ?></h3>

                    <div class="add-new-field-content accordion-section-content" style="display:block;">
                        <div>
                            <label for="woocommerce_checkout_fields_field_type"><?php _e( 'Field Type', 'flexible-checkout-fields' ); ?></label>

                            <select id="woocommerce_checkout_fields_field_type"
                                    name="woocommerce_checkout_fields_field_type">
								<?php foreach ( $checkout_field_type as $key => $value ): ?>
                                    <option value="<?php echo $key ?>"><?php echo $value['name'] ?></option>
								<?php endforeach; ?>
                            </select>
                        </div>

                        <div id="woocommerce_checkout_fields_field_name_container">
                            <label for="woocommerce_checkout_fields_field_name"><?php _e( 'Label', 'flexible-checkout-fields' ); ?></label>

                            <textarea id="woocommerce_checkout_fields_field_name"
                                      name="woocommerce_checkout_fields_field_name"></textarea>

                            <p class="description"><?php _e( 'You can use HTML.', 'flexible-checkout-fields' ); ?></p>
                        </div>

                        <div id="woocommerce_checkout_fields_field_name_container_pro" style="display:none;">
                            <div class="updated">
                                <?php
                                    $pro_link = get_locale() === 'pl_PL' ? 'https://www.wpdesk.pl/sklep/woocommerce-checkout-fields/' : 'https://www.wpdesk.net/products/flexible-checkout-fields-pro-woocommerce/';
                                ?>
                                <p><?php _e( 'This field is available in the PRO version.', 'flexible-checkout-fields' ); ?> <a href="<?php echo $pro_link; ?>?utm_source=flexible-checkout-fields-settings&utm_medium=link&utm_campaign=flexible-checkout-fields-pro-fields" target="_blank"><?php _e( 'Upgrade to PRO now &rarr;', 'flexible-checkout-fields' ); ?></a></p>
                            </div>
                        </div>

                        <div style="display:none;">
                            <label for="woocommerce_checkout_fields_field_section"><?php _e( 'Section', 'flexible-checkout-fields' ); ?></label>

                            <select id="woocommerce_checkout_fields_field_section"
                                    name="woocommerce_checkout_fields_field_section">
								<?php foreach ( $this->plugin->sections as $custom_section => $custom_section_data ) : ?>
									<?php $selected = ""; ?>
									<?php if ( $custom_section_data['tab'] == $current_tab ) {
										$selected = " selected";
									} ?>
                                    <option value="<?php echo $custom_section_data['section']; ?>" <?php echo $selected; ?>><?php echo $custom_section_data['tab_title']; ?></option>
								<?php endforeach; ?>
                            </select>
                        </div>

                        <p class="list-controls"><?php _e( 'Save changes after adding a field.', 'flexible-checkout-fields' ) ?></p>

                        <p class="button-controls wp-clearfix">
							<span class="add-to-menu">
								<input id="button_add_field" type="submit" name=""
                                       value="<?php _e( 'Add Field', 'flexible-checkout-fields' ) ?>"
                                       class="button-secondary right">
							</span>
                        </p>
                    </div>
                </form>
            </div>

			<?php include( 'settings-ads.php' ); ?>
        </div>

        <div id="menu-management-liquid">
            <div id="menu-management">
                <form method="post" action="" id="inspire_checkout_field" class="nav-menus-php">
					<?php settings_fields( 'inspire_checkout_fields_settings' ); ?>

                    <div class="menu-edit wp-clearfix">
                        <div id="nav-menu-header">
                            <div class="major-publishing-actions wp-clearfix">
                                <h3><?php _e( 'Edit Section', 'flexible-checkout-fields' ) ?></h3>

                                <div class="publishing-action">
                                    <span class="spinner"></span>
                                    <input type="submit" name=""
                                           value="<?php _e( 'Save Changes', 'flexible-checkout-fields' ) ?>"
                                           class="button button-primary">
                                </div>
                            </div>
                        </div>

						<?php foreach ( $checkout_fields as $key => $fields ): ?>
							<?php if ( 'fields_' . $key != $current_tab ) {
								continue;
							} ?>
                            <input type="hidden" name="inspire_checkout_fields[settings][<?php echo $key ?>]" value=""/>
                            <div id="post-body" class="fields-container">
                                <h3><?php _e( 'Section Fields', 'flexible-checkout-fields' ) ?><?php //echo $checkout_field_type_name[$key] ?></h3>

                                <ul class="fields menu sortable" id="<?php echo $key; ?>">
									<?php foreach ( $fields as $name => $field ): ?>
										<?php
										    $field_required = ( ! empty( $settings[ $key ][ $name ]['required'] ) && $settings[ $key ][ $name ]['required'] == '1' ) || ( isset( $field['required'] ) && $field['required'] == 1 && empty( $settings[ $key ][ $name ]['required'] ) );
										    $field_visible  = empty( $settings[ $key ][ $name ]['visible'] );
										    $is_custom_field = ! empty( $settings[ $key ][ $name ]['custom_field'] ) and $settings[ $key ][ $name ]['custom_field'] == 1;
										?>

                                        <li class="field-item menu-item<?php if ( ! $field_visible ): ?> field-hidden<?php endif; ?>">
                                            <div class="menu-item-bar">
                                                <div class="menu-item-handle field-item-handle">
													<?php if ( ! empty( $settings[ $key ][ $name ]['custom_field'] ) ): ?>
                                                        <input type="hidden"
                                                               name="inspire_checkout_fields[settings][<?php echo $key ?>][<?php echo $name ?>][custom_field]"
                                                               value="1"/>
													<?php endif; ?>

                                                    <input type="hidden"
                                                           name="inspire_checkout_fields[settings][<?php echo $key ?>][<?php echo $name ?>][name]"
                                                           value="<?php echo $name ?>"/>

                                                    <span class="item-title">
								    	    			<?php if ( ! empty( $field['label'] ) ): ?>
													        <?php echo wp_strip_all_tags( $field['label'] ); ?>
												        <?php else: ?>
													        <?php echo $name ?>
												        <?php endif; ?>

														<?php if ( $field_required ): ?> *<?php endif; ?>
													</span>

                                                    <span class="item-controls">
								    	    			<a href="#"
                                                           class="item-edit more"><?php _e( 'Edit', 'flexible-checkout-fields' ) ?></a>
													</span>
                                                </div>
                                            </div>

                                            <div class="menu-item-settings field-settings">
                                                <div class="nav-tab-wrapper">
                                                    <a href="#general"
                                                       class="nav-tab nav-tab-active"><?php _e( 'General', 'flexible-checkout-fields' ); ?></a>
                                                    <a class="nav-tab"
                                                       href="#apperance"><?php _e( 'Appearance', 'flexible-checkout-fields' ); ?></a>
													<?php
													$additional_tabs = apply_filters( 'flexible_checkout_fields_field_tabs', array() );
													foreach ( $additional_tabs as $additional_tab ) {
														?>
                                                        <a class="nav-tab"
                                                           href="#<?php echo $additional_tab['hash']; ?>"><?php echo $additional_tab['title']; ?></a>
														<?php
													}
													?>
                                                </div>
                                                <div class="field-settings-tab-container field-settings-general">
													<?php if ( $is_custom_field ): ?>
														<?php if ( isset( $checkout_field_type[ $settings[ $key ][ $name ]['type'] ]['description'] ) ) : ?>
                                                            <div class="element-<?php echo $settings[ $key ][ $name ]['type']; ?>-description show">
                                                                <p class="description"><?php echo $checkout_field_type[ $settings[ $key ][ $name ]['type'] ]['description']; ?></p>
                                                            </div>
														<?php endif; ?>
													<?php endif; ?>


                                                    <div>
                                                        <input type="hidden"
                                                               name="inspire_checkout_fields[settings][<?php echo $key ?>][<?php echo $name ?>][visible]"
                                                               value="1"/>

                                                        <label>
                                                            <input type="checkbox"
                                                                   name="inspire_checkout_fields[settings][<?php echo $key ?>][<?php echo $name ?>][visible]"
                                                                   value="0" <?php if ( $field_visible ): ?> checked<?php endif; ?>>
															<?php _e( 'Enable Field', 'flexible-checkout-fields' ) ?>
                                                        </label>
                                                    </div>

													<?php
													$checked = '';
													$style   = '';
													if ( isset( $settings[ $key ][ $name ]['type'] )
													     && isset( $checkout_field_type[ $settings[ $key ][ $name ]['type'] ]['has_required'] )
													     && $checkout_field_type[ $settings[ $key ][ $name ]['type'] ]['has_required'] == false
													) {
														$style = ' display:none; ';
													} else {
														if ( $field_required ) {
															$checked = ' checked';
														}
													}
													?>
                                                    <div style="<?php echo $style; ?>">
                                                        <input type="hidden"
                                                               name="inspire_checkout_fields[settings][<?php echo $key ?>][<?php echo $name ?>][required]"
                                                               value="0"/>

                                                        <label>
                                                            <input type="checkbox"
                                                                   name="inspire_checkout_fields[settings][<?php echo $key ?>][<?php echo $name ?>][required]"
                                                                   value="1" <?php echo $checked; ?>>
															<?php _e( 'Required Field', 'flexible-checkout-fields' ) ?>
                                                        </label>
                                                    </div>

                                                    <div class="field-type-label">

                                                        <label for="label_<?php echo $name ?>"><?php _e( 'Label', 'flexible-checkout-fields' ) ?></label>

                                                        <?php
                                                            $disabled = '';
                                                            $tip = '';
                                                            if ( in_array( $name, array(
                                                                    'billing_city', 'billing_state', 'billing_postcode',
	                                                                'shipping_city', 'shipping_state', 'shipping_postcode'
                                                            ) ) ) {
                                                                $disabled = 'disabled';
                                                                $tip = __( 'This field is address locale dependent and cannot be modified.', 'flexible-checkout-fields' );
                                                                ?>
	                                                            <span class="woocommerce-help-tip" data-tip="<?php echo $tip;?>"></span>
                                                                <?php
                                                            }
                                                        ?>

                                                        <textarea <?php echo $tip; ?> <?php echo $disabled; ?> data-field="<?php echo $name; ?>" class="fcf_label" id="label_<?php echo $name ?>" class="field-name"
                                                                  name="inspire_checkout_fields[settings][<?php echo $key ?>][<?php echo $name ?>][label]"><?php if ( isset( $settings[ $key ][ $name ]['label'] ) ): echo stripslashes( $settings[ $key ][ $name ]['label'] );
															elseif ( isset( $field['label'] ) ): echo $field['label']; endif; ?></textarea>

                                                        <p class="description"><?php _e( 'You can use HTML.', 'flexible-checkout-fields' ); ?></p>
                                                    </div>

													<?php if ( $is_custom_field ): ?>
														<?php
														$required = '';
														if ( isset( $checkout_field_type[ $settings[ $key ][ $name ]['type'] ]['has_options'] ) && $checkout_field_type[ $settings[ $key ][ $name ]['type'] ]['has_options'] ) {
															$required = ' required';
														}
														?>
                                                        <div class="element-option<?php if ( isset( $checkout_field_type[ $settings[ $key ][ $name ]['type'] ]['has_options'] ) && $checkout_field_type[ $settings[ $key ][ $name ]['type'] ]['has_options'] )
															echo " show" ?>">
                                                            <label for="option_<?php echo $name ?>"><?php _e( 'Options', 'flexible-checkout-fields' ) ?></label>

                                                            <textarea data-field="<?php echo $name; ?>" class="fcf_options" id="option_<?php echo $name ?>"
                                                                      name="inspire_checkout_fields[settings][<?php echo $key ?>][<?php echo $name ?>][option]" <?php echo $required; ?>><?php echo isset( $settings[ $key ][ $name ]['option'] ) ? esc_textarea( stripslashes( $settings[ $key ][ $name ]['option'] ) ) : ''; ?></textarea>

                                                            <p><?php _e( 'Format: <code>Value : Name</code>. Value will be in the code, name will be visible to the user. One option per line. Example:<br /><code>woman : I am a woman</code><br /><code>man : I am a man</code>', 'flexible-checkout-fields' ) ?></p>
                                                        </div>
													<?php endif; ?>

													<?php if ( $is_custom_field ): ?>
														<?php do_action( 'flexible_checkout_fields_settings_html', $key, $name, $settings ); ?>
                                                        <div>
                                                            <label for="type_<?php echo $name ?>"><?php _e( 'Field Type', 'flexible-checkout-fields' ) ?></label>

                                                            <select id="woocommerce_checkout_fields_field_type"
                                                                    name="inspire_checkout_fields[settings][<?php echo $key ?>][<?php echo $name ?>][type]"
                                                                    disabled>
																<?php foreach ( $checkout_field_type as $type_key => $value ): ?>
                                                                    <option value="<?php echo $type_key ?>"<?php if ( $settings[ $key ][ $name ]['type'] == $type_key ) {
																		echo " selected";
																	} ?>><?php echo $value['name'] ?></option>
																<?php endforeach; ?>
                                                            </select>
                                                        </div>
													<?php endif; ?>
                                                </div>
                                                <div class="field-settings-tab-container field-settings-apperance" style="display:none;">
													<?php if ( ! $is_custom_field || empty( $checkout_field_type[ $settings[ $key ][ $name ]['type'] ]['disable_placeholder'] ) || ! $checkout_field_type[ $settings[ $key ][ $name ]['type'] ]['disable_placeholder'] ): ?>
                                                        <div class="field_placeholder">
															<?php
															$required = '';
															if ( isset( $settings[ $key ][ $name ]['type'] ) && isset( $checkout_field_type[ $settings[ $key ][ $name ]['type'] ]['label_is_required'] ) ) {
																$required = ' required';
															}
															?>
                                                            <label for="placeholder_<?php echo $name ?>"><?php if ( $is_custom_field && isset( $checkout_field_type[ $settings[ $key ][ $name ]['type'] ]['placeholder_label'] ) ): ?><?php echo $checkout_field_type[ $settings[ $key ][ $name ]['type'] ]['placeholder_label']; ?><?php else: ?><?php _e( 'Placeholder', 'flexible-checkout-fields' ) ?><?php endif; ?></label>

	                                                        <?php
                                                                $disabled = '';
                                                                $tip = '';
                                                                if ( in_array( $name, array(
                                                                    'billing_city', 'billing_state', 'billing_postcode', 'billing_country',
                                                                    'shipping_city', 'shipping_state', 'shipping_postcode', 'shipping_country'
                                                                ) ) ) {
		                                                            $disabled = 'disabled';
                                                                    $tip = __( 'This field is address locale dependent and cannot be modified.', 'flexible-checkout-fields' );
                                                                    ?>
                                                                    <span class="woocommerce-help-tip" data-tip="<?php echo $tip;?>"></span>
                                                                    <?php
                                                                }
	                                                        ?>

                                                            <input <?php echo $disabled; ?> type="text" id="placeholder_<?php echo $name ?>"
                                                                   name="inspire_checkout_fields[settings][<?php echo $key ?>][<?php echo $name ?>][placeholder]"
                                                                   value="<?php if ( ! empty( $settings[ $key ][ $name ]['placeholder'] ) ): echo $settings[ $key ][ $name ]['placeholder'];
															       else: echo isset( $field['placeholder'] ) ? $field['placeholder'] : ''; endif; ?>" <?php echo $required; ?> />
                                                        </div>
													<?php endif; ?>
                                                    <div>
                                                        <label for="class_<?php echo $name ?>"><?php _e( 'CSS Class', 'flexible-checkout-fields' ) ?></label>
                                                        <input type="text" id="class_<?php echo $name ?>"
                                                               name="inspire_checkout_fields[settings][<?php echo $key ?>][<?php echo $name ?>][class]"
                                                               value="<?php if ( ! empty( $settings[ $key ][ $name ]['class'] ) ): echo $settings[ $key ][ $name ]['class'];
														       else: if ( ! empty( $field['class'] ) ) {
															       echo implode( ' ', $field['class'] );
														       } endif; ?>"/>
                                                    </div>
                                                </div>
												<?php do_action( 'flexible_checkout_fields_field_tabs_content', $key, $name, $field, $settings ); ?>
												<?php if ( $is_custom_field ) : ?>
                                                    <a class="remove-field" data-field="<?php echo $name; ?>"
                                                       href="#"><?php _e( 'Delete Field', 'flexible-checkout-fields' ) ?></a>
												<?php endif; ?>
                                            </div>
                                        </li>
									<?php endforeach; ?>
                                </ul>
                            </div>

							<?php do_action( 'flexible_checkout_fields_section_settings', $key, $settings ); ?>

						<?php endforeach; ?>

                        <div id="nav-menu-footer">
                            <div class="major-publishing-actions wp-clearfix">
                                <input type="submit" name=""
                                       value="<?php _e( 'Save Changes', 'flexible-checkout-fields' ) ?>"
                                       class="button button-primary">
                                <input type="submit"
                                       value="<?php _e( 'Reset Section Settings', 'flexible-checkout-fields' ); ?>"
                                       class="button reset_settings" id="submit" name="reset_settings">
                                <span class="spinner"></span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery('.sortable').sortable({
            handle: '.field-item-handle',
            placeholder: 'sortable-placeholder',
            opacity: 0.7,
            activate: function (event, ui) {
                ui.item.find('.field-settings').hide();
            }
        });

        // Add New Field
        jQuery("#add-new-field").submit(function (e) {
            var field_name = jQuery(this).find('#woocommerce_checkout_fields_field_name').val();
            var field_section = jQuery(this).find('#woocommerce_checkout_fields_field_section').val();
            var field_type = jQuery(this).find('#woocommerce_checkout_fields_field_type').val();
            var field_option = jQuery(this).find('#woocommerce_checkout_fields_field_option').val();
            //var field_slug        = stringToSlug(field_section + '_' + field_name + '_' + Math.floor((Math.random() * 100000) + 1));
            var field_slug = field_section + '_' + stringToSlug(field_name).substr(0, 20) + '_' + Math.floor((Math.random() * 100000) + 1);

            // Proceed if Name (label) is filled
            if (field_name) {
                var html = '';
                html += '<li class="field-item menu-item element_' + field_slug + ' just-added">';
                html += '<div class="menu-item-bar">';
                html += '<div class="menu-item-handle field-item-handle">';
                html += '<input type="hidden" name="inspire_checkout_fields[settings][' + field_section + '][' + field_slug + '][custom_field]" value="1">';
                html += '<input type="hidden" name="inspire_checkout_fields[settings][' + field_section + '][' + field_slug + '][name]" value="' + field_slug + '">';
                html += '<span class="item-title">';
                html += field_name;
                html += '</span>';
                html += '<span class="item-controls">';
                html += '<a href="#" class="item-edit more"><?php _e( 'Edit', 'flexible-checkout-fields' ) ?></a>';
                html += '</span>';
                html += '</div>';
                html += '</div>';
                html += '<div class="menu-item-settings field-settings">';

                html += '<div class="nav-tab-wrapper">';
                html += '<a href="#general" class="nav-tab nav-tab-active"><?php _e( 'General', 'flexible-checkout-fields' ); ?></a>';
                html += '<a class="nav-tab" href="#apperance"><?php _e( 'Appearance', 'flexible-checkout-fields' ); ?></a>';
				<?php
				$additional_tabs = apply_filters( 'flexible_checkout_fields_field_tabs', array() );
				foreach ( $additional_tabs as $additional_tab ) {
				?>
                html += '<a class="nav-tab" href="#<?php echo $additional_tab['hash']; ?>"><?php echo $additional_tab['title']; ?></a>';
				<?php
				}
				?>
                html += '</div>';
                html += '<div class="field-settings-tab-container field-settings-general">';

				<?php foreach ( $checkout_field_type as $key => $value ) : ?>
				<?php if ( isset( $value['description'] ) ) : ?>
                html += '<div class="element-<?php echo $key; ?>-description">';
                html += '<p class="description"><?php echo $value['description']; ?></p>';
                html += '</div>';
				<?php endif; ?>
				<?php endforeach; ?>
                html += '<div>';
                html += '<input type="hidden" name="inspire_checkout_fields[settings][' + field_section + '][' + field_slug + '][visible]" value="1">';
                html += '<label>';
                html += '<input type="checkbox" name="inspire_checkout_fields[settings][' + field_section + '][' + field_slug + '][visible]" value="0" checked>';
                html += '<?php _e( 'Enable Field', 'flexible-checkout-fields' ) ?>';
                html += '</label>';
                html += '</div>';
                html += '<div>';
                html += '<input type="hidden" name="inspire_checkout_fields[settings][' + field_section + '][' + field_slug + '][required]" value="0">';
                html += '<label>';
                html += '<input type="checkbox" name="inspire_checkout_fields[settings][' + field_section + '][' + field_slug + '][required]" value="1">';
                html += '<?php _e( 'Required Field', 'flexible-checkout-fields' ) ?>';
                html += '</label>';
                html += '</div>';
                html += '<div class="field-type-label">';
                html += '<label class="fcf_label" for="label_' + field_slug + '"><?php _e( 'Label', 'flexible-checkout-fields' ) ?></label>';
                html += '<textarea data-field="' + field_slug + '" id="label_' + field_slug + '" class="fcf_label field-name" name="inspire_checkout_fields[settings][' + field_section + '][' + field_slug + '][label]">' + field_name + '</textarea>';
                html += '<p class="description"><?php _e( 'You can use HTML.', 'flexible-checkout-fields' ); ?></p>';
                html += '</div>';

				<?php do_action( 'flexible_checkout_fields_settings_js_html' ); ?>

                html += '<div>';
                html += '<label for="type_' + field_slug + '"><?php _e( 'Field Type', 'flexible-checkout-fields' ) ?></label>';
                html += '<select id="woocommerce_checkout_fields_field_type" name="inspire_checkout_fields[settings][' + field_section + '][' + field_slug + '][type]" disabled>' + printSelectTypeOptions(field_type) + '</select>';
                html += '</div>';

                html += '</div>';
                html += '<div class="field-settings-tab-container field-settings-apperance" style="display:none;">';

                html += '<div class="field_placeholder">';
                html += '<label for="placeholder_' + field_slug + '"><?php _e( 'Placeholder', 'flexible-checkout-fields' ) ?></label>';
                html += '<input type="text" id="placeholder_' + field_slug + '" name="inspire_checkout_fields[settings][' + field_section + '][' + field_slug + '][placeholder]" value="">';
                html += '</div>';
                html += '<div>';
                html += '<label for="class_' + field_slug + '"><?php _e( 'CSS Class', 'flexible-checkout-fields' ) ?></label>';
                html += '<input type="text" id="class_' + field_slug + '" name="inspire_checkout_fields[settings][' + field_section + '][' + field_slug + '][class]" value="">';
                html += '</div>';

                html += '</div>';

				<?php do_action( 'flexible_checkout_fields_field_tabs_content_js' ); ?>

                html += '<a class="remove-field" href="#"><?php _e( 'Delete Field', 'flexible-checkout-fields' ) ?></a>';
                html += '</li>';
                html += '';

                jQuery('#' + field_section).append(html);
                jQuery('.element_' + field_slug + ' .element-file-description').hide();

                // Add Field Options or Value or Placeholder
                switch (field_type) {

				<?php do_action( 'flexible_checkout_fields_settings_js_options' ); ?>

                    default:
                        jQuery('.element_' + field_slug + ' .field_placeholder label').html('<?php _e( 'Placeholder', 'flexible-checkout-fields' ); ?>');
                        jQuery('.element_' + field_slug + ' .field_placeholder').show();
                        break;
                }
                jQuery(document).trigger("fcf:add_field", [ field_slug ] );
                jQuery(this).find('#woocommerce_checkout_fields_field_name').val('');
            }
            // Display Alert if Name (label) is NOT filled
            else {
                alert('<?php _e( 'Enter field label!', 'flexible-checkout-fields' ) ?>');
            }

            e.preventDefault();
        });

        // Toggle field settings
        jQuery(document).on('click', '.field-item a.more', function (e) {
            e.preventDefault();
            jQuery(this).closest('.field-item').find('.field-settings').slideToggle('fast');
            jQuery(this).closest('.field-item').toggleClass('menu-item-edit-active');
        });

        jQuery(document).on('change', '#woocommerce_checkout_fields_field_type', function (e) {
            <?php if (!is_flexible_checkout_fields_pro_active()) : ?>
                if ( jQuery(this).val() == 'text' || jQuery(this).val() == 'textarea' ) {
                    jQuery('#woocommerce_checkout_fields_field_name_container').show();
                    jQuery('#woocommerce_checkout_fields_field_name_container_pro').hide();
                    jQuery('#button_add_field').prop('disabled',false);
                }
                else {
                    jQuery('#woocommerce_checkout_fields_field_name_container').hide();
                    jQuery('#woocommerce_checkout_fields_field_name_container_pro').show();
                    jQuery('#button_add_field').prop('disabled',true);
                }
            <?php endif; ?>
        })

        // Toggle between placeholder or value
        jQuery(document).on('change', '.field-item .field-settings #woocommerce_checkout_fields_field_type', function (e) {
            switch (jQuery(this).val()) {
                default:
                    jQuery(this).closest('.field-item').find('.element-option').removeClass('show');
                    jQuery(this).closest('.field-item').find('.field_placeholder label').html('<?php _e( 'Placeholder', 'flexible-checkout-fields' ); ?>');
                    jQuery(this).closest('.field-item').find('.field_placeholder').show();
                    break;
            }
            e.preventDefault();
        });

        // Remove field
        jQuery(document).on('click', '.field-item a.remove-field', function (e) {
            e.preventDefault();
            var toRemove = jQuery(this).closest('li');
            if (confirm('<?php _e( 'Do you really want to delete this field: ', 'flexible-checkout-fields' ) ?>' + toRemove.find('.field-name').val() + '?')) {
                jQuery(this).trigger('fcf:remove_field');
                toRemove.remove();
            }
        });

        // When Saving Form Remove disabled from Selects
        jQuery('form').bind('submit', function () {
            jQuery(this).find('select').prop('disabled', false);
            jQuery(this).find('.major-publishing-actions').find('.spinner').css('visibility', 'visible');
            jQuery('.flexible_checkout_fields_add_rule select').each(function () {
                jQuery(this).attr('disabled', 'disabled');
            });
        });

        // Activate Spinner on Save
        jQuery('input[type="submit"]').on('click', function () {
            jQuery('#inspire_checkout_field [required]').each(function () {
                if (jQuery(this).val() == '' && jQuery(this).is(':hidden')) {
                    jQuery(this).closest('li').find('.item-controls>a').click();
                }
                if (jQuery(this).hasClass("reset_settings")) {
                    if (!confirm('<?php _e( 'Please confirm settings reset.', 'flexible-checkout-fields' ); ?>')) {
                        return false;
                    }
                }
            });


        });
    });

	<?php do_action( 'flexible_checkout_fields_java_script', $settings ); ?>

    jQuery(document).on('click', '.field-settings .nav-tab-wrapper > a', function () {
        jQuery(this).parent().find('a').each(function () {
            jQuery(this).removeClass('nav-tab-active');
        });
        jQuery(this).addClass('nav-tab-active');
        jQuery(this).parent().parent().find('.field-settings-tab-container').each(function () {
            jQuery(this).hide();
        });
        var href = jQuery(this).attr("href");
        var hash = href.substr(href.indexOf("#") + 1);
        jQuery(this).parent().parent().find('.field-settings-' + hash).each(function () {
            jQuery(this).show();
        });
        jQuery(this).blur();
        return false;
    });

    function printSelectTypeOptions(selected) {
        var index;
        var select;
        var sel = "";

        var type = {
		<?php foreach ( $checkout_field_type as $key => $value ) : ?>
		<?php echo $key; ?>:
        '<?php echo $value['name']; ?>',
		<?php endforeach; ?>
    }
        ;

        jQuery.each(type, function (key, value) {
            if (key == selected) sel = " selected";
            select += '<option value="' + key + '"' + sel + '>' + value + '</option>';
            sel = "";
        });

        return select;
    }

    function stringToSlug(str) {
        str = str.replace(/^\s+|\s+$/g, '');
        str = str.toLowerCase();

        var from = "àáäâèéëêìíïîòóöôùúüûñçęóąśłżźćń·/_,:;";
        var to = "aaaaeeeeiiiioooouuuunceoaslzxcn------";
        for (var i = 0, l = from.length; i < l; i++) {
            str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
        }

        str = str.replace(/[^a-z0-9 -]/g, '') // remove invalid chars
            .replace(/\s+/g, '_') // collapse whitespace and replace by -
            .replace(/-+/g, '_'); // collapse dashes

        return str;
    }

</script>
