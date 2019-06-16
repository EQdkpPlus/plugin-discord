<?php
/*	Project:	EQdkp-Plus
 *	Package:	GuildRequest Plugin
 *	Link:		http://eqdkp-plus.eu
 *
 *	Copyright (C) 2006-2016 EQdkp-Plus Developer Team
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU Affero General Public License as published
 *	by the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU Affero General Public License for more details.
 *
 *	You should have received a copy of the GNU Affero General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('EQDKP_INC')){
	header('HTTP/1.0 404 Not Found');exit;
}

include_once(registry::get_const('root_path').'maintenance/includes/sql_update_task.class.php');

if (!class_exists('update_discord_120')){
	class update_discord_120 extends sql_update_task{

		public $author		= 'GodMod';
		public $version		= '1.2.0';    // new version
		public $name		= 'Discord 1.2.0 Update';
		public $type		= 'plugin_update';
		public $plugin_path	= 'discord'; // important!

		/**
		* Constructor
		*/
		public function __construct(){
			parent::__construct();

			// init language
			$this->langs = array(
				'english' => array(
					'update_discord_120'	=> 'Discord 1.2.0 Update Package',
					'update_function' 		=> 'Authorize Bot',
				),
				'german' => array(
					'update_discord_120'	=> 'Discord 1.2.0 Update Paket',
					'update_function'		=> 'Authorisiere Bot',
				),
			);

		}
		
		public function update_function(){
			return true;
		}
		
		public function output_function(){
			$arrDiscordConfig = register('config')->get_config('discord');
			$token = $arrDiscordConfig['bot_token'];
			
			$out = '<script>    	
	    	var connection = new WebSocket("wss://gateway.discord.gg?v=6&encoding=json");

	    	// When the connection is open, send some data to the server
			connection.onopen = function () {
			  connection.send(\'{"op": 2,"d": {"token": "'.$token.'","properties": {"$os": "linux","$browser": "EQdkp Plus","$device": "EQdkp Plus"}}}\');
			};

			// Log errors
			connection.onerror = function (error) {
			  console.log("WebSocket Error " + error);
			};

			// Log messages from the server
			connection.onmessage = function (e) {
			  console.log("Server: " + e.data);
			};

			connection.onclose = function (e) {
			  console.debug("Server close: " + e);
			};
	    </script>';
			
			return $out;
		}
	}
}
?>
