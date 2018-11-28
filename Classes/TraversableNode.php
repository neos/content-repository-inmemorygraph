<?php

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
    public function getProperties()
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
     * @throws NodeOperationIsNotSupportedException
     */
    public function setName($newName)
    {
        throw new NodeOperationIsNotSupportedException();
    }

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
     * @throws NodeOperationIsNotSupportedException
     */
    public function setProperty($propertyName, $value)
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
     * @throws NodeOperationIsNotSupportedException
     */
    public function removeProperty($propertyName)
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
     * @throws NodeOperationIsNotSupportedException
     */
    public function setContentObject($contentObject)
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
     */
    public function unsetContentObject()
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @param ContentRepository\Model\NodeType $nodeType
     * @throws NodeOperationIsNotSupportedException
     */
    public function setNodeType(ContentRepository\Model\NodeType $nodeType)
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
     * @throws NodeOperationIsNotSupportedException
     */
    public function setHidden($hidden)
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
     * @throws NodeOperationIsNotSupportedException
     */
    public function setHiddenBeforeDateTime(\DateTime $dateTime = null)
    {
        throw new NodeOperationIsNotSupportedException();
    }

    public function getHiddenBeforeDateTime(): \DateTimeInterface
    {
        return $this->readOnlyNode->getHiddenBeforeDateTime();
    }

    /**
     * @param \DateTime|null $dateTime
     * @throws NodeOperationIsNotSupportedException
     */
    public function setHiddenAfterDateTime(\DateTime $dateTime = null)
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @param bool $hidden
     * @throws NodeOperationIsNotSupportedException
     */
    public function setHiddenInIndex($hidden)
    {
        throw new NodeOperationIsNotSupportedException();
    }

    public function isHiddenInIndex(): bool
    {
        return $this->readOnlyNode->isHiddenInIndex();
    }

    /**
     * @param array $accessRoles
     * @throws NodeOperationIsNotSupportedException
     */
    public function setAccessRoles(array $accessRoles)
    {
        throw new NodeOperationIsNotSupportedException();
    }

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
     * @return int|void
     * @throws NodeOperationIsNotSupportedException
     */
    public function getDepth()
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @param ContentRepository\Model\Workspace $workspace
     * @throws NodeOperationIsNotSupportedException
     */
    public function setWorkspace(ContentRepository\Model\Workspace $workspace)
    {
        throw new NodeOperationIsNotSupportedException();
    }

    public function getWorkspace(): ?ContentRepository\Model\Workspace
    {
        return $this->readOnlyNode->getWorkspace();
    }

    /**
     * @param int $index
     * @throws NodeOperationIsNotSupportedException
     */
    public function setIndex($index)
    {
        throw new NodeOperationIsNotSupportedException();
    }

    public function getIndex(): ?int
    {
        return $this->readOnlyNode->getIndex();
    }

    public function getParent(): ?TraversableNode
    {
        return $this->contentSubgraph->getParentNode($this);
    }

    public function getParentPath(): string
    {
        return $this->readOnlyNode->getParentPath();
    }

    /**
     * @param string $name
     * @param ContentRepository\Model\NodeType|null $nodeType
     * @param null $identifier
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
     * @param null $identifier
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
     * @return bool|void
     * @throws NodeOperationIsNotSupportedException
     */
    public function hasChildNodes($nodeTypeFilter = null)
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @throws NodeOperationIsNotSupportedException
     */
    public function remove()
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @param bool $removed
     * @throws NodeOperationIsNotSupportedException
     */
    public function setRemoved($removed)
    {
        throw new NodeOperationIsNotSupportedException();
    }

    public function isRemoved(): bool
    {
        return $this->readOnlyNode->isRemoved();
    }

    /**
     * @return bool|void
     * @throws NodeOperationIsNotSupportedException
     */
    public function isVisible()
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @return bool|void
     * @throws NodeOperationIsNotSupportedException
     */
    public function isAccessible()
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @return bool|void
     * @throws NodeOperationIsNotSupportedException
     */
    public function hasAccessRestrictions()
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @param ContentRepository\Model\NodeType $nodeType
     * @return bool|void
     * @throws NodeOperationIsNotSupportedException
     */
    public function isNodeTypeAllowedAsChildNode(ContentRepository\Model\NodeType $nodeType)
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @param ContentRepository\Model\NodeInterface $referenceNode
     * @throws NodeOperationIsNotSupportedException
     */
    public function moveBefore(ContentRepository\Model\NodeInterface $referenceNode)
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @param ContentRepository\Model\NodeInterface $referenceNode
     * @throws NodeOperationIsNotSupportedException
     */
    public function moveAfter(ContentRepository\Model\NodeInterface $referenceNode)
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @param ContentRepository\Model\NodeInterface $referenceNode
     * @throws NodeOperationIsNotSupportedException
     */
    public function moveInto(ContentRepository\Model\NodeInterface $referenceNode)
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @param ContentRepository\Model\NodeInterface $referenceNode
     * @param string $nodeName
     * @return ContentRepository\Model\NodeInterface|void
     * @throws NodeOperationIsNotSupportedException
     */
    public function copyBefore(ContentRepository\Model\NodeInterface $referenceNode, $nodeName)
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @param ContentRepository\Model\NodeInterface $referenceNode
     * @param string $nodeName
     * @return ContentRepository\Model\NodeInterface|void
     * @throws NodeOperationIsNotSupportedException
     */
    public function copyAfter(ContentRepository\Model\NodeInterface $referenceNode, $nodeName)
    {
        throw new NodeOperationIsNotSupportedException();
    }

    /**
     * @param ContentRepository\Model\NodeInterface $referenceNode
     * @param string $nodeName
     * @return ContentRepository\Model\NodeInterface|void
     * @throws NodeOperationIsNotSupportedException
     */
    public function copyInto(ContentRepository\Model\NodeInterface $referenceNode, $nodeName)
    {
        throw new NodeOperationIsNotSupportedException();
    }

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

    public function getDimensions(): array
    {
        return $this->readOnlyNode->getDimensions();
    }

    /**
     * @param ContentRepository\Service\Context $context
     * @throws NodeOperationIsNotSupportedException
     */
    public function createVariantForContext($context)
    {
        throw new NodeOperationIsNotSupportedException();
    }

    public function isAutoCreated(): bool
    {
        $isAutoCreated = new IsAutoCreated();

        return $isAutoCreated->isSatisfiedBy($this);
    }

    /**
     * @throws NodeOperationIsNotSupportedException
     */
    public function getOtherNodeVariants()
    {
        throw new NodeOperationIsNotSupportedException();
    }

    public function getHiddenAfterDateTime(): ?\DateTimeInterface
    {
        return $this->readOnlyNode->getHiddenAfterDateTime();
    }

    public function getCreationDateTime(): \DateTimeInterface
    {
        return $this->readOnlyNode->getCreationDateTime();
    }

    public function getLastModificationDateTime(): \DateTimeInterface
    {
        return $this->readOnlyNode->getLastModificationDateTime();
    }

    public function getLastPublicationDateTime(): \DateTimeInterface
    {
        return $this->readOnlyNode->getLastPublicationDateTime();
    }
}
