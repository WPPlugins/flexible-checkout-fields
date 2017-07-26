<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<?php
    $pro_link = get_locale() === 'pl_PL' ? 'https://www.wpdesk.pl/sklep/woocommerce-checkout-fields/' : 'https://www.wpdesk.net/products/flexible-checkout-fields-pro-woocommerce/';
?>

<div class="notice notice-success">
    <table>
        <tbody>
            <tr>
                <td width="60%">
                    <p><strong><?php _e( 'Buy Flexible Checkout Fields PRO to use Custom Sections:', 'flexible-checkout-fields' ); ?></strong></p>

                    <ul>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e( 'Add fields anywhere in the WooCommerce checkout form.', 'flexible-checkout-fields' ); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e( 'Get more fields: checkboxes, radios buttons, dropdowns, file uploads, date & time or color pickers and more.', 'flexible-checkout-fields' ); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php _e( 'Conditionally show or hide fields based on products or categories.', 'flexible-checkout-fields' ); ?></li>
                    </ul>
                </td>

                <td>
                    <a class="button button-primary button-hero" href="<?php echo $pro_link; ?>?utm_source=flexible-checkout-fields&utm_campaign=flexible-checkout-fields-custom-sections&utm_medium=button" target="_blank"><?php _e( 'Get Flexible Checkout Fields PRO now &rarr;', 'flexible-checkout-fields' ); ?></a>
                </td>
            </tr>
        </tbody>
    </table>
</div>
