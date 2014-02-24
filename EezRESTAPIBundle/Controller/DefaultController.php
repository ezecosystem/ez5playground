<?php

namespace GGGeek\eZ5Playground\EezRESTAPIBundle\Controller;

use eZ\Publish\Core\REST\Server\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;
use eZ\Publish\API\Repository\Values\Content\Query;
use GGGeek\eZ5Playground\EezRESTAPIBundle\Rest\ViewInterface;

class DefaultController extends BaseController
{
    /// @var array of GGGeek\eZ5Playground\EezRESTAPIBundle\Rest\ViewInterface
    protected $views = array();

    /**
     * The swiss-army-knife controller for all your needs to display content
     *
     * @param int $locationId
     * @param string $viewName
     * @param int $maxChildren -1 for unbounded, null to have the value of parameter 'eezrestapi.max_children_per_page' be used
     * @return Response
     */
    public function getContent( $locationId, $viewName, $maxChildren = null )
    {

        if ( !isset( $this->views[$viewName] ) )
        {
            return new Response( json_encode( "<error>No view '$viewName' found </error>" ), 404, array( 'content-type' => 'application/json' ) );
        }

        try
        {

            $locationService = $this->repository->getLocationService();
            $location = $locationService->loadLocation( $locationId );
            // allow the value of max children to be defined per-view (w. custom routing), or globally
            if ( $maxChildren === null )
            {
                if ( $this->container->hasParameter( 'eezrestapi.max_children_per_page' ) )
                {
                    $maxChildren =  $this->container->getParameter( 'eezrestapi.max_children_per_page' );
                }
            }
            $offset = 0;
            $siblings = null;
            $content = null;
            $data = array();
            $view = $this->views[$viewName];
            $filtersArray = $view->fetchFilter();
            $cacheExpiryLocationId = $locationId;
            if ( empty( $filtersArray ) )
            {
                $filtersArray = array( 'L', 'O', 'R', 'C', 'P', 'N' );
            }
            foreach ( $filtersArray as $filter )
            {
                switch ( strtoupper( $filter ) )
                {
                    case 'L' : $data['Location'] = $location;
                        break;
                    case 'O' :
                        if ( $content === null )
                            $content = $this->repository->getContentService()->loadContent( $locationId, null );
                        $data['Content'] = $content;
                        break;
                    case 'R' :
                        if ( $content === null )
                            $content = $this->repository->getContentService()->loadContent( $locationId, null );
                        $data['Relations'] = $this->repository->getContentService()->loadRelations( $content->getVersionInfo() );
                        break;
                    case 'C' :
                        if ( $this->request->query->has( 'offset' ) )
                        {
                            $offset = $this->request->query->get( 'offset' );
                        }
                        $data['Children'] = $locationService->loadLocationChildren( $location, $offset, $maxChildren );
                        break;
                    case 'P':
                    case 'N':
                        if ( $siblings === null )
                        {
                            $siblings = $this->getSiblings( $location );
                        }
                        if ( strtoupper( $filter ) == 'N' )
                            $data['Next'] = $siblings['Next'];
                        else
                            $data['Prev'] = $siblings['Prev'];
                        $cacheExpiryLocationId = $location->parentLocationId;
                        break;
                    default:
                        /// @todo log a warning
                }
            }
            $resultJson = self::jsonize( $view->render( $data ) );
            $response = new Response(
                json_encode( $resultJson ),
                200,
                array(
                    'content-type' => 'application/json',
                    'Vary' => 'X-User-Hash',
                    'X-Location-Id' => $cacheExpiryLocationId
                )
            );
            $response->setMaxAge( $this->container->getParameter( 'eezrestapi.cache_ttl' ) );
            $response->setPublic();
            return $response;

        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            return new Response( json_encode( "<error>No location found with id '$locationId'</error>" ), 404, array( 'content-type' => 'application/json' ) );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\UnauthorizedException $e )
        {
            return new Response( json_encode( "<error>User is not allowed to access location with id '$locationId'</error>" ), 401, array( 'content-type' => 'application/json' ) );
        }
        catch ( \Exception $e )
        {
            return new Response( json_encode( "<error>{$e->getMessage()}</error>" ), 500, array( 'content-type' => 'application/json' ) );
        }
    }

    /**
     * This method gets called by the framework with the services which get tagged as "gggeek_eezrestapi.view"
     * @param ViewInterface $view
     * @param string $alias
     */
    public function addView( ViewInterface $view, $alias )
    {
        $this->views[$alias] = $view;
    }

    /**
     * Finds the previous or next location to the given one (skipping over ones inaccessible to current user)
     *
     * @param $location
     * @return array (keys: 'Next', 'Prev')
     */
    private function getSiblings( $location )
    {
        $locationService = $this->repository->getLocationService();
        // work around the fact that current user might have no perms to access parent
        $parent = $this->repository->sudo(
            function() use ( $locationService, $location )
            {
                return $locationService->loadLocation( $location->parentLocationId );
            }
        );
        $brothers = $locationService->loadLocationChildren( $parent );
        $prevLocation = null;
        foreach ( $brothers->locations as $i => $brother )
        {
            if ( $brother->id == $location->id )
            {
                if ( $i == ( count( $brothers->locations ) - 1 ) )
                    return array( 'Prev' => $prevLocation, 'Next' => null );
                else
                    return array( 'Prev' => $prevLocation, 'Next' => $brothers->locations[$i + 1] );
            }
            $prevLocation = $brother;
        }
        return array( 'Prev' => null, 'Next' => null );
    }

    /**
     * Makes protected properties of objects visible for json encoding removing the 'ugly' chars, removes private ones
     * (in other words encodes objects as arrays)
     * @param mixed $value
     * @return mixed
     */
    public static function jsonize( $value )
    {
        if ( is_array( $value ) )
        {
            foreach( $value as $key => $val )
            {
                $value[$key] = self::jsonize( $val );
            }
            return $value;
        }
        if ( !is_object( $value ) )
        {
            return $value;
        }
        $token = chr(0) . '*' . chr(0);
        $value = (array) $value;
        foreach ( $value as $key => $val )
        {
            // private
            if ( preg_match( '/^\x00[^*]+$/', $key ) )
            {
                unset( $value[$key] );
                continue;
            }
            // protected
            if ( strpos( $key, $token ) === 0 )
            {
                unset($value[$key]);
                $key = str_replace( $token, '', $key );
            }
            $val = self::jsonize( $val );
            $value[$key] = $val;
        }
        return $value;
    }

}

?>
