<?php

declare(strict_types=1);

namespace Neos\ContentRepository\InMemoryGraph;

/*
 * This file is part of the Neos.ContentRepository.InMemoryGraph package.
 */

use Neos\ContentRepository\DimensionSpace\DimensionSpace;

/**
 * The content subgraph identifier value object
 */
final class ContentSubgraphIdentifier implements \JsonSerializable
{
    /**
     * @var string
     */
    protected $workspaceName;

    /**
     * @var DimensionSpace\DimensionSpacePoint
     */
    protected $dimensionSpacePoint;

    /**
     * @var string
     */
    protected $hash;

    /**
     * @param string $workspaceName
     * @param DimensionSpace\DimensionSpacePoint $dimensionSpacePoint
     */
    public function __construct(string $workspaceName, DimensionSpace\DimensionSpacePoint $dimensionSpacePoint)
    {
        $this->workspaceName = $workspaceName;
        $this->dimensionSpacePoint = $dimensionSpacePoint;

        $this->hash = md5($this->dimensionSpacePoint->getHash() . '@' . $this->workspaceName);
    }

    /**
     * @return string
     */
    public function getWorkspaceName(): string
    {
        return $this->workspaceName;
    }

    /**
     * @return DimensionSpace\DimensionSpacePoint
     */
    public function getDimensionSpacePoint(): DimensionSpace\DimensionSpacePoint
    {
        return $this->dimensionSpacePoint;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'workspaceName' => $this->workspaceName,
            'dimensionSpacePoint' => $this->dimensionSpacePoint
        ];
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->hash;
    }
}
