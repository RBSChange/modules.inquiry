<?php
/**
 * @package modules.inquiry
 */
class inquiry_BaseWorkflowAction extends workflow_BaseWorkflowaction
{	
	/**
	 * @param task_persistentdocument_usertask $task
	 * @return array
	 */
	protected function getCommonNotifParameters($usertask)
	{
		$document = $this->getDocument();
		return $document->getDocumentService()->getNotificationParameters($document);
	}
}