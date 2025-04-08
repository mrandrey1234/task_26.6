<?php
class HTMLTagIterator implements Iterator {
    /**
     * @var DOMNodeList Список найденных элементов
     */
    private $nodes;

    /**
     * Текущая позиция итератора
     * @var int
     */
    private $position = 0;

    /**
     * Конструктор принимает объект DOMDocument и имя тега для итерации.
     *
     * @param DOMDocument $dom
     * @param string      $tagName
     */
    public function __construct(DOMDocument $dom, $tagName) {
        $this->nodes = $dom->getElementsByTagName($tagName);
        $this->rewind();
    }

    /**
     * Сброс итератора на первую позицию.
     */
    #[\ReturnTypeWillChange]
    public function rewind() {
        $this->position = 0;
    }

    /**
     * Возвращает текущий элемент (DOMNode).
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function current() {
        return $this->nodes->item($this->position);
    }

    /**
     * Возвращает ключ текущего элемента.
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function key() {
        return $this->position;
    }

    /**
     * Переходит к следующему элементу.
     */
    #[\ReturnTypeWillChange]
    public function next() {
        ++$this->position;
    }

    /**
     * Проверяет, существует ли элемент с текущим ключом.
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function valid() {
        return $this->position < $this->nodes->length;
    }
}


$htmlFile = 'input.html';

$doc = new DOMDocument();
libxml_use_internal_errors(true);
if (!$doc->loadHTMLFile($htmlFile)) {
    die("Не удалось загрузить HTML из файла: $htmlFile");
}
libxml_clear_errors();

$title = $description = $keywords = '';


$titleIterator = new HTMLTagIterator($doc, 'title');
foreach ($titleIterator as $node) {
    $title = $node->nodeValue;
    $node->parentNode->removeChild($node);
    break;
}

$metaIterator = new HTMLTagIterator($doc, 'meta');
$nodesToRemove = [];
foreach ($metaIterator as $meta) {
    $nameAttr = strtolower($meta->getAttribute('name'));
    if ($nameAttr === 'description') {
        $description = $meta->getAttribute('content');
        $nodesToRemove[] = $meta;
    } elseif ($nameAttr === 'keywords') {
        $keywords = $meta->getAttribute('content');
        $nodesToRemove[] = $meta;
    }
}

foreach ($nodesToRemove as $node) {
    $node->parentNode->removeChild($node);
}

echo "Title: " . $title . "\n";
echo "Description: " . $description . "\n";
echo "Keywords: " . $keywords . "\n\n";

echo "Modified HTML:\n";
echo $doc->saveHTML();
?>
