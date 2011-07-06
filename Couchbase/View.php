<?php
/**
 * Define a Couchbase query.
 *
 * @package Couchbase
 * @license Apache 2.0
 */

class Couchbase_ResultsPaginator implements Iterator
{
    var $view;
    var $rowsPerPage;
    var $page_key = null;
    var $options = array();

    function __construct($view, $rowsPerPage = 10, $pageKey = null, $options = array())
    {
        $this->view = $view;
        $this->rowsPerPage = $rowsPerPage;
        $this->page_key = $pageKey;
        $this->options = $options;
    }

    function rewind()
    {
        $this->page_key = null;
    }

    function current()
    {
        // TODO: startkey_docid/endkey_docid
        $options = array_merge($this->options,
            array("limit" => $this->rowsPerPage));
        $result = $this->view->getResultsByRange($page_key, null, $options);
        return $result;
    }

    function key()
    {
        return $this->page_key;
    }

    function next()
    {
        // TODO: startkey_docid/endkey_docid
        $options = array_merge($this->options,
            array("limit" => $this->rowsPerPage + 1));
        $result = $this->view->getResultsByRange(
            $this->page_key,
            null,
            $options
        );

        if($result->rows[$this->rowsPerPage]->key) {
            $this->page_key = $result->rows[$this->rowsPerPage]->key;
        } else {
            $this->page_key = false;
        }

        unset($result->rows[$this->rowsPerPage]);
        return $result;
    }

    function valid()
    {
        $this->page_key !== false;
    }
}

/*
TODO: Add query options and different languages.
*/
class Couchbase_View
{
    var $_id;
    var $_rev;
    var $db;
    var $view_definition;

    function __construct()
    {
        $this->view_definition = new Couchbase_ViewDefinition;
    }

    function getResults($options = array())
    {
        return new Couchbase_ViewResult(
            $this->db->couchdb->view($this->ddoc_name, $this->name, $options)
        );
    }

    // alalalalalias
    function getResult($options = array()) {
        return $this->getResults($options);
    }

    function getResultsByKey($key, $options = array()) {
        return $this->getResults(array_merge($options, array("key" => $key)));
    }

    function getResultsByRange($start, $end = null, $options = array()) {
        return $this->getResults(array_merge($options, array(
            "startkey" => $start,
            "endkey" => $end
        )));
    }

    function getResultsPaginator($rowsPerPage = 10, $pageKey = null, $options = array())
    {
        return new Couchbase_ResultsPaginator($this, $rowsPerPage, $pageKey, $options);
    }

    function setMapFunction($code)
    {
        $this->view_definition->setMapFunction($code);
    }

    function setReduceFunction($code)
    {
        $this->view_definition->setReduceFunction($code);
    }
}

class Couchbase_ViewDefinition
{
    /**
     * Source code for map function. Currently must be JavaScript.
     *
     * @var string JavaScript map function.
     */
    var $map = "";

    /**
     * Source code for reduce function. Currently must be JavaScript.
     *
     * @var string JavaScript reduce function.
     */
    var $reduce = null;

    /**
     * Add map function code to the query definition.
     *
     * @param string $code map function code.
     * @return void
     */
    function setMapFunction($code)
    {
        $this->map = $code;
    }

    /**
     * Add reduce function code to the query definition.
     *
     * @param string $code reduce function code
     * @return void
     */
    function setReduceFunction($code)
    {
        $this->reduce = $code;
    }
}
