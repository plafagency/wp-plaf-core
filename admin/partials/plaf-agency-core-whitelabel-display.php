<?php

/**
 * White Label settings page for the admin area.
 *
 * @package    Plaf_Agency_Core
 * @subpackage Plaf_Agency_Core/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$client_id  = (int) get_option( 'plaf_client_logo_id', 0 );
$client_src = $client_id ? wp_get_attachment_image_src( $client_id, 'medium' ) : false;
?>

<div class="wrap">
	<h1><?php esc_html_e( 'White Label', 'plaf-agency-core' ); ?></h1>

	<?php if ( isset( $_GET['saved'] ) ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Configuración guardada.', 'plaf-agency-core' ); ?></p>
		</div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'plaf_save_whitelabel', 'plaf_whitelabel_nonce' ); ?>
		<input type="hidden" name="action" value="plaf_save_whitelabel" />

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<?php esc_html_e( 'Logo del cliente', 'plaf-agency-core' ); ?>
				</th>
				<td>
					<div class="plaf-logo-field">
						<input
							type="hidden"
							name="plaf_client_logo_id"
							class="plaf-logo-id"
							value="<?php echo esc_attr( $client_id ?: '' ); ?>"
						/>
						<img
							class="plaf-logo-preview"
							src="<?php echo $client_src ? esc_url( $client_src[0] ) : ''; ?>"
							<?php echo $client_src ? '' : 'style="display:none"'; ?>
							alt=""
						/>
						<div class="plaf-logo-actions">
							<button type="button" class="button plaf-upload-btn">
								<?php echo $client_src ? esc_html__( 'Cambiar logo', 'plaf-agency-core' ) : esc_html__( 'Seleccionar logo', 'plaf-agency-core' ); ?>
							</button>
							<button
								type="button"
								class="button plaf-remove-btn"
								<?php echo $client_src ? '' : 'style="display:none"'; ?>
							>
								<?php esc_html_e( 'Quitar', 'plaf-agency-core' ); ?>
							</button>
						</div>
					</div>
					<p class="description">
						<?php esc_html_e( 'Reemplaza el logo de WordPress en la página de login.', 'plaf-agency-core' ); ?>
					</p>
				</td>
			</tr>

		</table>

		<?php submit_button( __( 'Guardar cambios', 'plaf-agency-core' ) ); ?>
	</form>
</div>
