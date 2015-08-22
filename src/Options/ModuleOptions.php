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

use Zend\Stdlib\AbstractOptions,
    CmsCommon\Stdlib\DateTimeUtils;

class ModuleOptions extends AbstractOptions implements ModuleOptionsInterface
{
    /**
     * Turn off strict options mode
     *
     * @var bool
     */
    protected $__strictMode__ = false;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var bool
     */
    protected $enabled = true;

    /**
     * @var string|\DateTime
     */
    protected $since;

    /**
     * @var string|\DateTime
     */
    protected $to;

    /**
     * @var array
     */
    protected $headTitles = [];

    /**
     * @var array
     */
    protected $headTitleOptions = [];

    /**
     * @var array
     */
    protected $headMeta = [];

    /**
     * @var array
     */
    protected $headLinks = [];

    /**
     * @var array
     */
    protected $headStyles = [];

    /**
     * @var array
     */
    protected $headScripts = [];

    /**
     * @var array
     */
    protected $inlineScripts = [];

    /**
     * @var array
     */
    protected $moduleOptions = [];

    /**
     * @var array
     */
    protected $layouts = [];

    /**
     * {@inheritDoc}
     */
    public function setNamespace($namespace)
    {
        $this->namespace = (string) $namespace;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * {@inheritDoc}
     */
    public function setTemplate($template)
    {
        $this->template = (string) $template;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * {@inheritDoc}
     */
    public function setEnabled($flag)
    {
        $this->enabled = (bool) $flag;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritDoc}
     */
    public function setSince($date)
    {
        $this->since = DateTimeUtils::normalize($date);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSince()
    {
        return $this->since;
    }

    /**
     * {@inheritDoc}
     */
    public function setTo($date)
    {
        $this->to = DateTimeUtils::normalize($date);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * {@inheritDoc}
     */
    public function setHeadTitles($titles)
    {
        $this->headTitles = (array) $titles;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getHeadTitles()
    {
        return $this->headTitles;
    }

    /**
     * {@inheritDoc}
     */
    public function setHeadTitleOptions($options)
    {
        $this->headTitleOptions = (array) $options;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getHeadTitleOptions()
    {
        return $this->headTitleOptions;
    }

    /**
     * {@inheritDoc}
     */
    public function setHeadMeta($meta)
    {
        $this->headMeta = (array) $meta;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getHeadMeta()
    {
        return $this->headMeta;
    }

    /**
     * {@inheritDoc}
     */
    public function setHeadLinks($links)
    {
        $this->headLinks = (array) $links;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getHeadLinks()
    {
        return $this->headLinks;
    }

    /**
     * {@inheritDoc}
     */
    public function setHeadStyles($styles)
    {
        $this->headStyles = (array) $styles;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getHeadStyles()
    {
        return $this->headStyles;
    }

    /**
     * {@inheritDoc}
     */
    public function setHeadScripts($scripts)
    {
        $this->headScripts = (array) $scripts;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getHeadScripts()
    {
        return $this->headScripts;
    }

    /**
     * {@inheritDoc}
     */
    public function setInlineScripts($scripts)
    {
        $this->inlineScripts = (array) $scripts;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getInlineScripts()
    {
        return $this->inlineScripts;
    }

    /**
     * {@inheritDoc}
     */
    public function setModuleOptions($options)
    {
        $this->moduleOptions = (array) $options;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getModuleOptions()
    {
        return $this->moduleOptions;
    }

    /**
     * {@inheritDoc}
     */
    public function setLayouts($layouts)
    {
        $this->layouts = (array) $layouts;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getLayouts()
    {
        return $this->layouts;
    }
}
