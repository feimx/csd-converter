<?php

namespace FeiMx\Csd\Strategies;

use UnexpectedValueException;

class Cer implements StrategyInterface
{
    public $file;

    public $filename;

    public $content;

    public $pem_content;

    /**
     * Create a new Cer Instance.
     */
    public function __construct(string $file)
    {
        $this->file = $file;
        $filename = explode('/', $file);
        $this->filename = end($filename);
        $this->content = $this->getContentIsNotEmpty($file);
        $this->pem_content = $this->convertToPemIfIsNotConverted();
    }

    public function getContentIsNotEmpty(): string
    {
        if ('' !== $content = (string) file_get_contents($this->file)) {
            return  $content;
        }

        throw new UnexpectedValueException("Cannot read the certificate file {$this->file} or is empty");
    }

    public function convertToPemIfIsNotConverted(): string
    {
        $pemContent = $this->content;
        if (0 !== strpos($this->content, '-----BEGIN CERTIFICATE-----')) {
            $pemContent = $this->convertToPem($this->content);
        }

        return $this->pem_content = $pemContent;
    }

    public function convertToPem($content): string
    {
        return '-----BEGIN CERTIFICATE-----'.PHP_EOL
            .chunk_split(base64_encode($content), 64, PHP_EOL)
            .'-----END CERTIFICATE-----'.PHP_EOL;
    }

    public function save(string $path, string $filename = null)
    {
        if (null !== $filename) {
            $filename = preg_replace('/.key|.cer/', '', $filename);
            $filename = "${filename}.cer";
        } else {
            $filename = $this->filename;
        }

        file_put_contents("{$path}{$filename}", $this->content);
        file_put_contents("{$path}{$filename}.pem", $this->pem_content);
    }
}
