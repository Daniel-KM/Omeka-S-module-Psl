<?php
namespace Psl;

if (!class_exists(\Generic\AbstractModule::class)) {
    require file_exists(dirname(__DIR__) . '/Generic/AbstractModule.php')
        ? dirname(__DIR__) . '/Generic/AbstractModule.php'
        : __DIR__ . '/src/Generic/AbstractModule.php';
}

use Generic\AbstractModule;
use Omeka\Api\Representation\ItemRepresentation;
use Omeka\Permissions\Acl;
use Zend\EventManager\Event;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\View\Renderer\PhpRenderer;

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

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $sharedEventManager->attach(
            'Omeka\Controller\Site\Item',
            'view.show.before',
            [$this, 'handleViewShowBeforeItem']
        );
        $sharedEventManager->attach(
            'Omeka\Controller\Site\Media',
            'view.show.before',
            [$this, 'handleViewShowBeforeMedia']
        );

        $sharedEventManager->attach(
            \Omeka\Form\SettingForm::class,
            'form.add_elements',
            [$this, 'handleMainSettings']
        );
        $sharedEventManager->attach(
            \Omeka\Form\SettingForm::class,
            'form.add_input_filters',
            [$this, 'handleMainSettingsFilters']
        );
    }

    public function handleMainSettingsFilters(Event $event)
    {
        $event->getParam('inputFilter')
            ->get('psl')
            ->add([
                'name' => 'psl_reserved_item_sets',
                'required' => false,
            ])
            ->add([
                'name' => 'psl_reserved_media_types',
                'required' => false,
            ]);
    }

    public function handleViewShowBeforeItem(Event $event)
    {
        $view = $event->getTarget();
        $this->hideUvDownloadButton($view, $view->item, $view->item->media());
    }

    public function handleViewShowBeforeMedia(Event $event)
    {
        $view = $event->getTarget();
        $this->hideUvDownloadButton($view, $view->media->item(), [$view->media]);
    }

    protected function hideUvDownloadButton(PhpRenderer $view, ItemRepresentation $item, array $medias = [])
    {
        $services = $this->getServiceLocator();
        $settings = $services->get('Omeka\Settings');

        $reservedAll = $settings->get('psl_reserved_all');
        if ($reservedAll) {
            $view->headStyle()->appendStyle('.universal-viewer button.download { display: none; }');
            return true;
        }

        $reservedItemSets = $settings->get('psl_reserved_item_sets', []);
        if ($reservedItemSets) {
            $isReserved = (bool) array_intersect(array_keys($item->itemSets()), $reservedItemSets);
            if ($isReserved) {
                $view->headStyle()->appendStyle('.universal-viewer button.download { display: none; }');
                return true;
            }
        }

        $reservedMediaTypes = $settings->get('psl_reserved_media_types', []);
        if ($reservedMediaTypes) {
            foreach ($medias as $media) {
                $mediaType = $media->mediaType();
                if ($mediaType && iin_array($mediaType)) {
                    $view->headStyle()->appendStyle('.universal-viewer button.download { display: none; }');
                    return true;
                }
            }
        }

        return false;
    }
}
