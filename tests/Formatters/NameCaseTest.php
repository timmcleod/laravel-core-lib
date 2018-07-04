<?php

namespace Tests\Formatters;

use PHPUnit\Framework\TestCase;
use TimMcLeod\LaravelCoreLib\Formatters\NameCase;

class NameCaseTest extends TestCase
{
    /**
     * Test the NameCase::format($str) method.
     *
     * @return void
     */
    public function testNameCase()
    {
        $this->assertTrue(NameCase::format("michael o'carrol") === "Michael O'Carrol");
        $this->assertTrue(NameCase::format("tim mcleod") === "Tim McLeod");
        $this->assertTrue(NameCase::format("TIM MCLEOD") === "Tim McLeod");
        $this->assertTrue(NameCase::format("lucas l'amour") === "Lucas l'Amour");
        $this->assertTrue(NameCase::format("george d'onofrio") === "George d'Onofrio");
        $this->assertTrue(NameCase::format("william stanley iii") === "William Stanley III");
        $this->assertTrue(NameCase::format("UNITED STATES OF AMERICA") === "United States of America");
        $this->assertTrue(NameCase::format("t. von lieres und wilkau") === "T. von Lieres und Wilkau");
        $this->assertTrue(NameCase::format("paul van der knaap") === "Paul van der Knaap");
        $this->assertTrue(NameCase::format("jean-luc picard") === "Jean-Luc Picard");
        $this->assertTrue(NameCase::format("JOHN MCLAREN") === "John McLaren");
        $this->assertTrue(NameCase::format("hENRIC vIII") === "Henric VIII");
        $this->assertTrue(NameCase::format("VAsco da GAma") === "Vasco da Gama");
        $this->assertTrue(NameCase::format("BILL O'CONNOR") === "Bill O'Connor");
        $this->assertTrue(NameCase::format("bill o'connor") === "Bill O'Connor");

        // Override delimiters.
        $this->assertTrue(NameCase::format("bill o'connor", [' ']) === "Bill O'connor");

        // Override force lowercase.
        $this->assertTrue(NameCase::format("BILL O'CONNOR", [' '], ["bill", "o'connor"]) === "bill o'connor");

        // Override force uppercase.
        $this->assertTrue(NameCase::format("bill o'connor", [' '], null, ["BILL", "O'CONNOR"]) === "BILL O'CONNOR");
    }
}