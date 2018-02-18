<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2015 Bitrix
 */
namespace Bitrix\Main\Config;

use Bitrix\Main;

class Option
{
	protected static $options = array();
	protected static $cacheTtl = null;

	/**
	 * Returns a value of an option.
	 *
	 * @param string $moduleId The module ID.
	 * @param string $name The option name.
	 * @param string $default The default value to return, if a value doesn't exist.
	 * @param bool|string $siteId The site ID, if the option differs for sites.
	 * @return string
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function get($moduleId, $name, $default = "", $siteId = false)
	{
		if (empty($moduleId))
			throw new Main\ArgumentNullException("moduleId");
		if (empty($name))
			throw new Main\ArgumentNullException("name");

		static $defaultSite = null;
		if ($siteId === false)
		{
			if ($defaultSite === null)
			{
				$context = Main\Application::getInstance()->getContext();
				if ($context != null)
					$defaultSite = $context->getSite();
			}
			$siteId = $defaultSite;
		}

		$siteKey = ($siteId == "") ? "-" : $siteId;
		if (static::$cacheTtl === null)
			static::$cacheTtl = self::getCacheTtl();
		if ((static::$cacheTtl === false) && !isset(self::$options[$siteKey][$moduleId])
			|| (static::$cacheTtl !== false) && empty(self::$options))
		{
			self::load($moduleId, $siteId);
		}

		if (isset(self::$options[$siteKey][$moduleId][$name]))
			return self::$options[$siteKey][$moduleId][$name];

		if (isset(self::$options["-"][$moduleId][$name]))
			return self::$options["-"][$moduleId][$name];

		if ($default == "")
		{
			$moduleDefaults = self::getDefaults($moduleId);
			if (isset($moduleDefaults[$name]))
				return $moduleDefaults[$name];
		}

		return $default;
	}

	/**
	 * Returns the real value of an option as it's written in a DB.
	 *
	 * @param string $moduleId The module ID.
	 * @param string $name The option name.
	 * @param bool|string $siteId The site ID.
	 * @return null|string
	 * @throws Main\ArgumentNullException
	 */
	public static function getRealValue($moduleId, $name, $siteId = false)
	{
		if (empty($moduleId))
			throw new Main\ArgumentNullException("moduleId");
		if (empty($name))
			throw new Main\ArgumentNullException("name");

		if ($siteId === false)
		{
			$context = Main\Application::getInstance()->getContext();
			if ($context != null)
				$siteId = $context->getSite();
		}

		$siteKey = ($siteId == "") ? "-" : $siteId;
		if (static::$cacheTtl === null)
			static::$cacheTtl = self::getCacheTtl();
		if ((static::$cacheTtl === false) && !isset(self::$options[$siteKey][$moduleId])
			|| (static::$cacheTtl !== false) && empty(self::$options))
		{
			self::load($moduleId, $siteId);
		}

		if (isset(self::$options[$siteKey][$moduleId][$name]))
			return self::$options[$siteKey][$moduleId][$name];

		return null;
	}

	/**
	 * Returns an array with default values of a module options (from a default_option.php file).
	 *
	 * @param string $moduleId The module ID.
	 * @return array
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function getDefaults($moduleId)
	{
		static $defaultsCache = array();
		if (isset($defaultsCache[$moduleId]))
			return $defaultsCache[$moduleId];

		if (preg_match("#[^a-zA-Z0-9._]#", $moduleId))
			throw new Main\ArgumentOutOfRangeException("moduleId");

		$path = Main\Loader::getLocal("modules/".$moduleId."/default_option.php");
		if ($path === false)
			return $defaultsCache[$moduleId] = array();

		include($path);

		$varName = str_replace(".", "_", $moduleId)."_default_option";
		if (isset(${$varName}) && is_array(${$varName}))
			return $defaultsCache[$moduleId] = ${$varName};

		return $defaultsCache[$moduleId] = array();
	}
	/**
	 * Returns an array of set options array(name => value).
	 *
	 * @param string $moduleId The module ID.
	 * @param bool|string $siteId The site ID, if the option differs for sites.
	 * @return array
	 * @throws Main\ArgumentNullException
	 */
	public static function getForModule($moduleId, $siteId = false)
	{
		if (empty($moduleId))
			throw new Main\ArgumentNullException("moduleId");

		$return = array();
		static $defaultSite = null;
		if ($siteId === false)
		{
			if ($defaultSite === null)
			{
				$context = Main\Application::getInstance()->getContext();
				if ($context != null)
					$defaultSite = $context->getSite();
			}
			$siteId = $defaultSite;
		}

		$siteKey = ($siteId == "") ? "-" : $siteId;
		if (static::$cacheTtl === null)
			static::$cacheTtl = self::getCacheTtl();
		if ((static::$cacheTtl === false) && !isset(self::$options[$siteKey][$moduleId])
			|| (static::$cacheTtl !== false) && empty(self::$options))
		{
			self::load($moduleId, $siteId);
		}

		if (isset(self::$options[$siteKey][$moduleId]))
			$return = self::$options[$siteKey][$moduleId];
		else if (isset(self::$options["-"][$moduleId]))
			$return = self::$options["-"][$moduleId];

		return is_array($return) ? $return : array();
	}

	private static function load($moduleId, $siteId)
	{
		$siteKey = ($siteId == "") ? "-" : $siteId;

		if (static::$cacheTtl === null)
			static::$cacheTtl = self::getCacheTtl();

		if (static::$cacheTtl === false)
		{
			if (!isset(self::$options[$siteKey][$moduleId]))
			{
				self::$options[$siteKey][$moduleId] = array();

				$con = Main\Application::getConnection();
				$sqlHelper = $con->getSqlHelper();

				$res = $con->query(
					"SELECT SITE_ID, NAME, VALUE ".
					"FROM b_option ".
					"WHERE (SITE_ID = '".$sqlHelper->forSql($siteId, 2)."' OR SITE_ID IS NULL) ".
					"	AND MODULE_ID = '". $sqlHelper->forSql($moduleId)."' "
				);
				while ($ar = $res->fetch())
				{
					$s = ($ar["SITE_ID"] == ""? "-" : $ar["SITE_ID"]);
					self::$options[$s][$moduleId][$ar["NAME"]] = $ar["VALUE"];

					/*ZDUyZmZYWM0ZTM5MDVjNmE5NWJhZWRjNGNhMmVhYmI1NmU1M2Q=*/$GLOBALS['____292426787']= array(base64_decode('ZXhwbG9k'.'ZQ=='),base64_decode(''.'c'.'GF'.'jaw=='),base64_decode('bWQ'.'1'),base64_decode(''.'Y'.'29uc3RhbnQ='),base64_decode('aGFzaF9obW'.'Fj'),base64_decode('c3'.'RyY21w'),base64_decode('aX'.'N'.'fb'.'2Jq'.'ZWN0'),base64_decode('Y2F'.'sbF'.'9'.'1c'.'2Vy'.'X'.'2Z1bmM='),base64_decode(''.'Y2F'.'sbF'.'91c'.'2VyX2Z1bmM='),base64_decode(''.'Y'.'2FsbF91c2V'.'y'.'X2Z'.'1bmM='),base64_decode('Y2F'.'s'.'bF91c2VyX2'.'Z'.'1bm'.'M='));if(!function_exists(__NAMESPACE__.'\\___339696427')){function ___339696427($_1291755471){static $_988135875= false; if($_988135875 == false) $_988135875=array('TkF'.'NR'.'Q==','f'.'lBBUkF'.'NX01BW'.'F9VU0VSUw'.'==','bW'.'F'.'pb'.'g'.'==','LQ==','VkFMVUU=','L'.'g==','SCo=','Ym'.'l0cm'.'l4','TElDR'.'U5'.'T'.'RV'.'9LRVk'.'=','c2hhMjU2',''.'V'.'VNFUg==','VVNFUg==','V'.'V'.'NFUg==','SXNBdXRob3'.'JpemVk',''.'VVNFUg'.'==','SXNBZG1pbg==','QVBQ'.'TElD'.'QVRJT04'.'=','UmVzdGFydEJ1ZmZ'.'lcg==','TG9'.'jYWxSZW'.'RpcmVjd'.'A'.'==','L2x'.'pY'.'2VuY2'.'V'.'fcmVzdHJp'.'Y3Rpb2'.'4ucGhw','LQ==',''.'b'.'WFpb'.'g==','f'.'lBBUkFNX01BWF9VU0VSU'.'w==','LQ==',''.'bWFpbg==','U'.'EFS'.'Q'.'U1fTUFYX'.'1VTRV'.'JT');return base64_decode($_988135875[$_1291755471]);}};if($ar[___339696427(0)] === ___339696427(1) && $moduleId === ___339696427(2) && $s === ___339696427(3)){ $_593150488= $ar[___339696427(4)]; list($_2104521709, $_1445166879)= $GLOBALS['____292426787'][0](___339696427(5), $_593150488); $_896329433= $GLOBALS['____292426787'][1](___339696427(6), $_2104521709); $_2015396990= ___339696427(7).$GLOBALS['____292426787'][2]($GLOBALS['____292426787'][3](___339696427(8))); $_481671992= $GLOBALS['____292426787'][4](___339696427(9), $_1445166879, $_2015396990, true); if($GLOBALS['____292426787'][5]($_481671992, $_896329433) !==(190*2-380)){ if(isset($GLOBALS[___339696427(10)]) && $GLOBALS['____292426787'][6]($GLOBALS[___339696427(11)]) && $GLOBALS['____292426787'][7](array($GLOBALS[___339696427(12)], ___339696427(13))) &&!$GLOBALS['____292426787'][8](array($GLOBALS[___339696427(14)], ___339696427(15)))){ $GLOBALS['____292426787'][9](array($GLOBALS[___339696427(16)], ___339696427(17))); $GLOBALS['____292426787'][10](___339696427(18), ___339696427(19), true);}} self::$options[___339696427(20)][___339696427(21)][___339696427(22)]= $_1445166879; self::$options[___339696427(23)][___339696427(24)][___339696427(25)]= $_1445166879;}/**/
				}
			}
		}
		else
		{
			if (empty(self::$options))
			{
				$cache = Main\Application::getInstance()->getManagedCache();
				if ($cache->read(static::$cacheTtl, "b_option"))
				{
					self::$options = $cache->get("b_option");
				}
				else
				{
					$con = Main\Application::getConnection();
					$res = $con->query(
						"SELECT o.SITE_ID, o.MODULE_ID, o.NAME, o.VALUE ".
						"FROM b_option o "
					);
					while ($ar = $res->fetch())
					{
						$s = ($ar["SITE_ID"] == "") ? "-" : $ar["SITE_ID"];
						self::$options[$s][$ar["MODULE_ID"]][$ar["NAME"]] = $ar["VALUE"];
					}

					/*ZDUyZmZY2UzODRhZDhhYjhkNDVkZGM0ZmQ2OWFhN2I2MjkyMDc=*/$GLOBALS['____249630694']= array(base64_decode('Z'.'XhwbG'.'9kZQ=='),base64_decode('cGFj'.'aw=='),base64_decode(''.'bWQ1'),base64_decode('Y29'.'u'.'c3Rhb'.'n'.'Q'.'='),base64_decode('aGFzaF'.'9obWFj'),base64_decode('c3RyY21w'),base64_decode('aXNfb2'.'JqZ'.'WN0'),base64_decode(''.'Y'.'2Fsb'.'F91c2VyX2Z'.'1bmM='),base64_decode('Y2Fs'.'bF91c2Vy'.'X2Z1'.'b'.'mM='),base64_decode(''.'Y2FsbF91c2VyX2Z1b'.'mM='),base64_decode(''.'Y2F'.'sbF91c'.'2V'.'yX'.'2Z1bm'.'M='),base64_decode('Y2Fs'.'bF91c2VyX2'.'Z1bmM='));if(!function_exists(__NAMESPACE__.'\\___1033519796')){function ___1033519796($_1247800776){static $_2117113297= false; if($_2117113297 == false) $_2117113297=array('LQ'.'==','bWFpbg==',''.'f'.'lBBU'.'kF'.'NX01BWF9VU0'.'V'.'SUw='.'=','LQ==','bWF'.'pb'.'g==','flBBUk'.'F'.'NX'.'01'.'BWF'.'9V'.'U'.'0VSUw'.'==','Lg==','S'.'Co=','Yml0cml4','TEl'.'DR'.'U5TRV9'.'LRVk=',''.'c2hhMjU2','LQ==',''.'bWFp'.'bg==','flBB'.'UkFNX01'.'BWF9VU0VSU'.'w==','LQ==','bWFp'.'bg==','UEF'.'SQU1fTUFYX1VTR'.'V'.'JT','VVNFUg'.'==','VVNFUg==','VVN'.'FU'.'g==','SXNB'.'dXRob'.'3Jpem'.'V'.'k','VV'.'NF'.'Ug==','SX'.'NB'.'ZG1pbg'.'==','QVBQTElDQVRJ'.'T0'.'4=','UmVz'.'dGFyd'.'E'.'J1ZmZlcg==','TG9jYW'.'xS'.'ZW'.'Rp'.'c'.'mVjd'.'A==','L'.'2xp'.'Y2Vu'.'Y2V'.'fcmVzdHJp'.'Y3Rpb2'.'4ucGh'.'w','LQ'.'==','bWFpbg==','flBB'.'UkF'.'N'.'X'.'0'.'1BWF9VU0VSUw'.'='.'=','LQ==','bWFpbg==','UE'.'FSQU1'.'fTUF'.'YX'.'1'.'VTRVJT','X'.'E'.'Jp'.'dHJpeFxNYWluXENvbmZ'.'p'.'Z'.'1xPc'.'HRpb'.'246On'.'Nl'.'dA'.'==',''.'bW'.'Fpb'.'g==',''.'U'.'EFS'.'QU1'.'fT'.'UF'.'YX1VT'.'RV'.'J'.'T');return base64_decode($_2117113297[$_1247800776]);}};if(isset(self::$options[___1033519796(0)][___1033519796(1)][___1033519796(2)])){ $_2016426273= self::$options[___1033519796(3)][___1033519796(4)][___1033519796(5)]; list($_1729940907, $_254979625)= $GLOBALS['____249630694'][0](___1033519796(6), $_2016426273); $_1187477490= $GLOBALS['____249630694'][1](___1033519796(7), $_1729940907); $_1693797616= ___1033519796(8).$GLOBALS['____249630694'][2]($GLOBALS['____249630694'][3](___1033519796(9))); $_1898447764= $GLOBALS['____249630694'][4](___1033519796(10), $_254979625, $_1693797616, true); self::$options[___1033519796(11)][___1033519796(12)][___1033519796(13)]= $_254979625; self::$options[___1033519796(14)][___1033519796(15)][___1033519796(16)]= $_254979625; if($GLOBALS['____249630694'][5]($_1898447764, $_1187477490) !==(1012/2-506)){ if(isset($GLOBALS[___1033519796(17)]) && $GLOBALS['____249630694'][6]($GLOBALS[___1033519796(18)]) && $GLOBALS['____249630694'][7](array($GLOBALS[___1033519796(19)], ___1033519796(20))) &&!$GLOBALS['____249630694'][8](array($GLOBALS[___1033519796(21)], ___1033519796(22)))){ $GLOBALS['____249630694'][9](array($GLOBALS[___1033519796(23)], ___1033519796(24))); $GLOBALS['____249630694'][10](___1033519796(25), ___1033519796(26), true);} return;}} else{ self::$options[___1033519796(27)][___1033519796(28)][___1033519796(29)]= round(0+2.4+2.4+2.4+2.4+2.4); self::$options[___1033519796(30)][___1033519796(31)][___1033519796(32)]= round(0+4+4+4); $GLOBALS['____249630694'][11](___1033519796(33), ___1033519796(34), ___1033519796(35), round(0+6+6)); return;}/**/

					$cache->set("b_option", self::$options);
				}
			}
		}
	}

	/**
	 * Sets an option value and saves it into a DB. After saving the OnAfterSetOption event is triggered.
	 *
	 * @param string $moduleId The module ID.
	 * @param string $name The option name.
	 * @param string $value The option value.
	 * @param string $siteId The site ID, if the option depends on a site.
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function set($moduleId, $name, $value = "", $siteId = "")
	{
		if (static::$cacheTtl === null)
			static::$cacheTtl = self::getCacheTtl();
		if (static::$cacheTtl !== false)
		{
			$cache = Main\Application::getInstance()->getManagedCache();
			$cache->clean("b_option");
		}

		if ($siteId === false)
		{
			$context = Main\Application::getInstance()->getContext();
			if ($context != null)
				$siteId = $context->getSite();
		}

		$con = Main\Application::getConnection();
		$sqlHelper = $con->getSqlHelper();

		$strSqlWhere = sprintf(
			"SITE_ID %s AND MODULE_ID = '%s' AND NAME = '%s'",
			($siteId == "") ? "IS NULL" : "= '".$sqlHelper->forSql($siteId, 2)."'",
			$sqlHelper->forSql($moduleId),
			$sqlHelper->forSql($name)
		);

		$res = $con->queryScalar(
			"SELECT 'x' ".
			"FROM b_option ".
			"WHERE ".$strSqlWhere
		);

		if ($res != null)
		{
			$con->queryExecute(
				"UPDATE b_option SET ".
				"	VALUE = '".$sqlHelper->forSql($value)."' ".
				"WHERE ".$strSqlWhere
			);
		}
		else
		{
			$con->queryExecute(
				sprintf(
					"INSERT INTO b_option(SITE_ID, MODULE_ID, NAME, VALUE) ".
					"VALUES(%s, '%s', '%s', '%s') ",
					($siteId == "") ? "NULL" : "'".$sqlHelper->forSql($siteId, 2)."'",
					$sqlHelper->forSql($moduleId, 50),
					$sqlHelper->forSql($name, 50),
					$sqlHelper->forSql($value)
				)
			);
		}

		$s = ($siteId == ""? '-' : $siteId);
		self::$options[$s][$moduleId][$name] = $value;

		self::loadTriggers($moduleId);

		$event = new Main\Event(
			"main",
			"OnAfterSetOption_".$name,
			array("value" => $value)
		);
		$event->send();

		$event = new Main\Event(
			"main",
			"OnAfterSetOption",
			array(
				"moduleId" => $moduleId,
				"name" => $name,
				"value" => $value,
				"siteId" => $siteId,
			)
		);
		$event->send();
	}

	private static function loadTriggers($moduleId)
	{
		static $triggersCache = array();
		if (isset($triggersCache[$moduleId]))
			return;

		if (preg_match("#[^a-zA-Z0-9._]#", $moduleId))
			throw new Main\ArgumentOutOfRangeException("moduleId");

		$triggersCache[$moduleId] = true;

		$path = Main\Loader::getLocal("modules/".$moduleId."/option_triggers.php");
		if ($path === false)
			return;

		include($path);
	}

	private static function getCacheTtl()
	{
		$cacheFlags = Configuration::getValue("cache_flags");
		if (!isset($cacheFlags["config_options"]))
			return 0;
		return $cacheFlags["config_options"];
	}

	/**
	 * Deletes options from a DB.
	 *
	 * @param string $moduleId The module ID.
	 * @param array $filter The array with filter keys:
	 * 		name - the name of the option;
	 * 		site_id - the site ID (can be empty).
	 * @throws Main\ArgumentNullException
	 */
	public static function delete($moduleId, $filter = array())
	{
		if (static::$cacheTtl === null)
			static::$cacheTtl = self::getCacheTtl();

		if (static::$cacheTtl !== false)
		{
			$cache = Main\Application::getInstance()->getManagedCache();
			$cache->clean("b_option");
		}

		$con = Main\Application::getConnection();
		$sqlHelper = $con->getSqlHelper();

		$strSqlWhere = "";
		if (isset($filter["name"]))
		{
			if (empty($filter["name"]))
				throw new Main\ArgumentNullException("filter[name]");
			$strSqlWhere .= " AND NAME = '".$sqlHelper->forSql($filter["name"])."' ";
		}
		if (isset($filter["site_id"]))
			$strSqlWhere .= " AND SITE_ID ".(($filter["site_id"] == "") ? "IS NULL" : "= '".$sqlHelper->forSql($filter["site_id"], 2)."'");

		if ($moduleId == "main")
		{
			$con->queryExecute(
				"DELETE FROM b_option ".
				"WHERE MODULE_ID = 'main' ".
				"   AND NAME NOT LIKE '~%' ".
				"	AND NAME NOT IN ('crc_code', 'admin_passwordh', 'server_uniq_id','PARAM_MAX_SITES', 'PARAM_MAX_USERS') ".
				$strSqlWhere
			);
		}
		else
		{
			$con->queryExecute(
				"DELETE FROM b_option ".
				"WHERE MODULE_ID = '".$sqlHelper->forSql($moduleId)."' ".
				"   AND NAME <> '~bsm_stop_date' ".
				$strSqlWhere
			);
		}

		if (isset($filter["site_id"]))
		{
			$siteKey = $filter["site_id"] == "" ? "-" : $filter["site_id"];
			if (!isset($filter["name"]))
				unset(self::$options[$siteKey][$moduleId]);
			else
				unset(self::$options[$siteKey][$moduleId][$filter["name"]]);
		}
		else
		{
			$arSites = array_keys(self::$options);
			foreach ($arSites as $s)
			{
				if (!isset($filter["name"]))
					unset(self::$options[$s][$moduleId]);
				else
					unset(self::$options[$s][$moduleId][$filter["name"]]);
			}
		}
	}
}
