<?php

namespace App\Service;

use App\Exception\StatsNotAllException;
use UnexpectedValueException;

class CsvParser
{
    private MedalChecker $medalChecker;

    public function __construct(MedalChecker $medalChecker)
    {
        $this->medalChecker = $medalChecker;
    }

    public function parse(string $csvString, string $type): array
    {
        switch ($type) {
            case 'prime':
                return $this->parsePrimeCsv($csvString);
            case 'agentstats':
                return $this->parseAgentStatsCsv($csvString);
            default:
                throw new UnexpectedValueException('unknown CSV type');
        }
    }

    private function parsePrimeCsv(string $csvString): array
    {
        $csv = [];
        $headVars = [];
        $sepChar = "\t";

        $lines = explode("\n", trim($csvString));

        foreach ($lines as $i => $line) {
            $sepChar = strpos($line, ',') ? ',' : $sepChar;
            if (0 === $i) {
                $headVars = explode($sepChar, trim($line));

                continue;
            }

            $vars = explode($sepChar, $line);

            if (false === in_array(
                    $vars[0], [
                    'GESAMT',
                    'SIEMPRE',
                    'ALL TIME',
                ], true
                )
            ) {
                throw new StatsNotAllException('Prime stats not ALL');
            }

            $c = [];
            $dateTime = $vars[3].' '.$vars[4];

            foreach ($headVars as $i1 => $headVar) {
                $vName = $this->medalChecker->translatePrimeHeader($headVar);

                if ($vName) {
                    $c[$vName] = $vars[$i1];
                }
            }

            $csv[$dateTime] = $c;
        }

        if (!$csv) {
            throw new UnexpectedValueException('Invalid CSV');
        }

        return $csv;
    }

    private function parseAgentStatsCsv(string $csvString): array
    {
        $csv = [];
        $headVars = [];

        $lines = explode("\n", trim($csvString));

        foreach ($lines as $i => $line) {
            if (0 === $i) {
                $headVars = explode("\t", $line);

                continue;
            }

            $vars = explode("\t", $line);

            $c = [];
            $dateTime = $vars[0];

            foreach ($headVars as $i1 => $headVar) {
                if ($i1 > 0) {
                    $c[$headVar] = $vars[$i1 - 1];
                }
            }

            $csv[$dateTime] = $c;
        }

        if (!$csv) {
            throw new UnexpectedValueException('Invalid CSV');
        }

        return $csv;
    }
}
