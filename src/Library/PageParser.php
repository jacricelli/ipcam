<?php
/** @noinspection RegExpRedundantEscape */
declare(strict_types=1);

namespace App\Library;

use Cake\Utility\Inflector;
use DOMDocument;
use DOMXPath;
use stdClass;

/**
 * PageParser
 */
class PageParser
{
    // @codingStandardsIgnoreStart
    /**
     * Expresión regular para extraer las propiedades
     */
    private const PROPERTIES_REGEXP = <<<REGEXP
/var (sdcardDetected|sdcardTotalSpace|sdcardFreeSpace|recRuning|sdCardStu|rec_files_cnt|rec_files_total|page_maxitem|page_curr) = (\d+);/
REGEXP;

    /**
     * Expresión regular para extraer las grabaciones
     */
    private const RECORDINGS_REGEXP = <<<REGEXP
/^rec_files(_size|_recstart|_recend)?\[\d{1,2}\]+\s\=('.+'|\d+);/m
REGEXP;
    // @codingStandardsIgnoreEnd

    /**
     * Propiedades
     *
     * @var \stdClass|null
     */
    private ?stdClass $properties;

    /**
     * Grabaciones
     *
     * @var \App\Library\RecordingCollection|null
     */
    private ?RecordingCollection $recordings;

    /**
     * Constructor
     *
     * @param string $html Documento HTML
     * @throws \Exception
     */
    public function __construct(string $html)
    {
        $this->parse($html);
    }

    /**
     * Obtiene las propiedades
     *
     * @return \stdClass|null
     */
    public function getProperties(): ?stdClass
    {
        return $this->properties ?? null;
    }

    /**
     * Obtiene las grabaciones
     *
     * @return \App\Library\RecordingCollection|null
     */
    public function getRecordings(): ?RecordingCollection
    {
        return $this->recordings ?? null;
    }

    /**
     * Parsea el contenido HTML de una página
     *
     * @param string $html Documento HTML
     * @return void
     * @throws \Exception
     */
    private function parse(string $html): void
    {
        $doc = new DOMDocument();
        $doc->loadHTML($html, LIBXML_NOWARNING | LIBXML_NOERROR);
        $path = new DOMXPath($doc);
        $scripts = $path->query('//head//script[not(@src)]');
        $content = $scripts[0]->nodeValue;

        $this->properties = $this->extractProperties($content);
        $this->recordings = $this->extractRecordings($content);
    }

    /**
     * Extrae propiedades del dispositivo y de la página actual
     *
     * @param string $content Contenido
     * @return \stdClass
     */
    private function extractProperties(string $content): stdClass
    {
        preg_match_all(self::PROPERTIES_REGEXP, $content, $matches);

        $properties = [];
        foreach ($matches[1] as $index => $name) {
            $properties[Inflector::camelize($name)] = (int)$matches[2][$index];
        }

        return (object)$properties;
    }

    /**
     * Extrae los datos de las grabaciones
     *
     * @param string $content Contenido
     * @return \App\Library\RecordingCollection
     * @throws \Exception
     */
    private function extractRecordings(string $content): RecordingCollection
    {
        preg_match_all(self::RECORDINGS_REGEXP, $content, $matches);

        $recordings = [];
        foreach (array_chunk($matches[2], 4) as $chunk) {
            $recordings[] = new Recording(
                trim($chunk[0], "'"),
                (int)$chunk[1],
                new \DateTimeImmutable(trim($chunk[2], "'")),
                new \DateTimeImmutable(trim($chunk[3], "'"))
            );
        }

        return new RecordingCollection($recordings);
    }
}
