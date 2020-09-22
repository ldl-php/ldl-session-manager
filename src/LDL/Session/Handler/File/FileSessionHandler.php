<?php declare(strict_types=1);

namespace LDL\Session\Handler\File;

use LDL\Session\Handler\SessionHandlerInterface;
use LDL\Session\Handler\Exception\SessionHandlerWriteException;
use LDL\Session\Handler\Exception\SessionHandlerReadException;

class FileSessionHandler implements SessionHandlerInterface
{
    /**
     * @var string
     */
    private $savePath;

    /**
     * @param string $savePath
     * @param string $sessionName
     * @return bool
     * @throws SessionHandlerWriteException
     */
    function open($savePath, $sessionName) : bool
    {
        $this->savePath = $savePath;

        $exists = file_exists($this->savePath);

        if($exists && !is_writable($this->savePath)){
            $msg = "Session path: \"{$this->savePath}\" is not writable";
            throw new SessionHandlerWriteException($msg);
        }

        $check = !$exists && !@mkdir($this->savePath, 0777) && !is_dir($this->savePath);

        if(!$check){
            $msg = sprintf(
                'In session handler: %s, could not create save path directory "%s"',
                __CLASS__,
                $this->savePath
            );

            throw new SessionHandlerWriteException($msg);
        }

        return true;
    }

    public function close() :bool
    {
        return true;
    }

    public function read($id) : string
    {
        $file = implode(\DIRECTORY_SEPARATOR, [$this->savePath,"sess_$id"]);

        if(!file_exists($file)){
            return '';
        }

        if(!is_readable($file)){
            $msg = sprintf('%s: Could not read from file "%s"', __CLASS__, $file);
            throw new SessionHandlerReadException($msg);
        }

        return (string)@file_get_contents("$this->savePath/sess_$id");
    }

    public function write($id, $data) :bool
    {
        $file = implode(\DIRECTORY_SEPARATOR, [$this->savePath,"sess_$id"]);

        if(file_exists($file) && !is_writable($file)){
            $msg = sprintf('%s: Could not write to file "%s"', __CLASS__, $file);

            throw new SessionHandlerWriteException($msg);
        }

        return file_put_contents("$this->savePath/sess_$id", $data) === false ? false : true;
    }

    public function destroy($id)
    {
        $file = "$this->savePath/sess_$id";

        if(!is_writable($file)) {
            $msg = sprintf('%s: Could not unlink file "%s"', __CLASS__, $file);
            throw new SessionHandlerWriteException($msg);
        }

        if (file_exists($file)) {
            unlink($file);
        }

        return true;
    }

    public function gc($maxlifetime)
    {
        foreach (glob("$this->savePath/sess_*") as $file) {
            if (filemtime($file) + $maxlifetime < time() && file_exists($file) && is_writable($file)) {
                unlink($file);
            }
        }

        return true;
    }
}
