<?php

namespace MRussell\Http\Response;

use MRussell\Http\Request\RequestInterface;

class File extends AbstractResponse
{
    /**
     * The name of the File from Response
     * @var string
     */
    protected $fileName;

    /**
     * File Path for response
     * @var string
     */
    protected $destinationPath;


    public function __construct(RequestInterface $Request,$destination = '',$filename = '') {
        if ($destination !== ''){
            $this->setDestinationPath($destination);
        }
        if ($filename !== ''){
            $this->setFileName($filename);
        }
        parent::__construct($Request);
    }

    /**
     * @inheritdoc
     * Extract Filename from Headers and Set Default Destination Path if not set
     * Write response to File
     */
    public function extract()
    {
        if (parent::extract()){
            if (empty($this->destinationPath)){
                $this->setDestinationPath($this->getDefaultDestinationPath());
            }
            if (empty($this->fileName)) {
                $this->extractFileName();
            }
            if ($this->writeFile()){
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Get the default Destination Path to Store Files retrieved via Curl
     * @return string
     */
    protected function getDefaultDestinationPath(){
        return sys_get_temp_dir().'/CurlFiles';
    }

    /**
     * Configure the Destination path to store the File response
     * @param null $destination
     * @return $this
     */
    public function setDestinationPath($destination)
    {
        $this->destinationPath = $destination;
        return $this;
    }

    /**
     * Extract the filename from the Headers, and store it in filename property
     */
    protected function extractFileName(){
        foreach (explode("\r\n", $this->headers) as $header)
        {
            if (strpos($header, 'filename') !== false && strpos($header, 'Content-Disposition') !== false)
            {
                $fileName = substr($header, (strpos($header, "=")+1));
                $this->setFileName($fileName);
                break;
            }
        }
    }

    /**
     * Set the Filename for response to be saved to
     * @param $fileName
     * @return $this
     */
    public function setFileName($fileName)
    {
        $fileName = str_replace(" ", "_", $fileName);
        $fileName = preg_replace("([^\w\s\d\-\_~\'\[\]\(\).])", '', $fileName);
        $fileName = preg_replace("([\.]{2,})", '', $fileName);
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * Return the filename found in response
     * @return mixed
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Write the downloaded file
     * @return string|boolean - False if not written
     */
    public function writeFile()
    {
        if (!empty($this->fileName)) {
            if (!file_exists($this->destinationPath)) {
                mkdir($this->destinationPath, 0777);
            }
            $file = $this->file();
            $fileHandle = fopen($file, 'w+');
            fwrite($fileHandle, $this->body);
            fclose($fileHandle);
            return $file;
        } else {
            return FALSE;
        }
    }

    /**
     * Return the full File path, where Response was stored
     * @return string
     */
    public function file()
    {
        return rtrim($this->destinationPath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$this->fileName;
    }
}
