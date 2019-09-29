<?php

namespace Psl;

use Zend\Mvc\MvcEvent;
use Omeka\Module\AbstractModule;
use Omeka\Permissions\Acl;

class Module extends AbstractModule
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap(MvcEvent $event)
    {
        parent::onBootstrap($event);

        $acl = $this->getServiceLocator()->get('Omeka\Acl');

        if ($acl->hasResource('ZoteroImport\Controller\Index')) {
            $acl
                ->allow(
                    [Acl::ROLE_AUTHOR],
                    [
                        'ZoteroImport\Controller\Index',
                        \ZoteroImport\Api\Adapter\ZoteroImportAdapter::class,
                        \ZoteroImport\Api\Adapter\ZoteroImportItemAdapter::class,
                        \ZoteroImport\Entity\ZoteroImport::class,
                        \ZoteroImport\Entity\ZoteroImportItem::class,
                    ]
                );
        }

        if ($acl->hasResource('CSVImport\Controller\Index')) {
            $acl
                ->allow(
                    [Acl::ROLE_AUTHOR],
                    [
                        'CSVImport\Controller\Index',
                        \CSVImport\Api\Adapter\EntityAdapter::class,
                        \CSVImport\Api\Adapter\ImportAdapter::class,
                        \CSVImport\Entity\CSVImportEntity::class,
                        \CSVImport\Entity\CSVImportImport::class,
                    ]
                )
                ->allow(
                    [Acl::ROLE_AUTHOR],
                    [\Omeka\Api\Adapter\ItemAdapter::class],
                    ['batch_create']
                );
        }
    }
}
