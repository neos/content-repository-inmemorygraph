<?php
namespace Neos\ContentRepository\InMemoryGraph\ContentSubgraph;

/*
 * This file is part of the Neos.ContentRepository.InMemoryGraph package.
 */

use Neos\ContentRepository\InMemoryGraph\NodeAggregate\Node;
use Neos\ContentRepository\InMemoryGraph\NodeAggregate\NodeAggregate;

/**
 * The reference relation domain entity
 */
final class ReferenceRelation
{
    /**
     * @var Node
     */
    protected $source;

    /**
     * @var NodeAggregate
     */
    protected $target;

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
     * ReferenceEdge constructor.
     *
     * @param Node $source
     * @param NodeAggregate $target
     * @param ContentSubgraph $subgraph
     * @param string $subgraphHash
     * @param string $position
     * @param string $name
     * @param array $properties
     */
    public function __construct(
        Node $source,
        NodeAggregate $target,
        int $position = 0,
        string $name = null,
        array $properties = []
    ) {
        $this->source = $source;
        $this->target = $target;
        $this->position = $position;
        $this->name = $name;
        $this->properties = $properties;
    }

    /**
     * @return Node
     */
    public function getSource(): Node
    {
        return $this->source;
    }

    /**
     * @return NodeAggregate
     */
    public function getTarget(): NodeAggregate
    {
        return $this->target;
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
}
