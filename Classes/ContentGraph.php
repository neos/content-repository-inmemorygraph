<?php

declare(strict_types=1);

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

    /**
     * @param callable $callback
     * @param bool $includeShadowNodes
     * @return void
     */
    public function traverseNodeIndex(callable $callback, bool $includeShadowNodes = false): void
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

    /**
     * @param callable $callback
     * @return void
     */
    public function traverseSubgraphs(callable $callback): void
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

    /**
     * @param string $nodeIdentifier
     * @return ReadOnlyNode|null
     */
    public function getNode(string $nodeIdentifier): ?ReadOnlyNode
    {
        return $this->nodeIndex[$nodeIdentifier] ?? null;
    }

    /**
     * @param ReadOnlyNode $node
     * @return void
     */
    public function registerNode(ReadOnlyNode $node): void
    {
        $this->nodeIndex[$node->getNodeIdentifier()] = $node;
    }

    /**
     * @param string $nodeIdentifier
     * @return void
     */
    public function unregisterNode(string $nodeIdentifier): void
    {
        if (isset($this->nodeIndex[$nodeIdentifier])) {
            unset($this->nodeIndex[$nodeIdentifier]);
        }
    }

    /**
     * @param callable $callback
     * @return void
     */
    public function traverseNodeAggregateIndex(callable $callback): void
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

    /**
     * @param string $nodeAggregateIdentifier
     * @return ReadOnlyNodeAggregate|null
     */
    public function getNodeAggregate(string $nodeAggregateIdentifier): ?ReadOnlyNodeAggregate
    {
        return $this->nodeAggregateIndex[$nodeAggregateIdentifier] ?? null;
    }

    /**
     * @param ReadOnlyNodeAggregate $nodeAggregate
     * @return void
     */
    public function registerNodeAggregate(ReadOnlyNodeAggregate $nodeAggregate): void
    {
        $this->nodeAggregateIndex[$nodeAggregate->getIdentifier()] = $nodeAggregate;
    }

    /**
     * @param string $nodeAggregateIdentifier
     * @return void
     */
    public function unregisterNodeAggregate(string $nodeAggregateIdentifier): void
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

    /**
     * @param ContentSubgraphIdentifier $contentSubgraphIdentifier
     * @return ContentSubgraph|null
     */
    public function getSubgraphByIdentifier(ContentSubgraphIdentifier $contentSubgraphIdentifier): ?ContentSubgraph
    {
        return $this->getSubgraphByHash((string)$contentSubgraphIdentifier);
    }

    /**
     * @param string $hash
     * @return ContentSubgraph|null
     */
    public function getSubgraphByHash(string $hash): ?ContentSubgraph
    {
        return $this->subgraphs[$hash] ?? null;
    }
}
