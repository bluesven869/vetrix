<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2015 Bitrix
 */

/** !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! */
/** This is alfa version of component! Don't use it! */
/** !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! */

use Bitrix\Main\Localization\Loc;
use Bitrix\Tasks\Ui\Filter;

Loc::loadMessages(__FILE__);

CBitrixComponent::includeComponentClass("bitrix:tasks.base");

class TasksToolbarComponent extends TasksBaseComponent
{
	protected $gridOptions;
	protected $listState;
	protected $listCtrl;


	protected function checkParameters()
	{
		parent::checkParameters();

		$arParams =& $this->arParams;

		static::tryParseStringParameter($arParams[ 'SHOW_TOOLBAR' ], 'N');
		if($arParams['GROUP_ID'] > 0)
		{
			$arParams[ 'SHOW_TOOLBAR' ] = 'N';
		}
	}

	protected function doPreAction()
	{
		parent::doPreAction();

		$this->listState = Filter\Task::getListStateInstance();
		$this->listCtrl = Filter\Task::getListCtrlInstance();
		$this->listCtrl->useState($this->listState);

		$this->arResult[ 'VIEW_LIST' ] = $this->getViewList();
		//tmp
		if (isset($this->arResult['VIEW_LIST']['VIEW_MODE_TIMELINE']))
		{
			unset($this->arResult['VIEW_LIST']['VIEW_MODE_TIMELINE']);
		}
		$this->arResult[ 'COUNTERS' ] = $this->getCounters();
	}

	protected function getViewList()
	{
		$viewState = self::getViewState();
		return $viewState[ 'VIEWS' ];
	}

	private function getViewState()
	{
		static $viewState = null;
		if (is_null($viewState))
		{
			$viewState = $this->listState->getState();
		}

		return $viewState;
	}

	private function isAccessToCounters()
	{
		return ($this->arParams[ 'USER_ID' ] == $this->userId)
			   || \Bitrix\Tasks\Util\User::isAdmin()
			   || CTasksTools::IsPortalB24Admin()
			   || CTasks::IsSubordinate($this->arParams[ 'USER_ID' ], $this->userId);
	}

	protected function getCounters()
	{
		$counters = array();
		// not in groups
		if ($this->arParams[ 'GROUP_ID' ] > 0)
		{
			return $counters;
		}
		// check rights
		if (!$this->isAccessToCounters())
		{
			return $counters;
		}
		// alright - get counters

		$viewState = self::getViewState();
		$roleId = $viewState[ 'ROLE_SELECTED' ][ 'ID' ];
		$roleName = $viewState[ 'ROLE_SELECTED' ][ 'CODENAME' ];

		// set extended counter info
		switch ($roleName)
		{
			case 'VIEW_ROLE_RESPONSIBLE':
				$counters[ CTaskListState::VIEW_TASK_CATEGORY_NEW ] = $this->listCtrl->getCounter($roleId, CTaskListState::VIEW_TASK_CATEGORY_NEW);
				$counters[ CTaskListState::VIEW_TASK_CATEGORY_WO_DEADLINE ] = $this->listCtrl->getCounter($roleId, CTaskListState::VIEW_TASK_CATEGORY_WO_DEADLINE);
				$counters[ CTaskListState::VIEW_TASK_CATEGORY_EXPIRED ] = $this->listCtrl->getCounter($roleId, CTaskListState::VIEW_TASK_CATEGORY_EXPIRED);
				$counters[ CTaskListState::VIEW_TASK_CATEGORY_EXPIRED_CANDIDATES ] = $this->listCtrl->getCounter($roleId, CTaskListState::VIEW_TASK_CATEGORY_EXPIRED_CANDIDATES);
				break;
			case 'VIEW_ROLE_ACCOMPLICE':
				$counters[ CTaskListState::VIEW_TASK_CATEGORY_NEW ] = $this->listCtrl->getCounter($roleId, CTaskListState::VIEW_TASK_CATEGORY_NEW);
				$counters[ CTaskListState::VIEW_TASK_CATEGORY_EXPIRED ] = $this->listCtrl->getCounter($roleId, CTaskListState::VIEW_TASK_CATEGORY_EXPIRED);
				$counters[ CTaskListState::VIEW_TASK_CATEGORY_EXPIRED_CANDIDATES ] = $this->listCtrl->getCounter($roleId, CTaskListState::VIEW_TASK_CATEGORY_EXPIRED_CANDIDATES);
				break;
			case 'VIEW_ROLE_AUDITOR':
				$counters[ CTaskListState::VIEW_TASK_CATEGORY_EXPIRED ] = $this->listCtrl->getCounter($roleId, CTaskListState::VIEW_TASK_CATEGORY_EXPIRED);
				break;
			case 'VIEW_ROLE_ORIGINATOR':
				$counters[ CTaskListState::VIEW_TASK_CATEGORY_WO_DEADLINE ] = $this->listCtrl->getCounter($roleId, CTaskListState::VIEW_TASK_CATEGORY_WO_DEADLINE);
				$counters[ CTaskListState::VIEW_TASK_CATEGORY_WAIT_CTRL ] = $this->listCtrl->getCounter($roleId, CTaskListState::VIEW_TASK_CATEGORY_WAIT_CTRL);
				$counters[ CTaskListState::VIEW_TASK_CATEGORY_EXPIRED ] = $this->listCtrl->getCounter($roleId, CTaskListState::VIEW_TASK_CATEGORY_EXPIRED);
				break;
		}

		return $counters;
	}
}