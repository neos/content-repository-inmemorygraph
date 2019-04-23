<?php

declare(strict_types=1);

namespace Neos\ContentRepository\InMemoryGraph\Dimension;

/*
 * This file is part of the Neos.ContentRepository.InMemoryGraph package.
 */

use Neos\ContentRepository\DimensionSpace\Dimension;

/**
 * The legacy configuration based content dimension source
 */
class LegacyConfigurationBasedContentDimensionSource implements Dimension\ContentDimensionSourceInterface
{
    /**
     * @var array|Dimension\ContentDimension[]
     */
    protected $dimensions = [];

    /**
     * @var array
     */
    protected $rawDimensionConfiguration;

    public function __construct(array $rawDimensionConfiguration)
    {
        $this->rawDimensionConfiguration = $rawDimensionConfiguration;
    }

    protected function initializeDimensions(): void
    {
        foreach ($this->rawDimensionConfiguration as $rawContentDimensionIdentifier => $rawDimensionConfiguration) {
            $contentDimensionIdentifier = new Dimension\ContentDimensionIdentifier($rawContentDimensionIdentifier);

            if (!isset($rawDimensionConfiguration['presets']) || empty($rawDimensionConfiguration['presets'])) {
                throw new Dimension\Exception\ContentDimensionValuesAreMissing('Values for dimension "' . $rawContentDimensionIdentifier . '" are missing"', 1531737549);
            }

            $contentDimensionValues = [];
            $rawGeneralizations = [];
            foreach ($rawDimensionConfiguration['presets'] as $preset) {
                if (!is_array($preset) || !array_key_exists('values', $preset)) {
                    continue;
                }

                $rawValue = reset($preset['values']);
                if (count($preset['values']) > 1) {
                    $generalization = null;
                    foreach ($preset['values'] as $value) {
                        if (is_null($generalization)) {
                            $generalization = $value;
                        } else {
                            $rawGeneralizations[$generalization] = $value;
                        }
                    }
                }

                $specializationDepth = new Dimension\ContentDimensionValueSpecializationDepth(count($preset['values']) - 1);

                $dimensionConstraints = [];
                if (isset($preset['constraints']) && !empty($preset['constraints'])) {
                    foreach ($preset['constraints'] as $rawDimensionIdentifier => $identifierRestrictions) {
                        if (isset($identifierRestrictions['*'])) {
                            $wildcardAllowed = $identifierRestrictions['*'];
                            unset($identifierRestrictions['*']);
                        } else {
                            $wildcardAllowed = true;
                        }
                        $dimensionConstraints[$rawDimensionIdentifier] = new Dimension\ContentDimensionConstraints($wildcardAllowed, $identifierRestrictions);
                    }
                }

                $dimensionValueConfiguration = $preset;
                unset($dimensionValueConfiguration['values']);

                $contentDimensionValues[$rawValue] = new Dimension\ContentDimensionValue(
                    $rawValue,
                    $specializationDepth,
                    $dimensionConstraints,
                    $dimensionValueConfiguration
                );
            }

            if (!isset($rawDimensionConfiguration['default']) || (!isset($contentDimensionValues[$rawDimensionConfiguration['default']]))) {
                throw new Dimension\Exception\ContentDimensionDefaultValueIsMissing('Default value for dimension "' . $rawContentDimensionIdentifier . '"" is missing', 1531737067);
            }
            $defaultValue = $contentDimensionValues[$rawDimensionConfiguration['default']];

            $variationEdges = [];
            foreach ($rawGeneralizations as $specialization => $generalization) {
                $variationEdges[] = new Dimension\ContentDimensionValueVariationEdge($contentDimensionValues[$specialization], $contentDimensionValues[$generalization]);
            }

            $dimensionsConfiguration = $rawDimensionConfiguration;
            unset($dimensionsConfiguration['default']);
            unset($dimensionsConfiguration['defaultPreset']);
            unset($dimensionsConfiguration['presets']);

            $this->dimensions[$rawContentDimensionIdentifier] = new Dimension\ContentDimension(
                $contentDimensionIdentifier,
                $contentDimensionValues,
                $defaultValue,
                $variationEdges,
                $dimensionsConfiguration
            );
        }
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
