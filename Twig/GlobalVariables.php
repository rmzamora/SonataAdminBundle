<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Sonata\AdminBundle\Admin\Pool;

/**
 * Class GlobalVariables
 *
 * @package Sonata\AdminBundle\Twig
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class GlobalVariables
{
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return Pool
     */
    public function getAdminPool()
    {
        return $this->container->get('sonata.admin.pool');
    }

    /**
     * @param string $code
     * @param string $action
     * @param array  $parameters
     * @param mixed  $absolute
     *
     * @return string
     */
    public function url($code, $action, $parameters = array(), $absolute = false)
    {
        if ($pipe = strpos('|', $code)) {
            // convert code=sonata.page.admin.page|sonata.page.admin.snapshot, action=list
            // to => sonata.page.admin.page|sonata.page.admin.snapshot.list
            $action = $code.'.'.$action;
            $code = substr($code, 0, $pipe);
        }

        return $this->getAdminPool()->getAdminByAdminCode($code)->generateUrl($action, $parameters, $absolute);
    }
}
