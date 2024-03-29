<?php

declare(strict_types=1);

namespace Neos\ContentRepository\InMemoryGraph\ContentSubgraph;

/*
 * This file is part of the Neos.ContentRepository.InMemoryGraph package.
 */

use Neos\ContentRepository\DimensionSpace\DimensionSpace\DimensionSpacePoint;
use Neos\ContentRepository\Domain as ContentRepository;
use Neos\ContentRepository\Domain\ContentStream\ContentStreamIdentifier;
use Neos\ContentRepository\Domain\ContentSubgraph\NodePath;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Model\NodeTemplate;
use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\ContentRepository\Domain\Model\Workspace;
use Neos\ContentRepository\Domain\NodeAggregate\NodeAggregateIdentifier;
use Neos\ContentRepository\Domain\NodeAggregate\NodeName;
use Neos\ContentRepository\Domain\NodeType\NodeTypeConstraints;
use Neos\ContentRepository\Domain\NodeType\NodeTypeName;
use Neos\ContentRepository\Domain\Projection\Content\TraversableNodeInterface;
use Neos\ContentRepository\Domain\Projection\Content\TraversableNodes;
use Neos\ContentRepository\Domain\Service\Context;
use Neos\ContentRepository\Exception\NodeException;
use Neos\ContentRepository\Exception\NodeExistsException;
use Neos\ContentRepository\InMemoryGraph\NodeAggregate\Node;
use Neos\EventSourcedContentRepository\Domain\Context\NodeAggregate\OriginDimensionSpacePoint;
use Neos\EventSourcedContentRepository\Domain\ValueObject\PropertyName;
use Neos\Neos\Domain\Service\ContentContext;

/**
 * The traversable read only node implementation
 */
final class TraversableNode implements ContentRepository\Projection\Content\TraversableNodeInterface, ContentRepository\Model\NodeInterface
{
    /**
     * @var Node
     */
    protected $node;

    /**
     * @var ContentSubgraph
     */
    protected $contentSubgraph;

    public function __construct(Node $node, ContentSubgraph $contentSubgraph)
    {
        $this->node = $node;
        $this->contentSubgraph = $contentSubgraph;
    }

    public function getDataNode(): Node
    {
        return $this->node;
    }

    public function getProperties(): ContentRepository\Projection\Content\PropertyCollectionInterface
    {
        return $this->node->getProperties();
    }

    public function getProperty($propertyName)
    {
        return $this->node->getProperty($propertyName);
    }

    public function getLabel(): string
    {
        return $this->getNodeType()->getNodeLabelGenerator()->getLabel($this) ?? '';
    }

    public function hasProperty($propertyName): bool
    {
        return $this->node->hasProperty($propertyName);
    }

    public function getNodeType(): ContentRepository\Model\NodeType
    {
        return $this->node->getNodeType();
    }

    public function isHidden(): bool
    {
        return $this->node->isHidden();
    }

    public function getHiddenBeforeDateTime(): ?\DateTimeInterface
    {
        return $this->node->getHiddenBeforeDateTime();
    }

    public function isHiddenInIndex(): bool
    {
        return $this->node->isHiddenInIndex();
    }

    public function getAccessRoles(): array
    {
        return $this->node->getAccessRoles();
    }

    public function getPath(): string
    {
        return $this->node->getPath();
    }

    public function getDepth(): int
    {
        return $this->node->getDepth();
    }

    public function getWorkspace(): ?ContentRepository\Model\Workspace
    {
        return $this->node->getWorkspace();
    }

    public function getIndex(): ?int
    {
        return $this->node->getIndex();
    }

    public function getParent(): ?TraversableNode
    {
        return $this->contentSubgraph->getParentNode($this);
    }

    public function getParentPath(): string
    {
        return $this->node->getParentPath();
    }

    public function getNode($path): ?TraversableNodeInterface
    {
        return $this->contentSubgraph->getChildNode($this, $path);
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

    public function isRemoved(): bool
    {
        return $this->node->isRemoved();
    }

    public function getNodeData(): ContentRepository\Model\NodeData
    {
        return $this->node->getNodeData();
    }

    /**
     * @return ContentRepository\Service\Context
     * @throws \Exception
     */
    public function getContext(): ContentRepository\Service\Context
    {
        $dimensions = $this->node->getDimensions();
        unset($dimensions['_workspace']);
        $targetDimensions = $this->node->getOriginDimensionSpacePoint()->getCoordinates();
        unset($targetDimensions['_workspace']);
        return new ContentContext(
            $this->node->getWorkspace()->getName(),
            new \DateTimeImmutable(),
            $dimensions,
            $targetDimensions,
            true,
            true,
            true
        );
    }

    public function getDimensions(): array
    {
        return $this->node->getDimensions();
    }

    public function getHiddenAfterDateTime(): ?\DateTimeInterface
    {
        return $this->node->getHiddenAfterDateTime();
    }

    public function getCreationDateTime(): \DateTimeInterface
    {
        return $this->node->getCreationDateTime();
    }

    public function getLastModificationDateTime(): \DateTimeInterface
    {
        return $this->node->getLastModificationDateTime();
    }

    public function getLastPublicationDateTime(): \DateTimeInterface
    {
        return $this->node->getLastPublicationDateTime();
    }


    public function getCacheEntryIdentifier(): string
    {
        return sha1(json_encode([
            'nodeAggregateIdentifier' => $this->getNodeAggregateIdentifier(),
            'contentStreamIdentifier' => $this->getContentStreamIdentifier(),
            'dimensionSpacePoint' => $this->getDimensionSpacePoint()
        ]));
    }

    public function isRoot(): bool
    {
        return $this->node->isRoot();
    }

    public function isTethered(): bool
    {
        return $this->node->isTethered();
    }

    public function isVisible(): bool
    {
        return $this->node->isVisible();
    }

    public function isAccessible(): bool
    {
        return $this->node->isAccessible();
    }

    public function hasAccessRestrictions(): bool
    {
        return $this->node->hasAccessRestrictions();
    }

    public function getContentStreamIdentifier(): ContentStreamIdentifier
    {
        return $this->node->getContentStreamIdentifier();
    }

    public function getNodeAggregateIdentifier(): NodeAggregateIdentifier
    {
        return $this->node->getNodeAggregateIdentifier();
    }

    public function getNodeTypeName(): NodeTypeName
    {
        return $this->node->getNodeTypeName();
    }

    public function getNodeName(): ?NodeName
    {
        return $this->node->getNodeName();
    }

    public function getOriginDimensionSpacePoint(): OriginDimensionSpacePoint
    {
        return $this->node->getOriginDimensionSpacePoint();
    }

    public function getDimensionSpacePoint(): DimensionSpacePoint
    {
        return $this->contentSubgraph->getDimensionSpacePoint();
    }

    public function findParentNode(): TraversableNodeInterface
    {
        $parentNode = $this->contentSubgraph->getParentNode($this);
        if (!$parentNode) {
            throw new NodeException('Parent node not found', 1542983610);
        }
        return $parentNode;
    }

    public function findNodePath(): NodePath
    {
        return NodePath::fromString($this->node->getPath());
    }

    /**
     * Retrieves and returns a child node by name from the node's subgraph.
     *
     * @param NodeName $nodeName The name
     * @return TraversableNodeInterface
     * @throws NodeException If no child node with the given $nodeName can be found
     */
    public function findNamedChildNode(NodeName $nodeName): TraversableNodeInterface
    {
        return $this->contentSubgraph->getChildNode($this, $nodeName);
        // TODO: Implement findNamedChildNode() method.
    }

    /**
     * Retrieves and returns all direct child nodes of this node from its subgraph.
     * If node type constraints are specified, only nodes of that type are returned.
     *
     * @param NodeTypeConstraints $nodeTypeConstraints If specified, only nodes with that node type are considered
     * @param int $limit An optional limit for the number of nodes to find. Added or removed nodes can still change the number nodes!
     * @param int $offset An optional offset for the query
     * @return TraversableNodes Traversable nodes that matched the given constraints
     * @api
     */
    public function findChildNodes(
        NodeTypeConstraints $nodeTypeConstraints = null,
        int $limit = null,
        int $offset = null
    ): TraversableNodes {
        return TraversableNodes::fromArray($this->contentSubgraph->getChildNodes($this));
    }

    /**
     * Returns the number of direct child nodes of this node from its subgraph.
     *
     * @param NodeTypeConstraints|null $nodeTypeConstraints If specified, only nodes with that node type are considered
     * @return int
     */
    public function countChildNodes(NodeTypeConstraints $nodeTypeConstraints = null): int
    {
        // TODO: Implement countChildNodes() method.
    }

    /**
     * Retrieves and returns all nodes referenced by this node from its subgraph.
     *
     * @return TraversableNodes
     */
    public function findReferencedNodes(): TraversableNodes
    {
        // TODO: Implement findReferencedNodes() method.
    }

    /**
     * Retrieves and returns nodes referenced by this node by name from its subgraph.
     *
     * @param PropertyName $edgeName
     * @return TraversableNodes
     */
    public function findNamedReferencedNodes(PropertyName $edgeName): TraversableNodes
    {
        // TODO: Implement findNamedReferencedNodes() method.
    }

    /**
     * Retrieves and returns nodes referencing this node from its subgraph.
     *
     * @return TraversableNodes
     */
    public function findReferencingNodes(): TraversableNodes
    {
        // TODO: Implement findReferencingNodes() method.
    }

    /**
     * Retrieves and returns nodes referencing this node by name from its subgraph.
     *
     * @param PropertyName $nodeName
     * @return TraversableNodes
     */
    public function findNamedReferencingNodes(PropertyName $nodeName): TraversableNodes
    {
        // TODO: Implement findNamedReferencingNodes() method.
    }

    /**
     * Compare whether two traversable nodes are equal
     *
     * @param TraversableNodeInterface $other
     * @return bool
     */
    public function equals(TraversableNodeInterface $other): bool
    {
        // TODO: Implement equals() method.
    }

    public function setName($newName)
    {
        // TODO: Implement setName() method.
    }

    public function getName()
    {
        // TODO: Implement getName() method.
    }

    public function setProperty($propertyName, $value)
    {
        // TODO: Implement setProperty() method.
    }

    public function removeProperty($propertyName)
    {
        // TODO: Implement removeProperty() method.
    }

    public function getPropertyNames()
    {
        // TODO: Implement getPropertyNames() method.
    }

    public function setContentObject($contentObject)
    {
        // TODO: Implement setContentObject() method.
    }

    public function getContentObject()
    {
        // TODO: Implement getContentObject() method.
    }

    public function unsetContentObject()
    {
        // TODO: Implement unsetContentObject() method.
    }

    public function setNodeType(NodeType $nodeType)
    {
        // TODO: Implement setNodeType() method.
    }

    public function setHidden($hidden)
    {
        // TODO: Implement setHidden() method.
    }

    public function setHiddenBeforeDateTime(\DateTimeInterface $dateTime = null)
    {
        // TODO: Implement setHiddenBeforeDateTime() method.
    }

    public function setHiddenAfterDateTime(\DateTimeInterface $dateTime = null)
    {
        // TODO: Implement setHiddenAfterDateTime() method.
    }

    public function setHiddenInIndex($hidden)
    {
        // TODO: Implement setHiddenInIndex() method.
    }

    public function setAccessRoles(array $accessRoles)
    {
        // TODO: Implement setAccessRoles() method.
    }

    public function getContextPath()
    {
        // TODO: Implement getContextPath() method.
    }

    public function setWorkspace(Workspace $workspace)
    {
        // TODO: Implement setWorkspace() method.
    }

    public function getIdentifier()
    {
        // TODO: Implement getIdentifier() method.
    }

    public function setIndex($index)
    {
        // TODO: Implement setIndex() method.
    }

    public function createNode($name, NodeType $nodeType = null, $identifier = null)
    {
        // TODO: Implement createNode() method.
    }

    public function createSingleNode($name, NodeType $nodeType = null, $identifier = null)
    {
        // TODO: Implement createSingleNode() method.
    }

    public function createNodeFromTemplate(NodeTemplate $nodeTemplate, $nodeName = null)
    {
        // TODO: Implement createNodeFromTemplate() method.
    }

    public function getPrimaryChildNode()
    {
        // TODO: Implement getPrimaryChildNode() method.
    }

    public function hasChildNodes($nodeTypeFilter = null)
    {
        // TODO: Implement hasChildNodes() method.
    }

    public function remove()
    {
        // TODO: Implement remove() method.
    }

    public function setRemoved($removed)
    {
        // TODO: Implement setRemoved() method.
    }

    public function isNodeTypeAllowedAsChildNode(NodeType $nodeType)
    {
        // TODO: Implement isNodeTypeAllowedAsChildNode() method.
    }

    public function moveBefore(NodeInterface $referenceNode)
    {
        // TODO: Implement moveBefore() method.
    }

    public function moveAfter(NodeInterface $referenceNode)
    {
        // TODO: Implement moveAfter() method.
    }

    public function moveInto(NodeInterface $referenceNode)
    {
        // TODO: Implement moveInto() method.
    }

    public function copyBefore(NodeInterface $referenceNode, $nodeName)
    {
        // TODO: Implement copyBefore() method.
    }

    public function copyAfter(NodeInterface $referenceNode, $nodeName)
    {
        // TODO: Implement copyAfter() method.
    }

    public function copyInto(NodeInterface $referenceNode, $nodeName)
    {
        // TODO: Implement copyInto() method.
    }

    public function createVariantForContext($context)
    {
        // TODO: Implement createVariantForContext() method.
    }

    public function isAutoCreated()
    {
        // TODO: Implement isAutoCreated() method.
    }

    public function getOtherNodeVariants()
    {
        // TODO: Implement getOtherNodeVariants() method.
    }
}
