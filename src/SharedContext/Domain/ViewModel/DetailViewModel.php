<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Domain\ViewModel;

/**
 * @template T of object
 */
abstract class DetailViewModel
{
    /** @var T */
    public readonly object $object;

    /**
     * @param T $object
     */
    public function __construct(object $object)
    {
        $this->object = $object;
    }
}