<?php
/**
 * This file is part of the LdapToolsBundle package.
 *
 * (c) Chad Sikorra <Chad.Sikorra@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\LdapTools\Bundle\LdapToolsBundle\DependencyInjection\Compiler;

use LdapTools\Bundle\LdapToolsBundle\DependencyInjection\Compiler\EventRegisterPass;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EventRegisterPassSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('LdapTools\Bundle\LdapToolsBundle\DependencyInjection\Compiler\EventRegisterPass');
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    function it_should_process_tagged_services_when_there_are_none_found($container)
    {
        $container->findTaggedServiceIds(EventRegisterPass::SUBSCRIBER_TAG)->willReturn([]);
        $container->findTaggedServiceIds(EventRegisterPass::LISTENER_TAG)->willReturn([]);

        $this->process($container);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param \Symfony\Component\DependencyInjection\Definition $definition
     */
    function it_should_process_tagged_services_when_they_exist($container, $definition)
    {
        $container->findTaggedServiceIds(EventRegisterPass::SUBSCRIBER_TAG)->willReturn(
            ['foo.subscriber' => []]
        );
        $container->findTaggedServiceIds(EventRegisterPass::LISTENER_TAG)->willReturn(
            ['foo.listener' => [['method' => 'foo', 'event' => 'ldap.object.before_modify']]]
        );
        $container->findDefinition(EventRegisterPass::DISPATCHER)->willReturn($definition);
        $container->getDefinition('foo.listener')->willReturn($definition);
        $container->getDefinition('foo.subscriber')->willReturn($definition);

        $this->process($container);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param \Symfony\Component\DependencyInjection\Definition $definition
     */
    function it_should_require_the_method_and_event_property_for_the_listener_services($container, $definition)
    {
        $container->findTaggedServiceIds(EventRegisterPass::SUBSCRIBER_TAG)->willReturn([]);
        $container->findTaggedServiceIds(EventRegisterPass::LISTENER_TAG)->willReturn(
            ['foo.listener' => [['event' => 'ldap.object.before_modify']]]
        );
        $container->findDefinition(EventRegisterPass::DISPATCHER)->willReturn($definition);
        $container->getDefinition('foo.listener')->willReturn($definition);

        $this->shouldThrow('\InvalidArgumentException')->duringProcess($container);

        $container->findTaggedServiceIds(EventRegisterPass::LISTENER_TAG)->willReturn(
            ['foo.listener' => [['method' => 'foo']]]
        );

        $this->shouldThrow('\InvalidArgumentException')->duringProcess($container);

        $container->findTaggedServiceIds(EventRegisterPass::LISTENER_TAG)->willReturn(
            ['foo.listener' => []]
        );

        $this->shouldThrow('\InvalidArgumentException')->duringProcess($container);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param \Symfony\Component\DependencyInjection\Definition $definition
     */
    function it_should_not_allow_an_abstract_service_as_a_listener($container, $definition)
    {
        $container->findTaggedServiceIds(EventRegisterPass::SUBSCRIBER_TAG)->willReturn([]);
        $container->findTaggedServiceIds(EventRegisterPass::LISTENER_TAG)->willReturn(
            ['foo.listener' => [['method' => 'foo', 'event' => 'ldap.object.before_modify']]]
        );
        $container->findDefinition(EventRegisterPass::DISPATCHER)->willReturn($definition);
        $container->getDefinition('foo.listener')->willReturn($definition);
        $definition->isAbstract()->willReturn(true);

        $this->shouldThrow('\InvalidArgumentException')->duringProcess($container);
    }
}
