<?php
/**
 * inquiry_BlockInquirylistAction
 * @package modules.inquiry.lib.blocks
 */
class inquiry_BlockInquirylistAction extends website_TaggerBlockAction
{
	/**
	 * @see website_BlockAction::execute()
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @return String
	 */
	function execute($request, $response)
	{
		$currentUser = users_UserService::getInstance()->getCurrentFrontEndUser();
		if ($this->isInBackofficeEdition() || $currentUser === null)
		{
			return website_BlockView::NONE;
		}
				
		$nbItemPerPage = $this->getConfiguration()->getNbItemPerPage();
		$inqiuries = inquiry_InquiryService::getInstance()->getByAuthor($currentUser);
		$pageNumber = $request->getParameter('page');
		if (!is_numeric($pageNumber) || $pageNumber < 1 || $pageNumber > ceil(count($inqiuries) / $nbItemPerPage))
		{
			$pageNumber = 1;
		}
		$paginator = new paginator_Paginator('inquiry', $pageNumber, $inqiuries, $nbItemPerPage);			
		$request->setAttribute('inquiries', $paginator);
		
		return website_BlockView::SUCCESS;
	}
}