<?php

use PHPUnit\Framework\TestCase;

class SecuriteTest extends TestCase
{
    // --- hacherMotDePasse ---

    public function testHachageRetourneStringNonVide(): void
    {
        $hachage = Securite::hacherMotDePasse('motdepasse');
        $this->assertNotEmpty($hachage);
    }

    public function testHachageDifferentDuMotDePasseClair(): void
    {
        $mot_de_passe = 'motdepasse';
        $hachage = Securite::hacherMotDePasse($mot_de_passe);
        $this->assertNotEquals($mot_de_passe, $hachage);
    }

    public function testDeuxHachagesDuMemeMotDePasseSontDifferents(): void
    {
        // bcrypt génère un sel aléatoire — deux hachages ne sont jamais identiques
        $hachage1 = Securite::hacherMotDePasse('motdepasse');
        $hachage2 = Securite::hacherMotDePasse('motdepasse');
        $this->assertNotEquals($hachage1, $hachage2);
    }

    // --- verifierMotDePasse ---

    public function testVerificationCorrectRetourneTrue(): void
    {
        $hachage = Securite::hacherMotDePasse('secret123');
        $this->assertTrue(Securite::verifierMotDePasse('secret123', $hachage));
    }

    public function testVerificationMauvaisMotDePasseRetourneFalse(): void
    {
        $hachage = Securite::hacherMotDePasse('secret123');
        $this->assertFalse(Securite::verifierMotDePasse('mauvais', $hachage));
    }

    public function testVerificationHashInvalideRetourneFalse(): void
    {
        $this->assertFalse(Securite::verifierMotDePasse('secret123', 'hash_invalide'));
    }

    // --- genererNomFichier ---

    public function testExtensionAutoriseeRetourneString(): void
    {
        $nom = Securite::genererNomFichier('document.pdf');
        $this->assertNotNull($nom);
        $this->assertStringEndsWith('.pdf', $nom);
    }

    public function testExtensionMajusculesNormalisee(): void
    {
        $nom = Securite::genererNomFichier('image.JPG');
        $this->assertNotNull($nom);
        $this->assertStringEndsWith('.jpg', $nom);
    }

    public function testExtensionPHPRefusee(): void
    {
        $this->assertNull(Securite::genererNomFichier('script.php'));
    }

    public function testExtensionZipRefusee(): void
    {
        $this->assertNull(Securite::genererNomFichier('archive.zip'));
    }

    public function testSansExtensionRefusee(): void
    {
        $this->assertNull(Securite::genererNomFichier('fichier_sans_extension'));
    }

    public function testNomFichierUnique(): void
    {
        $nom1 = Securite::genererNomFichier('doc.pdf');
        $nom2 = Securite::genererNomFichier('doc.pdf');
        $this->assertNotEquals($nom1, $nom2);
    }
}
