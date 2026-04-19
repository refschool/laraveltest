<?php

namespace Tests\Unit;

use App\Services\PasswordService;
use Tests\TestCase;

/**
 * Tests du service PasswordService.
 * Illustre les tests de validation avec plusieurs règles combinées.
 */
class PasswordServiceTest extends TestCase
{
    private PasswordService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PasswordService();
    }

    // ---------------------------------------------------------------
    // validate() — mot de passe valide
    // ---------------------------------------------------------------

    public function test_password_valide_retourne_valid_true(): void
    {
        $result = $this->service->validate('MonMot@Passe1');

        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    // ---------------------------------------------------------------
    // validate() — chaque règle individuellement
    // ---------------------------------------------------------------

    public function test_password_trop_court_genere_une_erreur(): void
    {
        $result = $this->service->validate('Ab1!');

        $this->assertFalse($result['valid']);
        $this->assertContains('Au moins 8 caractères', $result['errors']);
    }

    public function test_password_sans_majuscule_genere_une_erreur(): void
    {
        $result = $this->service->validate('monmot@passe1');

        $this->assertFalse($result['valid']);
        $this->assertContains('Au moins une majuscule', $result['errors']);
    }

    public function test_password_sans_minuscule_genere_une_erreur(): void
    {
        $result = $this->service->validate('MONMOT@PASSE1');

        $this->assertFalse($result['valid']);
        $this->assertContains('Au moins une minuscule', $result['errors']);
    }

    public function test_password_sans_chiffre_genere_une_erreur(): void
    {
        $result = $this->service->validate('MonMot@Passe');

        $this->assertFalse($result['valid']);
        $this->assertContains('Au moins un chiffre', $result['errors']);
    }

    public function test_password_sans_special_genere_une_erreur(): void
    {
        $result = $this->service->validate('MonMotPasse1');

        $this->assertFalse($result['valid']);
        $this->assertContains('Au moins un caractère spécial (!@#$%^&*)', $result['errors']);
    }

    public function test_password_vide_genere_toutes_les_erreurs(): void
    {
        $result = $this->service->validate('');

        $this->assertFalse($result['valid']);
        $this->assertCount(5, $result['errors']);
    }

    // ---------------------------------------------------------------
    // hash() et verify()
    // ---------------------------------------------------------------

    public function test_hash_retourne_une_chaine_non_vide(): void
    {
        $hash = $this->service->hash('MonMot@Passe1');

        $this->assertNotEmpty($hash);
        $this->assertNotSame('MonMot@Passe1', $hash);
    }

    public function test_hash_deux_fois_le_meme_mdp_donne_des_hashs_differents(): void
    {
        // Bcrypt génère un sel aléatoire à chaque appel
        $hash1 = $this->service->hash('MonMot@Passe1');
        $hash2 = $this->service->hash('MonMot@Passe1');

        $this->assertNotSame($hash1, $hash2);
    }

    public function test_verify_retourne_true_si_mdp_correspond(): void
    {
        $hash = $this->service->hash('MonMot@Passe1');

        $this->assertTrue($this->service->verify('MonMot@Passe1', $hash));
    }

    public function test_verify_retourne_false_si_mdp_incorrect(): void
    {
        $hash = $this->service->hash('MonMot@Passe1');

        $this->assertFalse($this->service->verify('MauvaisMotDePasse', $hash));
    }

    // ---------------------------------------------------------------
    // matches()
    // ---------------------------------------------------------------

    public function test_matches_retourne_true_si_identiques(): void
    {
        $this->assertTrue($this->service->matches('abc', 'abc'));
    }

    public function test_matches_retourne_false_si_differents(): void
    {
        $this->assertFalse($this->service->matches('abc', 'ABC'));
    }
}
