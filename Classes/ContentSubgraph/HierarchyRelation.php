<?php

declare(strict_types=1);

namespace Neos\ContentRepository\InMemoryGraph\ContentSubgraph;

/*
 * This file is part of the Neos.ContentRepository.InMemoryGraph package.
 */

use Neos\ContentRepository\Domain\NodeAggregate\NodeName;
use Neos\ContentRepository\InMemoryGraph\NodeAggregate\Node;

/**
 * The hierarchy relation domain entity
 */
final class HierarchyRelation
{
    /**
     * @var Node
     */
    protected $parent;

    /**
     * @var Node
     */
    protected $child;

    /**
     * @var ContentSubgraph
     */
    protected $subgraph;

    /**
     * @var string
     */
    protected $subgraphHash;

    /**
     * @var int
     */
    protected $position;

    /**
     * @var NodeName
     */
    protected $name;

    /**
     * @var array
     */
    protected $properties = [];

    public function __construct(Node $parent, Node $child, ContentSubgraph $subgraph, string $subgraphHash, int $position, NodeName $name = null, array $properties = [])
    {
        $this->parent = $parent;
        $this->child = $child;
        $this->subgraph = $subgraph;
        $this->subgraphHash = $subgraphHash;
        $this->position = $position;
        $this->name = $name;
        $this->properties = $properties;

        $this->mergeStructurePropertiesWithParent();
    }

    public function getSubgraph(): ContentSubgraph
    {
        return $this->subgraph;
    }

    public function getSubgraphHash(): string
    {
        return $this->subgraphHash;
    }

    public function getParent():Node
    {
        return $this->parent;
    }

    public function getChild(): Node
    {
        return $this->child;
    }

    public function setPosition(string $position): void
    {
        $this->position = $position;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getName(): ?NodeName
    {
        return $this->name;
    }

    public function getNameForGraph(): string
    {
        return $this->getName() . '@' . $this->subgraphHash;
    }

    public function getProperties() : array
    {
        return $this->properties;
    }

    /**
     * @param string $propertyName
     * @return mixed|null
     */
    public function getProperty(string $propertyName)
    {
        return $this->properties[$propertyName] ?? null;
    }

    /**
     * @param string $propertyName
     * @param mixed $propertyValue
     * @return void
     */
    public function setProperty(string $propertyName, $propertyValue): void
    {
        $this->properties[$propertyName] = $propertyValue;
    }

    public function getParentEdge(): ?HierarchyRelation
    {
        return $this->getParent()->getIncomingHierarchyRelationInSubgraph($this->subgraph->getIdentifier());
    }

    public function mergeStructurePropertiesWithParent(): void
    {
        if (!$this->getParentEdge()) {
            return;
        }
        $this->properties['accessRoles'] = array_intersect($this->getProperty('accessRoles') ?: [], $this->getParentEdge()->getProperty('accessRoles') ?: []);
        $this->properties['hidden'] = $this->getProperty('hidden') || $this->getParentEdge()->getProperty('hidden');
        if ($this->getProperty('hiddenBeforeDateTime')) {
            if ($this->getParentEdge()->getProperty('hiddenBeforeDateTime')) {
                $this->properties['hiddenBeforeDateTime'] = max($this->getProperty('hiddenBeforeDateTime'), $this->getParentEdge()->getProperty('hiddenBeforeDateTime'));
            } else {
                $this->properties['hiddenBeforeDateTime'] = $this->getProperty('hiddenBeforeDateTime');
            }
        } else {
            $this->properties['hiddenBeforeDateTime'] = $this->getParentEdge()->getProperty('hiddenBeforeDateTime');
        }
        if ($this->getProperty('hiddenAfterDateTime')) {
            if ($this->getParentEdge()->getProperty('hiddenAfterDateTime')) {
                $this->properties['hiddenAfterDateTime'] = min($this->getProperty('hiddenAfterDateTime'), $this->getParentEdge()->getProperty('hiddenAfterDateTime'));
            } else {
                $this->properties['hiddenAfterDateTime'] = $this->getProperty('hiddenAfterDateTime');
            }
        } else {
            $this->properties['hiddenAfterDateTime'] = $this->getParentEdge()->getProperty('hiddenAfterDateTime');
        }
        $this->properties['hiddenInIndex'] = $this->getProperty('hiddenInIndex') || $this->getParentEdge()->getProperty('hiddenInIndex');
    }

    public function getLocalIdentifier(): string
    {
        return $this->getName() ? (string)$this->getNameForGraph() : (string)$this->getChild()->getNodeAggregateIdentifier();
    }
}
