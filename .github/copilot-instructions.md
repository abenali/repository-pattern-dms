# Instructions pour GitHub Copilot

## Contexte du projet

- Application PHP 8.4 / Symfony 7.3 de gestion documentaire (DMS).
- L'architecture suit Clean Architecture : `Domain`, `Application`, `Infrastructure` et `Presentation`.
- Le projet met en oeuvre les patterns Repository et Specification avec Doctrine ORM et PostgreSQL.
- Le namespace `App\` correspond au dossier `src/`; les tests utilisent `App\Tests\` dans `tests/`.

## Conventions de code

- Respecter les frontières des couches : le domaine ne dépend pas de Symfony, Doctrine ni de l'infrastructure.
- Déclarer `strict_types=1` dans les fichiers PHP et suivre le style ainsi que les types déjà utilisés.
- Dépendre des interfaces du domaine dans les cas d'usage; placer les implémentations techniques dans `Infrastructure`.
- Garder les changements ciblés et cohérents avec les patterns existants; éviter d'ajouter des dépendances sans nécessité.
- Ajouter ou adapter les tests pour tout changement de comportement.

## Vérification

- Tests : `make test` ou `php bin/phpunit`
- Analyse statique : `make phpstan` ou `vendor/bin/phpstan analyse`
- Vérification du style : `vendor/bin/php-cs-fixer fix --dry-run --diff`
- La CI utilise PHP 8.4 et PostgreSQL 15. Les tests d'intégration peuvent nécessiter une base configurée.
