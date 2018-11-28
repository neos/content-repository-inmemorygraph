<?php

declare(strict_types=1);

namespace Neos\ContentRepository\InMemoryGraph;

/*
 * This file is part of the Neos.ContentRepository.InMemoryGraph package.
 */

use Neos\ContentRepository\Domain as ContentRepository;
use Neos\ContentRepository\Domain\Utility\NodePaths;

/**
 * The traversable read only node implementation
 */
final class TraversableNode implements ContentRepository\Model\NodeInterface
{
    /**
     * @var ReadOnlyNode
     */
    protected $readOnlyNode;

    /**
     * @var ContentSubgraph
     */
    protected $contentSubgraph;

    /**
     * @param ReadOnlyNode $readOnlyNode
     * @param ContentSubgraph $contentSubgraph
     */
    public function __construct(ReadOnlyNode $readOnlyNode, ContentSubgraph $contentSubgraph)
    {
        $this->readOnlyNode = $readOnlyNode;
        $this->contentSubgraph = $contentSubgraph;
    }

    public function getIdentifier(): string
    {
        return $this->readOnlyNode->getIdentifier();
    }

    public function getNodeIdentifier(): string
    {
        return $this->readOnlyNode->getNodeIdentifier();
    }

    /**
     * @return array
     * @throws \Neos\ContentRepository\Exception\NodeException
     */
    public function getProperties(): array
    {
        return $this->readOnlyNode->getProperties();
    }

    /**
     * @param string $propertyName
     * @return mixed|null
     * @throws \Neos\ContentRepository\Exception\NodeException
     */
    public function getProperty($propertyName)
    {
        return $this->readOnlyNode->getProperty($propertyName);
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
        return $this->readOnlyNode->getName();
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
        return $this->readOnlyNode->hasProperty($propertyName);
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
        return $this->readOnlyNode->getPropertyNames();
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
     * @throws NodeOperationIsNotSupportedException
     * @return void
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
        return $this->readOnlyNode->getNodeType();
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
        return $this->readOnlyNode->isHidden();
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

    public function getHiddenBeforeDateTime(): \DateTimeInterface
    {
        return $this->readOnlyNode->getHiddenBeforeDateTime();
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
        return $this->readOnlyNode->isHiddenInIndex();
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
        return $this->readOnlyNode->getAccessRoles();
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->readOnlyNode->getPath();
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
                $this->readOnlyNode->getDimensions()
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
        return $this->readOnlyNode->getWorkspace();
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
        return $this->readOnlyNode->getIndex();
    }

    /**
     * @return TraversableNode|null
     */
    public function getParent(): ?TraversableNode
    {
        return $this->contentSubgraph->getParentNode($this);
    }

    /**
     * @return string
     */
    public function getParentPath(): string
    {
        return $this->readOnlyNode->getParentPath();
    }

    /**
     * @param string $name
     * @param ContentRepository\Model\NodeType|null $nodeType
     * @param string $identifier
     * @return \Neos\ContentRepository\Domain\Model\Node|void
     * @throws NodeOperationIsNotSupportedException
     */
    public function createNode($name, ContentRepository\Model\NodeType $nodeType = null, $identifier = null)
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @param string $name
     * @param ContentRepository\Model\NodeType|null $nodeType
     * @param string $identifier
     * @return \Neos\ContentRepository\Domain\Model\Node|void
     * @throws NodeOperationIsNotSupportedException
     */
    public function createSingleNode($name, ContentRepository\Model\NodeType $nodeType = null, $identifier = null)
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @param ContentRepository\Model\NodeTemplate $nodeTemplate
     * @param string|null $nodeName
     * @return ContentRepository\Model\NodeInterface|void
     * @throws NodeOperationIsNotSupportedException
     */
    public function createNodeFromTemplate(ContentRepository\Model\NodeTemplate $nodeTemplate, $nodeName = null)
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @param string $path
     * @return ContentRepository\Model\NodeInterface|null
     */
    public function getNode($path): ?ContentRepository\Model\NodeInterface
    {
        return $this->contentSubgraph->getChildNode($this, $path);
    }

    /**
     * @return ContentRepository\Model\NodeInterface|void
     * @throws NodeOperationIsNotSupportedException
     */
    public function getPrimaryChildNode()
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @param string $nodeTypeFilter
     * @param string $limit
     * @param string $offset
     * @return array|ContentRepository\Model\NodeInterface[]
     */
    public function getChildNodes($nodeTypeFilter = null, $limit = null, $offset = null): array
    {
        return $this->contentSubgraph->getChildNodes($this);
    }

    /**
     * @return array|Edge[]
     */
    public function getChildEdges(): array
    {
        return $this->contentSubgraph->getChildEdges($this);
    }

    /**
     * @param null $nodeTypeFilter
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

    public function isRemoved(): bool
    {
        return $this->readOnlyNode->isRemoved();
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
        return $this->readOnlyNode->getNodeData();
    }

    /**
     * @return ContentRepository\Service\Context
     * @throws \Exception
     */
    public function getContext(): ContentRepository\Service\Context
    {
        return new ContentRepository\Service\Context(
            $this->readOnlyNode->getWorkspace()->getName(),
            new \DateTimeImmutable(),
            $this->readOnlyNode->getDimensions(),
            $this->readOnlyNode->getDimensionSpacePoint()->getCoordinates(),
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
        return $this->readOnlyNode->getDimensions();
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
     */
    public function isAutoCreated(): bool
    {
        $isAutoCreated = new IsAutoCreated();

        return $isAutoCreated->isSatisfiedBy($this);
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
        return $this->readOnlyNode->getHiddenAfterDateTime();
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreationDateTime(): \DateTimeInterface
    {
        return $this->readOnlyNode->getCreationDateTime();
    }

    /**
     * @return \DateTimeInterface
     */
    public function getLastModificationDateTime(): \DateTimeInterface
    {
        return $this->readOnlyNode->getLastModificationDateTime();
    }

    /**
     * @return \DateTimeInterface
     */
    public function getLastPublicationDateTime(): \DateTimeInterface
    {
        return $this->readOnlyNode->getLastPublicationDateTime();
    }
}
