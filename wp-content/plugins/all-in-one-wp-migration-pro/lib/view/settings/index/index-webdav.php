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
				<h1><i class="ai1wm-icon-gear"></i> <?php _e( 'WebDAV Settings', AI1WMKE_PLUGIN_NAME ); ?></h1>
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

				<div id="ai1wmke-webdav-details">
					<form method="post" action="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ai1wmke_webdav_connection' ), 'ai1wmke_webdav_connection' ) ); ?>" enctype="multipart/form-data">
						<div class="ai1wm-field">
							<?php _e( 'Type', AI1WMKE_PLUGIN_NAME ); ?>
							<br />
							<div style="margin: 6px 0 8px 0;">
								<label for="ai1wmke-webdav-type-webdav">
									<input type="radio" id="ai1wmke-webdav-type-webdav" name="ai1wmke_webdav_type" class="ai1wmke-webdav-settings-type" value="webdav" <?php echo $type === 'webdav' ? 'checked="checked"' : null; ?> />
									<?php _e( 'WebDAV', AI1WMKE_PLUGIN_NAME ); ?>
								</label>
								<label for="ai1wmke-webdav-type-webdavs">
									<input type="radio" id="ai1wmke-webdav-type-webdavs" name="ai1wmke_webdav_type" class="ai1wmke-webdav-settings-type" value="webdavs" <?php echo $type === 'webdavs' ? 'checked="checked"' : null; ?> />
									<?php _e( 'WebDAV with HTTPS', AI1WMKE_PLUGIN_NAME ); ?>
								</label>
							</div>
						</div>

						<div class="ai1wm-field">
							<label for="ai1wmke-webdav-hostname">
								<?php _e( 'Hostname', AI1WMKE_PLUGIN_NAME ); ?>
								<br />
								<input type="text" placeholder="<?php _e( 'Enter Hostname', AI1WMKE_PLUGIN_NAME ); ?>" id="ai1wmke-webdav-hostname" name="ai1wmke_webdav_hostname" class="ai1wmke-webdav-settings-hostname" value="<?php echo esc_attr( $hostname ); ?>" />
							</label>
						</div>

						<div class="ai1wm-field">
							<?php _e( 'Authentication type', AI1WMKE_PLUGIN_NAME ); ?>
							<br />
							<div style="margin: 6px 0 8px 0;">
								<label for="ai1wmke-webdav-authentication-basic">
									<input type="radio" id="ai1wmke-webdav-authentication-basic" name="ai1wmke_webdav_authentication" class="ai1wmke-webdav-settings-authentication" value="basic" <?php echo $authentication === 'basic' ? 'checked="checked"' : null; ?> />
									<?php _e( 'Basic', AI1WMKE_PLUGIN_NAME ); ?>
								</label>
								<label for="ai1wmke-webdav-authentication-digest">
									<input type="radio" id="ai1wmke-webdav-authentication-digest" name="ai1wmke_webdav_authentication" class="ai1wmke-webdav-settings-authentication" value="digest" <?php echo $authentication === 'digest' ? 'checked="checked"' : null; ?> />
									<?php _e( 'Digest', AI1WMKE_PLUGIN_NAME ); ?>
								</label>
								<label for="ai1wmke-webdav-authentication-ntlm">
									<input type="radio" id="ai1wmke-webdav-authentication-ntlm" name="ai1wmke_webdav_authentication" class="ai1wmke-webdav-settings-authentication" value="ntlm" <?php echo $authentication === 'ntlm' ? 'checked="checked"' : null; ?> />
									<?php _e( 'NTLM', AI1WMKE_PLUGIN_NAME ); ?>
								</label>
							</div>
						</div>

						<div class="ai1wm-field">
							<label for="ai1wmke-webdav-username">
								<?php _e( 'Username', AI1WMKE_PLUGIN_NAME ); ?>
								<br />
								<input type="text" placeholder="<?php _e( 'Enter Username', AI1WMKE_PLUGIN_NAME ); ?>" id="ai1wmke-webdav-username" name="ai1wmke_webdav_username" class="ai1wmke-webdav-settings-username" value="<?php echo esc_attr( $username ); ?>" />
							</label>
						</div>

						<div class="ai1wm-field">
							<label for="ai1wmke-webdav-password">
								<?php _e( 'Password', AI1WMKE_PLUGIN_NAME ); ?>
								<br />
								<input type="password" placeholder="<?php echo $password ? str_repeat( '*', strlen( $password ) ) : __( 'Enter Password', AI1WMKE_PLUGIN_NAME ); ?>" id="ai1wmke-webdav-password" name="ai1wmke_webdav_password" class="ai1wmke-webdav-settings-password" autocomplete="off" />
							</label>
						</div>

						<div class="ai1wm-field">
							<label for="ai1wmke-webdav-directory">
								<?php _e( 'Root directory', AI1WMKE_PLUGIN_NAME ); ?>
								<br />
								<input type="text" placeholder="<?php _e( 'Enter Root directory', AI1WMKE_PLUGIN_NAME ); ?>" id="ai1wmke-webdav-directory" name="ai1wmke_webdav_directory" class="ai1wmke-webdav-settings-directory" value="<?php echo esc_attr( $directory ); ?>" />
							</label>
						</div>

						<div class="ai1wm-field">
							<label for="ai1wmke-webdav-port">
								<?php _e( 'Port', AI1WMKE_PLUGIN_NAME ); ?>
								<br />
								<input type="number" min="1" max="65535" placeholder="<?php _e( 'Enter Port', AI1WMKE_PLUGIN_NAME ); ?>" id="ai1wmke-webdav-port" name="ai1wmke_webdav_port" class="ai1wmke-webdav-settings-port" value="<?php echo esc_attr( $port ); ?>" />
							</label>
						</div>

						<p>
							<button type="submit" class="ai1wm-button-blue" name="ai1wmke_webdav_link" id="ai1wmke-webdav-link">
								<i class="ai1wm-icon-enter"></i>
								<?php _e( 'Update', AI1WMKE_PLUGIN_NAME ); ?>
							</button>
						</p>
					</form>
				</div>
			</div>

			<?php if ( $connection ) : ?>
				<div id="ai1wmke-webdav-config" class="ai1wm-holder">
					<h1><i class="ai1wm-icon-gear"></i> <?php _e( 'WebDAV Backups', AI1WMKE_PLUGIN_NAME ); ?></h1>
					<br />
					<br />

					<?php if ( Ai1wm_Message::has( 'settings' ) ) : ?>
						<div class="ai1wm-message ai1wm-success-message">
							<p><?php echo Ai1wm_Message::get( 'settings' ); ?></p>
						</div>
					<?php endif; ?>

					<form method="post" action="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ai1wmke_webdav_settings' ), 'ai1wmke_webdav_settings' ) ); ?>">
						<article class="ai1wmke-webdav-article">
							<h3><?php _e( 'Configure your backup plan', AI1WMKE_PLUGIN_NAME ); ?></h3>

							<p>
								<label for="ai1wmke-webdav-cron-timestamp">
									<?php _e( 'Backup time:', AI1WMKE_PLUGIN_NAME ); ?>
									<input type="text" name="ai1wmke_webdav_cron_timestamp" id="ai1wmke-webdav-cron-timestamp" value="<?php echo esc_attr( get_date_from_gmt( date( 'Y-m-d H:i:s', $webdav_cron_timestamp ), 'g:i a' ) ); ?>" autocomplete="off" />
									<code><?php echo ai1wm_get_timezone_string(); ?></code>
								</label>
							</p>

							<ul id="ai1wmke-webdav-cron">
								<li>
									<label for="ai1wmke-webdav-cron-hourly">
										<input type="checkbox" name="ai1wmke_webdav_cron[]" id="ai1wmke-webdav-cron-hourly" value="hourly" <?php echo in_array( 'hourly', $webdav_backup_schedules ) ? 'checked="checked"' : null; ?> />
										<?php _e( 'Every hour', AI1WMKE_PLUGIN_NAME ); ?>
									</label>
								</li>
								<li>
									<label for="ai1wmke-webdav-cron-daily">
										<input type="checkbox" name="ai1wmke_webdav_cron[]" id="ai1wmke-webdav-cron-daily" value="daily" <?php echo in_array( 'daily', $webdav_backup_schedules ) ? 'checked="checked"' : null; ?> />
										<?php _e( 'Every day', AI1WMKE_PLUGIN_NAME ); ?>
									</label>
								</li>
								<li>
									<label for="ai1wmke-webdav-cron-weekly">
										<input type="checkbox" name="ai1wmke_webdav_cron[]" id="ai1wmke-webdav-cron-weekly" value="weekly" <?php echo in_array( 'weekly', $webdav_backup_schedules ) ? 'checked="checked"' : null; ?> />
										<?php _e( 'Every week', AI1WMKE_PLUGIN_NAME ); ?>
									</label>
								</li>
								<li>
									<label for="ai1wmke-webdav-cron-monthly">
										<input type="checkbox" name="ai1wmke_webdav_cron[]" id="ai1wmke-webdav-cron-monthly" value="monthly" <?php echo in_array( 'monthly', $webdav_backup_schedules ) ? 'checked="checked"' : null; ?> />
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
						</article>

						<article class="ai1wmke-webdav-article">
							<h3><?php _e( 'Notification settings', AI1WMKE_PLUGIN_NAME ); ?></h3>
							<p>
								<label for="ai1wmke-webdav-notify-toggle">
									<input type="checkbox" id="ai1wmke-webdav-notify-toggle" name="ai1wmke_webdav_notify_toggle" <?php echo empty( $notify_ok_toggle ) ? null : 'checked'; ?> />
									<?php _e( 'Send an email when a backup is complete', AI1WMKE_PLUGIN_NAME ); ?>
								</label>
							</p>

							<p>
								<label for="ai1wmke-webdav-notify-error-toggle">
									<input type="checkbox" id="ai1wmke-webdav-notify-error-toggle" name="ai1wmke_webdav_notify_error_toggle" <?php echo empty( $notify_error_toggle ) ? null : 'checked'; ?> />
									<?php _e( 'Send an email if a backup fails', AI1WMKE_PLUGIN_NAME ); ?>
								</label>
							</p>

							<p>
								<label for="ai1wmke-webdav-notify-email">
									<?php _e( 'Email address', AI1WMKE_PLUGIN_NAME ); ?>
									<br />
									<input class="ai1wmke-webdav-email" style="width: 15rem;" type="email" id="ai1wmke-webdav-notify-email" name="ai1wmke_webdav_notify_email" value="<?php echo esc_attr( $notify_email ); ?>" />
								</label>
							</p>
						</article>

						<article class="ai1wmke-webdav-article">
							<h3><?php _e( 'Retention settings', AI1WMKE_PLUGIN_NAME ); ?></h3>
							<p>
								<div class="ai1wm-field">
									<label for="ai1wmke-webdav-backups">
										<?php _e( 'Keep the most recent', AI1WMKE_PLUGIN_NAME ); ?>
										<input style="width: 4.5em;" type="number" min="0" name="ai1wmke_webdav_backups" id="ai1wmke-webdav-backups" value="<?php echo intval( $backups ); ?>" />
									</label>
									<?php _e( 'backups. <small>Default: <strong>0</strong> unlimited</small>', AI1WMKE_PLUGIN_NAME ); ?>
								</div>

								<div class="ai1wm-field">
									<label for="ai1wmke-webdav-total">
										<?php _e( 'Limit the total size of backups to', AI1WMKE_PLUGIN_NAME ); ?>
										<input style="width: 4.5em;" type="number" min="0" name="ai1wmke_webdav_total" id="ai1wmke-webdav-total" value="<?php echo intval( $total ); ?>" />
									</label>
									<select style="margin-top: -2px;" name="ai1wmke_webdav_total_unit" id="ai1wmke-webdav-total-unit">
										<option value="MB" <?php echo strpos( $total, 'MB' ) !== false ? 'selected="selected"' : null; ?>><?php _e( 'MB', AI1WMKE_PLUGIN_NAME ); ?></option>
										<option value="GB" <?php echo strpos( $total, 'GB' ) !== false ? 'selected="selected"' : null; ?>><?php _e( 'GB', AI1WMKE_PLUGIN_NAME ); ?></option>
									</select>
									<?php _e( '<small>Default: <strong>0</strong> unlimited</small>', AI1WMKE_PLUGIN_NAME ); ?>
								</div>

								<div class="ai1wm-field">
									<label for="ai1wmke-webdav-days">
										<?php _e( 'Remove backups older than ', AI1WMKE_PLUGIN_NAME ); ?>
										<input style="width: 4.5em;" type="number" min="0" name="ai1wmke_webdav_days" id="ai1wmke-webdav-days" value="<?php echo intval( $days ); ?>" />
									</label>
									<?php _e( 'days. <small>Default: <strong>0</strong> off</small>', AI1WMKE_PLUGIN_NAME ); ?>
								</div>
							</p>
						</article>

						<article class="ai1wmke-webdav-article">
							<h3><?php _e( 'Transfer settings', AI1WMKE_PLUGIN_NAME ); ?></h3>
							<div class="ai1wm-field">
								<label><?php _e( 'Slow Internet (Home)', AI1WMKE_PLUGIN_NAME ); ?></label>
								<input name="ai1wmke_webdav_file_chunk_size" min="5242880" max="20971520" step="5242880" type="range" value="<?php echo $file_chunk_size; ?>" id="ai1wmke-webdav-file-chunk-size" />
								<label><?php _e( 'Fast Internet (Internet Servers)', AI1WMKE_PLUGIN_NAME ); ?></label>
							</div>
						</article>

						<p>
							<button type="submit" class="ai1wm-button-blue" name="ai1wmke_webdav_update" id="ai1wmke-webdav-update">
								<i class="ai1wm-icon-database"></i>
								<?php _e( 'Update', AI1WMKE_PLUGIN_NAME ); ?>
							</button>
						</p>
					</form>
				</div>
			<?php endif; ?>

			<?php do_action( 'ai1wmke_webdav_settings_left_end' ); ?>

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
