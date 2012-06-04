<?php
/**
 * inquiry_InquiryformService
 * @package modules.inquiry
 */
class inquiry_InquiryformService extends form_BaseformService
{
	const INQUIRY_URL_PARAM_NAME = 'inquiry_url';
	const INQUIRY_LINK_PARAM_NAME = 'inquiry_link';
	
	/**
	 * @var inquiry_InquiryformService
	 */
	private static $instance;

	/**
	 * @return inquiry_InquiryformService
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @return inquiry_persistentdocument_inquiryform
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_inquiry/inquiryform');
	}

	/**
	 * Create a query based on 'modules_inquiry/inquiryform' model.
	 * Return document that are instance of modules_inquiry/inquiryform,
	 * including potential children.
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_inquiry/inquiryform');
	}
	
	/**
	 * Create a query based on 'modules_inquiry/inquiryform' model.
	 * Only documents that are strictly instance of modules_inquiry/inquiryform
	 * (not children) will be retrieved
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createStrictQuery()
	{
		return $this->pp->createQuery('modules_inquiry/inquiryform', false);
	}
	
	/**
	 * @param array $params
	 * @return array
	 */
	public function getAcknowledgmentNotifParameters($params)
	{
		$parameters = parent::getAcknowledgmentNotifParameters($params);

		$result = $params['result'];		
		$inquiry = $result['inquiry'];
		$password = $inquiry->getPassword();
		$linkParameters = ($password) ? array('inquiryParam[password]' => $password) : array();
		$url = LinkHelper::getDocumentUrl($inquiry, null, $linkParameters);
		if ($url !== null)
		{
			$parameters[self::INQUIRY_URL_PARAM_NAME] = $url;
			$parameters[self::INQUIRY_LINK_PARAM_NAME] = '<a href="'.$url.'" class="link">'.$inquiry->getLabel().'</a>';
		}
	}
	
	/**
	 * @param form_persistentdocument_baseform $form
	 * @return string[]
	 */
	protected function getFieldRemplacementsForNotification($form)
	{
		$fieldArray = parent::getFieldRemplacementsForNotification($form);
		
		$fieldArray[] = '{'. self::INQUIRY_URL_PARAM_NAME. '}';
		$fieldArray[] = '{'. self::INQUIRY_LINK_PARAM_NAME. '}';
		
		return $fieldArray;
	}
	
	/**
	 * @param inquiry_persistentdocument_inquiryform $form
	 * @param form_persistentdocument_field[] $fields
	 * @param form_persistentdocument_response $response
	 * @param block_BlockRequest $request
	 * @param string $replyTo
	 * @return array an associative array contaning at least the key "success" with a boolean value. This array will be accessible during the acknowledgment notification sending.
	 */
	protected function handleData($form, $fields, $response, $request, $replyTo)
	{
		// Generate the inquiry.
		$inquiry = inquiry_InquiryService::getInstance()->getNewDocumentInstance();
		$inquiry->setLabel('temporary-label');
		$inquiry->setFormId($form->getId());
		$inquiry->setWebsiteId(website_WebsiteService::getInstance()->getCurrentWebsite()->getId());
		$inquiry->setStartTaskId($form->getStartTaskId());
		foreach ($this->getPublishedReceiverIds($form) as $userId)
		{
			$inquiry->addReceiver(DocumentHelper::getDocumentInstance($userId));
		}
		
		$responseData = $response->getAllData();
		$user = users_UserService::getInstance()->getCurrentFrontEndUser();
		$inquiry->setResponseData($responseData);
		$replyField = $form->getReplyToField();
		if ($replyField !== null)
		{
			$inquiry->setAuthorEmail($inquiry->getResponseFieldValue($replyField->getFieldName()));
		}
		else 
		{
			if ($user !== null)
			{
				$inquiry->setAuthorEmail($user->getEmail());
			}
			else
			{
				throw new Exception('There is no authenticated user or reply-to field.');
			}
		}
		
		if ($user === null)
		{
			$inquiry->setPassword(f_util_StringUtils::randomString());
		}
		
		$inbox = $form->getInbox();
		$folder = DocumentHelper::getDocumentInstance(ModuleService::getInstance()->getRootFolderId('inquiry'), 'modules_generic/rootfolder');
		if ($inbox)
		{
			$folder = generic_FolderService::getInstance()->mkdir($folder, $inbox);	
		}
		$inquiry->save($folder->getId());

		// Start the workflow.
		workflow_WorkflowEngineService::getInstance()->initWorkflowInstance($inquiry->getId(), $form->getStartTaskId(), array());
		
		return array('success' => true, 'inquiry' => $inquiry);
	}
	
	/**
	 * @param inquiry_persistentdocument_inquiryform $form
	 * @return integer[]
	 */
	private function getPublishedReceiverIds($form)
	{
		$receiverIds = DocumentHelper::getIdArrayFromDocumentArray($form->getReceiverArray());
		return users_UserService::getInstance()->convertToPublishedUserIds($receiverIds);
	}
	
	/**
	 * @param inquiry_persistentdocument_inquiryform $form
	 * @return workflow_persistentdocument_workflow | null
	 */
	private function getInquiryWorkflow($form)
	{
		return workflow_WorkflowEngineService::getInstance()->getActiveWorkflowDefinitionByStarttaskid($form->getStartTaskId());
	}
	
	/**
	 * @return string
	 */
	protected function getDefaultAcknowledgmentNotificationBody()
	{
		return '{'.self::CONTENT_REPLACEMENT_NAME.'}<br /><br />{'.self::INQUIRY_LINK_PARAM_NAME.'}';
	}

	/**
	 * @param inquiry_persistentdocument_inquiryform $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
	protected function postInsert($document, $parentNodeId = null)
	{
		parent::postInsert($document, $parentNodeId);
		
		// Initialize the email field.
		if (!$document->getIsDuplicating())
		{
			$field = form_MailService::getInstance()->getNewDocumentInstance();
			$field->setLabel(LocaleService::getInstance()->transFO('m.inquiry.bo.general.default-mail-label', array('ucf')));
			$field->setFieldName('email');
			$field->setRequired(true);
			$field->setIsLocked(true);
			$field->setUseAsReply(true);
			$field->setInitializeWithCurrentUserEmail(true);
			$field->setAcknowledgmentReceiver(true);
			$field->save($document->getId());
		}
	}

	/**
	 * @param inquiry_persistentdocument_inquiryform $document
	 * @return boolean true if the document is publishable, false if it is not.
	 */
	public function isPublishable($document)
	{
		if (!parent::isPublishable($document))
		{
			return false;
		}
		
		if (count($this->getPublishedReceiverIds($document)) < 1)		
		{
			$this->setActivePublicationStatusInfo($document, 'm.inquiry.document.inquiryform.publication.no-published-receiver');
			return false;
		}
		
		if ($this->getInquiryWorkflow($document) === null)		
		{
			$this->setActivePublicationStatusInfo($document, 'm.inquiry.document.inquiryform.publication.no-published-workflow');
			return false;
		}
		
		return true;
	}
}