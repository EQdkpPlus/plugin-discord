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
		'sk_discord_short_desc'        => 'Init Discord',
		'sk_discord_long_desc'         => 'Initiated the connection between EQdkp Plus and Discord',

		'discord_plugin_not_installed'	=> 'Discord-Plugin is not installed.',

		'discord_fs_general'        	=> 'General',
		'discord_f_guild_id' 			=> 'Guild-ID',
		'discord_f_help_guild_id' 		=> 'You can get the Guild-URL from the URL of your Guildserver. It is the first long number at the URL.',
		'discord_f_bot_client_id' 		=> 'Client-ID of your Bot Application',
		'discord_f_help_bot_client_id'	=> 'For more information and instructions see https://eqdkp-plus.eu/wiki/Discord',
		'discord_f_bot_token'			=> 'Access Token of the App Bot User',
		'discord_f_help_bot_token'		=> 'For more information and instructions see https://eqdkp-plus.eu/wiki/Discord',
		'discord_autorize_bot'			=> 'Add Bot to the Guildserver',
);

?>
