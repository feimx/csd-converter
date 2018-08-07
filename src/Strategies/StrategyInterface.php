<?php

namespace FeiMx\Csd\Strategies;

interface StrategyInterface
{
    public function getContentIsNotEmpty(): string;

    public function convertToPemIfIsNotConverted(): string;

    public function convertToPem($content): string;
}
