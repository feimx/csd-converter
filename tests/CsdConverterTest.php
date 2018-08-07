<?php

namespace FeiMx\Csd\Tests;

use Carbon\Carbon;
use FeiMx\Csd\CsdConverter;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class CsdConverterTest extends TestCase
{
    protected $serial_number = '20001000000300022815';

    protected $tax_id = 'LAN7008173R5';

    protected $password = '12345678a';

    protected $cer_file = __DIR__.'/fixtures/csds/LAN7008173R5.cer';

    protected $key_file = __DIR__.'/fixtures/csds/LAN7008173R5.key';

    protected $cer_pem = __DIR__.'/fixtures/csds/LAN7008173R5.cer.pem';

    protected $key_pem = __DIR__.'/fixtures/csds/LAN7008173R5.key.pem';

    protected $key_enc = __DIR__.'/fixtures/csds/LAN7008173R5.enc.key';

    protected $empty_cer = __DIR__.'/fixtures/csds/empty.cer';

    /** @test */
    public function instanceOfCsdConverter()
    {
        $csdConverter = new CsdConverter($this->cer_file, $this->key_file, $this->password);
        $this->assertInstanceOf(CsdConverter::class, $csdConverter);
    }

    /** @test */
    public function fileExistsOrIsReadable()
    {
        $this->expectException(InvalidArgumentException::class);
        $csdConverter = new CsdConverter('LAN7008173R5.cer', $this->key_file, $this->password);
    }

    /** @test */
    public function fileIsNotEmpty()
    {
        $this->expectException(UnexpectedValueException::class);
        $csdConverter = new CsdConverter($this->empty_cer, $this->key_file, $this->password);
    }

    /** @test */
    public function convertCerToPem()
    {
        $csdConverter = new CsdConverter($this->cer_file, $this->key_file, $this->password);
        $this->assertSame(
            file_get_contents($this->cer_pem),
            $csdConverter->cerStrategy->pem_content
        );
    }

    /** @test */
    public function convertCerToPemIfIsNotConverted()
    {
        $csdConverter = new CsdConverter($this->cer_pem, $this->key_file, $this->password);
        $this->assertSame(
            file_get_contents($this->cer_pem),
            $csdConverter->cerStrategy->pem_content
        );
    }

    /** @test */
    public function cerIsValidCsd()
    {
        $csdConverter = new CsdConverter($this->cer_pem, $this->key_file, $this->password);
        $this->assertTrue($csdConverter->isValidCsd());
    }

    /** @test */
    public function getSerialNumber()
    {
        $csdConverter = new CsdConverter($this->cer_pem, $this->key_file, $this->password);
        $this->assertSame($this->serial_number, $csdConverter->serial_number);
    }

    /** @test */
    public function getTaxId()
    {
        $csdConverter = new CsdConverter($this->cer_pem, $this->key_file, $this->password);
        $this->assertSame($this->tax_id, $csdConverter->tax_id);
    }

    /** @test */
    public function datesAreInstanceOfCarbon()
    {
        $csdConverter = new CsdConverter($this->cer_pem, $this->key_file, $this->password);
        $this->assertInstanceOf(Carbon::class, $csdConverter->valid_from);
        $this->assertInstanceOf(Carbon::class, $csdConverter->valid_to);
    }

    /** @test */
    public function isValid()
    {
        $csdConverter = new CsdConverter($this->cer_pem, $this->key_file, $this->password);
        $this->assertSame(CsdConverter::VALID, $csdConverter->getStatus());
    }

    /** @test */
    public function convertKeyToPemIfIsNotConverted()
    {
        $csdConverter = new CsdConverter($this->cer_pem, $this->key_file, $this->password);
        $this->assertSame(
            file_get_contents($this->key_pem),
            $csdConverter->keyStrategy->pem_content
        );
    }

    /** @test */
    public function assertSeeFilename()
    {
        $csdConverter = new CsdConverter($this->cer_file, $this->key_file, $this->password);
        $csdConverter->save(__DIR__.'/../temp/');
        $this->assertSame('LAN7008173R5.cer', $csdConverter->cerStrategy->filename);
        $this->assertSame('LAN7008173R5.key', $csdConverter->keyStrategy->filename);
    }

    /** @test */
    public function saveToAGivenPath()
    {
        $path = __DIR__.'/../temp/';
        $csdConverter = new CsdConverter($this->cer_pem, $this->key_file, $this->password);
        $csdConverter->save(__DIR__.'/../temp/');
        $this->assertFileExists("{$path}LAN7008173R5.cer");
        $this->assertFileExists("{$path}LAN7008173R5.key");
        $this->assertFileExists("{$path}LAN7008173R5.cer.pem");
        $this->assertFileExists("{$path}LAN7008173R5.key.pem");

        array_map('unlink', glob("$path/*.*"));
        rmdir($path);
    }

    /** @test */
    public function saveToAGivenPathWithFilename()
    {
        $path = __DIR__.'/../ss/';
        $csdConverter = new CsdConverter($this->cer_file, $this->key_file, $this->password);
        $csdConverter->save($path, 'yorchito.key');
        $this->assertFileExists("{$path}yorchito.cer");
        $this->assertFileExists("{$path}yorchito.key");
        $this->assertFileExists("{$path}yorchito.cer.pem");
        $this->assertFileExists("{$path}yorchito.key.pem");

        array_map('unlink', glob("$path/*.*"));
        rmdir($path);
    }

    /** @test */
    public function encryptPemInDes3()
    {
        $path = __DIR__.'/../temp/';
        $csdConverter = new CsdConverter($this->cer_pem, $this->key_file, $this->password);
        $csdConverter->save($path);
        $csdConverter->encryptKey("{$path}LAN7008173R5.key.pem", '12345678a');
        $this->assertFileExists("{$path}LAN7008173R5.enc.key");

        array_map('unlink', glob("$path/*.*"));
        rmdir($path);
    }
}
