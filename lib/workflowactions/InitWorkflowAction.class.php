<?php
class inquiry_InitWorkflowAction extends workflow_BaseWorkflowaction
{
	/**
	 * This method will execute the action.
	 * @return boolean true if the execution end successfully, false in error case.
	 */
	public function execute()
	{	
		$inquiry = $this->getDocument();
		
		$receiverIds = DocumentHelper::getIdArrayFromDocumentArray($inquiry->getReceiverArray());
		if (Framework::isDebugEnabled())
		{
			Framework::debug(__METHOD__ . ' receiverIds = ' . var_export($receiverIds, true));
		}
		$this->setCaseParameter('INQUIRY_RECEIVERS', $receiverIds);
		$this->setCaseParameter('__NEXT_ACTORS_IDS', $receiverIds);
		
		$this->setCaseParameter('INQUIRY_AUTHOR', $inquiry->getAuthorId());
		
		$form = $inquiry->getForm();
		$notification = $form->getMessageByAuthorNotification();
		if ($notification !== null)
		{
			$this->setCaseParameter('MESSAGE_BY_AUTHOR_NOTIFICATION_CODE', $notification->getCodename());
		}
		$notification = $form->getMessageByReceiverNotification();
		if ($notification !== null)
		{
			$this->setCaseParameter('MESSAGE_BY_RECEIVER_NOTIFICATION_CODE', $notification->getCodename());
		}
		$notification = $form->getCloseInquiryNotification();
		if ($notification !== null)
		{
			$this->setCaseParameter('CLOSE_INQUIRY_NOTIFICATION_CODE', $notification->getCodename());
		}
		
		$this->setExecutionStatus(workflow_WorkitemService::EXECUTION_SUCCESS);
		return true;
	}
}