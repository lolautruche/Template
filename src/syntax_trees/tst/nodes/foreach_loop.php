<?php
/**
 * File containing the ezcTemplateForeachLoopTstNode class
 *
 * @package Template
 * @version //autogen//
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * Control structure: foreach.
 *
 * @package Template
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version //autogen//
 */
class ezcTemplateForeachLoopTstNode extends ezcTemplateBlockTstNode
{
    public $array;
    public $keyVariableName;
    public $itemVariableName;
    public $value;

    /**
     *
     * @param ezcTemplateSource $source
     * @param ezcTemplateCursor $start
     * @param ezcTemplateCursor $end
     */
    public function __construct( ezcTemplateSourceCode $source, /*ezcTemplateCursor*/ $start, /*ezcTemplateCursor*/ $end )
    {
        parent::__construct( $source, $start, $end );
        $this->value = $this->keyVariableName = $this->itemVariableName = null;
        $this->name = 'foreach';
    }

    /**
     *
     * @retval ezcTemplateAstNode
     * @todo Not implemented yet.
     */
    public function transform()
    {
    }

    public function symbol()
    {
        $text = 'foreach ';
        $a = $this->array;
        $k = $this->keyVariableName;
        $i = $this->itemVariableName;

        if ( is_array( $a ) )
            $text .= "<array>";
        else
            $text .= "\$$a";

        $text .= " as ";

        if ( isset( $k ) )
            $text .= "\$$k => \$$i";
        else
            $text .= "\$$i";

        return $text;
    }

    public function canHandleElement( ezcTemplateTstNode $element )
    {
        return ( $element instanceof ezcTemplateLoopTstNode && $element->name != 'delimiter' );
    }

    public function handleElement( ezcTemplateTstNode $element )
    {
        $this->elements[] = $element;
        $element->parentBlock = $this;
    }
}
?>