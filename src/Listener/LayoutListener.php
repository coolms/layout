<?php
/**
 * CoolMS2 Layout Module (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/layout for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsLayout\Listener;

use DateTime,
    Zend\EventManager\AbstractListenerAggregate,
    Zend\EventManager\EventManagerInterface,
    Zend\Filter\FilterChain,
    Zend\Filter\FilterInterface,
    Zend\Mvc\Controller\AbstractController,
    Zend\Mvc\ModuleRouteListener,
    Zend\Mvc\MvcEvent,
    Zend\ServiceManager\ServiceLocatorInterface,
    Zend\Stdlib\AbstractOptions,
    Zend\View\Renderer\PhpRenderer,
    Zend\View\Helper\HeadScript,
    Zend\View\Helper\HeadStyle,
    Zend\View\Model\ViewModel,
    Zend\View\View,
    Zend\View\ViewEvent,
    CmsCommon\Stdlib\ArrayUtils,
    CmsCommon\Stdlib\DateTimeUtils,
    CmsLayout\Options\ModuleOptions,
    CmsLayout\Options\ModuleOptionsInterface;

/**
 * Layout event listener
 *
 * @author Dmitry Popov <d.popov@altgraphic.com>
 */
class LayoutListener extends AbstractListenerAggregate
{
    const ACTION_APPEND     = 'APPEND';
    const ACTION_PREPEND    = 'PREPEND';
    const ACTION_SET        = 'SET';
    const ACTION_DEFAULT    = self::ACTION_APPEND;

    /**
     * @var bool
     */
    private $enabled = true;

    /**
     * @var FilterInterface
     */
    private $methodNameFilter;

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, [$this, 'onDispatch']);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'onDispatchError']);
    }

    /**
     * Event callback to be triggered on dispatch
     *
     * @param MvcEvent $e
     * @return void
     */
    public function onDispatch(MvcEvent $e)
    {
        $services = $e->getApplication()->getServiceManager();
        /* @var $config ModuleOptionsInterface */
        $config = $services->get(ModuleOptions::class);

        $routeMatch = $e->getRouteMatch();
        $moduleNamespace = $routeMatch->getParam(
            ModuleRouteListener::MODULE_NAMESPACE,
            $routeMatch->getParam('controller')
        );

        $layouts = $config->getLayouts();
        $module = strstr($moduleNamespace, '\\', true);

        if (isset($layouts[$module]) && $this->isValid($layouts[$module])) {
            $config->setFromArray($this->normalizeOptions($layouts[$module]));
        }

        $className = $routeMatch->getParam('controller');
        if (isset($layouts[$className]) && $this->isValid($layouts[$className])) {
            $config->setFromArray($this->normalizeOptions($layouts[$controllerClass]));
        }

        $routeName = trim($e->getRouteMatch()->getMatchedRouteName(), '/');
        $routeToken = strtok($routeName, '/');
        while (false !== $routeToken) {
            $routeTokens[] = $routeToken;
            $routeName = implode('/', $routeTokens);
            if (isset($layouts[$routeName]) && $this->isValid($layouts[$routeName])) {
                $config->setFromArray($this->normalizeOptions($layouts[$routeName]));
            }

            $routeToken = strtok('/');
        }

        if ($options = $config->getModuleOptions()) {
            $this->loadLayoutModuleOptions($options, $services);
        }

        if (!$config->getNamespace()) {
            $config->setNamespace($module);
        }

        $e->getApplication()->getEventManager()->getSharedManager()->attach(
            View::class,
            ViewEvent::EVENT_RENDERER_POST,
            function ($e) use ($config) {
                $this->setNamespace($e, $config);
            },
            100
        );

        $e->getApplication()->getEventManager()->getSharedManager()->attach(
            View::class,
            ViewEvent::EVENT_RENDERER_POST,
            function ($e) use ($config) {
                $this->onViewRendererPost($e, $config);
            }
        );
    }

    /**
     * Event callback to be triggered on dispatch.error
     *
     * @param MvcEvent $e
     * @return void
     */
    public function onDispatchError(MvcEvent $e)
    {
        $services = $e->getApplication()->getServiceManager();
        /* @var $config \CmsLayout\Options\ModuleOptionsInterface */
        $config = $services->get(ModuleOptions::class);

        $e->getApplication()->getEventManager()->getSharedManager()->attach(
            View::class,
            ViewEvent::EVENT_RENDERER_POST,
            function ($e) use ($config) {
                $this->setNamespace($e, $config);
            },
            100
        );

        $e->getApplication()->getEventManager()->getSharedManager()->attach(
            View::class,
            ViewEvent::EVENT_RENDERER_POST,
            function ($e) use ($config) {
                $this->onViewRendererPost($e, $config);
            }
        );
    }

    /**
     * @param ViewEvent $e
     * @param ModuleOptionsInterface $config
     * @return void
     */
    protected function setNamespace(ViewEvent $e, ModuleOptionsInterface $config)
    {
        if ($e->getRenderer() instanceof PhpRenderer &&
            ($namespace = $config->getNamespace())
        ) {
            $e->getModel()->setOption('namespace', $namespace)
                ->setVariable('namespace', $namespace);
        }
    }

    /**
     * @param ViewEvent $e
     * @param ModuleOptionsInterface $config
     * @return void
     */
    protected function onViewRendererPost(ViewEvent $e, ModuleOptionsInterface $config)
    {
        if (!$this->enabled || !$e->getRenderer() instanceof PhpRenderer) {
            return;
        }

        if ($template = $config->getTemplate()) {
            $e->getModel()->setTemplate($template);
        }

        foreach ($config->toArray() as $name => $value) {
            if (!$value) continue;

            $methodName = $this->normalizeMethodName("setup$name");
            if (method_exists($this, $methodName)) {
                $this->$methodName($e, $value);
            }
        }

        if ($config->getWrapper()) {
            $wrapper = new ViewModel();
            $wrapper->setTemplate($config->getWrapper());
            $wrapper->addChild($e->getModel(), $config->getWrapperCaptureTo());
            $e->setModel($wrapper);
        }

        $this->setEnabled(false);
    }

    /**
     * @param bool $flag
     * @return self
     */
    public function setEnabled($flag)
    {
        $this->enabled = (bool) $flag;
        return $this;
    }

    /**
     * @param array|string $options
     * @return array
     */
    private function normalizeOptions($options)
    {
        if (is_string($options)) {
            $options = ['template' => $options];
        }

        return (array) $options;
    }

    /**
     * @param array $moduleOptions
     * @param ServiceLocatorInterface $services
     * @return void
     */
    private function loadLayoutModuleOptions(array $moduleOptions, ServiceLocatorInterface $services)
    {
        foreach ($moduleOptions as $name => $options) {
            if ((class_exists($name) || interface_exists($name)) && $services->has($name)) {
                $optionsService = $services->get($name);
                if ($optionsService instanceof AbstractOptions) {
                    $optionsService->setFromArray($options);
                }
            }
        }
    }

    /**
     * @param ViewEvent $e
     * @param array $titles
     * @return void
     */
    protected function setupHeadTitles(ViewEvent $e, array $titles)
    {
        $plugin = $e->getRenderer()->plugin('headTitle');
        foreach ($titles as $title) {
            $plugin($title);
        }
    }

    /**
     * @param ViewEvent $e
     * @param array $options
     * @return void
     */
    protected function setupHeadTitleOptions(ViewEvent $e, array $options)
    {
        $plugin = $e->getRenderer()->plugin('headTitle');

        foreach ($options as $name => $value) {
            $methodName = $this->normalizeMethodName("set_$name");
            if ($methodName === 'setDefaultAttachOrder') {
                $value = strtoupper($value);
            }

            if (is_callable([$plugin, $methodName])) {
                $plugin->{$methodName}($value);
            }
        }
    }

    /**
     * @param ViewEvent $e
     * @param array $meta
     * @return void
     */
    protected function setupHeadMeta(ViewEvent $e, array $meta)
    {
        $plugin = $e->getRenderer()->plugin('headMeta');

        foreach ($meta as $metaItem) {

            list($action, $index) = $this->getPluginAction($metaItem);

            $content    = isset($metaItem['content'])   ? $metaItem['content']   : '';
            $keyValue   = isset($metaItem['key_value']) ? $metaItem['key_value'] : '';
            $keyType    = isset($metaItem['key_type'])  ? $metaItem['key_type']  : 'name';
            $modifiers  = isset($metaItem['modifiers']) ? $metaItem['modifiers'] : [];

            if ($action) {
                if (!isset($metaItem['type'])) {
                    $metaItem = $plugin->createData($keyType, $keyValue, $content, $modifiers);
                    null === $index ? $plugin->$action($metaItem) : $plugin->$action($index, $metaItem);
                    continue;
                }

                $type = $this->normalizeMethodName($metaItem['type']);
                switch ($type) {
                    case 'name':
                    case 'httpEquiv':
                    case 'property':
                    case 'itemprop':
                        $method = $action . ucfirst($type);
                        if (null === $index) {
                            $plugin->$method($keyValue, $content, $modifiers);
                        } else {
                            $plugin->$method($index, $keyValue, $content, $modifiers);
                        }

                        continue 2;
                }
            }

            $plugin($content, $keyValue, $keyType, $modifiers);
        }
    }

    /**
     * @param ViewEvent $e
     * @param array $links
     * @throws Exception\RuntimeException
     * @return void
     */
    protected function setupHeadLinks(ViewEvent $e, array $links)
    {
        $renderer   = $e->getRenderer();
        $plugin     = $renderer->plugin('headLink');
        $assetPath  = $renderer->plugin('assetPath');

        foreach ($links as $link) {

            list($action, $index) = $this->getPluginAction($link);

            if (!isset($link['href'])) {
                if (!isset($link['extras']['href'])) {
                    throw new Exception\RuntimeException('"href" attribute is required for link tag');
                }

                $link['href'] = $link['extras']['href'];
                unset($link['extras']['href']);
            } else {
                $link['href'] = $assetPath($link['href']);
            }

            if ($action) {
                if (!isset($link['type'])) {
                    $link = $plugin->createData($link);
                    null === $index ? $plugin->$action($link) : $plugin->$action($index, $link);
                    continue;
                }

                $type   = $this->normalizeMethodName($link['type']);
                $method = $action . ucfirst($type);

                switch ($type) {
                    case 'stylesheet':
                        $media      = isset($link['media'])     ? $link['media']     : 'screen';
                        $condition  = isset($link['condition']) ? $link['condition'] : '';
                        $extras     = isset($link['extras'])    ? $link['extras']    : [];

                        if (null === $index) {
                            $plugin->$method($link['href'], $media, $condition, $extras);
                        } else {
                            $plugin->$method($index, $link['href'], $media, $condition, $extras);
                        }

                        continue 2;
                        break;
                    case 'alternate':
                        $title  = isset($link['title'])  ? $link['title']  : null;
                        $extras = isset($link['extras']) ? $link['extras'] : [];
                        $type   = isset($extras['type']) ? $extras['type'] : null;

                        unset($extras['type']);

                        if (null === $index) {
                            $plugin->$method($link['href'], $type, $title, $extras);
                        } else {
                            $plugin->$method($index, $link['href'], $type, $title, $extras);
                        }

                        continue 2;
                        break;
                    case 'next':
                    case 'prev':
                        if (null === $index) {
                            $plugin->$method($index, $link['href']);
                        } else {
                            $plugin->$method($link['href']);
                        }

                        continue 2;
                }
            }

            $plugin($link);
        }
    }

    /**
     * @param ViewEvent $e
     * @param array $scripts
     * @param string $plugin
     * @return void
     */
    protected function setupHeadScripts(ViewEvent $e, array $scripts, $plugin = 'headScript')
    {
        $renderer   = $e->getRenderer();
        $plugin     = $renderer->plugin($plugin);
        $assetPath  = $renderer->plugin('assetPath');

        foreach ($scripts as $script) {
            list($action, $index) = $this->getPluginAction($script);

            $src        = isset($script['src'])        ? $assetPath($script['src']) : null;
            $content    = isset($script['content'])    ? $script['content']         : null;
            $attributes = isset($script['attributes']) ? $script['attributes']      : [];
            $type       = isset($script['type'])       ? $script['type']            : 'text/javascript';
            $mode       = $src ? HeadScript::FILE : HeadScript::SCRIPT;

            if ($action) {
                $method = $action . ucfirst(strtolower($mode));

                switch ($mode) {
                    case HeadScript::FILE:
                        if (null === $index) {
                            $plugin->$method($src, $type, $attributes);
                        } else {
                            $plugin->$method($index, $src, $type, $attributes);
                        }

                        continue 2;
                        break;
                    case HeadScript::SCRIPT:
                        if (null === $index) {
                            $plugin->$method($content, $type, $attributes);
                        } else {
                            $plugin->$method($index, $content, $type, $attributes);
                        }

                        continue 2;
                }
            }

            $plugin(
                $mode,
                $mode === HeadScript::FILE ? $src : $content,
                static::ACTION_DEFAULT,
                $attributes,
                $type
            );
        }
    }

    /**
     * @param ViewEvent $e
     * @param array $scripts
     * @return void
     */
    protected function setupInlineScripts(ViewEvent $e, array $scripts)
    {
        $this->setupHeadScripts($e, $scripts, 'inlineScript');
    }

    /**
     * @param ViewEvent $e
     * @param array $styles
     * @return void
     */
    protected function setupHeadStyles(ViewEvent $e, array $styles)
    {
        $renderer   = $e->getRenderer();
        $plugin     = $renderer->plugin('headStyle');

        foreach ($styles as $style) {

            list($action, $index) = $this->getPluginAction($style);

            $content    = isset($style['content'])    ? $style['content']    : null;
            $attributes = isset($style['attributes']) ? $style['attributes'] : [];

            if ($action) {
                $method = $action . 'Style';
                if (null === $index) {
                    $plugin->$method($content, $attributes);
                } else {
                    $plugin->$method($index, $content, $attributes);
                }

                continue;
            }

            $plugin($content, static::ACTION_DEFAULT, $content);
        }
    }

    /**
     * @param array $options
     * @return array
     */
    protected function getPluginAction(&$options)
    {
        $action = null;
        $index  = null;

        if (!empty($options['action'])) {
            switch ($options['action']) {
                case 'offsetSet':
                    $index = empty($options['index']) ? 0 : $options['index'];
                    unset($options['index']);
                case 'append':
                case 'prepend':
                case 'set':
                    $action = $options['action'];
                    unset($options['action']);
            }
        }

        return [$action, $index];
    }

    /**
     * @param array|string $options
     * @return bool
     */
    private function isValid($options)
    {
        if (is_string($options)) {
            return true;
        }

        if (isset($options['enabled']) && !$options['enabled']) {
            return false;
        }

        if ((!empty($options['since']) &&
                DateTimeUtils::normalize($options['since']) > new DateTime('now')) ||
            (!empty($options['to']) &&
                DateTimeUtils::normalize($options['to']) < new DateTime('now'))
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param string $name
     * @return string
     */
    private function normalizeMethodName($name)
    {
        return lcfirst($this->getMethodNameFilter()->filter($name));
    }

    /**
     * @return FilterInterface
     */
    private function getMethodNameFilter()
    {
        if (null === $this->methodNameFilter) {
            $this->methodNameFilter = new FilterChain([
                    'filters' => [
                        ['name' => 'WordUnderscoreToCamelCase'],
                        ['name' => 'WordDashToCamelCase'],
                        ['name' => 'WordSeparatorToCamelCase'],
                    ],
                ]);
        }

        return $this->methodNameFilter;
    }
}
