<?php
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
     * @var TraversableNode
     */
    protected $parent;

    /**
     * @var TraversableNode
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
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $properties = [];

    public function __construct(TraversableNode $parent, TraversableNode $child, ContentSubgraph $subgraph, int $position = 0, $name = null, array $properties = [])
    {
        $this->parent = $parent;
        $this->child = $child;
        $this->subgraph = $subgraph;
        $this->subgraphHash = (string) $subgraph;
        $this->position = $position;
        $this->name = $name;
        $this->properties = $properties;
    }


    public function getSubgraph(): ContentSubgraph
    {
        return $this->subgraph;
    }

    public function getSubgraphHash(): string
    {
        return $this->subgraphHash;
    }

    public function getParent(): TraversableNode
    {
        return $this->parent;
    }
    public function getChild(): TraversableNode
    {
        return $this->child;
    }

    public function setPosition(int $position)
    {
        $this->position = $position;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getName(): ?string
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

    public function getProperty(string $propertyName)
    {
        return $this->properties[$propertyName] ?? null;
    }

    public function setProperty(string $propertyName, $propertyValue)
    {
        $this->properties[$propertyName] = $propertyValue;
    }

    public function getParentEdge(): ?Edge
    {
        return $this->subgraph->getParentEdge($this->getParent());
    }

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
