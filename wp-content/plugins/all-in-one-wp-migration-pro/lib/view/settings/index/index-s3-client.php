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
				<h1><i class="ai1wm-icon-gear"></i> <?php _e( 'S3 Client Settings', AI1WMKE_PLUGIN_NAME ); ?></h1>
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

				<div id="ai1wmke-s3-client-credentials">
					<form method="post" action="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ai1wmke_s3_client_connection' ), 'ai1wmke_s3_client_connection' ) ); ?>">
						<div class="ai1wm-field">
							<label for="ai1wmke-s3-client-api-endpoint">
								<?php _e( 'S3 Endpoint', AI1WMKE_PLUGIN_NAME ); ?>
								<br />
								<input type="text" placeholder="<?php _e( 'Enter S3 Endpoint', AI1WMKE_PLUGIN_NAME ); ?>" id="ai1wmke-s3-client-api-endpoint" class="ai1wmke-s3-client-settings-key" name="ai1wmke_s3_client_api_endpoint" value="<?php echo esc_attr( $api_endpoint ); ?>" />
							</label>
							<a href="https://help.servmask.com/knowledgebase/s3-client-endpoints/" target="_blank">
								<?php _e( 'Where to find your S3 Endpoint', AI1WMKE_PLUGIN_NAME ); ?>
							</a>
						</div>

						<div class="ai1wm-field">
							<label for="ai1wmke-s3-client-bucket-template">
								<?php _e( 'DNS-style bucket+hostname:port template for accessing a bucket', AI1WMKE_PLUGIN_NAME ); ?>
								<br />
								<input type="text" placeholder="<?php _e( 'Enter DNS-style bucket+hostname:port template', AI1WMKE_PLUGIN_NAME ); ?>" id="ai1wmke-s3-client-bucket-template" class="ai1wmke-s3-client-settings-key" name="ai1wmke_s3_client_bucket_template" value="<?php echo esc_attr( $bucket_template ); ?>" />
							</label>
							<a href="https://help.servmask.com/knowledgebase/s3-client-endpoints/" target="_blank">
								<?php _e( 'Where to find your Bucket Template', AI1WMKE_PLUGIN_NAME ); ?>
							</a>
						</div>

						<div class="ai1wm-field">
							<label for="ai1wmke-s3-client-region-name">
								<?php _e( 'Region Name', AI1WMKE_PLUGIN_NAME ); ?>
								<br />
								<input type="text" placeholder="<?php _e( 'Enter Region Name', AI1WMKE_PLUGIN_NAME ); ?>" id="ai1wmke-s3-client-region-name" class="ai1wmke-s3-client-settings-key" name="ai1wmke_s3_client_region_name" value="<?php echo esc_attr( $region_name ); ?>" />
							</label>
						</div>

						<div class="ai1wm-field">
							<label for="ai1wmke-s3-client-access-key">
								<?php _e( 'Access Key', AI1WMKE_PLUGIN_NAME ); ?>
								<br />
								<input type="text" placeholder="<?php _e( 'Enter Access Key', AI1WMKE_PLUGIN_NAME ); ?>" id="ai1wmke-s3-client-access-key" class="ai1wmke-s3-client-settings-key" name="ai1wmke_s3_client_access_key" value="<?php echo esc_attr( $access_key ); ?>" />
							</label>
						</div>

						<div class="ai1wm-field">
							<label for="ai1wmke-s3-client-secret-key">
								<?php _e( 'Secret Key', AI1WMKE_PLUGIN_NAME ); ?>
								<br />
								<input type="text" placeholder="<?php ( $secret_key ) ? _e( 'Hidden', AI1WMKE_PLUGIN_NAME ) : _e( 'Enter Secret Key', AI1WMKE_PLUGIN_NAME ); ?>" id="ai1wmke-s3-client-secret-key" class="ai1wmke-s3-client-settings-key" name="ai1wmke_s3_client_secret_key" autocomplete="off" />
							</label>
						</div>

						<div class="ai1wm-field">
							<label for="ai1wmke-s3-client-https-protocol">
								<input type="checkbox" id="ai1wmke-s3-client-https-protocol" name="ai1wmke_s3_client_https_protocol" value="1" <?php echo empty( $https_protocol ) ? null : 'checked'; ?> />
								<?php _e( 'Use HTTPS protocol', AI1WMKE_PLUGIN_NAME ); ?>
							</label>
						</div>

						<p>
							<button type="submit" class="ai1wm-button-blue" name="ai1wmke_s3_client_update" id="ai1wmke-s3-client-link">
								<i class="ai1wm-icon-enter"></i>
								<?php _e( 'Update', AI1WMKE_PLUGIN_NAME ); ?>
							</button>
						</p>
					</form>
				</div>
			</div>

			<?php if ( $buckets !== false ) : ?>
				<div id="ai1wmke-s3-client-config" class="ai1wm-holder">
					<h1><i class="ai1wm-icon-gear"></i> <?php _e( 'S3 Client Backups', AI1WMKE_PLUGIN_NAME ); ?></h1>
					<br />
					<br />

					<?php if ( Ai1wm_Message::has( 'bucket' ) ) : ?>
						<div class="ai1wm-message ai1wm-error-message">
							<p><?php echo Ai1wm_Message::get( 'bucket' ); ?></p>
						</div>
					<?php elseif ( Ai1wm_Message::has( 'settings' ) ) : ?>
						<div class="ai1wm-message ai1wm-success-message">
							<p><?php echo Ai1wm_Message::get( 'settings' ); ?></p>
						</div>
					<?php endif; ?>

					<form method="post" action="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ai1wmke_s3_client_settings' ), 'ai1wmke_s3_client_settings' ) ); ?>">
						<article class="ai1wmke-s3-client-article">
							<h3><?php _e( 'Bucket name', AI1WMKE_PLUGIN_NAME ); ?></h3>
							<p>
								<input type="text" placeholder="<?php _e( 'Enter Bucket Name', AI1WMKE_PLUGIN_NAME ); ?>" class="ai1wmke-s3-client-bucket-name" name="ai1wmke_s3_client_bucket_name" id="ai1wmke-s3-client-bucket-name" value="<?php echo esc_attr( $bucket_name ); ?>" />
							</p>
						</article>

						<article class="ai1wmke-s3-client-article">
							<h3><?php _e( 'Folder name', AI1WMKE_PLUGIN_NAME ); ?></h3>
							<p>
								<input type="text" placeholder="<?php _e( 'Enter Folder Name (optional)', AI1WMKE_PLUGIN_NAME ); ?>" class="ai1wmke-s3-client-folder-name" name="ai1wmke_s3_client_folder_name" id="ai1wmke-s3-client-folder-name" value="<?php echo esc_attr( $folder_name ); ?>" />
							</p>
						</article>

						<article class="ai1wmke-s3-client-article">
							<h3><?php _e( 'Storage class', AI1WMKE_PLUGIN_NAME ); ?></h3>
							<ul>
								<li>
									<label for="ai1wmke-s3-client-storage-class-standard">
										<input type="radio" name="ai1wmke_s3_client_storage_class" id="ai1wmke-s3-client-storage-class-standard" value="STANDARD" <?php echo $storage_class === 'STANDARD' ? 'checked="checked"' : null; ?> />
										<?php _e( 'Standard', AI1WMKE_PLUGIN_NAME ); ?>
										<a href="https://aws.amazon.com/s3/storage-classes/#General_Purpose" style="text-decoration: none;" target="_blank"><?php echo _e( '[?]', AI1WMKE_PLUGIN_NAME ); ?></a>
									</label>
								</li>
								<li>
									<label for="ai1wmke-s3-client-storage-class-standard-ia">
										<input type="radio" name="ai1wmke_s3_client_storage_class" id="ai1wmke-s3-client-storage-class-standard-ia" value="STANDARD_IA" <?php echo $storage_class === 'STANDARD_IA' ? 'checked="checked"' : null; ?> />
										<?php _e( 'Infrequent Access', AI1WMKE_PLUGIN_NAME ); ?>
										<a href="https://aws.amazon.com/s3/storage-classes/#Infrequent_Access" style="text-decoration: none;" target="_blank"><?php echo _e( '[?]', AI1WMKE_PLUGIN_NAME ); ?></a>
									</label>
								</li>
								<li>
									<label for="ai1wmke-s3-client-storage-class-reduced-redundancy">
										<input type="radio" name="ai1wmke_s3_client_storage_class" id="ai1wmke-s3-client-storage-class-reduced-redundancy" value="REDUCED_REDUNDANCY" <?php echo $storage_class === 'REDUCED_REDUNDANCY' ? 'checked="checked"' : null; ?> />
										<?php _e( 'Reduced Redundancy', AI1WMKE_PLUGIN_NAME ); ?>
										<a href="https://aws.amazon.com/s3/reduced-redundancy/" style="text-decoration: none;" target="_blank"><?php echo _e( '[?]', AI1WMKE_PLUGIN_NAME ); ?></a>
									</label>
								</li>
							</ul>
						</article>

						<article class="ai1wmke-s3-client-article">
							<h3><?php _e( 'Encryption', AI1WMKE_PLUGIN_NAME ); ?></h3>
							<p>
								<label for="ai1wmke-s3-client-encryption">
									<input type="checkbox" id="ai1wmke-s3-client-encryption" name="ai1wmke_s3_client_encryption" value="AES256" <?php echo empty( $encryption ) ? null : 'checked'; ?> />
									<?php _e( 'Protect data at rest by using S3 Client master-key', AI1WMKE_PLUGIN_NAME ); ?>
									<a href="http://docs.aws.amazon.com/AmazonS3/latest/dev/UsingEncryption.html" style="text-decoration: none;" target="_blank"><?php echo _e( '[?]', AI1WMKE_PLUGIN_NAME ); ?></a>
								</label>
							</p>
						</article>

						<article class="ai1wmke-s3-client-article">
							<h3><?php _e( 'Configure your backup plan', AI1WMKE_PLUGIN_NAME ); ?></h3>

							<p>
								<label for="ai1wmke-s3-client-cron-timestamp">
									<?php _e( 'Backup time:', AI1WMKE_PLUGIN_NAME ); ?>
									<input type="text" name="ai1wmke_s3_client_cron_timestamp" id="ai1wmke-s3-client-cron-timestamp" value="<?php echo esc_attr( get_date_from_gmt( date( 'Y-m-d H:i:s', $s3_cron_timestamp ), 'g:i a' ) ); ?>" autocomplete="off" />
									<code><?php echo ai1wm_get_timezone_string(); ?></code>
								</label>
							</p>

							<ul id="ai1wmke-s3-client-cron">
								<li>
									<label for="ai1wmke-s3-client-cron-hourly">
										<input type="checkbox" name="ai1wmke_s3_client_cron[]" id="ai1wmke-s3-client-cron-hourly" value="hourly" <?php echo in_array( 'hourly', $s3_backup_schedules ) ? 'checked' : null; ?> />
										<?php _e( 'Every hour', AI1WMKE_PLUGIN_NAME ); ?>
									</label>
								</li>
								<li>
									<label for="ai1wmke-s3-client-cron-daily">
										<input type="checkbox" name="ai1wmke_s3_client_cron[]" id="ai1wmke-s3-client-cron-daily" value="daily" <?php echo in_array( 'daily', $s3_backup_schedules ) ? 'checked' : null; ?> />
										<?php _e( 'Every day', AI1WMKE_PLUGIN_NAME ); ?>
									</label>
								</li>
								<li>
									<label for="ai1wmke-s3-client-cron-weekly">
										<input type="checkbox" name="ai1wmke_s3_client_cron[]" id="ai1wmke-s3-client-cron-weekly" value="weekly" <?php echo in_array( 'weekly', $s3_backup_schedules ) ? 'checked' : null; ?> />
										<?php _e( 'Every week', AI1WMKE_PLUGIN_NAME ); ?>
									</label>
								</li>
								<li>
									<label for="ai1wmke-s3-client-cron-monthly">
										<input type="checkbox" name="ai1wmke_s3_client_cron[]" id="ai1wmke-s3-client-cron-monthly" value="monthly" <?php echo in_array( 'monthly', $s3_backup_schedules ) ? 'checked' : null; ?> />
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
								<label for="ai1wmke-s3-client-incremental">
									<input type="checkbox" name="ai1wmke_s3_client_incremental" id="ai1wmke-s3-client-incremental" value="1" <?php echo empty( $incremental ) ? null : 'checked'; ?> />
									<?php _e( 'Enable incremental backups (optimize backup file size)', AI1WMKE_PLUGIN_NAME ); ?>
								</label>
							</p>
						</article>

						<article class="ai1wmke-s3-client-article">
							<h3><?php _e( 'Notification settings', AI1WMKE_PLUGIN_NAME ); ?></h3>
							<p>
								<label for="ai1wmke-s3-client-notify-toggle">
									<input type="checkbox" id="ai1wmke-s3-client-notify-toggle" name="ai1wmke_s3_client_notify_toggle" <?php echo empty( $notify_ok_toggle ) ? null : 'checked'; ?> />
									<?php _e( 'Send an email when a backup is complete', AI1WMKE_PLUGIN_NAME ); ?>
								</label>
							</p>

							<p>
								<label for="ai1wmke-s3-client-notify-error-toggle">
									<input type="checkbox" id="ai1wmke-s3-client-notify-error-toggle" name="ai1wmke_s3_client_notify_error_toggle" <?php echo empty( $notify_error_toggle ) ? null : 'checked'; ?> />
									<?php _e( 'Send an email if a backup fails', AI1WMKE_PLUGIN_NAME ); ?>
								</label>
							</p>

							<p>
								<label for="ai1wmke-s3-client-notify-email">
									<?php _e( 'Email address', AI1WMKE_PLUGIN_NAME ); ?>
									<br />
									<input class="ai1wmke-s3-client-email" type="email" id="ai1wmke-s3-client-notify-email" name="ai1wmke_s3_client_notify_email" value="<?php echo esc_attr( $notify_email ); ?>" />
								</label>
							</p>
						</article>

						<article class="ai1wmke-s3-client-article">
							<h3><?php _e( 'Retention settings', AI1WMKE_PLUGIN_NAME ); ?></h3>
							<p>
								<div class="ai1wm-field">
									<label for="ai1wmke-s3-client-backups">
										<?php _e( 'Keep the most recent', AI1WMKE_PLUGIN_NAME ); ?>
										<input style="width: 4.5em;" type="number" min="0" name="ai1wmke_s3_client_backups" id="ai1wmke-s3-client-backups" value="<?php echo intval( $backups ); ?>" />
									</label>
									<?php _e( 'backups. <small>Default: <strong>0</strong> unlimited</small>', AI1WMKE_PLUGIN_NAME ); ?>
								</div>

								<div class="ai1wm-field">
									<label for="ai1wmke-s3-client-total">
										<?php _e( 'Limit the total size of backups to', AI1WMKE_PLUGIN_NAME ); ?>
										<input style="width: 4.5em;" type="number" min="0" name="ai1wmke_s3_client_total" id="ai1wmke-s3-client-total" value="<?php echo intval( $total ); ?>" />
									</label>
									<select style="margin-top: -2px;" name="ai1wmke_s3_client_total_unit" id="ai1wmke-s3-client-total-unit">
										<option value="MB" <?php echo strpos( $total, 'MB' ) !== false ? 'selected="selected"' : null; ?>><?php _e( 'MB', AI1WMKE_PLUGIN_NAME ); ?></option>
										<option value="GB" <?php echo strpos( $total, 'GB' ) !== false ? 'selected="selected"' : null; ?>><?php _e( 'GB', AI1WMKE_PLUGIN_NAME ); ?></option>
									</select>
									<?php _e( '<small>Default: <strong>0</strong> unlimited</small>', AI1WMKE_PLUGIN_NAME ); ?>
								</div>

								<div class="ai1wm-field">
									<label for="ai1wmke-s3-client-days">
										<?php _e( 'Remove backups older than ', AI1WMKE_PLUGIN_NAME ); ?>
										<input style="width: 4.5em;" type="number" min="0" name="ai1wmke_s3_client_days" id="ai1wmke-s3-client-days" value="<?php echo intval( $days ); ?>" />
									</label>
									<?php _e( 'days. <small>Default: <strong>0</strong> off</small>', AI1WMKE_PLUGIN_NAME ); ?>
								</div>
							</p>
						</article>

						<article class="ai1wmke-s3-client-article">
							<h3><?php _e( 'Transfer settings', AI1WMKE_PLUGIN_NAME ); ?></h3>
							<div class="ai1wm-field">
								<label><?php _e( 'Slow Internet (Home)', AI1WMKE_PLUGIN_NAME ); ?></label>
								<input name="ai1wmke_s3_client_file_chunk_size" min="5242880" max="20971520" step="5242880" type="range" value="<?php echo $file_chunk_size; ?>" id="ai1wmke-s3-client-file-chunk-size" />
								<label><?php _e( 'Fast Internet (Internet Servers)', AI1WMKE_PLUGIN_NAME ); ?></label>
							</div>
						</article>

						<p>
							<button type="submit" class="ai1wm-button-blue" name="ai1wmke_s3_client_update" id="ai1wmke-s3-client-update">
								<i class="ai1wm-icon-database"></i>
								<?php _e( 'Update', AI1WMKE_PLUGIN_NAME ); ?>
							</button>
						</p>
					</form>
				</div>
			<?php endif; ?>

			<?php do_action( 'ai1wmke_s3_client_settings_left_end' ); ?>

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
