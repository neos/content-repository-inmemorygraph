<?php
namespace Neos\ContentRepository\InMemoryGraph;

/*
 * This file is part of the Neos.ContentRepository.InMemoryGraph package.
 */

/**
 * The IsAutoCreated specification for nodes
 */
final class IsAutoCreated
{
    public function isSatisfiedBy(TraversableNode $node): bool
    {
        $parent = $node->getParent();
        if ($parent === null) {
            return false;
        }

        return array_key_exists($node->getName(), $parent->getNodeType()->getAutoCreatedChildNodes());
    }
}
