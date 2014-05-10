<?php

/***************************************************************************
 *
 *	OUGC Revatar plugin (/inc/languages/english/admin/ougc_revatar.lang.php)
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

// Plugin API
$l['setting_group_ougc_revatar'] = 'OUGC Revatar';
$l['setting_group_ougc_revatar_desc'] = 'Basic avatar feature replication.';

// Settings
$l['setting_ougc_revatar_maxsize'] = 'Maximum Revatar Dimensions';
$l['setting_ougc_revatar_maxsize_desc'] = 'Maximum image dimensions for revatars on the format WIDTH<b>x</b>HEIGHT';
$l['setting_ougc_revatar_default'] = 'Default Revatar Image';
$l['setting_ougc_revatar_default_desc'] = 'Default image to use as revatar for users without one.';
$l['setting_ougc_revatar_defaultdims'] = 'Default Revatar Dimensions';
$l['setting_ougc_revatar_defaultdims_desc'] = 'Default image dimensions for the default revatar on the format WIDTH<b>|</b>HEIGHT';

// PluginLibrary
$l['ougc_revatar_pl_required'] = 'This plugin requires <a href="{1}">PluginLibrary</a> version {2} or later to be uploaded to your forum.';
$l['ougc_revatar_pl_old'] = 'This plugin requires <a href="{1}">PluginLibrary</a> version {2} or later, whereas your current version is {3}.';