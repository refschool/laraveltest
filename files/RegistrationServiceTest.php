<?php

namespace Tests\Unit;

use App\Contracts\EmailServiceInterface;
use App\Models\User;
use App\Services\RegistrationService;
use App\Services\UserService;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

/**
 * Tests du RegistrationService avec mocks.
 *
 * On mocke EmailServiceInterface pour :
 * - ne pas envoyer de vrais emails
 * - contrôler ce que retourne le service dans chaque scénario
 * - vérifier que les bonnes méthodes sont bien appelées
 */
class RegistrationServiceTest extends TestCase
{
    private UserService $userService;
    /** @var EmailServiceInterface&MockObject */
    private EmailServiceInterface $emailMock;
    private RegistrationService $registrationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userService = new UserService();

        // Création du mock de EmailServiceInterface
        $this->emailMock = $this->createMock(EmailServiceInterface::class);

        $this->registrationService = new RegistrationService(
            $this->userService,
            $this->emailMock,
        );
    }

    // ---------------------------------------------------------------
    // register() — cas nominaux
    // ---------------------------------------------------------------

    public function test_register_cree_et_retourne_un_user(): void
    {
        // Le mock retournera true (email envoyé avec succès)
        $this->emailMock
            ->method('sendWelcome')
            ->willReturn(true);

        $user = $this->registrationService->register('Alice', 'alice@example.com');

        $this->assertSame('Alice', $user->name);
        $this->assertSame('alice@example.com', $user->email);
    }

    public function test_register_appelle_send_welcome_une_seule_fois(): void
    {
        // On vérifie que sendWelcome est appelé exactement 1 fois
        $this->emailMock
            ->expects($this->once())      // ← assertion sur l'appel
            ->method('sendWelcome')
            ->willReturn(true);

        $this->registrationService->register('Alice', 'alice@example.com');
    }

    public function test_register_appelle_send_welcome_avec_le_bon_user(): void
    {
        $this->emailMock
            ->expects($this->once())
            ->method('sendWelcome')
            ->with($this->callback(function (User $user): bool {
                // On vérifie que le User passé est bien le bon
                return $user->email === 'alice@example.com';
            }))
            ->willReturn(true);

        $this->registrationService->register('Alice', 'alice@example.com');
    }

    // ---------------------------------------------------------------
    // register() — cas d'erreur
    // ---------------------------------------------------------------

    public function test_register_leve_exception_si_nom_vide(): void
    {
        // Le mock ne doit PAS être appelé dans ce cas
        $this->emailMock
            ->expects($this->never())
            ->method('sendWelcome');

        $this->expectException(\InvalidArgumentException::class);

        $this->registrationService->register('   ', 'alice@example.com');
    }

    public function test_register_leve_runtime_exception_si_email_echoue(): void
    {
        // Le mock simule un échec d'envoi
        $this->emailMock
            ->method('sendWelcome')
            ->willReturn(false);

        $this->expectException(\RuntimeException::class);

        $this->registrationService->register('Alice', 'alice@example.com');
    }

    public function test_register_leve_exception_si_email_invalide(): void
    {
        $this->emailMock->method('sendWelcome')->willReturn(true);

        $this->expectException(\InvalidArgumentException::class);

        $this->registrationService->register('Alice', 'pas-un-email');
    }

    // ---------------------------------------------------------------
    // forgotPassword()
    // ---------------------------------------------------------------

    public function test_forgot_password_retourne_false_si_utilisateur_inexistant(): void
    {
        // sendPasswordReset ne doit jamais être appelé
        $this->emailMock
            ->expects($this->never())
            ->method('sendPasswordReset');

        $result = $this->registrationService->forgotPassword('inexistant@example.com');

        $this->assertFalse($result);
    }

    public function test_forgot_password_envoie_email_si_utilisateur_existe(): void
    {
        // D'abord on inscrit l'utilisateur
        $this->emailMock->method('sendWelcome')->willReturn(true);
        $this->registrationService->register('Alice', 'alice@example.com');

        // Puis on vérifie que sendPasswordReset est bien appelé
        $this->emailMock
            ->expects($this->once())
            ->method('sendPasswordReset')
            ->with(
                $this->callback(fn(User $u) => $u->email === 'alice@example.com'),
                $this->isType('string'), // le token est une string
            )
            ->willReturn(true);

        $result = $this->registrationService->forgotPassword('alice@example.com');

        $this->assertTrue($result);
    }
}
