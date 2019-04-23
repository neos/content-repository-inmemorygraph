<?php

declare(strict_types=1);

namespace Neos\ContentRepository\InMemoryGraph;

/*
 * This file is part of the Neos.ContentRepository.InMemoryGraph package.
 */
use Neos\ContentRepository\DimensionSpace\DimensionSpace\DimensionSpacePoint;
use Neos\ContentRepository\Domain as ContentRepository;
use Neos\ContentRepository\Domain\Utility\NodePaths;
use Neos\ContentRepository\Exception\NodeException;
use Neos\ContentRepository\Exception\NodeTypeNotFoundException;

/**
 * A read only node implementation
 */
final class ReadOnlyNode implements ContentRepository\Model\NodeInterface
{
    /**
     * @var string
     */
    protected $nodeIdentifier;

    /**
     * @var ContentRepository\Model\NodeData
     */
    protected $nodeData;
    /**
     * @var array|Edge[]
     */
    protected $outgoingEdges = [];

    /**
     * @var array|Edge[]
     */
    protected $incomingEdges = [];

    /**
     * @var array|ReferenceEdge[]
     */
    protected $incomingReferenceEdges = [];

    /**
     * @var array|ReferenceEdge[]
     */
    protected $outgoingReferenceEdges = [];

    /**
     * @var DimensionSpacePoint
     */
    protected $dimensionSpacePoint;

    /**
     * @var ContentSubgraphIdentifier
     */
    protected $contentSubgraphIdentifier;

    public function __construct(
        ContentRepository\Model\NodeData $nodeData,
        string $nodeIdentifier,
        DimensionSpacePoint $dimensionSpacePoint
    ) {
        $this->nodeData = $nodeData;
        $this->nodeIdentifier = $nodeIdentifier;
        $this->dimensionSpacePoint = $dimensionSpacePoint;
        $this->contentSubgraphIdentifier = new ContentSubgraphIdentifier($nodeData->getWorkspace() ? $nodeData->getWorkspace()->getName() : '', $this->dimensionSpacePoint);
    }

    public function getIdentifier(): string
    {
        return $this->nodeData->getIdentifier();
    }

    public function getNodeIdentifier(): string
    {
        return $this->nodeIdentifier;
    }

    public function getDimensionSpacePoint(): DimensionSpacePoint
    {
        return $this->dimensionSpacePoint;
    }

    public function getContentSubgraphIdentifier(): ContentSubgraphIdentifier
    {
        return $this->contentSubgraphIdentifier;
    }

    /**
     * @return array
     * @throws NodeException
     */
    public function getProperties(): array
    {
        $properties = [];
        foreach ($this->getPropertyNames() as $propertyName) {
            $properties[$propertyName] = $this->getProperty($propertyName);
        }

        return $properties;
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
     * @return array|Edge[]
     */
    public function getOutgoingEdges()
    {
        $outgoingEdges = [];
        foreach ($this->outgoingEdges as $subgraphHash => $edges) {
            foreach ($edges as $edge) {
                /** @var Edge $edge */
                $outgoingEdges[$edge->getNameForGraph()] = $edge;
            }
        }

        return $outgoingEdges;
    }

    /**
     * @param ContentSubgraphIdentifier $subgraphIdentifier
     * @return array|Edge[]
     */
    public function getOutgoingEdgesInSubgraph(ContentSubgraphIdentifier $subgraphIdentifier)
    {
        return $this->outgoingEdges[(string) $subgraphIdentifier] ?? [];
    }

    /**
     * @param Edge $edge
     * @return void
     * @todo handle edge identity: force name? how to update?
     */
    public function registerOutgoingEdge(Edge $edge)
    {
        $this->outgoingEdges[$edge->getSubgraphHash()][$edge->getLocalIdentifier()] = $edge;
    }

    /**
     * @param Edge $edge
     * @return void
     * @todo handle edge identity: force name? how to update?
     */
    public function unregisterOutgoingEdge(Edge $edge)
    {
        if (isset($this->outgoingEdges[(string) $edge->getSubgraph()->getIdentifier()][$edge->getLocalIdentifier()])) {
            unset($this->outgoingEdges[(string) $edge->getSubgraph()->getIdentifier()][$edge->getLocalIdentifier()]);
        }
    }

    /**
     * @return array|Edge[]
     */
    public function getIncomingEdges(): array
    {
        return $this->incomingEdges;
    }

    public function getIncomingEdgeInSubgraph(ContentSubgraphIdentifier $contentSubgraphIdentifier): ?Edge
    {
        return $this->incomingEdges[(string) $contentSubgraphIdentifier] ?? null;
    }

    public function registerIncomingEdge(Edge $edge)
    {
        $this->incomingEdges[$edge->getSubgraphHash()] = $edge;
    }

    public function unregisterIncomingEdge(Edge $edge)
    {
        if (isset($this->incomingEdges[(string) $edge->getSubgraph()->getIdentifier()])) {
            unset($this->incomingEdges[(string) $edge->getSubgraph()->getIdentifier()]);
        }
    }

    /**
     * @return array|ReferenceEdge[]
     */
    public function getIncomingReferenceEdges(): array
    {
        return $this->incomingReferenceEdges;
    }

    /**
     * @param ReferenceEdge $referenceEdge
     * @return void
     */
    public function registerIncomingReferenceEdge(ReferenceEdge $referenceEdge)
    {
        $this->incomingReferenceEdges[] = $referenceEdge;
    }

    /**
     * @return array|ReferenceEdge[]
     */
    public function getOutgoingReferenceEdges(): array
    {
        return $this->outgoingReferenceEdges;
    }

    /**
     * @param ReferenceEdge $referenceEdge
     * @return
     */
    public function registerOutgoingReferenceEdge(ReferenceEdge $referenceEdge)
    {
        $this->outgoingReferenceEdges[] = $referenceEdge;
    }

    /**
     * @param string $newName
     * @throws NodeOperationIsNotSupported
     */
    public function setName($newName): void
    {
        throw new NodeOperationIsNotSupported();
    }

    public function getName(): string
    {
        return $this->nodeData->getName();
    }

    public function getLabel(): string
    {
        return $this->getNodeType()->getNodeLabelGenerator()->getLabel($this) ?? '';
    }

    /**
     * @param string $propertyName
     * @param mixed $value
     * @throws NodeOperationIsNotSupported
     */
    public function setProperty($propertyName, $value): void
    {
        throw new NodeOperationIsNotSupported();
    }

    public function hasProperty($propertyName): bool
    {
        return $this->nodeData->hasProperty($propertyName);
    }

    /**
     * @param string $propertyName
     * @throws NodeOperationIsNotSupported
     */
    public function removeProperty($propertyName): void
    {
        throw new NodeOperationIsNotSupported();
    }

    /**
     * @return array|string[]
     */
    public function getPropertyNames(): array
    {
        return $this->nodeData->getPropertyNames();
    }

    /**
     * @param \object $contentObject
     * @throws NodeOperationIsNotSupported
     */
    public function setContentObject($contentObject): void
    {
        throw new NodeOperationIsNotSupported();
    }

    /**
     * @return \object|void
     * @throws NodeOperationIsNotSupported
     */
    public function getContentObject(): void
    {
        throw new NodeOperationIsNotSupported();
    }

    /**
     * @throws NodeOperationIsNotSupported
     */
    public function unsetContentObject(): void
    {
        throw new NodeOperationIsNotSupported();
    }

    /**
     * @param ContentRepository\Model\NodeType $nodeType
     * @throws NodeOperationIsNotSupported
     */
    public function setNodeType(ContentRepository\Model\NodeType $nodeType): void
    {
        throw new NodeOperationIsNotSupported();
    }

    /**
     * @return ContentRepository\Model\NodeType
     * @throws NodeTypeNotFoundException
     */
    public function getNodeType(): ContentRepository\Model\NodeType
    {
        return $this->nodeData->getNodeType();
    }

    /**
     * @param bool $hidden
     * @throws NodeOperationIsNotSupported
     */
    public function setHidden($hidden): void
    {
        throw new NodeOperationIsNotSupported();
    }

    public function isHidden(): bool
    {
        return $this->nodeData->isHidden();
    }

    /**
     * @param \DateTimeInterface|null $dateTime
     * @throws NodeOperationIsNotSupported
     */
    public function setHiddenBeforeDateTime(\DateTimeInterface $dateTime = null): void
    {
        throw new NodeOperationIsNotSupported();
    }

    public function getHiddenBeforeDateTime(): ?\DateTimeInterface
    {
        return $this->nodeData->getHiddenBeforeDateTime();
    }

    /**
     * @param \DateTimeInterface|null $dateTime
     * @throws NodeOperationIsNotSupported
     */
    public function setHiddenAfterDateTime(\DateTimeInterface $dateTime = null): void
    {
        throw new NodeOperationIsNotSupported();
    }

    /**
     * @param bool $hidden
     * @throws NodeOperationIsNotSupported
     */
    public function setHiddenInIndex($hidden): void
    {
        throw new NodeOperationIsNotSupported();
    }

    public function isHiddenInIndex(): bool
    {
        return $this->nodeData->isHiddenInIndex();
    }

    /**
     * @param array $accessRoles
     * @throws NodeOperationIsNotSupported
     */
    public function setAccessRoles(array $accessRoles): void
    {
        throw new NodeOperationIsNotSupported();
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

    /**
     * @return int
     * @throws NodeOperationIsNotSupported
     */
    public function getDepth(): int
    {
        throw new NodeOperationIsNotSupported();
    }

    /**
     * @param ContentRepository\Model\Workspace $workspace
     * @throws NodeOperationIsNotSupported
     */
    public function setWorkspace(ContentRepository\Model\Workspace $workspace): void
    {
        throw new NodeOperationIsNotSupported();
    }

    public function getWorkspace(): ?ContentRepository\Model\Workspace
    {
        return $this->nodeData->getWorkspace();
    }

    /**
     * @param int $index
     * @throws NodeOperationIsNotSupported
     */
    public function setIndex($index): void
    {
        throw new NodeOperationIsNotSupported();
    }

    public function getIndex(): ?int
    {
        return $this->nodeData->getIndex();
    }

    public function getParent(): ?ContentRepository\Projection\Content\NodeInterface
    {
        if ($edge = $this->getIncomingEdgeInSubgraph($this->contentSubgraphIdentifier)) {
            return $edge->getParent();
        }

        return null;
    }

    public function getParentPath(): string
    {
        return $this->nodeData->getParentPath();
    }

    /**
     * @param string $name
     * @param ContentRepository\Model\NodeType|null $nodeType
     * @param null $identifier
     * @return \Neos\ContentRepository\Domain\Model\Node|void
     * @throws NodeOperationIsNotSupported
     */
    public function createNode($name, ContentRepository\Model\NodeType $nodeType = null, $identifier = null): void
    {
        throw new NodeOperationIsNotSupported();
    }

    /**
     * @param string $name
     * @param ContentRepository\Model\NodeType|null $nodeType
     * @param null $identifier
     * @return \Neos\ContentRepository\Domain\Model\Node|void
     * @throws NodeOperationIsNotSupported
     */
    public function createSingleNode($name, ContentRepository\Model\NodeType $nodeType = null, $identifier = null): void
    {
        throw new NodeOperationIsNotSupported();
    }

    /**
     * @param ContentRepository\Model\NodeTemplate $nodeTemplate
     * @param string|null $nodeName
     * @return ContentRepository\Model\NodeInterface|void
     * @throws NodeOperationIsNotSupported
     */
    public function createNodeFromTemplate(ContentRepository\Model\NodeTemplate $nodeTemplate, $nodeName = null): void
    {
        throw new NodeOperationIsNotSupported();
    }

    public function getNode($path): ?ContentRepository\Projection\Content\NodeInterface
    {
        $outgoingEdge = $this->getOutgoingEdgesInSubgraph($this->contentSubgraphIdentifier)[$path] ?? null;

        if ($outgoingEdge) {
            return $outgoingEdge->getChild();
        }

        return null;
    }

    /**
     * @return ContentRepository\Projection\Content\NodeInterface
     * @throws NodeOperationIsNotSupported
     */
    public function getPrimaryChildNode(): ContentRepository\Projection\Content\NodeInterface
    {
        throw new NodeOperationIsNotSupported();
    }

    /**
     * @param string $nodeTypeFilter
     * @param string $limit
     * @param string $offset
     * @return array|ReadOnlyNode[]
     */
    public function getChildNodes($nodeTypeFilter = null, $limit = null, $offset = null): array
    {
        $childNodes = [];
        foreach ($this->getOutgoingEdgesInSubgraph($this->contentSubgraphIdentifier) as $outgoingEdge) {
            $childNodes[$outgoingEdge->getChild()->getNodeIdentifier()] = $outgoingEdge->getChild();
        }

        return $childNodes;
    }

    /**
     * @param null $nodeTypeFilter
     * @return bool|void
     * @throws NodeOperationIsNotSupported
     */
    public function hasChildNodes($nodeTypeFilter = null): bool
    {
        throw new NodeOperationIsNotSupported();
    }

    /**
     * @throws NodeOperationIsNotSupported
     */
    public function remove(): void
    {
        throw new NodeOperationIsNotSupported();
    }

    /**
     * @param bool $removed
     * @throws NodeOperationIsNotSupported
     */
    public function setRemoved($removed)
    {
        throw new NodeOperationIsNotSupported();
    }

    public function isRemoved(): bool
    {
        return $this->nodeData->isRemoved();
    }

    /**
     * @return bool|void
     * @throws NodeOperationIsNotSupported
     */
    public function isVisible(): bool
    {
        throw new NodeOperationIsNotSupported();
    }

    /**
     * @return bool|void
     * @throws NodeOperationIsNotSupported
     */
    public function isAccessible(): bool
    {
        throw new NodeOperationIsNotSupported();
    }

    /**
     * @return bool|void
     * @throws NodeOperationIsNotSupported
     */
    public function hasAccessRestrictions()
    {
        throw new NodeOperationIsNotSupported();
    }

    /**
     * @param ContentRepository\Model\NodeType $nodeType
     * @return bool|void
     * @throws NodeOperationIsNotSupported
     */
    public function isNodeTypeAllowedAsChildNode(ContentRepository\Model\NodeType $nodeType)
    {
        throw new NodeOperationIsNotSupported();
    }

    /**
     * @param ContentRepository\Model\NodeInterface $referenceNode
     * @throws NodeOperationIsNotSupported
     */
    public function moveBefore(ContentRepository\Model\NodeInterface $referenceNode)
    {
        throw new NodeOperationIsNotSupported();
    }

    /**
     * @param ContentRepository\Model\NodeInterface $referenceNode
     * @throws NodeOperationIsNotSupported
     */
    public function moveAfter(ContentRepository\Model\NodeInterface $referenceNode)
    {
        throw new NodeOperationIsNotSupported();
    }

    /**
     * @param ContentRepository\Model\NodeInterface $referenceNode
     * @throws NodeOperationIsNotSupported
     */
    public function moveInto(ContentRepository\Model\NodeInterface $referenceNode)
    {
        throw new NodeOperationIsNotSupported();
    }

    /**
     * @param ContentRepository\Model\NodeInterface $referenceNode
     * @param string $nodeName
     * @return ContentRepository\Model\NodeInterface|void
     * @throws NodeOperationIsNotSupported
     */
    public function copyBefore(ContentRepository\Model\NodeInterface $referenceNode, $nodeName)
    {
        throw new NodeOperationIsNotSupported();
    }

    /**
     * @param ContentRepository\Model\NodeInterface $referenceNode
     * @param string $nodeName
     * @return ContentRepository\Model\NodeInterface|void
     * @throws NodeOperationIsNotSupported
     */
    public function copyAfter(ContentRepository\Model\NodeInterface $referenceNode, $nodeName)
    {
        throw new NodeOperationIsNotSupported();
    }

    /**
     * @param ContentRepository\Model\NodeInterface $referenceNode
     * @param string $nodeName
     * @return ContentRepository\Model\NodeInterface|void
     * @throws NodeOperationIsNotSupported
     */
    public function copyInto(ContentRepository\Model\NodeInterface $referenceNode, $nodeName)
    {
        throw new NodeOperationIsNotSupported();
    }

    public function getNodeData(): ContentRepository\Model\NodeData
    {
        return $this->nodeData;
    }

    /**
     * @return ContentRepository\Service\Context
     * @throws \Exception
     */
    public function getContext(): ContentRepository\Service\Context
    {
        return new ContentRepository\Service\Context(
            $this->nodeData->getWorkspace()->getName(),
            new \DateTimeImmutable(),
            $this->nodeData->getDimensions(),
            $this->dimensionSpacePoint->getCoordinates(),
            true,
            true,
            true
        );
    }

    public function getDimensions(): array
    {
        return $this->nodeData->getDimensionValues();
    }

    /**
     * @param ContentRepository\Service\Context $context
     * @return ContentRepository\Model\NodeInterface|void
     * @throws NodeOperationIsNotSupported
     */
    public function createVariantForContext($context)
    {
        throw new NodeOperationIsNotSupported();
    }

    public function isAutoCreated(): bool
    {
        $edge = $this->getIncomingEdgeInSubgraph($this->contentSubgraphIdentifier);
        if ($edge) {
            return isset($edge->getParent()->getNodeType()->getAutoCreatedChildNodes()[$edge->getName()]);
        } else {
            return false;
        }
    }

    /**
     * @return array
     * @throws NodeOperationIsNotSupported
     */
    public function getOtherNodeVariants(): array
    {
        throw new NodeOperationIsNotSupported();
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
}
