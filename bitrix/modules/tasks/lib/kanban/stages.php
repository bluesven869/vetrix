<?php
namespace Bitrix\Tasks\Kanban;

use \Bitrix\Tasks\Internals\TaskTable as Task;
use \Bitrix\Tasks\Internals\Task\SortingTable;
use \Bitrix\Tasks\Internals\Task\FavoriteTable;
use \Bitrix\Tasks\Internals\Task\ViewedTable;
use \Bitrix\Tasks\ProjectsTable;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

class StagesTable extends Entity\DataManager
{
	const MY_PLAN_VERSION = '6';

	/**
	 * System type of stages (new, in progress, etc.).
	 */
	const SYS_TYPE_NEW = 'NEW';
	const SYS_TYPE_DEFAULT = 'NEW';

	/**
	 * Default colors.
	 */
	const DEF_COLOR_STAGE = '47D1E2';

	/**
	 * Disable pin for this users.
	 * @var array
	 */
	private static $disablePin = array();

	/**
	 * Disable linked for this users.
	 * @var array
	 */
	private static $disableLink = array();

	/**
	 * Work mode.
	 * @var string
	 */
	private static $mode = 'G';

	/**
	 * Allowed work modes.
	 */
	const WORK_MODE_GROUP = 'G';
	const WORK_MODE_USER = 'U';

	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_tasks_stages';
	}

	/**
	 * Returns entity map definition.
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true
			)),
			'TITLE' => new Entity\StringField('TITLE', array(
				//
			)),
			'SORT' => new Entity\IntegerField('SORT', array(
				//
			)),
			'COLOR' => new Entity\StringField('COLOR', array(
				//
			)),
			'SYSTEM_TYPE' => new Entity\StringField('SYSTEM_TYPE', array(
				//
			)),
			'ENTITY_ID' => new Entity\IntegerField('ENTITY_ID', array(
				//
			)),
			'ENTITY_TYPE' => new Entity\StringField('ENTITY_TYPE', array(
				//
			))
		);
	}

	/**
	 * Check work mode.
	 * @param string $mode Mode.
	 * @return boolean
	 */
	public static function checkWorkMode($mode)
	{
		return  $mode == self::WORK_MODE_GROUP ||
				$mode == self::WORK_MODE_USER;
	}

	/**
	 * Set work mode.
	 * @param string $mode New mode.
	 * @return void
	 */
	public static function setWorkMode($mode)
	{
		if (self::checkWorkMode($mode))
		{
			self::$mode = $mode;
		}
	}

	/**
	 * Get work mode.
	 * @return string
	 */
	public static function getWorkMode()
	{
		return self::$mode;
	}

	/**
	 * Base delete-method, first check that column is not system.
	 * @param mixed $key Row key.
	 * @param int $entityId Id of entity.
	 * @return DeleteResult|false
	 */
	public static function delete($key, $entityId = 0)
	{
		$entityType = self::getWorkMode();

		$res = self::getList(array(
			'filter' => array(
				'ID' => $key,
				'ENTITY_ID' => $entityId,
				'=ENTITY_TYPE' => $entityType,
				'=SYSTEM_TYPE' => false
			)
		));
		if ($stage = $res->fetch())
		{
			$res = parent::delete($stage['ID']);
			// remove tasks from this stage
			if ($res->isSuccess())
			{
				if ($entityType == self::WORK_MODE_GROUP)
				{
					$resT = Task::getList(array(
						'select' => array('ID'),
						'filter' => array(
							'STAGE_ID' => $stage['ID']
						)
					));
					while ($row = $resT->fetch())
					{
						Task::update($row['ID'], array(
							'STAGE_ID' => 0
						));
					}
				}
				elseif ($entityType == self::WORK_MODE_USER)
				{
					$resT = TaskStageTable::getList(array(
						'filter' => array(
							'STAGE_ID' => $stage['ID']
						)
					));
					while ($row = $resT->fetch())
					{
						TaskStageTable::delete($row['ID']);
					}
				}
			}
			return $res;
		}

		return false;
	}

	/**
	 * Get stages and create default.
	 * @param int $entityId Id of entity.
	 * @param bool $disableCreate Return stages as is without create defaults.
	 * @return array
	 */
	public static function getStages($entityId = 0, $disableCreate = false)
	{
		static $stages = array();

		$entityType = self::getWorkMode();

		if (isset($stages[$entityType.$entityId]) && !empty($stages[$entityType.$entityId]))
		{
			return $stages[$entityType.$entityId];
		}

		$stages[$entityType.$entityId] = array();

		$res = self::getList(array(
			'filter' => array(
				'ENTITY_ID' => $entityId,
				'=ENTITY_TYPE' => $entityType
			),
			'order' => array(
				'SORT' => 'ASC'
			)
		));
		while ($row = $res->fetch())
		{
			// set default color
			if ($row['COLOR'] == '')
			{
				$row['COLOR'] = self::DEF_COLOR_STAGE;
			}
			// set default title
			if ($row['TITLE'] == '')
			{
				if ($row['SYSTEM_TYPE'] != '')
				{
					$row['TITLE'] = Loc::getMessage('TASKS_STAGE_' . $row['SYSTEM_TYPE']);
				}
				else
				{
					$row['TITLE'] = Loc::getMessage('TASKS_STAGE_' . self::SYS_TYPE_DEFAULT);
				}
			}
			$stages[$entityType.$entityId][$row['ID']] = $row;
		}
		if ($disableCreate)
		{
			return $stages[$entityType.$entityId];
		}
		// if empty, create default stages
		if (empty($stages[$entityType.$entityId]))
		{
			if ($entityType == self::WORK_MODE_USER)
			{
				self::add(array(
					'SYSTEM_TYPE' => self::SYS_TYPE_NEW,
					'TITLE' => Loc::getMessage('TASKS_STAGE_MP_1'),
					'SORT' => 100,
					'ENTITY_ID' => $entityId,
					'ENTITY_TYPE' => $entityType,
					'COLOR' => '00C4FB'
				));
				self::add(array(
					'TITLE' => Loc::getMessage('TASKS_STAGE_MP_2'),
					'SORT' => 200,
					'ENTITY_ID' => $entityId,
					'ENTITY_TYPE' => $entityType,
					'COLOR' => '47D1E2'
				));
			}
			else
			{
				if ($entityId > 0)
				{
					self::getStages(0);
					self::copyView(0, $entityId);
				}
				else
				{
					self::add(array(
						'SYSTEM_TYPE' => self::SYS_TYPE_NEW,
						'SORT' => 100,
						'ENTITY_ID' => $entityId,
						'ENTITY_TYPE' => $entityType,
						'COLOR' => '00C4FB'
					));
					self::add(array(
						'TITLE' => Loc::getMessage('TASKS_STAGE_WORK'),
						'SORT' => 200,
						'ENTITY_ID' => $entityId,
						'ENTITY_TYPE' => $entityType,
						'COLOR' => '47D1E2'
					));
					self::add(array(
						'TITLE' => Loc::getMessage('TASKS_STAGE_FINISH'),
						'SORT' => 300,
						'ENTITY_ID' => $entityId,
						'ENTITY_TYPE' => $entityType,
						'COLOR' => '75D900'
					));
				}
			}
			return self::getStages($entityId);
		}

		return $stages[$entityType.$entityId];
	}

	/**
	 * Add or update stages by code/id.
	 * @param int $id Stage id.
	 * @param array $fields Data array.
	 * @return res Database result.
	 */
	public static function updateByCode($id, $fields)
	{
		$id = intval($id);
		$afterId = isset($fields['AFTER_ID']) ? intval($fields['AFTER_ID']) : 0;
		$entityId = isset($fields['ENTITY_ID']) ? intval($fields['ENTITY_ID']) : 0;
		$entityType = self::getWorkMode();
		// get stages
		$newStageId = 0;
		$stages = array();
		$res = self::getList(array(
			'filter' => array(
				'ENTITY_ID' => $entityId,
				'=ENTITY_TYPE' => $entityType
			),
			'order' => array(
				'SORT' => 'ASC'
			)
		));
		while ($row = $res->fetch())
		{
			if ($row['SYSTEM_TYPE'] == self::SYS_TYPE_NEW)
			{
				$newStageId = $row['ID'];
			}
			$stages[$row['ID']] = $row;
		}
		// if move first - update tasks for fix in this stage
		if (
			isset($fields['AFTER_ID']) &&
			isset($stages[$id]) &&
			$entityType == self::WORK_MODE_GROUP
		)
		{
			if (
				$fields['AFTER_ID'] == 0 ||
				$stages[$id]['SYSTEM_TYPE'] == self::SYS_TYPE_NEW
			)
			{
				$connection = \Bitrix\Main\Application::getConnection();
				$sql = 'UPDATE '
						. '`' . Task::getTableName() . '` '
						. 'SET `STAGE_ID`=' . ($newStageId) . ' '
						. 'WHERE `STAGE_ID`=0 AND `GROUP_ID`=' . $entityId . ';';
				$connection->query($sql);
			}
		}
		// set new
		if (!isset($stages[$id]))
		{
			$id = 0;
		}
		$stages[$id] = array_merge(
						isset($stages[$id]) ? $stages[$id] : array(),
						$fields
					);
		// set sort
		if (array_key_exists('AFTER_ID', $fields))
		{
			if ($afterId == 0)
			{
				$stages[$id]['SORT'] = 10;
			}
			elseif (isset($stages[$afterId]))
			{
				$stages[$id]['SORT'] = $stages[$afterId]['SORT'] + 10;
			}
			else
			{
				$stages[$id]['SORT'] = count($stages) * 100 + 10;
			}
		}
		uasort($stages, function($a, $b)
		{
			if ($a['SORT'] == $b['SORT'])
			{
				return 0;
			}
			return ($a['SORT'] < $b['SORT']) ? -1 : 1;
		});
		// renew
		$return = null;
		$sort = 100;
		foreach ($stages as $i => $stage)
		{
			$fields = array(
				'TITLE' => $stage['TITLE'],
				'COLOR' => $stage['COLOR'],
				'ENTITY_ID' => $stage['ENTITY_ID'],
				'ENTITY_TYPE' => $entityType,
				'SORT' => $sort,
				'SYSTEM_TYPE' => $sort == 100 ? self::SYS_TYPE_NEW : ''
			);
			$sort += 100;
			if ($i > 0)
			{
				$res = self::update($i, $fields);
			}
			else
			{
				$res = self::add($fields);
			}
			if ($i == $id)
			{
				$return = $res;
			}
		}

		return $return;
	}

	/**
	 * Get stages id by stage code.
	 * @param int $id Id of stage.
	 * @param int $entityId Id of entity.
	 * @return int|array
	 */
	public static function getStageIdByCode($id, $entityId = 0)
	{
		if (self::getWorkMode() == self::WORK_MODE_USER)
		{
			return $id;
		}

		$stages = self::getStages($entityId);

		if (isset($stages[$id]))
		{
			$stage = $stages[$id];
			switch ($stage['SYSTEM_TYPE'])
			{
				case self::SYS_TYPE_NEW:
					return array($stage['ID'], 0);
				default:
					return $stage['ID'];
			}
		}

		return -1;
	}

	/**
	 * Get default stage id.
	 * @param int $id Id of stage.
	 * @return int
	 */
	public static function getDefaultStageId($id = 0)
	{
		foreach (self::getStages($id) as $stage)
		{
			if ($stage['SYSTEM_TYPE'] == self::SYS_TYPE_NEW)
			{
				return $stage['ID'];
			}
		}

		return -1;
	}

	/**
	 * Group tasks by filter and return counts for each stage.
	 * @param array $filter Filter for tasks.
	 * @param boolean $userId Context user id.
	 * @return \DatabaseResult
	 */
	public static function getStagesCount(array $filter = array(), $userId = false)
	{
		// common
		$sqlSearch = \CTasks::GetFilter($filter);
		$filterKeys = \CTasks::GetFilteredKeys($filter);
		// uf fields
		$userFieldsSql = new \CUserTypeSQL();
		$userFieldsSql->setEntity('TASKS_TASK', 'T.ID');
		$userFieldsSql->setFilter($filter);
		$ufFilterSql = $userFieldsSql->getFilter();
		if ($ufFilterSql != '')
		{
			$sqlSearch[] = '(' . $ufFilterSql . ')';
		}
		if ($userId === false)
		{
			$userId = \Bitrix\Tasks\Util\User::getId();
		}
		$userId = intval($userId);
		// query
		$connection = \Bitrix\Main\Application::getConnection();
		// if personal - search in another table
		if (self::getWorkMode() == self::WORK_MODE_USER)
		{
			$sql = 'SELECT STG.STAGE_ID, COUNT(STG.STAGE_ID) AS CNT '
					. 'FROM ' . TaskStageTable::getTableName() . ' STG '
					. 'LEFT JOIN ' . Task::getTableName() . ' T ON '
					. 'T.ID = STG.TASK_ID'
					. (in_array('FAVORITE', $filterKeys)
						?	' LEFT JOIN ' . FavoriteTable::getTableName() . ' FVT ON ' .
							' FVT.TASK_ID = T.ID AND FVT.USER_ID = ' . $userId . ' '
						: '')
					.	' LEFT JOIN ' . ViewedTable::getTableName() . ' TV ON ' .
						' TV.TASK_ID = T.ID AND TV.USER_ID = ' . $userId . ' '
					. 'WHERE ' . implode(' AND ', $sqlSearch) . ' '
					. 'GROUP BY STG.STAGE_ID;';
		}
		// else tasks table
		else
		{
			$sql = 'SELECT T.STAGE_ID, COUNT(T.STAGE_ID) AS CNT '
					. 'FROM ' . Task::getTableName() . ' T '
					. (in_array('FAVORITE', $filterKeys)
						?	' LEFT JOIN ' . FavoriteTable::getTableName() . ' FVT ON ' .
							' FVT.TASK_ID = T.ID AND FVT.USER_ID = ' . $userId . ' '
						: '')
					.	' LEFT JOIN ' . ViewedTable::getTableName() . ' TV ON ' .
						' TV.TASK_ID = T.ID AND TV.USER_ID = ' . $userId . ' '
					. 'WHERE ' . implode(' AND ', $sqlSearch) . ' '
					. 'GROUP BY T.STAGE_ID;';
		}
		return $connection->query($sql);
	}

	/**
	 * Copy view from one entity to another.
	 * @param int $fromEntityId From entity Id.
	 * @param int $toEntityId To entity Id.
	 * @param string $entityType Entity type.
	 * @return void
	 */
	public static function copyView($fromEntityId, $toEntityId, $entityType = self::WORK_MODE_GROUP)
	{
		if (
			$fromEntityId != $toEntityId &&
			($entityType == self::WORK_MODE_USER || $entityType == self::WORK_MODE_GROUP)
		)
		{
			$res = self::getList(array(
				'filter' => array(
					'ENTITY_ID' => $fromEntityId,
					'=ENTITY_TYPE' => $entityType
				),
				'order' => array(
					'ID' => 'ASC'
				)
			));
			while ($row = $res->fetch())
			{
				unset($row['ID']);
				$row['ENTITY_ID'] = $toEntityId;
				self::add($row);
			}
		}
	}

	/**
	 * Disable pin in stage for user.
	 * @param int|array $userIds User id.
	 * @return void
	 */
	public static function disablePinForUser($userIds)
	{
		if (!is_array($userIds))
		{
			$userIds = array($userIds);
		}
		self::$disablePin = array_merge(self::$disablePin, $userIds);
	}

	/**
	 * Disable link in stage for user.
	 * @param int|array $userIds User id.
	 * @return void
	 */
	public static function disableLinkForUser($userIds)
	{
		if (!is_array($userIds))
		{
			$userIds = array($userIds);
		}
		self::$disableLink = array_merge(self::$disableLink, $userIds);
	}

	/**
	 * Pin the task in the DEFAULT stage for users/group.
	 * @param int $taskId Task id.
	 * @param int|array $users Pin for users.
	 * @param boolean $refreshGroup Refresh sorting in group.
	 * @return void
	 */
	public static function pinInStage($taskId, $users = array(), $refreshGroup = false)
	{
		if (!is_array($users))
		{
			$users = array($users);
		}
		$newTask = empty($users);

		// get additional data
		$currentUsers = array();
		$res = \CTasks::getList(array(
				//
			), array(
				'ID' => $taskId,
				'CHECK_PERMISSIONS' => 'N'
			), array(
				'ID', 'GROUP_ID', 'STAGE_ID',
				'RESPONSIBLE_ID', 'CREATED_BY'
			)
		);
		if (($task = $res->fetch()))
		{
			$currentUsers[] = $task['RESPONSIBLE_ID'];
			$currentUsers[] = $task['CREATED_BY'];
		}
		else
		{
			return;
		}

		// prepare sort/filter
		$sort = array(
			'SORTING' => 'ASC',
			'STATUS_COMPLETE' => 'ASC',
			'DEADLINE' => 'ASC,NULLS',
			'ID' => 'ASC'
		);
		$filter = array(
			'CHECK_PERMISSIONS' => 'N',
			'ONLY_ROOT_TASKS' => 'N',
			'GROUP_ID' => $task['GROUP_ID'],
			'!ID' => $taskId
		);

		// get current other members
		$res = \CTaskMembers::GetList(
			array(),
			array('TASK_ID' => $taskId)
		);
		while ($row = $res->fetch())
		{
			$currentUsers[] = $row['USER_ID'];
		}

		if ($newTask)
		{
			$users = $currentUsers;
			$currentUsers = array();
		}

		$users = array_unique($users);

		// pin in personal default stage (if already Kanban exist)
		$personaleDefStages = array();
		self::setWorkMode(self::WORK_MODE_USER);
		foreach ($users as $userId)
		{
			$checkStages = self::getStages($userId, true);
			if (!empty($checkStages))
			{
				$personaleDefStages[$userId] = self::getDefaultStageId($userId);
				if (!in_array($userId, self::$disableLink))
				{
					$resStg = TaskStageTable::getList(array(
						'filter' => array(
							'TASK_ID' => $taskId,
							'STAGE_ID' => array_keys($checkStages)
						)
					));
					if (!$resStg->fetch())
					{
						TaskStageTable::add(array(
							'TASK_ID' => $taskId,
							'STAGE_ID' => self::getDefaultStageId($userId)
						));
					}
				}
			}
		}

		// work mode
		self::setWorkMode(
			$task['GROUP_ID'] > 0
			? self::WORK_MODE_GROUP
			: self::WORK_MODE_USER
		);

		if ($task['GROUP_ID'] > 0 && ($newTask || $refreshGroup))
		{
			$checkStages = self::getStages($task['GROUP_ID'], true);
		}
		else
		{
			$checkStages = array();
		}

		// one sort for project
		if ($task['GROUP_ID'] > 0 && !empty($checkStages) && ($newTask || $refreshGroup))
		{
			// if task without stage id
			if (!$task['STAGE_ID'])
			{
				$task['STAGE_ID'] = self::getDefaultStageId($task['GROUP_ID']);
			}
			$filter['STAGE_ID'] = self::getStageIdByCode(
				$task['STAGE_ID'],
				$task['GROUP_ID']
			);
			// get order
			if (($project = ProjectsTable::getById($task['GROUP_ID'])->fetch()))
			{
				$order = $project['ORDER_NEW_TASK'] ? $project['ORDER_NEW_TASK'] : 'desc';
			}
			else
			{
				$order = 'desc';
			}
			if ($order == 'asc')
			{
				$sort = array(
					'SORTING' => 'DESC',
					'STATUS_COMPLETE' => 'DESC',
					'DEADLINE' => 'DESC',
					'ID' => 'DESC'
				);
			}
			// set sorting
			$res = \CTasks::getList(
				$sort,
				$filter,
				array('ID', 'TITLE'),
				array(
					'NAV_PARAMS' => array(
						'nTopCount' => 1
					),
					'SORTING_GROUP_ID' => $task['GROUP_ID']
				)
			);
			if ($row = $res->fetch())
			{
				SortingTable::setSorting(
					\Bitrix\Tasks\Util\User::getId(),
					$task['GROUP_ID'],
					$taskId,
					$row['ID'],
					$order == 'asc' ? false : true
				);
			}
		}

		// and for each user
		foreach ($users as $userId)
		{
			if (
				!in_array($userId, self::$disablePin) &&
				!in_array($userId, $currentUsers) &&
				isset($personaleDefStages[$userId])
			)
			{
				// get order
				$order = \CUserOptions::getOption(
					'tasks',
					'order_new_task',
					'desc',
					$userId
				);
				if ($order == 'asc')
				{
					$sort = array(
						'SORTING' => 'DESC',
						'STATUS_COMPLETE' => 'DESC',
						'DEADLINE' => 'DESC',
						'ID' => 'DESC'
					);
				}
				else
				{
					$sort = array(
						'SORTING' => 'ASC',
						'STATUS_COMPLETE' => 'ASC',
						'DEADLINE' => 'ASC,NULLS',
						'ID' => 'ASC'
					);
				}
				// set sorting
				if (isset($filter['STAGE_ID']))
				{
					unset($filter['STAGE_ID']);
				}
				if (isset($filter['GROUP_ID']))
				{
					unset($filter['GROUP_ID']);
				}
				$filter['MEMBER'] = $userId;
				$filter['STAGES_ID'] = $personaleDefStages[$userId];
				$res = \CTasks::getList(
					$sort,
					$filter,
					array('ID', 'TITLE'),
					array(
						'NAV_PARAMS' => array(
							'nTopCount' => 1
						),
						'USER_ID' => $userId
					)
				);
				if ($row = $res->fetch())
				{
					SortingTable::setSorting(
						$userId,
						0,
						$taskId,
						$row['ID'],
						$order == 'asc' ? false : true
					);
				}
			}
		}
	}

	/**
	 * Pin the task in the stage for user/group.
	 * @param int $taskId Task id.
	 * @param int $stageId Stage id.
	 * @return void
	 */
	public static function pinInTheStage($taskId, $stageId)
	{
		if (($stage = StagesTable::getById($stageId)->fetch()))
		{
			$order = 'desc';
			// get order
			if ($stage['ENTITY_TYPE'] == self::WORK_MODE_GROUP)
			{
				if (($project = ProjectsTable::getById($stage['ENTITY_ID'])->fetch()))
				{
					$order = $project['ORDER_NEW_TASK'] ? $project['ORDER_NEW_TASK'] : 'desc';
				}
			}
			else
			{
				$order = \CUserOptions::getOption(
					'tasks',
					'order_new_task',
					'desc',
					$stage['ENTITY_ID']
				);
			}
			// set order
			if ($order == 'desc')
			{
				$sort = array(
					'SORTING' => 'ASC',
					'STATUS_COMPLETE' => 'ASC',
					'DEADLINE' => 'ASC,NULLS',
					'ID' => 'ASC'
				);
			}
			else
			{
				$sort = array(
					'SORTING' => 'DESC',
					'STATUS_COMPLETE' => 'DESC',
					'DEADLINE' => 'DESC',
					'ID' => 'DESC'
				);
			}
			// set filter
			$filter = array(
				'CHECK_PERMISSIONS' => 'N',
				'ONLY_ROOT_TASKS' => 'N',
				'!ID' => $taskId
			);
			if ($stage['ENTITY_TYPE'] == self::WORK_MODE_GROUP)
			{
				$filter['GROUP_ID'] = $stage['ENTITY_ID'];
				$filter['STAGE_ID'] = self::getStageIdByCode(
					$stageId,
					$stage['ENTITY_ID']
				);
			}
			else
			{
				$filter['MEMBER'] = $stage['ENTITY_ID'];
				$filter['STAGES_ID'] = $stageId;
			}
			// set params
			$params = array(
				'NAV_PARAMS' => array(
					'nTopCount' => 1
				)
			);
			if ($stage['ENTITY_TYPE'] == self::WORK_MODE_GROUP)
			{
				$params['SORTING_GROUP_ID'] = $stage['ENTITY_ID'];
			}
			else
			{
				$params['USER_ID'] = $stage['ENTITY_ID'];
			}
			// set sorting
			$res = \CTasks::getList(
				$sort,
				$filter,
				array('ID'),
				$params
			);
			if ($row = $res->fetch())
			{
				if ($stage['ENTITY_TYPE'] == self::WORK_MODE_GROUP)
				{
					$userId = \Bitrix\Tasks\Util\User::getId();
					$groupId = $stage['ENTITY_ID'];
				}
				else
				{
					$userId = $stage['ENTITY_ID'];
					$groupId = 0;
				}
				SortingTable::setSorting(
					$userId,
					$groupId,
					$taskId,
					$row['ID'],
					$order == 'asc' ? false : true
				);
			}
		}
	}

	/**
	 * Delete all stages of group after group delete.
	 * @param int $groupId Group id.
	 * @return void
	 */
	public static function onSocNetGroupDelete($groupId)
	{
		$res = self::getList(array(
			'filter' => array(
				'ENTITY_ID' => $groupId,
				'=ENTITY_TYPE' => self::WORK_MODE_GROUP
			)
		));
		while ($row = $res->fetch())
		{
			parent::delete($row['ID']);
		}
	}

	/**
	 * Delete all stages of user after user delete.
	 * @param int $userId User id.
	 * @return void
	 */
	public static function onUserDelete($userId)
	{
		$res = self::getList(array(
			'filter' => array(
				'ENTITY_ID' => $userId,
				'=ENTITY_TYPE' => self::WORK_MODE_USER
			)
		));
		while ($row = $res->fetch())
		{
			parent::delete($row['ID']);
		}
	}

	/**
	 * On stage delete.
	 * @param Entity\Event $event Event.
	 * @return void
	 */
	public static function onDelete(Entity\Event $event)
	{
		$primary = $event->getParameter('id');
		TaskStageTable::clearStage($primary['ID']);
	}
}