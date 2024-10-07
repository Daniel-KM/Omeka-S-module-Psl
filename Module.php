<?php declare(strict_types=1);

namespace Psl;

if (!class_exists(\Common\TraitModule::class)) {
    require_once dirname(__DIR__) . '/Common/TraitModule.php';
}

use Common\TraitModule;
use Laminas\EventManager\Event;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Api\Representation\ItemRepresentation;
use Omeka\Module\AbstractModule;
use Omeka\Permissions\Acl;

/**
 * PSL
 *
 * Divers modifications et fonctionnalités pour le site de PSL (https://bibnum.explore.psl.eu).
 *
 * @copyright Daniel Berthereau, 2017-2024
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt
 */
class Module extends AbstractModule
{
    use TraitModule;

    const NAMESPACE = __NAMESPACE__;

    public function onBootstrap(MvcEvent $event): void
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

    protected function preInstall(): void
    {
        $services = $this->getServiceLocator();
        $plugins = $services->get('ControllerPluginManager');
        $translate = $plugins->get('translate');

        if (!method_exists($this, 'checkModuleActiveVersion') || !$this->checkModuleActiveVersion('Common', '3.4.62')) {
            $message = new \Omeka\Stdlib\Message(
                $translate('The module %1$s should be upgraded to version %2$s or later.'), // @translate
                'Common', '3.4.62'
            );
            throw new \Omeka\Module\Exception\ModuleCannotInstallException((string) $message);
        }
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager): void
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
    }

    public function handleViewShowBeforeItem(Event $event): void
    {
        $view = $event->getTarget();
        $this->hideUvDownloadButton($view, $view->item, $view->item->media());
    }

    public function handleViewShowBeforeMedia(Event $event): void
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
            $view->headStyle()->appendStyle('.universal-viewer button.download { display: none !important; }');
            return true;
        }

        $reservedItemSets = $settings->get('psl_reserved_item_sets', []);
        if ($reservedItemSets) {
            $isReserved = (bool) array_intersect(array_keys($item->itemSets()), $reservedItemSets);
            if ($isReserved) {
                $view->headStyle()->appendStyle('.universal-viewer button.download { display: none !important; }');
                return true;
            }
        }

        $reservedMediaTypes = $settings->get('psl_reserved_media_types', []);
        if ($reservedMediaTypes) {
            foreach ($medias as $media) {
                $mediaType = $media->mediaType();
                if ($mediaType && in_array($mediaType, $reservedMediaTypes)) {
                    $view->headStyle()->appendStyle('.universal-viewer button.download { display: none !important; }');
                    return true;
                }
            }
        }

        return false;
    }
}
