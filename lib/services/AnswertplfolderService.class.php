<?php
/**
 * inquiry_AnswertplfolderService
 * @package modules.inquiry
 */
class inquiry_AnswertplfolderService extends generic_FolderService
{
	/**
	 * @var inquiry_AnswertplfolderService
	 */
	private static $instance;
	
	/**
	 * @return inquiry_AnswertplfolderService
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
	 * @return inquiry_persistentdocument_answertplfolder
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_inquiry/answertplfolder');
	}
	
	/**
	 * Create a query based on 'modules_inquiry/answertplfolder' model.
	 * Return document that are instance of modules_inquiry/answertplfolder,
	 * including potential children.
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_inquiry/answertplfolder');
	}
	
	/**
	 * Create a query based on 'modules_inquiry/answertplfolder' model.
	 * Only documents that are strictly instance of modules_inquiry/answertplfolder
	 * (not children) will be retrieved
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createStrictQuery()
	{
		return $this->pp->createQuery('modules_inquiry/answertplfolder', false);
	}
	
	/**
	 * @param inquiry_persistentdocument_answertplfolder $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
	protected function preInsert($document, $parentNodeId)
	{
		$result = $this->createQuery()->setProjection(Projections::count('id', 'count'))->findColumn('count');
		if (f_util_ArrayUtils::firstElement($result) > 0)
		{
			throw new Exception('modules_inquiry/answertplfolder already exists');
		}
	}

}