<?php
/**
 * Resend the creation notification of the last transition.
 * @package modules.workflow
 */
class inquiry_RecallAuthorWorkflowAction extends workflow_BaseWorkflowaction
{
	/**
	 * This method will execute the action.
	 * @return boolean true if the execution end successfully, false in error case.
	 */
	function execute()
	{
		$this->setCaseParameter('__NEXT_ACTORS_IDS', $this->getCaseParameter('INQUIRY_RECEIVERS'));
		
		// Send the notification to the author.
		$inquiry = $this->getDocument();
		$replacements = $inquiry->getDocumentService()->getNotificationParameters($inquiry);
		$this->sendNotification('modules_inquiry/recallAuthor', array($inquiry->getAuthorEmail()), $replacements);
				
		$this->setExecutionStatus(workflow_WorkitemService::EXECUTION_SUCCESS);
		return true;
	}
}