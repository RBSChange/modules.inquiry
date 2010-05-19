<?php
/**
 * inquiry_InquiryformScriptDocumentElement
 * @package modules.inquiry.persistentdocument.import
 */
class inquiry_InquiryformScriptDocumentElement extends import_ScriptDocumentElement
{
    /**
     * @return inquiry_persistentdocument_inquiryform
     */
    protected function initPersistentDocument()
    {
    	return inquiry_InquiryformService::getInstance()->getNewDocumentInstance();
    }
    
    /**
	 * @return f_persistentdocument_PersistentDocumentModel
	 */
	protected function getDocumentModel()
	{
		return f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName('modules_inquiry/inquiryform');
	}
}