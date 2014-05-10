<?php

/***************************************************************************
 *
 *	OUGC Revatar plugin (/inc/languages/english/admin/ougc_revatar.php)
 *	Author: Omar Gonzalez
 *	Copyright: © 2014 Omar Gonzalez
 *
 *	Website: http://omarg.me
 *
 *	Basic avatar feature replication.
 *
 ***************************************************************************

****************************************************************************
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
****************************************************************************/

// Die if IN_MYBB is not defined, for security reasons.
defined('IN_MYBB') or die('Direct initialization of this file is not allowed.');

// Run/Add Hooks
if(defined('IN_ADMINCP'))
{
	$plugins->add_hook('admin_config_settings_start', 'ougc_revatar_lang_load');
	$plugins->add_hook('admin_style_templates_set', 'ougc_revatar_lang_load');
	$plugins->add_hook('admin_config_settings_change', 'ougc_revatar_settings_change');
}
else
{
	global $plugins, $mybb;

	$plugins->add_hook('postbit', 'ougc_revatar_postbit');
	$plugins->add_hook('postbit_pm', 'ougc_revatar_postbit');
	$plugins->add_hook('postbit_prev', 'ougc_revatar_postbit');
	$plugins->add_hook('postbit_announcement', 'ougc_revatar_postbit');
	$plugins->add_hook('portal_start', 'ougc_revatar_portal');
	$plugins->add_hook('portal_announcement', 'ougc_revatar_postbit');

	// Temporal method to change users revatars
	$plugins->add_hook('member_profile_end', 'ougc_revatar_profile');

	if(in_array(THIS_SCRIPT, array('portal.php', 'private.php', 'showthread.php', 'editpost.php', 'newthread.php', 'newreply.php')))
	{
		global $templatelist;

		if(!isset($templatelist))
		{
			$templatelist = '';
		}
		else
		{
			$templatelist .= ',';
		}

		$templatelist .= 'ougc_revatar';
	}
}

// PLUGINLIBRARY
defined('PLUGINLIBRARY') or define('PLUGINLIBRARY', MYBB_ROOT.'inc/plugins/pluginlibrary.php');

// Plugin API
function ougc_revatar_info()
{
	global $lang;
	ougc_revatar_lang_load();

	return array(
		'name'			=> 'OUGC Revatar',
		'description'	=> $lang->setting_group_ougc_revatar_desc,
		'website'		=> 'http://omarg.me',
		'author'		=> 'Omar G.',
		'authorsite'	=> 'http://omarg.me',
		'version'		=> '0.1',
		'versioncode'	=> '0100',
		'compatibility'	=> '16*,17*',
		'guid' 			=> '',
		'pl'			=> array(
			'version'	=> 12,
			'url'		=> 'http://mods.mybb.com/view/pluginlibrary'
		)
	);
}

// _activate() routine
function ougc_revatar_activate()
{
	global $PL, $lang, $cache;
	ougc_revatar_lang_load();
	ougc_revatar_deactivate();

	// Add settings group
	$PL->settings('ougc_revatar', $lang->setting_group_ougc_revatar, $lang->setting_group_ougc_revatar_desc, array(
		'default'	=> array(
		   'title'			=> $lang->setting_ougc_revatar_default,
		   'description'	=> $lang->setting_ougc_revatar_default_desc,
		   'optionscode'	=> 'text',
			'value'			=>	'default'
		),
		'defaultdims'	=> array(
		   'title'			=> $lang->setting_ougc_revatar_defaultdims,
		   'description'	=> $lang->setting_ougc_revatar_defaultdims_desc,
		   'optionscode'	=> 'text',
			'value'			=>	'88|33'
		),
		'maxsize'	=> array(
		   'title'			=> $lang->setting_ougc_revatar_maxsize,
		   'description'	=> $lang->setting_ougc_revatar_maxsize_desc,
		   'optionscode'	=> 'text',
			'value'			=>	'88x33'
		)
	));

	// Add template group
	$PL->templates('ougcrevatar', '<lang:setting_group_ougc_revatar>', array(
		''	=> '<img src="{$settings[\'bburl\']}/{$revatar[\'image\']}" alt="{$revatar[\'username\']}" title="{$revatar[\'username\']}" width="{$revatar[\'width\']}" height="{$revatar[\'height\']}" />'
	));

	// Modify templates
	require_once MYBB_ROOT.'inc/adminfunctions_templates.php';
	find_replace_templatesets('member_profile_modoptions', '#'.preg_quote('</ul>').'#', '</ul><!--OUGC_REVATAR-->', 0);

	// Insert/update version into cache
	$plugins = $cache->read('ougc_plugins');
	if(!$plugins)
	{
		$plugins = array();
	}

	$info = ougc_revatar_info();

	if(!isset($plugins['revatar']))
	{
		$plugins['revatar'] = $info['versioncode'];
	}

	/*~*~* RUN UPDATES START *~*~*/

	/*~*~* RUN UPDATES END *~*~*/

	$plugins['revatar'] = $info['versioncode'];
	$cache->update('ougc_plugins', $plugins);
}

// _deactivate() routine
function ougc_revatar_deactivate()
{
	ougc_revatar_pl_check();

	// Revert template edits
	require_once MYBB_ROOT.'inc/adminfunctions_templates.php';
	find_replace_templatesets('postbit', '#'.preg_quote('{$post[\'ougc_revatar\']}').'#', '', 0);
	find_replace_templatesets('postbit_classic', '#'.preg_quote('{$post[\'ougc_revatar\']}').'#', '', 0);
	find_replace_templatesets('portal_announcement', '#'.preg_quote('{$announcement[\'ougc_revatar\']}').'#', '', 0);
	find_replace_templatesets('member_profile_modoptions', '#'.preg_quote('<!--OUGC_REVATAR-->').'#', '', 0);
}

// install() routine
function ougc_revatar_install()
{
	global $db;

	// Add DB entries
	if(!$db->field_exists('ougc_revatar', 'users'))
	{
		$db->add_column('users', 'ougc_revatar', 'varchar(200) NOT NULL default \'\'');
	}
	if(!$db->field_exists('ougc_revatardimensions', 'users'))
	{
		$db->add_column('users', 'ougc_revatardimensions', 'varchar(10) NOT NULL default \'\'');
	}
	if(!$db->field_exists('ougc_revatartype', 'users'))
	{
		$db->add_column('users', 'ougc_revatartype', 'varchar(10) NOT NULL default \'\'');
	}
}

// _is_installed() routine
function ougc_revatar_is_installed()
{
	global $db;

	return $db->field_exists('ougc_revatar', 'users');
}

// _uninstall() routine
function ougc_revatar_uninstall()
{
	global $db, $PL, $cache;
	ougc_revatar_pl_check();

	// Drop DB entries
	if($db->field_exists('ougc_revatar', 'users'))
	{
		$db->drop_column('users', 'ougc_revatar');
	}
	if($db->field_exists('ougc_revatardimensions', 'users'))
	{
		$db->drop_column('users', 'ougc_revatardimensions');
	}
	if($db->field_exists('ougc_revatartype', 'users'))
	{
		$db->drop_column('users', 'ougc_revatartype');
	}

	$PL->settings_delete('ougc_revatar');
	$PL->templates_delete('ougcrevatar');

	// Delete version from cache
	$plugins = (array)$cache->read('ougc_plugins');

	if(isset($plugins['revatar']))
	{
		unset($plugins['revatar']);
	}

	if(!empty($plugins))
	{
		$cache->update('ougc_plugins', $plugins);
	}
	else
	{
		$PL->cache_delete('ougc_plugins');
	}
}

// Loads language strings
function ougc_revatar_lang_load()
{
	global $lang;

	isset($lang->setting_group_ougc_revatar) or $lang->load('ougc_revatar');
}

// PluginLibrary dependency check & load
function ougc_revatar_pl_check()
{
	global $lang;
	ougc_revatar_lang_load();
	$info = ougc_revatar_info();

	if(!file_exists(PLUGINLIBRARY))
	{
		flash_message($lang->sprintf($lang->ougc_revatar_pl_required, $info['pl']['url'], $info['pl']['version']), 'error');
		admin_redirect('index.php?module=config-plugins');
		exit;
	}

	global $PL;

	$PL or require_once PLUGINLIBRARY;

	if($PL->version < $info['pl']['version'])
	{
		flash_message($lang->sprintf($lang->ougc_revatar_pl_old, $info['pl']['url'], $info['pl']['version'], $PL->version), 'error');
		admin_redirect('index.php?module=config-plugins');
		exit;
	}
}

// Language support for settings
function ougc_revatar_settings_change()
{
	global $db, $mybb;

	$query = $db->simple_select('settinggroups', 'name', 'gid=\''.(int)$mybb->input['gid'].'\'');
	$groupname = $db->fetch_field($query, 'name');

	if($groupname == 'ougc_revatar')
	{
		global $plugins;
		ougc_revatar_lang_load();
	}
}

// Temporal method to change users revatars
function ougc_revatar_profile()
{
	global $mybb, $modoptions;

	if(!$mybb->usergroup['canmodcp'] || !$modoptions)
	{
		return;
	}

	global $lang, $PL, $memprofile;
	$PL or require_once PLUGINLIBRARY;

	$profilelink = get_profile_link($memprofile['uid']);
	$url = $mybb->settings['bburl'].'/'.$PL->url_append($profilelink, array('revatar' => 1));

	if(isset($mybb->input['revatar']))
	{
		if($mybb->request_method == 'post')
		{
			global $db;

			$db->update_query('users', array(
				'ougc_revatar'				=> $db->escape_string($mybb->input['ougc_revatar'].'?dateline='.TIME_NOW),
				'ougc_revatardimensions'	=> $db->escape_string($mybb->input['ougc_revatardimensions']),
				'ougc_revatartype'			=> $db->escape_string($mybb->input['ougc_revatartype']),
			), 'uid=\''.(int)$memprofile['uid'].'\'');

			redirect($profilelink);
		}

		foreach(array('', 'dimensions', 'type') as $field)
		{
			if(!isset($mybb->input['ougc_revatar'.$field]))
			{
				$mybb->input['ougc_revatar'.$field] = $memprofile['ougc_revatar'.$field];
			}
		}

		error('
<form action="'.$url.'" method="post">
	URL: <input name="ougc_revatar" type="text" class="textbox" value="'.htmlspecialchars_uni($mybb->input['ougc_revatar'] ? $mybb->input['ougc_revatar'] : './uploads/revatars/revatar_X.png').'" />
	Dimensions: <input name="ougc_revatardimensions" type="text" class="textbox" value="'.htmlspecialchars_uni($mybb->input['ougc_revatardimensions'] ? $mybb->input['ougc_revatardimensions'] : '88|33').'" />
	Type: <input name="ougc_revatartype" type="text" class="textbox" value="'.htmlspecialchars_uni($mybb->input['ougc_revatartype'] ? $mybb->input['ougc_revatartype'] : 'upload').'" />
	<input value="'.$lang->go.'" type="submit" class="button" />
</form>');
	}

	$revatar_options = '<li><a href="'.$url.'">Change Revatar</a></li>';
	$modoptions = str_replace('<!--OUGC_REVATAR-->', $revatar_options, $modoptions);
}

// Hijack the portal query
function ougc_revatar_portal()
{
	control_object($GLOBALS['db'], '
		function query($string, $hide_errors=0, $write_query=0)
		{
			static $done = false;
			if(!$done && !$write_query && my_strpos($string, \'u.username, u.avatar, u.avatardimensions\'))
			{
				$done = true;
				$string = strtr($string, array(
					\'avatardimensions\' => \'avatardimensions, u.ougc_revatar, u.ougc_revatardimensions, u.ougc_revatartype\'
				));
			}
			return parent::query($string, $hide_errors, $write_query);
		}
	');
}

// Format the ratavar
function ougc_revatar_postbit(&$post)
{
	if(THIS_SCRIPT == 'portal.php')
	{
		global $announcement;

		$post = &$announcement;
	}

	global $settings;

	if(empty($post['ougc_revatar']))
	{
		$post['ougc_revatar'] = $settings['ougc_revatar_default'];
		$post['ougc_revatardimensions'] = $settings['ougc_revatar_defaultdims'];
	}

	if(empty($post['ougc_revatar']))
	{
		$post['ougc_revatar'] = '';

		return;
	}

	$dims = explode('|', $post['ougc_revatardimensions']);

	if(isset($dims[0]) && isset($dims[1]))
	{
		list($maxw, $maxh) = explode('x', my_strtolower($settings['ougc_revatar_maxsize']));

		if($dims[0] > $maxw || $dims[1] > $maxh)
		{
			require_once MYBB_ROOT.'inc/functions_image.php';

			$scaled = scale_image($dims[0], $dims[1], $maxw, $maxh);
		}
	}

	$revatar = array(
		'image'		=> htmlspecialchars_uni($post['ougc_revatar']),
		'username'	=> htmlspecialchars_uni($post['username']),
		'width'		=> isset($scaled['width']) ? $scaled['width'] : (int)$dims[0],
		'height'	=> isset($scaled['height']) ? $scaled['height'] : (int)$dims[1],
	);

	global $templates;

	eval('$post[\'ougc_revatar\'] = "'.$templates->get('ougcrevatar').'";');
}

// control_object by Zinga Burga from MyBBHacks ( mybbhacks.zingaburga.com ), 1.62
if(!function_exists('control_object'))
{
	function control_object(&$obj, $code)
	{
		static $cnt = 0;
		$newname = '_objcont_'.(++$cnt);
		$objserial = serialize($obj);
		$classname = get_class($obj);
		$checkstr = 'O:'.strlen($classname).':"'.$classname.'":';
		$checkstr_len = strlen($checkstr);
		if(substr($objserial, 0, $checkstr_len) == $checkstr)
		{
			$vars = array();
			// grab resources/object etc, stripping scope info from keys
			foreach((array)$obj as $k => $v)
			{
				if($p = strrpos($k, "\0"))
				{
					$k = substr($k, $p+1);
				}
				$vars[$k] = $v;
			}
			if(!empty($vars))
			{
				$code .= '
					function ___setvars(&$a) {
						foreach($a as $k => &$v)
							$this->$k = $v;
					}
				';
			}
			eval('class '.$newname.' extends '.$classname.' {'.$code.'}');
			$obj = unserialize('O:'.strlen($newname).':"'.$newname.'":'.substr($objserial, $checkstr_len));
			if(!empty($vars))
			{
				$obj->___setvars($vars);
			}
		}
		// else not a valid object or PHP serialize has changed
	}
}