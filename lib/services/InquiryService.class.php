<?php
/**
 * inquiry_InquiryService
 * @package modules.inquiry
 */
class inquiry_InquiryService extends f_persistentdocument_DocumentService
{
	/**
	 * @var inquiry_InquiryService
	 */
	private static $instance;

	/**
	 * @return inquiry_InquiryService
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}

	/**
	 * @return inquiry_persistentdocument_inquiry
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_inquiry/inquiry');
	}

	/**
	 * Create a query based on 'modules_inquiry/inquiry' model.
	 * Return document that are instance of modules_inquiry/inquiry,
	 * including potential children.
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_inquiry/inquiry');
	}
	
	/**
	 * Create a query based on 'modules_inquiry/inquiry' model.
	 * Only documents that are strictly instance of modules_inquiry/inquiry
	 * (not children) will be retrieved
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createStrictQuery()
	{
		return $this->pp->createQuery('modules_inquiry/inquiry', false);
	}
	
	/**
	 * @param inquiry_persistentdocument_inquiry $inquiry
	 * @param string $parameterName
	 * @return string | null
	 */
	public function getCaseParameter($inquiry, $parameterName)
	{
		$case = $this->getCase($inquiry);
		if ($case !== null)
		{
			return $case->getParameter($parameterName);
		}
		return null;
	}
	
	/**
	 * @param inquiry_persistentdocument_inquiry $inquiry
	 * @param string $parameterName
	 * @param mixed $value
	 * @return string | null
	 */
	public function setCaseParameter($inquiry, $parameterName, $value)
	{
		$case = $this->getCase($inquiry);
		if ($case !== null)
		{
			$case->setParameter($parameterName, $value);
		}
	}
	
	/**
	 * @param inquiry_persistentdocument_inquiry $inquiry
	 */
	public function close($inquiry)
	{
		$engine = workflow_WorkflowEngineService::getInstance();
		$engine->executeMessageTask($inquiry->getId(), 'CLOSE');
	}
	
	/**
	 * @param inquiry_persistentdocument_inquiry $inquiry
	 * @return workflow_persistentdocument_case | null
	 */
	private function getCase($inquiry)
	{
		$query = workflow_CaseService::getInstance()->createQuery();
		$query->add(Restrictions::eq('documentid', $inquiry->getId()));
		return $query->findUnique();
	}
	
	/**
	 * @param inquiry_persistentdocument_inquiry $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
	protected function postInsert($document, $parentNodeId)
	{
		if ($document->getLabel() === 'temporary-label')
		{
			$document->setLabel(LocaleService::getInstance()->transFO('m.inquiry.document.inquiry.label-template', array('ucf'), array('id' => $document->getId())));
			$tm = $this->getTransactionManager();
			$tm->beginTransaction();
			$this->getPersistentProvider()->updateDocument($document);
			$tm->commit();
		}
	}

	/**
	 * @param inquiry_persistentdocument_inquiry $document
	 * @return integer | null
	 */
	public function getWebsiteId($document)
	{
		return $document->getWebsiteId();
	}

	/**
	 * @param inquiry_persistentdocument_inquiry $document
	 * @return website_persistentdocument_page | null
	 */
	public function getDisplayPage($document)
	{
		//Check for original document;
		$document = DocumentHelper::getByCorrection($document);
		return TagService::getInstance()->getDetailPageForDocument($document);
	}

	/**
	 * @param inquiry_persistentdocument_inquiry $document
	 * @param string $forModuleName
	 * @param array $allowedSections
	 * @return array
	 */
	public function getResume($document, $forModuleName, $allowedSections = null)
	{
		$resume = parent::getResume($document, $forModuleName, $allowedSections);
		
		$resume['properties']['processingStatus'] = $document->getProcessingStatusLabel();		
		
		$form = $document->getForm();
		if ($form !== null)
		{
			$backUri = join(',', array('inquiry', 'openDocument', 'modules_inquiry_inquiry', $document->getId(), 'resume'));
			$formUri = join(',' , array('form', 'openDocument', 'modules_inquiry_inquiryform', $form->getId(), 'resume'));
			$resume['properties']['form'] = array("uri" => $formUri, "label" => $form->getLabel(), "backuri" => $backUri);
		}
		
		$author = $this->getAuthorDocument($document);				
		if ($author !== null)
		{
			$backUri = join(',', array('inquiry', 'openDocument', 'modules_inquiry_inquiry', $document->getId(), 'resume'));
			$userUri = join(',' , array('users', 'openDocument', 'modules_users_websitefrontenduser', $author->getId(), 'resume'));
			$resume['properties']['author'] = array("uri" => $userUri, "label" => $author->getLabel(), "backuri" => $backUri);
		}
		else
		{
			$resume['properties']['authoremail'] = $document->getAuthorEmail();
			unset($resume['properties']['author']);
		}
		
		$i = 0;
		$resume['responsedata'] = array();
		$resume['responsedata']['contents'] = $this->getResponseContents($i, array_values($document->getResponseData()), 0, null);
				
		$resume['messages'] = inquiry_MessageService::getInstance()->getInfosByTargetId($document->getId());
		
		return $resume;
	}
	
	/**
	 * @param integer $i
	 * @param array $responseData
	 * @param integer $level
	 * @param string $groupName
	 * @return array
	 */
	private function getResponseContents(&$i, $responseData, $level, $groupName)
	{	
		$contents = array();
		while ($i < count($responseData))
		{
			$row = $responseData[$i];
			$nodeLevel = isset($row['level']) ? $row['level'] : null;
			$nodeGroupName = isset($row['groupName']) ? $row['groupName'] : null;
			if ($nodeLevel > $level)
			{
				$contents[] = array(
					'isGroup' => true, 
					'label' => $nodeGroupName, 
					'contents' => $this->getResponseContents($i, $responseData, $nodeLevel, $nodeGroupName)
				);
			}
			else if ($level < $nodeLevel || $groupName != $nodeGroupName)
			{
				return $contents;
			}
			else 
			{
				$contents[] = $this->getFieldInfos($row);
			}
			$i++;
		}
		return $contents;
	}
	
	/**
	 * @param array $row
	 * @return array
	 */
	private function getFieldInfos($row)
	{
		$value = htmlspecialchars($row['value']);
		if ($row['type'] == 'date')
		{
			$mailValue = date_Formatter::toDefaultDate($value);
		}
		else
		{
			$mailValue = isset($row['mailValue']) ? $row['mailValue'] : $value;
		}
		$infos = array(
			'isGroup' => false,
			'label' => $row['label'],
			'mailValue' => $mailValue,
			'value' => $value
		);
		if ($value && isset($row['isFile']) && $row['isFile'] == 'true')
		{
			try 
			{
				$file = DocumentHelper::getDocumentInstance($value);
				$infos['isFile'] = true;
				$infos['href'] = LinkHelper::getUIActionLink('media', 'BoDisplay')->setQueryParameter('cmpref', $value)
					->setQueryParameter('lang', $file->getI18nInfo()->getVo())->setQueryParameter('forceDownload', 'true')->getUrl();
				$infos['linklabel'] = $file->getLabel();
			}
			catch (Exception $e)
			{
				$e; // Avoid Eclipse warning...
				$infos['mailValue'] = LocaleService::getInstance()->transBO('m.form.bo.general.unexisting-file', array('ucf'), array('id' => $value));
			}
		}
		return $infos;
	}
	
	/**
	 * @param users_persistentdocument_websitefrontenduser $user
	 * @return inquiry_persistentdocument_inquiry[]
	 */
	public function getByAuthor($user)
	{
		return $this->createQuery()->add(Restrictions::eq('authorid', $user->getId()))->addOrder(Order::desc('document_creationdate'))->find();
	}
	
	/**
	 * @param inquiry_persistentdocument_inquiry $inquiry
	 * @return users_persistentoducment_websitefrontenduser | null
	 */
	public function getAuthorDocument($inquiry)
	{
		$authorId = $inquiry->getAuthorId();
		if ($authorId !== null)
		{
			try 
			{
				return DocumentHelper::getDocumentInstance($authorId);
			}
			catch (Exception $e)
			{
				// User doesn't exist any more...
				$e; // Avoid Eclipse warning...
			}
		}
		return null;
	}
	

	/**
	 * @param inquiry_persistentdocument_inquiry $inquiry
	 * @return Array<String=>String>
	 */
	public function getNotificationParameters($inquiry)
	{
		$url = LinkHelper::getDocumentUrl($inquiry);
		$label = $inquiry->getLabelAsHtml();
		return array(
			'inquiryId' => $inquiry->getId(), 
			'inquiryLabel' => $label,
			'inquiryUrl' => $url,
			'inquiryLink' => (f_util_StringUtils::isEmpty($url) ? '' : '<a href="' . $url . '" class="link">' . $label . '</a>'),
			'inquiryCreationdate' => date_Formatter::format($inquiry->getCreationdate()),
			'inqiuryCreationdate' => date_Formatter::format($inquiry->getCreationdate()) // @deprecated (will be removed in 4.0)
		);
	}
	
	/**
	 * @param inquiry_persistentdocument_inquiry $inquiry
	 * @return Array<String=>String>
	 */
	public function getNotificationRecipients($inquiry)
	{
		$recipients = new mail_MessageRecipients();
		$emails = array($inquiry->getAuthorEmail());
		foreach ($inquiry->getReceiverArray() as $receiver)
		{
			$emails[] = $receiver->getEmail();
		}
		$recipients->setTo($emails);
		return $recipients;
	}	
	
	/**
	 * @param inquiry_persistentdocument_inquiry $inquiry
	 * @param string $parameterName
	 * @return notification_persistentdocument_notification | null
	 */
	public function getNotification($inquiry, $parameterName)
	{
		$code = $this->getCaseParameter($inquiry, $parameterName);
		if ($code)
		{
			return notification_NotificationService::getInstance()->getConfiguredByCodeName($code, $inquiry->getWebsiteId(), $inquiry->getLang());
		}
		return null;
	}
	
	/**
	 * @param inquiry_persistentdocument_inquiry $inquiry
	 * @return array
	 */
	public function getMessageTasksForAuthor($inquiry)
	{
		$tasks = array();
		foreach (workflow_WorkitemService::getInstance()->getActiveMessageWorkitemsByDocumentId($inquiry->getId()) as $workitem)
		{
			$taskId = $workitem->getTransition()->getTaskid();
			if (f_util_StringUtils::beginsWith($taskId, 'AUTHOR_'))
			{
				$tasks[$taskId] = $workitem->getLabelAsHtml();
			}
			else if (!f_util_StringUtils::beginsWith($taskId, 'RECEIVER_'))
			{
				$tasks[$taskId] = $workitem->getLabelAsHtml();
			}
		}
		return $tasks;
	}
	
	/**
	 * @param inquiry_persistentdocument_inquiry $inquiry
	 * @return array
	 */
	public function getMessageTasksForReceiver($inquiry)
	{
		$tasks = array();
		foreach (workflow_WorkitemService::getInstance()->getActiveMessageWorkitemsByDocumentId($inquiry->getId()) as $workitem)
		{
			$taskId = $workitem->getTransition()->getTaskid();
			if (f_util_StringUtils::beginsWith($taskId, 'RECEIVER_'))
			{
				$tasks[$taskId] = $workitem->getLabelAsHtml();
			}
			else if (!f_util_StringUtils::beginsWith($taskId, 'AUTHOR_'))
			{
				$tasks[$taskId] = $workitem->getLabelAsHtml();
			}
		}
		return $tasks;
	}
}