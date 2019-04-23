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
     * @param NodeAssignmentRegistry $nodeAssignments
     * @param ConsoleOutput|null $output
     */
    public function __construct(array $subgraphs, array $nodes, array $nodeAggregates, NodeAssignmentRegistry $nodeAssignments, ConsoleOutput $output = null)
    {
        $this->subgraphs = $subgraphs;
        $this->nodeAggregateIndex = $nodeAggregates;
        $numberOfNodes = 0;
        $numberOfEdges = 0;

        if ($output) {
            $output->outputLine('Assigning edges to nodes...');
            $output->progressStart(count($nodes));
        }
        foreach ($nodes as $node) {
            if ($node->getPath() !== '/') {
                foreach ($nodeAssignments->getSubgraphIdentifiersByPathAndNodeIdentifier($node->getPath(),
                    $node->getNodeIdentifier()) as $subgraphIdentifier) {
                    $parentNode = $nodeAssignments->getNodeByPathAndSubgraphIdentifier($node->getParentPath(), $subgraphIdentifier);
                    if ($parentNode) {
                        $edge = new Edge($parentNode, $node, $subgraphs[(string)$subgraphIdentifier],
                            (string)$subgraphIdentifier, $node->getIndex(), $node->getName(), [
                                'accessRoles' => $node->getAccessRoles(),
                                'hidden' => $node->isHidden(),
                                'hiddenBeforeDateTime' => $node->getHiddenBeforeDateTime(),
                                'hiddenAfterDateTime' => $node->getHiddenAfterDateTime(),
                                'hiddenInIndex' => $node->isHiddenInIndex(),
                            ]);

                        $numberOfEdges++;
                        $node->registerIncomingEdge($edge);
                        $parentNode->registerOutgoingEdge($edge);
                    }
                }
            }

            $this->nodeIndex[$node->getNodeIdentifier()] = $node;

            if ($output) {
                $output->progressAdvance();
            }
            $numberOfNodes++;
        }

        if ($output) {
            $output->progressFinish();
            $output->outputLine('Successfully initialized content graph containing ' . count($this->nodeIndex) . ' nodes and ' . $numberOfEdges . ' edges.');
        }

        if ($output) {
            $output->outputLine('Assigning reference edges to nodes...');
            $output->progressStart(count($nodes));
        }

        $numberOfReferenceEdges = 0;
        foreach ($nodes as $node) {
            if ($node->getPath() !== '/') {
                $numberOfReferenceEdges += $this->createReferenceEdges($node);
            }

            if ($output) {
                $output->progressAdvance();
            }
        }

        if ($output) {
            $output->progressFinish();
            $output->outputLine('Successfully created ' . $numberOfReferenceEdges . ' reference edges.');
        }
    }

    public function createReferenceEdges(ReadOnlyNode $sourceNode)
    {
        $numberOfReferenceEdges = 0;
        foreach ($sourceNode->getNodeType()->getProperties() as $referenceName => $propertyConfiguration) {
            if (isset($propertyConfiguration['type'])) {
                if (in_array($propertyConfiguration['type'], ['references', 'reference'])) {
                    $propertyValue = $sourceNode->getNodeData()->getProperty($referenceName);

                    if (!$propertyValue) {
                        $propertyValue = [];
                    } else {
                        if (!is_array($propertyValue)) {
                            $propertyValue = [$propertyValue];
                        }
                    }

                    foreach ($propertyValue as $index => $targetNodeAggregateIdentifier) {
                        $targetNodeAggregate = $this->getNodeAggregate($targetNodeAggregateIdentifier);

                        if ($targetNodeAggregate) {
                            $this->createSingleReferenceEdge($sourceNode, $referenceName, $index, $targetNodeAggregate);
                            $numberOfReferenceEdges++;
                        }
                    }
                }
            }
        }

        return $numberOfReferenceEdges;
    }

    public function createSingleReferenceEdge(
        ReadOnlyNode $sourceNode,
        string $referenceName,
        int $index,
        ReadOnlyNodeAggregate $targetNodeAggregate
    ) {
        $referenceEdge = new ReferenceEdge($sourceNode, $targetNodeAggregate, $index, $referenceName, []);

        $sourceNode->registerOutgoingReferenceEdge($referenceEdge);

        foreach ($targetNodeAggregate->getNodes() as $targetNodeCandidate) {
            foreach ($targetNodeCandidate->getIncomingEdges() as $edge) {
                if ($sourceNode->getIncomingEdgeInSubgraph($edge->getSubgraph()->getIdentifier())) {
                    $targetNodeCandidate->registerIncomingReferenceEdge($referenceEdge);
                    break;
                }
            }
        }
    }

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

    public function getNode(string $nodeIdentifier): ?ReadOnlyNode
    {
        return $this->nodeIndex[$nodeIdentifier] ?? null;
    }

    public function registerNode(ReadOnlyNode $node): void
    {
        $this->nodeIndex[$node->getNodeIdentifier()] = $node;
    }

    public function unregisterNode(string $nodeIdentifier): void
    {
        if (isset($this->nodeIndex[$nodeIdentifier])) {
            unset($this->nodeIndex[$nodeIdentifier]);
        }
    }
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

    public function getNodeAggregate(string $nodeAggregateIdentifier): ?ReadOnlyNodeAggregate
    {
        return $this->nodeAggregateIndex[$nodeAggregateIdentifier] ?? null;
    }

    public function registerNodeAggregate(ReadOnlyNodeAggregate $nodeAggregate): void
    {
        $this->nodeAggregateIndex[$nodeAggregate->getIdentifier()] = $nodeAggregate;
    }

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

    public function getSubgraphByIdentifier(ContentSubgraphIdentifier $contentSubgraphIdentifier): ?ContentSubgraph
    {
        return $this->getSubgraphByHash((string)$contentSubgraphIdentifier);
    }

    public function getSubgraphByHash(string $hash): ?ContentSubgraph
    {
        return $this->subgraphs[$hash] ?? null;
    }
}
