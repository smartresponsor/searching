<?php

declare(strict_types=1);

namespace App\Searching;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

/**
 * Defines the kernel responsibility within the Searching component runtime and its typed boundaries.
 */
final class Kernel extends BaseKernel
{
    use MicroKernelTrait;
}
