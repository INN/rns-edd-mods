<div class="wrap">

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
	<form action="<?php echo admin_url( 'options.php' ); ?>" method="post">
		<?php settings_fields( $this->option_group ); ?>
		<?php do_settings_sections( $this->menu_slug ); ?>
		<?php submit_button() ?>
	</form>

</div>
