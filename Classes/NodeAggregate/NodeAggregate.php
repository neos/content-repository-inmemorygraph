<?php

declare(strict_types=1);

namespace Neos\ContentRepository\InMemoryGraph\NodeAggregate;

/*
 * This file is part of the Neos.ContentRepository.InMemoryGraph package.
 */

use Neos\ContentRepository\DimensionSpace\DimensionSpace;
use Neos\ContentRepository\Domain as ContentRepository;
use Neos\ContentRepository\InMemoryGraph\Dimension\LegacyConfigurationAndWorkspaceBasedContentDimensionSource;

/**
 * A read only node aggregate
 */
final class NodeAggregate implements \Countable
{
    const ROOT_IDENTIFIER = '00000000-0000-0000-0000-000000000000';

    /**
     * @var ContentRepository\NodeAggregate\NodeAggregateIdentifier
     */
    protected $identifier;

    /**
     * @var array|Node[]
     */
    protected $nodes = [];

    /**
     * @var array|Node[]
     */
    protected $nodesWithoutWorkspace = [];

    /**
     * @var array|Node[]
     */
    protected $shadowNodes = [];

    /**
     * @param ContentRepository\NodeAggregate\NodeAggregateIdentifier $identifier
     * @param array|Node[] $nodes
     */
    public function __construct(ContentRepository\NodeAggregate\NodeAggregateIdentifier $identifier, array $nodes)
    {
        $this->identifier = $identifier;
        $this->addNodes($nodes);
    }

    public function addNodes(array $nodes): void
    {
        foreach ($nodes as $node) {
            if (!$node->getWorkspace()) {
                $this->nodesWithoutWorkspace[] = $node;
            } else {
                $coordinates = [];
                foreach ($node->getNodeData()->getDimensionValues() as $dimensionName => $rawDimensionValues) {
                    $coordinates[$dimensionName] = reset($rawDimensionValues);
                }
                $coordinates[LegacyConfigurationAndWorkspaceBasedContentDimensionSource::WORKSPACE_DIMENSION_IDENTIFIER] = $node->getWorkspace()->getName();
                $dimensionSpacePoint = new DimensionSpace\DimensionSpacePoint($coordinates);
                if ($node->getNodeData()->getMovedTo()) {
                    $this->shadowNodes[$dimensionSpacePoint->getHash()] = $node;
                } else {
                    $this->nodes[$dimensionSpacePoint->getHash()] = $node;
                }
            }
        }
    }

    public function removeNode(DimensionSpace\DimensionSpacePoint $dimensionSpacePoint): void
    {
        if (isset($this->nodes[$dimensionSpacePoint->getHash()])) {
            unset($this->nodes[$dimensionSpacePoint->getHash()]);
        }
    }

    public function getNodeByDimensionSpacePoint(DimensionSpace\DimensionSpacePoint $dimensionSpacePoint): ?Node
    {
        return $this->nodes[$dimensionSpacePoint->getHash()] ?? null;
    }

    /**
     * @return array|Node[]
     */
    public function getShadowNodes(): array
    {
        return $this->shadowNodes;
    }
    public function getShadowNodeByDimensionSpacePoint(DimensionSpace\DimensionSpacePoint $dimensionSpacePoint): ?Node
    {
        return $this->shadowNodes[$dimensionSpacePoint->getHash()] ?? null;
    }

    /**
     * @param ContentRepository\Model\Workspace $workspace
     * @return array|Node[]
     */
    public function getNodesByWorkspace(ContentRepository\Model\Workspace $workspace): array
    {
        $nodesByWorkspace = [];
        foreach ($this->nodes as $node) {
            if ($node->getWorkspace() === $workspace) {
                $nodesByWorkspace[] = $node;
            }
        }

        return $nodesByWorkspace;
    }

    /**
     * @return array|Node[]
     */
    public function getNodesWithoutWorkspace(): array
    {
        return $this->nodesWithoutWorkspace;
    }

    /**
     * @return array|Node[]
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    public function getIdentifier(): ContentRepository\NodeAggregate\NodeAggregateIdentifier
    {
        return $this->identifier;
    }

    public function isRoot(): bool
    {
        return $this->identifier === self::ROOT_IDENTIFIER;
    }

    public function count(): int
    {
        return count($this->nodes);
    }
    public function isEmpty(): bool
    {
        return count($this->nodes) === 0;
    }
}
