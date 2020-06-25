<?php

declare(strict_types=1);

use CIInfo\Test\TaggedRepositoryTestCase;

class GetAnnotatedTagStateTest extends TaggedRepositoryTestCase
{
    /**
     * {@inheritdoc}
     *
     * @see \CIInfo\Test\TaggedRepositoryTestCase::isAnnotatedTag()
     */
    protected static function isAnnotatedTag(): bool
    {
        return true;
    }
}
