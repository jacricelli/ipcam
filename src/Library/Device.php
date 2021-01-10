<?php
declare(strict_types=1);

namespace App\Library;

/**
 * Device
 */
class Device
{
    /**
     * Propiedades
     *
     * @var \stdClass
     */
    private \stdClass $properties;

    /**
     * Constructor
     *
     * @param \stdClass $properties Propiedades
     */
    public function __construct(\stdClass $properties)
    {
        $this->properties = $properties;
    }

    /**
     * Obtiene el espacio total
     *
     * @return int
     */
    public function getTotalSpace(): int
    {
        return $this->properties->SdcardTotalSpace ?? 0;
    }

    /**
     * Obtiene el espacio libre
     *
     * @return int
     */
    public function getFreeSpace(): int
    {
        return $this->properties->SdcardFreeSpace ?? 0;
    }

    /**
     * Obtiene el total de grabaciones
     *
     * @return int
     */
    public function getTotalRecordings(): int
    {
        return $this->properties->RecFilesTotal ?? 0;
    }

    /**
     * Obtiene la cantidad de grabaciones por página
     *
     * @return int
     */
    public function getRecordingsPerPage(): int
    {
        return $this->properties->PageMaxitem ?? 0;
    }

    /**
     * Obtiene el total de páginas
     *
     * @return int
     */
    public function getTotalPages(): int
    {
        if (isset($this->properties->RecFilesTotal, $this->properties->PageMaxitem)) {
            return (int)round($this->properties->RecFilesTotal / $this->properties->PageMaxitem);
        }

        return 0;
    }

    /**
     * Indica si la cámara se encuentra grabando
     *
     * @return bool
     */
    public function isRunning(): bool
    {
        return isset($this->properties->RecRuning) ? (bool)$this->properties->RecRuning : false;
    }
}
