<?php

namespace CyberDuck\BlockPage\Model;

use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\ORM\DataList;
use SilverStripe\View\SSViewer;

class CustomGridFieldAddExistingAutocompleter extends GridFieldAddExistingAutocompleter
{
    public function doSearch($gridField, $request)
    {
        $dataClass = $gridField->getModelClass();
        $allList = $this->searchList ? $this->searchList : DataList::create($dataClass);

        $searchFields = ($this->getSearchFields())
            ? $this->getSearchFields()
            : $this->scaffoldSearchFields($dataClass);
        if (!$searchFields) {
            throw new LogicException(
                sprintf(
                    'GridFieldAddExistingAutocompleter: No searchable fields could be found for class "%s"',
                    $dataClass
                )
            );
        }

        $params = array();
        foreach ($searchFields as $searchField) {
            $name = (strpos($searchField, ':') !== false) ? $searchField : "$searchField:StartsWith";
            $params[$name] = $request->getVar('gridfield_relationsearch');
        }
        $results = $allList
            ->subtract($gridField->getList())
            ->filterAny($params)
            ->sort(strtok($searchFields[0], ':'), 'ASC')
            ->limit($this->getResultsLimit());

        $json = array();
        Config::nest();
        SSViewer::config()->update('source_file_comments', false);
        $viewer = SSViewer::fromString($this->resultsFormat);
        $viewer->setTemplate([]);
        foreach ($results as $result) {
            $title = Convert::html2raw($viewer->process($result));
            $json[] = array(
                'label' => $title,
                'value' => $title,
                'id' => $result->ID,
            );
        }
        Config::unnest();
        $response = new HTTPResponse(json_encode($json));
        $response->addHeader('Content-Type', 'application/json');
        return $response;
    }
}
