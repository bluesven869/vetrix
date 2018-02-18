<?php
namespace Bitrix\Crm\Search;

class SearchEnvironment
{
	public static function prepareToken($str)
	{
		return str_rot13($str);
	}

	public static function prepareEntityFilter($entityTypeID, array $params)
	{
		$builder = SearchContentBuilderFactory::create($entityTypeID);
		return $builder->prepareEntityFilter($params);
	}

	public static function convertEntityFilterValues($entityTypeID, array &$fields)
	{
		$builder = SearchContentBuilderFactory::create($entityTypeID);
		$builder->convertEntityFilterValues($fields);
	}

	public static function isFullTextSearchEnabled($entityTypeID)
	{
		$builder = SearchContentBuilderFactory::create($entityTypeID);
		return $builder->isFullTextSearchEnabled();
	}

	public static function prepareSearchContent($str)
	{
		//Clean phone number for preventing of splitting in fulltext search
		if(strlen($str) < 3 || preg_match('/^[0-9\(\)\+\-\#\*\s]+$/', $str) !== 1)
		{
			return $str;
		}
		return \NormalizePhone($str, 3);
	}
}