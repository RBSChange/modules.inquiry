<?php
/**
 * @package modules.inquiry
 */
class inquiry_CloseWorkflowAction extends inquiry_BaseWorkflowAction
{
	/**
	 * This method will execute the action.
	 * @return boolean true if the execution end successfully, false in error case.
	 */
	public function execute()
	{
		// Update the document's status.
		$document = $this->getDocument();
		$documentService = $document->getDocumentService();
		$documentService->file($document->getId());

		// Send the alert.
		$ns = notification_NotificationService::getInstance();
		$codeName = $this->getCaseParameter('CLOSE_INQUIRY_NOTIFICATION_CODE');
		$notification = $ns->getConfiguredByCodeName($codeName, $document->getWebsiteId(), $document->getLang());
		if ($notification instanceof notification_persistentdocument_notification)
		{
			$notification->setSendingModuleName('inquiry');
			$callback = array($documentService, 'getNotificationParameters');
			$recipients = $documentService->getNotificationRecipients($document);
			$ns->sendNotificationCallback($notification, $recipients, $callback, $document);
		}

		$this->setExecutionStatus(workflow_WorkitemService::EXECUTION_SUCCESS);
		return true;
	}
}