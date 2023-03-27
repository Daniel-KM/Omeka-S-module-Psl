<?php declare(strict_types=1);

namespace Psl\Form;

use Laminas\Form\Element;
use Laminas\Form\Fieldset;
use Omeka\Form\Element\ItemSetSelect;
use Psl\Form\Element\MediaTypeSelect;

class SettingsFieldset extends Fieldset
{
    /**
     * @var string
     */
    protected $label = 'PSL'; // @translate

    protected $elementGroups = [
        'psl' => 'psl', // @translate
    ];

    public function init(): void
    {
        $this
            ->setAttribute('id', 'psl')
            ->setOption('element_groups', $this->elementGroups)
            ->add([
                'name' => 'psl_reserved_all',
                'type' => Element\Checkbox::class,
                'options' => [
                    'element_group' => 'psl',
                    'label' => 'Désactiver le bouton "Télécharger" de Universal Viewer', // @translate
                ],
                'attributes' => [
                    'id' => 'psl_reserved_all',
                ],
            ])
            ->add([
                'name' => 'psl_reserved_item_sets',
                'type' => ItemSetSelect::class,
                'options' => [
                    'element_group' => 'psl',
                    'label' => 'Désactiver le bouton "Télécharger" de Universal Viewer pour les collections', // @translate
                    'empty_option' => '',
                ],
                'attributes' => [
                    'id' => 'psl_reserved_item_sets',
                    'multiple' => true,
                    'required' => false,
                    'class' => 'chosen-select',
                    'data-placeholder' => 'Select an item set', // @translate
                ],
            ])
            ->add([
                'name' => 'psl_reserved_media_types',
                'type' => MediaTypeSelect::class,
                'options' => [
                    'element_group' => 'psl',
                    'label' => 'Désactiver le bouton "Télécharger" de Universal Viewer pour les types de média', // @translate
                    'empty_option' => '',
                ],
                'attributes' => [
                    'id' => 'psl_reserved_media_types',
                    'multiple' => true,
                    'required' => false,
                    'class' => 'chosen-select',
                    'data-placeholder' => 'Select media types', // @translate
                ],
            ])
        ;
    }
}
