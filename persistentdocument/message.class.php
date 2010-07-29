<?php
class inquiry_persistentdocument_message extends inquiry_persistentdocument_messagebase
{
	/**
	 * @return String
	 */
	public function getContentsAsHtml()
	{
		return website_BBCodeService::getInstance()->ToHtml(parent::getContents());
	}
	
	/**
	 * @return String
	 */
	public function getContentsAsInput()
	{
		return htmlspecialchars(parent::getContents());
	}
	
	/**
	 * Get the readable message date.
	 * @return String
	 */
	public function getMessageDate()
	{
		if (defined('DEFAULT_TIMEZONE'))
		{
			date_default_timezone_set(DEFAULT_TIMEZONE);
		}
		$offset = intval(date('Z'));
		$creationDate = date_Calendar::getInstance($this->getCreationdate());
		$creationDate->add(date_Calendar::SECOND, $offset);
		return $creationDate->toString();
	}
	
	/**
	 * @return users_persistentdocument_user | null
	 */
	public function getAuthorDocument()
	{
		if ($this->getAuthorid())
		{
			try
			{
				return DocumentHelper::getDocumentInstance($this->getAuthorid());
			}
			catch (Exception $e)
			{
				if (Framework::isDebugEnabled())
				{
					Framework::debug(__METHOD__ . ' ' . $e->getMessage());
				}
			}
		}
		// The author doesn't exist any more...
		return null;
	}
	
	/**
	 * @return string
	 */
	public function getAuthorFullName()
	{
		$author = $this->getAuthorDocument();
		if ($author !== null)
		{
			return $author->getFullname();
		}
		return $this->getAuthor();
	}
	
	/**
	 * @return string
	 */
	public function getAuthorFullNameAsHtml()
	{
		$author = $this->getAuthorDocument();
		if ($author !== null)
		{
			return $author->getFullnameAsHtml();
		}
		return $this->getAuthorAsHtml();
	}
}