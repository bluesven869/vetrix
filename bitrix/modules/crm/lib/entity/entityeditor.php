<?php
namespace Bitrix\Crm\Entity;

class EntityEditor
{
	public static function internalizeMultifieldData(array $data, array &$entityFields)
	{
		foreach($data as $typeName => $items)
		{
			if(!isset($entityFields[$typeName]))
			{
				$entityFields[$typeName] = array();
			}

			foreach($items as $itemID => $item)
			{
				$entityFields[$typeName][] = array_merge(array('ID' => $itemID), $item);
			}
		}
	}

	/**
	 * @param \Bitrix\Crm\Conversion\EntityConversionWizard $wizard
	 * @param int $entityTypeID
	 * @param array $entityFields
	 * @param array $userFields
	 */
	public static function prepareConvesionMap($wizard, $entityTypeID, array &$entityFields, array &$userFields)
	{
		$mappedFields = $wizard->mapEntityFields($entityTypeID, array('ENABLE_FILES' => false));
		foreach($mappedFields as $k => $v)
		{
			if(strpos($k, 'UF_CRM') === 0)
			{
				$userFields[$k] = $v;
			}
			elseif($k === 'FM')
			{
				self::internalizeMultifieldData($v, $entityFields);
			}
			else
			{
				$entityFields[$k] = $v;
			}
		}
	}
}