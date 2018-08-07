<?php

namespace FeiMx\Csd;

use Carbon\Carbon;
use FeiMx\Csd\Strategies\Cer;
use FeiMx\Csd\Strategies\Key;
use InvalidArgumentException;
use RuntimeException;

class CsdConverter
{
    const VALID = 1;

    const INVALID = 0;

    const EXPIRED = -1;

    public $cerStrategy;

    public $keyStrategy;

    public $data = [];

    public $version;

    public $serial_number;

    public $tax_id;

    public $name;

    public $valid_from;

    public $valid_to;

    /**
     * Create a new CsdConverter Instance.
     */
    public function __construct(string $cer_file, string $key_file, string $password)
    {
        $this->assertValidFile($cer_file);
        $this->cerStrategy = new Cer($cer_file);

        $this->assertValidFile($key_file);
        $this->keyStrategy = new Key($key_file, $password);

        $this->setData();
    }

    protected function assertValidFile(string $cer_file)
    {
        if (!file_exists($cer_file) || !is_readable($cer_file)) {
            throw new InvalidArgumentException("File {$cer_file} does not exists or is not readable");
        }
    }

    protected function setData()
    {
        $this->data = openssl_x509_parse($this->cerStrategy->pem_content, true);
        if (!is_array($this->data)) {
            throw new RuntimeException("Cannot parse the certificate file {$this->cerStrategy->filename}");
        }

        $this->mapDataToProps($this->data);
    }

    public function isValidCsd()
    {
        return isset($this->data['subject']['OU']) && !empty($this->data['subject']['OU']);
    }

    public function getStatus()
    {
        $now = Carbon::now();
        if ($now > $this->valid_to) {
            return self::EXPIRED;
        }

        if ($now < $this->valid_from) {
            return self::INVALID;
        }

        if ($now > $this->valid_from && $now < $this->valid_to) {
            return self::VALID;
        }
    }

    protected function mapDataToProps($data)
    {
        $this->version = $data['version'];

        $this->serial_number = $this->paserSeriaNumberHex($data['serialNumberHex']);

        $this->tax_id = preg_split('/\s/', $data['subject']['x500UniqueIdentifier'])[0];

        $this->name = $data['subject']['name'];

        $this->valid_from = Carbon::createFromTimestamp($data['validFrom_time_t']);

        $this->valid_to = Carbon::createFromTimestamp($data['validTo_time_t']);
    }

    public function paserSeriaNumberHex($serialNumber)
    {
        $parsedSerialNumber = '';
        for ($i = 0; $i < strlen($serialNumber); ++$i) {
            if (0 != $i % 2) {
                $parsedSerialNumber .= substr($serialNumber, $i, 1);
            }
        }

        return $parsedSerialNumber;
    }

    public function save(string $path, string $filename = null)
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $this->cerStrategy->save($path, $filename);

        $this->keyStrategy->save($path, $filename);
    }

    public function encryptKey(string $pemfile, string $password)
    {
        return $this->keyStrategy->encrypt($pemfile, $password);
    }
}
