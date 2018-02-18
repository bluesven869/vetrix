<?php

namespace Bitrix\Disk\Volume;

use Bitrix\Main\Entity;
use Bitrix\Disk\Volume;
use Bitrix\Disk\Internals\Error\Error;
use Bitrix\Disk\Internals\Error\ErrorCollection;
use Bitrix\Disk\Internals\Error\IErrorable;


/**
 * Disk cleanlier class.
 * @package Bitrix\Disk\Volume
 */
class Cleaner implements IErrorable, Volume\IVolumeTimeLimit
{
	/** @var ErrorCollection */
	private $errorCollection;

	/** @var Volume\Timer */
	private $timer;

	/** @var Volume\Task */
	public $task;

	/** @var \Bitrix\Disk\User $owner */
	private $owner;

	// interval agent start
	const AGENT_INTERVAL = 10;

	// fix every n interaction
	const STATUS_FIX_INTERVAL = 20;

	// limit maximum number selected files
	const MAX_FILE_PER_INTERACTION = 1000;

	// limit maximum number selected folders
	const MAX_FOLDER_PER_INTERACTION = 1000;


	/**
	 * @param int $ownerId Whom will mark as deleted by.
	 * @param int $filterId Id of saved indicator result from b_disk_volume.
	 */
	public function __construct($ownerId = \Bitrix\Disk\SystemUser::SYSTEM_USER_ID, $filterId = -1)
	{
		$this->errorCollection = new ErrorCollection();

		$this->timer = new Volume\Timer();
		if (self::isCronRun())
		{
			$this->timer->setTimeLimit(\Bitrix\Disk\Volume\Timer::MAX_EXECUTION_TIME * 20);
		}
		$this->timer->startTimer();

		$this->task = new Volume\Task();
		if ($filterId > 0)
		{
			if(!$this->task->loadTaskById($filterId, $ownerId))
			{
				$this->errorCollection->add(array(new Error('Cleaner task not found', 'CLEANER_TASK_NOT_FOUND')));
			}
		}

		if ($this->task->getOwnerId() > 0)
		{
			$this->owner = \Bitrix\Disk\User::loadById($this->task->getOwnerId());
		}
	}



	/**
	 * Returns the fully qualified name of this class.
	 * @return string
	 */
	public static function className()
	{
		return get_called_class();
	}


	/**
	 * Returns agent's name.
	 * @param int|string $filterId Id of saved indicator result from b_disk_volume.
	 * @return string
	 */
	public static function agentName($filterId)
	{
		return static::className()."::runWorker({$filterId});";
	}

	/**
	 * Determines if a script is loaded via cron/command line.
	 * @return bool
	 */
	public static function isCronRun()
	{
		$isCronRun = false;
		if (
			!\Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24') &&
			(php_sapi_name() === 'cli')
		)
		{
			$isCronRun = true;
		}

		return $isCronRun;
	}


	/**
	 * Runs clean process.
	 * @param int $filterId Id of saved indicator result from b_disk_volume.
	 * @return string
	 */
	public static function runWorker($filterId)
	{
		// only one interaction per hit
		if (!self::isCronRun())
		{
			if (defined(__NAMESPACE__ . '\\CLEANER_RUN_WORKER_LOCK'))
			{
				// do nothing, repeat
				return static::agentName($filterId);
			}
		}

		$cleaner = new static(\Bitrix\Disk\SystemUser::SYSTEM_USER_ID, $filterId);

		if (!Volume\Task::isRunningMode($cleaner->task->getStatus()))
		{
			return '';// non running state
		}

		$indicator = $cleaner->task->getIndicator();
		if (!$indicator instanceof Volume\IVolumeIndicator)
		{
			return '';
		}

		if (!defined(__NAMESPACE__ . '\\CLEANER_RUN_WORKER_LOCK'))
		{
			define(__NAMESPACE__ . '\\CLEANER_RUN_WORKER_LOCK', true);
		}

		if ($cleaner->task->getStatus() != Volume\Task::TASK_STATUS_RUNNING)
		{
			$cleaner->task->setStatus(Volume\Task::TASK_STATUS_RUNNING);
		}

		// subTask to run
		$subTask = '';
		if (Volume\Task::isRunningMode($cleaner->task->getStatusSubTask(Volume\Task::DROP_TRASHCAN)))
		{
			$subTask = Volume\Task::DROP_TRASHCAN;
		}
		elseif (Volume\Task::isRunningMode($cleaner->task->getStatusSubTask(Volume\Task::EMPTY_FOLDER)))
		{
			$subTask = Volume\Task::EMPTY_FOLDER;
		}
		elseif (Volume\Task::isRunningMode($cleaner->task->getStatusSubTask(Volume\Task::DROP_FOLDER)))
		{
			$subTask = Volume\Task::DROP_FOLDER;
		}
		elseif (Volume\Task::isRunningMode($cleaner->task->getStatusSubTask(Volume\Task::DROP_UNNECESSARY_VERSION)))
		{
			$subTask = Volume\Task::DROP_UNNECESSARY_VERSION;
		}

		$repeatMeasure = function () use ($cleaner, $indicator)
		{
			// reset offset
			$cleaner->task->setLastFileId(0);
			$cleaner->task->fixState();

			// check final result repeat measure
			\Bitrix\Disk\Volume\Cleaner::repeatMeasure($indicator);

			// reload task
			$cleaner->task->loadTaskById($indicator->getFilterId(), $cleaner->task->getOwnerId());
		};

		// run subTask
		$taskDone = false;
		switch ($subTask)
		{
			case Volume\Task::DROP_TRASHCAN:
			{
				if ($cleaner->task->getStatusSubTask($subTask) != Volume\Task::TASK_STATUS_RUNNING)
				{
					$cleaner->task->setStatusSubTask($subTask, Volume\Task::TASK_STATUS_RUNNING);
				}

				if($cleaner->deleteTrashcanByFilter($indicator))
				{
					$repeatMeasure();
					$taskDone = $cleaner->task->hasTaskFinished($subTask);
				}

				break;
			}

			case Volume\Task::EMPTY_FOLDER:
			case Volume\Task::DROP_FOLDER:
			{
				if ($cleaner->task->getStatusSubTask($subTask) != Volume\Task::TASK_STATUS_RUNNING)
				{
					$cleaner->task->setStatusSubTask($subTask, Volume\Task::TASK_STATUS_RUNNING);
				}

				$folderId = $cleaner->task->getParam('FOLDER_ID');
				$folder = \Bitrix\Disk\Folder::getById($folderId);
				if ($folder instanceof \Bitrix\Disk\Folder)
				{
					if ($cleaner->deleteFolder($folder, ($subTask === Volume\Task::EMPTY_FOLDER)))
					{
						$repeatMeasure();
						$taskDone = $cleaner->task->hasTaskFinished($subTask);
					}
				}

				break;
			}

			case Volume\Task::DROP_UNNECESSARY_VERSION:
			{
				if ($cleaner->task->getStatusSubTask($subTask) != Volume\Task::TASK_STATUS_RUNNING)
				{
					$cleaner->task->setStatusSubTask($subTask, Volume\Task::TASK_STATUS_RUNNING);
				}

				if($cleaner->deleteUnnecessaryVersionByFilter($indicator))
				{
					$repeatMeasure();
					$taskDone = $cleaner->task->hasTaskFinished($subTask);
				}

				break;
			}

			default:
			{
				$taskDone = true;
			}
		}

		/*
		if ($taskDone)
		{
			// reset offset
			$cleaner->task->setLastFileId(0);
			$cleaner->task->fixState();

			// check final result repeat measure
			//self::repeatMeasure($indicator);

			// reload task
			$cleaner->task->loadTaskById($filterId, $cleaner->task->getOwnerId());
			$taskDone = $cleaner->task->hasTaskFinished($subTask);
		}
		*/

		// $cleaner->hasErrors()
		///$cleaner->hasTimeLimitReached()

		if($taskDone)
		{
			// finish
			$cleaner->task->setStatusSubTask($subTask, Volume\Task::TASK_STATUS_DONE);
			$cleaner->task->setStatus(Volume\Task::TASK_STATUS_DONE);
		}

		// Fix task state
		$cleaner->task->fixState();

		// count statistic for progress bar
		self::countWorker($cleaner->task->getOwnerId());

		if($taskDone)
		{
			return '';
		}

		return static::agentName($filterId);
	}


	/**
	 * Adds delayed delete worker agent.
	 * @param array $params Named parameters:
	 * 		int ownerId - who is owner,
	 * 		int filterId - as row private id from b_disk_volume as filter id,
	 * 		int storageId - limit only one storage
	 * 		bool DROP_UNNECESSARY_VERSION - set job to delete unused version,
	 * 		bool DROP_TRASHCAN - set job to empty trashcan.
	 * 		bool DROP_FOLDER - set job to drop everything.
	 * 		bool EMPTY_FOLDER - set job to empty folder structure.
	 * @return boolean
	 */
	public static function addWorker($params)
	{
		$ownerId = (int)$params['ownerId'];
		$filterId = (int)$params['filterId'];

		$task = new Volume\Task();
		if ($filterId > 0)
		{
			if(!$task->loadTaskById($filterId, $ownerId))
			{
				return false;
			}
			$ownerId = $task->getOwnerId();
		}

		$task->setStatus(Volume\Task::TASK_STATUS_WAIT);

		$subTaskCommands = array(
			Volume\Task::DROP_UNNECESSARY_VERSION,
			Volume\Task::DROP_TRASHCAN,
			Volume\Task::DROP_FOLDER,
			Volume\Task::EMPTY_FOLDER,
		);
		foreach ($subTaskCommands as $command)
		{
			if (isset($params[$command]))
			{
				$task->setStatusSubTask(
					$command,
					(($params[$command] === true) ? Volume\Task::TASK_STATUS_WAIT : Volume\Task::TASK_STATUS_NONE)
				);
			}
		}


		if ($filterId > 0)
		{
			if (isset($params['manual']))
			{
				$task->resetFail();
			}
			$agentParamsAdded = $task->fixState();
		}
		else
		{
			$task->setIndicatorType(Volume\Storage\Storage::className());
			$task->setParam('STORAGE_ID', (int)$params['storageId']);
			$task->setOwnerId($ownerId);

			$agentParamsAdded = $task->fixState();
			$filterId = $task->getId();
		}

		$agentAdded = false;
		if ($agentParamsAdded && $filterId > 0)
		{
			$agentAdded = true;
			$agents = \CAgent::GetList(
				array('ID' => 'DESC'),
				array('=NAME' => static::agentName($filterId))
			);
			if (!$agents->Fetch())
			{
				$agentAdded = (bool)(\CAgent::AddAgent(
										static::agentName($filterId),
										'disk',
										(self::canAgentUseCrontab() ? 'N' : 'Y'),
										self::AGENT_INTERVAL
									) !== false);
			}
		}

		// count statistic for progress bar
		self::countWorker($ownerId);

		return $agentAdded;
	}


	/**
	 * Checks ability agent to use Crontab.
	 * @return bool
	 */
	public static function canAgentUseCrontab()
	{
		$canAgentsUseCrontab = false;
		$agentsUseCrontab = \Bitrix\Main\Config\Option::get('main', 'agents_use_crontab', 'N');
		if (
			!\Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24') &&
			($agentsUseCrontab === 'Y' || (defined('BX_CRONTAB_SUPPORT') && BX_CRONTAB_SUPPORT === true))
		)
		{
			$canAgentsUseCrontab = true;
		}

		return $canAgentsUseCrontab;
	}

	/**
	 * Cancels all agent process.
	 * @param int $ownerId Whom will mark as deleted by.
	 * @return void
	 */
	public static function cancelWorkers($ownerId)
	{
		$workerResult = \Bitrix\Disk\Internals\VolumeTable::getList(array(
			'select' => array(
				'ID',
			),
			'filter' => array(
				'=OWNER_ID' => $ownerId,
				'=AGENT_LOCK' => array(Volume\Task::TASK_STATUS_WAIT, Volume\Task::TASK_STATUS_RUNNING),
			)
		));
		foreach ($workerResult as $row)
		{
			\Bitrix\Disk\Internals\VolumeTable::update($row['ID'], array('AGENT_LOCK' => Volume\Task::TASK_STATUS_CANCEL));
		}

		self::clearProgressInfo($ownerId);
	}


	/**
	 * Count worker agent for user.
	 * @param int $ownerId Whom will mark as deleted by.
	 * @return int
	 */
	public static function countWorker($ownerId)
	{
		$workerResult = \Bitrix\Disk\Internals\VolumeTable::getList(array(
			'runtime' => array(
				new Entity\ExpressionField('CNT', 'COUNT(*)'),
				new Entity\ExpressionField('FILE_COUNT', 'SUM(FILE_COUNT)'),
				new Entity\ExpressionField('UNNECESSARY_VERSION_COUNT', 'SUM(UNNECESSARY_VERSION_COUNT)'),
				new Entity\ExpressionField('DROPPED_FILE_COUNT', 'SUM(DROPPED_FILE_COUNT)'),
				new Entity\ExpressionField('DROPPED_VERSION_COUNT', 'SUM(DROPPED_VERSION_COUNT)'),
				new Entity\ExpressionField('FAIL_COUNT', 'SUM(FAIL_COUNT)'),
			),
			'select' => array(
				'CNT',
				'FILE_COUNT',
				'UNNECESSARY_VERSION_COUNT',
				'DROPPED_FILE_COUNT',
				'DROPPED_VERSION_COUNT',
				'FAIL_COUNT',
				\Bitrix\Disk\Volume\Task::DROP_UNNECESSARY_VERSION,
				\Bitrix\Disk\Volume\Task::DROP_TRASHCAN,
				\Bitrix\Disk\Volume\Task::EMPTY_FOLDER,
				\Bitrix\Disk\Volume\Task::DROP_FOLDER,
			),
			'group' => array(
				\Bitrix\Disk\Volume\Task::DROP_UNNECESSARY_VERSION,
				\Bitrix\Disk\Volume\Task::DROP_TRASHCAN,
				\Bitrix\Disk\Volume\Task::EMPTY_FOLDER,
				\Bitrix\Disk\Volume\Task::DROP_FOLDER,
			),
			'filter' => array(
				'=OWNER_ID' => $ownerId,
				'=AGENT_LOCK' => array(Volume\Task::TASK_STATUS_WAIT, Volume\Task::TASK_STATUS_RUNNING),
			)
		));

		$totalFilesToDrop = 0;
		$droppedFilesCount = 0;
		$workerCount = 0;
		$failCount = 0;

		if ($workerResult->getSelectedRowsCount() > 0)
		{
			foreach ($workerResult as $row)
			{
				$workerCount += $row['CNT'];
				$failCount += $row['FAIL_COUNT'];
				if (Volume\Task::isRunningMode($row[\Bitrix\Disk\Volume\Task::DROP_UNNECESSARY_VERSION]))
				{
					$totalFilesToDrop += $row['UNNECESSARY_VERSION_COUNT'];
					$droppedFilesCount += $row['DROPPED_VERSION_COUNT'];
				}
				if (Volume\Task::isRunningMode($row[\Bitrix\Disk\Volume\Task::DROP_TRASHCAN]))
				{
					$totalFilesToDrop += $row['FILE_COUNT'];
					$droppedFilesCount += $row['DROPPED_FILE_COUNT'];
				}
				if (Volume\Task::isRunningMode($row[\Bitrix\Disk\Volume\Task::DROP_FOLDER]))
				{
					$totalFilesToDrop += $row['FILE_COUNT'];
					$droppedFilesCount += $row['DROPPED_FILE_COUNT'];
				}
				if (Volume\Task::isRunningMode($row[\Bitrix\Disk\Volume\Task::EMPTY_FOLDER]))
				{
					$totalFilesToDrop += $row['FILE_COUNT'];
					$droppedFilesCount += $row['DROPPED_FILE_COUNT'];
				}
			}
			self::setProgressInfo($ownerId, $totalFilesToDrop, $droppedFilesCount, $failCount);
		}
		else
		{
			self::clearProgressInfo($ownerId);
		}

		return $workerCount;
	}


	/**
	 * Check if workers exists. Sets up/removes missing task. Remove stepper info.
	 * @param int $ownerId Whom will mark as deleted by.
	 * @return int
	 */
	public static function checkRestoreWorkers($ownerId)
	{
		$workerResult = \Bitrix\Disk\Internals\VolumeTable::getList(array(
			'select' => array(
				'ID',
				'STORAGE_ID',
				\Bitrix\Disk\Volume\Task::DROP_UNNECESSARY_VERSION,
				\Bitrix\Disk\Volume\Task::DROP_TRASHCAN,
				\Bitrix\Disk\Volume\Task::EMPTY_FOLDER,
				\Bitrix\Disk\Volume\Task::DROP_FOLDER,
			),
			'filter' => array(
				'=OWNER_ID' => $ownerId,
				'=AGENT_LOCK' => array(Volume\Task::TASK_STATUS_WAIT, Volume\Task::TASK_STATUS_RUNNING),
			)
		));
		$workerCount = 0;
		if ($workerResult->getSelectedRowsCount() > 0)
		{
			$agents = \CAgent::GetList(
				array('ID' => 'DESC'),
				array('NAME' => self::agentName('%'))
			);
			$agentList = array();
			while ($agent = $agents->Fetch())
			{
				$agentList[] = $agent['NAME'];
			}

			$restoredWorkerCount = 0;
			foreach ($workerResult as $row)
			{
				$workerCount ++;
				if (in_array(self::agentName($row['ID']), $agentList) === false)
				{
					$agentParams = array(
						'ownerId' => $ownerId,
						'storageId' => $row['STORAGE_ID'],
						'filterId' => $row['ID'],
					);
					if (Volume\Task::isRunningMode($row[\Bitrix\Disk\Volume\Task::DROP_UNNECESSARY_VERSION]))
					{
						$agentParams[\Bitrix\Disk\Volume\Task::DROP_UNNECESSARY_VERSION] = true;
					}
					if (Volume\Task::isRunningMode($row[\Bitrix\Disk\Volume\Task::DROP_TRASHCAN]))
					{
						$agentParams[\Bitrix\Disk\Volume\Task::DROP_TRASHCAN] = true;
					}
					if (Volume\Task::isRunningMode($row[\Bitrix\Disk\Volume\Task::DROP_FOLDER]))
					{
						$agentParams[\Bitrix\Disk\Volume\Task::DROP_FOLDER] = true;
					}
					if (Volume\Task::isRunningMode($row[\Bitrix\Disk\Volume\Task::EMPTY_FOLDER]))
					{
						$agentParams[\Bitrix\Disk\Volume\Task::EMPTY_FOLDER] = true;
					}

					if (self::addWorker($agentParams))
					{
						$restoredWorkerCount ++;
					}
				}
			}

			if ($restoredWorkerCount > 0)
			{
				self::countWorker($ownerId);
			}
		}
		else
		{
			self::clearProgressInfo($ownerId);
		}

		return $workerCount;
	}


	/**
	 * Deletes files corresponding to indicator filter.
	 * @param Volume\IVolumeIndicator $indicator Ignited indicator for file list filter.
	 * @return boolean
	 */
	public function deleteFileByFilter(Volume\IVolumeIndicator $indicator)
	{
		$subTaskDone = true;

		$filter = array();
		if ($this->task->getLastFileId() > 0)
		{
			$filter['>=ID'] = $this->task->getLastFileId();
		}

		$indicator->setLimit(self::MAX_FILE_PER_INTERACTION);

		$fileList = $indicator->getCorrespondingFileList($filter);

		$this->task->setIterationFileCount($fileList->getSelectedRowsCount());

		$countFileErasure = 0;

		foreach ($fileList as $row)
		{
			$fileId = $row['ID'];
			$file = \Bitrix\Disk\File::getById($fileId);
			if ($file instanceof \Bitrix\Disk\File)
			{
				$securityContext = $this->getSecurityContext($this->owner, $file);
				if($file->canDelete($securityContext))
				{
					$this->deleteFile($file);
					$countFileErasure ++;
				}
				else
				{
					$errorText = "Access denied to file #$fileId";
					$this->task->increaseFailCount();
					$this->task->setLastError($errorText);
					$this->errorCollection->add(array(new Error($errorText, 'ACCESS_DENIED')));
				}
			}

			$this->task->setLastFileId($fileId);

			// fix interval task state
			if ($countFileErasure >= self::STATUS_FIX_INTERVAL)
			{
				$countFileErasure = 0;

				if ($this->task->hasUserCanceled())
				{
					$subTaskDone = false;
					break;
				}

				$this->task->fixState();

				// count statistic for progress bar
				self::countWorker($this->task->getOwnerId());
			}

			if (!$this->checkTimeEnd())
			{
				$subTaskDone = false;
				break;
			}
		}

		return $subTaskDone;
	}


	/**
	 * Deletes files in trashcan.
	 * @param Volume\IVolumeIndicator $indicator Ignited indicator for file list filter.
	 * @return boolean
	 */
	public function deleteTrashcanByFilter(Volume\IVolumeIndicator $indicator)
	{
		$subTaskDone = true;

		$filter = array(
			'!DELETED_TYPE' => \Bitrix\Disk\Internals\ObjectTable::DELETED_TYPE_NONE
		);
		if ($this->task->getLastFileId() > 0)
		{
			$filter['>=ID'] = $this->task->getLastFileId();
		}

		$indicator->setLimit(self::MAX_FILE_PER_INTERACTION);

		$fileList = $indicator->getCorrespondingFileList($filter);

		$this->task->setIterationFileCount($fileList->getSelectedRowsCount());

		$countFileErasure = 0;

		foreach ($fileList as $row)
		{
			$fileId = $row['ID'];
			$file = \Bitrix\Disk\File::getById($fileId);
			if ($file instanceof \Bitrix\Disk\File)
			{
				$securityContext = $this->getSecurityContext($this->owner, $file);
				if($file->canDelete($securityContext))
				{
					$this->deleteFile($file);
					$countFileErasure ++;
				}
				else
				{
					$errorText = "Access denied to file #$fileId";
					$this->task->increaseFailCount();
					$this->task->setLastError($errorText);
					$this->errorCollection->add(array(new Error($errorText, 'ACCESS_DENIED')));
				}
			}

			$this->task->setLastFileId($fileId);

			// fix interval task state
			if ($countFileErasure >= self::STATUS_FIX_INTERVAL)
			{
				$countFileErasure = 0;

				if ($this->task->hasUserCanceled())
				{
					$subTaskDone = false;
					break;
				}

				$this->task->fixState();

				// count statistic for progress bar
				self::countWorker($this->task->getOwnerId());
			}

			if (!$this->checkTimeEnd())
			{
				$subTaskDone = false;
				break;
			}
		}

		$indicator->setLimit(self::MAX_FOLDER_PER_INTERACTION);

		$folderList = $indicator->getCorrespondingFolderList(array('!DELETED_TYPE' => \Bitrix\Disk\Internals\ObjectTable::DELETED_TYPE_NONE));

		foreach ($folderList as $row)
		{
			$folder = \Bitrix\Disk\Folder::getById($row['ID']);
			if ($folder instanceof \Bitrix\Disk\Folder)
			{
				$this->deleteFolder($folder);
				$countFileErasure ++;
			}

			// fix interval task state
			if ($countFileErasure >= self::STATUS_FIX_INTERVAL)
			{
				$countFileErasure = 0;

				if ($this->task->hasUserCanceled())
				{
					$subTaskDone = false;
					break;
				}

				$this->task->fixState();

				// count statistic for progress bar
				self::countWorker($this->task->getOwnerId());
			}

			if (!$this->checkTimeEnd())
			{
				$subTaskDone = false;
				break;
			}
		}

		return $subTaskDone;
	}


	/**
	 * Deletes unused file versions.
	 * @param Volume\IVolumeIndicator $indicator Ignited indicator for file list filter.
	 * @return boolean
	 */
	public function deleteUnnecessaryVersionByFilter(Volume\IVolumeIndicator $indicator)
	{
		$subTaskDone = true;

		$filter = array();
		if ($this->task->getLastFileId() > 0)
		{
			$filter['>=FILE_ID'] = $this->task->getLastFileId();
		}

		$indicator->setLimit(self::MAX_FILE_PER_INTERACTION);

		$versionList = $indicator->getCorrespondingUnnecessaryVersionList($filter);

		$this->task->setIterationFileCount($versionList->getSelectedRowsCount());

		$versionsPerFile = array();
		foreach ($versionList as $row)
		{
			$fileId = $row['FILE_ID'];
			$versionId = $row['VERSION_ID'];
			if (!isset($versionsPerFile[$fileId]))
			{
				$versionsPerFile[$fileId] = array();
			}
			$versionsPerFile[$fileId][] = $versionId;
		}
		unset($row, $fileId, $versionId, $versionList);


		$countFileErasure = 0;

		foreach ($versionsPerFile as $fileId => $versionIds)
		{
			$file = \Bitrix\Disk\File::getById($fileId);

			if ($file instanceof \Bitrix\Disk\File)
			{
				$securityContext = $this->getSecurityContext($this->owner, $file);
				if($file->canDelete($securityContext))
				{
					$this->deleteFileUnnecessaryVersion($file, array('=ID' => $versionIds));
					$countFileErasure++;
				}
				else
				{
					$errorText = "Access denied to file #$fileId";
					$this->task->increaseFailCount();
					$this->task->setLastError($errorText);
					$this->errorCollection->add(array(new Error($errorText, 'ACCESS_DENIED')));
				}
			}

			$this->task->setLastFileId($fileId);

			// fix interval task state
			if ($countFileErasure >= self::STATUS_FIX_INTERVAL)
			{
				$countFileErasure = 0;

				if ($this->task->hasUserCanceled())
				{
					$subTaskDone = false;
					break;
				}

				$this->task->fixState();

				// count statistic for progress bar
				self::countWorker($this->task->getOwnerId());
			}

			if (!$this->checkTimeEnd())
			{
				$subTaskDone = false;
				break;
			}
		}

		return $subTaskDone;
	}


	/**
	 * Returns disk security context.
	 * @param \Bitrix\Disk\User $user Task owner.
	 * @param \Bitrix\Disk\BaseObject $object File or folder.
	 * @return \Bitrix\Disk\Security\SecurityContext
	 */
	private function getSecurityContext($user, $object)
	{
		static $securityContextCache = array();

		$userId = $user->getId();
		$storageId = $object->getStorageId();

		if (!($securityContextCache[$userId][$storageId] instanceof \Bitrix\Disk\Security\SecurityContext))
		{
			if (!isset($securityContextCache[$userId]))
			{
				$securityContextCache[$userId] = array();
			}

			if ($user->isAdmin())
			{
				$securityContextCache[$userId][$storageId] = new \Bitrix\Disk\Security\FakeSecurityContext($userId);
			}
			else
			{
				$securityContextCache[$userId][$storageId] = $object->getStorage()->getSecurityContext($userId);
			}
		}

		return $securityContextCache[$userId][$storageId];
	}


	/**
	 * Deletes file.
	 * @param \Bitrix\Disk\File $file File to drop.
	 * @return boolean
	 */
	public function deleteFile(\Bitrix\Disk\File $file)
	{
		$logData = $this->task->collectLogData($file);

		if(!$file->delete($this->task->getOwnerId()))
		{
			$this->errorCollection->add($file->getErrors());
			$this->task->increaseFailCount();
			$this->task->setLastError($file->getErrors());
			return false;
		}

		$this->task->log($logData, __FUNCTION__);

		$this->task->increaseDroppedFileCount();

		return true;
	}


	/**
	 * Deletes file unnecessary versions.
	 * @param \Bitrix\Disk\File $file File to purify.
	 * @param array $additionalFilter Additional filter for vertion selection.
	 * @return boolean
	 */
	public function deleteFileUnnecessaryVersion(\Bitrix\Disk\File $file, $additionalFilter = array())
	{
		$subTaskDone = true;

		$filter = array(
			'=OBJECT_ID' => $file->getId(),
		);
		if (count($additionalFilter) > 0)
		{
			$filter = array_merge($filter, $additionalFilter);
		}

		$versionList = \Bitrix\Disk\Version::getList(array(
			'filter' => $filter,
			'select' => array('ID')
		));
		foreach ($versionList as $row)
		{
			$versionId = $row['ID'];

			/** @var \Bitrix\Disk\Version $version */
			$version = \Bitrix\Disk\Version::getById($versionId);
			if(!$version instanceof \Bitrix\Disk\Version)
			{
				continue;
			}

			// is a head
			//if ($version->isHead())
			if ($version->getFileId() == $file->getFileId())
			{
				continue;
			}

			// attached_object
			$attachedList = \Bitrix\Disk\AttachedObject::getList(array(
				'filter' => array(
					'=OBJECT_ID' => $file->getId(),
					'=VERSION_ID' => $version->getId(),
				),
				'select' => array('ID'),
				'limit' => 1,
			));
			if($attachedList->getSelectedRowsCount() > 0)
			{
				continue;
			}

			// external_link
			$externalLinkList = \Bitrix\Disk\ExternalLink::getList(array(
				'filter' => array(
					'=OBJECT_ID' => $file->getId(),
					'=VERSION_ID' => $version->getId(),
					'!TYPE' => \Bitrix\Disk\ExternalLink::TYPE_AUTO,
				),
				'select' => array('ID'),
				'limit' => 1,
			));
			if($externalLinkList->getSelectedRowsCount() > 0)
			{
				continue;
			}

			$logData = $this->task->collectLogData($version);

			// drop
			if(!$version->delete($this->task->getOwnerId()))
			{
				$this->errorCollection->add($version->getErrors());
				$this->task->increaseFailCount();
				$this->task->setLastError($version->getErrors());
			}
			else
			{
				$this->task->log($logData, __FUNCTION__);
				$this->task->increaseDroppedVersionCount();
			}

			if (!$this->checkTimeEnd())
			{
				$subTaskDone = false;
				break;
			}
		}

		return $subTaskDone;
	}


	/**
	 * Deletes folder.
	 * @param \Bitrix\Disk\Folder $folder Folder to drop.
	 * @param boolean $emptyOnly Just delete folder's content.
	 * @return boolean
	 */
	public function deleteFolder(\Bitrix\Disk\Folder $folder, $emptyOnly = false)
	{
		$subTaskDone = true;

		if (!$emptyOnly && !$this->isAllowDeleteFolder($folder))
		{
			$errorText = 'Access denied to folder #'. $folder->getId();
			$this->errorCollection->add(array(new Error($errorText, 'ACCESS_DENIED')));
			//$this->task->increaseFailCount();
			$this->task->setLastError($errorText);

			return false;
		}

		$countFileErasure = 0;

		$objectList = \Bitrix\Disk\Internals\ObjectTable::getList(array(
			'filter' => array(
				'=PATH_CHILD.PARENT_ID' => $folder->getId(),
			),
			'order' => array(
				'PATH_CHILD.DEPTH_LEVEL' => 'DESC',
				'ID' => 'ASC'
			),
			'limit' => self::MAX_FOLDER_PER_INTERACTION,
		));

		$this->task->setIterationFileCount($objectList->getSelectedRowsCount());

		foreach ($objectList as $row)
		{
			if ($row['ID'] == $folder->getId())
			{
				continue;
			}

			$object = \Bitrix\Disk\BaseObject::buildFromArray($row);

			/** @var Folder|File $object */
			if($object instanceof \Bitrix\Disk\Folder)
			{
				/** @var \Bitrix\Disk\File $object */
				$securityContext = $this->getSecurityContext($this->owner, $object);
				if($object->canDelete($securityContext))
				{
					if ($this->isAllowDeleteFolder($object))
					{
						$logData = $this->task->collectLogData($object);

						/** @var \Bitrix\Disk\Folder $object */
						if (!$object->deleteTree($this->task->getOwnerId()))
						{
							$this->errorCollection->add($object->getErrors());
							//$this->task->increaseFailCount();
							$this->task->setLastError($object->getErrors());

							$subTaskDone = false;
						}
						else
						{
							$this->task->log($logData, __FUNCTION__);
							$this->task->increaseDroppedFolderCount();
						}
					}
				}
				else
				{
					$errorText = 'Access denied to folder #'. $object->getId();
					$this->errorCollection->add(array(new Error($errorText, 'ACCESS_DENIED')));
					//$this->task->increaseFailCount();
					$this->task->setLastError($errorText);
				}
			}
			elseif($object instanceof \Bitrix\Disk\File)
			{
				/** @var \Bitrix\Disk\File $object */
				$securityContext = $this->getSecurityContext($this->owner, $object);
				if($object->canDelete($securityContext))
				{
					$subTaskDone = $this->deleteFile($object);
				}
				else
				{
					$errorText = 'Access denied to file #'. $object->getId();
					$this->errorCollection->add(array(new Error($errorText, 'ACCESS_DENIED')));
					$this->task->increaseFailCount();
					$this->task->setLastError($errorText);
				}
			}

			// fix interval task state
			$countFileErasure ++;
			if ($countFileErasure >= self::STATUS_FIX_INTERVAL)
			{
				$countFileErasure = 0;

				if ($this->task->hasUserCanceled())
				{
					$subTaskDone = false;
					break;
				}

				$this->task->fixState();

				// count statistic for progress bar
				self::countWorker($this->task->getOwnerId());

			}

			if (!$this->checkTimeEnd())
			{
				$subTaskDone = false;
				break;
			}

		}

		if ($subTaskDone)
		{
			if ($emptyOnly === false)
			{
				$logData = $this->task->collectLogData($folder);

				if (!$folder->deleteTree($this->task->getOwnerId()))
				{
					$this->errorCollection->add($folder->getErrors());
					//$this->task->increaseFailCount();
					$this->task->setLastError($folder->getErrors());

					return false;
				}

				$this->task->log($logData, __FUNCTION__);
				$this->task->increaseDroppedFolderCount();
			}
		}

		return $subTaskDone;
	}


	/**
	 * Check ability to drop folder.
	 * @param \Bitrix\Disk\Folder $folder Folder to drop.
	 * @return boolean
	 */
	public function isAllowDeleteFolder(\Bitrix\Disk\Folder $folder)
	{
		$allowDrop = true;

		/** @var \Bitrix\Disk\Volume\IDeleteConstraint[] $constraintList */
		static $constraintList;
		if (empty($constraintList))
		{
			$constraintList = array();

			// full list available indicators
			$constraintIdList = \Bitrix\Disk\Volume\Base::listConstraint();
			foreach ($constraintIdList as $indicatorId => $indicatorIdClass)
			{
				$constraintList[$indicatorId] = new $indicatorIdClass();
			}
		}

		foreach ($constraintList as $indicatorId => $indicator)
		{
			if (!$indicator->isAllowDeleteFolder($folder))
			{
				$allowDrop = false;
			}
		}

		return $allowDrop;
	}

	/**
	 * Repeats measurement for indicator.
	 * @param Volume\IVolumeIndicator $indicator Ignited indicator for measure.
	 * @return boolean
	 */
	public static function repeatMeasure(Volume\IVolumeIndicator $indicator)
	{
		$indicator->resetMeasurementResult();
		$indicator->measure();

		if ($indicator->getFilterValue('STORAGE_ID') > 0)
		{
			if ($indicator::className() != Volume\Storage\Storage::className())
			{
				/** @var \Bitrix\Disk\Volume\IVolumeIndicator $storageIndicator */
				$storageIndicator = new Volume\Storage\Storage();
				$storageIndicator->setOwner($indicator->getOwner());

				$storageIndicator->addFilter('STORAGE_ID', $indicator->getFilterValue('STORAGE_ID'));
				$result = $storageIndicator->getMeasurementResult();
				if ($row = $result->fetch())
				{
					$storageIndicator->setFilterId($row['ID']);
				}
				$storageIndicator->measure();
			}

			if ($indicator::className() != Volume\Storage\TrashCan::className())
			{
				/** @var \Bitrix\Disk\Volume\IVolumeIndicator $trashCanIndicator */
				$trashCanIndicator = new Volume\Storage\TrashCan();
				$trashCanIndicator->setOwner($indicator->getOwner());

				$trashCanIndicator->addFilter('STORAGE_ID', $indicator->getFilterValue('STORAGE_ID'));
				$result = $trashCanIndicator->getMeasurementResult();
				if ($row = $result->fetch())
				{
					$trashCanIndicator->setFilterId($row['ID']);
				}
				$trashCanIndicator->measure();
			}
		}

		return true;
	}


	/**
	 * Sets start up time.
	 * @return void
	 */
	public function startTimer()
	{
		$this->timer->startTimer();
	}

	/**
	 * Checks timer for time limitation/
	 * @return bool
	 */
	public function checkTimeEnd()
	{
		return $this->timer->checkTimeEnd();
	}

	/**
	 * Tells true if time limit reached.
	 * @return boolean
	 */
	public function hasTimeLimitReached()
	{
		return $this->timer->hasTimeLimitReached();
	}

	/**
	 * Sets limitation time in seconds.
	 * @param int $timeLimit Timeout in seconds.
	 * @return void
	 */
	public function setTimeLimit($timeLimit)
	{
		$this->timer->setTimeLimit($timeLimit);
	}

	/**
	 * Gets limitation time in seconds.
	 * @return int
	 */
	public function getTimeLimit()
	{
		return $this->timer->getTimeLimit();
	}

	/**
	 * Gets step identification.
	 * @return string|null
	 */
	public function getStepId()
	{
		return $this->timer->getStepId();
	}

	/**
	 * Sets step identification.
	 * @param string $stepId Step id.
	 * @return void
	 */
	public function setStepId($stepId)
	{
		$this->timer->setStepId($stepId);
	}

	/**
	 * Gets dropped file count.
	 * @return int
	 */
	public function getDroppedFileCount()
	{
		return $this->task->getDroppedFileCount();
	}

	/**
	 * Gets dropped version count.
	 * @return int
	 */
	public function getDroppedVersionCount()
	{
		return $this->task->getDroppedVersionCount();
	}

	/**
	 * Gets dropped folder count.
	 * @return int
	 */
	public function getDroppedFolderCount()
	{
		return $this->task->getDroppedFolderCount();
	}

	/**
	 * @return Error[]
	 */
	public function getErrors()
	{
		return $this->errorCollection->toArray();
	}

	/**
	 * @return boolean
	 */
	public function hasErrors()
	{
		return $this->errorCollection->hasErrors();
	}

	/**
	 * Returns array of errors with the necessary code.
	 * @param string $code Code of error.
	 * @return Error[]
	 */
	public function getErrorsByCode($code)
	{
		return $this->errorCollection->getErrorsByCode($code);
	}

	/**
	 * Returns an error with the necessary code.
	 * @param string|int $code The code of the error.
	 * @return \Bitrix\Main\Error|null
	 */
	public function getErrorByCode($code)
	{
		return $this->errorCollection->getErrorByCode($code);
	}


	/**
	 * Set up information showing at stepper progress bar.
	 * @param int $ownerId Whom will mark as deleted by.
	 * @return array|null
	 */
	public static function getProgressInfo($ownerId)
	{
		$optionSerialized = \Bitrix\Main\Config\Option::get(
			'main.stepper.disk',
			Volume\Cleaner::className(). $ownerId,
			''
		);
		if (!empty($optionSerialized))
		{
			return unserialize($optionSerialized);
		}

		return null;
	}

	/**
	 * Set up information showing at stepper progress bar.
	 * @param int $ownerId Whom will mark as deleted by.
	 * @param int $totalFilesToDrop  Total files to drop.
	 * @param int $droppedFilesCount Dropped files count.
	 * @param int $failCount Failed deletion count.
	 * @return void
	 */
	public static function setProgressInfo($ownerId, $totalFilesToDrop, $droppedFilesCount = 0, $failCount = 0)
	{
		if ($totalFilesToDrop  > 0)
		{
			$option = Volume\Cleaner::getProgressInfo($ownerId);
			if (!empty($option) && $option['count'] > 0)
			{
				$prevTotalFilesToDrop = $option['count'];
				//$prevDroppedFilesCount = $option['steps'];

				// If total count decreases mean some agents finished its work.
				if ($prevTotalFilesToDrop > $totalFilesToDrop)
				{
					$droppedFilesCount = ($prevTotalFilesToDrop - $totalFilesToDrop) + $droppedFilesCount;
					$totalFilesToDrop = $prevTotalFilesToDrop;
				}
			}

			\Bitrix\Main\Config\Option::set(
				'main.stepper.disk',
				self::className().$ownerId,
				serialize(array('steps' => ($droppedFilesCount + $failCount), 'count' => $totalFilesToDrop))
			);
		}
		else
		{
			self::clearProgressInfo($ownerId);
		}
	}

	/**
	 * Remove stepper progress bar.
	 * @param int $ownerId Whom will mark as deleted by.
	 * @return void
	 */
	public static function clearProgressInfo($ownerId)
	{
		\Bitrix\Main\Config\Option::delete(
			'main.stepper.disk',
			array('name' => self::className(). $ownerId)
		);
	}
}

