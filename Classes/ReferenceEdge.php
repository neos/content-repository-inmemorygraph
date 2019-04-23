<?php
namespace Neos\ContentRepository\InMemoryGraph;

/*
 * This file is part of the Neos.ContentRepository.InMemoryGraph package.
 */

/**
 * The ReferenceEdge domain entity
 */
final class ReferenceEdge
{
    /**
     * @var ReadOnlyNode
     */
    protected $source;

    /**
     * @var ReadOnlyNodeAggregate
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
     * @param ReadOnlyNode $source
     * @param ReadOnlyNodeAggregate $target
     * @param ContentSubgraph $subgraph
     * @param string $subgraphHash
     * @param string $position
     * @param string $name
     * @param array $properties
     */
    public function __construct(
        ReadOnlyNode $source,
        ReadOnlyNodeAggregate $target,
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
     * @return ReadOnlyNode
     */
    public function getSource(): ReadOnlyNode
    {
        return $this->source;
    }

    /**
     * @return ReadOnlyNodeAggregate
     */
    public function getTarget(): ReadOnlyNodeAggregate
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
