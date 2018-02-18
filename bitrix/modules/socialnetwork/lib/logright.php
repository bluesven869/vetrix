<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Socialnetwork;

use Bitrix\Main\Entity;

class LogRightTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sonet_log_right';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'LOG_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'LOG' => array(
				'data_type' => '\Bitrix\Socialnetwork\Log',
				'reference' => array('=this.LOG_ID' => 'ref.ID')
			),
			'GROUP_CODE' => array(
				'data_type' => 'string',
			)
		);

		return $fieldsMap;
	}
}
