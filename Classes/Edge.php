<?php

declare(strict_types=1);

namespace Neos\ContentRepository\InMemoryGraph;

/*
 * This file is part of the Neos.ContentRepository.InMemoryGraph package.
 */

/**
 * The Edge domain entity
 */
final class Edge
{
    /**
     * @var ReadOnlyNode
     */
    protected $parent;

    /**
     * @var ReadOnlyNode
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
     * @var string
     */
    protected $position;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * Edge constructor.
     * @param ReadOnlyNode $parent
     * @param ReadOnlyNode $child
     * @param ContentSubgraph $subgraph
     * @param string $subgraphHash
     * @param string $position
     * @param string $name
     * @param array $properties
     */
    public function __construct(ReadOnlyNode $parent, ReadOnlyNode $child, ContentSubgraph $subgraph, string $subgraphHash, $position = 'start', $name = null, array $properties = [])
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


    /**
     * @return ContentSubgraph
     */
    public function getSubgraph(): ContentSubgraph
    {
        return $this->subgraph;
    }

    /**
     * @return string
     */
    public function getSubgraphHash(): string
    {
        return $this->subgraphHash;
    }

    /**
     * @return ReadOnlyNode
     */
    public function getParent():ReadOnlyNode
    {
        return $this->parent;
    }
    /**
     * @return ReadOnlyNode
     */
    public function getChild(): ReadOnlyNode
    {
        return $this->child;
    }

    public function setPosition(string $position): void
    {
        $this->position = $position;
    }

    public function getPosition(): string
    {
        return $this->position;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getNameForGraph()
    {
        return $this->getName() . '@' . $this->subgraphHash;
    }

    /**
     * @return array
     */
    public function getProperties() : array
    {
        return $this->properties;
    }

    /**
     * @param $propertyName
     * @return mixed|null
     */
    public function getProperty($propertyName)
    {
        return $this->properties[$propertyName] ?? null;
    }

    /**
     * @param $propertyName
     * @param $propertyValue
     * @return void
     */
    public function setProperty($propertyName, $propertyValue)
    {
        $this->properties[$propertyName] = $propertyValue;
    }

    /**
     * @return Edge|null
     */
    public function getParentEdge()
    {
        return $this->getParent()->getIncomingEdgeInSubgraph($this->subgraph->getIdentifier());
    }

    /**
     * @return void
     */
    public function mergeStructurePropertiesWithParent()
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
        return $this->getName() ?: $this->getChild()->getIdentifier();
    }
}
