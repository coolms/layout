<?php
/**
 * CoolMS2 Layout Module (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/layout for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsLayout\Event;

use Zend\EventManager\AbstractListenerAggregate,
    Zend\EventManager\EventManagerInterface,
    Zend\Mvc\MvcEvent;

/**
 * Layout event listener
 *
 * @author Dmitry Popov <d.popov@altgraphic.com>
 */
class LayoutListener extends AbstractListenerAggregate
{
    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_BOOTSTRAP, [$this, 'onBootstrap'], 1000);
    }

    /**
     * Event callback to be triggered on bootstrap
     *
     * @param MvcEvent $e
     * @return void
     */
    public function onBootstrap(MvcEvent $e)
    {
        $shareManager = $e->getApplication()->getEventManager()->getSharedManager();
        $shareManager->attach('Zend\Mvc\Controller\AbstractController', 'dispatch', function($e) {

            $controller = $e->getTarget();
            $controllerClass = get_class($controller);
            $moduleNamespace = substr($controllerClass, 0, strpos($controllerClass, '\\'));

            /* @var $config \CmsLayout\Options\ModuleOptionsInterface */
            $config  = $e->getApplication()->getServiceManager()->get('CmsLayout\\Options\\ModuleOptions');
            $layouts = $config->getLayouts();

            $name = null;

            if (isset($layouts[$controllerClass])
                && $this->isValid($layouts[$controllerClass], $controllerClass)
            ) {
                $name = $controllerClass;
            } elseif (isset($layouts[$moduleNamespace])
                && $this->isValid($layouts[$moduleNamespace], $moduleNamespace)
            ) {
                $name = $moduleNamespace;
            } else {
                $routeName = $e->getRouteMatch()->getMatchedRouteName();
                $routeName = trim($routeName, '/');
                if (isset($layouts[$routeName])
                    && $this->isValid($layouts[$routeName], $routeName)
                ) {
                    $name = $routeName;
                } else {
                    $routeNameTokens = explode('/', $routeName);
                    while (array_pop($routeNameTokens)) {
                        $routeName = implode('/', $routeNameTokens);
                        if (isset($layouts[$routeName])
                            && $this->isValid($layouts[$routeName], $routeName)
                        ) {
                            $name = $routeName;
                            break;
                        }
                    }
                }
            }

            if (null !== $name && ($options = $layouts[$name])) {
                if (is_string($options)) {
                    $controller->layout($options);
                } elseif (is_array($options)) {
                    if ($options['layout'] === '') {
                        return;
                    }
                    $controller->layout($options['layout']);
                }
            }
        }, 100);
    }

    /**
     * @param array|string $options
     * @param string $name
     * @return bool
     * @throws Exception\InvalidArgumentException
     */
    private function isValid($options, $name)
    {
        if (is_string($options)) {
            return true;
        }

        if (!isset($options['layout'])) {
            throw new Exception\InvalidArgumentException(
                "Layout view script cannot be found in '$name'"
            );
        }

        if (isset($options['active']) && !$options['active']) {
            return false;
        }

        $now = new \DateTime('now');
        if (!empty($options['since'])) {
            if (!$options['since'] instanceof \DateTime) {
                $options['since'] = new \DateTime($options['since']);
            }
            if ($options['since'] > $now) {
                return false;
            }
        }
        if (!empty($options['to'])) {
            if (!$options['to'] instanceof \DateTime) {
                $options['to'] = new \DateTime($options['to']);
            }
            if ($options['to'] < $now) {
                return false;
            }
        }

        return true;
    }
}
