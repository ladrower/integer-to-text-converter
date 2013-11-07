<?php namespace Ladrower\Utils;

/**
 * Class Converter
 * Integer to human readable text converter
 * @link https://github.com/ladrower/integer-to-text-converter
 */
class Converter
{
    private $_availableLang = array( 'en' => 0, 'ru' => 1 );

    private $_language = 0;

    /**
     * Signs array
     * @var $_signsMap array( signName => array( languageCode => signTranslated ) );
     */
    private $_signsMap = array(
        'minus' => array( 0 => 'minus', 1 => 'минус' ),
        'plus' 	=> array( 0 => 'plus', 1 => 'плюс' )
    );

    /**
     * Units array
     * Contains male|female values
     * @var $_unitsMap array( languageCode => array( unitMaleValue, unitFemaleValue ) );
     */
    private $_unitsMap = array(
        0 => array(
            array( 'zero', 'zero'),
            array( 'one', 'one') ,
            array( 'two', 'two'),
            array( 'three', 'three'),
            array( 'four', 'four'),
            array( 'five', 'five'),
            array( 'six', 'six'),
            array( 'seven', 'seven'),
            array( 'eight', 'eight'),
            array( 'nine', 'nine')
        ),
        1 => array(
            array( 'ноль', 'ноль'),
            array( 'один', 'одна' ),
            array( 'два', 'две'),
            array( 'три', 'три'),
            array( 'четыре', 'четыре'),
            array( 'пять', 'пять'),
            array( 'шесть', 'шесть'),
            array( 'семь', 'семь'),
            array( 'восемь', 'восемь'),
            array( 'девять', 'девять')
        )
    );

    private $_teensMap = array(
        0 => array( 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen' ),
        1 => array( 'одиннадцать', 'двенадцать', 'тринадцать', 'четырнадцать', 'пятнадцать', 'шестнадцать', 'семнадцать', 'восемнадцать', 'девятнадцать' )
    );

    private $_tensMap = array(
        0 => array( 'ten', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety' ),
        1 => array( 'десять', 'двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто' )
    );

    private $_hundredsMap = array(
        0 => array( 'one hundred', 'two hundred', 'three hundred', 'four hundred', 'five hundred', 'six hundred', 'seven hundred', 'eight hundred', 'nine hundred'),
        1 => array( 'сто', 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот' )
    );

    /**
     * Groups gender map
     * example for russian: 'тысяча' -> female, 'миллион' -> male
     * @var $_groupsGender
     */
    private $_groupsGender = array( 0 => 0, 1 => 1, 2 => 0, 3 => 0 );

    /**
     * Group names map
     * Contains word morphology forms
     * @var $_groupsMap
     */
    private $_groupsMap = array( 0 => array( 1 => array('','thousand','thousand','thousand'),
        2 => array('','million','million','million'),
        3 => array('','billion','billion','billion') ) ,
        1 => array( 1 => array( 0 => '', 1 => 'тысяча', 2 => 'тысячи', 3 => 'тысяч' ),
            2 => array( 0 => '', 1 => 'миллион', 2 => 'миллиона', 3 => 'миллионов' ),
            3 => array( 0 => '', 1 => 'миллиард', 2 => 'миллиарда', 3 => 'миллиардов' ) ) );

    private $_tensHundredsJoiner = array( 0 => 'and ', 1 => '' );

    private static $_instance = null;

    public static function getInstance()
    {
        if (self::$_instance === null)
            self::$_instance = new self();
        return self::$_instance;
    }

    private function __construct()
    {

    }

    /**
     * public int setLanguage
     * @param string $lang
     * @return int (languageCode that was just set)
     */
    public function setLanguage($lang)
    {
        $lang = strtolower($lang);
        return $this->_language = ( array_key_exists( $lang, $this->_availableLang ) ) ? $this->_availableLang[$lang] : 0 ;
    }

    /**
     * public string intToText
     * @throws Exception Throws exception in case $i is out of Integer range
     * @param int $i
     * @param string|bool $lang default false
     * @return string
     */
    public function intToText($i, $lang = false)
    {
        if ($lang)
            $this->setLanguage($lang);

        if ( abs($i) > PHP_INT_MAX )
            throw new Exception('Number out of range');

        $i = (int) $i;

        $text = null;
        $sign = null;

        if ( $i == 0 )
            return $this->_unitsMap[$this->_language][0][0];

        if ( $i < 0 ) {
            $sign = $this->_signsMap['minus'][$this->_language] . " ";
            $i = -$i;
        }

        $gcount = 0;
        while ($i > 0) {
            $text = $this->_groupToWords($i%1000, $gcount) . $text;
            $i = (int) ($i / 1000);
            $gcount++;
        }

        $text = $sign . $text;

        return trim( preg_replace("'\s+'", ' ', $text) );
    }

    /**
     * private string _groupToWords
     * @param int $i (number to process)
     * @param int $group (group level)
     * @return string (current group text representation)
     */
    private function _groupToWords($i, $group)
    {
        $hundreds = (int) ($i / 100);
        $tens = (int) ( ($i - $hundreds * 100) / 10 );
        $units = $i - $hundreds * 100 - $tens * 10;

        $string = null;

        if ( $hundreds > 0 ) {
            $string .= $this->_hundredsMap[$this->_language][$hundreds-1] . " ";
            if ( $tens > 0 || $units > 0 )
                $string .= $this->_tensHundredsJoiner[$this->_language];
        }

        if ( $tens == 1 && $units > 0 ) {
            $string .= $this->_teensMap[$this->_language][$units-1] . " ";
        }
        elseif ( $tens > 0 ) {
            $string .= $this->_tensMap[$this->_language][$tens-1] . " ";

            if ( $units > 0 )
                $string .= $this->_unitsMap[$this->_language][$units][$this->_groupsGender[$group]] . " ";
        }
        elseif ( $units > 0 )
            $string .= $this->_unitsMap[$this->_language][$units][$this->_groupsGender[$group]] . " ";

        if ( $group > 0 )
            $string .= $this->_groupsMap[$this->_language][$group][$this->_getGroupIndex($hundreds, $tens, $units)] . " ";

        return $string;
    }

    /**
     * private int _getGroupIndex
     * @param int $hundreds
     * @param int $tens
     * @param int $units
     * @return int (group array index)
     */
    private function _getGroupIndex($hundreds, $tens, $units)
    {
        if ( $hundreds == 0 && $tens == 0 &&  $units == 0)
            return 0;

        if ( $tens == 0 &&  $units == 0)
            return 3;

        if ( ($tens*10 + $units) < 15 && ($tens*10 + $units) > 10)
            return 3;

        switch ($units) {
            case 1:
                return 1;
            case 2:
            case 3:
            case 4:
                return 2;
            case 0:
            case 5:
            case 6:
            case 7:
            case 8:
            case 9:
                return 3;
        }

        return 0;
    }
}


$converter = \Ladrower\Utils\Converter::getInstance();

try {
    $text = $converter->intToText(10071111, 'ru');
    echo $text;
} catch(Exception $e) {
    echo $e;
}