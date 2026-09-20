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

class Ai1wmke_Error_Exception extends Exception {}
class Ai1wmke_Connect_Exception extends Ai1wmke_Error_Exception {}
class Ai1wmke_Schedules_Exception extends Ai1wmke_Error_Exception {}
class Ai1wmke_Rate_Limit_Exception extends Ai1wmke_Connect_Exception {}
class Ai1wmke_Internal_Server_Error_Exception extends Ai1wmke_Connect_Exception {}
class Ai1wmke_Authentication_Failed_Exception extends Ai1wmke_Error_Exception {}
class Ai1wmke_Invalid_Resource_Name_Exception extends Ai1wmke_Error_Exception {}

class Ai1wmke_Bad_Auth_Token_Exception extends Ai1wmke_Error_Exception {}
class Ai1wmke_Unauthorized_Exception extends Ai1wmke_Error_Exception {}

class Ai1wmke_Authorization_Header_Malformed_Exception extends Ai1wmke_Error_Exception {}
class Ai1wmke_Invalid_Access_Key_Id_Exception extends Ai1wmke_Error_Exception {}
class Ai1wmke_Invalid_Bucket_Name_Exception extends Ai1wmke_Error_Exception {}
class Ai1wmke_Invalid_Request_Exception extends Ai1wmke_Error_Exception {}
class Ai1wmke_Signature_Does_Not_Match_Exception extends Ai1wmke_Error_Exception {}
class Ai1wmke_Access_Denied_Exception extends Ai1wmke_Error_Exception {}
class Ai1wmke_All_Access_Disabled_Exception extends Ai1wmke_Error_Exception {}
class Ai1wmke_No_Such_Bucket_Exception extends Ai1wmke_Error_Exception {}
class Ai1wmke_Bucket_Already_Exists_Exception extends Ai1wmke_Error_Exception {}
class Ai1wmke_Location_Constraint_Exception extends Ai1wmke_Error_Exception {}

class Ai1wmke_Shared_Link_Already_Exists_Exception extends Ai1wmke_Error_Exception {}
class Ai1wmke_Incorrect_Offset_Exception extends Ai1wmke_Error_Exception {}
class Ai1wmke_Lookup_Failed_Exception extends Ai1wmke_Incorrect_Offset_Exception {}

class Ai1wmke_List_Exception extends Ai1wmke_Connect_Exception {}
class Ai1wmke_Upload_Exception extends Ai1wmke_Connect_Exception {}
class Ai1wmke_Download_Exception extends Ai1wmke_Connect_Exception {}
class Ai1wmke_Login_Exception extends Ai1wmke_Connect_Exception {}
class Ai1wmke_Change_Directory_Exception extends Ai1wmke_Connect_Exception {}
class Ai1wmke_Write_Error_Exception extends Ai1wmke_Error_Exception {}
class Ai1wmke_Operation_Timedout_Exception extends Ai1wmke_Error_Exception {}

class Ai1wmke_Permission_Denied_Exception extends Ai1wmke_Error_Exception {}

class Ai1wmke_Invalid_Grant_Exception extends Ai1wmke_Error_Exception {}
class Ai1wmke_Request_Timeout_Exception extends Ai1wmke_Connect_Exception {}

class Ai1wmke_Unrecognized_Client_Exception extends Ai1wmke_Error_Exception {}
class Ai1wmke_Invalid_Signature_Exception extends Ai1wmke_Error_Exception {}
class Ai1wmke_Resource_Not_Found_Exception extends Ai1wmke_Error_Exception {}

class Ai1wmke_Bad_Arguments_Exception extends Ai1wmke_Error_Exception {}
class Ai1wmke_Resource_Does_Not_Exist_Exception extends Ai1wmke_Error_Exception {}
class Ai1wmke_Cryptographic_Error_Exception extends Ai1wmke_Error_Exception {}

class Ai1wmke_Unknown_Error_Exception extends Ai1wmke_Error_Exception {}
class Ai1wmke_Invalid_Range_Exception extends Ai1wmke_Error_Exception {}

class Ai1wmke_Push_Exception extends Ai1wmke_Error_Exception {}
class Ai1wmke_Pull_Exception extends Ai1wmke_Error_Exception {}
