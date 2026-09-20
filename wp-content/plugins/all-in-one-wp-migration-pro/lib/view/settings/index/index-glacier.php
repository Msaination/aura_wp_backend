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
				<h1><i class="ai1wm-icon-gear"></i> <?php _e( 'Amazon Glacier Settings', AI1WMKE_PLUGIN_NAME ); ?></h1>
				<br />
				<br />

				<?php if ( Ai1wm_Message::has( 'error' ) ) : ?>
					<div class="ai1wm-message ai1wm-error-message">
						<p><?php echo Ai1wm_Message::get( 'error' ); ?></p>
					</div>
				<?php elseif ( Ai1wm_Message::has( 'success' ) ) : ?>
					<div class="ai1wm-message ai1wm-success-message">
						<p><?php echo Ai1wm_Message::get( 'success' ); ?></p>
					</div>
				<?php endif; ?>

				<div id="ai1wmke-glacier-credentials">
					<form method="post" action="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ai1wmke_glacier_connection' ), 'ai1wmke_glacier_connection' ) ); ?>">
						<div class="ai1wm-field">
							<label for="ai1wmke-glacier-account-id">
								<?php _e( 'AWS Account ID', AI1WMKE_PLUGIN_NAME ); ?>
								<br />
								<input type="text" placeholder="<?php _e( 'Enter AWS Account ID', AI1WMKE_PLUGIN_NAME ); ?>" id="ai1wmke-glacier-account-id" class="ai1wmke-glacier-settings-account-id" name="ai1wmke_glacier_account_id" value="<?php echo esc_attr( $account_id ); ?>" />
							</label>
							<a href="https://docs.aws.amazon.com/IAM/latest/UserGuide/console_account-alias.html#FindingYourAWSId" target="_blank"><?php _e( 'How to find your AWS Account ID', AI1WMKE_PLUGIN_NAME ); ?></a>
						</div>

						<div class="ai1wm-field">
							<label for="ai1wmke-glacier-access-key">
								<?php _e( 'Access Key', AI1WMKE_PLUGIN_NAME ); ?>
								<br />
								<input type="text" placeholder="<?php _e( 'Enter Access Key', AI1WMKE_PLUGIN_NAME ); ?>" id="ai1wmke-glacier-access-key" class="ai1wmke-glacier-settings-key" name="ai1wmke_glacier_access_key" value="<?php echo esc_attr( $access_key ); ?>" />
							</label>
							<a href="https://docs.aws.amazon.com/IAM/latest/UserGuide/id_credentials_access-keys.html#Using_CreateAccessKey" target="_blank"><?php _e( 'How to find your Access Key', AI1WMKE_PLUGIN_NAME ); ?></a>
						</div>

						<div class="ai1wm-field">
							<label for="ai1wmke-glacier-secret-key">
								<?php _e( 'Secret Key', AI1WMKE_PLUGIN_NAME ); ?>
								<br />
								<input type="text" placeholder="<?php ( $secret_key ) ? _e( 'Hidden', AI1WMKE_PLUGIN_NAME ) : _e( 'Enter Secret Key', AI1WMKE_PLUGIN_NAME ); ?>" id="ai1wmke-glacier-secret-key" class="ai1wmke-glacier-settings-key" name="ai1wmke_glacier_secret_key" autocomplete="off" />
							</label>
							<a href="https://docs.aws.amazon.com/IAM/latest/UserGuide/id_credentials_access-keys.html#Using_CreateAccessKey" target="_blank"><?php _e( 'How to find your Secret Key', AI1WMKE_PLUGIN_NAME ); ?></a>
						</div>

						<p>
							<button type="submit" class="ai1wm-button-blue" name="ai1wmke_glacier_update" id="ai1wmke-glacier-link">
								<i class="ai1wm-icon-enter"></i>
								<?php _e( 'Update', AI1WMKE_PLUGIN_NAME ); ?>
							</button>
						</p>
					</form>
				</div>
			</div>

			<?php if ( $vaults !== false ) : ?>
				<div id="ai1wmke-glacier-config" class="ai1wm-holder">
					<h1><i class="ai1wm-icon-gear"></i> <?php _e( 'Amazon Glacier Backups', AI1WMKE_PLUGIN_NAME ); ?></h1>
					<br />
					<br />

					<?php if ( Ai1wm_Message::has( 'vault' ) ) : ?>
						<div class="ai1wm-message ai1wm-error-message">
							<p><?php echo Ai1wm_Message::get( 'vault' ); ?></p>
						</div>
					<?php elseif ( Ai1wm_Message::has( 'settings' ) ) : ?>
						<div class="ai1wm-message ai1wm-success-message">
							<p><?php echo Ai1wm_Message::get( 'settings' ); ?></p>
						</div>
					<?php endif; ?>

					<form method="post" action="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ai1wmke_glacier_settings' ), 'ai1wmke_glacier_settings' ) ); ?>">
						<article class="ai1wmke-glacier-article">
							<h3><?php _e( 'Region name', AI1WMKE_PLUGIN_NAME ); ?></h3>
							<p>
								<?php if ( count( $regions ) > 0 ) : ?>
									<select class="ai1wmke-glacier-region-name" id="ai1wmke-glacier-region-name" name="ai1wmke_glacier_region_name">
										<?php foreach ( $regions as $key => $value ) : ?>
											<option value="<?php echo esc_attr( $key ); ?>" <?php echo $region_name === $key ? 'selected' : null; ?>><?php echo esc_html( $value ); ?></option>
										<?php endforeach; ?>
									</select>
								<?php else : ?>
									<input type="text" placeholder="<?php _e( 'Enter Amazon Glacier Region Name', AI1WMKE_PLUGIN_NAME ); ?>" class="ai1wmke-glacier-region-name" name="ai1wmke_glacier_region_name" id="ai1wmke-glacier-region-name" value="<?php echo esc_attr( $region_name ); ?>" />
								<?php endif; ?>
							</p>
						</article>

						<article class="ai1wmke-glacier-article">
							<h3><?php _e( 'Vault name', AI1WMKE_PLUGIN_NAME ); ?></h3>
							<p>
								<input type="text" placeholder="<?php _e( 'Enter Amazon Glacier Vault Name', AI1WMKE_PLUGIN_NAME ); ?>" class="ai1wmke-glacier-vault-name" name="ai1wmke_glacier_vault_name" id="ai1wmke-glacier-vault-name" value="<?php echo esc_attr( $vault_name ); ?>" />
							</p>
						</article>

						<article class="ai1wmke-glacier-article">
							<h3><?php _e( 'Configure your backup plan', AI1WMKE_PLUGIN_NAME ); ?></h3>

							<p>
								<label for="ai1wmke-glacier-cron-timestamp">
									<?php _e( 'Backup time:', AI1WMKE_PLUGIN_NAME ); ?>
									<input type="text" name="ai1wmke_glacier_cron_timestamp" id="ai1wmke-glacier-cron-timestamp" value="<?php echo esc_attr( get_date_from_gmt( date( 'Y-m-d H:i:s', $glacier_cron_timestamp ), 'g:i a' ) ); ?>" autocomplete="off" />
									<code><?php echo ai1wm_get_timezone_string(); ?></code>
								</label>
							</p>

							<ul id="ai1wmke-glacier-cron">
								<li>
									<label for="ai1wmke-glacier-cron-hourly">
										<input type="checkbox" name="ai1wmke_glacier_cron[]" id="ai1wmke-glacier-cron-hourly" value="hourly" <?php echo in_array( 'hourly', $glacier_backup_schedules ) ? 'checked' : null; ?> />
										<?php _e( 'Every hour', AI1WMKE_PLUGIN_NAME ); ?>
									</label>
								</li>
								<li>
									<label for="ai1wmke-glacier-cron-daily">
										<input type="checkbox" name="ai1wmke_glacier_cron[]" id="ai1wmke-glacier-cron-daily" value="daily" <?php echo in_array( 'daily', $glacier_backup_schedules ) ? 'checked' : null; ?> />
										<?php _e( 'Every day', AI1WMKE_PLUGIN_NAME ); ?>
									</label>
								</li>
								<li>
									<label for="ai1wmke-glacier-cron-weekly">
										<input type="checkbox" name="ai1wmke_glacier_cron[]" id="ai1wmke-glacier-cron-weekly" value="weekly" <?php echo in_array( 'weekly', $glacier_backup_schedules ) ? 'checked' : null; ?> />
										<?php _e( 'Every week', AI1WMKE_PLUGIN_NAME ); ?>
									</label>
								</li>
								<li>
									<label for="ai1wmke-glacier-cron-monthly">
										<input type="checkbox" name="ai1wmke_glacier_cron[]" id="ai1wmke-glacier-cron-monthly" value="monthly" <?php echo in_array( 'monthly', $glacier_backup_schedules ) ? 'checked' : null; ?> />
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

						<article class="ai1wmke-glacier-article">
							<h3><?php _e( 'Notification settings', AI1WMKE_PLUGIN_NAME ); ?></h3>
							<p>
								<label for="ai1wmke-glacier-notify-toggle">
									<input type="checkbox" id="ai1wmke-glacier-notify-toggle" name="ai1wmke_glacier_notify_toggle" <?php echo empty( $notify_ok_toggle ) ? null : 'checked'; ?> />
									<?php _e( 'Send an email when a backup is complete', AI1WMKE_PLUGIN_NAME ); ?>
								</label>
							</p>

							<p>
								<label for="ai1wmke-glacier-notify-error-toggle">
									<input type="checkbox" id="ai1wmke-glacier-notify-error-toggle" name="ai1wmke_glacier_notify_error_toggle" <?php echo empty( $notify_error_toggle ) ? null : 'checked'; ?> />
									<?php _e( 'Send an email if a backup fails', AI1WMKE_PLUGIN_NAME ); ?>
								</label>
							</p>

							<p>
								<label for="ai1wmke-glacier-notify-email">
									<?php _e( 'Email address', AI1WMKE_PLUGIN_NAME ); ?>
									<br />
									<input class="ai1wmke-glacier-email" type="email" id="ai1wmke-glacier-notify-email" name="ai1wmke_glacier_notify_email" value="<?php echo esc_attr( $notify_email ); ?>" />
								</label>
							</p>
						</article>

						<article class="ai1wmke-glacier-article">
							<h3><?php _e( 'Security settings', AI1WMKE_PLUGIN_NAME ); ?></h3>
							<p>
								<label for="ai1wmke-glacier-lock-mode">
									<input type="checkbox" id="ai1wmke-glacier-lock-mode" name="ai1wmke_glacier_lock_mode" <?php echo empty( $lock_mode ) ? null : 'checked'; ?> />
									<?php printf( __( 'Lock this page for all users except <strong>%s</strong>. <a href="https://help.servmask.com/knowledgebase/lock-settings-page/" target="_blank">More details</a>', AI1WMKE_PLUGIN_NAME ), $user_display_name ); ?>
								</label>
							</p>
						</article>

						<article class="ai1wmke-glacier-article">
							<h3><?php _e( 'Transfer settings', AI1WMKE_PLUGIN_NAME ); ?></h3>
							<div class="ai1wm-field">
								<label><?php _e( 'Slow Internet (Home)', AI1WMKE_PLUGIN_NAME ); ?></label>
								<input name="ai1wmke_glacier_file_chunk_size" type="range" value="<?php echo $file_chunk_size; ?>" id="ai1wmke-glacier-file-chunk-size" min="2" max="5" step="1" />
								<label><?php _e( 'Fast Internet (Internet Servers)', AI1WMKE_PLUGIN_NAME ); ?></label>
							</div>
						</article>

						<p>
							<button type="submit" class="ai1wm-button-blue" name="ai1wmke_glacier_update" id="ai1wmke-glacier-update">
								<i class="ai1wm-icon-database"></i>
								<?php _e( 'Update', AI1WMKE_PLUGIN_NAME ); ?>
							</button>
						</p>
					</form>
				</div>
			<?php endif; ?>

			<?php do_action( 'ai1wmke_glacier_settings_left_end' ); ?>

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
