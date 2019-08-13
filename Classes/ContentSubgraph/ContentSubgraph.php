<?php

declare(strict_types=1);

namespace Neos\ContentRepository\InMemoryGraph\ContentSubgraph;

/*
 * This file is part of the Neos.ContentRepository.InMemoryGraph package.
 */

use Neos\ContentRepository\DimensionSpace\DimensionSpace;
use Neos\ContentRepository\Domain as ContentRepository;
use Neos\ContentRepository\InMemoryGraph\NodeAggregate\Node;

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
     * @var array|ContentRepository\Projection\Content\TraversableNodeInterface[]
     */
    protected $traversableNodeIndex;

    /**
     * @var array|ContentRepository\Projection\Content\NodeInterface[]
     */
    protected $nodeIndex;

    /**
     * @var array|ContentRepository\Projection\Content\TraversableNodeInterface[]
     */
    protected $pathIndex;

    /**
     * @var ContentRepository\Projection\Content\TraversableNodeInterface
     */
    protected $rootNode;

    /**
     * @var array|HierarchyRelation[]
     */
    protected $parentEdges = [];

    /**
     * @var array|HierarchyRelation[][]
     */
    protected $childEdges = [];

    /**
     * @var ContentGraph
     */
    protected $graph;

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

    /**
     * @param ContentRepository\Projection\Content\NodeInterface $node
     * @return void
     */
    public function registerNode(ContentRepository\Projection\Content\NodeInterface $node): void
    {
        $this->nodeIndex[(string) $node->getNodeAggregateIdentifier()] = $node;
        $traversableNode = new TraversableNode($node, $this);

        if (isset($this->pathIndex[$node->getParentPath()])) {
            $traversableParentNode = $this->pathIndex[$node->getParentPath()];
            $edge = new HierarchyRelation($this->nodeIndex[(string)$traversableParentNode->getNodeAggregateIdentifier()], $node, $this, (string)$this->identifier, $node->getIndex() ?: 0, $node->getNodeName());
            $this->parentEdges[$node->getCacheEntryIdentifier()] = $edge;
            $this->childEdges[$this->pathIndex[$node->getParentPath()]->getIdentifier()][$node->getName()] = $edge;
        } else {
            // orphaned or root node, no edges to be assigned
        }
        $this->traversableNodeIndex[$traversableNode->getIdentifier()] = $traversableNode;
        $this->pathIndex[$node->getPath()] = $traversableNode;
        if ($node->getPath() === '/') {
            $this->rootNode = $traversableNode;
        }
    }

    public function unregisterNode(Node $node): void
    {
        if (isset($this->nodeIndex[(string) $node->getNodeAggregateIdentifier()])) {
            if (isset($this->pathIndex[$node->getPath()])) {
                unset($this->pathIndex[$node->getPath()]);
            }
            if (isset($this->traversableNodeIndex[$node->getCacheEntryIdentifier()])) {
                unset($this->traversableNodeIndex[$node->getCacheEntryIdentifier()]);
            }
            $traversableNode = $this->getNodeByIdentifier($node->getCacheEntryIdentifier());
            if ($traversableNode) {
                foreach ($this->getChildNodes($traversableNode) as $childNode) {
                    unset($this->parentEdges[$childNode->getCacheEntryIdentifier()]);
                }
                unset($this->childEdges[$traversableNode->getCacheEntryIdentifier()]);
            }
            if ($node->getPath() === '/') {
                $this->rootNode = null;
            }
            unset($this->nodeIndex[(string) $node->getNodeAggregateIdentifier()]);
        }
    }

    public function updateNodeAggregateIdentifier(string $oldIdentifier, string $newIdentifier): void
    {
        if (isset($this->traversableNodeIndex[$oldIdentifier])) {
            $this->traversableNodeIndex[$newIdentifier] = $this->traversableNodeIndex[$oldIdentifier];
            unset($this->traversableNodeIndex[$oldIdentifier]);
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

    /**
     * @param string $identifier
     * @return TraversableNode|null
     */
    public function getNodeByIdentifier(string $identifier): ?TraversableNode
    {
        return $this->traversableNodeIndex[$identifier] ?? null;
    }

    /**
     * @param string $nodeIdentifier
     * @return Node|null
     */
    public function getNode($nodeIdentifier)
    {
        return $this->nodeRegistry[$nodeIdentifier] ?? null;
    }

    /**
     * @param string $path
     * @return TraversableNode|null
     */
    public function getNodeByPath(string $path): ?TraversableNode
    {
        return $this->pathIndex[$path] ?? null;
    }

    /**
     * @return array|TraversableNode[]
     */
    public function getNodes(): array
    {
        return $this->traversableNodeIndex;
    }

    public function getParentNode(TraversableNode $node): ?TraversableNode
    {
        $incomingHierarchyRelation = $node->getDataNode()->getIncomingHierarchyRelationInSubgraph($this->identifier);
        if ($incomingHierarchyRelation) {
            return new TraversableNode($incomingHierarchyRelation->getParent(), $this);
        }
        return null;
    }

    /**
     * @param TraversableNode $node
     * @return HierarchyRelation|null
     */
    public function getParentEdge(TraversableNode $node): ?HierarchyRelation
    {
        return $this->parentEdges[(string)$node->getNodeAggregateIdentifier()] ?? null;
    }

    /**
     * @param TraversableNode $node
     * @return array|TraversableNode[]
     */
    public function getChildNodes(TraversableNode $node): array
    {
        if (!isset($this->childEdges[(string)$node->getNodeAggregateIdentifier()])) {
            return [];
        }
        return array_map(function (HierarchyRelation $edge) {
            return $edge->getChild();
        }, $this->childEdges[(string)$node->getNodeAggregateIdentifier()]);
    }

    /**
     * @param TraversableNode $node
     * @param ContentRepository\NodeAggregate\NodeName $edgeName
     * @return TraversableNode|null
     */
    public function getChildNode(TraversableNode $node, ContentRepository\NodeAggregate\NodeName $edgeName): ?TraversableNode
    {
        return isset($this->childEdges[(string)$node->getNodeAggregateIdentifier()][(string)$edgeName]) ? $this->childEdges[(string)$node->getNodeAggregateIdentifier()][(string)$edgeName]->getChild() : null;
    }

    /**
     * @param TraversableNode $node
     * @return array|HierarchyRelation[]
     */
    public function getChildEdges(TraversableNode $node): array
    {
        return $this->childEdges[(string)$node->getNodeAggregateIdentifier()] ?? [];
    }

    /**
     * @param TraversableNode $node
     * @param string $edgeName
     * @return HierarchyRelation|null
     */
    public function getChildEdge(TraversableNode $node, string $edgeName): ?HierarchyRelation
    {
        return $this->childEdges[(string)$node->getNodeAggregateIdentifier()][$edgeName] ?? null;
    }

    public function setGraph(ContentGraph $graph): void
    {
        $this->graph = $graph;
    }

    public function getDimensionSpacePoint(): DimensionSpace\DimensionSpacePoint
    {
        return $this->dimensionSpacePoint;
    }

    public function getWorkspace(): ContentRepository\Model\Workspace
    {
        return $this->workspace;
    }

    public function getGraph() : ?ContentGraph
    {
        return $this->graph;
    }

    public function connectNodes(Node $parent, Node $child, int $position, ContentRepository\NodeAggregate\NodeName $name = null, array $properties = []) : HierarchyRelation
    {
        $hierarchyRelation = new HierarchyRelation($parent, $child, $this, (string)$this->identifier, $position, $name, $properties);
        $parent->registerOutgoingHierarchyRelation($hierarchyRelation);
        $child->registerIncomingHierarchyRelation($hierarchyRelation);

        return $hierarchyRelation;
    }

    public function disconnectNodes(HierarchyRelation $hierarchyRelation)
    {
        $hierarchyRelation->getParent()->unregisterOutgoingHierarchyRelation($hierarchyRelation);
        $hierarchyRelation->getChild()->unregisterIncomingRelation($hierarchyRelation);
        unset($hierarchyRelation);
    }

    /**
     * @param callable $nodeAction
     * @param callable $edgeAction
     * @return void
     */
    public function traverse(callable $nodeAction = null, callable $edgeAction = null)
    {
        $this->traverseNode($this->graph->getRootNode(), $nodeAction, $edgeAction);
    }

    /**
     * @param Node $node
     * @param callable $nodeAction
     * @param callable $edgeAction
     * @return void
     */
    protected function traverseNode(Node $node, callable $nodeAction = null, callable $edgeAction = null)
    {
        if ($nodeAction) {
            $continue = $nodeAction($node);
        } else {
            $continue = true;
        }
        if ($continue !== false) {
            foreach ($node->getOutgoingHierarchyRelationsInSubgraph($this) as $edge) {
                $this->traverseEdge($edge, $edgeAction, $nodeAction);
            }
        }
    }

    /**
     * @param HierarchyRelation $edge
     * @param callable $edgeAction
     * @param callable $nodeAction
     * @return void
     */
    protected function traverseEdge(HierarchyRelation $edge, callable $edgeAction = null, callable $nodeAction = null)
    {
        if ($edgeAction) {
            $continue = $edgeAction($edge);
        } else {
            $continue = true;
        }
        if ($continue !== false) {
            $this->traverseNode($edge->getChild(), $nodeAction, $edgeAction);
        }
    }

    public function getIdentifier(): ContentSubgraphIdentifier
    {
        return $this->identifier;
    }

    public function __toString() : string
    {
        return (string) $this->getIdentifier();
    }

    public function jsonSerialize(): ContentSubgraphIdentifier
    {
        return $this->getIdentifier();
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->traversableNodeIndex);
    }
}
