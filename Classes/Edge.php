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

    /**
     * Edge constructor.
     *
     * @param TraversableNode $parent
     * @param TraversableNode $child
     * @param ContentSubgraph $subgraph
     * @param int $position
     * @param string $name
     * @param array $properties
     */
    public function __construct(TraversableNode $parent, TraversableNode $child, ContentSubgraph $subgraph, int $position = 0, string $name = null, array $properties = [])
    {
        $this->parent = $parent;
        $this->child = $child;
        $this->subgraph = $subgraph;
        $this->subgraphHash = (string)$subgraph;
        $this->position = $position;
        $this->name = $name;
        $this->properties = $properties;
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
     * @return TraversableNode
     */
    public function getParent(): TraversableNode
    {
        return $this->parent;
    }

    /**
     * @return TraversableNode
     */
    public function getChild(): TraversableNode
    {
        return $this->child;
    }

    /**
     * @param int $position
     * @return void
     */
    public function setPosition(int $position)
    {
        $this->position = $position;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getNameForGraph(): string
    {
        return $this->getName() . '@' . $this->subgraphHash;
    }

    /**
     * @return array
     */
    public function getProperties(): array
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
    public function setProperty(string $propertyName, $propertyValue)
    {
        $this->properties[$propertyName] = $propertyValue;
    }

    /**
     * @return Edge|null
     */
    public function getParentEdge(): ?Edge
    {
        return $this->subgraph->getParentEdge($this->getParent());
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

    /**
     * @return string
     */
    public function getLocalIdentifier(): string
    {
        return $this->getName() ?: $this->getChild()->getIdentifier();
    }
}
