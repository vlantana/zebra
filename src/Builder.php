<?php

namespace Zebra;

class Builder
{
    const AZTEK = 'B0';
    const CODE11 = 'B1';
    const INTERLEAVED2 = 'B2';
    const CODE39 = 'B3';
    const CODE49 = 'B4';
    const PLANET = 'B5';
    const PDF417 = 'B7';
    const EAN8 = 'B8';
    const UPCE = 'B9';
    const CODE93 = 'BA';
    const CODABLOCK = 'BB';
    const CODE128 = 'BC';
    const UPS = 'BD';
    const EAN13 = 'BE';
    const MICROPDF417 = 'BF';
    const INDUSTRIAL2 = 'BI';
    const STANDARD2 = 'BJ';
    const ANSI = 'BK';
    const LOGMARS = 'BL';
    const MSI = 'BM';
    const PLESSEY = 'BP';
    const QR = 'BQ';
    const GS1 = 'BR';
    const UPC_EAN = 'BS';
    const TLC39 = 'BT';
    const UPCA = 'BU';
    const DATAMATRIX = 'BX';
    const DEFAULT = 'BY';
    const POSTAL = 'BZ';
    
    private $zpl = '';
    
    private $wrapper = [
        'start' => '^XA',
        'end' => '^XZ',
    ];
    
    /**
     * Builder constructor.
     *
     * @param int $width Label width
     * @param int $height Label height
     * @param int $top Label shift from the top
     * @param int $left Label shift from the left
     * @param int $darkness Ink darkness setting
     */
    public function __construct($width, $height, $top = 0, $left = 0, $darkness = 20)
    {
        $this->zpl = "^PW$width,LL$height^LT$top^LS$left~SD$darkness";
    }
    
    /**
     * @return string ZPL raw command
     */
    public function __toString()
    {
        return $this->wrapper['start'] . $this->zpl . $this->wrapper['end'];
    }
    
    /**
     * Pass a raw command to builder
     *
     * @param string $command
     * @param array $parameters
     *
     * @return $this
     */
    public function raw($command, $parameters = [])
    {
        $this->zpl .= "^". strtoupper($command) . implode(',', $parameters);
        
        return $this;
    }
    
    public function graphics($type, $data, $bytes_per_row, $byte_count, $field_count = null)
    {
        $field_count = $field_count ?: $byte_count;
        
        $this->zpl .= "^GF$type,$byte_count,$field_count,$bytes_per_row,$data";
        
        return $this;
    }
    
    /**
     * @param int $left Block margin from left in points
     * @param int $top Block margin from top in points
     * @param int $right_align Align to right
     *
     * @return $this
     */
    public function margin($left = 0, $top =0, $right_align = 0)
    {
        $this->zpl .= "^FO{$left},{$top},{$right_align}";
        
        return $this;
    }
    
    /**
     * @param int $width Block width from left in points
     * @param int $lines Max lines of text
     * @param int    $line_spacing Space between the lines [-9999 - 9999]
     * @param string $align Text justification [L(eft), C(enter), R(ight), J(ustified)]
     *
     * @return $this
     */
    public function block($width, $lines = 1, $line_spacing = 0, $align = 'L')
    {
        $this->zpl .= "^FB{$width},{$lines},{$line_spacing},{$align}";
        
        return $this;
    }
    
    /**
     * @param string $code Barcode code
     * @param string $type Barcode type
     * @param int $height Barcode height
     * @param int $line_width Line width
     * @param string $orientation Orientation N(ormal)/R(otated 90)/I(inverted 180)/B(ottom up 270)
     * @param string $label Print label Y/N
     * @param string $label_above Positiion label above code Y/N
     * @param int $pad_left Fixed code string length. Avoid to disable.
     * @param string $pad_string Pad string
     *
     * @return $this
     */
    public function barcode($code, $type, $height = 50, $line_width = 3, $orientation = 'N', $label = 'Y', $label_above = 'N', $pad_left = 0, $pad_string = '0')
    {
        $this->zpl .= "^BY{$line_width}^{$type}{$orientation},{$height},{$label},{$label_above}";
        
        if ($pad_left) {
            $code = str_pad($code, $pad_left, $pad_string, STR_PAD_LEFT);
        }
        
        $this->text($code)->end();
        
        return $this;
    }
    
    /**
     * @param string $text
     *
     * @return $this
     */
    public function text($text)
    {
        $text = iconv('UTF-8', "ISO-8859-1//TRANSLIT", $text);
        $this->zpl .= "^FD{$text}";
        
        return $this;
    }
    
    /**
     * @param string $type Font type (0-Z)
     * @param int $height Font height (multiply by 10)
     * @param int $width Font width (multiply by 10)
     *
     * @return $this
     */
    public function font($type = 'D', $height = null, $width = null)
    {
        $this->zpl .= "^A$type,$height,$width";
        
        return $this;
    }
    
    /**
     * @param int $width Square width
     * @param int $height Square height (line count)
     * @param int $line_thickness Square line thickness
     *
     * @return $this
     */
    public function square($width, $height, $line_thickness = 3)
    {
        $this->zpl .= "^GB{$width},{$height},$line_thickness";
        
        return $this;
    }
    
    /**
     * Denote the end of the field definition
     *
     * @return $this
     */
    public function end()
    {
        $this->zpl .= "^FS";
        
        return $this;
    }
    
}