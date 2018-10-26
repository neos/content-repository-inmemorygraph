<?php

namespace Neos\ContentRepository\InMemoryGraph;

/*
 * This file is part of the Neos.ContentRepository.InMemoryGraph package.
 */

use Neos\Flow\Cli\ConsoleOutput;

/**
 * The in-memory content graph
 */
final class ContentGraph
{
    /**
     * @var array|ContentSubgraph[]
     */
    protected $subgraphs;

    /**
     * @var array|ReadOnlyNode[]
     */
    protected $nodeIndex;

    /**
     * @var array|ReadOnlyNodeAggregate[]
     */
    protected $nodeAggregateIndex;

    /**
     * @param array|ContentSubgraph[] $subgraphs
     * @param array|ReadOnlyNode[] $nodes
     * @param array|ReadOnlyNodeAggregate[] $nodeAggregates
     * @param ConsoleOutput|null $output
     */
    public function __construct(array $subgraphs, array $nodes, array $nodeAggregates, ConsoleOutput $output = null)
    {
        $this->subgraphs = $subgraphs;
        $this->nodeAggregateIndex = $nodeAggregates;
        $numberOfNodes = 0;
        $numberOfEdges = 0;

        foreach ($nodes as $node) {
            $this->nodeIndex[$node->getNodeIdentifier()] = $node;
            $numberOfNodes++;
        }
        foreach ($subgraphs as $subgraph) {
            $numberOfEdges += count($subgraph);
        }
        if ($output) {
            $output->outputLine('Successfully initialized content graph containing ' . count($this->nodeIndex) . ' nodes and ' . $numberOfEdges . ' edges.');
        }
    }

    public function traverseNodeIndex(callable $callback, bool $includeShadowNodes = false)
    {
        foreach ($this->nodeIndex as $node) {
            if (!$includeShadowNodes && $node->getNodeData()->isInternal()) {
                continue;
            }
            $continue = $callback($node);
            if ($continue === false) {
                break;
            }
        }
    }

    public function traverseSubgraphs(callable $callback)
    {
        foreach ($this->subgraphs as $subgraph) {
            $subgraph->traverse($callback);
        }
    }

    /**
     * @return array|ReadOnlyNode[]
     */
    public function getNodes(): array
    {
        return $this->nodeIndex;
    }

    public function getNode(string $nodeIdentifier): ?ReadOnlyNode
    {
        return $this->nodeIndex[$nodeIdentifier] ?? null;
    }

    public function registerNode(ReadOnlyNode $node)
    {
        $this->nodeIndex[$node->getNodeIdentifier()] = $node;
    }

    public function unregisterNode(string $nodeIdentifier)
    {
        if (isset($this->nodeIndex[$nodeIdentifier])) {
            unset($this->nodeIndex[$nodeIdentifier]);
        }
    }

    public function traverseNodeAggregateIndex(callable $callback)
    {
        foreach ($this->nodeAggregateIndex as $nodeAggregate) {
            $callback($nodeAggregate);
        }
    }

    /**
     * @return array|ReadOnlyNodeAggregate[]
     */
    public function getNodeAggregates(): array
    {
        return $this->nodeAggregateIndex;
    }

    public function getNodeAggregate(string $nodeAggregateIdentifier): ?ReadOnlyNodeAggregate
    {
        return $this->nodeAggregateIndex[$nodeAggregateIdentifier] ?? null;
    }

    public function registerNodeAggregate(ReadOnlyNodeAggregate $nodeAggregate)
    {
        $this->nodeAggregateIndex[$nodeAggregate->getIdentifier()] = $nodeAggregate;
    }

    public function unregisterNodeAggregate(string $nodeAggregateIdentifier)
    {
        if (isset($this->nodeAggregateIndex[$nodeAggregateIdentifier])) {
            unset($this->nodeAggregateIndex[$nodeAggregateIdentifier]);
        }
    }

    /**
     * @return array|ContentSubgraph[]
     */
    public function getSubgraphs(): array
    {
        return $this->subgraphs;
    }

    public function getSubgraphByIdentifier(ContentSubgraphIdentifier $contentSubgraphIdentifier): ?ContentSubgraph
    {
        return $this->getSubgraphByHash((string) $contentSubgraphIdentifier);
    }

    public function getSubgraphByHash(string $hash): ?ContentSubgraph
    {
        return $this->subgraphs[$hash] ?? null;
    }
}
