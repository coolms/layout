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
     * @param array $layouts
     * @return self
     */
    public function setLayouts($layouts);

    /**
     * @return array
     */
    public function getLayouts();
}
