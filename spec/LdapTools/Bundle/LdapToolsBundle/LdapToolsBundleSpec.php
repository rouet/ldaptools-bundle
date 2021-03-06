<?php
/**
 * This file is part of the LdapToolsBundle package.
 *
 * (c) Chad Sikorra <Chad.Sikorra@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\LdapTools\Bundle\LdapToolsBundle;

use Doctrine\DBAL\Types\Type;
use LdapTools\Bundle\LdapToolsBundle\DependencyInjection\Compiler\EventRegisterPass;
use LdapTools\Bundle\LdapToolsBundle\DependencyInjection\Security\Factory\LdapFormLoginFactory;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LdapToolsBundleSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('LdapTools\Bundle\LdapToolsBundle\LdapToolsBundle');
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param \Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension $extension
     */
    function it_should_add_the_security_listener_factory_and_compiler_pass_when_calling_build($container, $extension)
    {
        $extension->addSecurityListenerFactory(new LdapFormLoginFactory())->shouldBeCalled();
        $container->getExtension('security')->willReturn($extension);
        $container->addCompilerPass(new EventRegisterPass())->shouldBeCalled();

        $this->build($container);
    }

    function it_should_register_the_custom_doctrine_types()
    {
        $this->shouldHaveDoctrineTypes(['ldap_object','ldap_object_collection']);
    }

    public function getMatchers()
    {
        return [
            'haveDoctrineTypes' => function($subject, array $types) {
                $hasTypes = true;

                foreach ($types as $type) {
                    if (!Type::hasType($type)) {
                        $hasTypes = false;
                        break;
                    }
                }

                return $hasTypes;
            },
        ];
    }
}
