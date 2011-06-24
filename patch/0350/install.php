<?php
/**
 * inquiry_patch_0350
 * @package modules.inquiry
 */
class inquiry_patch_0350 extends patch_BasePatch
{
	/**
	 * Entry point of the patch execution.
	 */
	public function execute()
	{
		$pp = f_persistentdocument_PersistentProvider::getInstance();
		$tm = f_persistentdocument_TransactionManager::getInstance();
		
		try 
		{
			$tm->beginTransaction();
			foreach (inquiry_MessageService::getInstance()->createQuery()->find() as $message)
			{
				$message->setContentsAsBBCode($message->getContents());
				$pp->updateDocument($message);
			}
			$tm->commit();
		}
		catch (Exception $e)
		{
			$tm->rollback($e);
		}
	}
}