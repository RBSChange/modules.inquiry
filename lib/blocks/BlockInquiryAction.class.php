<?php
/**
 * @package modules.inquiry
 */
class inquiry_BlockInquiryAction extends website_BlockAction
{
	/**
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @return String
	 */
	public function execute($request, $response)
	{
		$inquiry = $this->getDocumentParameter();
		if (!$this->checkAccess($request, $inquiry))
		{
			return $this->getLoginView($request, $inquiry);
		}
				
		return $this->getSuccessView($request, $inquiry);
	}
	
	/**
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @return String
	 */
	public function executePreview($request, $response)
	{
		$inquiry = $this->getDocumentParameter();
		if (!$this->checkAccess($request, $inquiry))
		{
			return $this->getLoginView($request, $inquiry);
		}
		
		$parser = new website_BBCodeParser();
		$request->setAttribute('contentsPreview', $parser->convertBBCodeToHtml($request->getParameter('contents'), $parser->getModuleProfile('inquiry')));
		return $this->getSuccessView($request, $inquiry);
	}
	
	/**
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @return String
	 */
	public function executeSend($request, $response)
	{
		$inquiry = $this->getDocumentParameter();
		if (!$this->checkAccess($request, $inquiry))
		{
			return $this->getLoginView($request, $inquiry);
		}
		
		if (!$request->getParameter('contents'))
		{
			$this->addError(LocaleService::getInstance()->transFO('m.inquiry.frontoffice.error-no-contents', array('ucf')));
			return $this->getSuccessView($request, $inquiry);
		}
		$this->sendMessage($request, $inquiry);
				
		HttpController::getInstance()->redirectToUrl(LinkHelper::getCurrentUrlComplete());
		return website_BlockView::NONE;
	}
	
	/**
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @return String
	 */
	public function executeTask($request, $response)
	{
		$inquiry = $this->getDocumentParameter();
		if (!$this->checkAccess($request, $inquiry))
		{
			return $this->getLoginView($request, $inquiry);
		}
		
		if (!$request->getParameter('contents'))
		{
			$this->addError(LocaleService::getInstance()->transFO('m.inquiry.frontoffice.error-no-contents', array('ucf')));
			return $this->getSuccessView($request, $inquiry);
		}
		$this->sendMessage($request, $inquiry);
		
		$taskLabel = $request->getParameter('website_BlockAction_submit');
		$taskLabel = $taskLabel[$this->getBlockId()]['task'];
		foreach ($inquiry->getDocumentService()->getMessageTasksForAuthor($inquiry) as $taskId => $messageTaskLabel)
		{
			if ($taskLabel == $messageTaskLabel)
			{
				return $this->doExecuteMessageTask($request, $response, $inquiry, $taskId);
			}
		}
		$this->addError(LocaleService::getInstance()->transFO('m.inquiry.frontoffice.invalid-task', array('ucf')));
		return website_BlockView::ERROR;
	}
	
	/**
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @param inquiry_persistentodcument_inquiry $inquiry
	 * @param string $taskI
	 * @return String
	 */
	protected function doExecuteMessageTask($request, $response, $inquiry, $taskId)
	{
		if ($taskId == 'CLOSE')
		{
			$inquiry->getDocumentService()->setCaseParameter($inquiry, 'CLOSED_BY', 'author');
		}
		
		workflow_WorkflowEngineService::getInstance()->executeMessageTask($inquiry->getId(), $taskId);
		
		HttpController::getInstance()->redirectToUrl(LinkHelper::getCurrentUrlComplete());
		return website_BlockView::NONE;
	}
	
	/**
	 * @param f_mvc_Request $request
	 * @param inquiry_persistentdocument_inquiry $inquiry
	 */
	private function getSuccessView($request, $inquiry)
	{
		$request->setAttribute('inquiry', $inquiry);
		
		$messages = inquiry_MessageService::getInstance()->getByTargetId($inquiry->getId());
		if (count($messages) > 0)
		{
			$nbItemPerPage = $this->getConfiguration()->getNbItemPerPage();
			$pageNumber = $request->getParameter('page');
			if (!is_numeric($pageNumber) || $pageNumber < 1 || $pageNumber > ceil(count($messages) / $nbItemPerPage))
			{
				$pageNumber = 1;
			}
			$paginator = new paginator_Paginator('inquiry', $pageNumber, $messages, $nbItemPerPage);			
			$request->setAttribute('messages', $paginator);
		}
		
		$tasks = array();
		foreach ($inquiry->getDocumentService()->getMessageTasksForAuthor($inquiry) as $taskLabel)
		{
			$tasks[] = $taskLabel;
		}
		$request->setAttribute('tasks', $tasks);
		
		return website_BlockView::SUCCESS;
	}
	
	/**
	 * @param f_mvc_Request $request
	 * @param inquiry_persistentdocument_inquiry $inquiry
	 */
	private function getLoginView($request, $inquiry)
	{
		$request->setAttribute('inquiry', $inquiry);		
		return 'Login';
	}
	
	/**
	 * @param f_mvc_Request $request
	 * @param inquiry_persistentdocument_inquiry $inquiry
	 */
	private function checkAccess($request, $inquiry)
	{
		if ($inquiry === null)
		{
			return false;
		}
	
		$currentUser = users_UserService::getInstance()->getCurrentFrontEndUser();
		if ($currentUser !== null && $currentUser == $inquiry->getAuthorDocument())
		{
			return true;
		}
		
		$inquiryPassword = $inquiry->getPassword();
		if ($inquiryPassword !== null && $request->hasParameter('password') && $inquiryPassword == $request->getParameter('password'))
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * @param f_mvc_Request $request
	 * @param inquiry_persistentdocument_inquiry $inquiry
	 */
	private function sendMessage($request, $inquiry)
	{
		$contents = $request->getParameter('contents');
		inquiry_MessageService::getInstance()->sendToStaff($inquiry, $contents);
	}
	
	// Deprecated.
	
	/**
	 * @deprecated (will be removed in 4.0)
	 */
	protected function doExecuteUserTask($request, $response, $inquiry, $task)
	{
		TaskHelper::getUsertaskService()->execute($task, '', '', $task->getUser());
	
		HttpController::getInstance()->redirectToUrl(LinkHelper::getCurrentUrlComplete());
		return website_BlockView::NONE;
	}
}