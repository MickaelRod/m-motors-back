<?php

use PHPUnit\Framework\TestCase;

class VehiculeTest extends TestCase
{
    // --- basculerType ---

    public function testBasculerAchatRetourneLocation(): void
    {
        $this->assertEquals('location', Vehicule::basculerType('achat'));
    }

    public function testBasculerLocationRetourneAchat(): void
    {
        $this->assertEquals('achat', Vehicule::basculerType('location'));
    }

    public function testBasculerTypeInconnuRetourneNull(): void
    {
        $this->assertNull(Vehicule::basculerType('vente'));
        $this->assertNull(Vehicule::basculerType(''));
    }

    // --- estTypeValide ---

    public function testTypeAchatEstValide(): void
    {
        $this->assertTrue(Vehicule::estTypeValide('achat'));
    }

    public function testTypeLocationEstValide(): void
    {
        $this->assertTrue(Vehicule::estTypeValide('location'));
    }

    public function testTypeInconnuEstInvalide(): void
    {
        $this->assertFalse(Vehicule::estTypeValide('autre'));
        $this->assertFalse(Vehicule::estTypeValide('ACHAT'));
        $this->assertFalse(Vehicule::estTypeValide(''));
    }
}
