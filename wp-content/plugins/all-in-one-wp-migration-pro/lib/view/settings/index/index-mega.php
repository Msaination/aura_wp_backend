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
				<h1><i class="ai1wm-icon-gear"></i> <?php _e( 'Mega Settings', AI1WMKE_PLUGIN_NAME ); ?></h1>
				<br />
				<br />

				<?php if ( Ai1wm_Message::has( 'success' ) ) : ?>
					<div class="ai1wm-message ai1wm-success-message">
						<p><?php echo Ai1wm_Message::get( 'success' ); ?></p>
					</div>
				<?php elseif ( Ai1wm_Message::has( 'error' ) ) : ?>
					<div class="ai1wm-message ai1wm-error-message">
						<p><?php echo Ai1wm_Message::get( 'error' ); ?></p>
					</div>
				<?php endif; ?>

				<?php if ( $user_session ) : ?>
					<p id="ai1wmke-mega-details">
						<?php _e( 'Retrieving Mega account details..', AI1WMKE_PLUGIN_NAME ); ?>
					</p>

					<div id="ai1wmke-mega-progress">
						<div id="ai1wmke-mega-progress-bar"></div>
					</div>

					<p id="ai1wmke-mega-space"></p>

					<form method="post" action="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ai1wmke_mega_revoke' ), 'ai1wmke_mega_revoke' ) ); ?>">
						<button type="submit" class="ai1wm-button-red" style="float: left;" name="ai1wmke_mega_logout" id="ai1wmke-mega-logout">
							<i class="ai1wm-icon-exit"></i>
							<?php _e( 'Sign Out from your mega account', AI1WMKE_PLUGIN_NAME ); ?>
						</button>
						<span class="spinner" style="float: left;"></span>
						<div class="ai1wm-clear"></div>
					</form>
				<?php else : ?>
					<div id="ai1wmke-mega-credentials">
						<form method="post" action="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ai1wmke_mega_connection' ), 'ai1wmke_mega_connection' ) ); ?>">
							<div class="ai1wm-field">
								<label for="ai1wmke-mega-user-email">
									<?php _e( 'E-mail', AI1WMKE_PLUGIN_NAME ); ?>
									<br />
									<input type="text" placeholder="<?php _e( 'Enter user email', AI1WMKE_PLUGIN_NAME ); ?>" id="ai1wmke-mega-user-email" class="ai1wmke-mega-settings-key" name="ai1wmke_mega_user_email" value="<?php echo esc_attr( $user_email ); ?>" />
								</label>
							</div>

							<div class="ai1wm-field">
								<label for="ai1wmke-mega-user-password">
									<?php _e( 'Password', AI1WMKE_PLUGIN_NAME ); ?>
									<br />
									<input type="password" placeholder="<?php _e( 'Enter user password', AI1WMKE_PLUGIN_NAME ); ?>" id="ai1wmke-mega-user-password" class="ai1wmke-mega-settings-key" name="ai1wmke_mega_user_password" value="<?php echo esc_attr( $user_password ); ?>" autocomplete="off" />
								</label>
							</div>

							<p>
								<button type="submit" class="ai1wm-button-blue" style="float: left;" name="ai1wmke_mega_link" id="ai1wmke-mega-link">
									<i class="ai1wm-icon-enter"></i>
									<?php _e( 'Sign in', AI1WMKE_PLUGIN_NAME ); ?>
								</button>
								<span class="spinner" style="float: left;"></span>
								<div class="ai1wm-clear"></div>
							</p>
						</form>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $user_session ) : ?>
				<div id="ai1wmke-mega-config" class="ai1wm-holder">
					<h1><i class="ai1wm-icon-gear"></i> <?php _e( 'Mega Backups', AI1WMKE_PLUGIN_NAME ); ?></h1>
					<br />
					<br />

					<?php if ( Ai1wm_Message::has( 'settings' ) ) : ?>
						<div class="ai1wm-message ai1wm-success-message">
							<p><?php echo Ai1wm_Message::get( 'settings' ); ?></p>
						</div>
					<?php endif; ?>

					<form method="post" action="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ai1wmke_mega_settings' ), 'ai1wmke_mega_settings' ) ); ?>">
						<article class="ai1wmke-mega-article">
							<h3><?php _e( 'Configure your backup plan', AI1WMKE_PLUGIN_NAME ); ?></h3>

							<p>
								<label for="ai1wmke-mega-cron-timestamp">
									<?php _e( 'Backup time:', AI1WMKE_PLUGIN_NAME ); ?>
									<input type="text" name="ai1wmke_mega_cron_timestamp" id="ai1wmke-mega-cron-timestamp" value="<?php echo esc_attr( get_date_from_gmt( date( 'Y-m-d H:i:s', $mega_cron_timestamp ), 'g:i a' ) ); ?>" autocomplete="off" />
									<code><?php echo ai1wm_get_timezone_string(); ?></code>
								</label>
							</p>

							<ul id="ai1wmke-mega-cron">
								<li>
									<label for="ai1wmke-mega-cron-hourly">
										<input type="checkbox" name="ai1wmke_mega_cron[]" id="ai1wmke-mega-cron-hourly" value="hourly" <?php echo in_array( 'hourly', $mega_backup_schedules ) ? 'checked' : null; ?> />
										<?php _e( 'Every hour', AI1WMKE_PLUGIN_NAME ); ?>
									</label>
								</li>
								<li>
									<label for="ai1wmke-mega-cron-daily">
										<input type="checkbox" name="ai1wmke_mega_cron[]" id="ai1wmke-mega-cron-daily" value="daily" <?php echo in_array( 'daily', $mega_backup_schedules ) ? 'checked' : null; ?> />
										<?php _e( 'Every day', AI1WMKE_PLUGIN_NAME ); ?>
									</label>
								</li>
								<li>
									<label for="ai1wmke-mega-cron-weekly">
										<input type="checkbox" name="ai1wmke_mega_cron[]" id="ai1wmke-mega-cron-weekly" value="weekly" <?php echo in_array( 'weekly', $mega_backup_schedules ) ? 'checked' : null; ?> />
										<?php _e( 'Every week', AI1WMKE_PLUGIN_NAME ); ?>
									</label>
								</li>
								<li>
									<label for="ai1wmke-mega-cron-monthly">
										<input type="checkbox" name="ai1wmke_mega_cron[]" id="ai1wmke-mega-cron-monthly" value="monthly" <?php echo in_array( 'monthly', $mega_backup_schedules ) ? 'checked' : null; ?> />
										<?php _e( 'Every month', AI1WMKE_PLUGIN_NAME ); ?>
									</label>
								</li>
							</ul>

							<p>
								<?php _e( 'Last backup date:', AI1WMKE_PLUGIN_NAME ); ?>
								<strong>
									<?php echo $last_backup_date; ?>
								</strong>
							</p>

							<p>
								<?php _e( 'Next backup date:', AI1WMKE_PLUGIN_NAME ); ?>
								<strong>
									<?php echo $next_backup_date; ?>
								</strong>
							</p>

							<p>
								<label for="ai1wmke-mega-incremental">
									<input type="checkbox" name="ai1wmke_mega_incremental" id="ai1wmke-mega-incremental" value="1" <?php echo empty( $incremental ) ? null : 'checked'; ?> />
									<?php _e( 'Enable incremental backups (optimize backup file size)', AI1WMKE_PLUGIN_NAME ); ?>
								</label>
							</p>
						</article>

						<article class="ai1wmke-mega-article">
							<h3><?php _e( 'Destination folder', AI1WMKE_PLUGIN_NAME ); ?></h3>
							<p id="ai1wmke-mega-folder-details">
								<span class="spinner" style="visibility: visible;"></span>
								<?php _e( 'Retrieving Mega folder details..', AI1WMKE_PLUGIN_NAME ); ?>
							</p>
							<p>
								<input type="hidden" name="ai1wmke_mega_node_id" id="ai1wmke-mega-node-id" />
								<button type="button" class="ai1wm-button-gray" name="ai1wmke_mega_change" id="ai1wmke-mega-change">
									<i class="ai1wm-icon-folder"></i>
									<?php _e( 'Change', AI1WMKE_PLUGIN_NAME ); ?>
								</button>
							</p>
						</article>

						<article class="ai1wmke-mega-article">
							<h3><?php _e( 'Notification settings', AI1WMKE_PLUGIN_NAME ); ?></h3>
							<p>
								<label for="ai1wmke-mega-notify-toggle">
									<input type="checkbox" id="ai1wmke-mega-notify-toggle" name="ai1wmke_mega_notify_toggle" <?php echo empty( $notify_ok_toggle ) ? null : 'checked'; ?> />
									<?php _e( 'Send an email when a backup is complete', AI1WMKE_PLUGIN_NAME ); ?>
								</label>
							</p>

							<p>
								<label for="ai1wmke-mega-notify-error-toggle">
									<input type="checkbox" id="ai1wmke-mega-notify-error-toggle" name="ai1wmke_mega_notify_error_toggle" <?php echo empty( $notify_error_toggle ) ? null : 'checked'; ?> />
									<?php _e( 'Send an email if a backup fails', AI1WMKE_PLUGIN_NAME ); ?>
								</label>
							</p>

							<p>
								<label for="ai1wmke-mega-notify-email">
									<?php _e( 'Email address', AI1WMKE_PLUGIN_NAME ); ?>
									<br />
									<input class="ai1wmke-mega-email" style="width: 15rem;" type="email" id="ai1wmke-mega-notify-email" name="ai1wmke_mega_notify_email" value="<?php echo esc_attr( $notify_email ); ?>" />
								</label>
							</p>
						</article>

						<article class="ai1wmke-mega-article">
							<h3><?php _e( 'Retention settings', AI1WMKE_PLUGIN_NAME ); ?></h3>
							<p>
								<div class="ai1wm-field">
									<label for="ai1wmke-mega-backups">
										<?php _e( 'Keep the most recent', AI1WMKE_PLUGIN_NAME ); ?>
										<input style="width: 4.5em;" type="number" min="0" name="ai1wmke_mega_backups" id="ai1wmke-mega-backups" value="<?php echo intval( $backups ); ?>" />
									</label>
									<?php _e( 'backups. <small>Default: <strong>0</strong> unlimited</small>', AI1WMKE_PLUGIN_NAME ); ?>
								</div>

								<div class="ai1wm-field">
									<label for="ai1wmke-mega-total">
										<?php _e( 'Limit the total size of backups to', AI1WMKE_PLUGIN_NAME ); ?>
										<input style="width: 4.5em;" type="number" min="0" name="ai1wmke_mega_total" id="ai1wmke-mega-total" value="<?php echo intval( $total ); ?>" />
									</label>
									<select style="margin-top: -2px;" name="ai1wmke_mega_total_unit" id="ai1wmke-mega-total-unit">
										<option value="MB" <?php echo strpos( $total, 'MB' ) !== false ? 'selected="selected"' : null; ?>><?php _e( 'MB', AI1WMKE_PLUGIN_NAME ); ?></option>
										<option value="GB" <?php echo strpos( $total, 'GB' ) !== false ? 'selected="selected"' : null; ?>><?php _e( 'GB', AI1WMKE_PLUGIN_NAME ); ?></option>
									</select>
									<?php _e( '<small>Default: <strong>0</strong> unlimited</small>', AI1WMKE_PLUGIN_NAME ); ?>
								</div>

								<div class="ai1wm-field">
									<label for="ai1wmke-mega-days">
										<?php _e( 'Remove backups older than ', AI1WMKE_PLUGIN_NAME ); ?>
										<input style="width: 4.5em;" type="number" min="0" name="ai1wmke_mega_days" id="ai1wmke-mega-days" value="<?php echo intval( $days ); ?>" />
									</label>
									<?php _e( 'days. <small>Default: <strong>0</strong> off</small>', AI1WMKE_PLUGIN_NAME ); ?>
								</div>
							</p>
						</article>

						<article class="ai1wmke-mega-article">
							<h3><?php _e( 'Security settings', AI1WMKE_PLUGIN_NAME ); ?></h3>
							<p>
								<label for="ai1wmke-mega-lock-mode">
									<input type="checkbox" id="ai1wmke-mega-lock-mode" name="ai1wmke_mega_lock_mode" <?php echo empty( $lock_mode ) ? null : 'checked'; ?> />
									<?php printf( __( 'Lock this page for all users except <strong>%s</strong>. <a href="https://help.servmask.com/knowledgebase/lock-settings-page/" target="_blank">More details</a>', AI1WMKE_PLUGIN_NAME ), $user_display_name ); ?>
								</label>
							</p>
						</article>

						<p>
							<button type="submit" class="ai1wm-button-blue" name="ai1wmke_mega_update" id="ai1wmke-mega-update">
								<i class="ai1wm-icon-database"></i>
								<?php _e( 'Update', AI1WMKE_PLUGIN_NAME ); ?>
							</button>
						</p>
					</form>
				</div>
			<?php endif; ?>

			<?php do_action( 'ai1wmke_mega_settings_left_end' ); ?>

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
