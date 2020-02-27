<?php

namespace CyberDuck\BlockPage\Admin;

use CyberDuck\BlockPage\Action\GridFieldVersionedContentBlockItemRequest;
use CyberDuck\BlockPage\Model\BlockDataList;
use CyberDuck\BlockPage\Model\ContentBlock;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\Filters\PartialMatchFilter;
use SilverStripe\View\ViewableData;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

class BlockAdmin extends ModelAdmin
{
    private $filterRequest;

    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id , $fields);
        $form
            ->Fields()
            ->fieldByName($this->sanitiseClassName($this->modelClass))
            ->getConfig()
            ->removeComponentsByType(GridFieldOrderableRows::class)
            ->getComponentByType(GridFieldDetailForm::class)
            ->setItemRequestClass(GridFieldVersionedContentBlockItemRequest::class);

        return $form;
    }
}
