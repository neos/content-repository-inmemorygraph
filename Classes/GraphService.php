<?php

declare(strict_types=1);

namespace Neos\ContentRepository\InMemoryGraph;

/*
 * This file is part of the Neos.ContentRepository.InMemoryGraph package.
 */
use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\DimensionSpace\Dimension\ContentDimensionIdentifier;
use Neos\ContentRepository\DimensionSpace\DimensionSpace;
use Neos\ContentRepository\Domain as ContentRepository;
use Neos\Flow\Cli\ConsoleOutput;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\Persistence\QueryResultInterface;

/**
 * The service for building up the content graph
 */
final class GraphService
{
    /**
     * @var DimensionSpace\InterDimensionalVariationGraph
     */
    protected $variationGraph;

    /**
     * @var DimensionSpace\ContentDimensionZookeeper
     */
    protected $contentDimensionZookeeper;

    /**
     * @var ContentRepository\Repository\NodeDataRepository
     */
    protected $nodeDataRepository;

    /**
     * @var ContentRepository\Repository\WorkspaceRepository
     */
    protected $workspaceRepository;

    /**
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @var array|ContentRepository\Model\Workspace[]
     */
    protected $indexedWorkspaces;

    /**
     * @var ContentDimensionIdentifier
     */
    protected $workspaceDimensionIdentifier;

    /**
     * @var array|string[]
     */
    protected $systemNodeIdentifiers;
    /**
     * @Flow\Inject
     * @var DimensionSpacePointFactory
     */
    protected $dimensionSpacePointFactory;

    public function __construct(
        DimensionSpace\InterDimensionalVariationGraph $variationGraph,
        DimensionSpace\ContentDimensionZookeeper $contentDimensionZookeeper,
        ContentRepository\Repository\WorkspaceRepository $workspaceRepository,
        ContentRepository\Repository\NodeDataRepository $nodeDataRepository,
        PersistenceManagerInterface $persistenceManager
    ) {
        $this->variationGraph = $variationGraph;
        $this->contentDimensionZookeeper = $contentDimensionZookeeper;
        $this->workspaceRepository = $workspaceRepository;
        $this->nodeDataRepository = $nodeDataRepository;
        $this->persistenceManager = $persistenceManager;
        $this->workspaceDimensionIdentifier = new ContentDimensionIdentifier('_workspace');
    }

    public function getContentGraph(ConsoleOutput $output = null): ContentGraph
    {
        $nodeDataRecords = $this->fetchNodeDataRecords();

        return $this->getContentGraphForNodeDataRecords($nodeDataRecords, $output);
    }

    public function getContentGraphForNodeDataRecords($nodeDataRecords, ConsoleOutput $output = null): ContentGraph
    {
        $start = microtime(true);

        foreach ($this->workspaceRepository->findAll() as $workspace) {
            /** @var ContentRepository\Model\Workspace $workspace */
            $this->indexedWorkspaces[$workspace->getName()] = $workspace;
        }
        if ($output) {
            $output->outputLine('Initializing subgraphs...');
        }

        $subgraphs = $this->getSubgraphs();
        if ($output) {
            $output->outputLine('Initialized ' . count($subgraphs) . ' subgraphs after ' . (microtime(true) - $start));
        }
        $this->variationGraph->getRootGeneralizations();

        $nodes = [];
        if ($output) {
            $output->outputLine('Initializing nodes...');
            $output->progressStart($nodeDataRecords->count());
        }
        foreach ($nodeDataRecords as $nodeDataRecord) {
            $nodeIdentifier = $this->persistenceManager->getIdentifierByObject($nodeDataRecord);
            $dimensionSpacePoint = $this->dimensionSpacePointFactory->createFromNodeData($nodeDataRecord);
            $nodes[$nodeIdentifier] = new ReadOnlyNode($nodeDataRecord, $nodeIdentifier, $dimensionSpacePoint);

            if ($output) {
                $output->progressAdvance();
            }
        }
        if ($output) {
            $output->progressFinish();
            $output->outputLine('Initialized nodes after ' . (microtime(true) - $start));
        }

        $aggregates = $this->groupNodesToAggregates($nodes);
        if ($output) {
            $output->outputLine('Initialized node aggregates after ' . (microtime(true) - $start));
        }
        $this->assignNodesToSubgraphs($aggregates, $subgraphs, $output);
        if ($output) {
            $output->outputLine('Initialized node assignments after ' . (microtime(true) - $start));
        }
        $result = new ContentGraph($subgraphs, $nodes, $aggregates, $output);
        if ($output) {
            $output->outputLine('Initialized graph after ' . (microtime(true) - $start));
            $output->outputLine('Memory used: %4.2fMB', [memory_get_peak_usage(true) / 1048576]);
        }
        return $result;
    }

    /**
     * @return array|ContentSubgraph[]
     */
    protected function getSubgraphs(): array
    {
        $subgraphs = [];
        $allowedDimensionSubspace = $this->contentDimensionZookeeper->getAllowedDimensionSubspace();

        foreach ($allowedDimensionSubspace as $dimensionSpacePoint) {
            $subgraph = new ContentSubgraph($this->getWorkspaceForDimensionSpacePoint($dimensionSpacePoint), $dimensionSpacePoint);
            $subgraphs[(string)$subgraph] = $subgraph;
        }

        return $subgraphs;
    }

    /**
     * @return QueryResultInterface|ContentRepository\Model\NodeData[]
     */
    protected function fetchNodeDataRecords(): QueryResultInterface
    {
        $query = $this->nodeDataRepository->createQuery();
        $query->setOrderings([
            'path' => 'ASC',
            'workspace' => 'ASC'
        ]);

        return $query->execute();
    }

    /**
     * @param array|ReadOnlyNode[] $nodes
     * @param ConsoleOutput $output = null
     * @return array|ReadOnlyNodeAggregate[]
     */
    protected function groupNodesToAggregates(array $nodes, ConsoleOutput $output = null): array
    {
        $aggregates = [];
        $nodesByAggregateIdentifier = [];

        if ($output) {
            $output->outputLine('Grouping nodes to aggregates');
            $output->progressStart(count($aggregates));
        }
        foreach ($nodes as $node) {
            $identifier = $node->getPath() === '/' ? ReadOnlyNodeAggregate::ROOT_IDENTIFIER : $node->getIdentifier();
            $nodesByAggregateIdentifier[$identifier][] = $node;
            if ($node->getPath() !== '/') {
                foreach ($node->getDimensionSpacePoint()->getCoordinates() as $dimensionValue) {
                    if ($dimensionValue === '_') {
                        $this->systemNodeIdentifiers[$node->getIdentifier()] = $node->getIdentifier();
                    }
                }
            }
            if ($output) {
                $output->progressAdvance();
            }
        }
        foreach ($nodesByAggregateIdentifier as $nodeAggregateIdentifier => $nodes) {
            $aggregates[$nodeAggregateIdentifier] = new ReadOnlyNodeAggregate($nodeAggregateIdentifier, $nodes);
        }
        if ($output) {
            $output->progressFinish();
            $output->outputLine();
        }

        return $aggregates;
    }

    /**
     * @param array|ReadOnlyNodeAggregate[] $aggregates
     * @param array|ContentSubgraph[] $subgraphs
     * @param ConsoleOutput|null $output
     * @return NodeAssignmentRegistry
     */
    protected function determineNodeAssignments(array $aggregates, array $subgraphs, ConsoleOutput $output = null): NodeAssignmentRegistry
    {
        $nodeAssignmentRegistry = new NodeAssignmentRegistry();

        if ($output) {
            $output->progressStart(count($aggregates));
        }
        foreach ($aggregates as $aggregateIdentifier => $aggregate) {
            foreach ($subgraphs as $subgraph) {
                $node = $this->findBestSuitedNodeForSubgraph(
                    $subgraph->getWorkspace(),
                    $subgraph->getDimensionSpacePoint(),
                    $aggregate
                );
                if ($node) {
                    $path = $node->getPath();
                    $nodeAssignmentRegistry->registerNodeByPathAndSubgraphIdentifier(
                        $path,
                        $subgraph->getIdentifier(),
                        $node
                    );
                    $nodeAssignmentRegistry->registerSubgraphIdentifierByPathAndNodeIdentifier(
                        $path,
                        $node->getNodeIdentifier(),
                        $subgraph->getIdentifier()
                    );
                }
            }
            if ($output) {
                $output->progressAdvance();
            }
        }
        if ($output) {
            $output->progressFinish();
        }

        return $nodeAssignmentRegistry;
    }

    protected function findBestSuitedNodeForSubgraph(
        ContentRepository\Model\Workspace $workspace,
        DimensionSpace\DimensionSpacePoint $dimensionSpacePoint,
        ReadOnlyNodeAggregate $nodeAggregate
    ): ?ReadOnlyNode {
        if ($nodeAggregate->isRoot()) {
            $nodes = $nodeAggregate->getNodesByWorkspace($workspace);
            return reset($nodes);
        } elseif (isset($this->systemNodeIdentifiers[$nodeAggregate->getIdentifier()])) {
            $nodes = $nodeAggregate->getNodes();
            return reset($nodes);
        }

        $node = $nodeAggregate->getNodeByDimensionSpacePoint($dimensionSpacePoint);
        if ($node) {
            return $node;
        } else {
            foreach ($this->variationGraph->getWeightedGeneralizations($dimensionSpacePoint) as $generalization) {
                $node = $nodeAggregate->getNodeByDimensionSpacePoint($generalization);
                if ($node) {
                    return $node;
                }
            }
        }

        return null;
    }

    protected function getWorkspaceForDimensionSpacePoint(DimensionSpace\DimensionSpacePoint $dimensionSpacePoint): ?ContentRepository\Model\Workspace
    {
        return $this->indexedWorkspaces[$dimensionSpacePoint->getCoordinate($this->workspaceDimensionIdentifier)] ?? null;
    }
}
