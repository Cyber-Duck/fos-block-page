<?php

namespace CyberDuck\BlockPage\Extension;

use CyberDuck\BlockPage\Action\GridFieldVersionedContentBlockItemRequest;
use CyberDuck\BlockPage\Model\ContentBlock;
use CyberDuck\BlockPage\Model\CustomGridFieldAddExistingAutocompleter;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField_ActionMenu;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldPageCount;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataObject;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

class BlockPageExtension extends DataExtension
{
    private static $db = [];

    private static $many_many = [
        'ContentBlocks' => ContentBlock::class
    ];

    private static $many_many_extraFields = [
        'ContentBlocks' => [
            'SortBlock' => 'Int'
        ]
    ];

    private static $owns = [
        'ContentBlocks'
    ];

    public function updateCMSFields(FieldList $fields)
    {
        if ($this->owner->ID > 0) {
            $contentBlocksFieldConfig = GridFieldConfig::create();
            $contentBlocksFieldConfig->addComponent(new GridFieldButtonRow('before'));
            $contentBlocksFieldConfig->addComponent(new GridFieldAddNewButton('buttons-before-left'));
            $contentBlocksFieldConfig->addComponent(new CustomGridFieldAddExistingAutocompleter('buttons-before-right'));
            $contentBlocksFieldConfig->addComponent(new GridFieldToolbarHeader());
            $contentBlocksFieldConfig->addComponent(new GridFieldSortableHeader());
            $contentBlocksFieldConfig->addComponent(new GridFieldFilterHeader());
            $contentBlocksFieldConfig->addComponent(new GridFieldDataColumns());
            $contentBlocksFieldConfig->addComponent(new GridFieldEditButton());
            $contentBlocksFieldConfig->addComponent(new GridFieldDeleteAction(true));
            $contentBlocksFieldConfig->addComponent(new GridField_ActionMenu());
            $contentBlocksFieldConfig->addComponent(new GridFieldDetailForm());
            $contentBlocksFieldConfig->addComponent(new GridFieldOrderableRows('SortBlock'));
            $contentBlocksFieldConfig->getComponentByType(GridFieldDetailForm::class)->setItemRequestClass(GridFieldVersionedContentBlockItemRequest::class);

            $contentBlocksField = GridField::create(
                'ContentBlocks',
                'Content Blocks',
                $this->owner->ContentBlocks(),
                $contentBlocksFieldConfig
            );

            $session = Controller::curr()->getRequest()->getSession();
            $session->set('BlockRelationID', $this->owner->ID);
            $session->set('BlockRelationClass', $this->owner->ClassName);

            $fields->addFieldToTab('Root.ContentBlocks', $contentBlocksField);
        } else {
            $fields->addFieldToTab('Root.ContentBlocks', LiteralField::create(false, 'Please save this block to start adding items<br><br>'));
        }
    }
}
