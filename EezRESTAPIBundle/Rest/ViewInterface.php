<?php
/**
 * @author gaetano giunta
 * @copyright (C) 2014 G. Giunta
 * @license
 */

namespace GGGeek\eZ5Playground\EezRESTAPIBundle\Rest;


interface ViewInterface
{
    /**
     * @return array an array containing the list of things that will be fetched and passed to the render() call
     */
    public function fetchFilter();

    /**
     * Receives an array with the repositories entities fetched, returns data which will be encoded as json in response
     *
     * @param array $data An array
     * @return mixed
     */
    public function render( $data );
}