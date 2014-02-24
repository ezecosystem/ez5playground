<?php
/**
 * Overrides the twig extension from the eZ Kernel to support an extra parameter in ez_render_field
 */

namespace GGGeek\eZ5Playground\TwigBundle\Twig;

use eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension\ContentExtension as BaseExtension;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Field;
use InvalidArgumentException;
use Twig_Template;

class ContentExtension extends BaseExtension
{
    /**
     * Renders the HTML for a given field.
     *
     * @param \eZ\Publish\Core\Repository\Values\Content\Content $content
     * @param string $fieldIdentifier Identifier for the field we want to render
     * @param array $params An array of parameters to pass to the field view
     * @param string $default A string to be displayed if the given attribute is not found
     * @throws \InvalidArgumentException If $fieldIdentifier is invalid in $content
     * @return string The HTML markup
     */
    public function renderField( Content $content, $fieldIdentifier, array $params = array() )
    {
        $field = $this->translationHelper->getTranslatedField( $content, $fieldIdentifier, isset( $params['lang'] ) ? $params['lang'] : null );
        if ( !$field instanceof Field )
        {
            if ( isset( $params['ifMissing'] ) )
                return $params['ifMissing'];

            throw new InvalidArgumentException(
                "Invalid field identifier '$fieldIdentifier' for content #{$content->contentInfo->id}"
            );
        }

        $localTemplate = null;
        if ( isset( $params['template'] ) )
        {
            // local override of the template
            // this template is put on the top the templates stack
            $localTemplate = $params['template'];
            unset( $params['template'] );
        }

        $params = $this->getRenderFieldBlockParameters( $content, $field, $params );

        // Getting instance of Twig_Template that will be used to render blocks
        if ( !$this->template instanceof Twig_Template )
        {
            $tpl = reset( $this->renderFieldResources );
            $this->template = $this->environment->loadTemplate( $tpl['template'] );
        }

        return $this->template->renderBlock(
            $this->getRenderFieldBlockName( $content, $field ),
            $params,
            $this->getBlocksByField( $content, $field, $localTemplate )
        );
    }
}