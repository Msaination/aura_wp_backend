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
				<h1><i class="ai1wm-icon-gear"></i> <?php _e( 'Microsoft Azure Storage Settings', AI1WMKE_PLUGIN_NAME ); ?></h1>
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

				<div id="ai1wmke-azure-storage-credentials">
					<form method="post" action="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ai1wmke_azure_storage_connection' ), 'ai1wmke_azure_storage_connection' ) ); ?>">
						<div class="ai1wm-field">
							<label for="ai1wmke-azure-storage-account-name">
								<?php _e( 'Account Name', AI1WMKE_PLUGIN_NAME ); ?>
								<br />
								<input type="text" placeholder="<?php _e( 'Enter Azure Storage Account Name', AI1WMKE_PLUGIN_NAME ); ?>" id="ai1wmke-azure-storage-account-name" class="ai1wmke-azure-storage-settings-key" name="ai1wmke_azure_storage_account_name" value="<?php echo esc_attr( $account_name ); ?>" />
							</label>
							<a
								href="https://docs.microsoft.com/en-us/azure/storage/common/storage-account-manage#access-keys"
								target="_blank">
								<?php _e( 'How to find your Account Name', AI1WMKE_PLUGIN_NAME ); ?>
							</a>
						</div>

						<div class="ai1wm-field">
							<label for="ai1wmke-azure-storage-account-key">
								<?php _e( 'Account Key', AI1WMKE_PLUGIN_NAME ); ?>
								<br />
								<input type="text" placeholder="<?php ( $account_key ) ? _e( 'Hidden', AI1WMKE_PLUGIN_NAME ) : _e( 'Enter Azure Storage Account Key', AI1WMKE_PLUGIN_NAME ); ?>" id="ai1wmke-azure-storage-account-key" class="ai1wmke-azure-storage-settings-key" name="ai1wmke_azure_storage_account_key" autocomplete="off" />
							</label>
							<a
								href="https://docs.microsoft.com/en-us/azure/storage/common/storage-account-manage#access-keys"
								target="_blank">
								<?php _e( 'How to find your Account Key', AI1WMKE_PLUGIN_NAME ); ?>
							</a>
						</div>

						<p>
							<button type="submit" class="ai1wm-button-blue" name="ai1wmke_azure_storage_update" id="ai1wmke-azure-storage-link">
								<i class="ai1wm-icon-enter"></i>
								<?php _e( 'Update', AI1WMKE_PLUGIN_NAME ); ?>
							</button>
						</p>
					</form>
				</div>
			</div>

			<?php if ( $shares !== false ) : ?>
				<div id="ai1wmke-azure-storage-config" class="ai1wm-holder">
					<h1><i class="ai1wm-icon-gear"></i> <?php _e( 'Microsoft Azure Storage Backups', AI1WMKE_PLUGIN_NAME ); ?></h1>
					<br />
					<br />

					<?php if ( Ai1wm_Message::has( 'settings' ) ) : ?>
						<div class="ai1wm-message ai1wm-success-message">
							<p><?php echo Ai1wm_Message::get( 'settings' ); ?></p>
						</div>
					<?php elseif ( Ai1wm_Message::has( 'share' ) ) : ?>
						<div class="ai1wm-message ai1wm-error-message">
							<p><?php echo Ai1wm_Message::get( 'share' ); ?></p>
						</div>
					<?php endif; ?>

					<form method="post" action="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ai1wmke_azure_storage_settings' ), 'ai1wmke_azure_storage_settings' ) ); ?>">

						<article class="ai1wmke-azure-storage-article">
							<h3><?php _e( 'Share name', AI1WMKE_PLUGIN_NAME ); ?></h3>
							<p id="ai1wmke-azure-storage-share-details">
								<span class="spinner" style="visibility: visible;"></span>
								<?php _e( 'Retrieving Azure Storage share details..', AI1WMKE_PLUGIN_NAME ); ?>
							</p>
							<p>
								<input type="hidden" name="ai1wmke_azure_storage_share_name" id="ai1wmke-azure-storage-share-name" />
								<input type="hidden" name="ai1wmke_azure_storage_folder_name" id="ai1wmke-azure-storage-folder-name" />
								<button type="button" class="ai1wm-button-gray" name="ai1wmke_azure_storage_change" id="ai1wmke-azure-storage-change">
									<i class="ai1wm-icon-folder"></i>
									<?php _e( 'Change', AI1WMKE_PLUGIN_NAME ); ?>
								</button>
							</p>
						</article>

						<article class="ai1wmke-azure-storage-article">
							<h3><?php _e( 'Configure your backup plan', AI1WMKE_PLUGIN_NAME ); ?></h3>
							<p>
								<label for="ai1wmke-azure-storage-cron-timestamp">
									<?php _e( 'Backup time:', AI1WMKE_PLUGIN_NAME ); ?>
									<input type="text" name="ai1wmke_azure_storage_cron_timestamp" id="ai1wmke-azure-storage-cron-timestamp" value="<?php echo esc_attr( get_date_from_gmt( date( 'Y-m-d H:i:s', $azure_cron_timestamp ), 'g:i a' ) ); ?>" autocomplete="off" />
									<code><?php echo ai1wm_get_timezone_string(); ?></code>
								</label>
							</p>
							<ul id="ai1wmke-azure-storage-cron">
								<li>
									<label for="ai1wmke-azure-storage-cron-hourly">
										<input type="checkbox" name="ai1wmke_azure_storage_cron[]" id="ai1wmke-azure-storage-cron-hourly" value="hourly" <?php echo in_array( 'hourly', $azure_backup_schedules ) ? 'checked' : null; ?> />
										<?php _e( 'Every hour', AI1WMKE_PLUGIN_NAME ); ?>
									</label>
								</li>
								<li>
									<label for="ai1wmke-azure-storage-cron-daily">
										<input type="checkbox" name="ai1wmke_azure_storage_cron[]" id="ai1wmke-azure-storage-cron-daily" value="daily" <?php echo in_array( 'daily', $azure_backup_schedules ) ? 'checked' : null; ?> />
										<?php _e( 'Every day', AI1WMKE_PLUGIN_NAME ); ?>
									</label>
								</li>
								<li>
									<label for="ai1wmke-azure-storage-cron-weekly">
										<input type="checkbox" name="ai1wmke_azure_storage_cron[]" id="ai1wmke-azure-storage-cron-weekly" value="weekly" <?php echo in_array( 'weekly', $azure_backup_schedules ) ? 'checked' : null; ?> />
										<?php _e( 'Every week', AI1WMKE_PLUGIN_NAME ); ?>
									</label>
								</li>
								<li>
									<label for="ai1wmke-azure-storage-cron-monthly">
										<input type="checkbox" name="ai1wmke_azure_storage_cron[]" id="ai1wmke-azure-storage-cron-monthly" value="monthly" <?php echo in_array( 'monthly', $azure_backup_schedules ) ? 'checked' : null; ?> />
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
								<label for="ai1wmke-azure-storage-ssl">
									<input type="checkbox" name="ai1wmke_azure_storage_ssl" id="ai1wmke-azure-storage-ssl" value="1" <?php echo empty( $ssl ) ? 'checked' : null; ?> />
									<?php _e( 'Disable connecting to Microsoft Azure Storage via SSL (only if export is failing)', AI1WMKE_PLUGIN_NAME ); ?>
								</label>
							</p>
						</article>

						<article class="ai1wmke-azure-storage-article">
							<h3><?php _e( 'Notification settings', AI1WMKE_PLUGIN_NAME ); ?></h3>
							<p>
								<label for="ai1wmke-azure-storage-notify-toggle">
									<input type="checkbox" id="ai1wmke-azure-storage-notify-toggle" name="ai1wmke_azure_storage_notify_toggle" <?php echo empty( $notify_ok_toggle ) ? null : 'checked'; ?> />
									<?php _e( 'Send an email when a backup is complete', AI1WMKE_PLUGIN_NAME ); ?>
								</label>
							</p>

							<p>
								<label for="ai1wmke-azure-storage-notify-error-toggle">
									<input type="checkbox" id="ai1wmke-azure-storage-notify-error-toggle" name="ai1wmke_azure_storage_notify_error_toggle" <?php echo empty( $notify_error_toggle ) ? null : 'checked'; ?> />
									<?php _e( 'Send an email if a backup fails', AI1WMKE_PLUGIN_NAME ); ?>
								</label>
							</p>

							<p>
								<label for="ai1wmke-azure-storage-notify-email">
									<?php _e( 'Email address', AI1WMKE_PLUGIN_NAME ); ?>
									<br />
									<input class="ai1wmke-azure-storage-email" style="width: 15rem;" type="email" id="ai1wmke-azure-storage-notify-email" name="ai1wmke_azure_storage_notify_email" value="<?php echo esc_attr( $notify_email ); ?>" />
								</label>
							</p>
						</article>

						<article class="ai1wmke-azure-storage-article">
							<h3><?php _e( 'Retention settings', AI1WMKE_PLUGIN_NAME ); ?></h3>

							<p>
								<div class="ai1wm-field">
									<label for="ai1wmke-azure-storage-backups">
										<?php _e( 'Keep the most recent', AI1WMKE_PLUGIN_NAME ); ?>
										<input style="width: 4.5em;" type="number" min="0" name="ai1wmke_azure_storage_backups" id="ai1wmke-azure-storage-backups" value="<?php echo intval( $backups ); ?>" />
									</label>
									<?php _e( 'backups. <small>Default: <strong>0</strong> unlimited</small>', AI1WMKE_PLUGIN_NAME ); ?>
								</div>

								<div class="ai1wm-field">
									<label for="ai1wmke-azure-storage-total">
										<?php _e( 'Limit the total size of backups to', AI1WMKE_PLUGIN_NAME ); ?>
										<input style="width: 4.5em;" type="number" min="0" name="ai1wmke_azure_storage_total" id="ai1wmke-azure-storage-total" value="<?php echo intval( $total ); ?>" />
									</label>
									<select style="margin-top: -2px;" name="ai1wmke_azure_storage_total_unit" id="ai1wmke-azure-storage-total-unit">
										<option value="MB" <?php echo strpos( $total, 'MB' ) !== false ? 'selected="selected"' : null; ?>><?php _e( 'MB', AI1WMKE_PLUGIN_NAME ); ?></option>
										<option value="GB" <?php echo strpos( $total, 'GB' ) !== false ? 'selected="selected"' : null; ?>><?php _e( 'GB', AI1WMKE_PLUGIN_NAME ); ?></option>
									</select>
									<?php _e( '<small>Default: <strong>0</strong> unlimited</small>', AI1WMKE_PLUGIN_NAME ); ?>
								</div>
								<div class="ai1wm-field">
									<label for="ai1wmke-azure-storage-days">
										<?php _e( 'Remove backups older than ', AI1WMKE_PLUGIN_NAME ); ?>
										<input style="width: 4.5em;" type="number" min="0" name="ai1wmke_azure_storage_days" id="ai1wmke-azure-storage-days" value="<?php echo intval( $days ); ?>" />
									</label>
									<?php _e( 'days. <small>Default: <strong>0</strong> off</small>', AI1WMKE_PLUGIN_NAME ); ?>
								</div>
							</p>
						</article>

						<p>
							<button type="submit" class="ai1wm-button-blue" name="ai1wmke_azure_storage_update" id="ai1wmke-azure-storage-update">
								<i class="ai1wm-icon-database"></i>
								<?php _e( 'Update', AI1WMKE_PLUGIN_NAME ); ?>
							</button>
						</p>
					</form>
				</div>
			<?php endif; ?>

			<?php do_action( 'ai1wmke_azure_storage_settings_left_end' ); ?>

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
