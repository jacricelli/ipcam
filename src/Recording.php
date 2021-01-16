<?php
declare(strict_types=1);

namespace IPCam;

use DateTimeInterface;

/**
 * Recording
 */
class Recording
{
    // phpcs:disable
    /**
     * Constructor
     *
     * @param string $name Nombre del archivo
     * @param int $size Tamaño del archivo
     * @param \DateTimeInterface $start Fecha de comienzo
     * @param \DateTimeInterface $end Fecha de finalización
     */
    public function __construct(
        private string $name,
        private int $size,
        private DateTimeInterface $start,
        private DateTimeInterface $end
    ) {
    }
    // phpcs:enable

    /**
     * Obtiene el nombre del archivo
     *
     * @return string
     */
    public function getFileName(): string
    {
        return $this->name;
    }

    /**
     * Obtiene el tamaño del archivo
     *
     * @return int
     */
    public function getFileSize(): int
    {
        return $this->size;
    }

    /**
     * Obtiene la fecha de comienzo de la grabación
     *
     * @return \DateTimeInterface
     */
    public function getStartDate(): DateTimeInterface
    {
        return $this->start;
    }

    /**
     * Obtiene la fecha de finalización de la grabación
     *
     * @return \DateTimeInterface
     */
    public function getEndDate(): DateTimeInterface
    {
        return $this->end;
    }
}
