<?php
/**
 * Class where to put your custom methods for document inquiry_persistentdocument_inquiry
 * @package modules.inquiry.persistentdocument
 */
class inquiry_persistentdocument_inquiry extends inquiry_persistentdocument_inquirybase
{
	/**
	 * @return users_persistentdocument_websitefrontenduser | null
	 */
	public function getAuthorDocument()
	{
		return $this->getDocumentService()->getAuthorDocument($this);
	}

	/**
	 * @return inquiry_persistentdocument_inquiryform
	 */
	public function getForm()
	{
		try 
		{
			return DocumentHelper::getDocumentInstance($this->getFormId());
		}
		catch (Exception $e)
		{
			// The form doesn't exist any more.
			return null;
		}
	}
	
	/**
	 * @return Array
	 */
	public function getResponseData()
	{
		$data = parent::getResponseData();
		if ($data !== null)
		{
			return unserialize($data);
		}
		return array();
	}
	
	/**
	 * @return Array
	 */
	public function setResponseData($array)
	{
		if (!is_array($array))
		{
			$array = null;
		}
		parent::setResponseData(serialize($array));
	}
	
	/**
	 * @return Array
	 */
	public function getResponseFieldNames()
	{
		return array_keys($this->getResponseData());
	}
	
	/**
	 * @return string | null
	 */
	public function getResponseFieldValue($key)
	{
		$data = $this->getResponseData();
		if (isset($data[$key]) && isset($data[$key]['value']))
		{
			return $data[$key]['value'];
		}
		return null;
	}
	
	/**
	 * @return string | null
	 */
	public function getResponseFieldLabel($key)
	{
		$data = $this->getResponseData();
		if (isset($data[$key]) && isset($data[$key]['label']))
		{
			return $data[$key]['label'];
		}
		return null;
	}
	
	/**
	 * @return string | null
	 */
	public function getResponseFieldType($key)
	{
		$data = $this->getResponseData();
		if (isset($data[$key]) && isset($data[$key]['label']))
		{
			return $data[$key]['type'];
		}
		return null;
	}
	
	/**
	 * @return boolean
	 */
	public function isClosed()
	{
		return $this->getProcessingStatus() == 'closed';
	}	
	
	/**
	 * @return string
	 */
	public function getProcessingStatusLabel()
	{
		$list = list_ListService::getInstance()->getByListId('modules_inquiry/processingstatuses');
		$status = $this->getProcessingStatus();
		$item = $list->getItemByValue($status);
		if ($item !== null)
		{
			$label = $item->getLabel();
			if ($status == 'closed')
			{
				$label .= ' ' . f_Locale::translate('&modules.inquiry.document.inquiry.' . (($this->getDocumentService()->getCaseParameter($this, 'CLOSED_BY') == 'author') ? 'by-author' : 'by-receiver') . ';');
			}
			return $label;
		}
		return 'Unknown status';
	}
	
	/**
	 * @return string
	 */
	public function getProcessingStatusLabelAsHtml()
	{
		return f_util_HtmlUtils::textToHtml($this->getProcessingStatusLabel());
	}
	
	/**
	 * @return notification_persistentdocument_notification
	 */
	public function getMessageByAuthorNotification()
	{
		return $this->getDocumentService()->getNotification($this, 'MESSAGE_BY_AUTHOR_NOTIFICATION_CODE');
	}
	
	/**
	 * @return notification_persistentdocument_notification
	 */
	public function getMessageByReceiverNotification()
	{
		return $this->getDocumentService()->getNotification($this, 'MESSAGE_BY_RECEIVER_NOTIFICATION_CODE');
	}
}