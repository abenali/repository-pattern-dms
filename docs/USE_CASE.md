# **🎯 USE CASE #3 : DOCUMENT MANAGEMENT SYSTEM (DMS)**

## **🎭 Contexte métier**

Tu travailles pour **DocHub**, une plateforme SaaS de gestion documentaire pour entreprises.
Les clients stockent des milliers de documents (contrats, factures, rapports, présentations) et ont besoin de :

* **Rechercher** avec des critères complexes
* **Filtrer** selon plusieurs dimensions (auteur, date, tags, type, statut, taille)
* **Trier** (date, nom, taille)
* **Paginer** (afficher 20 résultats / page)

**Problème actuel** : Le code a des requêtes DQL partout, difficile à maintenir et à tester.

---

## **📊 User Stories**

**US1 : En tant qu'utilisateur, je veux chercher mes documents selon des critères multiples**

**Critères d'acceptation :**

* Recherche par auteur
* Recherche par statut (draft, pending, approved, archived)
* Recherche par tags (un ou plusieurs)
* Recherche par période (createdAfter, createdBefore)
* Recherche par type de fichier (PDF, DOCX, XLSX, etc.)
* Combinaison de critères avec AND/OR

**US2 : En tant qu'utilisateur, je veux trier et paginer les résultats**

**Critères d'acceptation :**

* Tri par date de création (ASC/DESC)
* Tri par nom (ASC/DESC)
* Tri par taille (ASC/DESC)
* Pagination (20 résultats par page)
* Retourne le nombre total de résultats

**US3 : En tant que développeur, je veux réutiliser les filtres facilement**

**Critères d'acceptation :**

* Les specifications sont réutilisables
* Les specifications sont combinables (AND, OR, NOT)
* Facile d'ajouter un nouveau critère sans modifier le repository
* Chaque specification est testable unitairement

**US4 : En tant qu'architecte, je veux découpler la logique métier de Doctrine**

**Critères d'acceptation :**

* Le Use Case ne dépend PAS de Doctrine
* Le Use Case dépend de DocumentRepositoryInterface
* On peut remplacer Doctrine par Elasticsearch sans modifier le Use Case
* PHPStan niveau 6 : ✅


## **🏗️ Contraintes techniques**

### **Architecture**

* **Clean Architecture** (4 couches)
* **Repository Pattern** + **Specification Pattern**
* **Query Object Pattern** pour tri/pagination
* Pas de DQL dans les Use Cases

### **Stack**

1. Symfony 7.3
2. PHP 8.2
3. PostgreSQL
4. Doctrine (mais abstrait derrière Repository)
5. PHPUnit

### **Qualité**

* PHPStan niveau 6 : ✅
* Tests unitaires sur chaque Specification
* Tests d'intégration sur le Repository

---

## **📐 Spécifications fonctionnelles**

### Entités métier

**Document**

* `id`: UUID
* `title`: string
* `author`: User
* `status`: DocumentStatus (enum: DRAFT, PENDING, APPROVED, ARCHIVED)
* `fileType`: string (pdf, docx, xlsx, etc.)
* `fileSize`: int (bytes)
* `tags`: array<string>
* `createdAt`: DateTimeImmutable
* `updatedAt`: DateTimeImmutable

**User (simplifié)**

* `id`: UUID
* `name`: string
* `email`: string

**DocumentStatus (Enum)**

* DRAFT
* PENDING
* APPROVED
* ARCHIVED

---

## 🎯 Architecture attendue

### Specifications (Domain)
```
interface SpecificationInterface
{
    public function isSatisfiedBy(Document $document): bool;
}
```

**Specifications concrètes :**

* `AuthorSpecification`
* `StatusSpecification`
* `TagsSpecification`
* `CreatedAfterSpecification`
* `CreatedBeforeSpecification`
* `FileTypeSpecification`
* `AndSpecification` (composite)
* `OrSpecification` (composite)
* `NotSpecification` (composite)

### Query Object (Domain)
```
class DocumentQuery
{
    public function __construct(
        public readonly ?SpecificationInterface $specification = null,
        public readonly ?string $orderBy = null,
        public readonly ?string $orderDirection = 'ASC',
        public readonly int $page = 1,
        public readonly int $limit = 20
    ) {}
}
```

### Repository (Domain Interface)
```
interface DocumentRepositoryInterface
{
    public function findById(string $id): Document;
    public function save(Document $document): void;

    // Specification Pattern
    public function findByQuery(DocumentQuery $query): PaginatedResult;
    
    // Count pour la pagination
    public function countBySpecification(?SpecificationInterface $spec): int;
}
```

### **Use Case (Application)**
```
class SearchDocumentsHandler
{
    public function execute(SearchDocumentsCommand $command): SearchDocumentsResponse
    {
        // 1. Construire les specifications
        // 2. Créer le Query Object
        // 3. Appeler le repository
        // 4. Retourner les résultats paginés
    }
}
```

---

## 🔌 API REST attendue

### Endpoint : `GET /api/documents/search`

### **Query params :**
```
?authorId=uuid
&status=approved
&tags[]=finance&tags[]=urgent
&createdAfter=2024-01-01
&fileType=pdf
&orderBy=createdAt
&orderDirection=DESC
&page=1
&limit=20
```
### Response :
```
{
    "data": [
        {
            "id": "uuid",
            "title": "Contract Q4 2024",
            "author": {"id": "uuid", "name": "Alice"},
            "status": "approved",
            "fileType": "pdf",
            "fileSize": 2048576,
            "tags": ["finance", "urgent"],
            "createdAt": "2024-11-15T10:30:00Z"
        }
    ],
    "pagination": {
        "total": 150,
        "page": 1,
        "limit": 20,
        "totalPages": 8
    }
}
```
