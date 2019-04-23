<?php

namespace Neos\ContentRepository\InMemoryGraph\ContentSubgraph;

/*
 * This file is part of the Neos.ContentRepository.InMemoryGraph package.
 */

use Neos\ContentRepository\InMemoryGraph\ContentSubgraph\ContentSubgraphIdentifier;
use Neos\ContentRepository\InMemoryGraph\NodeAggregate\Node;

/**
 * The node assignment registry
 *
 * A temporary storage for node relation data to create edges from
 */
final class NodeAssignmentRegistry
{
    /**
     * @var array|Node[][]
     */
    protected $nodesByPathAndSubgraph;

    /**
     * @var array|ContentSubgraphIdentifier[][][]
     */
    protected $subgraphsByPathAndNodeIdentifier;

    public function registerNodeByPathAndSubgraphIdentifier(string $path, ContentSubgraphIdentifier $subgraphIdentifier, Node $node)
    {
        $this->nodesByPathAndSubgraph[$path][(string) $subgraphIdentifier] = $node;
    }

    public function getNodeByPathAndSubgraphIdentifier(string $path, ContentSubgraphIdentifier $subgraphIdentifier): ?Node
    {
        return $this->nodesByPathAndSubgraph[$path][(string) $subgraphIdentifier] ?? null;
    }

    public function registerSubgraphIdentifierByPathAndNodeIdentifier(string $path, string $nodeIdentifier, ContentSubgraphIdentifier $contentSubgraphIdentifier)
    {
        $this->subgraphsByPathAndNodeIdentifier[$path][$nodeIdentifier][(string) $contentSubgraphIdentifier] = $contentSubgraphIdentifier;
    }

    /**
     * @param string $path
     * @param string $nodeIdentifier
     * @return array|ContentSubgraphIdentifier[]
     */
    public function getSubgraphIdentifiersByPathAndNodeIdentifier(string $path, string $nodeIdentifier): array
    {
        return $this->subgraphsByPathAndNodeIdentifier[$path][$nodeIdentifier] ?? [];
    }
}
