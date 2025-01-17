<?php

/**
 * helpdesk - Menus
 *
 * @author Dave Hall <skwashd@phpgroupware.org>
 * @author Sigurd Nes <sigurdne@online.no>
 * @copyright Copyright (C) 2007,2008 Free Software Foundation, Inc. http://www.fsf.org/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @package helpdesk
 * @version $Id: class.menu.inc.php 6711 2010-12-28 15:15:42Z sigurdne $
 */

/*
	   This program is free software: you can redistribute it and/or modify
	   it under the terms of the GNU General Public License as published by
	   the Free Software Foundation, either version 2 of the License, or
	   (at your option) any later version.

	   This program is distributed in the hope that it will be useful,
	   but WITHOUT ANY WARRANTY; without even the implied warranty of
	   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	   GNU General Public License for more details.

	   You should have received a copy of the GNU General Public License
	   along with this program.  If not, see <http://www.gnu.org/licenses/>.
	 */

use App\modules\phpgwapi\services\Settings;
use App\modules\phpgwapi\security\Acl;
use App\modules\phpgwapi\services\Translation;
use App\modules\phpgwapi\controllers\Accounts\Accounts;
use App\modules\phpgwapi\controllers\Locations;




/**
 * Menus
 *
 * @package helpdesk
 */
class helpdesk_menu
{
	/**
	 * Get the menus for the helpdesk
	 *
	 * @return array available menus for the current user
	 */
	public function get_menu($type = '')
	{
		$userSettings = Settings::getInstance()->get('user');
		$flags = Settings::getInstance()->get('flags');
		$translation = Translation::getInstance();
		$location_obj = new Locations();
		$accounts_obj = new Accounts();


		$incoming_app			 = $flags['currentapp'];
		Settings::getInstance()->update('flags', ['currentapp' => 'helpdesk']);
		$acl					 =	Acl::getInstance();
		$menus = array();

		$config = CreateObject('phpgwapi.config', 'helpdesk')->read();
		if (!empty($config['app_name']))
		{
			$lang_app_name = $config['app_name'];
		}
		else
		{
			$lang_app_name = lang('helpdesk');
		}

		$menus['navbar'] = array(
			'helpdesk' => array(
				'text'	=> $lang_app_name,
				'url'	=> phpgw::link('/index.php', array('menuaction' => "helpdesk.uitts.index")),
				'image'	=> array('helpdesk', 'navbar'),
				'order'	=> 35,
				'group'	=> 'facilities management'
			),
		);

		$menus['toolbar'] = array();


		if (
			$acl->check('run', Acl::READ, 'admin')
			|| $acl->check('admin', Acl::ADD, 'helpdesk')
		)
		{

			$menus['admin'] = array(
				'index'	=> array(
					'text'	=> lang('Configuration'),
					'url'	=> phpgw::link('/index.php', array('menuaction' => 'admin.uiconfig.index', 'appname' => 'helpdesk')),
					'children' => array(
						'custom_config' => array(
							'text' => lang('custom config'),
							'nav_location' => 'navbar#' . $location_obj->get_id('helpdesk', '.admin'),
							'url' => phpgw::link('/index.php', array(
								'menuaction' => 'admin.uiconfig2.index',
								'location_id' => $location_obj->get_id('helpdesk', '.admin')
							))
						)
					)
				),
				'ticket_attribs' => array(
					'text' => lang('ticket Attributes'),
					'url' => phpgw::link('/index.php', array(
						'menuaction' => 'admin.ui_custom.list_attribute',
						'appname' => 'helpdesk',
						'location' => '.ticket',
						'menu_selection' => 'admin::helpdesk::ticket_attribs'
					))
				),
				'ticket_functions' => array(
					'text' => lang('custom functions'),
					'url' => phpgw::link('/index.php', array(
						'menuaction' => 'admin.ui_custom.list_custom_function',
						'appname' => 'helpdesk',
						'location' => '.ticket',
						'menu_selection' => 'admin::helpdesk::ticket_functions'
					))
				),
				'ticket_cats'	=> array(
					'text'	=> lang('Ticket Categories'),
					'url'	=> phpgw::link('/index.php', array('menuaction' => 'admin.uicategories.index', 'appname' => 'helpdesk', 'location' => '.ticket', 'global_cats' => 'true', 'menu_selection' => 'admin::helpdesk::ticket_cats'))
				),
				'cat_assignment'	=> array(
					'text'	=> lang('category assignment'),
					'url'	=> phpgw::link('/index.php', array('menuaction' => 'helpdesk.uicat_assignment.edit'))
				),
				'cat_respond_messages'	=> array(
					'text'	=> lang('category respond messages'),
					'url'	=> phpgw::link('/index.php', array('menuaction' => 'helpdesk.uicat_respond_messages.edit'))
				),
				'cat_anonyminizer'	=> array(
					'text'	=> lang('category anonyminizer'),
					'url'	=> phpgw::link('/index.php', array('menuaction' => 'helpdesk.uicat_anonyminizer.edit'))
				),
				'ticket_status'	=> array(
					'text'	=> lang('Ticket status'),
					'url'	=> phpgw::link('/index.php', array('menuaction' => 'helpdesk.uigeneric.index', 'type' => 'helpdesk_status'))
				),
				'acl'	=> array(
					'text'	=> lang('Configure Access Permissions'),
					'url'	=> phpgw::link('/index.php', array('menuaction' => 'preferences.uiadmin_acl.list_acl', 'acl_app' => 'helpdesk'))
				),
				'external_com_type' => array(
					'text' => lang('external communication type'),
					'url' => phpgw::link('/index.php', array(
						'menuaction' => 'helpdesk.uigeneric.index',
						'type' => 'external_com_type'
					))
				),
				'custom_menu_items' => array(
					'text' => lang('custom menu items'),
					'url' => phpgw::link('/index.php', array(
						'menuaction' => 'helpdesk.uigeneric.index',
						'type' => 'custom_menu_items'
					))
				),
				'async_settings' => array(
					'text' => lang('Asynchronous Tasks'),
					'url' => phpgw::link('/index.php', array(
						'menuaction' => 'helpdesk.uiasync_settings.index',
						'appname' => 'helpdesk'
					))
				),
			);
		}

		if (isset($userSettings['apps']['preferences']))
		{
			$menus['preferences'] = array(
				array(
					'text'	=> $translation->translate('Preferences', array(), true),
					'url'	=> phpgw::link('/preferences/section', array('appname' => 'helpdesk', 'type' => 'user'))
				),
				array(
					'text'	=> $translation->translate('Grant Access', array(), true),
					'url'	=> phpgw::link('/index.php', array('menuaction' => 'property.uiadmin.aclprefs', 'acl_app' => 'helpdesk'))
				)
			);

			$menus['toolbar'][] = array(
				'text'	=> $translation->translate('Preferences', array(), true),
				'url'	=> phpgw::link('/preferences/section', array('appname'	=> 'helpdesk')),
				'image'	=> array('helpdesk', 'preferences')
			);
		}

		$menus['navigation'] = array();


		if ($acl->check('.ticket', ACL_READ, 'helpdesk'))
		{
			$categories	= CreateObject('phpgwapi.categories', -1, 'helpdesk', '.ticket');
			$categories->supress_info	= true;

			$_cats = $categories->return_sorted_array(0, false, '', '', '', false, false);

			$_categories = array();
			$subs = false;
			foreach ($_cats as $_cat)
			{
				if ($_cat['level'] == 0 && $_cat['active'] != 2 && $acl->check(".ticket.category.{$_cat['id']}", ACL_READ, 'helpdesk'))
				{
					$_categories[] = $_cat;
				}
				else if ($_cat['level'] > 0 && $_cat['active'] != 2)
				{
					$subs = true;
				}
			}

			if ($subs)
			{

				$menus['navbar']['helpdesk']['url'] = phpgw::link('/index.php', array('menuaction' => 'helpdesk.uitts.index', 'parent_cat_id' => -1));

				$default_interface = isset($config['tts_default_interface']) ? $config['tts_default_interface'] : '';

				$_simple = false;
				/*
					 * Inverted logic
					 */
				if ($default_interface == 'simplified')
				{
					$_simple = true;
				}

				$user_groups =  $accounts_obj->membership($userSettings['account_id']);
				$simple_group = isset($config['fmttssimple_group']) ? $config['fmttssimple_group'] : array();
				foreach ($user_groups as $group => $dummy)
				{
					if (in_array($group, $simple_group))
					{
						if ($default_interface == 'simplified')
						{
							$_simple = false;
						}
						else
						{
							$_simple = true;
						}
						break;
					}
				}

				if (!$_simple)
				{
					$menus['navigation']['helpdesk_-2'] = array(
						'url'	=> phpgw::link('/index.php', array('menuaction' => 'helpdesk.uitts.index', 'parent_cat_id' => -2)),
						'text'	=> lang('top level'),
						'image'		=> array('helpdesk', 'helpdesk')
					);
				}

				foreach ($_categories as $_category)
				{
					$menus['navigation']["helpdesk_{$_category['id']}"] = array(
						'url'	=> phpgw::link('/index.php', array('menuaction' => 'helpdesk.uitts.index', 'parent_cat_id' => $_category['id'])),
						'text'	=> $_category['name'],
						'image'		=> array('helpdesk', 'helpdesk')
					);
				}
			}
			else
			{
				$menus['navigation']['helpdesk'] = array(
					'url'	=> phpgw::link('/index.php', array('menuaction' => 'helpdesk.uitts.index')),
					'text'	=> lang('inbox'),
					'image'		=> array('helpdesk', 'helpdesk')
				);
			}
		}



		if ($acl->check('.ticket.response_template', ACL_READ, 'helpdesk')) //manage
		{
			$menus['navigation']['response_template'] = array(
				'url' => phpgw::link('/index.php', array(
					'menuaction' => 'helpdesk.uigeneric.index',
					'type' => 'response_template'
				)),
				'text' => lang('response template'),
				'image' => array('helpdesk', 'helpdesk')
			);
		}

		if ($acl->check('.email_out', ACL_READ, 'helpdesk')) //manage
		{
			$menus['navigation']['email_out'] = array(
				'text' => lang('email out'),
				'url' => phpgw::link('/index.php', array('menuaction' => 'helpdesk.uiemail_out.index')),
				'image' => array('helpdesk', 'helpdesk'),
				'children' => array(
					'template' => array(
						'text' => lang('email template'),
						'url' => phpgw::link('/index.php', array(
							'menuaction' => 'helpdesk.uigeneric.index',
							'type' => 'email_template',
							'admin' => true
						))
					),
					'recipient_set' => array(
						'text' => lang('admin recipient set'),
						'url' => phpgw::link('/index.php', array(
							'menuaction' => 'helpdesk.uigeneric.index',
							'type' => 'email_recipient_set',
							'admin' => true
						))
					),
					'recipient_list' => array(
						'text' => lang('recipient list'),
						'url' => phpgw::link('/index.php', array(
							'menuaction' => 'helpdesk.uigeneric.index',
							'type' => 'email_recipient_list',
							'admin' => true
						))
					),
				)
			);
		}

		if ($acl->check('.custom', ACL_READ, 'helpdesk')) //manage
		{
			$custom_menu_items = CreateObject('helpdesk.sogeneric', 'custom_menu_items')->read_tree(array(
				'type' => 'custom_menu_items',
				'filter' => array('location' => '.ticket')
			));

			if ($custom_menu_items)
			{
				$menus['navigation']['report'] = array(
					'url'	=> phpgw::link('/index.php', array('menuaction' => 'helpdesk.uicustom.index')),
					'text' => lang('reports'),
					'image' => array('helpdesk', 'helpdesk')
				);
				foreach ($custom_menu_items as $item)
				{
					if (empty($item['local_files']))
					{
						$item['url'] .= '&' . get_phpgw_session_url();
					}
					$menus['navigation']['report']['children'][] = $item;
				}
			}
		}

		Settings::getInstance()->update('flags', ['currentapp' => $incoming_app]);

		return $menus;
	}
}
