<?php
	/**
	 * phpGroupWare - property: a Facilities Management System.
	 *
	 * @author Sigurd Nes <sigurdne@online.no>
	 * @copyright Copyright (C) 2003,2004,2005,2006,2007,2008,2009,2010,2011,2012 Free Software Foundation, Inc. http://www.fsf.org/
	 * This file is part of phpGroupWare.
	 *
	 * phpGroupWare is free software; you can redistribute it and/or modify
	 * it under the terms of the GNU General Public License as published by
	 * the Free Software Foundation; either version 2 of the License, or
	 * (at your option) any later version.
	 *
	 * phpGroupWare is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * along with phpGroupWare; if not, write to the Free Software
	 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	 *
	 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
	 * @internal Development of this application was funded by http://www.bergen.kommune.no/bbb_/ekstern/
	 * @package property
	 * @subpackage admin
	 * @version $Id$
	 */

	use App\modules\phpgwapi\services\Settings;
	use App\modules\phpgwapi\security\Acl;
	use App\modules\phpgwapi\services\Cache;
	use App\modules\phpgwapi\services\Translation;

	 phpgw::import_class('phpgwapi.uicommon_jquery');

	/**
	 * Description
	 * @package property
	 */
	class property_uigeneric extends phpgwapi_uicommon_jquery
	{

		protected $appname		 = 'property';
		private $receipt		 = array();
		private $call_appname;
		var $grants;
		var $start;
		var $query;
		var $sort;
		var $order;
		var $sub;
		var $currentapp;
		var $location_info;
		var $bo, $bocommon,$allrows,$user_id,
		$acl, $acl_location, $acl_read, $acl_add, $acl_edit,$acl_delete, $acl_manage,
		$custom, $account,$type, $type_id, $translation;

		var $public_functions = array
			(
			'query'			 => true,
			'index'			 => true,
			'add'			 => true,
			'edit'			 => true,
			'save'			 => true,
			'delete'		 => true,
			'download'		 => true,
			'columns'		 => true,
			'attrib_history' => true,
			'edit_field'	 => true,
			'get_list'		 => true
		);

		function __construct()
		{
			parent::__construct();
			array_push(parent::$tmpl_search_path, PHPGW_SERVER_ROOT . '/property/templates/base');
			$called_class		 = get_called_class();
			$called_class_arr	 = explode('_', $called_class);
			$call_appname		 = !empty($called_class_arr[0]) && !empty($this->apps[$called_class_arr[0]]) ? $called_class_arr[0] : 'property';
			$this->bo			 = CreateObject("{$call_appname}.bogeneric");
			$this->call_appname	 = $call_appname;

			$this->account	 = $this->userSettings['account_id'];
			$this->bo->get_location_info();
			$this->bocommon	 = & $this->bo->bocommon;
			$this->custom	 = & $this->bo->custom;
			$this->translation = Translation::getInstance();

			$this->location_info								 = $this->bo->location_info;
			$this->flags['menu_selection']	 = $this->location_info['menu_selection'];
			$this->acl											 = Acl::getInstance();
			$this->acl_location									 = $this->location_info['acl_location'];
			$this->acl_read										 = $this->acl->check($this->acl_location, ACL_READ, $this->location_info['acl_app']);
			$this->acl_add										 = $this->acl->check($this->acl_location, ACL_ADD, $this->location_info['acl_app']);
			$this->acl_edit										 = $this->acl->check($this->acl_location, ACL_EDIT, $this->location_info['acl_app']);
			$this->acl_delete									 = $this->acl->check($this->acl_location, ACL_DELETE, $this->location_info['acl_app']);
			$this->acl_manage									 = $this->acl->check($this->acl_location, 16, $this->location_info['acl_app']);

			$this->start	 = $this->bo->start;
			$this->query	 = $this->bo->query;
			$this->sort		 = $this->bo->sort;
			$this->order	 = $this->bo->order;
			$this->allrows	 = $this->bo->allrows;

			$this->type		 = $this->bo->type;
			$this->type_id	 = $this->bo->type_id;

			if ($appname = $this->bo->appname)
			{
				if($this->flags['menu_selection'])
				{
					$this->flags['menu_selection']	 = str_replace('property', $appname, $this->flags['menu_selection']);
				}
				$this->appname										 = $appname;
			}

			$_menu_selection = Sanitizer::get_var('menu_selection');
			//Override
			if ($_menu_selection)
			{
				$this->flags['menu_selection'] = $_menu_selection;
			}

			Settings::getInstance()->update('flags', ['menu_selection' => $this->flags['menu_selection']]);

		}

		function get_list()
		{
			$params = array(
				'type'		 => Sanitizer::get_var('type'),
				'selected'	 => Sanitizer::get_var('selected'),
				'mapping'	 => Sanitizer::get_var('mapping'),
				'filter'	 => Sanitizer::get_var('filter'),
			);

			return $this->bo->get_list($params);
		}
		/*
		 * Overrides with incoming data from POST
		 */

		private function _populate( $data = array() )
		{
			$id_name = $this->location_info['id']['name'];

//			$id = Sanitizer::get_var($id_name);
			$id = $_POST[$id_name];

			$values = array();

			if ($id || $id === '0')
			{
				$values[$id_name] = $id;
			}
			else
			{
				$values[$id_name] = Sanitizer::clean_value($_POST['values'][$id_name]);
			}

			foreach ($this->location_info['fields'] as $field)
			{
				switch ($field['type'])
				{
					case 'integer':
					case 'int':
						$value_type	 = 'int';
						break;
					case 'html':
						$value_type	 = 'html';
						break;
					case 'date':
						$value_type	 = 'date';
						break;
					default:
						$value_type	 = 'string';
						break;
				}
				$values[$field['name']] = Sanitizer::clean_value($_POST['values'][$field['name']], $value_type);
			}

			$values_attribute = Sanitizer::get_var('values_attribute');

			if ($this->location_info['id']['type'] != 'auto')
			{
				if (empty($values[$id_name]) && $values[$id_name] !== '0')
				{
					$this->receipt['error'][] = array('msg' => lang('missing value for %1', lang('id')));
				}
			}

			if ($values[$id_name] && $this->location_info['id']['type'] == 'int' && !ctype_digit($values[$id_name]))
			{
				$this->receipt['error'][] = array('msg' => lang('Please enter an integer !'));
				unset($values[$id_name]);
			}

			if ($values[$id_name] || $values[$id_name] === '0')
			{
				$data[$id_name] = $values[$id_name];
			}

			foreach ($this->location_info['fields'] as $_field)
			{

				switch ($_field['type'])
				{
					case 'integer':
					case 'int':
						$value_type	 = 'int';
						break;
					case 'html':
						$value_type	 = 'html';
						break;
					default:
						$value_type	 = 'string';
						break;
				}

				if ($_field['type'] == 'html')
				{
					$value_type = 'html';
				}
				$data[$_field['name']]	 = $values[$_field['name']];
//				$data[$_field['name']]	 = Sanitizer::clean_value($data[$_field['name']], $value_type);

				if (isset($_field['nullable']) && $_field['nullable'] != true)
				{
					if (empty($data[$_field['name']]))
					{
						$this->receipt['error'][] = array('msg' => lang('missing value for %1', $_field['name']));
					}
				}
			}
			/*
			 * Extra data from custom fields
			 */

			if (isset($values_attribute) && is_array($values_attribute))
			{
				foreach ($values_attribute as $attribute)
				{
					if ($attribute['nullable'] != 1 && (!$attribute['value'] && !$values['extra'][$attribute['name']]))
					{
						$this->receipt['error'][] = array('msg' => lang('Please enter value for attribute %1', $attribute['input_text']));
					}

					if (isset($attribute['value']) && $attribute['value'] && $attribute['datatype'] == 'I' && !ctype_digit($attribute['value']))
					{
						$this->receipt['error'][] = array('msg' => lang('Please enter integer for attribute %1', $attribute['input_text']));
					}
				}

				$data['attributes'] = $values_attribute;
			}

			return $data;
		}

		private function _get_filters( $selected = 0 )
		{
			$values_combo_box	 = array();
			$combos				 = array();
			$i					 = 0;
			foreach ($this->location_info['fields'] as $field)
			{
				if (!empty($field['filter']) && empty($field['hidden']))
				{
					if ($field['values_def']['valueset'])
					{
						$values_combo_box[] = $field['values_def']['valueset'];
						// TODO find selected value
					}
					else if (isset($field['values_def']['method']))
					{
						$method_input = array();
						foreach ($field['values_def']['method_input'] as $_argument => $_argument_value)
						{
							if(!is_array($_argument_value))
							{
								if (preg_match('/^\$this->/', $_argument_value))
								{
									$_argument_value_name	 = ltrim($_argument_value, '$this->');
									$_argument_value		 = $this->$_argument_value_name;
								}
								else if (preg_match('/^##/', $_argument_value))
								{
									$_argument_value_name	 = trim($_argument_value, '#');
									$_argument_value		 = Sanitizer::get_var($_argument_value_name);
								}
							}
							$method_input[$_argument] = $_argument_value;
						}
						$values_combo_box[] = execMethod($field['values_def']['method'], $method_input);
					}
					$default_value = array('id' => '', 'name' => lang('select') . ' ' . $field['descr']);
					array_unshift($values_combo_box[$i], $default_value);

					$combos[$i] = array('type'	 => 'filter',
						'name'	 => "filter_{$field['name']}",
						'text'	 => $field['descr'] . ':',
						'list'	 => $values_combo_box[$i]
					);
					$i++;
				}
			}

			return $combos;
		}

		function save_sessiondata()
		{
			$data = array
				(
				'start'		 => $this->start,
				'query'		 => $this->query,
				'sort'		 => $this->sort,
				'order'		 => $this->order,
				'allrows'	 => $this->allrows,
				'type'		 => $this->type
			);
			$this->bo->save_sessiondata($data);
		}

		function download()
		{
			$values	 = $this->query();
			$uicols	 = $this->bo->uicols;
			$this->bocommon->download($values, $uicols['name'], $uicols['descr'], $uicols['input_type']);
		}

		function columns()
		{
			$this->flags['xslt_app'] = true;
			$this->flags['noframework']	 = true;
			Settings::getInstance()->set('flags', $this->flags);

			$template_set = isset($this->serverSettings['template_set']) ? $this->serverSettings['template_set'] : 'base';
			$xsl_rootdir = PHPGW_SERVER_ROOT . "/property/templates/{$template_set}";
			phpgwapi_xslttemplates::getInstance()->add_file(array('columns'), $xsl_rootdir);
			$values											 = Sanitizer::get_var('values');

			if ($values['save'])
			{
				$preferences = createObject('phpgwapi.preferences', $this->account);
				$preferences->read();
				$preferences->add($this->location_info['acl_app'], "generic_columns_{$this->type}_{$this->type_id}", $values['columns'], 'user');
				$preferences->save_repository();

				$receipt['message'][] = array('msg' => lang('columns is updated'));
			}

			$function_msg = lang('Select Column');

			$link_data = array
				(
				'menuaction' => "{$this->call_appname}.uigeneric.columns",
				'type'		 => $this->type,
				'type_id'	 => $this->type_id
			);

			$msgbox_data = $this->bocommon->msgbox_data($receipt);

			$data = array
				(
				'msgbox_data'	 => $this->phpgwapi_common->msgbox($msgbox_data),
				'column_list'	 => $this->bo->column_list($values['columns'], $allrows		 = true),
				'function_msg'	 => $function_msg,
				'form_action'	 => phpgw::link('/index.php', $link_data),
				'lang_columns'	 => lang('columns'),
				'lang_none'		 => lang('None'),
				'lang_save'		 => lang('save'),
				'select_name'	 => 'period'
			);

			$this->flags['app_header'] = $function_msg;
			Settings::getInstance()->update('flags', ['app_header' => $this->flags['app_header']]);
			phpgwapi_xslttemplates::getInstance()->set_var('phpgw', array('columns' => $data));
		}

		function index()
		{
			if (!$this->acl_read)
			{
				$this->bocommon->no_access();
				return;
			}

			$this->apps['manual']['section'] = "general.index.{$this->type}";
			Settings::getInstance()->set('apps', $this->apps);

			if (Sanitizer::get_var('phpgw_return_as') == 'json')
			{
				return $this->query();
			}


			$appname										 = $this->location_info['name'];
			$this->flags['app_header']	 = $this->translation->translate($this->location_info['acl_app'], array(), false, $this->location_info['acl_app']) . "::{$appname}";
			Settings::getInstance()->update('flags', ['app_header' => $this->flags['app_header']]);

			$data = array(
				'datatable_name' => $appname,
				'form'			 => array(
					'toolbar' => array(
						'item' => array(
						)
					)
				),
				'datatable'		 => array(
					'source'		 => self::link(array(
						'menuaction'		 => "{$this->call_appname}.uigeneric.index",
						'appname'			 => $this->appname,
						'type'				 => $this->type,
						'type_id'			 => $this->type_id,
						'phpgw_return_as'	 => 'json'
					)),
					'download'		 => self::link(array('menuaction' => "{$this->call_appname}.uigeneric.download",
						'appname'	 => $this->appname,
						'type'		 => $this->type,
						'type_id'	 => $this->type_id,
						'export'	 => true,
						'allrows'	 => true)),
					"columns"		 => array('onclick' => "JqueryPortico.openPopup({menuaction:'{$this->call_appname}.uigeneric.columns', appname:'{$this->bo->appname}',type:'{$this->type}', type_id:'{$this->type_id}'}, {closeAction:'reload'})"),
					'new_item'		 => self::link(array(
						'menuaction' => "{$this->call_appname}.uigeneric.add",
						'appname'	 => $this->bo->appname,
						'type'		 => $this->type,
						'type_id'	 => $this->type_id
					)),
					'allrows'		 => true,
					'editor_action'	 => self::link(array('menuaction' => "{$this->call_appname}.uigeneric.edit_field",
						'appname'	 => $this->appname,
						'type'		 => $this->type,
						'type_id'	 => $this->type_id)),
					'field'			 => array()
				)
			);

			$filters = $this->_get_filters();

			foreach ($filters as $filter)
			{
				array_unshift($data['form']['toolbar']['item'], $filter);
			}

			$this->bo->read(array('dry_run' => true));
			$uicols = $this->bo->uicols;

			$count_uicols_name = count($uicols['name']);

			for ($k = 0; $k < $count_uicols_name; $k++)
			{
				$params = array(
					'key'		 => $uicols['name'][$k],
					'label'		 => $uicols['descr'][$k],
					'sortable'	 => ($uicols['sortable'][$k]) ? true : false,
					'hidden'	 => ($uicols['input_type'][$k] == 'hidden') ? true : false
				);
				switch ($uicols['datatype'][$k])
				{
					case 'email':
					case 'varchar':
					case 'I':
					case 'V':
						$params['editor'] = true;
						break;
				}

				if ($uicols['name'][$k] == 'id')
				{
					$params['formatter'] = 'JqueryPortico.formatLink';
					$params['editor']	 = false;
				}
				array_push($data['datatable']['field'], $params);
			}

			$parameters = array
				(
				'parameter' => array
					(
					array
						(
						'name'	 => 'id',
						'source' => 'id'
					),
				)
			);

			if ($this->acl_edit)
			{
				$data['datatable']['actions'][] = array
					(
					'my_name'	 => 'edit',
					'statustext' => lang('edit the actor'),
					'text'		 => lang('edit'),
					'action'	 => phpgw::link('/index.php', array
						(
						'menuaction' => isset($this->location_info['edit_action']) && $this->location_info['edit_action'] ? $this->location_info['edit_action'] : "{$this->call_appname}.uigeneric.edit",
						'appname'	 => $this->appname,
						'type'		 => $this->type,
						'type_id'	 => $this->type_id
					)),
					'parameters' => json_encode($parameters)
				);

				$data['datatable']['actions'][] = array
					(
					'my_name'	 => 'edit',
					'statustext' => lang('edit the actor'),
					'text'		 => lang('open edit in new window'),
					'action'	 => phpgw::link('/index.php', array
						(
						'menuaction' => isset($this->location_info['edit_action']) && $this->location_info['edit_action'] ? $this->location_info['edit_action'] : "{$this->call_appname}.uigeneric.edit",
						'appname'	 => $this->appname,
						'type'		 => $this->type,
						'type_id'	 => $this->type_id
					)),
					'target'	 => '_blank',
					'parameters' => json_encode($parameters)
				);
			}

			if ($this->acl_delete)
			{
				$data['datatable']['actions'][] = array
					(
					'my_name'		 => 'delete',
					'statustext'	 => lang('delete the actor'),
					'text'			 => lang('delete'),
					'confirm_msg'	 => lang('do you really want to delete this entry'),
					'action'		 => phpgw::link('/index.php', array
						(
						'menuaction' => "{$this->call_appname}.uigeneric.delete",
						'appname'	 => $this->appname,
						'type'		 => $this->type,
						'type_id'	 => $this->type_id
					)),
					'parameters'	 => json_encode($parameters)
				);
			}
			unset($parameters);

			self::render_template_xsl('datatable2', $data);
		}

		/**
		 * Fetch data from $this->bo based on parametres
		 * @return array
		 */
		public function query()
		{
			$query	 = Sanitizer::get_var('query');
			$search	 = Sanitizer::get_var('search');
			$order	 = Sanitizer::get_var('order');
			$draw	 = Sanitizer::get_var('draw', 'int');
			$columns = Sanitizer::get_var('columns');
			$export	 = Sanitizer::get_var('export', 'bool');

			$params = array(
				'start'		 => Sanitizer::get_var('start', 'int', 'REQUEST', 0),
				'results'	 => Sanitizer::get_var('length', 'int', 'REQUEST', 0),
				'query'		 => $query ? $query : $search['value'],
				'order'		 => $columns[$order[0]['column']]['data'],
				'sort'		 => $order[0]['dir'],
				'dir'		 => $order[0]['dir'],
				'cat_id'	 => Sanitizer::get_var('cat_id', 'int', 'REQUEST', 0),
				'allrows'	 => Sanitizer::get_var('length', 'int') == -1 || $export,
			);

			foreach ($this->location_info['fields'] as $field)
			{
				if (isset($field['filter']) && $field['filter'])
				{
					$params['filter'][$field['name']] = Sanitizer::get_var("filter_{$field['name']}");
				}
			}

			$result_objects	 = array();
			$result_count	 = 0;

			$values = $this->bo->read($params);
			if ($export)
			{
				return $values;
			}

			$result_data = array('results' => $values);

			$result_data['total_records']	 = $this->bo->total_records;
			$result_data['draw']			 = $draw;

			$link_data = array
				(
				'menuaction' => "{$this->call_appname}.uigeneric.edit",
				'appname'	 => $this->appname,
				'type'		 => $this->type,
				'type_id'	 => $this->type_id
			);

			array_walk($result_data['results'], array($this, '_add_links'), $link_data);

			return $this->jquery_results($result_data);
		}

		public function add()
		{
			$this->edit();
		}

		function edit( $values = array() )
		{
			if (!$this->acl_add)
			{
				$this->bocommon->no_access();
				return;
			}

			$id					 = isset($values['id']) && ($values['id'] || $values['id'] === '0') ? $values['id'] : Sanitizer::get_var($this->location_info['id']['name']);
			$values_attribute	 = Sanitizer::get_var('values_attribute');

			$this->apps['manual']['section'] = 'general.edit.' . $this->type;
			Settings::getInstance()->set('apps', $this->apps);

			if ($id || $id === '0')
			{
				$values			 = $this->bo->read_single(array('id' => $id));
				$function_msg	 = $this->location_info['edit_msg'];
				$action			 = 'edit';
			}
			else
			{
				$values			 = $this->bo->read_single();
				$function_msg	 = $this->location_info['add_msg'];
				$action			 = 'add';
			}

			/* Preserve attribute values from post */
			if ($this->receipt['error'])
			{
				foreach ($this->location_info['fields'] as $field)
				{
					switch ($field['type'])
					{
						case 'integer':
						case 'int':
							$value_type	 = 'int';
							break;
						case 'html':
							$value_type	 = 'html';
							break;
						case 'date':
							$value_type	 = 'date';
							break;
						default:
							$value_type	 = 'string';
							break;
					}
					$values[$field['name']] = Sanitizer::clean_value($_POST['values'][$field['name']], $value_type);
				}

				if (isset($values_attribute) && is_array($values_attribute))
				{
					$values = $this->custom->preserve_attribute_values($values, $values_attribute);
				}
			}

			$link_save = array
				(
				'menuaction' => "{$this->call_appname}.uigeneric.save",
				'id'		 => $id,
				'appname'	 => $this->appname,
				'type'		 => $this->type,
				'type_id'	 => $this->type_id
			);

			$link_index = array
				(
				'menuaction' => "{$this->call_appname}.uigeneric.index",
				'appname'	 => $this->appname,
				'type'		 => $this->type,
				'type_id'	 => $this->type_id
			);

			$tabs			 = array();
			$tabs['generic'] = array('label' => lang('generic'), 'link' => '#generic');
			$active_tab		 = 'generic';

			if (isset($values['attributes']) && is_array($values['attributes']))
			{
				foreach ($values['attributes'] as & $attribute)
				{
					if ($attribute['history'] == true)
					{
						$link_history_data = array
							(
							'menuaction'	 => "{$this->call_appname}.uigeneric.attrib_history",
							'appname'		 => $this->appname,
							'attrib_id'		 => $attribute['id'],
							'id'			 => $id,
							'acl_location'	 => $this->acl_location,
							'edit'			 => true
						);

						$attribute['link_history'] = phpgw::link('/index.php', $link_history_data);
					}
				}

				$system_location = $this->location_info['system_location'] ? $this->location_info['system_location'] : $this->location_info['acl_location'];

				$attributes_groups = $this->custom->get_attribute_groups($this->location_info['acl_app'], $system_location, $values['attributes']);

				$attributes = array();
				foreach ($attributes_groups as $group)
				{
					if (is_array($group['attributes']))
					{
						$attributes[] = $group;
					}
				}
				unset($attributes_groups);
				unset($values['attributes']);
			}
			$jscode = '';
			$jqcal = createObject('phpgwapi.jqcal');
			foreach ($this->location_info['fields'] as & $field)
			{
				$field['value'] = isset($values[$field['name']]) ? $values[$field['name']] : '';

				if ($field['type'] == 'html')
				{
					self::rich_text_editor($field['name']);
				}
				else if ($field['type'] == 'date')
				{
					$jqcal->add_listener($field['name']);
					$dateformat		 = $this->userSettings['preferences']['common']['dateformat'];
					$field['value']	 = $this->phpgwapi_common->show_date($field['value'], $dateformat);
				}
				elseif (in_array($field['type'], array('select', 'multiple_select')))
				{
					$jscode .= <<<JS
					$("#{$field['name']}").select2({
						placeholder: "{$field['descr']}",
						width: '75%'
					});

					$('#{$field['name']}').on('select2:open', function (e) {

						$(".select2-search__field").each(function()
						{
							if ($(this).attr("aria-controls") == 'select2-{$field['name']}-results')
							{
								$(this)[0].focus();
							}
						});
					});
JS;

				}

				if (!empty($field['js_file']))
				{
					self::add_javascript($this->appname, 'base', $field['js_file']);
				}

				if (isset($field['values_def']))
				{
					if ($field['values_def']['valueset'] && is_array($field['values_def']['valueset']))
					{
						$field['valueset'] = $field['values_def']['valueset'];
						foreach ($field['valueset'] as &$_entry)
						{
							$_entry['selected'] = $_entry['id'] == $field['value'] ? 1 : 0;
						}
					}
					else if (isset($field['values_def']['method']))
					{
						$method_input = array();
						foreach ($field['values_def']['method_input'] as $_argument => $_argument_value)
						{
							if (!is_array($_argument_value) && preg_match('/^##/', $_argument_value))
							{
								$_argument_value_name	 = trim($_argument_value, '#');
								$_argument_value		 = !empty($values[$_argument_value_name]) ? explode(',', trim($values[$_argument_value_name], ',')) : '';
							}

							if ($_argument == 'filter' && is_array($_argument_value))
							{
								foreach ($_argument_value as $key => &$value)
								{
									if (preg_match('/^##/', $value))
									{
										$_argument_value_name	 = trim($value, '#');
										$value					 = $values[$_argument_value_name] ? $values[$_argument_value_name] : -1;
									}
								}
							}

							if (!is_array($_argument_value) && preg_match('/^\$this->/', $_argument_value))
							{
								$_argument_value_name	 = ltrim($_argument_value, '$this->');
								$_argument_value		 = $this->$_argument_value_name;
							}

							$method_input[$_argument] = $_argument_value;
						}

						$field['valueset'] = execMethod($field['values_def']['method'], $method_input);
					}

					if (isset($values['id']) && $values['id'] && isset($field['role']) && $field['role'] == 'parent')
					{
						// can not select it self as parent.
						$exclude	 = array($values['id']);
						$children	 = $this->bo->get_children2($values['id'], 0, true);

						foreach ($children as $child)
						{
							$exclude[] = $child['id'];
						}

						$k = count($field['valueset']);
						for ($i = 0; $i < $k; $i++)
						{
							if (in_array($field['valueset'][$i]['id'], $exclude))
							{
								unset($field['valueset'][$i]);
							}
						}
					}
				}
			}

			$msgbox_data = $this->bocommon->msgbox_data($this->receipt);

			$data = array
				(
				'msgbox_data'		 => $this->phpgwapi_common->msgbox($msgbox_data),
				'form_action'		 => phpgw::link('/index.php', $link_save),
				'cancel_url'		 => phpgw::link('/index.php', $link_index),
				'done_action'		 => phpgw::link('/index.php', array('menuaction' => "{$this->call_appname}.uigeneric.index",
					'type'		 => $this->type, 'type_id'	 => $this->type_id)),
				'lang_descr'		 => lang('Descr'),
				'lang_save'			 => lang('save'),
				'lang_cancel'		 => lang('cancel'),
				'lang_apply'		 => lang('apply'),
				'value_id'			 => isset($values['id']) ? $values['id'] : '',
				'value_descr'		 => $values['descr'],
				'attributes_group'	 => $attributes,
				'lookup_functions'	 => isset($values['lookup_functions']) ? $values['lookup_functions'] : '',
				'textareacols'		 => isset($this->userSettings['preferences']['property']['textareacols']) && $this->userSettings['preferences']['property']['textareacols'] ? $this->userSettings['preferences']['property']['textareacols'] : 60,
				'textarearows'		 => isset($this->userSettings['preferences']['property']['textarearows']) && $this->userSettings['preferences']['property']['textarearows'] ? $this->userSettings['preferences']['property']['textarearows'] : 10,
				'tabs'				 => phpgwapi_jquery::tabview_generate($tabs, $active_tab),
				'id_name'			 => $this->location_info['id']['name'],
				'id_type'			 => $this->location_info['id']['type'],
				'fields'			 => $this->location_info['fields'],
				'validator'			 => phpgwapi_jquery::formvalidator_generate(array('location', 'date',
					'security', 'file'))
			);

			$appname = $this->location_info['name'];

			$this->flags['app_header'] = $this->translation->translate($this->location_info['acl_app'], array(), false, $this->location_info['acl_app']) . "::{$appname}::{$function_msg}";
			Settings::getInstance()->update('flags', ['app_header' => $this->flags['app_header']]);
			phpgwapi_jquery::load_widget('select2');

			if($jscode)
			{
				phpgwapi_js::getInstance()->add_code('', $jscode, true);
			}

			self::render_template_xsl(array('generic', 'attributes_form'), array('edit' => $data));
		}

		function attrib_history()
		{
			$this->flags['noframework'] = true;
			Settings::getInstance()->update('flags', ['noframework' => $this->flags['noframework']]);

			$appname		 = Sanitizer::get_var('appname', 'string');
			$acl_location	 = Sanitizer::get_var('acl_location', 'string');
			$id				 = Sanitizer::get_var('id', 'int');
			$attrib_id		 = Sanitizer::get_var('attrib_id', 'int');

			$data_lookup = array
				(
				'appname'		 => $appname,
				'acl_location'	 => $acl_location,
				'id'			 => $id,
				'attrib_id'		 => $attrib_id,
			);

			$delete	 = Sanitizer::get_var('delete', 'bool');
			$edit	 = Sanitizer::get_var('edit', 'bool');

			if ($delete)
			{
				$data_lookup['history_id'] = Sanitizer::get_var('history_id', 'int');
				$this->bo->delete_history_item($data_lookup);

				return 'ok';
			}

			$link_data = array
				(
				'menuaction'		 => "{$this->call_appname}.uigeneric.attrib_history",
				'appname'			 => $appname,
				'acl_location'		 => $acl_location,
				'id'				 => $id,
				'attrib_id'			 => $attrib_id,
				'edit'				 => $edit,
				'phpgw_return_as'	 => 'json'
			);


			if (Sanitizer::get_var('phpgw_return_as') == 'json')
			{
				$values		 = $this->bo->read_attrib_history($data_lookup);
				$dateformat	 = $this->userSettings['preferences']['common']['dateformat'];

				$content = array();
				//while (is_array($values) && list(, $entry) = each($values))
				if (is_array($values))
				{
					foreach ($values as $entry)
					{
						$content[] = array
							(
							'id'			 => $entry['id'],
							'value'			 => $entry['new_value'],
							'user'			 => $entry['owner'],
							'time_created'	 => $this->phpgwapi_common->show_date($entry['datetime'], "{$dateformat} G:i:s")
						);
					}
				}

				$draw	 = Sanitizer::get_var('draw', 'int');
				$allrows = Sanitizer::get_var('length', 'int') == -1;

				$start			 = Sanitizer::get_var('start', 'int', 'REQUEST', 0);
				$total_records	 = count($content);

				$num_rows = Sanitizer::get_var('length', 'int', 'REQUEST', 0);

				if ($allrows)
				{
					$out = $content;
				}
				else
				{
					if ($total_records > $num_rows)
					{
						$page		 = ceil(( $start / $total_records ) * ($total_records / $num_rows));
						$values_part = array_chunk($content, $num_rows);
						$out		 = $values_part[$page];
					}
					else
					{
						$out = $content;
					}
				}

				$result_data = array('results' => $out);

				$result_data['total_records']	 = $total_records;
				$result_data['draw']			 = $draw;

				return $this->jquery_results($result_data);
			}

			$tabletools = array();
			if ($edit && $this->acl->check($acl_location, ACL_DELETE, $this->call_appname))
			{

				$parameters = array
					(
					'parameter' => array(
						array(
							'name'	 => 'history_id',
							'source' => 'id'
						)
					)
				);

				$tabletools[] = array(
					'my_name'		 => 'delete',
					'text'			 => lang('delete'),
					'confirm_msg'	 => lang('do you really want to delete this entry'),
					'action'		 => phpgw::link('/index.php', array	(
						'menuaction'	 => "{$this->call_appname}.uigeneric.attrib_history",
						'acl_location'	 => $acl_location,
						'id'			 => $id,
						'attrib_id'		 => $attrib_id,
						'delete'		 => true,
						'edit'			 => true,
						'type'			 => $this->type
					)),
					'parameters'	 => json_encode($parameters)
				);
			}

			$history_def = array
				(
				array('key' => 'value', 'label' => lang('value'), 'sortable' => false),
				array('key' => 'time_created', 'label' => lang('time created'), 'sortable' => false),
				array('key' => 'user', 'label' => lang('user'), 'sortable' => false),
				array('key' => 'id', 'hidden' => true)
			);

			$datatable_def	 = array();
			$datatable_def[] = array
				(
				'container'	 => 'datatable-container_0',
				'requestUrl' => json_encode(self::link($link_data)),
				'ColumnDefs' => $history_def,
				'tabletools' => $tabletools,
				'config'	 => array(
					array('disableFilter' => true)
				)
			);

			$data = array(
				'base_java_url'	 => json_encode(array('menuaction' => "{$this->call_appname}.uigeneric.attrib_history")),
				'datatable_def'	 => $datatable_def,
				'link_url'		 => phpgw::link('/index.php', $link_data),
				'img_path'		 => $this->phpgwapi_common->get_image_path('phpgwapi', 'default')
			);

			$custom			 = createObject('phpgwapi.custom_fields');
			$attrib_data	 = $custom->get($this->call_appname, $acl_location, $attrib_id);
			$appname		 = $attrib_data['input_text'];
			$function_msg	 = lang('history');

			$this->flags['app_header'] = lang($this->call_appname) . ' - ' . $appname . ': ' . $function_msg;
			Settings::getInstance()->update('flags', ['app_header' => $this->flags['app_header']]);

			self::render_template_xsl(array('attrib_history', 'datatable_inline'), array(
				'attrib_history' => $data));
		}

		function delete()
		{
			if (!$this->acl_delete)
			{
				return lang('no access');
			}

			$id = Sanitizer::get_var($this->location_info['id']['name']);

			if (Sanitizer::get_var('phpgw_return_as') == 'json')
			{
				$this->bo->delete($id);
				return lang('id %1 has been deleted', $id);
			}
		}

		/**
		 * Saves an entry to the database for new/edit - redirects to view
		 *
		 * @param int  $id  entity id - no id means 'new'
		 *
		 * @return void
		 */
		public function save()
		{
			if (!$_POST)
			{
				return $this->edit();
			}

			$id = $_POST[$this->location_info['id']['name']];

			if ($id !== '0')
			{
				$id = Sanitizer::clean_value($id);
			}

			$values = Sanitizer::get_var('values');

			if ($id || $id === '0')
			{
				$data	 = $this->bo->read_single(array('id' => $id, 'view' => true));
				$action	 = 'edit';
			}
			else
			{
				$data	 = $this->bo->read_single(array('view' => true));
				$action	 = 'add';
			}

			/*
			 * Overrides with incoming data from POST
			 */
			$data = $this->_populate($data);

			if ($this->receipt['error'])
			{
				$this->edit();
			}
			else
			{
				try
				{
					$fields		 = $data;
					$attributes	 = $data['attributes'];
					unset($fields['attributes']);

					$receipt		 = $this->bo->save($fields, $action, $attributes);
					$id				 = $receipt['id'];
					$this->receipt	 = $receipt;
				}
				catch (Exception $e)
				{
					if ($e)
					{
						Cache::message_set($e->getMessage(), 'error');
						$this->edit();
						return;
					}
				}

				if ($values['apply'])
				{
					if ($id)
					{
						self::message_set($this->receipt);
						self::redirect(array('menuaction' => "{$this->call_appname}.uigeneric.edit",
							'id'		 => $id,
							'appname'	 => $this->appname,
							'type'		 => $this->type,
							'type_id'	 => $this->type_id
							)
						);
					}
					$this->edit();
					return;
				}
				self::redirect(array('menuaction' => "{$this->call_appname}.uigeneric.index",
					'appname'	 => $this->appname,
					'type'		 => $this->type,
					'type_id'	 => $this->type_id));
			}
		}

		public function edit_field()
		{
			$id			 = Sanitizer::get_var('id', 'int', 'POST');
			$field_name	 = Sanitizer::get_var('field_name', 'string');

			if (!$this->acl_edit)
			{
				return lang('no access');
			}

			if ($id && $field_name)
			{

				$data = array(
					'id'		 => $id,
					'field_name' => $field_name,
					'value'		 => Sanitizer::get_var('value')
				);

				try
				{
					$this->bo->edit_field($data);
				}
				catch (Exception $e)
				{
					if ($e)
					{
						echo $e->getMessage();
					}
				}
				echo true;
			}
			else
			{
				echo "ERROR";
			}
		}
	}