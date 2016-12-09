<?php
/*	Project:	EQdkp-Plus
 *	Package:	MediaCenter Plugin
 *	Link:		http://eqdkp-plus.eu
 *
 *	Copyright (C) 2006-2015 EQdkp-Plus Developer Team
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

if (!defined('EQDKP_INC'))
{
    header('HTTP/1.0 404 Not Found');exit;
}

$lang = array(
  'discord'                   	=> 'Discord',

  // Description
  'sk_discord_short_desc'        => 'Discord einrichten',
  'sk_discord_long_desc'         => 'Richte die Verbindung zwischen EQdkp Plus und Discord ein',
  
  'discord_plugin_not_installed'	=> 'Das Discord-Plugin ist nicht installiert.',
		
  'discord_fs_general'        		=> 'Allgemeines',
	'discord_f_guild_id' 			=> 'Gilden-ID',
	'discord_f_help_guild_id' 		=> 'Die Gilden-ID bekommst du am besten über die URL deines Gildenservers heraus. Die erste lange Zahl ist die Gilden-ID',
	'discord_f_bot_client_id' 		=> 'Client-ID deiner Bot-Applikation',
	'discord_f_help_bot_client_id'	=> 'Für weitere Informationen siehe https://eqdkp-plus.eu/wiki/Discord',
	'discord_f_bot_token'			=> 'Access Token des App Bot-Users',
	'discord_f_help_bot_token'		=> 'Für weitere Informationen siehe https://eqdkp-plus.eu/wiki/Discord',
	'discord_autorize_bot'			=> 'Bot zum Gildenserver hinzufügen',	
);

?>
