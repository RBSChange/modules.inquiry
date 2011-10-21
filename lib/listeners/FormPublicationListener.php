<?php
class inquiry_FromPublicationListener
{
	/**
	 * @param f_persistentdocument_DocumentService $sender
	 * @param array $params
	 */
	public function onPersistentDocumentActivated($sender, $params)
	{
		$document = $params['document'];
		$this->rePublishRelatedForms($document);
	}
	
	/**
	 * @param f_persistentdocument_DocumentService $sender
	 * @param array $params
	 */
	public function onPersistentDocumentPublished($sender, $params)
	{
		$document = $params['document'];
		$this->rePublishRelatedForms($document);
	}
	
	/**
	 * @param f_persistentdocument_DocumentService $sender
	 * @param array $params
	 */
	public function onPersistentDocumentUnpublished($sender, $params)
	{
		$document = $params['document'];
		$this->rePublishRelatedForms($document);
	}
	
	/**
	 * @param f_persistentdocument_DocumentService $sender
	 * @param array $params
	 */
	public function onPersistentDocumentDeactivated($sender, $params)
	{
		$document = $params['document'];
		$this->rePublishRelatedForms($document);
	}
	
	/**
	 * @param f_persistentdocument_DocumentService $sender
	 * @param array $params
	 */
	public function onPersistentDocumentFiled($sender, $params)
	{
		$document = $params['document'];
		$this->rePublishRelatedForms($document);
	}
	
	/**
	 * @param f_persistentdocument_DocumentService $sender
	 * @param array $params
	 */
	public function onPersistentDocumentInTrash ($sender, $params)
	{
		$document = $params['document'];
		$this->rePublishRelatedForms($document);
	}
	
	/**
	 * @param f_persistentodcument_PersistentDocument $document
	 */
	private function rePublishRelatedForms($document)
	{
		if ($document instanceof users_persistentdocument_user)
		{
			foreach ($document->getInquiryformArrayInverse() as $form)
			{
				$form->getDocumentService()->publishIfPossible($form->getId());
			}
		}
		else if ($document instanceof workflow_persistentdocument_workflow)
		{
			$startTaskId = $document->getStarttaskid();
			$query = inquiry_InquiryformService::getInstance()->createQuery();
			$query->add(Restrictions::eq('startTaskId', $startTaskId));
			$query->add(Restrictions::in('publicationstatus', array('PUBLISHED', 'ACTIVE')));
			foreach ($query->find() as $form)
			{
				$form->getDocumentService()->publishIfPossible($form->getId());
			}
		}
	}
}