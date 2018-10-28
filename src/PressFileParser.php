<?php

namespace vicgonvt\LaraPress;

use Illuminate\Support\Facades\File;

class PressFileParser
{
    /**
     * @var
     */
    private $splitFile;

    /**
     * @var
     */
    private $parsedData;

    /**
     * PressFileParser constructor.
     *
     * @param $filename
     */
    public function __construct($filename)
    {
        $this->filename = $filename;

        $this->splitFile();

        $this->explodeData();
        
        $this->processFields();
    }

    /**
     * Get the underlying parsed data.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->parsedData;
    }

    /**
     * Takes each field and tries to find a class with matching name. If found, it will try to call
     * the process() method on it. Any other fields that don't have matching classes get sent to
     * a catch all class of Extra, where they will be JSON Encoded into the 'extra' field.
     */
    protected function processFields()
    {
        foreach ($this->parsedData as $fieldType => $fieldData) {
            $class = 'vicgonvt\LaraPress\Field\\' . ucfirst(camel_case($fieldType));

            if ( ! class_exists($class) && ! method_exists($class, 'process')) {
                $class = 'vicgonvt\LaraPress\Field\Extra';
            }

            $this->parsedData = array_merge(
                $this->parsedData,
                $class::process($fieldType, $fieldData, $this->parsedData)
            );
        }
    }

    /**
     * It separates the head on each new line, trims it and saves it to parsedData variable.
     */
    protected function explodeData()
    {
        foreach (explode("\n", trim($this->splitFile[1])) as $fieldString) {
            $fieldString = trim($fieldString);

            preg_match('/(.*?)\:(.*)/', $fieldString, $fieldArray);

            if (isset($fieldArray[1])) {
                $this->parsedData[trim($fieldArray[1])] = trim($fieldArray[2]);
            }
        }

        $this->parsedData['body'] = trim($this->splitFile[2]);
    }

    /**
     * It separates the head from the body for further manipulation.
     */
    protected function splitFile()
    {
        preg_match(
            '/^\-{3}(.*?)\-{3}(.*)/s',
            File::exists($this->filename) ? File::get($this->filename) : $this->filename,
            $this->splitFile
        );
    }
}