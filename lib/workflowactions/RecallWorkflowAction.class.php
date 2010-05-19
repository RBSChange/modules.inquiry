<?php
/**
 * Resend the creation notification of the last transition.
 * @package modules.workflow
 */
class inquiry_RecallWorkflowAction extends workflow_BaseWorkflowaction
{
	/**
	 * This method will execute the action.
	 * @return boolean true if the execution end successfully, false in error case.
	 */
	function execute()
	{
		$this->setCaseParameter('__NEXT_ACTORS_IDS', $this->getCaseParameter('INQUIRY_RECEIVERS'));
		
		// Send the notification to the receivers.
		$inquiry = $this->getDocument();
		$emails = array();
		foreach ($inquiry->getPublishedReceiverArray() as $user)
		{
			$emails[] = $user->getEmail();
		}
		$replacements = $inquiry->getDocumentService()->getNotificationParameters($inquiry);
		$this->sendNotification('modules_inquiry/recallReceiver', $emails, $replacements);
		
		$this->setExecutionStatus(workflow_WorkitemService::EXECUTION_SUCCESS);
		return true;
	}
}