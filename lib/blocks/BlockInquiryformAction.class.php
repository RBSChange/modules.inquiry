<?php
/**
 * inquiry_BlockInquiryformAction
 * @package modules.inquiry.lib.blocks
 */
class inquiry_BlockInquiryformAction extends form_BlockFormBaseAction
{
	/**
	 * @param inquiry_persistentdocument_inquiryform $form
	 * @param f_mvc_Request $request
	 * @return string
	 */
	protected function checkAccess($form, $request)
	{
		if ($form->getSecured() && users_UserService::getInstance()->getCurrentFrontEndUser() === null)
		{
			$agaviUser = Controller::getInstance()->getContext()->getUser();
			$agaviUser->setAttribute('illegalAccessPage', $_SERVER["REQUEST_URI"]);
			$this->addError(LocaleService::getInstance()->transFO('m.inquiry.frontoffice.error-secured-form', array('ucf')));
			return false;
		}
		return true;
	}
}