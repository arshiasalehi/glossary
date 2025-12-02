<?php
require_once __DIR__ . '/../config.php';

class Glossary
{
    private PDO $pdo;
    private string $model;
    private ?string $apiKey;

    public function __construct(string $model, ?string $apiKey)
    {
        $this->pdo = $this->connect();
        $this->model = $model;
        $this->apiKey = $apiKey;
    }

    private function connect(): PDO
    {
        $dbHost = env('DB_HOST', '127.0.0.1');
        $dbPort = env('DB_PORT', '3306');
        $dbName = env('DB_NAME', 'glossary');
        $dbUser = env('DB_USER', 'root');
        $dbPass = env('DB_PASS', '');
        $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
        return new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    public function countTerms(): int
    {
        try {
            return (int)$this->pdo->query("SELECT COUNT(*) FROM terms")->fetchColumn();
        } catch (Throwable) {
            return 0;
        }
    }

    public function seedSampleTerms(): void
    {
        $sample = [
            ['API', 'Application Programming Interface', 'Ensemble de règles et de protocoles permettant à des applications de communiquer entre elles.', 'Set of rules and protocols allowing applications to communicate with each other.'],
            ['Base de données', 'Database', 'Collection organisée de données structurées permettant le stockage, la récupération et la manipulation efficace des informations.', 'Organized collection of structured data allowing efficient storage, retrieval, and manipulation of information.'],
            ['SQL', 'Structured Query Language', 'Langage standardisé pour gérer les bases de données relationnelles.', 'Standardized language for managing relational databases.'],
            ['HTTP', 'HyperText Transfer Protocol', 'Protocole de communication utilisé pour transférer des données sur le web.', 'Communication protocol used to transfer data on the web.'],
            ['Encapsulation', 'Encapsulation', "Principe de la POO qui consiste à cacher les détails d'implémentation d'une classe.", 'OOP principle that hides implementation details of a class.'],
            ['Héritage', 'Inheritance', "Mécanisme de la POO permettant à une classe d'hériter des propriétés d'une autre classe.", 'OOP mechanism allowing a class to inherit properties from another class.'],
            ['Polymorphisme', 'Polymorphism', "Capacité d'un objet à prendre plusieurs formes.", 'Ability of an object to take multiple forms.'],
            ['MVC', 'Model-View-Controller', "Pattern architectural séparant la logique métier, l'interface utilisateur et la gestion des interactions.", 'Architectural pattern separating business logic, user interface, and interaction management.'],
            ['Firewall', 'Firewall', 'Système de sécurité réseau contrôlant le trafic entrant et sortant.', 'Network security system controlling incoming and outgoing traffic.'],
            ['Algorithme', 'Algorithm', "Ensemble d'instructions finies et précises permettant de résoudre un problème.", 'Finite and precise set of instructions to solve a problem.'],
        ];
        $stmt = $this->pdo->prepare(
            'INSERT INTO terms (french_term, english_term, french_definition, english_definition)
             VALUES (:fr, :en, :def_fr, :def_en)
             ON DUPLICATE KEY UPDATE
                french_definition = VALUES(french_definition),
                english_definition = VALUES(english_definition)'
        );
        foreach ($sample as [$fr, $en, $defFr, $defEn]) {
            $stmt->execute([
                'fr' => $fr,
                'en' => $en,
                'def_fr' => $defFr,
                'def_en' => $defEn,
            ]);
        }
    }

    public function findTerm(string $term, string $direction): ?array
    {
        $column = $direction === 'fr_to_en' ? 'french_term' : 'english_term';
        $stmt = $this->pdo->prepare("SELECT * FROM terms WHERE LOWER($column) = LOWER(:term) LIMIT 1");
        $stmt->execute(['term' => $term]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function saveTerm(array $data): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO terms (french_term, english_term, french_definition, english_definition)
             VALUES (:french_term, :english_term, :french_definition, :english_definition)
             ON DUPLICATE KEY UPDATE
                french_definition = VALUES(french_definition),
                english_definition = VALUES(english_definition)'
        );
        $stmt->execute([
            'french_term' => $data['french_term'],
            'english_term' => $data['english_term'],
            'french_definition' => $data['french_definition'] ?? null,
            'english_definition' => $data['english_definition'] ?? null,
        ]);
    }

    public function createTerm(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO terms (french_term, english_term, french_definition, english_definition)
             VALUES (:french_term, :english_term, :french_definition, :english_definition)'
        );
        $stmt->execute([
            'french_term' => $data['french_term'] ?? '',
            'english_term' => $data['english_term'] ?? '',
            'french_definition' => $data['french_definition'] ?? null,
            'english_definition' => $data['english_definition'] ?? null,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function updateTerm(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE terms
             SET french_term = :french_term,
                 english_term = :english_term,
                 french_definition = :french_definition,
                 english_definition = :english_definition
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'french_term' => $data['french_term'] ?? '',
            'english_term' => $data['english_term'] ?? '',
            'french_definition' => $data['french_definition'] ?? null,
            'english_definition' => $data['english_definition'] ?? null,
        ]);
    }

    public function getTerm(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM terms WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function lookupWithAI(string $term, string $direction): array
    {
        if (!$this->apiKey) {
            throw new RuntimeException('API key not configured server-side');
        }
        $prompt = [
            'contents' => [[
                'parts' => [[
                    'text' => "Translate and define the given term for a glossary.\n" .
                        "Direction: " . ($direction === 'fr_to_en' ? 'French to English' : 'English to French') . "\n" .
                        "Return ONLY a JSON object with keys: french_term, english_term, french_definition, english_definition.\n" .
                        "No extra text, no markdown, no explanations.\n" .
                        "Term: \"{$term}\""
                ]]
            ]]
        ];

        $ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key=" . urlencode($this->apiKey));
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($prompt),
        ]);
        $raw = curl_exec($ch);
        if ($raw === false) {
            throw new RuntimeException('Gemini request failed: ' . curl_error($ch));
        }
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $data = json_decode($raw, true);
        if ($code >= 400) {
            $msg = $data['error']['message'] ?? ("HTTP $code");
            throw new RuntimeException("Gemini error: $msg");
        }
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $json = json_decode($text, true);
        if (!is_array($json) && preg_match('/\\{.*\\}/s', $text, $m)) {
            $json = json_decode($m[0], true);
        }
        if (!is_array($json)) {
            throw new RuntimeException('Gemini returned unparsable data');
        }
        return [
            'french_term' => $json['french_term'] ?? ($direction === 'fr_to_en' ? $term : ($json['french'] ?? $json['fr'] ?? '')),
            'english_term' => $json['english_term'] ?? ($direction === 'en_to_fr' ? $term : ($json['english'] ?? $json['en'] ?? '')),
            'french_definition' => $json['french_definition'] ?? ($json['definition_fr'] ?? ''),
            'english_definition' => $json['english_definition'] ?? ($json['definition_en'] ?? ''),
            'raw' => $text,
        ];
    }

    public function listTerms(?string $q = null): array
    {
        if ($q !== null && trim($q) !== '') {
            $like = '%' . trim($q) . '%';
            $stmt = $this->pdo->prepare(
                'SELECT * FROM terms WHERE french_term LIKE :q OR english_term LIKE :q ORDER BY id DESC'
            );
            $stmt->execute(['q' => $like]);
            return $stmt->fetchAll();
        }
        $stmt = $this->pdo->query('SELECT * FROM terms ORDER BY id DESC');
        return $stmt->fetchAll();
    }

    public function deleteTerm(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM terms WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
