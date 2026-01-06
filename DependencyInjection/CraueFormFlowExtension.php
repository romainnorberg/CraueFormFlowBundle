<?php

declare(strict_types=1);

namespace Craue\FormFlowBundle\DependencyInjection;

use Craue\FormFlowBundle\Form\FormFlowInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class CraueFormFlowExtension extends Extension implements CompilerPassInterface
{
    public const FORM_FLOW_TAG = 'craue.form.flow';

    public function load(array $configs, ContainerBuilder $container): void
    {
        // Symfony 8: XML config non supportée -> on charge le fichier PHP
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        $container->registerForAutoconfiguration(FormFlowInterface::class)
            ->addTag(self::FORM_FLOW_TAG);
    }

    public function process(ContainerBuilder $container): void
    {
        $baseFlowDefinitionMethodCalls = $container->getDefinition('craue.form.flow')->getMethodCalls();

        foreach (array_keys($container->findTaggedServiceIds(self::FORM_FLOW_TAG)) as $id) {
            $container->findDefinition($id)->setMethodCalls($baseFlowDefinitionMethodCalls);
        }
    }
}