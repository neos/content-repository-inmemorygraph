<?php

namespace Neos\ContentRepository\InMemoryGraph;

/*
 * This file is part of the Neos.ContentRepository.InMemoryGraph package.
 */

use Neos\ContentRepository\DimensionSpace\DimensionSpace;
use Neos\ContentRepository\Domain as ContentRepository;

/**
 * An in-memory content subgraph
 */
final class ContentSubgraph implements \JsonSerializable, \Countable
{
    /**
     * @todo replace with content stream identifier once available
     * @var ContentRepository\Model\Workspace
     */
    protected $workspace;

    /**
     * @var DimensionSpace\DimensionSpacePoint
     */
    protected $dimensionSpacePoint;

    /**
     * @var ContentSubgraphIdentifier
     */
    protected $identifier;

    /**
     * @var array|ContentRepository\Model\NodeInterface[]
     */
    protected $nodeIndex;

    /**
     * @var array|ReadOnlyNode[]
     */
    protected $readOnlyNodeIndex;

    /**
     * @var array|ContentRepository\Model\NodeInterface[]
     */
    protected $pathIndex;

    /**
     * @var TraversableNode
     */
    protected $rootNode;

    /**
     * @var array|Edge[]
     */
    protected $parentEdges = [];

    /**
     * @var array|Edge[][]
     */
    protected $childEdges = [];

    /**
     * @param ContentRepository\Model\Workspace $workspace
     * @param DimensionSpace\DimensionSpacePoint $dimensionSpacePoint
     */
    public function __construct(ContentRepository\Model\Workspace $workspace, DimensionSpace\DimensionSpacePoint $dimensionSpacePoint)
    {
        $this->workspace = $workspace;
        $this->dimensionSpacePoint = $dimensionSpacePoint;
        $this->identifier = new ContentSubgraphIdentifier($this->workspace->getName(), $this->dimensionSpacePoint);
    }

    public function registerNode(ReadOnlyNode $node)
    {
        $this->readOnlyNodeIndex[$node->getNodeIdentifier()] = $node;
        $traversableNode = new TraversableNode($node, $this);

        if (isset($this->pathIndex[$node->getParentPath()])) {
            $parentNode = $this->pathIndex[$node->getParentPath()];
            $edge = new Edge($parentNode, $traversableNode, $this, $node->getIndex() ?: 0, $node->getName());
            $this->parentEdges[$node->getIdentifier()] = $edge;
            $this->childEdges[$this->pathIndex[$node->getParentPath()]->getIdentifier()][$node->getName()] = $edge;
        } else {
            // orphaned or root node, no edges to be assigned
        }
        $this->nodeIndex[$traversableNode->getIdentifier()] = $traversableNode;
        $this->pathIndex[$node->getPath()] = $traversableNode;
        if ($node->getPath() === '/') {
            $this->rootNode = $traversableNode;
        }
    }

    public function unregisterNode(ReadOnlyNode $node)
    {
        if (isset($this->readOnlyNodeIndex[$node->getNodeIdentifier()])) {
            if (isset($this->pathIndex[$node->getPath()])) {
                unset($this->pathIndex[$node->getPath()]);
            }
            if (isset($this->nodeIndex[$node->getIdentifier()])) {
                unset($this->nodeIndex[$node->getIdentifier()]);
            }
            $traversableNode = $this->getNodeByIdentifier($node->getIdentifier());
            if ($traversableNode) {
                foreach ($this->getChildNodes($traversableNode) as $childNode) {
                    unset($this->parentEdges[$childNode->getIdentifier()]);
                }
                unset($this->childEdges[$traversableNode->getIdentifier()]);
            }
            if ($node->getPath() === '/') {
                $this->rootNode = null;
            }
            unset($this->readOnlyNodeIndex[$node->getNodeIdentifier()]);
        }
    }

    public function updateNodeAggregateIdentifier(string $oldIdentifier, string $newIdentifier)
    {
        if (isset($this->nodeIndex[$oldIdentifier])) {
            $this->nodeIndex[$newIdentifier] = $this->nodeIndex[$oldIdentifier];
            unset($this->nodeIndex[$oldIdentifier]);
        }
        if (isset($this->pathIndex[$oldIdentifier])) {
            $this->pathIndex[$newIdentifier] = $this->pathIndex[$oldIdentifier];
            unset($this->pathIndex[$oldIdentifier]);
        }
        if (isset($this->parentNodes[$oldIdentifier])) {
            $this->parentEdges[$newIdentifier] = $this->parentEdges[$oldIdentifier];
            unset($this->parentEdges[$oldIdentifier]);
        }
        if (isset($this->childNodes[$oldIdentifier])) {
            $this->childEdges[$newIdentifier] = $this->childEdges[$oldIdentifier];
            unset($this->childEdges[$oldIdentifier]);
        }
    }

    public function getNodeByIdentifier(string $identifier): ?TraversableNode
    {
        return $this->nodeIndex[$identifier] ?? null;
    }

    public function getNodeByNodeIdentifier(string $nodeIdentifier): ?TraversableNode
    {
        return isset($this->readOnlyNodeIndex[$nodeIdentifier]) ? $this->getNodeByIdentifier($this->readOnlyNodeIndex[$nodeIdentifier]->getIdentifier()) : null;
    }

    public function getNodeByPath(string $path): ?TraversableNode
    {
        return $this->pathIndex[$path] ?? null;
    }

    /**
     * @return array|TraversableNode[]
     */
    public function getNodes(): array
    {
        return $this->nodeIndex;
    }

    public function getParentNode(TraversableNode $node): ?TraversableNode
    {
        return isset($this->parentEdges[$node->getIdentifier()]) ? $this->parentEdges[$node->getIdentifier()]->getParent() : null;
    }

    public function getParentEdge(TraversableNode $node): ?Edge
    {
        return $this->parentEdges[$node->getIdentifier()] ?? null;
    }

    /**
     * @param TraversableNode $node
     * @return array|TraversableNode[]
     */
    public function getChildNodes(TraversableNode $node): array
    {
        if (!isset($this->childEdges[$node->getIdentifier()])) {
            return [];
        }
        return array_map(function(Edge $edge) {
            return $edge->getChild();
        }, $this->childEdges[$node->getIdentifier()]);
    }

    public function getChildNode(TraversableNode $node, string $edgeName): ?TraversableNode
    {
        return isset($this->childEdges[$node->getIdentifier()][$edgeName]) ? $this->childEdges[$node->getIdentifier()][$edgeName]->getChild() : null;
    }

    /**
     * @param TraversableNode $node
     * @return array|Edge[]
     */
    public function getChildEdges(TraversableNode $node): array
    {
        return $this->childEdges[$node->getIdentifier()] ?? [];
    }

    public function getChildEdge(TraversableNode $node, string $edgeName): ?Edge
    {
        return $this->childEdges[$node->getIdentifier()][$edgeName] ?? null;
    }

    public function getDimensionSpacePoint(): DimensionSpace\DimensionSpacePoint
    {
        return $this->dimensionSpacePoint;
    }

    public function getWorkspace(): ContentRepository\Model\Workspace
    {
        return $this->workspace;
    }

    public function getIdentifier(): ContentSubgraphIdentifier
    {
        return $this->identifier;
    }

    public function traverse(callable $action = null)
    {
        $this->traverseNode($this->rootNode, $action);
    }

    protected function traverseNode(TraversableNode $node, callable $action = null)
    {
        if ($action) {
            $continue = $action($node);
        } else {
            $continue = true;
        }
        if ($continue !== false) {
            foreach ($this->getChildNodes($node) as $node) {
                $this->traverseNode($node, $action);
            }
        }
    }

    public function __toString() : string
    {
        return (string) $this->getIdentifier();
    }

    public function jsonSerialize(): ContentSubgraphIdentifier
    {
        return $this->getIdentifier();
    }

    public function count(): int
    {
        return count($this->nodeIndex);
    }
}
