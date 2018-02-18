<?php
namespace Bitrix\Crm\Settings;
use Bitrix\Main;
class LayoutSettings
{
	/** @var LayoutSettings */
	private static $current = null;

	/** @var BooleanSetting */
	private $enableSlider = null;

	function __construct()
	{
		$this->enableSlider = new BooleanSetting('enable_slider', false);
	}
	/**
	 * Get current instance
	 * @return LayoutSettings
	 */
	public static function getCurrent()
	{
		if(self::$current === null)
		{
			self::$current = new LayoutSettings();
		}
		return self::$current;
	}
	/**
	 * Check if slider enabled for edit and view actions
	 * @return bool
	 */
	public function isSliderEnabled()
	{
		return $this->enableSlider->get();
	}
	/**
	 * Enabled slider for edit and view actions
	 * @param bool $enabled Enabled Flag.
	 * @return void
	 */
	public function enableSlider($enabled)
	{
		$this->enableSlider->set($enabled);
	}
}