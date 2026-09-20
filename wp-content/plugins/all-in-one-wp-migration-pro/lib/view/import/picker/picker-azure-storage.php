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

<div id="ai1wmke-azure-storage-import-modal" class="ai1wmke-azure-storage-modal-container" role="dialog" tabindex="-1">
	<div class="ai1wmke-azure-storage-modal-content" v-if="items !== false">
		<div class="ai1wmke-azure-storage-file-browser">
			<div class="ai1wmke-azure-storage-path-list">
				<template v-for="(item, index) in path">
					<span v-if="index !== path.length - 1">
						<span class="ai1wmke-azure-storage-path-item" v-on:click="browse(item, index)" v-html="item.label"></span>
						<i class="ai1wm-icon-chevron-right"></i>
					</span>
					<span v-else>
						<span class="ai1wmke-azure-storage-path-item" v-html="item.label"></span>
					</span>
				</template>
			</div>

			<ul class="ai1wmke-azure-storage-file-list">
				<li class="ai1wmke-azure-storage-file-title" v-if="items.length > 0">
					<span class="ai1wmke-azure-storage-file-label">
						<?php _e( 'Name', AI1WMKE_PLUGIN_NAME ); ?>
					</span>
					<span class="ai1wmke-azure-storage-file-date">
						<?php _e( 'Date', AI1WMKE_PLUGIN_NAME ); ?>
					</span>
					<span class="ai1wmke-azure-storage-file-size">
						<?php _e( 'Size', AI1WMKE_PLUGIN_NAME ); ?>
					</span>
				</li>
				<li class="ai1wmke-azure-storage-file-item" v-for="item in items" v-on:click="browse(item)">
					<span class="ai1wmke-azure-storage-file-label">
						<i v-bind:class="icon(item)"></i>
						{{ item.label }}
					</span>
					<span class="ai1wmke-azure-storage-file-date">{{ item.date }}</span>
					<span class="ai1wmke-azure-storage-file-size">{{ item.size }}</span>
				</li>
				<li
					v-if="items !== false && items.length === 0"
					style="text-align: center; cursor: default;"
					class="ai1wmke-azure-storage-file-item">
					<strong><?php _e( 'No folders or files to list. Click on the navbar to go back.', AI1WMKE_PLUGIN_NAME ); ?></strong>
				</li>
				<li class="ai1wmke-azure-storage-file-info" v-if="num_hidden_files === 1">
					{{ num_hidden_files }}
					<?php _e( 'file is hidden', AI1WMKE_PLUGIN_NAME ); ?>
					<i class="ai1wm-icon-help" title="<?php _e( 'Only wpress backups are listed', AI1WMKE_PLUGIN_NAME ); ?>"></i>
				</li>
				<li class="ai1wmke-azure-storage-file-info" v-if="num_hidden_files > 1">
					{{ num_hidden_files }}
					<?php _e( 'files are hidden', AI1WMKE_PLUGIN_NAME ); ?>
					<i class="ai1wm-icon-help" title="<?php _e( 'Only wpress backups are listed', AI1WMKE_PLUGIN_NAME ); ?>"></i>
				</li>
			</ul>
		</div>
	</div>

	<div class="ai1wmke-azure-storage-modal-loader" v-if="items === false">
		<p>
			<span class="ai1wmke-azure-storage-modal-spinner spinner"></span>
		</p>
		<p>
			<span class="ai1wmke-azure-storage-contact">
				<?php _e( 'Connecting to Microsoft Azure Storage ...', AI1WMKE_PLUGIN_NAME ); ?>
			</span>
		</p>
	</div>

	<div class="ai1wmke-azure-storage-modal-action">
		<transition>
			<p class="ai1wmke-azure-storage-selected-file" v-if="file">
				<i class="ai1wm-icon-file-zip"></i>
				{{ file.label }}
			</p>
		</transition>

		<p>
			<button type="button" class="ai1wm-button-red" v-on:click="cancel">
				<?php _e( 'Close', AI1WMKE_PLUGIN_NAME ); ?>
			</button>
			<button type="button" class="ai1wm-button-green" v-if="file" v-on:click="restore(file)">
				<i class="ai1wm-icon-publish"></i>
				<?php _e( 'Import', AI1WMKE_PLUGIN_NAME ); ?>
			</button>
		</p>
	</div>
</div>

<div id="ai1wmke-azure-storage-import-overlay" class="ai1wmke-azure-storage-overlay"></div>
