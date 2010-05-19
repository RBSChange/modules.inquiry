<?php
/**
 * inquiry_SendMessageAction
 * @package modules.inquiry.actions
 */
class inquiry_SendMessageAction extends f_action_BaseJSONAction
{
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
		$target = $this->getDocumentInstanceFromRequest($request);
		$currentUser = users_UserService::getInstance()->getCurrentBackEndUser();
		if ($target->isPublished() && $currentUser !== null && in_array($currentUser, $target->getReceiverArray()))
		{
			if ($request->hasParameter('contents'))
			{
				$contents = trim($request->getParameter('contents'));
				if ($contents)
				{
					$ims = inquiry_MessageService::getInstance();
					$ims->sendFromStaff($target, $contents);
					
					$taskId = $request->getParameter('taskId');
					$taskType = $request->getParameter('taskType');
					if (f_util_StringUtils::isNotEmpty($taskId) && f_util_StringUtils::isNotEmpty($taskType))
					{
						switch ($taskType)
						{
							case 'USER':
								$task = DocumentHelper::getDocumentInstance($taskId);
								if ($task->getUser()->getId() === $currentUser->getId())
								{
									$this->doExecuteUserTask($context, $request, $target, $task);
								}
								else
								{
									return $this->sendJSONError(f_Locale::translateUI('&modules.inquiry.bo.doceditor.panel.messages.Error-task-not-for-you;', true));
								}
								break;
							
							case 'MSG':
								$taskFound = false;
								foreach ($target->getDocumentService()->getMessageTasksForReceiver($target) as $thisTaskId => $thisTaskLabel)
								{
									if ($taskId == $thisTaskId)
									{
										$this->doExecuteMessageTask($context, $request, $target, $taskId);
										$taskFound = true;
									}
								}
								if (!$taskFound)
								{
									return $this->sendJSONError(f_Locale::translateUI('&modules.inquiry.bo.doceditor.panel.messages.Error-task-not-for-you;', true));
								}
								break;
							
							default: 
								return $this->sendJSONError(f_Locale::translateUI('&modules.inquiry.bo.doceditor.panel.messages.Error-unknown-task-type;', true));
								break;
						}
					}
					return $this->sendJSON($ims->getInfosByTargetId($target->getId(), true));
				}
			}
			return $this->sendJSONError(f_Locale::translateUI('&modules.inquiry.bo.doceditor.panel.messages.Error-no-message-to-send;', true));
		}
		return $this->sendJSONError(f_Locale::translateUI('&modules.inquiry.bo.doceditor.panel.messages.Error-not-receiver;', true));
	}
	
	/**
	 * @param Context $context
	 * @param Request $request
	 * @param inquiry_persistentodcument_inquiry $inquiry
	 * @param string $taskId
	 */
	protected function doExecuteMessageTask($context, $request, $inquiry, $taskId)
	{
		if ($taskId == 'CLOSE')
		{
			$inquiry->getDocumentService()->setCaseParameter($inquiry, 'CLOSED_BY', 'receiver');
		}		
		workflow_WorkflowEngineService::getInstance()->executeMessageTask($inquiry->getId(), $taskId);
	}
	
	/**
	 * @param Context $context
	 * @param Request $request
	 * @param inquiry_persistentodcument_inquiry $inquiry
	 * @param task_persistentdocument_usertask $task
	 */
	protected function doExecuteUserTask($context, $request, $inquiry, $task)
	{
		TaskHelper::getUsertaskService()->execute($task, '', '', $task->getUser());
	}
}