<?php
class inquiry_CloseWorkflowAction extends workflow_BaseWorkflowaction
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
		$notification = $ns->getByCodeName($this->getCaseParameter('CLOSE_INQUIRY_NOTIFICATION_CODE'));
		$recipients = $documentService->getNotificationRecipients($document);
		$replacements = $documentService->getNotificationParameters($document);
		$ns->send($notification, $recipients, $replacements);

		$this->setExecutionStatus(workflow_WorkitemService::EXECUTION_SUCCESS);
		return true;
	}
}