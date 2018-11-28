<?php

declare(strict_types=1);

namespace Neos\ContentRepository\InMemoryGraph;

/*
 * This file is part of the Neos.ContentRepository.InMemoryGraph package.
 */

use Neos\ContentRepository\DimensionSpace\DimensionSpace;
use Neos\ContentRepository\Domain as ContentRepository;
use Neos\ContentRepository\Domain\Utility\NodePaths;

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
     * @var DimensionSpace\DimensionSpacePoint
     */
    protected $dimensionSpacePoint;

    /**
     * @var ContentSubgraphIdentifier
     */
    protected $contentSubgraphIdentifier;

    /**
     * @param ContentRepository\Model\NodeData $nodeData
     * @param string $nodeIdentifier
     * @param DimensionSpace\DimensionSpacePoint $dimensionSpacePoint
     */
    public function __construct(ContentRepository\Model\NodeData $nodeData, string $nodeIdentifier, DimensionSpace\DimensionSpacePoint $dimensionSpacePoint)
    {
        $this->nodeData = $nodeData;
        $this->nodeIdentifier = $nodeIdentifier;
        $this->dimensionSpacePoint = $dimensionSpacePoint;
        $this->contentSubgraphIdentifier = new ContentSubgraphIdentifier($nodeData->getWorkspace() ? $nodeData->getWorkspace()->getName() : '', $this->dimensionSpacePoint);
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->nodeData->getIdentifier();
    }

    /**
     * @return string
     */
    public function getNodeIdentifier(): string
    {
        return $this->nodeIdentifier;
    }

    /**
     * @return DimensionSpace\DimensionSpacePoint
     */
    public function getDimensionSpacePoint(): DimensionSpace\DimensionSpacePoint
    {
        return $this->dimensionSpacePoint;
    }

    /**
     * @return ContentSubgraphIdentifier
     */
    public function getContentSubgraphIdentifier(): ContentSubgraphIdentifier
    {
        return $this->contentSubgraphIdentifier;
    }

    /**
     * @return array
     * @throws \Neos\ContentRepository\Exception\NodeException
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
     * @throws \Neos\ContentRepository\Exception\NodeException
     */
    public function getProperty($propertyName)
    {
        return $this->nodeData->getProperty($propertyName);
    }

    /**
     * @param string $newName
     * @return void
     * @throws NodeOperationIsNotSupportedException
     */
    public function setName($newName): void
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->nodeData->getName();
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->getNodeType()->getNodeLabelGenerator()->getLabel($this) ?? '';
    }

    /**
     * @param string $propertyName
     * @param mixed $value
     * @return void
     * @throws NodeOperationIsNotSupportedException
     */
    public function setProperty($propertyName, $value): void
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @param string $propertyName
     * @return bool
     */
    public function hasProperty($propertyName): bool
    {
        return $this->nodeData->hasProperty($propertyName);
    }

    /**
     * @param string $propertyName
     * @return void
     * @throws NodeOperationIsNotSupportedException
     */
    public function removeProperty($propertyName): void
    {
        throw new NodeOperationIsNotSupportedException();
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
     * @return void
     * @throws NodeOperationIsNotSupportedException
     */
    public function setContentObject($contentObject): void
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @return \object|void
     * @throws NodeOperationIsNotSupportedException
     */
    public function getContentObject()
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @return void
     * @throws NodeOperationIsNotSupportedException
     */
    public function unsetContentObject(): void
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @param ContentRepository\Model\NodeType $nodeType
     * @return void
     * @throws NodeOperationIsNotSupportedException
     */
    public function setNodeType(ContentRepository\Model\NodeType $nodeType): void
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @return ContentRepository\Model\NodeType
     */
    public function getNodeType(): ContentRepository\Model\NodeType
    {
        return $this->nodeData->getNodeType();
    }

    /**
     * @param bool $hidden
     * @return void
     * @throws NodeOperationIsNotSupportedException
     */
    public function setHidden($hidden): void
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @return bool
     */
    public function isHidden(): bool
    {
        return $this->nodeData->isHidden();
    }

    /**
     * @param \DateTime|null $dateTime
     * @return void
     * @throws NodeOperationIsNotSupportedException
     */
    public function setHiddenBeforeDateTime(\DateTime $dateTime = null): void
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @return \DateTimeInterface
     */
    public function getHiddenBeforeDateTime(): \DateTimeInterface
    {
        return $this->nodeData->getHiddenBeforeDateTime();
    }

    /**
     * @param \DateTime|null $dateTime
     * @return void
     * @throws NodeOperationIsNotSupportedException
     */
    public function setHiddenAfterDateTime(\DateTime $dateTime = null): void
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @param bool $hidden
     * @return void
     * @throws NodeOperationIsNotSupportedException
     */
    public function setHiddenInIndex($hidden): void
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @return bool
     */
    public function isHiddenInIndex(): bool
    {
        return $this->nodeData->isHiddenInIndex();
    }

    /**
     * @param array $accessRoles
     * @return void
     * @throws NodeOperationIsNotSupportedException
     */
    public function setAccessRoles(array $accessRoles): void
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @return array
     */
    public function getAccessRoles(): array
    {
        return $this->nodeData->getAccessRoles();
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->nodeData->getPath();
    }

    /**
     * @return string
     */
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
     * @throws NodeOperationIsNotSupportedException
     */
    public function getDepth(): int
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @param ContentRepository\Model\Workspace $workspace
     * @return void
     * @throws NodeOperationIsNotSupportedException
     */
    public function setWorkspace(ContentRepository\Model\Workspace $workspace): void
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @return ContentRepository\Model\Workspace|null
     */
    public function getWorkspace(): ?ContentRepository\Model\Workspace
    {
        return $this->nodeData->getWorkspace();
    }

    /**
     * @param int $index
     * @return void
     * @throws NodeOperationIsNotSupportedException
     */
    public function setIndex($index): void
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @return int|null
     */
    public function getIndex(): ?int
    {
        return $this->nodeData->getIndex();
    }

    /**
     * @return ContentRepository\Model\NodeInterface|null
     * @throws NodeOperationIsNotSupportedException
     */
    public function getParent(): ?ContentRepository\Model\NodeInterface
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @return string
     */
    public function getParentPath(): string
    {
        return $this->nodeData->getParentPath();
    }

    /**
     * @param string $name
     * @param ContentRepository\Model\NodeType|null $nodeType
     * @param null $identifier
     * @return \Neos\ContentRepository\Domain\Model\Node
     * @throws NodeOperationIsNotSupportedException
     */
    public function createNode($name, ContentRepository\Model\NodeType $nodeType = null, $identifier = null): ContentRepository\Model\Node
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @param string $name
     * @param ContentRepository\Model\NodeType|null $nodeType
     * @param null $identifier
     * @return \Neos\ContentRepository\Domain\Model\Node
     * @throws NodeOperationIsNotSupportedException
     */
    public function createSingleNode($name, ContentRepository\Model\NodeType $nodeType = null, $identifier = null): ContentRepository\Model\Node
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @param ContentRepository\Model\NodeTemplate $nodeTemplate
     * @param string|null $nodeName
     * @return ContentRepository\Model\NodeInterface
     * @throws NodeOperationIsNotSupportedException
     */
    public function createNodeFromTemplate(ContentRepository\Model\NodeTemplate $nodeTemplate, $nodeName = null): ContentRepository\Model\Node
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @param string $path
     * @return ContentRepository\Model\NodeInterface|void
     * @throws NodeOperationIsNotSupportedException
     */
    public function getNode($path): ?ContentRepository\Model\NodeInterface
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @return ContentRepository\Model\NodeInterface|void
     * @throws NodeOperationIsNotSupportedException
     */
    public function getPrimaryChildNode(): ?ContentRepository\Model\NodeInterface
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @param string $nodeTypeFilter
     * @param string $limit
     * @param string $offset
     * @return array|ContentRepository\Model\NodeInterface[]
     * @throws NodeOperationIsNotSupportedException
     */
    public function getChildNodes($nodeTypeFilter = null, $limit = null, $offset = null): array
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @param string $nodeTypeFilter
     * @return bool
     * @throws NodeOperationIsNotSupportedException
     */
    public function hasChildNodes($nodeTypeFilter = null): bool
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @return void
     * @throws NodeOperationIsNotSupportedException
     */
    public function remove(): void
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @param bool $removed
     * @return void
     * @throws NodeOperationIsNotSupportedException
     */
    public function setRemoved($removed): void
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @return bool
     */
    public function isRemoved(): bool
    {
        return $this->nodeData->isRemoved();
    }

    /**
     * @return bool
     * @throws NodeOperationIsNotSupportedException
     */
    public function isVisible(): bool
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @return bool
     * @throws NodeOperationIsNotSupportedException
     */
    public function isAccessible(): bool
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @return bool
     * @throws NodeOperationIsNotSupportedException
     */
    public function hasAccessRestrictions(): bool
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @param ContentRepository\Model\NodeType $nodeType
     * @return bool
     * @throws NodeOperationIsNotSupportedException
     */
    public function isNodeTypeAllowedAsChildNode(ContentRepository\Model\NodeType $nodeType): bool
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @param ContentRepository\Model\NodeInterface $referenceNode
     * @return void
     * @throws NodeOperationIsNotSupportedException
     */
    public function moveBefore(ContentRepository\Model\NodeInterface $referenceNode): void
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @param ContentRepository\Model\NodeInterface $referenceNode
     * @return void
     * @throws NodeOperationIsNotSupportedException
     */
    public function moveAfter(ContentRepository\Model\NodeInterface $referenceNode): void
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @param ContentRepository\Model\NodeInterface $referenceNode
     * @return void
     * @throws NodeOperationIsNotSupportedException
     */
    public function moveInto(ContentRepository\Model\NodeInterface $referenceNode): void
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @param ContentRepository\Model\NodeInterface $referenceNode
     * @param string $nodeName
     * @return ContentRepository\Model\NodeInterface
     * @throws NodeOperationIsNotSupportedException
     */
    public function copyBefore(ContentRepository\Model\NodeInterface $referenceNode, $nodeName): ContentRepository\Model\NodeInterface
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @param ContentRepository\Model\NodeInterface $referenceNode
     * @param string $nodeName
     * @return ContentRepository\Model\NodeInterface
     * @throws NodeOperationIsNotSupportedException
     */
    public function copyAfter(ContentRepository\Model\NodeInterface $referenceNode, $nodeName): ContentRepository\Model\NodeInterface
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @param ContentRepository\Model\NodeInterface $referenceNode
     * @param string $nodeName
     * @return ContentRepository\Model\NodeInterface
     * @throws NodeOperationIsNotSupportedException
     */
    public function copyInto(ContentRepository\Model\NodeInterface $referenceNode, $nodeName): ContentRepository\Model\NodeInterface
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @return ContentRepository\Model\NodeData
     */
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

    /**
     * @return array
     */
    public function getDimensions(): array
    {
        return $this->nodeData->getDimensionValues();
    }

    /**
     * @param ContentRepository\Service\Context $context
     * @return ContentRepository\Model\NodeInterface
     * @throws NodeOperationIsNotSupportedException
     */
    public function createVariantForContext($context): ContentRepository\Model\NodeInterface
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @return bool
     * @throws NodeOperationIsNotSupportedException
     */
    public function isAutoCreated(): bool
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @return array|ContentRepository\Model\NodeInterface[]
     * @throws NodeOperationIsNotSupportedException
     */
    public function getOtherNodeVariants(): array
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getHiddenAfterDateTime(): ?\DateTimeInterface
    {
        return $this->nodeData->getHiddenAfterDateTime();
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreationDateTime(): \DateTimeInterface
    {
        return $this->nodeData->getCreationDateTime();
    }

    /**
     * @return \DateTimeInterface
     */
    public function getLastModificationDateTime(): \DateTimeInterface
    {
        return $this->nodeData->getLastModificationDateTime();
    }

    /**
     * @return \DateTimeInterface
     */
    public function getLastPublicationDateTime(): \DateTimeInterface
    {
        return $this->nodeData->getLastPublicationDateTime();
    }
}
