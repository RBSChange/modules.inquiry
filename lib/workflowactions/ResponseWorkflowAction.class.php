<?php
class inquiry_ResponseWorkflowAction extends inquiry_BaseWorkflowAction
{
	/**
	 * This method will execute the action.
	 * @return boolean true if the execution end successfully, false in error case.
	 */
	public function execute()
	{		
		$this->setCaseParameter('__NEXT_ACTORS_IDS', $this->getCaseParameter('INQUIRY_RECEIVERS'));
		
		$inquiry = $this->getDocument();
		$actorId = $this->getWorkitem()->getUserid();
		if ($actorId === null || $actorId == $this->getDocument()->getAuthorId())
		{
			$inquiry->setProcessingStatus('processing');
		}
		else 
		{
			$inquiry->setProcessingStatus('waitingauthor');
		}
		$inquiry->save();
		
		$this->setExecutionStatus(workflow_WorkitemService::EXECUTION_SUCCESS);
		return true;
	}
}