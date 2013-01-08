<?php
/**
 * inquiry_AnswertemplateScriptDocumentElement
 * @package modules.inquiry.persistentdocument.import
 */
class inquiry_AnswertemplateScriptDocumentElement extends import_ScriptDocumentElement
{
    /**
     * @return inquiry_persistentdocument_answertemplate
     */
    protected function initPersistentDocument()
    {
    	return inquiry_AnswertemplateService::getInstance()->getNewDocumentInstance();
    }
    
    /**
	 * @return f_persistentdocument_PersistentDocumentModel
	 */
	protected function getDocumentModel()
	{
		return f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName('modules_inquiry/answertemplate');
	}
}