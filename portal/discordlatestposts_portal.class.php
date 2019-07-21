<?php
/*	Project:	EQdkp-Plus
 *	Package:	Last posts Portal Module
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

if ( !defined('EQDKP_INC') ){
	header('HTTP/1.0 404 Not Found');exit;
}

class discordlatestposts_portal extends portal_generic {

	protected static $path		= 'discordlatestposts';
	
	protected static $data		= array(
			'name'			=> 'Latest Discord Posts',
			'version'		=> '0.1.0',
			'author'		=> 'GodMod',
			'icon'			=> 'fa-group',
			'contact'		=> EQDKP_PROJECT_URL,
			'description'	=> 'View the latest Discord posts.',
			'reload_on_vis'	=> true,
			'lang_prefix'	=> 'discord_',
			'multiple'		=> true,
	);

	protected static $multiple = true;
	
	protected static $apiLevel = 20;

	public function get_settings($state){
		$moduleID = $this->config('_module_id');
		
		$settings	= array(
				'amount'	=> array(
						'type'		=> 'spinner',
				),
				'cachetime' => array(
						'type' => 'spinner',
						'default' => 3,
				),
				'blackwhitelist'	=> array(
						'type'	=> 'dropdown',
						'options'	=> array(
								'black'		=> 'Blacklist',
								'white'		=> 'Whitelist',
						)
				),
		);
		
		include_once($this->root_path.'plugins/discord/portal/discordpostviewer.class.php');
		$objDiscordPostViewer = register('discordpostviewer', array($moduleID));

		$arrOptions = $objDiscordPostViewer->getChannels();

		$visibility = $this->config('visibility');
		if (is_array($visibility)) {
			foreach ($visibility as $key => $value){
				$dir_lang = $this->user->lang('discordlatestposts_f_privateforums').(((int)$value == 0) ? $this->user->lang('cl_all') : $this->pdh->get('user_groups', 'name', array($value)));
				
				$settings['privateforums_'.$value]	= array(
						'dir_lang'	=> $dir_lang,
						'type'		=> 'multiselect',
						'help'		=> 'discord_f_help_privateforums2',
						'options'	=> $arrOptions,
				);
				
			}
		}

		return $settings;
	}


	public function output() {
		$moduleID = $this->id;
		
		//Calculate Max Width
		if($this->wide_content){
			$max_width = '97%';
		} else {
			$max_width = "180px";
			
			if($this->position == 'left'){
				if($this->user->style['column_left_width'] != ""){
					if(strpos($this->user->style['column_left_width'], 'px') !== false){
						$max_width = (intval($this->user->style['column_left_width']) - 30).'px';
					} else {
						$max_width = '97%';
					}
					
				}
			} elseif($this->position == 'right'){
				if($this->user->style['column_right_width'] != ""){
					if(strpos($this->user->style['column_right_width'], 'px') !== false){
						$max_width = (intval($this->user->style['column_right_width']) - 30).'px';
					} else {
						$max_width = '97%';
					}
					
				}
			}

		}

		
		$this->tpl->add_css(
				".dclp_text_margin {
					margin-left: 38px;
				}
				
				.dclp_text {
					max-width: ".$max_width.";
					word-wrap:break-word;
				}

				.dclp_hori .dclp_text {
					margin-left: 40px;
				}
				"
				);
		
		$intCachetime	= ($this->config('cachetime')) ? (60*intval($this->config('cachetime'))) : (3*60);

		$this->tpl->add_js('
			setInterval(function() {
				$.get("'.$this->server_path.'plugins/discord/portal/ajax.php'.$this->SID.'&mid='.$moduleID.'&wide='.(($this->wide_content) ? 1 : 0).'", function(data){
					if(data){
						$(".discordposts_'.$moduleID.'_container").html(data);
					}
				});
				
			}, 1000*'.intval($intCachetime).');
		');
		
		
		include_once($this->root_path.'plugins/discord/portal/discordpostviewer.class.php');
		$objDiscordPostViewer = register('discordpostviewer', array($moduleID, $this->wide_content));
		
		$myOut = $objDiscordPostViewer->output();

		return '<div class="discordposts_'.$moduleID.'_container">'.$myOut.'</div>';
	}
	

}

?>
