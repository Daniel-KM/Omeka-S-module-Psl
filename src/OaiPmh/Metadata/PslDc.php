<?php declare(strict_types=1);

namespace Psl\OaiPmh\Metadata;

use DOMElement;
use OaiPmhRepository\OaiPmh\Metadata\AbstractMetadata;
use Omeka\Api\Representation\ItemRepresentation;

/**
 * Class implmenting metadata output for the psl_dc metadata format.
 */
class PslDc extends AbstractMetadata
{
    /** OAI-PMH metadata prefix */
    const METADATA_PREFIX = 'psl_dc';

    /** XML namespace for output format */
    const METADATA_NAMESPACE = 'http://purl.org/psl/psl_dc/';

    /** XML schema for output format */
    const METADATA_SCHEMA = 'http://purl.org/psl/psl_dc/psl_dc.xsd';

    /** XML namespace for unqualified Dublin Core */
    const DC_NAMESPACE_URI = 'http://purl.org/dc/elements/1.1/';

    /**
     * Appends Dublin Core metadata.
     *
     * Appends a metadata element, an child element with the required format,
     * and further children for each of the Dublin Core fields present in the
     * item.
     */
    public function appendMetadata(DOMElement $metadataElement, ItemRepresentation $item): void
    {
        $document = $metadataElement->ownerDocument;

        $psl_dc = $document->createElementNS(self::METADATA_NAMESPACE, 'psl_dc:dc');
        $metadataElement->appendChild($psl_dc);

        /* Must manually specify XML schema uri per spec, but DOM won't include
         * a redundant xmlns:xsi attribute, so we just set the attribute
         */
        $psl_dc->setAttribute('xmlns:dc', self::DC_NAMESPACE_URI);
        $psl_dc->setAttribute('xmlns:xsi', parent::XML_SCHEMA_NAMESPACE_URI);
        $psl_dc->setAttribute('xsi:schemaLocation', self::METADATA_NAMESPACE . ' ' .
            self::METADATA_SCHEMA);

        /* Each of the 16 unqualified Dublin Core elements, in the order
         * specified by the oai_dc XML schema
         */
        $dcElementNames = [
            'title', 'creator', 'subject', 'description', 'publisher',
            'contributor', 'date', 'type', 'format', 'identifier', 'source',
            'language', 'relation', 'coverage', 'rights',
        ];

        /* Must create elements using createElement to make DOM allow a
         * top-level xmlns declaration instead of wasteful and non-
         * compliant per-node declarations.
         */
        foreach ($dcElementNames as $elementName) {
            $values = $item->value("dcterms:$elementName", ['all' => true]);
            if (!empty($values)) {
                foreach ($values as $value) {
                    $this->appendNewElement($psl_dc, "dc:$elementName", (string) $value);
                }
            }
        }

        $values = $item->value('dcpsl:institution', ['all' => true]);
        if (!empty($values)) {
            foreach ($values as $value) {
                $element = $this->appendNewElement($psl_dc, 'dc:source', (string) $value);
                $element->setAttribute('xsi:type', 'psl_dc:institution');
            }
        }

        $values = $item->value('dcpsl:rameau', ['all' => true]);
        if (!empty($values)) {
            foreach ($values as $value) {
                $element = $this->appendNewElement($psl_dc, 'dc:subject', (string) $value);
                $element->setAttribute('xsi:type', 'psl_dc:rameau');
            }
        }

        $appendIdentifier = $this->singleIdentifier($item);
        if ($appendIdentifier) {
            $this->appendNewElement($psl_dc, 'dc:identifier', $appendIdentifier);
        }

        // Also append an identifier for each file
        if ($this->settings->get('oaipmhrepository_expose_media', false)) {
            foreach ($item->media() as $media) {
                $this->appendNewElement($psl_dc, 'dc:identifier', $media->originalUrl());
            }
        }
    }

    public function getMetadataPrefix()
    {
        return self::METADATA_PREFIX;
    }

    public function getMetadataSchema()
    {
        return self::METADATA_SCHEMA;
    }

    public function getMetadataNamespace()
    {
        return self::METADATA_NAMESPACE;
    }
}
