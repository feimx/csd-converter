<?php

namespace FeiMx\Csd\Strategies;

use InvalidArgumentException;
use UnexpectedValueException;

class Key implements StrategyInterface
{
    public $file;

    public $filename;

    public $password;

    public $content;

    public $pem_content;

    public $enc_content;

    /**
     * Create a new Key Instance.
     */
    public function __construct(string $file, string $password)
    {
        $this->file = $file;
        $filename = explode('/', $file);
        $this->filename = end($filename);
        $this->password = $password;
        $this->content = $this->getContentIsNotEmpty($file);
        $this->pem_content = $this->convertToPemIfIsNotConverted();
    }

    public function getContentIsNotEmpty(): string
    {
        if ('' !== $content = (string) file_get_contents($this->file)) {
            return  $content;
        }

        throw new UnexpectedValueException("Cannot read the key file {$this->file} or is empty");
    }

    public function convertToPemIfIsNotConverted(): string
    {
        $pemContent = $this->content;
        if (
            0 !== strpos($pemContent, '-----BEGIN PRIVATE KEY-----')
            || 0 !== strpos($pemContent, '-----BEGIN RSA PRIVATE KEY-----')
        ) {
            $pemContent = $this->convertToPem($pemContent);
        }

        return $this->pem_content = $pemContent;
    }

    public function convertToPem($content): string
    {
        $pem = shell_exec("openssl pkcs8 -inform DER -in {$this->file} -passin pass:{$this->password}");
        if (null === $pem) {
            throw new InvalidArgumentException("We can open {$this->file}, maybe the password is invalid?");
        }

        return $pem;
    }

    public function encrypt(string $pemfile, string $password)
    {
        $file = str_replace('.key', '.key.pem', $this->file);
        $enc2 = shell_exec("openssl rsa -in {$pemfile} -des3 -passout pass:{$password}");

        if (null === $enc2) {
            throw new InvalidArgumentException("We can open {$this->pemfile}");
        }

        $encFile = str_replace('.key.pem', '.enc.key', $pemfile);

        $this->enc_content = $enc2;

        file_put_contents($encFile, $enc2);

        return $enc2;
    }

    public function save(string $path, string $filename = null)
    {
        if (null !== $filename) {
            $filename = preg_replace('/.key|.cer/', '', $filename);
            $filename = "${filename}.key";
        } else {
            $filename = $this->filename;
        }
        file_put_contents("{$path}{$filename}", $this->content);
        file_put_contents("{$path}{$filename}.pem", $this->pem_content);
    }
}
