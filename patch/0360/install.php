<?php
/**
 * inquiry_patch_0360
 * @package modules.inquiry
 */
class inquiry_patch_0360 extends patch_BasePatch
{
	/**
	 * Entry point of the patch execution.
	 */
	public function execute()
	{
		$newPath = f_util_FileUtils::buildWebeditPath('modules/inquiry/persistentdocument/inquiryform.xml');
		$newModel = generator_PersistentModel::loadModelFromString(f_util_FileUtils::read($newPath), 'inquiry', 'inquiryform');
		$newProp = $newModel->getPropertyByName('createInquiryNotification');
		f_persistentdocument_PersistentProvider::getInstance()->addProperty('inquiry', 'inquiryform', $newProp);
		$this->execChangeCommand('compile-db-schema');
	}
}