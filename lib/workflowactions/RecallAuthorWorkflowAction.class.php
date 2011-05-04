<?php
/**
 * @package modules.inquiry
 */
class inquiry_RecallAuthorWorkflowAction extends inquiry_BaseWorkflowAction
{
	/**
	 * This method will execute the action.
	 * @return boolean true if the execution end successfully, false in error case.
	 */
	function execute()
	{
		$this->setCaseParameter('__NEXT_ACTORS_IDS', $this->getCaseParameter('INQUIRY_RECEIVERS'));
		
		// Send the notification to the author.
		$document = $this->getDocument();
		$ns = notification_NotificationService::getInstance();
		$notification = $ns->getConfiguredByCodeName('modules_inquiry/recallAuthor', $document->getWebsiteId(), $document->getLang());
		if ($notification instanceof notification_persistentdocument_notification)
		{
			$notification->setSendingModuleName('inquiry');
			$callback = array($document->getDocumentService(), 'getNotificationParameters');
			$recipients = new mail_MessageRecipients();
			$recipients->setTo(array($document->getAuthorEmail()));
			$ns->sendNotificationCallback($notification, $recipients, $callback, $document);
		}		
		
		$this->setExecutionStatus(workflow_WorkitemService::EXECUTION_SUCCESS);
		return true;
	}
}