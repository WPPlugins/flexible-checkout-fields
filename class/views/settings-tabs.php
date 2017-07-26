<h2 class="nav-tab-wrapper">
    <?php foreach ( $args['tabs'] as $tab_key => $tab_caption ): ?>
        <?php $active = $args['current_tab'] == $tab_key ? 'nav-tab-active' : '';?>
        <a class="nav-tab <?= $active ?>" href="?page=inspire_checkout_fields_settings&tab=<?= $tab_key ?>"><?php echo $tab_caption; ?></a>
    <?php endforeach; ?>
</h2>

<p><?php _e( 'Read the <a href="https://www.wpdesk.net/docs/flexible-checkout-fields-docs/" target="_blank">configuraion manual &rarr;', 'flexible-checkout-fields' ); ?></a></p>
