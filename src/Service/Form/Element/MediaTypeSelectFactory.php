<?php declare(strict_types=1);
namespace Psl\Service\Form\Element;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psl\Form\Element\MediaTypeSelect;

class MediaTypeSelectFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $list = $this->listMediaTypes($services);

        $element = new MediaTypeSelect(null, $options ?? []);
        return $element
            ->setValueOptions($list)
            ->setEmptyOption('Select media type…'); // @translate
    }

    protected function listMediaTypes(ContainerInterface $services)
    {
        $connection = $services->get('Omeka\Connection');
        $sql = <<<SQL
SELECT DISTINCT(media_type)
FROM media
WHERE media_type IS NOT NULL
AND media_type != ""
ORDER BY media_type ASC
SQL;
        $result = $connection->executeQuery($sql)->fetchFirstColumn();
        return array_combine($result, $result);
    }
}
