<?php
namespace Acme\Utilities;
/**
 * @author Ron White, ronwhite562@gmail.com
 * @version 1.0, [Dev-Master]
 * @since 2018-04-19
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

abstract class WriterAbstractFactory
{
    abstract public function writeJsonToFile(
        string $service,
        string $json,
        string $filePath
        );
    
    abstract public function writeArrayToFile(
        string $serviceStr,
        array $serviceArr,
        string $filePath
        );
    
}