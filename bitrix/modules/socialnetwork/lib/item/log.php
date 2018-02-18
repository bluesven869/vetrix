<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Socialnetwork\Item;

use Bitrix\Socialnetwork\LogRightTable;
use Bitrix\Socialnetwork\LogTable;

class Log
{
	public static function setLimitedView($params = array())
	{
		if (!is_array($params))
		{
			$params = array();
		}

		$logId = (
			!empty($params['logId'])
			&& intval($params['logId']) > 0
				? intval($params['logId'])
				: 0
		);

		if ($logId <= 0)
		{
			return false;
		}

		$res = LogTable::getList(array(
			'filter' => array(
				'ID' => $logId
			),
			'select' => array('USER_ID', 'PARAMS')
		));

		$logEntry = $res->fetch();
		if (empty($logEntry))
		{
			return false;
		}

		$status = (
			isset($params['status'])
			&& $params['status'] === true
		);

		if ($status)
		{
			$logEntryParams = array();

			if (
				!empty($logEntry['PARAMS'])
				&& ($logEntryParams = unserialize($logEntry['PARAMS']))
				&& !empty($logEntryParams['RIGHTS'])
			)
			{
				return false;
			}

			$logRightsList = array();

			$res = LogRightTable::getList(array(
				'filter' => array(
					'LOG_ID' => $logId
				),
				'select' => array('GROUP_CODE')
			));
			while($right = $res->fetch())
			{
				$logRightsList[] = $right['GROUP_CODE'];
			}

			LogTable::update($logId, array(
				'PARAMS' => serialize(array_merge($logEntryParams, array('RIGHTS' => $logRightsList)))
			));

			\CSocNetLogRights::deleteByLogID($logId);
			if (intval($logEntry['USER_ID']) > 0)
			{
				\CSocNetLogRights::add($logId, array('SA', 'U'.$logEntry['USER_ID']));
			}
		}
		else
		{
			if (
				empty($logEntry['PARAMS'])
				|| !($logEntryParams = unserialize($logEntry['PARAMS']))
				|| empty($logEntryParams['RIGHTS'])
			)
			{
				return false;
			}

			$logRightsOriginal = $logEntryParams['RIGHTS'];

			unset($logEntryParams['RIGHTS']);

			LogTable::update($logId, array(
				'PARAMS' => (empty($logEntryParams) ? '' : serialize($logEntryParams))
			));

			\CSocNetLogRights::deleteByLogID($logId);
			\CSocNetLogRights::add($logId, $logRightsOriginal);
		}

		return true;
	}
}
