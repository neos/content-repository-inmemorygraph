<?php

declare(strict_types=1);

namespace Neos\ContentRepository\InMemoryGraph\Dimension;

/*
 * This file is part of the Neos.ContentRepository.InMemoryGraph package.
 */

use Neos\ContentRepository\DimensionSpace\Dimension;
use Neos\ContentRepository\Domain as ContentRepository;
use Neos\Flow\Security;

/**
 * The legacy configuration based content dimension source including workspaces as the least important dimension
 */
class LegacyConfigurationAndWorkspaceBasedContentDimensionSource implements Dimension\ContentDimensionSourceInterface
{
    /**
     * @var array|Dimension\ContentDimension[]
     */
    protected $dimensions;

    /**
     * @var LegacyConfigurationBasedContentDimensionSource
     */
    protected $baseContentDimensionSource;

    /**
     * @var ContentRepository\Repository\WorkspaceRepository
     */
    protected $workspaceRepository;

    public function __construct(LegacyConfigurationBasedContentDimensionSource $baseContentDimensionSource, ContentRepository\Repository\WorkspaceRepository $workspaceRepository)
    {
        $this->baseContentDimensionSource = $baseContentDimensionSource;
        $this->workspaceRepository = $workspaceRepository;
    }

    protected function initializeDimensions(): void
    {
        $this->dimensions = $this->baseContentDimensionSource->getContentDimensionsOrderedByPriority();
        /** @var Dimension\ContentDimensionValueVariationEdge[] $variationEdges */
        $dimensionValues = [];
        $variationEdges = [];
        $generalizationIdentifiers = [];
        $defaultValue = null;
        foreach ($this->workspaceRepository->findAll() as $workspace) {
            /** @var ContentRepository\Model\Workspace $workspace */
            if ($workspace->getOwner()) {
                $continue = true;
                foreach ($workspace->getOwner()->getAccounts() as $account) {
                    /** @var Security\Account $account */
                    if (
                        $account->hasRole(new Security\Policy\Role('Neos.Neos:Editor')) ||
                        $account->hasRole(new Security\Policy\Role('Neos.Neos:Administrator'))
                    ) {
                        $continue = false;
                        break;
                    }
                }
                if ($continue) {
                    continue;
                }
            } else {
                if (\mb_substr($workspace->getName(), 0, 5) === 'user-') {
                    continue;
                }
            }

            /** @var ContentRepository\Model\Workspace $workspace */
            if (!isset($dimensionValues[$workspace->getName()])) {
                $dimensionValues[$workspace->getName()] = new Dimension\ContentDimensionValue(
                    $workspace->getName(),
                    new Dimension\ContentDimensionValueSpecializationDepth(count($workspace->getBaseWorkspaces()))
                );
                if ($workspace->getBaseWorkspace()) {
                    $generalizationIdentifiers[$workspace->getName()] = $workspace->getBaseWorkspace()->getName();
                } else {
                    if (!$defaultValue || $workspace->getName() === 'live') {
                        $defaultValue = $dimensionValues[$workspace->getName()];
                    }
                }
            }
        }
        foreach ($generalizationIdentifiers as $generalizationIdentifier => $specializationIdentifier) {
            $variationEdges[] = new Dimension\ContentDimensionValueVariationEdge($dimensionValues[$generalizationIdentifier], $dimensionValues[$specializationIdentifier]);
        }
        if (isset($this->dimensions['_workspace'])) {
            throw new DimensionIdentifierIsConflicting('Dimension identifier "_workspace" required for variation calculation but already occupied', 1532071326);
        }
        $this->dimensions['_workspace'] = new Dimension\ContentDimension(
            new Dimension\ContentDimensionIdentifier('_workspace'),
            $dimensionValues,
            $defaultValue,
            $variationEdges
        );
    }

    /**
     * Returns a content dimension by its identifier, if available
     *
     * @param Dimension\ContentDimensionIdentifier $dimensionIdentifier
     * @return Dimension\ContentDimension|null
     */
    public function getDimension(Dimension\ContentDimensionIdentifier $dimensionIdentifier): ?Dimension\ContentDimension
    {
        if (is_null($this->dimensions)) {
            $this->initializeDimensions();
        }
        return $this->dimensions[(string)$dimensionIdentifier] ?? null;
    }

    /**
     * Returns all available content dimensions in correct order of priority
     *
     * @return array|Dimension\ContentDimension[]
     */
    public function getContentDimensionsOrderedByPriority(): array
    {
        if (is_null($this->dimensions)) {
            $this->initializeDimensions();
        }
        return $this->dimensions;
    }
}
