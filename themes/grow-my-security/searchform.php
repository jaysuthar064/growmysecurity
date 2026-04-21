<?php
/**
 * Search form template.
 *
 * @package GrowMySecurity
 */

$search_id = wp_unique_id( 'gms-search-form-' );
?>
<form role="search" method="get" class="gms-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $search_id ); ?>"><?php esc_html_e( 'Search for:', 'grow-my-security' ); ?></label>
	<input id="<?php echo esc_attr( $search_id ); ?>" class="gms-search-form__input" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search insights, services, and pages', 'grow-my-security' ); ?>">
	<button class="gms-button" type="submit"><?php esc_html_e( 'Search', 'grow-my-security' ); ?></button>
</form>