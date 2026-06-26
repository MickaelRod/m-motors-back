<?php

use PHPUnit\Framework\TestCase;

class DossierTest extends TestCase
{
    // --- estStatutValide ---

    public function testStatutsValidesRetournentTrue(): void
    {
        $this->assertTrue(Dossier::estStatutValide('en_attente'));
        $this->assertTrue(Dossier::estStatutValide('valide'));
        $this->assertTrue(Dossier::estStatutValide('refuse'));
    }

    public function testStatutInconnuRetourneFalse(): void
    {
        $this->assertFalse(Dossier::estStatutValide('en_cours'));
        $this->assertFalse(Dossier::estStatutValide(''));
        $this->assertFalse(Dossier::estStatutValide('Valide'));
    }

    // --- traduireType ---

    public function testTraduireTypesConnus(): void
    {
        $this->assertEquals('Achat comptant',         Dossier::traduireType('achat'));
        $this->assertEquals('Achat avec financement', Dossier::traduireType('financement'));
        $this->assertEquals('Location (LLD)',          Dossier::traduireType('location'));
        $this->assertEquals('Demande générale',        Dossier::traduireType('autre'));
    }

    public function testTraduireTypeInconnuRetourneCodeBrut(): void
    {
        $this->assertEquals('inconnu', Dossier::traduireType('inconnu'));
        $this->assertEquals('',        Dossier::traduireType(''));
    }

    // --- traduireStatut ---

    public function testTraduireStatutsConnus(): void
    {
        $this->assertEquals('En attente', Dossier::traduireStatut('en_attente'));
        $this->assertEquals('Validé',     Dossier::traduireStatut('valide'));
        $this->assertEquals('Refusé',     Dossier::traduireStatut('refuse'));
    }

    public function testTraduireStatutInconnuRetourneCodeBrut(): void
    {
        $this->assertEquals('inconnu', Dossier::traduireStatut('inconnu'));
        $this->assertEquals('',        Dossier::traduireStatut(''));
    }
}
