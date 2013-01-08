<?php
/**
 * inquiry_AnswertemplateService
 * @package modules.inquiry
 */
class inquiry_AnswertemplateService extends f_persistentdocument_DocumentService
{
	/**
	 * @var inquiry_AnswertemplateService
	 */
	private static $instance;
	
	/**
	 * @return inquiry_AnswertemplateService
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
	 * @return inquiry_persistentdocument_answertemplate
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_inquiry/answertemplate');
	}
	
	/**
	 * Create a query based on 'modules_inquiry/answertemplate' model.
	 * Return document that are instance of modules_inquiry/answertemplate,
	 * including potential children.
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_inquiry/answertemplate');
	}
	
	/**
	 * Create a query based on 'modules_inquiry/answertemplate' model.
	 * Only documents that are strictly instance of modules_inquiry/answertemplate
	 * (not children) will be retrieved
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createStrictQuery()
	{
		return $this->pp->createQuery('modules_inquiry/answertemplate', false);
	}
	
	private $varNames = array("inquiryId", "targetLabel");
	
	/**
	 * @return array
	 */
	public function getVarsInfo()
	{
		$ls = LocaleService::getInstance();
		$varsInfo = array();
		foreach ($this->varNames as $varName)
		{
			$varsInfo["{" . $varName . "}"] = $ls->transBO("m.inquiry.document.answertemplate.var-" . strtolower($varName), array('ucf'));
		}
		asort($varsInfo);
		return $varsInfo;
	}
	
	/**
	 * @param inquiry_persistentdocument_answertemplate $document
	 * @param string[] $propertiesNames
	 * @param array $formProperties
	 * @param integer $parentId
	 */
	public function addFormProperties($document, $propertiesNames, &$formProperties, $parentId = null)
	{
		$varsInfo = array();
		foreach ($this->getVarsInfo() as $varCode => $varLabel)
		{
			$varsInfo[] = array("value" => $varCode, "label" => $varLabel);
		}
		$formProperties["varsinfo"] = $varsInfo;
	}
}