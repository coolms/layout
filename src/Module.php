<?php
/**
 * CoolMS2 Layout Module (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/layout for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsLayout;

use Zend\Loader\ClassMapAutoloader,
    Zend\Loader\StandardAutoloader,
    Zend\ModuleManager\Feature\AutoloaderProviderInterface,
    Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function getAutoloaderConfig()
    {
        return [
            ClassMapAutoloader::class => [
                __DIR__ . '/../autoload_classmap.php',
            ],
            StandardAutoloader::class => [
                'fallback_autoloader' => true,
                'namespaces' => [
                    __NAMESPACE__ => __DIR__,
                ],
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}
