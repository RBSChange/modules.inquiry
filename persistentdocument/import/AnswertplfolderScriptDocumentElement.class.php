<?php
/**
 * inquiry_AnswertplfolderScriptDocumentElement
 * @package modules.inquiry.persistentdocument.import
 */
class inquiry_AnswertplfolderScriptDocumentElement extends import_ScriptDocumentElement
{
    /**
     * @return inquiry_persistentdocument_answertplfolder
     */
    protected function initPersistentDocument()
    {
    	return inquiry_AnswertplfolderService::getInstance()->getNewDocumentInstance();
    }
    
    /**
	 * @return f_persistentdocument_PersistentDocumentModel
	 */
	protected function getDocumentModel()
	{
		return f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName('modules_inquiry/answertplfolder');
	}
}