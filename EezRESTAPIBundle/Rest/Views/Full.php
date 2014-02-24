<?php

namespace GGGeek\eZ5Playground\EezRESTAPIBundle\Rest\Views;

use GGGeek\eZ5Playground\EezRESTAPIBundle\Controller\DefaultController;
use GGGeek\eZ5Playground\EezRESTAPIBundle\Rest\ViewInterface;

class Full implements ViewInterface
{
    public function fetchFilter()
    {
        // empty array means "fetch all"
        return array();
    }

    /**
     * The simplest way to encode results: just like json_encode but deals better with protected/private object members
     */
    public function render( $data )
    {
        return DefaultController::jsonize($data);
    }
}

?>
