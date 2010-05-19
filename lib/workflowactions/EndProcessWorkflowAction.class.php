<?php
class inquiry_EndProcessWorkflowAction extends workflow_BaseWorkflowaction
{
	/**
	 * This method will execute the action.
	 * @return boolean true if the execution end successfully, false in error case.
	 */
	public function execute()
	{		
		$inquiry = $this->getDocument();
		$inquiry->setProcessingStatus('closed');
	
		$this->setExecutionStatus(workflow_WorkitemService::EXECUTION_SUCCESS);
		return true;
	}
}