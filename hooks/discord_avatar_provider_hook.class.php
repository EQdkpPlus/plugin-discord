<?php
/*	Project:	EQdkp-Plus
 *	Package:	Chat Plugin
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

if (!defined('EQDKP_INC'))
{
  header('HTTP/1.0 404 Not Found');exit;
}


/*+----------------------------------------------------------------------------
  | chat_portal_hook
  +--------------------------------------------------------------------------*/
if (!class_exists('chat_portal_hook'))
{
  class discord_avatar_provider_hook extends gen_class
  {

  	public static $shortcuts = array('puf' => 'urlfetcher');
  	
  	private $url = 'https://cdn.discordapp.com/avatars/%s/%s.jpg';
  	private $intCachingTime = 24; //hours
	/**
    * hook_portal
    * Do the hook 'portal'
    *
    * @return array
    */
	public function avatar_provider($arrParams)
	{		
		return array('discord' => array('name' => 'Discord'));
	}
	
	
	public function user_avatarimg($arrParams){
		$user_id = $arrParams['user_id'];
		$fullSize = $arrParams['fullsize'];
		$avatarimg = $arrParams['avatarimg'];
		$strAvatarType = $arrParams['avatartype'];
		$blnDefault= $arrParams['default'];
		#if($strAvatarType != 'discord') return array('discord' => '');
		
		
		$strEQdkpUsername = $this->pdh->get('user', 'name', array($user_id));
		
		$intSize = 128; 
		$strCachedImage = $this->getCachedImage($strEQdkpUsername, $user_id, $intSize);
		
		if (!$strCachedImage){
			//Download
			$result = $this->cacheImage($strEQdkpUsername, $user_id, $intSize);
			$strCachedImage = $result;
		}
		if(!filesize($strCachedImage)) $strCachedImage = "";
		
		return array('discord' => $strCachedImage);
	}
	
	public function getCachedImage($strEQdkpUsername, $intEQdkpUserID, $intSize){
		
		$strImage = md5($intEQdkpUserID).'_'.$intSize.'.jpg';
	
		$strCacheFolder = $this->pfh->FolderPath('discord','eqdkp');
		$strRelativeFile = $strCacheFolder.$strImage;
	
		if (is_file($strRelativeFile)){
			//Check Cachetime
			if ((filemtime($strRelativeFile)+(3600*$this->intCachingTime)) > time()) return $strRelativeFile;
		}
		return false;
	}
	
	public function cacheImage($strEQdkpUsername, $intEQdkpUserID, $intSize){	
		$strImage = md5($intEQdkpUserID).'_'.$intSize.'.jpg';
		$strCacheFolder = $this->pfh->FolderPath('discord','eqdkp');
		$strRelativeFile = $strCacheFolder.$strImage;
		
		//Fetch data from Discord
		$arrDiscordConfig = $this->config->get_config('discord');
		$guild = $arrDiscordConfig['guild_id'];
		$token = $arrDiscordConfig['bot_token'];
		$result = register('urlfetcher')->fetch('https://discord.com/api/guilds/'.$guild.'/members?limit=1000', array('Authorization: Bot '.$token));
		$strUserID = $strAvatarHash = false;

		if($result){
			$arrJSON = json_decode($result, true);
			
			foreach($arrJSON as $val){
				if(utf8_strtolower($val['user']['username']) == utf8_strtolower($strEQdkpUsername)){
					$strUserID = $val['user']['id'];
					$strAvatarHash = $val['user']['avatar'];
				}
			}
		}
		
		if(!$strUserID) {
			$this->pfh->putContent($strRelativeFile, "");
			return $strRelativeFile;
		}
	
		$strAvatarURL = sprintf($this->url, $strUserID, $strAvatarHash);
		$result = $this->puf->fetch($strAvatarURL);
		if($result){
			$this->pfh->putContent($strRelativeFile, $result);
			if (is_file($strRelativeFile)) return $strRelativeFile;
		}
	
		$this->pfh->putContent($strRelativeFile, "");
		return $strRelativeFile;
	}
	
  }
}
?>