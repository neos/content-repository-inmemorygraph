<?php

declare(strict_types=1);

namespace Neos\ContentRepository\InMemoryGraph;

/*
 * This file is part of the Neos.ContentRepository.InMemoryGraph package.
 */

use Neos\ContentRepository\DimensionSpace\DimensionSpace;
use Neos\ContentRepository\Domain as ContentRepository;

/**
 * A read only node aggregate
 */
final class ReadOnlyNodeAggregate implements \Countable
{
    const ROOT_IDENTIFIER = '00000000-0000-0000-0000-000000000000';

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var array|ReadOnlyNode[]
     */
    protected $nodes = [];

    /**
     * @var array|ReadOnlyNode[]
     */
    protected $nodesWithoutWorkspace = [];

    /**
     * @var array|ReadOnlyNode[]
     */
    protected $shadowNodes = [];

    /**
     * @param string $identifier
     * @param array|ReadOnlyNode[] $nodes
     */
    public function __construct(string $identifier, array $nodes)
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
                $coordinates['_workspace'] = $node->getWorkspace()->getName();
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

    public function getNodeByDimensionSpacePoint(DimensionSpace\DimensionSpacePoint $dimensionSpacePoint): ?ReadOnlyNode
    {
        return $this->nodes[$dimensionSpacePoint->getHash()] ?? null;
    }

    /**
     * @return array|ReadOnlyNode[]
     */
    public function getShadowNodes(): array
    {
        return $this->shadowNodes;
    }
    public function getShadowNodeByDimensionSpacePoint(DimensionSpace\DimensionSpacePoint $dimensionSpacePoint): ?ReadOnlyNode
    {
        return $this->shadowNodes[$dimensionSpacePoint->getHash()] ?? null;
    }

    /**
     * @param ContentRepository\Model\Workspace $workspace
     * @return array|ReadOnlyNode[]
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
     * @return array|ReadOnlyNode[]
     */
    public function getNodesWithoutWorkspace(): array
    {
        return $this->nodesWithoutWorkspace;
    }

    /**
     * @return array|ReadOnlyNode[]
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    public function getIdentifier(): string
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
