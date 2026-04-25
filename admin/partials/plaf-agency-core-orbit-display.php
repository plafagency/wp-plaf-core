<?php

/**
 * Orbit integration settings page for the admin area.
 *
 * @package    Plaf_Agency_Core
 * @subpackage Plaf_Agency_Core/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$orbit_endpoint  = get_option( 'plaf_orbit_endpoint', '' );
$orbit_last_sync = get_option( 'plaf_orbit_last_sync', '' );
?>

<div class="wrap">
	<h1><?php esc_html_e( 'Orbit', 'plaf-agency-core' ); ?></h1>

	<?php if ( isset( $_GET['saved'] ) ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Configuración guardada.', 'plaf-agency-core' ); ?></p>
		</div>
	<?php endif; ?>

	<p class="description">
		<?php esc_html_e( 'Conecta este sitio con una cuenta en Orbit. Generá una API Key desde el tab WordPress de la cuenta en Orbit y pegala acá.', 'plaf-agency-core' ); ?>
	</p>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'plaf_save_orbit', 'plaf_orbit_nonce' ); ?>
		<input type="hidden" name="action" value="plaf_save_orbit" />

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="plaf_orbit_endpoint"><?php esc_html_e( 'Endpoint de Orbit', 'plaf-agency-core' ); ?></label>
				</th>
				<td>
					<input
						type="url"
						id="plaf_orbit_endpoint"
						name="plaf_orbit_endpoint"
						value="<?php echo esc_attr( $orbit_endpoint ); ?>"
						class="regular-text"
						placeholder="https://tu-app.netlify.app/api/wordpress-sync"
					/>
					<p class="description">
						<?php esc_html_e( 'URL de sincronización (ej: https://tu-orbit.com/api/wordpress-sync).', 'plaf-agency-core' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="plaf_orbit_api_key"><?php esc_html_e( 'API Key de Orbit', 'plaf-agency-core' ); ?></label>
				</th>
				<td>
					<input
						type="password"
						id="plaf_orbit_api_key"
						name="plaf_orbit_api_key"
						value=""
						class="regular-text"
						autocomplete="new-password"
						placeholder="<?php echo get_option( 'plaf_orbit_api_key' ) ? esc_attr__( 'Dejá vacío para no cambiarla', 'plaf-agency-core' ) : esc_attr__( 'Pegá tu API Key acá', 'plaf-agency-core' ); ?>"
					/>
					<p class="description">
						<?php esc_html_e( 'Solo ingresá una nueva key para reemplazar la existente.', 'plaf-agency-core' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Sincronización manual', 'plaf-agency-core' ); ?></th>
				<td>
					<button type="button" id="plaf-orbit-sync-btn" class="button button-secondary">
						<?php esc_html_e( 'Sync ahora', 'plaf-agency-core' ); ?>
					</button>
					<span id="plaf-orbit-sync-status" style="margin-left: 10px;">
						<?php if ( $orbit_last_sync ) : ?>
							<?php
							/* translators: %s: datetime */
							printf( esc_html__( 'Último sync: %s', 'plaf-agency-core' ), esc_html( $orbit_last_sync ) );
							?>
						<?php endif; ?>
					</span>
				</td>
			</tr>
		</table>

		<?php submit_button( __( 'Guardar cambios', 'plaf-agency-core' ) ); ?>
	</form>
</div>
