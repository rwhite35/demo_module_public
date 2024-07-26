<?php
namespace Acme\Utilities;
/**
 * @author Ron White, ronwhite562@gmail.com
 * @version 1.0, [Dev-Master]
 * @since 2018-04-19
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

abstract class QuickcheckAbstractFactory
{
    abstract public function quickcheckService(
        string $from,               // default mailbox that can accept bounced notices
        array $quickCheckArr,       // array of users and their rules to check
        string $scriptPath,         // global constant path to quickcheck executable
        string $report              // the consolidated report to send this user.
        );
    
    
}