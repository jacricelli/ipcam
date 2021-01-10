<?php
declare(strict_types=1);

namespace App\Library;

use DateTimeInterface;

/**
 * Recording
 */
class Recording implements RecordingInterface
{
    /**
     * Nombre del archivo
     *
     * @var string
     */
    private string $name;

    /**
     * Tamaño del archivo
     *
     * @var int
     */
    private int $size;

    /**
     * Comienzo de la grabación
     *
     * @var \DateTimeInterface
     */
    private DateTimeInterface $start;

    /**
     * Fecha de finalización de la grabación
     *
     * @var \DateTimeInterface
     */
    private DateTimeInterface $end;

    /**
     * Constructor
     *
     * @param string $name Nombre del archivo
     * @param int $size Tamaño del archivo
     * @param \DateTimeInterface $start Fecha de comienzo
     * @param \DateTimeInterface $end Fecha de finalización
     */
    public function __construct(
        string $name,
        int $size,
        DateTimeInterface $start,
        DateTimeInterface $end
    ) {
        $this->name = $name;
        $this->size = $size;
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * @inheritDoc
     */
    public function getFilename(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function getFileSize(): int
    {
        return $this->size;
    }

    /**
     * @inheritDoc
     */
    public function getStartDate(): DateTimeInterface
    {
        return $this->start;
    }

    /**
     * @inheritDoc
     */
    public function getEndDate(): DateTimeInterface
    {
        return $this->end;
    }
}
