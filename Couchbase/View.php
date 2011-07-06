<?php
/**
 * Define a Couchbase query.
 *
 * @package Couchbase
 * @license Apache 2.0
 */

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

    function getResult($options = array())
    {
        return new Couchbase_ViewResult(
            $this->db->couchdb->view($this->ddoc_name, $this->name, $options)
        );
    }

    function getResultByKey($key, $options = array())
    {
        return $this->getResult(array_merge($options, array("key" => $key)));
    }

    function getResultByRange($start, $end = null, $options = array())
    {
        $key_options = $startkey_options = $endkey_options = array();

        if(is_array($start)) {
            // TODO: throw warning if either is empty
            $startkey_options = array("startkey" => $start[0], "startkey_docid" => $start[1]);
        } else {
            $startkey_options = array("startkey" => $start);
        }

        if(is_array($end)) {
            // TODO: throw warning if either is empty
            $endkey_options = array("endkey" => $end[0], "endkey_docid" => $end[1]);
        } else {
            $endkey_options = array("endkey" => $end);
        }

        $key_options = array_merge($startkey_options, $endkey_options);
        return $this->getResult(array_merge($options, $key_options));
    }

    function getResultPaginator($rowsPerPage = 10, $pageKey = null, $options = array())
    {
        return new Couchbase_ViewResultPaginator($this, $rowsPerPage, $pageKey, $options);
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
