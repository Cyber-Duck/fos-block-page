<?php


namespace CyberDuck\BlockPage\Extension;


use CyberDuck\BlockPage\Model\ContentBlock;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\FieldType\DBText;

class SubsiteExtension extends DataExtension
{
    private static $db = [
        'BlockTypeWhitelist' => DBText::class
    ];

    function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName("BlockTypeWhitelist");
        $fields->addFieldsToTab("Root.Main", [
            ToggleCompositeField::create(
                'BlockTypeWhitelistToggle',
                _t(__CLASS__ . '.BlockTypeWhitelistField', 'Allowed block types?'),
                [
                    CheckboxSetField::create('BlockTypeWhitelist', '', $this->owner->getContentBlocksMap())
                ]
            )->setHeadingLevel(4),
        ]);
    }

    public function getContentBlocksMap()
    {
        $blocks_map = [];

        $blocks = (array) ClassInfo::subclassesFor(ContentBlock::class, false);

        foreach($blocks as $block=>$blockClass) {
            $blocks_map[$blockClass] = singleton($blockClass)->i18n_singular_name();
        }

        asort($blocks_map);

        return $blocks_map;
    }
}
