<?php
/*	Project:	EQdkp-Plus
 *	Package:	Voice Portal Module
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

class discordpostviewer extends gen_class {
	
	private $module_id = 0;
	private $wide_content = false;
	
	public function __construct($id, $blnWideContent=false){
		$this->module_id = $id;
		$this->wide_content = $blnWideContent;
		
		include_once($this->root_path.'plugins/discord/includes/Parsedown.php');
	}
	
	public function getChannels($blnReturnRaw = false){
		$arrOut = $arrJSON = array();
		
		$arrDiscordConfig = register('config')->get_config('discord');
		$guildid = $arrDiscordConfig['guild_id'];
		$token = $arrDiscordConfig['bot_token'];
		
		$result = register('urlfetcher')->fetch('https://discordapp.com/api/guilds/'.$guildid.'/channels', array('Authorization: Bot '.$token));
		if($result){
			$arrJSON = json_decode($result, true);
			
			foreach($arrJSON as $val){
				if($val['type'] == 'text'){
					$arrOut[$val['id']] = 	$val['name'];
				}
			}
		}
		
		return ($blnReturnRaw) ? $arrJSON : $arrOut;
	}
	
	public function getPosts($arrPrivateforums, $black_or_white, $topicnumber, $showcontent){
		$topicnumber	= ($this->config('amount')) ? $this->config('amount') : 5;
		$intCachetime	= ($this->config('cachetime')) ? (60*intval($this->config('cachetime'))) : (3*60);
		
		$arrLastMessage = $arrForums = array();
		$arrRawChannels = $this->getChannels(true);
		foreach($arrRawChannels as $val){
			if($val['type'] == '0'){
				$arrForums[$val['id']] = 	$val['name'];
				$arrLastMessage[$val['id']] = $val['last_message_id'];
			}
		}	
		
		$arrData = $arrTime = array();
		
		$Parsedown = new Parsedown();
		$Parsedown->setSafeMode(true);
		
		$arrDiscordConfig = register('config')->get_config('discord');
		$guildid = $arrDiscordConfig['guild_id'];
		$token = $arrDiscordConfig['bot_token'];
		
		foreach($arrForums as $forumID => $forumName){
			if($black_or_white == 'IN'){
				//WL
				if(is_array($arrPrivateforums) && !empty($arrPrivateforums) && !in_array($forumID, $arrPrivateforums)) continue;
			} else {
				//BL
				if(is_array($arrPrivateforums) && !empty($arrPrivateforums) && in_array($forumID, $arrPrivateforums)) continue;
			}

			
			$strLastMessage = $arrLastMessage[$forumID];
			if(!$strLastMessage) continue;
			
			$strCachedLastMessage = $this->pdc->get('discord.lastmessageid.'.$forumID);
			
			//Load from Cache
			if($strCachedLastMessage && ($strLastMessage === $strCachedLastMessage)) {
				$arrJSON = $this->pdc->get('discord.messages.'.$forumID);
				if($arrJSON){
					if(isset($arrJSON['error'])) continue;
					
					foreach($arrJSON as $arrPost){
						
						$arrData[] = array(
								'username'	=> $arrPost['author']['username'],
								'content' 	=> nl2br($Parsedown->text($arrPost['content'])),
								'topic_link'	=> 'https://discordapp.com/channels/'.$guildid.'/'.$forumID,
								'topic_title'	=> '#'.$forumName,
								'posttime'	=> $arrPost['timestamp'],
								'topic_id'	=> $forumID,
								'avatar'	=> ($arrPost['author']['avatar']) ? "https://cdn.discordapp.com/avatars/".$arrPost['author']['id']."/".$arrPost['author']['avatar'].".png" : "https://discordapp.com/assets/1cbd08c76f8af6dddce02c5138971129.png",
						);
						
						$arrTime[] = strtotime($arrPost['timestamp']);
					}
					
					continue;
				}
			}

			$this->pdc->put('discord.lastmessageid.'.$forumID, $strLastMessage, 3600*24*7);
			
			$result = register('urlfetcher')->fetch('https://discordapp.com/api/channels/'.$forumID.'/messages?around='.$strLastMessage.'&limit='.($topicnumber*2), array('Authorization: Bot '.$token));
			if($result){
				$arrJSON = json_decode($result, true);
				if($arrJSON){
					$this->pdc->put('discord.messages.'.$forumID, $arrJSON, 3600*24*7);
				}
				
				foreach($arrJSON as $arrPost){
					$arrData[] = array(
							'username'	=> $arrPost['author']['username'],
							'content' 	=> nl2br($Parsedown->text($arrPost['content'])),
							'topic_link'	=> 'https://discordapp.com/channels/'.$guildid.'/'.$forumID,
							'topic_title'	=> '#'.$forumName,
							'posttime'	=> $arrPost['timestamp'],
							'topic_id'	=> $forumID,
							'avatar'	=> ($arrPost['author']['avatar']) ? "https://cdn.discordapp.com/avatars/".$arrPost['author']['id']."/".$arrPost['author']['avatar'].".png" : "https://discordapp.com/assets/1cbd08c76f8af6dddce02c5138971129.png",
					);
					
					$arrTime[] = strtotime($arrPost['timestamp']);
				}
				
				
			} else {
				$arrJSON = array('error' => true);
				$this->pdc->put('discord.messages.'.$forumID, $arrJSON, 3600);
			}
		}
		
		//Now sort the date
		array_multisort($arrTime, SORT_DESC, SORT_NUMERIC, $arrData);
		
		if(count($arrData) > $topicnumber){
			$arrData = array_slice($arrData, 0, $topicnumber);
		}
		
		//Cache data
		register('pdc')->put('portal.module.discordlatestposts.'.$this->module_id.'.u'.$this->user->id, $arrData, $intCachetime);
		
		return $arrData;
	}
	
	public function output() {
		$arrData = $this->pdc->get('portal.module.discordlatestposts.'.$this->module_id.'.u'.$this->user->id);
		$myTarget	= '_blank';
		
		if(!$arrData){
			// Set some Variables we're using in the BB Modules..
			$topicnumber	= ($this->config('amount')) ? $this->config('amount') : 5;
			$black_or_white	= ($this->config('blackwhitelist') == 'white') ? 'IN' : 'NOT IN';
			
			// Create Array of allowed/disallowed forums
			$arrUserMemberships = $this->pdh->get('user_groups_users', 'memberships', array($this->user->id));
			array_push($arrUserMemberships, 0);
			$arrForums = array();
			$visibilityGrps = $this->config('visibility');
			
			foreach ($arrUserMemberships as $groupid) {
				//only load forums for which actual settings are set
				
				if(!in_array($groupid, $visibilityGrps)) {
					continue;
				}
				
				$strForums = $this->config('privateforums_'.$groupid);
				
				if(is_array($strForums)) $arrForums = array_merge($arrForums, $strForums);
			}
			
			$arrForums = array_unique($arrForums);
			
			// if there are no forums selected and its whitelist
			if (count($arrForums) == 0 && $black_or_white == 'IN') return $this->user->lang('discordlatestposts_noselectedboards');
			
			$arrData = $this->getPosts($arrForums, $black_or_white, $topicnumber, $this->config('showcontent'));

		} //Now we should have data
		
		// Wide Content
		$myOut = "";
		$blnForumName = false;
		if($this->wide_content){
			if(count($arrData)){
				$myOut = "<table class='table fullwidth colorswitch'>";
				
				foreach($arrData as $row){
					$myOut .= '<tr>
						<td>';
					
					$myOut .= '<div class="dclp_entry dclp_hori">';
					
					// output date as well as User and text
					$useravatar = $row['avatar'];
					if ($useravatar) $myOut .= '<div class="user-avatar-small user-avatar-border floatLeft"><img src="'.sanitize($useravatar).'" class="user-avatar small" /></div>';
					$myOut .= '<div class="dclp_date small dclp_text_margin">'.sanitize($row['username']).', '.sanitize($row['topic_title']).'; '. $this->time->createTimeTag(strtotime($row['posttime']), $this->time->user_date(strtotime($row['posttime']), true)).'</div>';
					$myOut .= '<div class="dclp_text">'. $row['content'].'</div>';
					$myOut .= '</div><div class="clear"></div>';
					
					$myOut .= '  </td>
						</tr>';
					
				}
				
				$myOut .= "</table>";

			} else {
				$myOut .= $this->user->lang('discordlatestposts_noentries');
			}

			
		} else {
			
			if(count($arrData)){
				$myOut = "<table class='table fullwidth colorswitch'>";
				
				foreach($arrData as $row){
					$myOut .= '<tr>
						<td>';
					
					$myOut .= '<div class="dclp_entry">';
					
					// output date as well as User and text
					$useravatar = $row['avatar'];
					if ($useravatar) $myOut .= '<div class="user-avatar-small user-avatar-border floatLeft"><img src="'.sanitize($useravatar).'" class="user-avatar small" /></div>';
					$myOut .= '<div class="dclp_date small dclp_text_margin">'.sanitize($row['username']).', '.sanitize($row['topic_title']).'<br />'. $this->time->createTimeTag(strtotime($row['posttime']), $this->time->user_date(strtotime($row['posttime']), true)).'</div>';
					$myOut .= '<div class="dclp_text">'. $row['content'].'</div>';
					$myOut .= '</div><div class="clear"></div>';
					
					$myOut .= '  </td>
						</tr>';
					
				}
				
				$myOut .= "</table>";
			} else {
				$myOut = $this->user->lang('discordlatestposts_noentries');
			}
		}
		
		return $myOut;
	}
	
	private function config($strValue){
		$strConfigVal = $this->config->get($strValue, 'pmod_'.$this->module_id);
		return $strConfigVal;
	}
	
}
?>
