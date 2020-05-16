<?php
/*	Project:	EQdkp-Plus
 *	Package:	Discord Plugin
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

// EQdkp required files/vars
define('EQDKP_INC', true);
define('IN_ADMIN', true);
define('PLUGIN', 'discord');

$eqdkp_root_path = './../../../';
include_once($eqdkp_root_path.'common.php');

class discordAdminSettings extends page_generic {
	/**
	 * Constructor
	 */
	public function __construct(){
		// plugin installed?
		if (!$this->pm->check('discord', PLUGIN_INSTALLED))
			message_die($this->user->lang('discord_plugin_not_installed'));

			$handler = array(
					'ajaxguildid' => array('process' => 'ajaxGuildID', 'csrf' => false, 'check' => 'a_discord_manage'),
					'save' => array('process' => 'save', 'csrf' => true, 'check' => 'a_discord_manage'),
			);
			parent::__construct('a_discord_manage', $handler);

			$this->process();
	}

	private $arrData = false;

	public function save(){
		$objForm				= register('form', array('discord_settings'));
		$objForm->langPrefix	= 'discord_';
		$objForm->validate		= true;
		$objForm->add_fieldsets($this->fields());
		$arrValues				= $objForm->return_values();

		if($objForm->error){
			$this->arrData		= $arrValues;
		}else{
			// update configuration
			$this->config->set($arrValues, '', 'discord');
			// Success message - Message, Title
			$messages[]			= array($this->user->lang('save_suc'), $this->user->lang('settings'));
			$this->display($messages);
		}
	}
	
	public function ajaxGuildID(){
		$arrDiscordConfig = $this->config->get_config('discord');
		$token = $arrDiscordConfig['bot_token'];
		$result = register('urlfetcher')->fetch('https://discordapp.com/api/users/@me/guilds', array('Authorization: Bot '.$token));
		if($result){
			$arrJSON = json_decode($result, true);
			
			if(isset($arrJSON[0]['id'])){
				echo $arrJSON[0]['id'];
			}
		}	
		
		exit();
	}

	private function fields(){
		$arrFields = array(
				'general' => array(
						'bot_client_id' => array(
								'type' => 'text',
								'required' => true,
						),
						'bot_token' => array(
								'type' => 'text',
								'required' => true,
						),
						'guild_id' => array(
								'type' => 'text',
								'required' => true,
						),
				),
		);
		
		$arrValues		= $this->config->get_config('discord');
		if($arrValues['bot_client_id'] != ""){
			$arrFields['general']['bot_token']['after_txt'] = '<div><br /><a href="https://discordapp.com/oauth2/authorize?&client_id='.$arrValues['bot_client_id'].'&scope=bot&permissions=522304" target="_blank" class="button" onclick="init_functions()">'.$this->user->lang('discord_autorize_bot').'</a></div>';
		}
		
		if($arrValues['bot_client_id'] == "" && $arrValues['bot_token'] == ""){
			unset($arrFields['general']['guild_id']);
		}
		
		return $arrFields;
	}

	public function display($messages=array()){
		// -- Messages ------------------------------------------------------------
		if ($messages){
			foreach($messages as $val)
				$this->core->message($val[0], $val[1], 'green');
		}

		// get the saved data
		$arrValues		= $this->config->get_config('discord');
		if ($this->arrData !== false) $arrValues = $this->arrData;

		// -- Template ------------------------------------------------------------
		// initialize form class
		$objForm				= register('form', array('discord_settings'));
		$objForm->reset_fields();
		$objForm->lang_prefix	= 'discord_';
		$objForm->validate		= true;
		$objForm->use_fieldsets	= true;
		$objForm->add_fieldsets($this->fields());

		// Output the form, pass values in
		$objForm->output($arrValues);

		$this->core->set_vars(array(
				'page_title'	=> $this->user->lang('discord').' '.$this->user->lang('settings'),
				'template_path'	=> $this->pm->get_data('discord', 'template_path'),
				'template_file'	=> 'admin/settings.html',
				'page_path'			=> [
						['title'=>$this->user->lang('menu_admin_panel'), 'url'=>$this->root_path.'admin/'.$this->SID],
						['title'=>$this->user->lang('discord').': '.$this->user->lang('settings'), 'url'=>' '],
				],
				'display'		=> true
		));
	}

}
registry::register('discordAdminSettings');
?>