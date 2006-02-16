<?php
/**
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version //autogentag//
 * @filesource
 * @package Template
 * @subpackage Tests
 */

/**
 * @package Template
 * @subpackage Tests
 */
class ezcTemplateOutputContextTest extends ezcTestCase
{
    public static function suite()
    {
         return new ezcTestSuite( "ezcTemplateOutputContextTest" );
    }

    /**
     * Test passing constructor values
     */
    public function testInit()
    {
        $context = new ezcTemplateMockContext;
    }
}

/**
 * Sample implementation of an output context, this tests that there are no
 * interface functions which are not implemented.
 */
class ezcTemplateMockContext implements ezcTemplateOutputContext
{
    public function cleanupWhitespace()
    {
        return false;
    }

    public function cleanupEol()
    {
        return false;
    }

    public function indent()
    {
        return false;
    }

    public function transformOutput()
    {
        return false;
    }

    public function identifier()
    {
        return 'mock';
    }
}

?>