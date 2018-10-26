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
     * @throws NodeOperationIsNotSupported
     */
    public function setName($newName)
    {
        throw new NodeOperationIsNotSupported();
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
     * @throws NodeOperationIsNotSupported
     */
    public function setProperty($propertyName, $value)
    {
        throw new NodeOperationIsNotSupported();
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
     * @throws NodeOperationIsNotSupported
     */
    public function removeProperty($propertyName)
    {
        throw new NodeOperationIsNotSupported();
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
     * @throws NodeOperationIsNotSupported
     */
    public function setContentObject($contentObject)
    {
        throw new NodeOperationIsNotSupported();
    }

    /**
     * @return \object|void
     * @throws NodeOperationIsNotSupported
     */
    public function getContentObject()
    {
        throw new NodeOperationIsNotSupported();
    }

    /**
     * @throws NodeOperationIsNotSupported
     */
    public function unsetContentObject()
    {
        throw new NodeOperationIsNotSupported();
    }

    /**
     * @param ContentRepository\Model\NodeType $nodeType
     * @throws NodeOperationIsNotSupported
     */
    public function setNodeType(ContentRepository\Model\NodeType $nodeType)
    {
        throw new NodeOperationIsNotSupported();
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
     * @throws NodeOperationIsNotSupported
     */
    public function setHidden($hidden)
    {
        throw new NodeOperationIsNotSupported();
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
     * @throws NodeOperationIsNotSupported
     */
    public function setHiddenBeforeDateTime(\DateTime $dateTime = null)
    {
        throw new NodeOperationIsNotSupported();
    }

    public function getHiddenBeforeDateTime(): \DateTimeInterface
    {
        return $this->readOnlyNode->getHiddenBeforeDateTime();
    }

    /**
     * @param \DateTime|null $dateTime
     * @throws NodeOperationIsNotSupported
     */
    public function setHiddenAfterDateTime(\DateTime $dateTime = null)
    {
        throw new NodeOperationIsNotSupported();
    }

    /**
     * @param bool $hidden
     * @throws NodeOperationIsNotSupported
     */
    public function setHiddenInIndex($hidden)
    {
        throw new NodeOperationIsNotSupported();
    }

    public function isHiddenInIndex(): bool
    {
        return $this->readOnlyNode->isHiddenInIndex();
    }

    /**
     * @param array $accessRoles
     * @throws NodeOperationIsNotSupported
     */
    public function setAccessRoles(array $accessRoles)
    {
        throw new NodeOperationIsNotSupported();
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
     * @throws NodeOperationIsNotSupported
     */
    public function getDepth()
    {
        throw new NodeOperationIsNotSupported();
    }

    /**
     * @param ContentRepository\Model\Workspace $workspace
     * @throws NodeOperationIsNotSupported
     */
    public function setWorkspace(ContentRepository\Model\Workspace $workspace)
    {
        throw new NodeOperationIsNotSupported();
    }

    public function getWorkspace(): ?ContentRepository\Model\Workspace
    {
        return $this->readOnlyNode->getWorkspace();
    }

    /**
     * @param int $index
     * @throws NodeOperationIsNotSupported
     */
    public function setIndex($index)
    {
        throw new NodeOperationIsNotSupported();
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
     * @throws NodeOperationIsNotSupported
     */
    public function createNode($name, ContentRepository\Model\NodeType $nodeType = null, $identifier = null)
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
    public function createSingleNode($name, ContentRepository\Model\NodeType $nodeType = null, $identifier = null)
    {
        throw new NodeOperationIsNotSupported();
    }

    /**
     * @param ContentRepository\Model\NodeTemplate $nodeTemplate
     * @param string|null $nodeName
     * @return ContentRepository\Model\NodeInterface|void
     * @throws NodeOperationIsNotSupported
     */
    public function createNodeFromTemplate(ContentRepository\Model\NodeTemplate $nodeTemplate, $nodeName = null)
    {
        throw new NodeOperationIsNotSupported();
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
     * @throws NodeOperationIsNotSupported
     */
    public function getPrimaryChildNode()
    {
        throw new NodeOperationIsNotSupported();
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
     * @throws NodeOperationIsNotSupported
     */
    public function hasChildNodes($nodeTypeFilter = null)
    {
        throw new NodeOperationIsNotSupported();
    }

    /**
     * @throws NodeOperationIsNotSupported
     */
    public function remove()
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
        return $this->readOnlyNode->isRemoved();
    }

    /**
     * @return bool|void
     * @throws NodeOperationIsNotSupported
     */
    public function isVisible()
    {
        throw new NodeOperationIsNotSupported();
    }

    /**
     * @return bool|void
     * @throws NodeOperationIsNotSupported
     */
    public function isAccessible()
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
     * @throws NodeOperationIsNotSupported
     */
    public function createVariantForContext($context)
    {
        throw new NodeOperationIsNotSupported();
    }

    public function isAutoCreated(): bool
    {
        $isAutoCreated = new IsAutoCreated();

        return $isAutoCreated->isSatisfiedBy($this);
    }

    /**
     * @throws NodeOperationIsNotSupported
     */
    public function getOtherNodeVariants()
    {
        throw new NodeOperationIsNotSupported();
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
