<?php
class inquiry_MessageService extends f_persistentdocument_DocumentService
{
	const MESSAGE_FROM_STAFF = 'modules_inquiry/messageFromStaff';
	const MESSAGE_TO_STAFF = 'modules_inquiry/messageToStaff';
	
	/**
	 * @var inquiry_MessageService
	 */
	private static $instance;

	/**
	 * @return inquiry_MessageService
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
	 * @return inquiry_persistentdocument_message
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_inquiry/message');
	}

	/**
	 * Create a query based on 'modules_inquiry/message' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_inquiry/message');
	}
	
	/**
	 * @param Integer $targetId
	 * @return Array<inquiry_persistentdocument_message>
	 */
	public function getByTargetId($targetId, $start = null, $limit = null, $order = 'desc')
	{
		$query = $this->createQuery()->add(Restrictions::eq('target.id', $targetId));
		if ($start !== null)
		{
			$query->setFirstResult($start);
		}
		if ($limit !== null)
		{
			$query->setMaxResults($limit);
		}
		if ($order == 'asc')
		{
			$query->addOrder(Order::asc('document_creationdate'));
		}
		else
		{
			$query->addOrder(Order::desc('document_creationdate'));
		}
		return $query->find();
	}
	
	/**
	 * @param Integer $targetId
	 */
	public function deleteByTargetId($targetId)
	{
		$query = $this->createQuery()->add(Restrictions::eq('target.id', $targetId));
		foreach ($query->find() as $comment)
		{
			$comment->delete();
		}
	}
	
	/**
	 * @param Integer $targetId
	 * @param Boolean $includeDetails
	 * @return Array<String => String>
	 */
	public function getInfosByTargetId($targetId, $includeDetails = false)
	{
		$infos = array('toStaffCount' => 0, 'fromStaffCount' => 0);
		$messages = $this->getByTargetId($targetId);
		$infos['totalCount'] = strval(count($messages));
		if ($includeDetails)
		{
			$infos['messages'] = array();
		}
		foreach ($messages as $message)
		{
			if ($includeDetails)
			{
				$messageInfo = array();
				$messageInfo['date'] = date_Formatter::formatBO($message->getCreationdate());
				$messageInfo['contents'] = $message->getContentsAsHtml();
				$messageInfo['authorFullName'] = $message->getAuthorFullName();
				$messageInfo['label'] = $message->getLabel();
				if ($message->getIsFromStaff())
				{
					$infos['toStaffCount']++;
					$messageInfo['messageType'] = 'toStaff';
				}
				else 
				{
					$infos['fromStaffCount']++;
					$messageInfo['messageType'] = 'fromStaff';
				}
				$infos['messages'][] = $messageInfo;
			}
			else 
			{
				if ($message->getIsFromStaff())
				{
					$infos['toStaffCount']++;
				}
				else 
				{
					$infos['fromStaffCount']++;
				}	
			}			
		}
		$infos['toStaffCount'] = strval($infos['toStaffCount']);
		$infos['fromStaffCount'] = strval($infos['fromStaffCount']);
		
		$infos['tasks'] = array();
		$currentUser = users_UserService::getInstance()->getCurrentBackEndUser();
		$inquiry = DocumentHelper::getDocumentInstance($targetId);
		if ($currentUser !== null && in_array($currentUser, $inquiry->getReceiverArray()))
		{
			$infos['canSendNew'] = $inquiry->isPublished();
			foreach (TaskHelper::getPendingTasksForCurrentBackendUserByDocumentId($inquiry->getId()) as $task)
			{
				$infos['tasks'][] = array(
					'type' => 'USER', 
					'id' => $task->getId(),
					'label' => $task->getWorkitem()->getLabelAsHtml()
				);
			}
			foreach ($inquiry->getDocumentService()->getMessageTasksForReceiver($inquiry) as $taskId => $taskLabel)
			{
				$infos['tasks'][] = array(
					'type' => 'MSG', 
					'id' => $taskId,
					'label' => $taskLabel
				);
			}
		}
		else
		{
			$infos['canSendNew'] = false;
		}
		return $infos;
	}
	
	/**
	 * @param inquiry_persistentdocument_inquiry $target
	 * @param string $contents
	 */
	public function sendToStaff($target, $contents)
	{
		$sender = users_UserService::getInstance()->getCurrentFrontEndUser();
		$senderEmail = ($sender !== null) ? $sender->getEmail() : $target->getAuthorEmail();
		$this->execSendMessage($target, $contents, $senderEmail, $sender, false);
	}

	/**
	 * @param inquiry_persistentdocument_inquiry $target
	 * @param string $contents
	 * @param users_persistentdocument_user $sender
	 */
	public function sendFromStaff($target, $contents)
	{
		$sender = users_UserService::getInstance()->getCurrentBackEndUser();
		$this->execSendMessage($target, $contents, $sender->getEmail(), $sender, true);
	}

	/**
	 * @param inquiry_persistentdocument_inquiry $target
	 * @param string $content
	 * @param string $senderEmail
	 * @param user_persistentdocument_user $sender
	 * @param boolean $isFromStaff
	 */
	protected function execSendMessage($target, $contents, $senderEmail, $sender, $isFromStaff)
	{
		$date = date_Formatter::format(date_Calendar::getInstance());
		$message = $this->getNewDocumentInstance();
		$message->setSender($sender);
		$message->setSenderEmail($senderEmail);
		$message->setContentsAsBBCode($contents);
		$message->setTarget($target);
		$message->setIsFromStaff($isFromStaff);
		$message->setLabel(LocaleService::getInstance()->transFO('m.inquiry.document.message.label-' . ($isFromStaff ? 'from' : 'to') . '-staff', array('ucf'), array('author' => $message->getAuthorFullName(), 'date' => $date)));
		$message->save();
		
		// Send the notification.
		$notification = $isFromStaff ? $target->getMessageByReceiverNotification() : $target->getMessageByAuthorNotification();
		if ($notification !== null)
		{
			$ns = $notification->getDocumentService();
			$notification->setSendingModuleName('inquiry');
			$callback = array($this, 'getNotificationParameters');
			$recipients = $this->getNotificationRecipients($message);
			$ns->sendNotificationCallback($notification, $recipients, $callback, $message);
		}
	}
	
	/**
	 * @param inquiry_persistentdocument_message $message
	 * @return Array<String=>String>
	 */
	public function getNotificationParameters($message)
	{
		$target = $message->getTarget();
		$inquiryParameters = $target->getDocumentService()->getNotificationParameters($target);
		$messageParameters = array('messageContents' => $message->getContentsAsHtml());
		return array_merge($inquiryParameters, $messageParameters);
	}
	
	/**
	 * @param inquiry_persistentdocument_inquiry $target
	 * @return Array<String=>String>
	 */
	protected function getNotificationRecipients($message)
	{
		$target = $message->getTarget();
		return $target->getDocumentService()->getNotificationRecipients($target);
	}
}