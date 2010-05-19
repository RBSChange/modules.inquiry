<?php
/**
 * inquiry_LoadMessagesByTargetAction
 * @package modules.message.actions
 */
class inquiry_LoadMessagesByTargetAction extends f_action_BaseJSONAction
{
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
		$targetId = $this->getDocumentIdFromRequest($request);
		
		$result = inquiry_MessageService::getInstance()->getInfosByTargetId($targetId, true);
				
		return $this->sendJSON($result);
	}
}