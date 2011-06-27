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
		$parser = new website_BBCodeParser();
		
		try 
		{
			$tm->beginTransaction();
			foreach (inquiry_MessageService::getInstance()->createQuery()->find() as $doc)
			{
				$text = $doc->getContents();
				if (f_util_StringUtils::beginsWith($text, '<div data-profile="'))
				{
					$text = $parser->convertXmlToBBCode($text);
				}
				$doc->setContentsAsBBCode($text);
				$pp->updateDocument($doc);
			}
			$tm->commit();
		}
		catch (Exception $e)
		{
			$tm->rollback($e);
		}
	}
}