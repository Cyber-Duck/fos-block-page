<?php

namespace CyberDuck\BlockPage\Model;
use SilverStripe\ORM\DataObjectSchema;

use Page;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldVersionedState;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TabSet;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\Filters\SearchFilter;
use SilverStripe\ORM\Search\SearchContext;
use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionProvider;
use SilverStripe\Versioned\Versioned;
use SilverStripe\Versioned\VersionedGridFieldItemRequest;
use SilverStripe\View\SSViewer;

class ContentBlock extends DataObject implements PermissionProvider
{
    private static $table_name = 'ContentBlock';

    private static $db = [];

    private static $belongs_many_many = [
        'Pages' => Page::class,
    ];

    private static $owned_by = [
        'Pages'
    ];

    private static $extensions = [
        Versioned::class
    ];

    private static $versioned_gridfield_extensions = true;

    private static $singular_name = 'Content Block';

    private static $plural_name = 'Content Blocks';

    private static $summary_fields = [
        'Thumbnail'   => '',
        'ID'          => 'ID',
        'BlockType'   => 'Content type',
        'Title'       => 'Title',
        'Pages.Count' => 'Pages'
    ];

    public function searchableFields()
    {
        $fields = [
            'ID' => [
                'filter' => 'ExactMatchFilter',
                'title' => 'ID',
                'field' => TextField::class,
            ],
            'ClassName' => [
                'filter' => 'ExactMatchFilter',
                'title' => 'Block Type',
                'field' => DropdownField::create('ClassName')->setSource(ContentBlock::get()->map('ClassName', 'ClassName')->toArray())->setEmptyString('-- Content Type --'),
            ],
            'Title' => [
                'filter' => 'PartialMatchFilter',
                'title' => 'Title',
                'field' => TextField::class,
            ],
        ];

        $this->extend("updateSearchableFields", $fields);

        return $fields;
    }

    public function getThumbnail()
    {
        return DBField::create_field('HTMLText', sprintf('<img src="%s" height="20">', $this->config()->get('preview')));
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('Pages');

        if($this->getAction() == 'new') {
            return $this->getCMSSelectionFields($fields);
        } else {
            $editor = GridFieldConfig_RelationEditor::create();
            $grid = new GridField('Pages', 'Pages', $this->Pages(), $editor);
            $grid->getConfig()
                ->removeComponentsByType(GridFieldAddNewButton::class);
            $fields->addFieldToTab('Root.Pages', $grid);
        }
        return $fields;
    }

    public function getTemplateHolder()
    {
        return $this->renderWith(['type' => 'Blocks', 'ContentBlock_holder']);
    }

    public function getTemplate()
    {
        $template = SSViewer::chooseTemplate(['type' => "Blocks", $this->ClassName]);

        if($template) {
            return $this->renderWith($template);
        }
    }

    public function getAction()
    {
        $path = explode('/', Controller::curr()->getRequest()->getURL());
        return array_pop($path);
    }

    public function getBlockType()
    {
        return $this->owner->ClassName::config()->get('title');
    }

    private function getCMSSelectionFields(FieldList $fields)
    {
        $fields->removeByName('Root');
        // fields used in the inital selection request
        $session = Controller::curr()->getRequest()->getSession();
        $fields->push(HiddenField::create('BlockRelationID')->setValue($session->get('BlockRelationID')));
        $fields->push(HiddenField::create('BlockRelationClass')->setValue($session->get('BlockRelationClass')));

        // create the selection tab and options
        $fields->push(TabSet::create('Root', Tab::create('Main')));

        $rules = (array) Config::inst()->get(ContentBlock::class, 'restrict');

        if(array_key_exists($session->get('BlockRelationClass'), $rules)) {
            $classes = $rules[$session->get('BlockRelationClass')];
        } else {
            $classes = (array) Config::inst()->get(ContentBlock::class, 'blocks');
        }

        $options = [];
        foreach($classes as $class) {
            $options[$class] = DBField::create_field('HTMLText', Controller::curr()
                ->customise([
                    'Preview'     => $class::config()->get('preview'),
                    'Title'       => $class::config()->get('title'),
                    'Description' => $class::config()->get('description')
                ])
                ->renderWith('/Includes/ContentBlockOption')
            );
        }
        $checked = key(array_slice($options, 0, 1, true));

        $fields->addFieldToTab('Root.Main', OptionsetField::create('ContentBlock', false, $options, $checked));

        return $fields;
    }

    public function providePermissions()
    {
        return [
            'VIEW_CONTENT_BLOCKS' => [
                'name' => 'View content blocks',
                'help' => 'Allow viewing page content blocks',
                'category' => 'Page - Content Blocks',
                'sort' => 100
            ],
            'CREATE_CONTENT_BLOCKS' => [
                'name' => 'Create content blocks',
                'help' => 'Allow creating page content blocks',
                'category' => 'Page - Content Blocks',
                'sort' => 100
            ],
            'EDIT_CONTENT_BLOCKS' => [
                'name' => 'Edit content blocks',
                'help' => 'Allow editing page content blocks',
                'category' => 'Page - Content Blocks',
                'sort' => 100
            ],
            'DELETE_CONTENT_BLOCKS' => [
                'name' => 'Delete content blocks',
                'help' => 'Allow deleting page content blocks',
                'category' => 'Page - Content Blocks',
                'sort' => 100
            ]
        ];
    }

    public function canView($member = null, $context = [])
    {
        return Permission::check('VIEW_CONTENT_BLOCKS', 'any', $member);
    }

    public function canCreate($member = null, $context = [])
    {
        return Permission::check('CREATE_CONTENT_BLOCKS', 'any', $member);
    }

    public function canEdit($member = null, $context = [])
    {
        return Permission::check('EDIT_CONTENT_BLOCKS', 'any', $member);
    }

    public function canDelete($member = null, $context = [])
    {
        return Permission::check('DELETE_CONTENT_BLOCKS', 'any', $member);
    }

	public function getAnchorsInBlock()
	{
		$dbSchema = Injector::inst()->get(DataObjectSchema::class);

		$anchors = [];

		$anchors[] = sprintf("block-%s", $this->ID);

		$fields = $dbSchema->databaseFields($this->ClassName);

		foreach($fields as $field => $type) {
			if($type === 'HTMLText') {
				$content = $this->getField($field);

				if($content) {
					// Get anchors using the same regex as AnchorSelectorField
					$parseSuccess = preg_match_all("/\\s+(name|id)\\s*=\\s*([\"'])([^\\2\\s>]*?)\\2|\\s+(name|id)\\s*=\\s*([^\"']+)[\\s +>]/im", $content, $matches);

					if($parseSuccess) {
						// Cleanup results and merge them to the results,
						$anchors = array_merge($anchors, array_values(array_unique(array_filter(array_merge($matches[3], $matches[5])))));
					}
				}
			}
		}

		$this->extend('updateAnchorsInBlock', $anchors);

		return $anchors;
	}
}
