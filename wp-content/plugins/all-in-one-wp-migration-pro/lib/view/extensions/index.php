<?php
/**
 * Copyright (C) 2014-2025 ServMask Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Attribution: This code is part of the All-in-One WP Migration plugin, developed by
 *
 * ███████╗███████╗██████╗ ██╗   ██╗███╗   ███╗ █████╗ ███████╗██╗  ██╗
 * ██╔════╝██╔════╝██╔══██╗██║   ██║████╗ ████║██╔══██╗██╔════╝██║ ██╔╝
 * ███████╗█████╗  ██████╔╝██║   ██║██╔████╔██║███████║███████╗█████╔╝
 * ╚════██║██╔══╝  ██╔══██╗╚██╗ ██╔╝██║╚██╔╝██║██╔══██║╚════██║██╔═██╗
 * ███████║███████╗██║  ██║ ╚████╔╝ ██║ ╚═╝ ██║██║  ██║███████║██║  ██╗
 * ╚══════╝╚══════╝╚═╝  ╚═╝  ╚═══╝  ╚═╝     ╚═╝╚═╝  ╚═╝╚══════╝╚═╝  ╚═╝
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Kangaroos cannot jump here' );
}
?>

<div class="ai1wm-container">
	<div class="ai1wm-row">
		<div class="ai1wm-left">
			<div class="ai1wm-holder">
				<h1><i class="ai1wm-icon-export"></i> <?php _e( 'Extensions', AI1WMKE_PLUGIN_NAME ); ?></h1>

				<div class="ai1wm-clear"></div>

				<p class="ai1wm-paragraph">
					<?php _e( 'With the All-in-One WP Migration Pro, you can enable all available extensions for All-in-One WP Migration.', AI1WMKE_PLUGIN_NAME ); ?>
				</p>

				<?php if ( Ai1wm_Message::has( 'extensions' ) ) : ?>
					<div class="ai1wm-message ai1wm-success-message">
						<p><?php echo Ai1wm_Message::get( 'extensions' ); ?></p>
					</div>
				<?php elseif ( Ai1wm_Message::has( 'info' ) ) : ?>
					<div class="ai1wm-message ai1wm-info-message">
						<p><?php echo Ai1wm_Message::get( 'info' ); ?></p>
					</div>
				<?php endif; ?>

				<div class="ai1wm-extensions">
					<div class="ai1wm-extensions-list">
						<?php if ( empty( $extensions ) ) : ?>
							<div class="ai1wm-message ai1wm-info-message">
								<p><?php _e( 'No extensions available.', AI1WMKE_PLUGIN_NAME ); ?></p>
							</div>
						<?php else : ?>
							<table class="ai1wm-extensions-table">
								<thead>
									<tr>
										<th><?php _e( 'Name', AI1WMKE_PLUGIN_NAME ); ?></th>
										<th><?php _e( 'Status', AI1WMKE_PLUGIN_NAME ); ?></th>
										<th><?php _e( 'Actions', AI1WMKE_PLUGIN_NAME ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $extensions as $short => $extension ) : ?>
										<tr>
											<td>
												<strong><?php echo esc_html( $extension['title'] ); ?></strong>
												<p><?php echo esc_html( $extension['about'] ); ?></p>
											</td>
											<td>
												<?php if ( ai1wmke_is_enabled( $short ) ) : ?>
												<span class="ai1wm-extension-status ai1wm-extension-status-enabled"><?php _e( 'Active', AI1WMKE_PLUGIN_NAME ); ?></span>
												<?php else : ?>
													<span class="ai1wm-extension-status ai1wm-extension-status-disabled"><?php _e( 'Inactive', AI1WMKE_PLUGIN_NAME ); ?></span>
												<?php endif; ?>
											</td>
											<td>
												<form method="post" action="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ai1wmke_extensions' ), 'ai1wmke_extensions' ) ); ?>">
													<input type="hidden" name="ai1wmke_extension_short" value="<?php echo esc_attr( $short ); ?>" />

													<?php if ( ai1wmke_is_enabled( $short ) ) : ?>
														<button type="submit" class="ai1wm-button-red">
															<i class="ai1wm-icon-close"></i>
															<?php _e( 'Disable', AI1WMKE_PLUGIN_NAME ); ?>
														</button>
													<?php else : ?>
														<button type="submit" class="ai1wm-button-green">
															<i class="ai1wm-icon-checkmark"></i>
															<?php _e( 'Enable', AI1WMKE_PLUGIN_NAME ); ?>
														</button>
													<?php endif; ?>
												</form>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<div class="ai1wm-right">
			<div class="ai1wm-sidebar">
				<div class="ai1wm-segment">
					<?php if ( ! AI1WM_DEBUG ) : ?>
						<?php require_once AI1WM_TEMPLATES_PATH . '/common/share-buttons.php'; ?>
					<?php endif; ?>

					<h2><?php _e( 'Leave Feedback', AI1WMKE_PLUGIN_NAME ); ?></h2>

					<?php require_once AI1WM_TEMPLATES_PATH . '/common/leave-feedback.php'; ?>
				</div>
			</div>
		</div>
	</div>
</div>
