<?php
/**
 * Class where to put your custom methods for document inquiry_persistentdocument_answertemplate
 * @package modules.inquiry.persistentdocument
 */
class inquiry_persistentdocument_answertemplate extends inquiry_persistentdocument_answertemplatebase
{
	
	/**
	 * Return contents of answer template with var substituted by inquiry data
	 * @param inquiry_persistentdocument_inquiry $inquiry
	 */
	public function getSubstitutedContents($inquiry)
	{
		$contents = $this->getContents();
		
		$from = array();
		$to = array();
		
		$from[] = "{inquiryId}";
		$to[] = $inquiry->getId();
		
		$targetId = $inquiry->getTagetId();
		
		if ($targetId != null)
		{
			$target = DocumentHelper::getDocumentInstanceIfExists($targetId);
			
			if ($target != null)
			{
				$from[] = "{targetLabel}";
				$to[] = $target->getLabel();
			}
		
		}
		
		return str_replace($from, $to, $contents);
	}

}