<?php

declare(strict_types=1);

namespace Neos\ContentRepository\InMemoryGraph;

/*
 * This file is part of the Neos.ContentRepository.InMemoryGraph package.
 */

/**
 * The IsAutoCreated specification for nodes
 */
final class IsAutoCreated
{
    /**
     * @param TraversableNode $node
     * @return bool
     */
    public function isSatisfiedBy(TraversableNode $node): bool
    {
        $parent = $node->getParent();
        if ($parent === null) {
            return false;
        }

        return array_key_exists($node->getName(), $parent->getNodeType()->getAutoCreatedChildNodes());
    }
}
