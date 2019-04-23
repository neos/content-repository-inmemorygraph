<?php

declare(strict_types=1);

namespace Neos\ContentRepository\InMemoryGraph\ContentSubgraph;

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

    public function __construct(string $workspaceName, DimensionSpace\DimensionSpacePoint $dimensionSpacePoint)
    {
        $this->workspaceName = $workspaceName;
        $this->dimensionSpacePoint = $dimensionSpacePoint;
        $this->hash = md5(json_encode($this));
    }

    public function getWorkspaceName(): string
    {
        return $this->workspaceName;
    }

    public function getDimensionSpacePoint(): DimensionSpace\DimensionSpacePoint
    {
        return $this->dimensionSpacePoint;
    }

    public function jsonSerialize(): array
    {
        return [
            'workspaceName' => $this->workspaceName,
            'dimensionSpacePoint' => $this->dimensionSpacePoint
        ];
    }

    public function __toString(): string
    {
        return $this->hash;
    }
}
