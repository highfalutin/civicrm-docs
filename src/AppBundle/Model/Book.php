<?php

namespace AppBundle\Model;

use Symfony\Component\Yaml\Parser;
use AppBundle\Utils\StringTools;

class Book {

  /**
   * @var string The reference identifier for this book, taken from
   *             the name of the book's .yml file.
   */
  public $slug;

  /**
   * @var string The title of the book, taken from the "name"
   */
  public $name;

  /**
   * @var string Short phrase describing the book, taken from the
   */
  public $description;

  /**
   * @var array An array (without keys of Language objects to
   */
  public $languages;

  /**
   * @var int Used to sort books
   */
  public $weight;

  /**
   * Creates a book based on a yaml conf file
   *
   * @param string $confFile The path to the yaml configuration file which
   *                         defines the attributes of the book.
   */
  public function __construct($confFile) {
    $parser = new Parser();
    $yaml = $parser->parse(file_get_contents($confFile));
    $this->slug = StringTools::urlSafe(basename($confFile, '.yml'));
    $this->name = $yaml['name'];
    $this->weight = isset($yaml['weight']) ? $yaml['weight'] : 0;
    $this->description = isset($yaml['description']) ? $yaml['description'] : "";
    foreach ($yaml['langs'] as $code => $languageData) {
      $this->languages[] = new Language($code, $languageData);
    }
  }

  /**
   * @return bool True when the book contains multiple languages.
   */
  public function isMultiLanguage() {
    return count($this->languages) > 1;
  }

  /**
   * Selects one of the languages within the book
   *
   * @param string $code Two letter language code to describe the language
   *
   * @return Language
   */
  public function getLanguageByCode($code) {
    $chosen = NULL;
    foreach ($this->languages as $language) {
      if ($language->code == $code) {
        $chosen = $language;
        break;
      }
    }
    return $chosen;
  }

  /**
   * Check this book for any problems in the way it's defined.
   *
   * If validation succeeds, this function returns nothing
   *
   * If validation fails, this function throws an exception.
   */
  public function validate() {
    $illegalBookSlugs = array(
      "bundles",
      "static",
    );
    if (in_array($this->slug, $illegalBookSlugs)) {
      throw new \Exception("Book slug is '{$this->slug}' but this word is "
          . "reserved in order to maintain functionality within this app. "
          . "Reserved words are: " . implode(", ", $illegalBookSlugs));
    }
  }

}