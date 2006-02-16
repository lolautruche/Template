<?php
/**
 * File containing the ezcTemplateFileFailedUnlinkException class
 *
 * @package Template
 * @version //autogen//
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * Exception for problems when unlinking template files.
 *
 * @package Template
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version //autogen//
 */
class ezcTemplateFileFailedUnlinkException extends Exception
{

    /**
     * Initialises the exception with the template file path.
     *
     * @param string $stream The stream path to the template file which could not be
     * unlinked.
     */
    public function __construct( $stream )
    {
        parent::__construct( "Unlinking template file <$stream> failed." );
    }

}
?>