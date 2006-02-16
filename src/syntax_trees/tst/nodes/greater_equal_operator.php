<?php
/**
 * File containing the ezcTemplateGreaterEqualOperatorTstNode class
 *
 * @package Template
 * @version //autogen//
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * Operator for comparing two values using PHPs ==.
 *
 * @package Template
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version //autogen//
 */
class ezcTemplateGreaterEqualOperatorTstNode extends ezcTemplateOperatorTstNode
{
    /**
     * Initialise operator with source and cursor positions.
     *
     * @param ezcTemplateSource $source
     * @param ezcTemplateCursor $start
     * @param ezcTemplateCursor $end
     */
    public function __construct( ezcTemplateSourceCode $source, /*ezcTemplateCursor*/ $start, /*ezcTemplateCursor*/ $end )
    {
        parent::__construct( $source, $start, $end,
                             6, 1, self::NON_ASSOCIATIVE );
    }

    /**
     * Returns the right angle bracket and equal sign (>=) symbol.
     *
     * @return string
     */
    public function symbol()
    {
        return '>=';
    }

    /**
     *
     * @retval ezcTemplateAstNode
     * @todo Not implemented yet.
     */
    public function transform()
    {
    }

}
?>