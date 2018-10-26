<?php
namespace Neos\ContentRepository\InMemoryGraph;

/*
 * This file is part of the Neos.ContentRepository.InMemoryGraph package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Domain\Model\NodeData;
use Neos\ContentRepository\DimensionSpace\DimensionSpace;

/**
 * @Flow\Scope("singleton")
 */
class DimensionSpacePointFactory
{
    /**
     * @Flow\Inject
     * @var Dimension\LegacyConfigurationAndWorkspaceBasedContentDimensionSource
     */
    protected $contentDimensionSource;

    /**
     * Create a DimensionSpacePoint from a given NodeData object, considering
     * its dimension values and workspace
     *
     * @param NodeData $nodeDataRecord
     * @return DimensionSpace\DimensionSpacePoint
     */
    public function createFromNodeData(NodeData $nodeDataRecord): DimensionSpace\DimensionSpacePoint
    {
        $coordinates = [];
        $rawDimensionValues = $nodeDataRecord->getDimensionValues();
        foreach ($this->contentDimensionSource->getContentDimensionsOrderedByPriority() as $contentDimensionIdentifier => $contentDimension) {
            if ($contentDimensionIdentifier === '_workspace') {
                $coordinates['_workspace'] = $nodeDataRecord->getWorkspace() ? $nodeDataRecord->getWorkspace()->getName() : '_';
            } else {
                $coordinates[$contentDimensionIdentifier] = isset($rawDimensionValues[$contentDimensionIdentifier]) ? reset($rawDimensionValues[$contentDimensionIdentifier]) : '_';
            }

        }

        return new DimensionSpace\DimensionSpacePoint($coordinates);
    }
}
