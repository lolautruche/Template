<?php
/**
 * File containing the ezcTemplateSubtractionAssignmentOperatorAstNode class
 *
 * @package Template
 * @version //autogen//
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * Represents the PHP subtraction assignment operator -=
 *
 * @package Template
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version //autogen//
 */
class ezcTemplateSubtractionAssignmentOperatorAstNode extends ezcTemplateOperatorAstNode
{
    /**
     * Initialize operator code constructor with 2 parameters (binary).
     */
    public function __construct()
    {
        parent::__construct( self::OPERATOR_TYPE_BINARY );
    }

    /**
     * Returns a text string representing the PHP operator.
     * @return string
     */
    public function getOperatorPHPSymbol()
    {
        return '-=';
    }
}
?>
