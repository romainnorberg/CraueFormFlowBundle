<?php

declare(strict_types=1);

use Craue\FormFlowBundle\EventListener\FlowExpiredEventListener;
use Craue\FormFlowBundle\EventListener\PreviousStepInvalidEventListener;
use Craue\FormFlowBundle\Form\Extension\FormFlowFormExtension;
use Craue\FormFlowBundle\Form\Extension\FormFlowHiddenFieldExtension;
use Craue\FormFlowBundle\Form\FormFlow;
use Craue\FormFlowBundle\Form\FormFlowEvents;
use Craue\FormFlowBundle\Storage\DataManager;
use Craue\FormFlowBundle\Storage\SessionStorage;
use Craue\FormFlowBundle\Twig\Extension\FormFlowExtension;
use Craue\FormFlowBundle\Util\FormFlowUtil;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    // Storage (default + alias public pour BC)
    $services->set('craue.form.flow.storage_default', SessionStorage::class)
        ->args([service('request_stack')])
        ->public(false);

    $services->alias('craue.form.flow.storage', 'craue.form.flow.storage_default')
        ->public(true);

    // Data manager (default + alias)
    $services->set('craue.form.flow.data_manager_default', DataManager::class)
        ->args([service('craue.form.flow.storage')])
        ->public(false);

    $services->alias('craue.form.flow.data_manager', 'craue.form.flow.data_manager_default')
        ->public(false);

    // Core flow service
    $services->set('craue.form.flow', FormFlow::class)
        ->call('setDataManager', [service('craue.form.flow.data_manager')])
        ->call('setFormFactory', [service('form.factory')])
        ->call('setRequestStack', [service('request_stack')])
        ->call('setEventDispatcher', [service('event_dispatcher')]);

    // Form type extensions
    $services->set('craue.form.flow.form_extension', FormFlowFormExtension::class)
        ->tag('form.type_extension', ['extended-type' => 'Symfony\Component\Form\Extension\Core\Type\FormType']);

    $services->set('craue.form.flow.hidden_field_extension', FormFlowHiddenFieldExtension::class)
        ->tag('form.type_extension', ['extended-type' => 'Symfony\Component\Form\Extension\Core\Type\HiddenType']);

    // Event listeners
    $services->set('craue.form.flow.event_listener.previous_step_invalid', PreviousStepInvalidEventListener::class)
        ->tag('kernel.event_listener', ['event' => FormFlowEvents::PREVIOUS_STEP_INVALID, 'method' => 'onPreviousStepInvalid'])
        ->call('setTranslator', [service('translator')]);

    $services->set('craue.form.flow.event_listener.flow_expired', FlowExpiredEventListener::class)
        ->tag('kernel.event_listener', ['event' => FormFlowEvents::FLOW_EXPIRED, 'method' => 'onFlowExpired'])
        ->call('setTranslator', [service('translator')]);

    // Util + alias d’autowiring (BC)
    $services->set('craue_formflow_util', FormFlowUtil::class)
        ->public(true);

    $services->alias(FormFlowUtil::class, 'craue_formflow_util')
        ->public(false);

    // Twig extension
    $services->set('twig.extension.craue_formflow', FormFlowExtension::class)
        ->tag('twig.extension')
        ->call('setFormFlowUtil', [service('craue_formflow_util')]);
};