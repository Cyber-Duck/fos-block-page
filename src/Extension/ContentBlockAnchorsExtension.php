<?php

namespace CyberDuck\BlockPage\Extension;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DataExtension;

class ContentBlockAnchorsExtension extends DataExtension
{
	public function updateAnchorsOnPage(&$anchors)
	{
		$block_anchors = [];

		if($this->owner->hasMethod('ContentBlocks')) foreach($this->owner->ContentBlocks() as $block) {
			$block_anchors = array_merge($block->getAnchorsInBlock(), $block_anchors);
		}

		$anchors = array_unique(array_merge($anchors, $block_anchors));
	}
}
