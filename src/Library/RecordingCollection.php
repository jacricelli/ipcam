<?php
declare(strict_types=1);

namespace App\Library;

use Countable;
use Iterator;

/**
 * RecordingCollection
 */
class RecordingCollection implements Iterator, Countable
{
    /**
     * Ítems
     *
     * @var array
     */
    private array $items;

    /**
     * Índice
     *
     * @var int
     */
    private int $index = 0;

    /**
     * Constructor
     *
     * @param \App\Library\Recording[] $items Ítems
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * @inheritDoc
     */
    public function current(): Recording
    {
        return $this->items[$this->index];
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        $this->index++;
    }

    /**
     * @inheritDoc
     */
    public function key(): int
    {
        return $this->index;
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return isset($this->items[$this->key()]);
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->index = 0;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->items);
    }
}
