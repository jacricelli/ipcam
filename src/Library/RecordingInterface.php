<?php
declare(strict_types=1);

namespace App\Library;

/**
 * RecordingInterface
 */
interface RecordingInterface
{
    /**
     * Obtiene el nombre del archivo
     *
     * @return string
     */
    public function getFilename(): string;

    /**
     * Obtiene el tamaño del archivo
     *
     * @return int
     */
    public function getFileSize(): int;

    /**
     * Obtiene la fecha de comienzo de la grabación
     *
     * @return \DateTimeInterface
     */
    public function getStartDate(): \DateTimeInterface;

    /**
     * Obtiene la fecha de finalización de la grabación
     *
     * @return \DateTimeInterface
     */
    public function getEndDate(): \DateTimeInterface;
}
