<?php
namespace Psl;

if (!class_exists(\Generic\AbstractModule::class)) {
    require file_exists(dirname(__DIR__) . '/Generic/AbstractModule.php')
        ? dirname(__DIR__) . '/Generic/AbstractModule.php'
        : __DIR__ . '/src/Generic/AbstractModule.php';
}

use Generic\AbstractModule;
use Omeka\Permissions\Acl;
use Zend\Mvc\MvcEvent;

class Module extends AbstractModule
{
    const NAMESPACE = __NAMESPACE__;

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
