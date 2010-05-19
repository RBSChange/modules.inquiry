<?php
/**
 * inquiry_InquiryScriptDocumentElement
 * @package modules.inquiry.persistentdocument.import
 */
class inquiry_InquiryScriptDocumentElement extends import_ScriptDocumentElement
{
    /**
     * @return inquiry_persistentdocument_inquiry
     */
    protected function initPersistentDocument()
    {
    	return inquiry_InquiryService::getInstance()->getNewDocumentInstance();
    }
    
    /**
	 * @return f_persistentdocument_PersistentDocumentModel
	 */
	protected function getDocumentModel()
	{
		return f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName('modules_inquiry/inquiry');
	}
}