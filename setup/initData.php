<?php
/**
 * @package modules.inquiry.setup
 */
class inquiry_Setup extends object_InitDataSetup
{
	public function install()
	{
		$this->executeModuleScript('init.xml');
		$this->executeModuleScript('default-workflow-with-recall.xml');
		$this->executeModuleScript('default-workflow-without-recall.xml');
		
		$mbs = uixul_ModuleBindingService::getInstance();
		$mbs->addImportInPerspective('form', 'inquiry', 'form.perspective');
		$mbs->addImportInActions('form', 'inquiry', 'form.actions');
		$result = $mbs->addImportForm('form', 'modules_inquiry/inquiryform');
		if ($result['action'] == 'create')
		{
			uixul_DocumentEditorService::getInstance()->compileEditorsConfig();
		}
		change_PermissionService::getInstance()->addImportInRight('form', 'inquiry', 'form.rights');
	}

	/**
	 * @return String[]
	 */
	public function getRequiredPackages()
	{
		// Return an array of packages name if the data you are inserting in
		// this file depend on the data of other packages.
		// Example:
		// return array('modules_website', 'modules_users');
		return array();
	}
}