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

<div id="ai1wmke-glacier-import-modal" class="ai1wmke-glacier-modal-container" role="dialog" tabindex="-1">
	<div class="ai1wmke-glacier-modal-content" v-if="items !== false">
		<div class="ai1wmke-glacier-file-browser">
			<div class="ai1wmke-glacier-path-list">
				<template v-for="(item, index) in path">
					<span v-if="index !== path.length - 1">
						<span class="ai1wmke-glacier-path-item" v-on:click="browse(item, index)" v-html="item.label"></span>
						<i class="ai1wm-icon-chevron-right"></i>
					</span>
					<span v-else>
						<span class="ai1wmke-glacier-path-item" v-html="item.label"></span>
					</span>
				</template>
			</div>

			<ul class="ai1wmke-glacier-file-list">
				<li class="ai1wmke-glacier-file-title" v-if="items.length > 0">
					<span class="ai1wmke-glacier-file-label">
						<?php _e( 'Name', AI1WMKE_PLUGIN_NAME ); ?>
					</span>
					<span class="ai1wmke-glacier-file-date">
						<?php _e( 'Date', AI1WMKE_PLUGIN_NAME ); ?>
					</span>
					<span class="ai1wmke-glacier-file-size">
						<?php _e( 'Size', AI1WMKE_PLUGIN_NAME ); ?>
					</span>
				</li>
				<li class="ai1wmke-glacier-file-item" v-for="item in items" v-on:click="browse(item)">
					<span class="ai1wmke-glacier-file-label">
						<i v-bind:class="icon(item)"></i>
						{{ item.label }}
					</span>
					<span class="ai1wmke-glacier-file-date">{{ item.date }}</span>
					<span class="ai1wmke-glacier-file-size">{{ item.size }}</span>
				</li>
				<li class="ai1wmke-glacier-file-load-more" v-if="items.length > 0 && items.length % 10 === 0">
					<template v-if="items[items.length - 1].type === 'vault'">
						<a href="#" v-on:click.prevent="showMoreVaults(items[items.length - 1])"><?php _e( 'Show more vaults', AI1WMKE_PLUGIN_NAME ); ?></a>
						<span class="ai1wmke-glacier-file-load-spinner spinner" v-if="vaults === false"></span>
					</template>
				</li>
				<li class="ai1wmke-glacier-file-error" v-for="error in errors">
					<strong>{{ error }}</strong>
				</li>
			</ul>
		</div>
	</div>

	<div class="ai1wmke-glacier-modal-loader" v-if="items === false">
		<p>
			<span class="ai1wmke-glacier-modal-spinner spinner"></span>
		</p>
		<p>
			<span class="ai1wmke-glacier-contact">
				<?php _e( 'Connecting to Amazon Glacier ...', AI1WMKE_PLUGIN_NAME ); ?>
			</span>
		</p>
	</div>

	<div class="ai1wmke-glacier-modal-action">
		<transition>
			<p class="ai1wmke-glacier-selected-file" v-if="file">
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

<div id="ai1wmke-glacier-import-overlay" class="ai1wmke-glacier-overlay"></div>
