# ADR 003: Utilisation du Repository Pattern + Specification Pattern pour le DMS

## Statut
✅ Accepté - 10/12/2025

## Contexte

### Problème métier



**À compléter ici :**
```
Le système DocHub doit gérer des milliers de documents avec des métadonnées riches et variées.
La complexité vient du fait que les utilisateurs ont besoin d'effectuer des recherches complexes et combinables (par auteur, par date, par statut, par tags, etc.).
Les besoins business incluent la possibilité de créer des filtres dynamiques, de trier les résultats selon plusieurs axes et de gérer la pagination de manière performante.
```

### Problèmes techniques



**À compléter ici :**
```
D'un point de vue technique :
- Violation de SRP (Single Responsibility Principle) car les Use Cases métier contiennent de la logique de construction de requête (DQL), qui est une responsabilité d'infrastructure. Violation de l'OCP (Open/Closed Principle) car l'ajout d'un nouveau critère de recherche modifie le Use Case.
- Couplage fort car les Use Cases sont directement liés à l'implémentation de Doctrine (QueryBuilder, DQL).
- Difficulté à tester car il est impossible de tester la logique de recherche (les `where`, `andWhere`...) sans une base de données réelle, rendant les tests lents et dépendants de l'infrastructure.
- Changement de source de données impossible car la logique de requête est spécifique à Doctrine et disséminée dans toute l'application. Migrer vers Elasticsearch, par exemple, nécessiterait de réécrire tous les Use Cases.
```

---

## Décision

Nous avons décidé d'utiliser le **Repository Pattern** combiné au **Specification Pattern** pour encapsuler l'accès aux données et la logique de filtrage.

### Pourquoi Repository + Specification ?



**À compléter ici :**
```
Le Repository Pattern résout le problème de couplage entre le métier et la source de données en fournissant une abstraction sur la gestion des données.
Le Specification Pattern résout le problème de la logique de filtrage complexe en l'encapsulant dans des objets réutilisables et combinables.

Nous les combinons car :
1. Repository = accès aux données (COMMENT récupérer les données : SQL, API, etc.). Sa seule responsabilité est de persister et de récupérer des entités.
2. Specification = logique de filtrage (QUOI récupérer : quels sont les critères métier). Sa seule responsabilité est de définir une condition.
3. Séparation claire des responsabilités : le Use Case définit QUOI chercher (les Specifications), et le Repository décide COMMENT le chercher (la technologie de persistance).
```

### Pourquoi Visitor Pattern pour la traduction Doctrine ?



**À compléter ici :**
```
Le Visitor Pattern permet de séparer l'algorithme de sa structure. Ici, il permet de séparer la définition d'une Specification (le QUOI) de sa traduction en une technologie concrète comme DQL (le COMMENT).
Sans Visitor, on aurait dû mettre une méthode `toDQL()` dans l'interface `SpecificationInterface`. Cela violerait la Clean Architecture en intégrant un détail d'infrastructure (Doctrine) dans le domaine.
Avec Visitor, les Specifications restent pures et agnostiques. On peut créer différents Visitors (`DoctrineSpecificationVisitor`, `ElasticsearchSpecificationVisitor`) pour traduire la même Specification dans différentes technologies, sans jamais modifier les Specifications elles-mêmes.
```

---

## Conséquences

### Positives ✅



**À compléter ici :**
```
1. Respect de SOLID :
   - SRP : Le Repository gère l'accès aux données, la Specification la logique de filtrage, le Visitor la traduction technologique. Chacun a une seule responsabilité.
   - OCP : Pour ajouter un nouveau critère, on crée une nouvelle classe `Specification` et on met à jour le Visitor. Le Repository et le Use Case restent inchangés (ouverts à l'extension, fermés à la modification).
   - DIP : Le Use Case dépend de l'interface `DocumentRepositoryInterface` (une abstraction), pas de l'implémentation `DoctrineDocumentRepository` (un détail).

2. Découplage : Le Use Case manipule des objets du domaine (`Specification`) et n'a aucune connaissance de Doctrine ou du SQL. La couche de persistance peut être changée sans impacter le métier.

3. Réutilisabilité : Chaque `Specification` est un composant réutilisable. `new StatusSpecification('published')` peut être utilisé dans plusieurs Use Cases (recherche, reporting, etc.). Elles sont combinables à l'infini (AND, OR, NOT).

4. Testabilité : Chaque `Specification` peut être testée unitairement en mémoire (`isSatisfiedBy($document)`), sans base de données. Le Use Case peut être testé en mockant le Repository. Les tests sont rapides, fiables et isolés.

5. Flexibilité : Changer de source de données (de PostgreSQL à Elasticsearch) ne requiert que de créer une nouvelle implémentation du Repository et un nouveau Visitor. Le code métier reste intact.
```

### Négatives ⚠️



**À compléter ici :**
```
1. Complexité : Le nombre de classes (Repository, Specifications, Visitor, interfaces...) est plus élevé qu'une simple requête DQL dans un contrôleur. Cela peut sembler de l'over-engineering pour des besoins simples.

2. Courbe d'apprentissage : Le Specification Pattern, et surtout le Visitor Pattern, sont des concepts avancés qui peuvent être difficiles à comprendre pour les développeurs juniors ou ceux non familiers avec le DDD.

3. Performance : L'abstraction a un coût. La construction de l'arbre de Specifications et sa traduction par le Visitor peuvent introduire un léger overhead par rapport à une requête DQL native et optimisée à la main. Cet impact est cependant souvent négligeable.
```

---

## Alternatives considérées

### Alternative 1 : DQL directement dans les Use Cases

**Description :**
```php
class SearchDocumentsHandler
{
    public function __construct(private EntityManagerInterface $em) {}
    
    public function execute($command)
    {
        $qb = $this->em->createQueryBuilder()
            ->select('d')
            ->from(Document::class, 'd')
            ->where('d.author = :author')
            ->andWhere('d.status = :status');
        // ...
    }
}
```

**Pourquoi rejetée :**


**À compléter ici :**
```
Cette approche viole les principes de Single Responsibility et de Dependency Inversion.
Le problème est que le Use Case, qui devrait contenir de la logique métier pure, est pollué par des détails d'infrastructure (Doctrine, DQL).
Impossible de tester le Use Case sans une base de données, et impossible de changer de source de données sans réécrire tout le code.
```

### Alternative 2 : Repository avec méthodes spécifiques

**Description :**
```php
interface DocumentRepositoryInterface
{
    public function findByAuthor(User $author): array;
    public function findByStatus(string $status): array;
    public function findByAuthorAndStatus(User $author, string $status): array;
    public function findByAuthorAndStatusAndTags(...): array;
    // 50+ méthodes ?
}
```

**Pourquoi rejetée :**
[Explique le problème de l'explosion combinatoire]

**À compléter ici :**
```
Cette approche peut nous amener à écrire un nombre important de méthodes dans l'interface du Repository.
Avec N critères, on a potentiellement 2^N - 1 combinaisons possibles, ce qui est intenable.
Impossible de maintenir car l'ajout d'un seul critère forcerait à ajouter des dizaines de nouvelles méthodes pour couvrir toutes les combinaisons. L'interface devient un "fourre-tout".
```

### Alternative 3 : Query Builder exposé

**Description :**
```php
interface DocumentRepositoryInterface
{
    public function createQueryBuilder(): QueryBuilder;
}

// Usage dans le Use Case
$qb = $repo->createQueryBuilder();
$qb->where('d.author = :author'); // ❌ DQL dans le Use Case !
```

**Pourquoi rejetée :**
[Explique pourquoi c'est un faux découplage]

**À compléter ici :**
```
Cette approche expose un détail d'implémentation (le QueryBuilder de Doctrine) en dehors de la couche de persistance.
Le Use Case dépend toujours de Doctrine, même si c'est à travers une méthode de Repository. Le couplage reste fort.
Pas de réelle abstraction car le Use Case doit connaître la syntaxe et les méthodes du QueryBuilder sous-jacent. Le découplage est une illusion.
```

---

## Implémentation technique

### Architecture choisie

**Domain** :
- Interface `SpecificationInterface` : contrat des specifications
- Interface `DocumentRepositoryInterface` : contrat du repository
- Specifications concrètes : `AuthorSpecification`, `StatusSpecification`, etc.
- Specifications composites : `AndSpecification`, `OrSpecification`, `NotSpecification`
- `DocumentQuery` : Query Object (tri, pagination)
- Entités : `Document`, `User`, `DocumentStatus` (enum)

**Application** :
- Use Case : `SearchDocumentsHandler`
- DTOs : `SearchDocumentsCommand`, `SearchDocumentsResponse`

**Infrastructure** :
- `DoctrineDocumentRepository` : implémente `DocumentRepositoryInterface`
- `DoctrineSpecificationVisitor` : traduit Specifications en DQL
- Mapping Doctrine des entités

**Presentation** :
- Controller API REST

### Flux d'exécution

[Décris le flux complet]

**À compléter ici :**
```
1. Le Controller reçoit la requête HTTP avec les query params (filtres, tri, page).
2. Il crée un `SearchDocumentsCommand` (DTO) avec ces critères et le passe au handler.
3. Le `SearchDocumentsHandler` (Use Case) reçoit le Command :
   a) Il construit les objets `Specification` nécessaires (`AuthorSpecification`, `StatusSpecification`...) à partir des données du Command.
   b) Il combine ces Specifications avec `AndSpecification` si plusieurs critères sont fournis.
   c) Il crée un objet `DocumentQuery` qui encapsule la `Specification` composite, le tri et la pagination.
   d) Il appelle la méthode `findByQuery(DocumentQuery $query)` du Repository.
4. Le `DoctrineDocumentRepository` :
   a) Crée un `QueryBuilder` Doctrine.
   b) Instancie le `DoctrineSpecificationVisitor` et lui passe le `QueryBuilder`.
   c) Appelle `specification->accept($visitor)`, ce qui déclenche la traduction de l'arbre de Specifications en clauses DQL (`WHERE`, `AND`, `OR`...).
   d) Applique le tri et la pagination au `QueryBuilder`.
   e) Exécute la requête et récupère les entités Doctrine.
5. Le Use Case reçoit les entités, les transforme en DTOs et retourne une `SearchDocumentsResponse` (contenant les résultats et les infos de pagination).
6. Le Controller reçoit la Response, la sérialise en JSON et la retourne au client.
```

### Visitor Pattern : Traduction Specification → DQL

[Explique comment le Visitor fonctionne]

**À compléter ici :**
```
Le DoctrineSpecificationVisitor implémente une méthode `visit` pour chaque type de Specification concrète :
1. Il est initialisé avec le QueryBuilder Doctrine.
2. Quand `specification->accept($visitor)` est appelé, la bonne méthode `visit` est déclenchée par double dispatch.
3. Pour chaque type de spec, le visitor ajoute la clause DQL correspondante au QueryBuilder :
   - `visitAuthor(AuthorSpecification $spec)` → `$qb->andWhere('d.author = :author')`
   - `visitStatus(StatusSpecification $spec)` → `$qb->andWhere('d.status = :status')`
   - `visitAnd(AndSpecification $spec)` → Visite récursivement chaque spec enfant et les combine avec `AND`.
   - `visitOr(OrSpecification $spec)` → Crée une expression `OR` (`$qb->expr()->orX(...)`) et visite récursivement chaque spec enfant.

Avantage : Les Specifications (`AuthorSpecification`, etc.) ne contiennent aucune logique DQL. Elles sont pures et agnostiques de la persistance.
```

### Composite Pattern : AND/OR/NOT

[Explique comment combiner les specifications]

**À compléter ici :**
```
AndSpecification :
- Contient un tableau de Specifications dans son constructeur.
- `isSatisfiedBy()` retourne `true` SI TOUTES les specifications internes retournent `true`.
- Le Visitor parcourt le tableau et ajoute chaque traduction de spec avec un `AND`.

OrSpecification :
- Contient un tableau de Specifications.
- `isSatisfiedBy()` retourne `true` SI AU MOINS UNE des specifications internes retourne `true`.
- Le Visitor parcourt le tableau et combine les traductions avec une expression `OR`.

NotSpecification :
- Contient UNE seule Specification.
- `isSatisfiedBy()` retourne l'inverse du résultat de la specification interne.
- Le Visitor ajoute `NOT()` autour de la traduction de la spec interne.
```

### Query Object Pattern

[Explique le rôle du DocumentQuery]

**À compléter ici :**
```
DocumentQuery encapsule tous les aspects d'une requête complexe en un seul objet :
- La `Specification` (le QUOI, les critères de filtrage).
- Le tri (`orderBy`, `orderDirection`).
- La pagination (`page`, `limit`).

Avantage : L'interface du Repository est simplifiée à l'extrême (`findByQuery(Query $query)`). On passe un seul objet cohérent au lieu d'une multitude de paramètres qui pourraient être inconsistants entre eux. C'est plus propre et plus expressif.
```

---

## Impact sur les tests

[Explique comment Repository + Specification améliorent la testabilité]

**À compléter ici :**
```
Avant (DQL partout) :
- Impossible de tester la logique de filtrage sans se connecter à une base de données.
- Tests d'intégration lents et fragiles, dépendants d'un état de la BDD.
- Difficile d'isoler les cas de test pour chaque critère de recherche.

Après (Specification + Repository) :
- Chaque Specification est testable unitairement et en mémoire.
  Ex: `$spec = new AuthorSpecification($user); self::assertTrue($spec->isSatisfiedBy($document));`
- Le Use Case est testé en mockant l'interface du Repository, sans aucune dépendance à Doctrine ou à la BDD.
- Les tests sont extrêmement rapides, isolés et fiables.
- L'ajout d'un nouveau critère de recherche n'impacte que son propre test, les autres ne changent pas.
```

---

## Extensibilité

**Pour ajouter un nouveau critère de recherche (ex: FileSize) :**

[Liste les étapes]

**À compléter ici :**
```
1. Créer la classe `FileSizeSpecification implements SpecificationInterface`.
2. Implémenter la méthode `isSatisfiedBy(Document $doc): bool` avec la logique métier (ex: `$doc->getSize() > $this->minSize`).
3. Ajouter la méthode `visitFileSize(FileSizeSpecification $spec)` dans `DoctrineSpecificationVisitor` pour la traduire en DQL (`$qb->andWhere('d.size > :minSize')`).
4. Créer le test unitaire `FileSizeSpecificationTest` pour valider la logique en mémoire.
5. C'est tout. Aucune modification du Repository, du Use Case, ou des autres Specifications.
Temps estimé : 10-15 minutes.
```

**Pour changer de source de données (ex: Elasticsearch) :**

[Explique les étapes]

**À compléter ici :**
```
1. Créer une nouvelle classe `ElasticsearchDocumentRepository` qui implémente `DocumentRepositoryInterface`.
2. Créer un `ElasticsearchSpecificationVisitor` qui traduit les mêmes objets Specification en requêtes JSON pour Elasticsearch.
3. Dans le conteneur de services (`services.yaml`), changer le binding de `DocumentRepositoryInterface` pour qu'il pointe vers `ElasticsearchDocumentRepository`.
4. Le Use Case, les Specifications, les contrôleurs et tout le code métier restent INCHANGÉS. Zéro modification.
Temps estimé : 2-3 jours (pour une implémentation robuste) contre plusieurs semaines ou mois de réécriture complète.
```

---

## Métriques de décision

| Métrique | DQL partout | Repository basique | Repository + Specification |
|----------|-------------|-------------------|---------------------------|
| Nombre de classes | 2 | 5 | 15+ |
| Méthodes repository | 1-2 | 50+ | 3 |
| Couplage à Doctrine (Use Case) | Fort | Fort | Zéro |
| Temps ajout critère | ~30 min | ~20 min | ~10 min |
| Testabilité (1-10) | 3/10 | 5/10 | 9/10 |
| Flexibilité source données | 1/10 | 3/10 | 10/10 |

---

## Notes d'implémentation

- Pattern reconnu en Domain-Driven Design (Evans, Fowler)
- Compatible avec Doctrine mais découplé
- Peut sembler "over-engineering" pour des requêtes simples (3-4 critères max)
- Devient indispensable dès que les requêtes se complexifient (6+ critères)
- Le Visitor Pattern demande une courbe d'apprentissage

---

## Références

- [Martin Fowler - Repository Pattern](https://martinfowler.com/eaaCatalog/repository.html)
- [Martin Fowler - Specification Pattern](https://martinfowler.com/apsupp/spec.pdf)
- [Eric Evans - Domain-Driven Design](https://www.domainlanguage.com/ddd/)
- [Doctrine Query Builder](https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/query-builder.html)
