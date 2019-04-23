<?php

declare(strict_types=1);

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

    /**
     * @param ReadOnlyNode $node
     * @return void
     */
    public function registerNode(ReadOnlyNode $node): void
    {
        $this->readOnlyNodeIndex[$node->getNodeIdentifier()] = $node;
        $traversableNode = new TraversableNode($node, $this);

        if (isset($this->pathIndex[$node->getParentPath()])) {
            $parentNode = $this->pathIndex[$node->getParentPath()];
            $edge = new Edge($parentNode, $traversableNode, $this, (string)$this->identifier, $node->getIndex() ?: 0, $node->getName());
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

    /**
     * @param ContentRepository\Model\NodeInterface $node
     * @return void
     */
    public function unregisterNode(ReadOnlyNode $node): void
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

    /**
     * @param string $oldIdentifier
     * @param string $newIdentifier
     * @return void
     */
    public function updateNodeAggregateIdentifier(string $oldIdentifier, string $newIdentifier): void
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

    /**
     * @param string $identifier
     * @return TraversableNode|null
     */
    public function getNodeByIdentifier(string $identifier): ?TraversableNode
    {
        return $this->nodeIndex[$identifier] ?? null;
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
        return $this->nodeIndex;
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
     * @return Edge|null
     */
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
        return array_map(function (Edge $edge) {
            return $edge->getChild();
        }, $this->childEdges[$node->getIdentifier()]);
    }

    /**
     * @param TraversableNode $node
     * @param string $edgeName
     * @return TraversableNode|null
     */
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

    /**
     * @param TraversableNode $node
     * @param string $edgeName
     * @return Edge|null
     */
    public function getChildEdge(TraversableNode $node, string $edgeName): ?Edge
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
     * @return Edge
     */
    public function connectNodes(Node $parent, Node $child, $position = 'start', $name = null, array $properties = []) : Edge
    {
        $edge = new Edge($parent, $child, $this, $position, $name, $properties);
        $parent->registerOutgoingEdge($edge);
        $child->registerIncomingEdge($edge);

        return $edge;
    }

    /**
     * @param Edge $edge
     * @return void
     */
    public function disconnectNodes(Edge $edge)
    {
        $edge->getParent()->unregisterOutgoingEdge($edge);
        $edge->getChild()->unregisterIncomingEdge($edge);
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
     * @param Edge $edge
     * @param callable $edgeAction
     * @param callable $nodeAction
     * @return void
     */
    protected function traverseEdge(Edge $edge, callable $edgeAction = null, callable $nodeAction = null)
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
        return count($this->nodeIndex);
    }
}
