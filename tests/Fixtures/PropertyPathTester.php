<?php

/*
 * This file is part of the phlexible expression package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Phlexible\Component\ExpressionTests\Fixtures;

// phpcs:disable SlevomatCodingStandard.Classes.UnusedPrivateElements.UnusedProperty

/**
 * Property path tester
 */
class PropertyPathTester
{
    public $numberOne = 11;
    public $numberTwo = 9;
    private $numberThree = 8;

    public function getNumberFour(): int
    {
        return 7;
    }
}
