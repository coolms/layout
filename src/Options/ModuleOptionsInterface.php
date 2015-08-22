<?php 
/**
 * CoolMS2 Layout Module (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/layout for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsLayout\Options;

interface ModuleOptionsInterface
{
    /**
     * @param string $template
     * @return self
     */
    public function setNamespace($namespace);

    /**
     * @return string
     */
    public function getNamespace();

    /**
     * @param string $template
     * @return self
     */
    public function setTemplate($template);

    /**
     * @return string
     */
    public function getTemplate();

    /**
     * @param bool $flag
     * @return self
     */
    public function setEnabled($flag);

    /**
     * @return bool
     */
    public function getEnabled();

    /**
     * @param string|int|\DateTime $date
     * @return self
     */
    public function setSince($date);

    /**
     * @return \DateTime
     */
    public function getSince();

    /**
     * @param string|int|\DateTime $date
     * @return self
     */
    public function setTo($date);

    /**
     * @return \DateTime
     */
    public function getTo();

    /**
     * @param array $titles
     * @return self
     */
    public function setHeadTitles($titles);

    /**
     * @return array
     */
    public function getHeadTitles();

    /**
     * @param array $options
     * @return self
     */
    public function setHeadTitleOptions($options);

    /**
     * @return array
     */
    public function getHeadTitleOptions();

    /**
     * @param array $meta
     * @return self
     */
    public function setHeadMeta($meta);

    /**
     * @return array
     */
    public function getHeadMeta();

    /**
     * @param array $links
     * @return self
     */
    public function setHeadLinks($links);

    /**
     * @return array
     */
    public function getHeadLinks();

    /**
     * @param array $styles
     * @return self
     */
    public function setHeadStyles($styles);

    /**
     * @return array
     */
    public function getHeadStyles();

    /**
     * @param array $scripts
     * @return self
     */
    public function setHeadScripts($scripts);

    /**
     * @return array
     */
    public function getHeadScripts();

    /**
     * @param array $scripts
     * @return self
     */
    public function setInlineScripts($scripts);

    /**
     * @return array
     */
    public function getInlineScripts();

    /**
     * @param array $options
     * @return self
     */
    public function setModuleOptions($options);

    /**
     * @return array
     */
    public function getModuleOptions();

    /**
     * @param array $layouts
     * @return self
     */
    public function setLayouts($layouts);

    /**
     * @return array
     */
    public function getLayouts();
}
