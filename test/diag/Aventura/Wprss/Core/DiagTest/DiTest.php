<?php

namespace Aventura\Wprss\Core\DiagTest;

use RebelCode\Wprss\Debug\DiagTest\Model\TestCase;

/**
 * Tests dependency injection mechanism.
 *
 * @since 4.11
 */
class DiTest extends TestCase
{
    /**
     * Tests whether the global container is returned correctly.
     *
     * @since 4.11
     */
    public function testGlobalContainer()
    {
        $container = wprss_wp_container();
        $this->assertTrue($container instanceof \Interop\Container\ContainerInterface, 'Global container is not a valid container');
    }

    /**
     * Tests whether the WPRA Core container is returned correctly.
     *
     * @since 4.11
     */
    public function testWpraCoreContainer()
    {
        $container = wprss_core_container();
        $this->assertTrue($container instanceof \Interop\Container\ContainerInterface, 'WPRA Core container is not a valid container');
    }

    /**
     * Tests whether the essential `event_manager` service is returned correctly.
     *
     * @since 4.11
     */
    public function testEventManagerService()
    {
        $container = wprss_wp_container();
        $subject = $container->get(sprintf('%1$sevent_manager', \WPRSS_SERVICE_ID_PREFIX));
        $this->assertTrue($subject instanceof \Aventura\Wprss\Core\Model\Event\EventManagerInterface, 'Not a valid event manager');
    }

    /**
     * Tests whether the essential `logger` service is returned correctly.
     *
     * @since 4.11
     */
    public function testLoggerService()
    {
        $container = wprss_wp_container();
        $subject = $container->get(sprintf('%1$slogger', \WPRSS_SERVICE_ID_PREFIX));
        $this->assertTrue($subject instanceof \Aventura\Wprss\Core\Plugin\ComponentInterface, 'Not a valid component');
        $this->assertTrue($subject instanceof \Aventura\Wprss\Core\Model\LoggerInterface, 'Not a valid logger');
    }

    /**
     * Tests whether the essential `factory` service is returned correctly.
     *
     * @since 4.11
     */
    public function testFactoryService()
    {
        $container = wprss_wp_container();
        $subject = $container->get(sprintf('%1$sfactory', \WPRSS_SERVICE_ID_PREFIX));
        $this->assertTrue($subject instanceof \Dhii\Di\FactoryInterface, 'Not a valid factory');
    }

    /**
     * Tests whether the essential `plugin` service is returned correctly.
     *
     * @since 4.11
     */
    public function testPluginService()
    {
        $container = wprss_wp_container();
        $subject = $container->get(sprintf('%1$splugin', \WPRSS_SERVICE_ID_PREFIX));
        $this->assertTrue($subject instanceof \Aventura\Wprss\Core\Plugin\PluginInterface, 'Not a valid plugin');
    }

    /**
     * Tests whether the essential `admin_helper` service is returned correctly.
     *
     * @since 4.11
     */
    public function testAdminHelperService()
    {
        $container = wprss_wp_container();
        $subject = $container->get(sprintf('%1$sadmin_helper', \WPRSS_SERVICE_ID_PREFIX));
        $this->assertTrue($subject instanceof \Aventura\Wprss\Core\Plugin\ComponentInterface, 'Not a valid component');
        $this->assertTrue($subject instanceof \Aventura\Wprss\Core\Component\AdminHelper, 'Not the right admin helper');
    }

    /**
     * Tests whether the essential `leave_review` service is returned correctly.
     *
     * @since 4.11
     */
    public function testLeaveReviewService()
    {
        $container = wprss_wp_container();
        $subject = $container->get(sprintf('%1$sleave_review', \WPRSS_SERVICE_ID_PREFIX));
        $this->assertTrue($subject instanceof \Aventura\Wprss\Core\Plugin\ComponentInterface, 'Not a valid component');
        $this->assertTrue($subject instanceof \Aventura\Wprss\Core\Component\LeaveReviewNotification, 'Not the right component');
    }

    /**
     * Tests whether the structure of container relationships is correct.
     *
     * @since 4.11
     */
    public function testContainerStructure()
    {
        $globalContainer = wprss_wp_container();
        $wpraContainer = wprss_hub_container();
        $coreContainer = wprss_core_container();

        $this->assertTrue(in_array($wpraContainer, $globalContainer->getContainers(), true), 'Global container does not container the WPRA container');
        $this->assertTrue(in_array($coreContainer, $wpraContainer->getContainers(), true), 'WPRA container does not contain the Core container');
    }
}
