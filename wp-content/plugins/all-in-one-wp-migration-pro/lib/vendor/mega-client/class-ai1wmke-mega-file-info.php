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

class Ai1wmke_Mega_File_Info {

	const TYPE_FILE    = 0;
	const TYPE_FOLDER  = 1;
	const TYPE_ROOT    = 2;
	const TYPE_INBOX   = 3;
	const TYPE_TRASH   = 4;
	const TYPE_CONTACT = 8;
	const TYPE_NETWORK = 9;

	/**
	 * Node ID
	 *
	 * @var string
	 */
	protected $node_id = null;

	/**
	 * Parent node ID
	 *
	 * @var string
	 */
	protected $parent_node_id = null;

	/**
	 * Owner ID
	 *
	 * @var string
	 */
	protected $owner_id = null;

	/**
	 * Node attributes
	 *
	 * @var array
	 */
	protected $attributes = null;

	/**
	 * Node type
	 *
	 * @var integer
	 */
	protected $type = null;

	/**
	 * Node key
	 *
	 * @var string
	 */
	protected $key = null;

	/**
	 * Node size
	 *
	 * @var integer
	 */
	protected $size = null;

	/**
	 * Base URL
	 *
	 * @var integer
	 */
	protected $last_modified_date = null;

	public function __construct( array $data ) {
		$this->node_id            = $data['h'];
		$this->parent_node_id     = $data['p'];
		$this->owner_id           = $data['u'];
		$this->attributes         = $data['a'];
		$this->type               = $data['t'];
		$this->key                = $data['k'];
		$this->size               = $data['s'];
		$this->last_modified_date = $data['ts'];
	}

	public function get_node_id() {
		return $this->node_id;
	}

	public function get_parent_node_id() {
		return $this->parent_node_id;
	}

	public function get_key() {
		return $this->key;
	}

	public function get_file_name() {
		return $this->attributes['n'];
	}

	public function get_size() {
		return $this->size;
	}

	public function get_type() {
		return $this->type === self::TYPE_FILE ? 'file' : 'folder';
	}

	public function is_dir() {
		return $this->type === self::TYPE_FOLDER;
	}

	public function is_file() {
		return $this->type === self::TYPE_FILE;
	}

	public function get_last_modified_date() {
		return $this->last_modified_date;
	}
}
