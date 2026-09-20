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

<div id="ai1wmke-gdrive-import-modal" class="ai1wmke-gdrive-modal-container" role="dialog" tabindex="-1">
	<div class="ai1wmke-gdrive-modal-content" v-if="loading === false">
		<div class="ai1wmke-gdrive-file-browser">
			<div class="ai1wmke-gdrive-path-list">
				<template v-for="(item, index) in path">
					<span v-if="index !== path.length - 1">
						<span class="ai1wmke-gdrive-path-item" v-on:click="browse(item, index)" v-html="item.name"></span>
						<i class="ai1wm-icon-chevron-right"></i>
					</span>
					<span v-else>
						<span class="ai1wmke-gdrive-path-item" style="cursor: default" v-html="item.name"></span>
					</span>
				</template>
			</div>
			<div class="ai1wmke-gdrive-file-list" v-if="items.length > 0">
				<div class="ai1wmke-gdrive-file-item">
					<span class="ai1wmke-gdrive-file-name-header">
						<?php _e( 'Name', AI1WMKE_PLUGIN_NAME ); ?>
					</span>
					<span class="ai1wmke-gdrive-file-date-header">
						<?php _e( 'Date', AI1WMKE_PLUGIN_NAME ); ?>
					</span>
					<span class="ai1wmke-gdrive-file-size-header">
						<?php _e( 'Size', AI1WMKE_PLUGIN_NAME ); ?>
					</span>
				</div>
			</div>
			<ul class="ai1wmke-gdrive-file-list">
				<li v-for="item in items" v-on:click="browse(item)" class="ai1wmke-gdrive-file-item">
					<span class="ai1wmke-gdrive-file-name">
						<i v-bind:class="icon(item)"></i>
						{{ item.name }}
					</span>
					<span class="ai1wmke-gdrive-file-date">{{ item.date }}</span>
					<span class="ai1wmke-gdrive-file-size">{{ item.size }}</span>
				</li>
				<li v-if="loading === false && items.length === 0" style="text-align: center; cursor: default;" class="ai1wmke-gdrive-file-item">
					<strong><?php _e( 'No folders or files to list. Click on the navbar to go back.', AI1WMKE_PLUGIN_NAME ); ?></strong>
				</li>
				<li class="ai1wmke-gdrive-load-more" v-if="nextPageToken">
					<span class="ai1wmke-gdrive-load-more-button" v-on:click="browse(currentItem, currentIndex, nextPageToken)"><?php _e( 'Load more', AI1WMKE_PLUGIN_NAME ); ?></span>
				</li>
				<li class="ai1wmke-gdrive-file-info" v-if="items.length > 0">
					<?php _e( 'Only wpress backups are listed', AI1WMKE_PLUGIN_NAME ); ?>
				</li>
				<li class="ai1wmke-gdrive-file-info" v-if="loading === false && items.length > 0">
					<?php _e( 'Open with a click', AI1WMKE_PLUGIN_NAME ); ?>
				</li>
			</ul>
		</div>
	</div>

	<div class="ai1wmke-gdrive-modal-loader" v-if="loading === true">
		<p>
			<span style="float: none; visibility: visible;" class="spinner"></span>
		</p>
		<p>
			<span class="ai1wmke-gdrive-contact">
				<?php _e( 'Connecting to Google Drive ...', AI1WMKE_PLUGIN_NAME ); ?>
			</span>
		</p>
	</div>

	<div class="ai1wmke-gdrive-modal-action">
		<transition>
			<p class="ai1wmke-gdrive-selected-file" v-if="selectedItem">
				<i class="ai1wm-icon-file-zip"></i>
				{{ selectedItem.name }}
			</p>
		</transition>

		<p class="ai1wmke-gdrive-justified-container">
			<button type="button" class="ai1wm-button-red" v-on:click="cancel">
				<?php _e( 'Close', AI1WMKE_PLUGIN_NAME ); ?>
			</button>
			<button type="button" class="ai1wm-button-green" v-if="selectedItem" v-on:click="restore(selectedItem)">
				<i class="ai1wm-icon-publish"></i>
				<?php _e( 'Import', AI1WMKE_PLUGIN_NAME ); ?>
			</button>
		</p>
	</div>
</div>

<div id="ai1wmke-gdrive-import-overlay" class="ai1wmke-gdrive-overlay"></div>
