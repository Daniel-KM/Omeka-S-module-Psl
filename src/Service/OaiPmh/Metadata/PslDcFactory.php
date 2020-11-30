<?php

namespace Psl\Service\OaiPmh\Metadata;

use Interop\Container\ContainerInterface;
use Psl\OaiPmh\Metadata\PslDc;
use Laminas\ServiceManager\Factory\FactoryInterface;

class PslDcFactory implements FactoryInterface
{
    /**
     * Create the media ingester manager service.
     *
     * @return PslDc
     */
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $settings = $services->get('Omeka\Settings');
        $oaiSetManager = $services->get('OaiPmhRepository\OaiPmh\OaiSetManager');
        $oaiSet = $oaiSetManager->get($settings->get('oaipmhrepository_oai_set_format', 'base'));
        $metadataFormat = new PslDc();
        $metadataFormat->setSettings($settings);
        $metadataFormat->setOaiSet($oaiSet);
        $isGlobalRepository = !$services->get('ControllerPluginManager')
            ->get('params')->fromRoute('__SITE__', false);
        $metadataFormat->setIsGlobalRepository($isGlobalRepository);
        return $metadataFormat;
    }
}
