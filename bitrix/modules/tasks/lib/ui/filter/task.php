<?php

namespace Bitrix\Tasks\Ui\Filter;

use Bitrix\Main\Grid;
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Filter;
use Bitrix\Tasks\Util;


class Task
{
	protected static $filterId = null;
	protected static $filterSuffix = '';
	protected static $groupId = 0;
	protected static $userId = null;
	protected static $gridOptions = null;
	protected static $filterOptions = null;

	/**
	 * Get available fields in filter.
	 * @return array
	 */
	protected function getAvailableFields()
	{
		$fields = array(
			'ID',
			'TITLE',
//			'REAL_STATUS',
			'STATUS',
			'PROBLEM',
			'PARAMS',
			'PRIORITY',
			'MARK',
			'ALLOW_TIME_TRACKING',
			'DEADLINE',
			'CREATED_DATE',
			'CLOSED_DATE',
			'DATE_START',
			'START_DATE_PLAN',
			'END_DATE_PLAN',
			'RESPONSIBLE_ID',
			'CREATED_BY',
			'ACCOMPLICE',
			'AUDITOR',
			'TAG',
			'ACTIVE'
		);

		if(static::getGroupId() == 0)
			$fields[]='GROUP_ID';

		return $fields;
	}

	/**
	 * @return Grid\Options
	 */
	public static function getGridOptions()
	{
		if (is_null(static::$gridOptions) || !(static::$gridOptions instanceof Grid\Options))
		{
			static::$gridOptions = new Grid\Options(static::getFilterId());
		}

		return static::$gridOptions;
	}

	public static function getFilterId()
	{
		if(!static::$filterId)
		{
			$stateInstance = static::getListStateInstance();
			$roleId = $stateInstance->getUserRole();
			$section = $stateInstance->getSection();
			$typeFilter = \CTaskListState::VIEW_SECTION_ADVANCED_FILTER == $section ? 'ADVANCED' : 'MAIN';

			$state = $stateInstance->getState();
			$presetSelected = array_key_exists('PRESET_SELECTED', $state) && $state['PRESET_SELECTED']['ID'] == -10  ? 'Y' : 'N';

			static::$filterId = 'TASKS_GRID_ROLE_ID_' . $roleId . '_' . (int)(static::getGroupId() > 0).'_'.$typeFilter.'_'.$presetSelected.static::$filterSuffix;
		}

		return static::$filterId;
	}

	public static function setFilterId($filterId)
	{
		static::$filterId = $filterId;
	}

	public static function getPresets()
	{
		$presets = array(
			'filter_tasks_in_progress' => array(
				'name' => Loc::getMessage('TASKS_PRESET_IN_PROGRESS'),
				'default' => true,
				'fields' => array(
					'STATUS' => array(
						\CTasks::STATE_PENDING,
						\CTasks::STATE_IN_PROGRESS
					)
				)
			),
			'filter_tasks_completed' => array(
				'name' => Loc::getMessage('TASKS_PRESET_COMPLETED'),
				'default' => false,
				'fields' => array(
					'STATUS' => array(
						\CTasks::STATE_COMPLETED
					)
				)
			),
			'filter_tasks_deferred' => array(
				'name' => Loc::getMessage('TASKS_PRESET_DEFERRED'),
				'default' => false,
				'fields' => array(
					'PROBLEM' => \CTaskListState::VIEW_TASK_CATEGORY_DEFERRED
				)
			),
			'filter_tasks_expire' => array(
				'name' => Loc::getMessage('TASKS_PRESET_EXPIRED'),
				'default' => false,
				'fields' => array(
					'PROBLEM' => \CTaskListState::VIEW_TASK_CATEGORY_EXPIRED
				)
			),
			'filter_tasks_expire_candidate' => array(
				'name' => Loc::getMessage('TASKS_PRESET_EXPIRED_CAND'),
				'default' => false,
				'fields' => array(
					'PROBLEM' => \CTaskListState::VIEW_TASK_CATEGORY_EXPIRED_CANDIDATES
				)
			)
		);

		return $presets;
	}

	public static function processFilter($processGridFilter = true)
	{
		$stateFilter = static::processStateFilter();
		$specialFilter = static::processSpecialPresetsFilter();
		$gridFilter = $processGridFilter ? static::processGridFilter($stateFilter) : array();

		return array_merge($stateFilter, $specialFilter, $gridFilter);
	}

	protected static function processSpecialPresetsFilter()
	{
		$arrFilter = array();
		$request = Context::getCurrent()->getRequest()->toArray();
		if(array_key_exists('F_FILTER_SWITCH_PRESET', $request)
			&& static::getFilterCtrlInstance()->checkExistsPresetById($request['F_FILTER_SWITCH_PRESET']))
		{
			$arrFilter = static::getFilterCtrlInstance()->getFilterPresetConditionById($request['F_FILTER_SWITCH_PRESET']);
		}

		return $arrFilter;
	}

	protected static function processGridFilter(&$stateFilter = array())
	{
		$filters = static::getFilters();
		$filterData = static::getFilterData();
//		static::getListStateInstance()->setTaskCategory(\CTaskListState::VIEW_TASK_CATEGORY_ALL);

		if (!array_key_exists('FILTER_APPLIED', $filterData) || $filterData[ 'FILTER_APPLIED' ] != true)
		{
			return array();
		}

		$arrFilter = array();

		if (array_key_exists('FIND', $filterData) && !empty($filterData[ 'FIND' ]))
		{
			$arrFilter[ '*%SEARCH_INDEX' ] = trim($filterData[ 'FIND' ]);
		}

		foreach ($filters as $filterRow)
		{
			switch ($filterRow[ 'type' ])
			{
				default:
					if (array_key_exists($filterRow[ 'id' ], $filterData) && !empty($filterData[ $filterRow[ 'id' ] ]))
					{
						if (is_numeric($filterData[ $filterRow[ 'id' ] ]) && !($filterRow[ 'id' ] == 'TITLE' && !empty($filterData[ $filterRow[ 'id' ] ])))
						{
							$arrFilter[ $filterRow[ 'id' ] ] = $filterData[ $filterRow[ 'id' ] ];
						}
						else
						{
							$arrFilter[ '%' . $filterRow[ 'id' ] ] = $filterData[ $filterRow[ 'id' ] ];
						}
					}
					break;
				case 'date':
					if($filterRow[ 'id' ] == 'ACTIVE')
					{
						$arrFilter['ACTIVE']['START'] = $filterData[ $filterRow[ 'id' ] . '_from' ];
						$arrFilter['ACTIVE']['END'] = $filterData[ $filterRow[ 'id' ] . '_to' ];

						continue;
					}

					if (array_key_exists($filterRow[ 'id' ] . '_from', $filterData) && !empty($filterData[ $filterRow[ 'id' ] . '_from' ]))
					{
						$arrFilter[ '>=' . $filterRow[ 'id' ] ] = $filterData[ $filterRow[ 'id' ] . '_from' ];
					}
					if (array_key_exists($filterRow[ 'id' ] . '_to', $filterData) && !empty($filterData[ $filterRow[ 'id' ] . '_to' ]))
					{
						$arrFilter[ '<=' . $filterRow[ 'id' ] ] = $filterData[ $filterRow[ 'id' ] . '_to' ];
					}
					break;
				case 'number':
					if (array_key_exists($filterRow[ 'id' ] . '_from', $filterData) && !empty($filterData[ $filterRow[ 'id' ] . '_from' ]))
					{
						$arrFilter[ '>=' . $filterRow[ 'id' ] ] = $filterData[ $filterRow[ 'id' ] . '_from' ];
					}
					if (array_key_exists($filterRow[ 'id' ] . '_to', $filterData) && !empty($filterData[ $filterRow[ 'id' ] . '_to' ]))
					{
						$arrFilter[ '<=' . $filterRow[ 'id' ] ] = $filterData[ $filterRow[ 'id' ] . '_to' ];
					}

					if (
						array_key_exists('>=' . $filterRow[ 'id' ], $arrFilter)
						&& array_key_exists('<=' . $filterRow[ 'id' ], $arrFilter)
						&& $arrFilter[ '>=' . $filterRow[ 'id' ] ] == $arrFilter[ '<=' . $filterRow[ 'id' ] ]
					)
					{
						$arrFilter[ $filterRow[ 'id' ] ] = $arrFilter[ '>=' . $filterRow[ 'id' ] ];
						unset($arrFilter[ '>=' . $filterRow[ 'id' ] ], $arrFilter[ '<=' . $filterRow[ 'id' ] ]);
					}
					break;
				case 'list':
					if ($filterRow[ 'id' ] == 'PARAMS' && !empty($filterData[ $filterRow[ 'id' ] ]))
					{
						$subfilter = array();
						foreach($filterData[ $filterRow[ 'id' ] ] as $param)
						{

							switch ($param)
							{
								case 'MARKED':
									$subfilter["!MARK"] = false;
									break;
								case 'OVERDUED':
									$subfilter["OVERDUED"] = "Y";
									break;
								case 'IN_REPORT':
									$subfilter["ADD_IN_REPORT"] = "Y";
									break;
								case 'SUBORDINATE':
									// Don't set SUBORDINATE_TASKS for admin, it will cause all tasks to be showed
									if (!\Bitrix\Tasks\Util\User::isSuper())
									{
										$subfilter["SUBORDINATE_TASKS"] = "Y";
									}
									break;
								case 'ANY_TASK':
									unset($stateFilter['::SUBFILTER-ROOT']['MEMBER']);
									break;
							}
						}

						$arrFilter['::SUBFILTER-PARAMS']=$subfilter;
						$arrFilter['::SUBFILTER-PARAMS']['::LOGIC']='OR';
					}
					else if ($filterRow[ 'id' ] == 'PROBLEM' && !empty($filterData[ $filterRow[ 'id' ] ]))
					{
						switch($filterData[ $filterRow[ 'id' ] ])
						{
							case \CTaskListState::VIEW_TASK_CATEGORY_WAIT_CTRL:
								$arrFilter['::SUBFILTER-PROBLEM'][ 'REAL_STATUS' ][] = \CTasks::STATE_SUPPOSEDLY_COMPLETED;
								break;
							case \CTaskListState::VIEW_TASK_CATEGORY_DEFERRED:
								$arrFilter['::SUBFILTER-PROBLEM'][ 'REAL_STATUS' ][] = \CTasks::STATE_DEFERRED;
								break;
							case \CTaskListState::VIEW_TASK_CATEGORY_EXPIRED:
								$arrFilter['::SUBFILTER-PROBLEM'][ 'STATUS' ][] = \CTasks::METASTATE_EXPIRED;
								break;
							case \CTaskListState::VIEW_TASK_CATEGORY_EXPIRED_CANDIDATES:
								$arrFilter['::SUBFILTER-PROBLEM'][ 'STATUS' ][] = \CTasks::METASTATE_EXPIRED_SOON;
								break;
							case \CTaskListState::VIEW_TASK_CATEGORY_WO_DEADLINE:
								$arrFilter[ 'DEADLINE' ] = '';
								$arrFilter[ 'STATUS' ][] = \CTasks::METASTATE_VIRGIN_NEW;
								$arrFilter[ 'STATUS' ][] = \CTasks::STATE_NEW;
								$arrFilter[ 'STATUS' ][] = \CTasks::STATE_PENDING;
								$arrFilter[ 'STATUS' ][] = \CTasks::STATE_IN_PROGRESS;
//								$arrFilter[ 'STATUS' ][] = \CTasks::STATE_DEFERRED;
								break;
							case \CTaskListState::VIEW_TASK_CATEGORY_NEW:
								$arrFilter['VIEWED'] = 0;
								$arrFilter['VIEWED_BY'] = self::getUserId();

								break;
							default:

								break;
						}
					}
					elseif ($filterRow[ 'id' ] == 'STATUS' && !empty($filterData[ $filterRow[ 'id' ] ]))
					{
						$arrFilter[ 'REAL_STATUS' ] = $filterData[ $filterRow[ 'id' ] ];
					}
					elseif (array_key_exists($filterRow[ 'id' ], $filterData) && !empty($filterData[ $filterRow[ 'id' ] ]))
					{
						$arrFilter[ $filterRow[ 'id' ] ] = $filterData[ $filterRow[ 'id' ] ];
					}
					break;
			}
		}

		static::getListStateInstance()->saveState();
		return $arrFilter;
	}

	public static function getFilters()
	{
		static $filters = null;

		if (is_null($filters))
		{
			$filters = static::getFilterRaw();
			$defaultFilters = self::getDefaultFilterFields();

			foreach ($defaultFilters as $fieldId)
			{
				if (array_key_exists($fieldId, $filters))
				{
					if(!array_key_exists('default', $filters[$fieldId]))
					{
						$filters[$fieldId]['default'] = true;
					}
				}
			}
		}

		return $filters;
	}

	private static function getDefaultFilterFields()
	{
		$roleId = \CTaskListState::getInstance(Util\User::getId())->getUserRole();

		switch ($roleId)
		{
			case \CTaskListState::VIEW_ROLE_ACCOMPLICE:
			case \CTaskListState::VIEW_ROLE_RESPONSIBLE:
				$defaultFields = array(
					'CREATED_BY',
					'DEADLINE',
					'STATUS',
					'GROUP_ID',
					'PROBLEM',
				);
				break;
			case \CTaskListState::VIEW_ROLE_ORIGINATOR:
				$defaultFields = array(
					'RESPONSIBLE_ID',
					'STATUS',
					'DEADLINE',
					'GROUP_ID',
					'PROBLEM',
				);
				break;
			default:
				$defaultFields = array(
					'CREATED_BY',
					'RESPONSIBLE_ID',
					'STATUS',
					'DEADLINE',
					'PROBLEM',
					'GROUP_ID'
				);
				break;
		}

		return $defaultFields;
	}

	public static function getVisibleColumns()
	{
		$columns = static::getGridOptions()->GetVisibleColumns();

		if (empty($columns))
		{
			$columns = self::getDefaultVisibleColumns();
		}

		return $columns;
	}

	private function getDefaultVisibleColumns()
	{
		$stateInstance = static::getListStateInstance();
		$roleId = $stateInstance->getUserRole();
		$section = $stateInstance->getSection();
		$typeFilter = \CTaskListState::VIEW_SECTION_ADVANCED_FILTER == $section ? 'ADVANCED' : 'MAIN';

		if($typeFilter == 'ADVANCED')
		{
			$roleId = 'default';
		}

		switch ($roleId)
		{
			case \CTaskListState::VIEW_ROLE_ACCOMPLICE:
			case \CTaskListState::VIEW_ROLE_RESPONSIBLE:
				$defaultColumns = array(
					'TITLE',
					'DEADLINE',
					'CREATED_BY',
					'ORIGINATOR_NAME',
				);
				break;
			case \CTaskListState::VIEW_ROLE_ORIGINATOR:
				$defaultColumns = array(
					'TITLE',
					'DEADLINE',
					'RESPONSIBLE_ID',
					'RESPONSIBLE_NAME'
				);
				break;
			case \CTaskListState::VIEW_ROLE_AUDITOR:
				$defaultColumns = array(
					'TITLE',
					'DEADLINE',
					'CREATED_BY',
					'ORIGINATOR_NAME',
					'RESPONSIBLE_ID',
					'RESPONSIBLE_NAME'
				);
				break;
			default:
				$defaultColumns = array(
					'TITLE',
					'DEADLINE',
					'CREATED_BY',
					'ORIGINATOR_NAME',
					'RESPONSIBLE_ID',
					'RESPONSIBLE_NAME'
				);
				break;
		}

		return $defaultColumns;
	}

	protected static function getFilterRaw()
	{
		$fields = static::getAvailableFields();
		$filter = array();

		if (in_array('CREATED_BY', $fields))
		{
			$filter['CREATED_BY'] = array(
				'id' => 'CREATED_BY',
				'name' => Loc::getMessage('TASKS_HELPER_FLT_CREATED_BY'),
				'params' => array('multiple' => 'Y'),
				'type' => 'custom_entity',
				'selector' => array(
					'TYPE' => 'user',
					'DATA' => array(
						'ID' => 'user',
						'FIELD_ID' => 'CREATED_BY'
					)
				)
			);
		}

		if (in_array('RESPONSIBLE_ID', $fields))
		{
			$filter['RESPONSIBLE_ID'] = array(
				'id' => 'RESPONSIBLE_ID',
				'name' => Loc::getMessage('TASKS_HELPER_FLT_RESPONSIBLE_ID'),
				'params' => array('multiple' => 'Y'),
				'type' => 'custom_entity',
				'selector' => array(
					'TYPE' => 'user',
					'DATA' => array(
						'ID' => 'user',
						'FIELD_ID' => 'RESPONSIBLE_ID'
					)
				)
			);
		}

		if (in_array('STATUS', $fields))
		{
			$filter['STATUS'] = array(
				'id' => 'STATUS',
				'name' => Loc::getMessage('TASKS_FILTER_STATUS'),
				'type' => 'list',
				'params' => array(
					'multiple' => 'Y'
				),
				'items' => array(
					//					\CTasks::METASTATE_VIRGIN_NEW => Loc::getMessage('TASKS_STATUS_1'),
					\CTasks::STATE_PENDING => Loc::getMessage('TASKS_STATUS_2'),
					\CTasks::STATE_IN_PROGRESS => Loc::getMessage('TASKS_STATUS_3'),
					\CTasks::STATE_SUPPOSEDLY_COMPLETED => Loc::getMessage('TASKS_STATUS_4'),
					\CTasks::STATE_COMPLETED => Loc::getMessage('TASKS_STATUS_5'),
					//					\CTasks::STATE_DEFERRED => Loc::getMessage('TASKS_STATUS_6'),
					//\CTasks::STATE_DECLINED => Loc::getMessage('TASKS_STATUS_7')
				)
			);
		}

		if (in_array('DEADLINE', $fields))
		{
			$filter['DEADLINE'] = array(
				'id' => 'DEADLINE',
				'name' => Loc::getMessage('TASKS_FILTER_DEADLINE'),
				'type' => 'date'
			);
		}

		if (in_array('GROUP_ID', $fields))
		{
			$filter['GROUP_ID'] = array(
				'id' => 'GROUP_ID',
				'name' => Loc::getMessage('TASKS_HELPER_FLT_GROUP'),
				'params' => array('multiple' => 'Y'),
				'type' => 'custom_entity',
				'selector' => array(
					'TYPE' => 'group',
					'DATA' => array(
						'ID' => 'group',
						'FIELD_ID' => 'GROUP_ID'
					)
				)
			);
		}

		if (in_array('PROBLEM', $fields))
		{
			$filter['PROBLEM'] = array(
				'id' => 'PROBLEM',
				'name' => Loc::getMessage('TASKS_FILTER_PROBLEM'),
				'type' => 'list',
				'items' => self::getAllowedTaskCategories()
			);
		}

		if (in_array('PARAMS', $fields))
		{
			$filter['PARAMS'] = array(
				'id' => 'PARAMS',
				'name' => Loc::getMessage('TASKS_FILTER_PARAMS'),
				'type' => 'list',
				'params' => array(
					'multiple' => 'Y'
				),
				'items' => array(
					'MARKED'=>Loc::getMessage('TASKS_FILTER_PARAMS_MARKED'),
					'IN_REPORT'=>Loc::getMessage('TASKS_FILTER_PARAMS_IN_REPORT'),
					'OVERDUED'=>Loc::getMessage('TASKS_FILTER_PARAMS_OVERDUED'),
//					'SUBORDINATE'=>Loc::getMessage('TASKS_FILTER_PARAMS_SUBORDINATE'),
					'ANY_TASK'=>Loc::getMessage('TASKS_FILTER_PARAMS_ANY_TASK')
				)
			);
		}

		if (in_array('ID', $fields))
		{
			$filter['ID'] = array(
				'id' => 'ID',
				'name' => Loc::getMessage('TASKS_FILTER_ID'),
				'type' => 'number'
			);
		}
		if (in_array('TITLE', $fields))
		{
			$filter['TITLE'] = array(
				'id' => 'TITLE',
				'name' => Loc::getMessage('TASKS_FILTER_TITLE'),
				'type' => 'string'
			);
		}
		if (in_array('PRIORITY', $fields))
		{
			$filter['PRIORITY'] = array(
				'id' => 'PRIORITY',
				'name' => Loc::getMessage('TASKS_PRIORITY'),
				'type' => 'list',
				'items' => array(
					1 => Loc::getMessage('TASKS_PRIORITY_1'),
					2 => Loc::getMessage('TASKS_PRIORITY_2'),
				)
			);
		}
		if (in_array('MARK', $fields))
		{
			$filter['MARK'] = array(
				'id' => 'MARK',
				'name' => Loc::getMessage('TASKS_FILTER_MARK'),
				'type' => 'list',
				'items' => array(
					'P' => Loc::getMessage('TASKS_MARK_P'),
					'N' => Loc::getMessage('TASKS_MARK_N')
				)
			);
		}
		if (in_array('ALLOW_TIME_TRACKING', $fields))
		{
			$filter['ALLOW_TIME_TRACKING'] = array(
				'id' => 'ALLOW_TIME_TRACKING',
				'name' => Loc::getMessage('TASKS_FILTER_ALLOW_TIME_TRACKING'),
				'type' => 'list',
				'items' => array(
					'Y' => Loc::getMessage('TASKS_ALLOW_TIME_TRACKING_Y'),
					'N' => Loc::getMessage('TASKS_ALLOW_TIME_TRACKING_N'),
				)
			);
		}
		if (in_array('CREATED_DATE', $fields))
		{
			$filter['CREATED_DATE'] = array(
				'id' => 'CREATED_DATE',
				'name' => Loc::getMessage('TASKS_FILTER_CREATED_DATE'),
				'type' => 'date'
			);
		}
		if (in_array('CLOSED_DATE', $fields))
		{
			$filter['CLOSED_DATE'] = array(
				'id' => 'CLOSED_DATE',
				'name' => Loc::getMessage('TASKS_FILTER_CLOSED_DATE'),
				'type' => 'date'
			);
		}
		if (in_array('DATE_START', $fields))
		{
			$filter['DATE_START'] = array(
				'id' => 'DATE_START',
				'name' => Loc::getMessage('TASKS_FILTER_DATE_START'),
				'type' => 'date'
			);
		}
		if (in_array('START_DATE_PLAN', $fields))
		{
			$filter['START_DATE_PLAN'] = array(
				'id' => 'START_DATE_PLAN',
				'name' => Loc::getMessage('TASKS_FILTER_START_DATE_PLAN'),
				'type' => 'date'
			);
		}
		if (in_array('END_DATE_PLAN', $fields))
		{
			$filter['END_DATE_PLAN'] = array(
				'id' => 'END_DATE_PLAN',
				'name' => Loc::getMessage('TASKS_FILTER_END_DATE_PLAN'),
				'type' => 'date'
			);
		}

		if (in_array('ACTIVE', $fields))
		{
			$filter['ACTIVE'] = array(
				'id' => 'ACTIVE',
				'name' => Loc::getMessage('TASKS_FILTER_ACTIVE'),
				'type' => 'date'
			);
		}

		if (in_array('ACCOMPLICE', $fields))
		{
			$filter['ACCOMPLICE'] = array(
				'id' => 'ACCOMPLICE',
				'name' => Loc::getMessage('TASKS_HELPER_FLT_ACCOMPLICES'),
				'params' => array('multiple' => 'Y'),
				'type' => 'custom_entity',
				'selector' => array(
					'TYPE' => 'user',
					'DATA' => array(
						'ID' => 'user',
						'FIELD_ID' => 'ACCOMPLICE'
					)
				)
			);
		}
		if (in_array('AUDITOR', $fields))
		{
			$filter['AUDITOR'] = array(
				'id' => 'AUDITOR',
				'name' => Loc::getMessage('TASKS_HELPER_FLT_AUDITOR'),
				'params' => array('multiple' => 'Y'),
				'type' => 'custom_entity',
				'selector' => array(
					'TYPE' => 'user',
					'DATA' => array(
						'ID' => 'user',
						'FIELD_ID' => 'AUDITOR'
					)
				)
			);
		}
		if (in_array('TAG', $fields))
		{
			$filter['TAG'] = array(
				'id' => 'TAG',
				'name' => Loc::getMessage('TASKS_FILTER_TAG'),
				'type' => 'string'
			);
		}

		return $filter;
	}

	protected static function getAllowedTaskCategories()
	{
		$list = array();

		$taskCategories = array(
			\CTaskListState::VIEW_TASK_CATEGORY_DEFERRED,
			\CTaskListState::VIEW_TASK_CATEGORY_WO_DEADLINE,
			\CTaskListState::VIEW_TASK_CATEGORY_NEW,
			\CTaskListState::VIEW_TASK_CATEGORY_EXPIRED_CANDIDATES,
			\CTaskListState::VIEW_TASK_CATEGORY_EXPIRED,
			\CTaskListState::VIEW_TASK_CATEGORY_WAIT_CTRL,
		);

		foreach ($taskCategories as $categoryId)
		{
			if(static::getListStateInstance()->isCategoryExists((int)$categoryId))
			{
				$list[$categoryId] = \CTaskListState::getTaskCategoryName($categoryId);
			}
		}

		return $list;
	}

	public static function getListStateInstance()
	{
		static $instance = null;

		if (is_null($instance))
		{
			$instance = \CTaskListState::getInstance(static::getUserId());
		}
		return $instance;
	}

	public static function getUserId()
	{
		if (!static::$userId)
		{
			static::$userId = Util\User::getId();
		}

		return static::$userId;
	}

	public static function setUserId($userId)
	{
		static::$userId = $userId;
	}

	protected static function getFilterData()
	{
		$filters = static::getFilters();
		$filterOptions = static::getFilterOptions();

		return $filterOptions->getFilter($filters);
	}

	public static function getFilterOptions()
	{
		if (is_null(static::$filterOptions) || !(static::$filterOptions instanceof Filter\Options))
		{
			static::$filterOptions = new Filter\Options(static::getFilterId(), static::getPresets());
		}

		return static::$filterOptions;
	}

	protected static function processStateFilter()
	{
		$listStateInstance = static::getListStateInstance();
		$listCtrlInstance = static::getListCtrlInstance();
		$filterCtrlInstance = static::getFilterCtrlInstance();

		//static::makeAdvancedFilter();

		if ($listStateInstance->getSection() == \CTaskListState::VIEW_SECTION_ADVANCED_FILTER)
		{
			$listCtrlInstance->useAdvancedFilterObject($filterCtrlInstance);

			//_print_r('Preset: '.$filterInstance->getSelectedFilterPresetId());
		}

		// getCommonFilter contains filtering by GROUP_ID and submode-filter conditions (ONLY_ROOT_TASKS and SAME_GROUP_PARENT)
		return array_merge($listCtrlInstance->getFilter(), $listCtrlInstance->getCommonFilter());
	}

	public static function getListCtrlInstance()
	{
		static $instance = null;

		if (is_null($instance))
		{
			$instance = \CTaskListCtrl::getInstance(static::getUserId());
		}

		return $instance;
	}

	private static function getFilterCtrlInstance()
	{
		static $instance = null;

		if (is_null($instance))
		{
			$instance = \CTaskFilterCtrl::getInstance(static::getUserId(), (static::getGroupId() > 0));
		}

		return $instance;
	}

	public static function getGroupId()
	{
		return self::$groupId;
	}

	public static function setGroupId($groupId)
	{
		self::$groupId = $groupId;
	}

	public static function listStateInit()
	{
//		Convert\Filter::process();

		$listStateInstance = self::getListStateInstance();
		$listCtrlInstance = self::getFilterCtrlInstance();

//		$listCtrlInstance->SwitchFilterPreset(\CTaskFilterCtrl::STD_PRESET_ACTIVE_MY_TASKS);
		$listCtrlInstance->SwitchFilterPreset(\CTaskFilterCtrl::STD_PRESET_ALL_MY_TASKS);

		$request = \Bitrix\Main\Context::getCurrent()->getRequest()->toArray();

		if (!empty($request['F_SECTION']))
		{
			if ($request['F_SECTION'] == 'ROLES')
			{
				$listStateInstance->setSection(\CTaskListState::VIEW_SECTION_ROLES);
			}
			elseif ($request['F_SECTION'] == 'ADVANCED')
			{
				$listStateInstance->setSection(\CTaskListState::VIEW_SECTION_ADVANCED_FILTER);
			}
		}
		elseif (array_key_exists('F_ADVANCED', $request) && $request['F_ADVANCED'] === 'Y')
		{
			$listStateInstance->setSection(\CTaskListState::VIEW_SECTION_ADVANCED_FILTER);
		}
		elseif (array_key_exists('F_FILTER_SWITCH_PRESET', $request))
		{
			$listStateInstance->setSection(\CTaskListState::VIEW_SECTION_ADVANCED_FILTER);
			$listCtrlInstance->SwitchFilterPreset((int)$request['F_FILTER_SWITCH_PRESET']);
		}

		$stateParam = $request[ 'F_STATE' ];

		if(!is_array($stateParam))
		{
			$stateParam = (array)$stateParam;
		}

		if(!empty($stateParam))
		{
			foreach($stateParam as $state)
			{
				$symbol = substr($state, 0, 2);
				$value = \CTaskListState::decodeState(substr($state, 2));

				switch ($symbol)
				{
					case 'sR':    // set role
						$listStateInstance->setSection(\CTaskListState::VIEW_SECTION_ROLES);
					
						try
						{
							$listStateInstance->setUserRole($value);
						}
						catch(\TasksException $e)
						{
							$listStateInstance->setUserRole(\CTaskListState::VIEW_ROLE_RESPONSIBLE);
						}

						$listStateInstance->setTaskCategory(\CTaskListState::VIEW_TASK_CATEGORY_ALL);
						break;

					case 'sV':    // set view
						$availableModes = $listStateInstance->getAllowedViewModes();
						if (in_array($value, $availableModes))
						{
							$listStateInstance->setViewMode($value);
						}
						else
						{
							$listStateInstance->setViewMode(\CTaskListState::VIEW_MODE_LIST);
						}
						break;

					case 'sC':    // set category
						$listStateInstance->setTaskCategory($value);
						break;

					case 'eS':    // enable submode
						$listStateInstance->switchOnSubmode($value);
						break;

					case 'dS':    // disable submode
						$listStateInstance->switchOffSubmode($value);
						break;
				}
			}
		}

		$listStateInstance->saveState(); // to db

		return $listStateInstance;
	}
}