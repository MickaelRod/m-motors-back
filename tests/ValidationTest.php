<?php

use PHPUnit\Framework\TestCase;

class ValidationTest extends TestCase
{
    // --- email ---

    public function testEmailValide(): void
    {
        $this->assertTrue(Validation::email('user@example.com'));
        $this->assertTrue(Validation::email('prenom.nom+tag@domaine.fr'));
    }

    public function testEmailInvalide(): void
    {
        $this->assertFalse(Validation::email('user@'));
        $this->assertFalse(Validation::email('plain'));
        $this->assertFalse(Validation::email(''));
        $this->assertFalse(Validation::email('@domaine.com'));
    }

    // --- telephone ---

    public function testTelephoneValide(): void
    {
        $this->assertTrue(Validation::telephone('+33 6 12 34 56 78'));
        $this->assertTrue(Validation::telephone('0601020304'));
        $this->assertTrue(Validation::telephone('+1-800-555-0199'));
    }

    public function testTelephoneInvalide(): void
    {
        $this->assertFalse(Validation::telephone('12'));
        $this->assertFalse(Validation::telephone('abc'));
        $this->assertFalse(Validation::telephone(''));
    }

    // --- motDePasse ---

    public function testMotDePasseValide(): void
    {
        $this->assertTrue(Validation::motDePasse('12345678'));
        $this->assertTrue(Validation::motDePasse('motdepassetreslongetcomplexe'));
    }

    public function testMotDePasseTropCourt(): void
    {
        $this->assertFalse(Validation::motDePasse('1234567'));
        $this->assertFalse(Validation::motDePasse(''));
    }

    // --- typeCommercial ---

    public function testTypeCommercialValide(): void
    {
        $this->assertTrue(Validation::typeCommercial('achat'));
        $this->assertTrue(Validation::typeCommercial('location'));
    }

    public function testTypeCommercialInvalide(): void
    {
        $this->assertFalse(Validation::typeCommercial('vente'));
        $this->assertFalse(Validation::typeCommercial('ACHAT'));
        $this->assertFalse(Validation::typeCommercial(''));
    }

    // --- statutDossier ---

    public function testStatutDossierValide(): void
    {
        $this->assertTrue(Validation::statutDossier('en_attente'));
        $this->assertTrue(Validation::statutDossier('valide'));
        $this->assertTrue(Validation::statutDossier('refuse'));
    }

    public function testStatutDossierInvalide(): void
    {
        $this->assertFalse(Validation::statutDossier('en_cours'));
        $this->assertFalse(Validation::statutDossier(''));
        $this->assertFalse(Validation::statutDossier('Valide'));
    }
}
