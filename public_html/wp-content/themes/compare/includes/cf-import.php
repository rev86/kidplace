<div class="wrap">

	<h2><?php esc_html_e( 'Import / Export Custom Fields', 'compare' ) ?> </h2>


	<p><?php esc_html_e( 'Click button bellow to get JSON export of your created stores and feeds from custom table', 'compare' ) ?></p>
	<?php $permalink = ( is_ssl() ? 'https' : 'http' ).'://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>
	<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'export' ), $permalink ) ) ?>" class="button"><?php esc_html_e( 'Export', 'compare' ) ?></a>
	<?php
	if( isset( $_GET['action'] ) && $_GET['action'] == 'export' ){
		compare_export_cf_values();
	}
	?>

	<br /><br />
	<hr />

	<p><?php esc_html_e( 'Paste JSON of your custom stores and feed list and click on import button', 'compare' ) ?></p>
	<?php compare_import_cf_values() ?>
	<form method="post" action="<?php echo esc_url( add_query_arg( array( 'action' => 'import' ), $permalink ) ) ?>">
		<textarea name="compare_custom_data" class="cf-import"></textarea>
		<input type="submit" class="button" value="<?php esc_attr_e( 'Import', 'compare' ) ?>">
	</form>

</div>