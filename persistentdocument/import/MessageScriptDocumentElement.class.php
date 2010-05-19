<?php
/**
 * inquiry_MessageScriptDocumentElement
 * @package modules.inquiry.persistentdocument.import
 */
class inquiry_MessageScriptDocumentElement extends import_ScriptDocumentElement
{
    /**
     * @return inquiry_persistentdocument_message
     */
    protected function initPersistentDocument()
    {
    	return inquiry_MessageService::getInstance()->getNewDocumentInstance();
    }
    
    /**
	 * @return f_persistentdocument_PersistentDocumentModel
	 */
	protected function getDocumentModel()
	{
		return f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName('modules_inquiry/message');
	}
}