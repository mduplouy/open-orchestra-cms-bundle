<?php

/*
 * Business & Decision - Commercial License
 *
 * Copyright 2014 Business & Decision.
 *
 * All rights reserved. You CANNOT use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell this Software or any parts of this
 * Software, without the written authorization of Business & Decision.
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * See LICENSE.txt file for the full LICENSE text.
 */

namespace PHPOrchestra\CMSBundle\Controller\Block;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * SearchResult Controller
 */
class SearchResultController extends Controller
{
    /**
     * Render the search's result block
     *
     * @param string $nodeId node identifiant
     * @param int    $nbdoc number of documents per page
     * @param array  $fielddisplayed array of field name which are display
     * @param int    $nbspellcheck number of spell check result
     * @param int    $limitField number of letter per field
     * @param array  $facets
     * @param array  $filter array of filter
     * @param array  $optionsearch array of option
     * @param array  $optionsdismax array of option for dismax component
     * @param int    $page page number
     * @param array  $_page_parameters additional parameters extracted from url
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(
        $nodeId,
        $nbdoc,
        $fielddisplayed,
        $nbspellcheck,
        $limitField = 50,
        $facets = array(),
        $filter = array(),
        $optionsearch = array(),
        $optionsdismax = array(),
        $page = 1,
        $_page_parameters = array()
    ) {
        return $this->show(
            $nodeId,
            $nbdoc,
            $fielddisplayed,
            $nbspellcheck,
            $limitField,
            $facets,
            $filter,
            $optionsearch,
            $optionsdismax,
            $page,
            $_page_parameters
        );
    }

    /**
     * @see \PHPOrchestra\CMSBundle\Controller\Block\BlockInterface::showAction()
     */
    public function showBackAction(
        $nodeId,
        $nbdoc,
        $fielddisplayed,
        $nbspellcheck,
        $limitField = 50,
        $facets = array(),
        $filter = array(),
        $optionsearch = array(),
        $optionsdismax = array(),
        $page = 1,
        $_page_parameters = array()
    ) {
        return $this->show(
            $nodeId,
            $nbdoc,
            $fielddisplayed,
            $nbspellcheck,
            $limitField,
            $facets,
            $filter,
            $optionsearch,
            $optionsdismax,
            $page,
            $_page_parameters
        );
    }

    /**
     * Get list of words find by auto-completion with solr
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function autocompleteAction(Request $request)
    {
        // Take words in the search word field
        $terms = $request->query->get('term');

        // Create facet query
        $client = $this->get('solarium.client');
        $query = $client->createSelect();
        $query->setQuery('*:*');
        $facetSet = $query->getFacetSet();
        $facetSet->setLimit($request->get('limit', 5));
        $facet = $facetSet->createFacetField('autocomplete')->setField('suggest');
        $facet->setMinCount(1);
        $facet->setPrefix($terms);
        $resultset = $client->select($query);

        $result = array();
        foreach ($resultset->getFacetSet()->getFacets() as $facet) {
            $values = $facet->getValues();
            foreach ($values as $name => $value) {
                $result[] = $name;
            }
        }
        return new JsonResponse($result);
    }

    /**
     * Search in solr
     *
     * @param string $data searching word
     * @param $nbspellcheck
     * @param array $optionSearch array of option to the search
     * @param array $facets array of option to the facets
     * @param $filters
     * @param $dismax
     *
     * @return Solarium\QueryType\Select\Result\Result
     */
    protected function callResearch($data, $nbspellcheck, $optionSearch, $facets, $filters, $dismax)
    {
        // Research
        $searchManager = $this->get('php_orchestra_indexation.search_manager');
        $query = $searchManager->search($data, null, $optionSearch);
    
        // Spell check setting
        $query = $searchManager->spellCheck($query, $data, $nbspellcheck);

        // Faceting
        if (isset($facets) && !empty($facets)) {
            $this->callFacet($query, $searchManager, $facets);
        }
        
        // Filtering
        if (isset($filters) && !empty($filters)) {
            if (!isset($filters['name'])) {
                // array of filter
                foreach ($filters as $filter) {
                    $searchManager->filter($query, $filter['name'], $filter['field']);
                }
            } else {
                $searchManager->filter($query, $filters['name'], $filters['field']);
            }
        }
        
        /*/ Dismax
        if (isset($dismax) && !empty($dismax)) {
            if (isset($dismax['mm'])) {
                $searchManager->disMax($query, $dismax['fields'], $dismax['boost'], $dismax['mm']);
            } else {
                $searchManager->disMax($query, $dismax['fields'], $dismax['boost']);
            }
        }*/
    
        return $this->result($query, $searchManager);
    }

    /**
     * Create a filter query
     *
     * @param string $data search word
     * @param string $filter query filter
     * @param string $facetName facet name
     */
    protected function callFilter($data, $filter, $facetName)
    {
        $client = $this->get('solarium.client');
        $query  = $client->createSelect();
    
        $query->setQuery($data);
        $query->createFilterQuery($facetName)->setQuery($filter);
    
        return $client->select($query);
    }

    /**
     * Return result of the query
     *
     * @param Solarium\QueryType\Select\Query\Query $query
     * @param PHPOrchestra\BlockBundle\IndexCommand\SolrSearchCommand $search
     *
     * @return mixed|NULL|array|Solarium\QueryType\Select\Result\Result
     */
    protected function result($query, $search)
    {
        // Result
        $resultset = $this->get('php_orchestra_indexation.search_manager')->select($query);
    
        if ($resultset->getNumFound() < 1) {
            $result = array();
            $spellcheck = $resultset->getSpellcheck();
    
            if (isset($spellcheck)) {
                $suggestions = $resultset->getSpellcheck()->getSuggestions();
    
                if (isset($suggestions)) {
                    foreach ($suggestions as $suggest) {
                        $search->search($suggest->getword(), $query);
    
                        $result[] = $this->get('php_orchestra_indexation.search_manager')->select($query);
                    }
                }
            }
            return $result;
        } else {
            return $resultset;
        }
    }

    /**
     * Call facet services
     *
     * @param Solarium\QueryType\Select\Query\Query $query
     * @param PHPOrchestra\BlockBundle\IndexCommand\SolrSearchCommand $search search services
     * @param array $facets
     */
    protected function callFacet($query, $search, $facets)
    {
        $facetSet = $query->getFacetSet();

        if (isset($facets['facetField'])) {
            if (isset($facets['facetField']['name']) && isset($facets['facetField']['field'])) {
                if (isset($facets['facetField']['options'])) {
                    $search->facetField(
                        $facetSet,
                        $facets['facetField']['name'],
                        $facets['facetField']['field'],
                        $facets['facetField']['options']
                    );
                } else {
                    $search->facetField(
                        $facetSet,
                        $facets['facetField']['name'],
                        $facets['facetField']['field']
                    );
                }
            }
        }
        if (isset($facets['facetQuery'])) {
            if (isset($facets['facetQuery']['field']) && isset($facets['facetQuery']['query'])) {
                $search->facetQuery(
                    $facetSet,
                    $facets['facetQuery']['field'],
                    $facets['facetQuery']['query']
                );
            }
        }
        if (isset($facets['multiQuery'])) {
            if (isset($facets['multiQuery']['field']) && isset($facets['multiQuery']['queries'])) {
                $search->facetmultiQUery(
                    $facetSet,
                    $facets['multiQuery']['field'],
                    $facets['multiQuery']['queries']
                );
            }
        }
        if (isset($facets['facetRange'])) {
            if (isset($facets['facetRange']['name']) && isset($facets['field'])
                    && isset($facets['facetRange']['start']) && isset($facets['facetRange']['gap'])
                    && isset($facets['facetRange']['end'])) {
                $search->facetRange(
                    $facetSet,
                    $facets['facetRange']['name'],
                    $facets['facetRange']['field'],
                    $facets['facetRange']['start'],
                    $facets['facetRange']['gap'],
                    $facets['facetRange']['end']
                );
            }
        }
    }

    /**
     * Call search template
     *
     * @param string $data search word
     * @param Solarium\QueryType\Result\Result $resultSet
     * @param string $nodeId identifiant of node
     * @param int $page number of page
     * @param int $nbdoc number of documents per page selected by the user
     * @param array $fields array of field displayed
     * @param int $limitField number of letters per field
     * @param array $facets array if they have facets
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    protected function callTemplate($data, $resultSet, $nodeId, $page, $nbdoc, $fields, $limitField, $facets = array())
    {
        $firstField = array_shift($fields);
        if (isset($facets)) {
            return $this->render(
                "PHPOrchestraCMSBundle:Block/SearchResult:show.html.twig",
                array(
                    'data' => $data,
                    'resultset' => $resultSet,
                    'nodeId' => $nodeId,
                    'page' => $page,
                    'nbdocs' => $nbdoc,
                    'fieldsdisplayed' => $fields,
                    'facetsArray' => $facets,
                    'baseUrl' => $this->container->get('router')->getContext()->getBaseUrl(),
                    'firstField' => $firstField,
                    'limitField' => $limitField
                )
            );
        } else {
            return $this->render(
                "PHPOrchestraCMSBundle:Block/SearchResult:show.html.twig",
                array(
                    'data' => $data,
                    'resultset' => $resultSet,
                    'nodeId' => $nodeId,
                    'page' => $page,
                    'nbdocs' => $nbdoc,
                    'fieldsdisplayed' => $fields,
                    'baseUrl' => $this->container->get('router')->getContext()->getBaseUrl(),
                    'firstField' => $firstField,
                    'limitField' => $limitField
                )
            );
        }
    }


    /**
     * @param string $nodeId node identifiant
     * @param int    $nbdoc number of documents per page
     * @param array  $fielddisplayed array of field name which are display
     * @param int    $nbspellcheck number of spell check result
     * @param int    $limitField number of letter per field
     * @param array  $facets
     * @param array  $filter array of filter
     * @param array  $optionsearch array of option
     * @param array  $optionsdismax array of option for dismax component
     * @param int    $page page number
     * @param array  $_page_parameters additional parameters extracted from url
     *
     * @return Response
     */
    protected function show($nodeId, $nbdoc, $fielddisplayed, $nbspellcheck, $limitField, $facets, $filter, $optionsearch, $optionsdismax, $page, $_page_parameters)
    {
        if (isset($_page_parameters['query']) && is_array($_page_parameters['query'])) {
            $paramQuery = $_page_parameters['query'];
            if (isset($paramQuery['autocomplete_search']) || isset($paramQuery['terms'])) {

                if (isset($paramQuery['autocomplete_search'])) {
                    $data = $paramQuery['autocomplete_search']['terms'];
                } else {
                    $data = $paramQuery['terms'];
                }

                if (isset($paramQuery['page'])) {
                    $page = $paramQuery['page'];
                }

                $optionsearch = array_merge(
                    array('start' => ($page * $nbdoc) - $nbdoc, 'rows' => $page * $nbdoc),
                    $optionsearch
                );

                // Result of search
                $resultSet = $this->callResearch(
                    $data,
                    $nbspellcheck,
                    $optionsearch,
                    $facets,
                    $filter,
                    $optionsdismax
                );

                // Call template
                return $this->callTemplate(
                    $data,
                    $resultSet,
                    $nodeId,
                    $page,
                    $nbdoc,
                    $fielddisplayed,
                    $limitField,
                    $facets
                );

            } else {
                // Filter
                if (isset($paramQuery['data']) && isset($paramQuery['filter']) && isset($paramQuery['facetname'])) {

                    if (isset($paramQuery['page'])) {
                        $page = $paramQuery['page'];
                    }

                    // Result of filter query
                    $resultSet = $this->callFilter($paramQuery['data'], $paramQuery['filter'], $paramQuery['facetname']);

                    // Call template
                    return $this->callTemplate(
                        $paramQuery['data'],
                        $resultSet,
                        $nodeId,
                        $page,
                        $nbdoc,
                        $fielddisplayed,
                        $limitField,
                        $facets
                    );
                }
            }
        }

        return new Response($this->get('translator')->trans('No results found'));
    }
}
