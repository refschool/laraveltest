# 🧪 Laravel — Projet de tests unitaires

Projet d'apprentissage des tests unitaires avec PHPUnit dans un contexte Laravel.
Couvre les patterns essentiels : assertions, exceptions, data providers, mocks et stubs.

---

## 🚀 Installation

```bash
# Prérequis : PHP 8.1+ et Composer
composer install

# Lancer tous les tests
composer test

# Lancer par suite
composer test-unit
composer test-feature

# Filtrer par classe ou méthode
./vendor/bin/phpunit --filter=CalculatorTest
./vendor/bin/phpunit --filter=test_register_appelle_send_welcome_une_seule_fois
```

---

## 📁 Structure

```
laravel-tests/
├── app/
│   ├── Contracts/
│   │   └── EmailServiceInterface.php   ← Interface (injectée, mockable)
│   ├── Models/
│   │   └── User.php                    ← Modèle métier
│   └── Services/
│       ├── Calculator.php              ← Calculs de base
│       ├── EmailService.php            ← Implémentation email (prod)
│       ├── OrderService.php            ← Logique de commande
│       ├── PasswordService.php         ← Hash / validation mdp
│       ├── RegistrationService.php     ← Inscription (dépend de EmailServiceInterface)
│       └── UserService.php             ← CRUD utilisateurs en mémoire
└── tests/
    ├── TestCase.php                    ← Classe de base commune
    ├── Unit/
    │   ├── CalculatorTest.php          ← Assertions, data providers, exceptions
    │   ├── UserTest.php                ← Tests de modèle
    │   ├── OrderServiceTest.php        ← Règles métier complexes
    │   ├── PasswordServiceTest.php     ← Validation + bcrypt
    │   └── RegistrationServiceTest.php ← Mocks & stubs (createMock)
    └── Feature/
        └── UserServiceTest.php         ← Tests d'intégration
```

---

## 📚 Concepts illustrés

### 1. Assertions courantes

```php
$this->assertSame(5.0, $calc->add(2, 3));
$this->assertTrue($user->isAdmin());
$this->assertNull($service->findByEmail('x@x.com'));
$this->assertCount(2, $admins);
$this->assertEmpty($errors);
$this->assertContains('Au moins une majuscule', $result['errors']);
$this->assertArrayHasKey('total', $result);
```

### 2. Tester les exceptions

```php
$this->expectException(\InvalidArgumentException::class);
$this->expectExceptionMessage('Division par zéro impossible.');

$this->calc->divide(10, 0); // doit lever l'exception
```

### 3. Data Providers

```php
/** @dataProvider shippingProvider */
public function test_frais_de_livraison(float $subtotal, float $expected): void
{
    $this->assertSame($expected, $this->service->calculateShipping($subtotal));
}

public static function shippingProvider(): array
{
    return [
        'sous le seuil'  => [30.0, 5.90],
        'au seuil'       => [50.0, 0.0],
        'au dessus'      => [80.0, 0.0],
    ];
}
```

### 4. Mocks avec `createMock()`

```php
// Créer un mock de l'interface
$emailMock = $this->createMock(EmailServiceInterface::class);

// Stub : définir ce que retourne la méthode
$emailMock->method('sendWelcome')->willReturn(true);

// Spy : vérifier qu'une méthode est appelée exactement 1 fois
$emailMock->expects($this->once())->method('sendWelcome')->willReturn(true);

// Vérifier les arguments passés
$emailMock
    ->expects($this->once())
    ->method('sendWelcome')
    ->with($this->callback(fn(User $u) => $u->email === 'alice@example.com'))
    ->willReturn(true);

// Vérifier qu'une méthode n'est JAMAIS appelée
$emailMock->expects($this->never())->method('sendWelcome');
```

### 5. setUp() / tearDown()

```php
protected function setUp(): void
{
    parent::setUp();
    // Exécuté avant CHAQUE test — repart d'un état propre
    $this->service = new UserService();
}
```

---

## ✅ Bonnes pratiques

| Règle | Pourquoi |
|---|---|
| Un test = un comportement | Facile à diagnostiquer |
| Nommage `test_ce_que_ça_fait()` | Lisible comme documentation |
| `setUp()` pour l'état initial | Évite la duplication |
| Data providers pour les cas multiples | DRY et exhaustif |
| Interfaces + mocks pour les dépendances I/O | Rapide, isolé, déterministe |
| `assertSame` plutôt que `assertEquals` | Vérifie aussi le type |

---

## 🔗 Ressources

- [PHPUnit Documentation](https://docs.phpunit.de/)
- [Laravel Testing](https://laravel.com/docs/testing)
- [Test Driven Development](https://en.wikipedia.org/wiki/Test-driven_development)
