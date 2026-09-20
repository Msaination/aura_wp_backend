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

<div id="ai1wmke-mega-settings-modal" class="ai1wmke-mega-modal-container">
	<div class="ai1wmke-mega-modal-content" v-if="items !== false">
		<div class="ai1wmke-mega-file-browser">
			<div class="ai1wmke-mega-path-list">
				<template v-for="(item, index) in path">
					<span v-if="index !== path.length - 1">
						<span class="ai1wmke-mega-path-item" v-on:click="browse(item, index)" v-html="item.name"></span>
						<i class="ai1wm-icon-chevron-right"></i>
					</span>
					<span v-else>
						<span class="ai1wmke-mega-path-item" style="cursor: default" v-html="item.name"></span>
					</span>
				</template>
			</div>
			<div class="ai1wmke-mega-file-list" v-if="items !== false && items.length > 0">
				<div class="ai1wmke-mega-file-item">
					<span style="width: 80%;" class="ai1wmke-mega-file-name-header">
						<?php _e( 'Name', AI1WMKE_PLUGIN_NAME ); ?>
					</span>
					<span class="ai1wmke-mega-file-date-header">
						<?php _e( 'Date', AI1WMKE_PLUGIN_NAME ); ?>
					</span>
				</div>
			</div>
			<ul class="ai1wmke-mega-file-list">
				<li
					v-for="item in items"
					v-on:click="select(item)"
					v-on:dblclick="browse(item)"
					v-bind:class="{'ai1wmke-mega-dir-selected': (item === selectedItem || isPreselectedItem(item))}"
					class="ai1wmke-mega-file-item">
					<span style="width: 80%;" class="ai1wmke-mega-file-name">
						<i v-bind:class="icon(item)"></i>
						{{ item.name }}
					</span>
					<span class="ai1wmke-mega-file-date">{{ item.date }}</span>
				</li>
				<li
					v-if="items !== false && items.length === 0"
					style="text-align: center; cursor: default;"
					class="ai1wmke-mega-file-item">
					<strong><?php _e( 'No folders to list. Click on the navbar to go back.', AI1WMKE_PLUGIN_NAME ); ?></strong>
				</li>
			</ul>
		</div>
	</div>

	<div class="ai1wmke-mega-modal-loader" v-if="items === false">
		<p>
			<span style="float: none; visibility: visible;" class="spinner"></span>
		</p>
		<p>
			<span class="ai1wmke-mega-contact">
				<?php _e( 'Connecting to Mega ...', AI1WMKE_PLUGIN_NAME ); ?>
			</span>
		</p>
	</div>

	<div class="ai1wmke-mega-modal-legend">
		<p style="box-shadow: 0px -1px 1px 0px rgb(221, 221, 221);" class="ai1wmke-mega-file-info" v-if="items !== false">
			<?php _e( 'Select with a click', AI1WMKE_PLUGIN_NAME ); ?>
			<br />
			<?php _e( 'Open with two clicks', AI1WMKE_PLUGIN_NAME ); ?>
		</p>
	</div>

	<div class="ai1wmke-mega-modal-action">
		<p class="ai1wmke-mega-justified-container">
			<button type="button" class="ai1wm-button-red" v-on:click="cancel">
				<?php _e( 'Close', AI1WMKE_PLUGIN_NAME ); ?>
			</button>
			<button type="button" class="ai1wm-button-green" v-if="selectedItem" v-on:click="store">
				<?php _e( 'Select folder &gt;', AI1WMKE_PLUGIN_NAME ); ?>
			</button>
		</p>
	</div>
</div>

<div id="ai1wmke-mega-settings-overlay" class="ai1wmke-mega-overlay"></div>
