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
     * @param Node $node
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

    /**
     * @param ContentRepository\Model\NodeInterface $node
     * @return void
     */
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

    /**
     * @param string $oldIdentifier
     * @param string $newIdentifier
     * @return void
     */
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

    /**
     * @param TraversableNode $node
     * @return TraversableNode|null
     */
    public function getParentNode(TraversableNode $node): ?TraversableNode
    {
        return isset($this->parentEdges[$node->getIdentifier()]) ? $this->parentEdges[$node->getIdentifier()]->getParent() : null;
    }

    /**
     * @param TraversableNode $node
     * @return HierarchyRelation|null
     */
    public function getParentEdge(TraversableNode $node): ?HierarchyRelation
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
        return array_map(function (HierarchyRelation $edge) {
            return $edge->getChild();
        }, $this->childEdges[$node->getIdentifier()]);
    }

    /**
     * @param TraversableNode $node
     * @param ContentRepository\NodeAggregate\NodeName $edgeName
     * @return TraversableNode|null
     */
    public function getChildNode(TraversableNode $node, ContentRepository\NodeAggregate\NodeName $edgeName): ?TraversableNode
    {
        return isset($this->childEdges[$node->getIdentifier()][(string)$edgeName]) ? $this->childEdges[$node->getIdentifier()][(string)$edgeName]->getChild() : null;
    }

    /**
     * @param TraversableNode $node
     * @return array|HierarchyRelation[]
     */
    public function getChildEdges(TraversableNode $node): array
    {
        return $this->childEdges[$node->getIdentifier()] ?? [];
    }

    /**
     * @param TraversableNode $node
     * @param string $edgeName
     * @return HierarchyRelation|null
     */
    public function getChildEdge(TraversableNode $node, string $edgeName): ?HierarchyRelation
    {
        return $this->childEdges[$node->getIdentifier()][$edgeName] ?? null;
    }

    /**
     * @param Graph $graph
     * @return void
     */
    public function setGraph(Graph $graph)
    {
        $this->graph = $graph;
    }

    /**
     * @return DimensionSpace\DimensionSpacePoint
     */
    public function getDimensionSpacePoint(): DimensionSpace\DimensionSpacePoint
    {
        return $this->dimensionSpacePoint;
    }

    /**
     * @return ContentRepository\Model\Workspace
     */
    public function getWorkspace(): ContentRepository\Model\Workspace
    {
        return $this->workspace;
    }

    /**
     * @return Tree|null
     */
    public function getFallback()
    {
        return $this->fallback;
    }

    /**
     * @return array|Tree[]
     */
    public function getVariants() : array
    {
        return $this->variants;
    }

    /**
     * @param Tree $tree
     * @return void
     */
    public function addVariant(Tree $tree)
    {
        $this->variants[$tree->getIdentityHash()] = $tree;
    }

    /**
     * @return Graph
     */
    public function getGraph() : Graph
    {
        return $this->graph;
    }

    /**
     * @param Node $parent
     * @param Node $child
     * @param string $position
     * @param string $name
     * @param array $properties
     * @return HierarchyRelation
     */
    public function connectNodes(Node $parent, Node $child, $position = 'start', $name = null, array $properties = []) : HierarchyRelation
    {
        $edge = new HierarchyRelation($parent, $child, $this, $position, $name, $properties);
        $parent->registerOutgoingEdge($edge);
        $child->registerIncomingHierarchyRelation($edge);

        return $edge;
    }

    /**
     * @param HierarchyRelation $edge
     * @return void
     */
    public function disconnectNodes(HierarchyRelation $edge)
    {
        $edge->getParent()->unregisterOutgoingEdge($edge);
        $edge->getChild()->unregisterIncomingRelation($edge);
        unset($edge);
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
            foreach ($node->getOutgoingEdgesInTree($this) as $edge) {
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
