<?php
/**
 * Class where to put your custom methods for document inquiry_persistentdocument_inquiryform
 * @package modules.inquiry.persistentdocument
 */
class inquiry_persistentdocument_inquiryform extends inquiry_persistentdocument_inquiryformbase 
{
	/**
	 * @return indexer_IndexedDocument
	 */
	public function getBackofficeIndexedDocument()
	{
		$indexedDoc = parent::getBackofficeIndexedDocument();
		$indexedDoc->setStringField('module', 'form');
		return $indexedDoc;
	}
}