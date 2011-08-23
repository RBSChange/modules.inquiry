<?php
/**
 * @package modules.inquiry.lib.services
 */
class inquiry_ModuleService extends ModuleBaseService
{
	/**
	 * Singleton
	 * @var inquiry_ModuleService
	 */
	private static $instance = null;

	/**
	 * @return inquiry_ModuleService
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self();
		}
		return self::$instance;
	}
}