<?php

declare(strict_types=1);

namespace Neos\ContentRepository\InMemoryGraph\NodeAggregate;

/*
 * This file is part of the Neos.ContentRepository.InMemoryGraph package.
 */
use Neos\ContentRepository\DimensionSpace\DimensionSpace\DimensionSpacePoint;
use Neos\ContentRepository\Domain as ContentRepository;
use Neos\ContentRepository\Domain\ContentStream\ContentStreamIdentifier;
use Neos\ContentRepository\Domain\NodeAggregate\NodeAggregateIdentifier;
use Neos\ContentRepository\Domain\NodeAggregate\NodeName;
use Neos\ContentRepository\Domain\NodeType\NodeTypeName;
use Neos\ContentRepository\Domain\Utility\NodePaths;
use Neos\ContentRepository\Exception\NodeException;
use Neos\ContentRepository\InMemoryGraph\ContentSubgraph\ContentSubgraphIdentifier;
use Neos\ContentRepository\InMemoryGraph\ContentSubgraph\HierarchyRelation;
use Neos\ContentRepository\InMemoryGraph\ContentSubgraph\ReferenceRelation;

/**
 * A node implementation
 */
final class Node implements ContentRepository\Projection\Content\NodeInterface
{
    /**
     * @var NodeAggregateIdentifier
     */
    protected $nodeAggregateIdentifier;

    /**
     * @var ContentRepository\Model\NodeData
     */
    protected $nodeData;
    /**
     * @var array|HierarchyRelation[][]
     */
    protected $outgoingHierarchyRelations = [];

    /**
     * @var array|HierarchyRelation[]
     */
    protected $incomingHierarchyRelations = [];

    /**
     * @var array|ReferenceRelation[]
     */
    protected $incomingReferenceRelations = [];

    /**
     * @var array|ReferenceRelation[]
     */
    protected $outgoingReferenceRelations = [];

    /**
     * @var DimensionSpacePoint
     */
    protected $originDimensionSpacePoint;

    public function __construct(
        ContentRepository\Model\NodeData $nodeData,
        DimensionSpacePoint $originDimensionSpacePoint
    ) {
        $this->nodeData = $nodeData;
        $this->nodeAggregateIdentifier = NodeAggregateIdentifier::fromString($nodeData->getIdentifier());
        $this->originDimensionSpacePoint = $originDimensionSpacePoint;
    }

    public function getNodeAggregateIdentifier(): NodeAggregateIdentifier
    {
        return $this->nodeAggregateIdentifier;
    }

    public function getOriginDimensionSpacePoint(): DimensionSpacePoint
    {
        return $this->originDimensionSpacePoint;
    }

    public function getProperties(): ContentRepository\Projection\Content\PropertyCollectionInterface
    {
        $properties = [];
        foreach ($this->nodeData->getPropertyNames() as $propertyName) {
            $properties[$propertyName] = $this->getProperty($propertyName);
        }

        return new ContentRepository\Model\ArrayPropertyCollection($properties);
    }

    /**
     * @param string $propertyName
     * @return mixed|null
     * @throws NodeException
     */
    public function getProperty($propertyName)
    {
        return $this->nodeData->getProperty($propertyName);
    }

    /**
     * @return array|HierarchyRelation[]
     */
    public function getOutgoingHierarchyRelations(): array
    {
        $outgoingHierarchyRelations = [];
        foreach ($this->outgoingHierarchyRelations as $subgraphHash => $hierarchyRelations) {
            foreach ($hierarchyRelations as $hierarchyRelation) {
                $outgoingHierarchyRelations[$hierarchyRelation->getNameForGraph()] = $hierarchyRelation;
            }
        }

        return $outgoingHierarchyRelations;
    }

    /**
     * @param ContentSubgraphIdentifier $subgraphIdentifier
     * @return array|HierarchyRelation[]
     */
    public function getOutgoingHierarchyRelationsInSubgraph(ContentSubgraphIdentifier $subgraphIdentifier): array
    {
        return $this->outgoingHierarchyRelations[(string) $subgraphIdentifier] ?? [];
    }

    /**
     * @param HierarchyRelation $hierarchyRelation
     * @return void
     * @todo handle hierarchyRelation identity: force name? how to update?
     */
    public function registerOutgoingHierarchyRelation(HierarchyRelation $hierarchyRelation): void
    {
        $this->outgoingHierarchyRelations[$hierarchyRelation->getSubgraphHash()][$hierarchyRelation->getLocalIdentifier()] = $hierarchyRelation;
    }

    /**
     * @param HierarchyRelation $hierarchyRelation
     * @return void
     * @todo handle hierarchyRelation identity: force name? how to update?
     */
    public function unregisterOutgoingHierarchyRelation(HierarchyRelation $hierarchyRelation): void
    {
        if (isset($this->outgoingHierarchyRelations[(string) $hierarchyRelation->getSubgraph()->getIdentifier()][$hierarchyRelation->getLocalIdentifier()])) {
            unset($this->outgoingHierarchyRelations[(string) $hierarchyRelation->getSubgraph()->getIdentifier()][$hierarchyRelation->getLocalIdentifier()]);
        }
    }

    /**
     * @return array|HierarchyRelation[]
     */
    public function getIncomingHierarchyRelations(): array
    {
        return $this->incomingHierarchyRelations;
    }

    public function getIncomingHierarchyRelationInSubgraph(ContentSubgraphIdentifier $contentSubgraphIdentifier): ?HierarchyRelation
    {
        return $this->incomingHierarchyRelations[(string) $contentSubgraphIdentifier] ?? null;
    }

    public function registerIncomingHierarchyRelation(HierarchyRelation $hierarchyRelation): void
    {
        $this->incomingHierarchyRelations[$hierarchyRelation->getSubgraphHash()] = $hierarchyRelation;
    }

    public function unregisterIncomingRelation(HierarchyRelation $hierarchyRelation): void
    {
        if (isset($this->incomingHierarchyRelations[(string) $hierarchyRelation->getSubgraph()->getIdentifier()])) {
            unset($this->incomingHierarchyRelations[(string) $hierarchyRelation->getSubgraph()->getIdentifier()]);
        }
    }

    /**
     * @return array|ReferenceRelation[]
     */
    public function getIncomingReferenceRelations(): array
    {
        return $this->incomingReferenceRelations;
    }

    public function registerIncomingReferenceRelation(ReferenceRelation $referenceRelation): void
    {
        $this->incomingReferenceRelations[] = $referenceRelation;
    }

    /**
     * @return array|ReferenceRelation[]
     */
    public function getOutgoingReferenceRelations(): array
    {
        return $this->outgoingReferenceRelations;
    }

    public function registerOutgoingReferenceRelation(ReferenceRelation $referenceRelation): void
    {
        $this->outgoingReferenceRelations[] = $referenceRelation;
    }

    public function getLabel(): string
    {
        return $this->getNodeType()->getNodeLabelGenerator()->getLabel($this) ?? '';
    }

    public function hasProperty($propertyName): bool
    {
        return $this->nodeData->hasProperty($propertyName);
    }

    public function getNodeType(): ContentRepository\Model\NodeType
    {
        return $this->nodeData->getNodeType();
    }

    public function isHidden(): bool
    {
        return $this->nodeData->isHidden();
    }

    public function getHiddenBeforeDateTime(): ?\DateTimeInterface
    {
        return $this->nodeData->getHiddenBeforeDateTime();
    }

    public function isHiddenInIndex(): bool
    {
        return $this->nodeData->isHiddenInIndex();
    }

    public function getAccessRoles(): array
    {
        return $this->nodeData->getAccessRoles();
    }

    public function getPath(): string
    {
        return $this->nodeData->getPath();
    }

    public function getContextPath(): string
    {
        if ($this->getWorkspace() !== null) {
            return NodePaths::generateContextPath(
                $this->getPath(),
                $this->getWorkspace()->getName(),
                $this->nodeData->getDimensions()
            );
        }

        return '';
    }

    public function getDepth(): int
    {
        return $this->nodeData->getDepth();
    }

    public function getWorkspace(): ?ContentRepository\Model\Workspace
    {
        return $this->nodeData->getWorkspace();
    }

    public function getIndex(): ?int
    {
        return $this->nodeData->getIndex();
    }

    public function getParentPath(): string
    {
        return $this->nodeData->getParentPath();
    }

    public function isRemoved(): bool
    {
        return $this->nodeData->isRemoved();
    }

    public function isVisible(): bool
    {
        return $this->nodeData->isVisible();
    }

    public function isAccessible(): bool
    {
        return $this->nodeData->isAccessible();
    }

    public function hasAccessRestrictions()
    {
        return $this->nodeData->hasAccessRestrictions();
    }

    public function getNodeData(): ContentRepository\Model\NodeData
    {
        return $this->nodeData;
    }

    public function getContext(): ContentRepository\Service\Context
    {
        return new ContentRepository\Service\Context(
            $this->nodeData->getWorkspace()->getName(),
            new \DateTimeImmutable(),
            $this->nodeData->getDimensions(),
            $this->originDimensionSpacePoint->getCoordinates(),
            true,
            true,
            true
        );
    }

    public function getDimensions(): array
    {
        return $this->nodeData->getDimensionValues();
    }

    public function getHiddenAfterDateTime(): ?\DateTimeInterface
    {
        return $this->nodeData->getHiddenAfterDateTime();
    }

    public function getCreationDateTime(): \DateTimeInterface
    {
        return $this->nodeData->getCreationDateTime();
    }

    public function getLastModificationDateTime(): \DateTimeInterface
    {
        return $this->nodeData->getLastModificationDateTime();
    }

    public function getLastPublicationDateTime(): \DateTimeInterface
    {
        return $this->nodeData->getLastPublicationDateTime();
    }

    public function getCacheEntryIdentifier(): string
    {
        return sha1(json_encode([
            'nodeAggregateIdentifier' => $this->nodeAggregateIdentifier,
            'contentStreamIdentifier' => $this->getContentStreamIdentifier(),
            'originDimensionSpacePoint' => $this->originDimensionSpacePoint
        ]));
    }

    public function isRoot(): bool
    {
        return $this->nodeData->getPath() === '/';
    }

    public function isTethered(): bool
    {
        $hierarchyRelation = $this->getIncomingHierarchyRelationInSubgraph(new ContentSubgraphIdentifier($this->getWorkspace()->getName(), $this->originDimensionSpacePoint));
        if ($hierarchyRelation) {
            return isset($hierarchyRelation->getParent()->getNodeType()->getAutoCreatedChildNodes()[$hierarchyRelation->getName()]);
        } else {
            return false;
        }
    }

    public function getContentStreamIdentifier(): ContentStreamIdentifier
    {
        return ContentStreamIdentifier::fromString($this->getWorkspace()->getName());
    }

    public function getNodeTypeName(): NodeTypeName
    {
        return NodeTypeName::fromString($this->getNodeType()->getName());
    }

    public function getNodeName(): ?NodeName
    {
        return NodeName::fromString($this->nodeData->getName());
    }
}
