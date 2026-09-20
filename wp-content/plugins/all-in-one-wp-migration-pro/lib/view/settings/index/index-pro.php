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
				<h1><i class="ai1wm-icon-gear"></i> <?php _e( 'Settings', AI1WMKE_PLUGIN_NAME ); ?></h1>
				<br />
				<br />

				<?php if ( Ai1wm_Message::has( 'settings' ) ) : ?>
					<div class="ai1wm-message ai1wm-success-message">
						<p><?php echo Ai1wm_Message::get( 'settings' ); ?></p>
					</div>
				<?php endif; ?>

				<form method="post" action="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ai1wmke_pro_settings' ), 'ai1wmke_pro_settings' ) ); ?>">

					<article class="ai1wmke-pro-article">
						<h3><?php _e( 'Retention settings', AI1WMKE_PLUGIN_NAME ); ?></h3>
						<p>
							<div class="ai1wm-field">
								<label for="ai1wmke-pro-backups">
									<?php _e( 'Keep the most recent', AI1WMKE_PLUGIN_NAME ); ?>
									<input style="width: 4.5em;" type="number" min="0" name="ai1wmke_pro_backups" id="ai1wmke-pro-backups" value="<?php echo intval( $backups ); ?>" />
								</label>
								<?php _e( 'backups. <small>Default: <strong>0</strong> unlimited</small>', AI1WMKE_PLUGIN_NAME ); ?>
							</div>

							<div class="ai1wm-field">
								<label for="ai1wmke-pro-total">
									<?php _e( 'Limit the total size of backups to', AI1WMKE_PLUGIN_NAME ); ?>
									<input style="width: 4.5em;" type="number" min="0" name="ai1wmke_pro_total" id="ai1wmke-pro-total" value="<?php echo intval( $total ); ?>" />
								</label>
								<select style="margin-top: -2px;" name="ai1wmke_pro_total_unit" id="ai1wmke-pro-total-unit">
									<option value="MB" <?php echo strpos( $total, 'MB' ) !== false ? 'selected="selected"' : null; ?>><?php _e( 'MB', AI1WMKE_PLUGIN_NAME ); ?></option>
									<option value="GB" <?php echo strpos( $total, 'GB' ) !== false ? 'selected="selected"' : null; ?>><?php _e( 'GB', AI1WMKE_PLUGIN_NAME ); ?></option>
								</select>
								<?php _e( '<small>Default: <strong>0</strong> unlimited</small>', AI1WMKE_PLUGIN_NAME ); ?>
							</div>

							<div class="ai1wm-field">
								<label for="ai1wmke-pro-days">
									<?php _e( 'Remove backups older than ', AI1WMKE_PLUGIN_NAME ); ?>
									<input style="width: 4.5em;" type="number" min="0" name="ai1wmke_pro_days" id="ai1wmke-pro-days" value="<?php echo intval( $days ); ?>" />
								</label>
								<?php _e( 'days. <small>Default: <strong>0</strong> off</small>', AI1WMKE_PLUGIN_NAME ); ?>
							</div>

							<div class="ai1wm-field" id="ai1wmke-pro-backups-path">
								<folder-browser destination="<?php echo esc_attr( $destination ); ?>" dir-separator="<?php echo esc_attr( DIRECTORY_SEPARATOR ); ?>">
									<h3><?php _e( 'Backups directory', AI1WMKE_PLUGIN_NAME ); ?></h3>
								</folder-browser>
							</div>
						</p>
					</article>
					<p>
						<button type="submit" class="ai1wm-button-blue" name="ai1wmke_pro_update" id="ai1wmke-pro-update">
							<i class="ai1wm-icon-database"></i>
							<?php _e( 'Update', AI1WMKE_PLUGIN_NAME ); ?>
						</button>
					</p>
				</form>
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
