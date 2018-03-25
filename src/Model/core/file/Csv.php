<?php

namespace App\Model\core\file;

use App\Model\core\AbstractModel;

class Csv extends AbstractModel
{

    protected $_lineLength = 0;
    protected $_lineCount = 0;
    protected $_delimiter = ',';
    protected $_enclosure = '"';


    /**
     * Set max file line length
     *
     * @param   int $length
     * @return  Varien_File_Csv
     */
    public function setLineLength($length)
    {
        $this->_lineLength = $length;
        return $this;
    }

    /**
     * et max file line count
     *
     * @param   string $delimiter
     * @return  Varien_File_Csv
     */
    public function setLineCount($cpt)
    {
        $this->_lineCount = $cpt;
        return $this;
    }

    /**
     * Set CSV column delimiter
     *
     * @param   string $delimiter
     * @return  Varien_File_Csv
     */
    public function setDelimiter($delimiter)
    {
        $this->_delimiter = $delimiter;
        return $this;
    }

    /**
     * Set CSV column value enclosure
     *
     * @param   string $enclosure
     * @return  Varien_File_Csv
     */
    public function setEnclosure($enclosure)
    {
        $this->_enclosure = $enclosure;
        return $this;
    }

    /**
     * Retrieve CSV file data as array
     *
     * @param   string $file
     * @return  array
     */
    public function getData($file)
    {
        ini_set("memory_limit", "4096M");
        
        $data = array();
        if (!file_exists($file)) {
            throw new \Exception('File "' . $file . '" do not exists');
        }

        $fh = fopen($file, 'r');
        $cpt = 0;
        while ($rowData = fgetcsv($fh, $this->_lineLength, $this->_delimiter, $this->_enclosure)) {
            $data[] = $rowData;

            $cpt++;
            if($this->_lineCount > 0 && $this->_lineCount == $cpt) {
                break;
            }
        }
        fclose($fh);
        return $data;
    }

    /**
     * Retrieve CSV file data as pairs
     *
     * @param   string $file
     * @param   int $keyIndex
     * @param   int $valueIndex
     * @return  array
     */
    public function getDataPairs($file, $keyIndex = 0, $valueIndex = 1)
    {
        $data = array();
        $csvData = $this->getData($file);
        foreach ($csvData as $rowData) {
            if (isset($rowData[$keyIndex])) {
                $data[$rowData[$keyIndex]] = isset($rowData[$valueIndex]) ? $rowData[$valueIndex] : null;
            }
        }
        return $data;
    }

    /**
     * Saving data row array into file
     *
     * @param   string $file
     * @param   array $data
     * @return  Varien_File_Csv
     */
    public function saveData($file, $data)
    {
        $fh = fopen($file, 'w');
        foreach ($data as $dataRow) {
            $this->fputcsv($fh, $dataRow, $this->_delimiter, $this->_enclosure);
        }
        fclose($fh);
        return $this;
    }
}