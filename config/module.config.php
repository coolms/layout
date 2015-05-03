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

return [
    'controllers' => [
        'aliases' => [
            'CmsLayout\Controller\Admin' => 'CmsLayout\Mvc\Controller\AdminController',
        ],
        'invokables' => [
            'CmsLayout\Mvc\Controller\AdminController'
                => 'CmsLayout\Mvc\Controller\AdminController',
        ],
    ],
    'listeners' => [
        'CmsLayout\Event\LayoutListener' => 'CmsLayout\Event\LayoutListener',
    ],
    'router' => [
        'routes' => [
            'cms-admin' => [
                'child_routes' => [
                    'layout' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/layout[/:controller[/:action[/:id]]]',
                            'constraints' => [
                                'controller' => '[a-zA-Z\-]*',
                                'action' => '[a-zA-Z\-]*',
                                'id' => '[a-zA-Z0-9\-]*',
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'CmsLayout\Controller',
                                'controller' => 'Admin',
                                'action' => 'index',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'aliases' => [
            'CmsLayout\Options\ModuleOptionsInterface' => 'CmsLayout\Options\ModuleOptions',
        ],
        'factories' => [
            'CmsLayout\Options\ModuleOptions' => 'CmsLayout\Factory\ModuleOptionsFactory',
        ],
        'invokables' => [
            'CmsLayout\Event\LayoutListener' => 'CmsLayout\Event\LayoutListener',
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'CmsLayout' => __DIR__ . '/../view',
        ],
    ],
];
