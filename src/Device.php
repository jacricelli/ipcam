<?php
declare(strict_types=1);

namespace IPCam;

/**
 * Device
 */
class Device
{
    /**
     * Propiedades
     *
     * @var array|string[]
     */
    private array $properties = [
        'sdcardDetected' => 0,
        'sdcardTotalSpace' => 0,
        'sdcardFreeSpace' => 0,
        'recRuning' => 0,
        'sdCardStu' => 0,
        'rec_files_cnt' => 0,
        'rec_files_total' => 0,
        'page_maxitem' => 0
    ];

    /**
     * Constructor
     *
     * @param array $properties Propiedades
     */
    public function __construct(array $properties)
    {
        foreach ($properties as $name => $value) {
            if (isset($this->properties[$name])) {
                $this->properties[$name] = $value;
            }
        }
    }

    /**
     * Obtiene el espacio libre
     *
     * @return int
     */
    public function getFreeSpace(): int
    {
        return $this->properties['sdcardFreeSpace'];
    }

    /**
     * Obtiene el espacio total
     *
     * @return int
     */
    public function getTotalSpace(): int
    {
        return $this->properties['sdcardTotalSpace'];
    }

    /**
     * Obtiene el número de grabaciones
     *
     * @return int
     */
    public function getTotalRecordings(): int
    {
        return $this->properties['rec_files_total'];
    }

    /**
     * Obtiene el número de páginas
     *
     * @return int
     */
    public function getTotalPages(): int
    {
        return (int)ceil($this->properties['rec_files_total'] / $this->properties['page_maxitem']);
    }

    /**
     * Obtiene el número de grabaciones por página
     *
     * @return int
     */
    public function getRecordingsPerPage(): int
    {
        return $this->properties['page_maxitem'];
    }

    /**
     * Indica si la cámara se encuentra actualmente grabando
     *
     * @return bool
     */
    public function isRecording(): bool
    {
        return (bool)$this->properties['recRuning'];
    }
}
