<?php
require_once __DIR__ . '/../config.php';

class Glossary
{
    public const CATEGORIES = ['Networking', 'Security', 'Databases', 'Programming', 'AI/ML'];

    private string $model;
    private ?string $apiKey;
    private string $dbUrl;
    private ?string $dbAuth;
    private ?string $firebaseApiKey;
    private ?string $idToken = null;

    public function __construct(string $model, ?string $apiKey)
    {
        $this->model = $model;
        $this->apiKey = $apiKey;
        $this->dbUrl = rtrim(env('FIREBASE_DB_URL', 'https://final-8e953-default-rtdb.firebaseio.com'), '/');
        $this->dbAuth = env('FIREBASE_DB_AUTH', null); // optional auth token
        $this->firebaseApiKey = env('FIREBASE_API_KEY', null);
    }

    private function firebaseRequest(string $method, string $path, ?array $data = null): mixed
    {
        $url = $this->dbUrl . '/' . ltrim($path, '/');
        $params = [];
        $authToken = $this->getAuthToken();
        if ($authToken) {
            $params['auth'] = $authToken;
        }
        if ($params) {
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($params);
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        $raw = curl_exec($ch);
        if ($raw === false) {
            throw new RuntimeException('Firebase request failed: ' . curl_error($ch));
        }
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $decoded = json_decode($raw, true);
        if ($code >= 400) {
            $msg = $decoded['error'] ?? ("HTTP $code");
            throw new RuntimeException("Firebase error: $msg");
        }
        return $decoded;
    }

    private function getAuthToken(): ?string
    {
        if ($this->dbAuth) {
            return $this->dbAuth;
        }
        if ($this->idToken) {
            return $this->idToken;
        }
        if (!$this->firebaseApiKey) {
            return null; // will likely 401 if rules require auth
        }
        // Anonymous sign-up to obtain idToken
        $ch = curl_init("https://identitytoolkit.googleapis.com/v1/accounts:signUp?key=" . urlencode($this->firebaseApiKey));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode(new stdClass()),
        ]);
        $raw = curl_exec($ch);
        if ($raw === false) {
            throw new RuntimeException('Firebase auth failed: ' . curl_error($ch));
        }
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $data = json_decode($raw, true);
        if ($code >= 400) {
            $msg = $data['error']['message'] ?? ("HTTP $code");
            throw new RuntimeException("Firebase auth error: $msg");
        }
        $this->idToken = $data['idToken'] ?? null;
        return $this->idToken;
    }

    private function allTerms(): array
    {
        $data = $this->firebaseRequest('GET', 'terms.json') ?? [];
        $terms = [];
        if (is_array($data)) {
            foreach ($data as $id => $row) {
                if (!is_array($row)) {
                    continue;
                }
                $row['id'] = $id;
                $terms[] = $row;
            }
        }
        return $terms;
    }

    public function countTerms(): int
    {
        return count($this->allTerms());
    }

    public function seedSampleTerms(): void
    {
        $sample = [
            ['API', 'Application Programming Interface', 'Ensemble de règles et de protocoles permettant à des applications de communiquer entre elles.', 'Set of rules and protocols allowing applications to communicate with each other.', 'Programming'],
            ['Base de données', 'Database', 'Collection organisée de données structurées permettant le stockage, la récupération et la manipulation efficace des informations.', 'Organized collection of structured data allowing efficient storage, retrieval, and manipulation of information.', 'Databases'],
            ['SQL', 'Structured Query Language', 'Langage standardisé pour gérer les bases de données relationnelles.', 'Standardized language for managing relational databases.', 'Databases'],
            ['HTTP', 'HyperText Transfer Protocol', 'Protocole de communication utilisé pour transférer des données sur le web.', 'Communication protocol used to transfer data on the web.', 'Networking'],
            ['Encapsulation', 'Encapsulation', "Principe de la POO qui consiste à cacher les détails d'implémentation d'une classe.", 'OOP principle that hides implementation details of a class.', 'Programming'],
            ['Héritage', 'Inheritance', "Mécanisme de la POO permettant à une classe d'hériter des propriétés d'une autre classe.", 'OOP mechanism allowing a class to inherit properties from another class.', 'Programming'],
            ['Polymorphisme', 'Polymorphism', "Capacité d'un objet à prendre plusieurs formes.", 'Ability of an object to take multiple forms.', 'Programming'],
            ['MVC', 'Model-View-Controller', "Pattern architectural séparant la logique métier, l'interface utilisateur et la gestion des interactions.", 'Architectural pattern separating business logic, user interface, and interaction management.', 'Programming'],
            ['Firewall', 'Firewall', 'Système de sécurité réseau contrôlant le trafic entrant et sortant.', 'Network security system controlling incoming and outgoing traffic.', 'Security'],
            ['Algorithme', 'Algorithm', "Ensemble d'instructions finies et précises permettant de résoudre un problème.", 'Finite and precise set of instructions to solve a problem.', 'AI/ML'],
        ];
        $existing = $this->allTerms();
        if (!empty($existing)) {
            return;
        }
        foreach ($sample as [$fr, $en, $defFr, $defEn, $cat]) {
            $this->createTerm([
                'french_term' => $fr,
                'english_term' => $en,
                'french_definition' => $defFr,
                'english_definition' => $defEn,
                'category' => $cat,
            ]);
        }
    }

    public function findTerm(string $term, string $direction): ?array
    {
        $column = $direction === 'fr_to_en' ? 'french_term' : 'english_term';
        $terms = $this->allTerms();
        foreach ($terms as $row) {
            $value = $row[$column] ?? '';
            if (mb_strtolower($value) === mb_strtolower($term)) {
                return $row;
            }
        }
        return null;
    }

    public function saveTerm(array $data): void
    {
        $existing = $this->findTerm($data['french_term'] ?? '', 'fr_to_en') ?? $this->findTerm($data['english_term'] ?? '', 'en_to_fr');
        if ($existing) {
            $this->updateTerm((string)($existing['id'] ?? ''), $data);
            return;
        }
        $this->createTerm($data);
    }

    public function createTerm(array $data): string
    {
        $payload = [
            'french_term' => $data['french_term'] ?? '',
            'english_term' => $data['english_term'] ?? '',
            'french_definition' => $data['french_definition'] ?? null,
            'english_definition' => $data['english_definition'] ?? null,
            'category' => $this->sanitizeCategory($data['category'] ?? null),
        ];
        $resp = $this->firebaseRequest('POST', 'terms.json', $payload);
        $id = $resp['name'] ?? null;
        return $id ? (string)$id : '';
    }

    public function updateTerm(string $id, array $data): void
    {
        $payload = [
            'french_term' => $data['french_term'] ?? '',
            'english_term' => $data['english_term'] ?? '',
            'french_definition' => $data['french_definition'] ?? null,
            'english_definition' => $data['english_definition'] ?? null,
            'category' => $this->sanitizeCategory($data['category'] ?? null),
        ];
        $this->firebaseRequest('PATCH', "terms/{$id}.json", $payload);
    }

    public function getTerm(string $id): ?array
    {
        foreach ($this->allTerms() as $row) {
            if ((string)($row['id'] ?? '') === (string)$id) {
                return $row;
            }
        }
        return null;
    }

    public function lookupWithAI(string $term, string $direction): array
    {
        if (!$this->apiKey) {
            throw new RuntimeException('API key not configured server-side');
        }
        $categoriesList = implode(', ', self::CATEGORIES);
        $prompt = [
            'contents' => [[
                'parts' => [[
                    'text' => "Translate and define the given term for a glossary.\n" .
                        "Direction: " . ($direction === 'fr_to_en' ? 'French to English' : 'English to French') . "\n" .
                        "Assign ONE category from this fixed set: {$categoriesList}.\n" .
                        "Return ONLY a JSON object with keys: french_term, english_term, french_definition, english_definition, category.\n" .
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
            'category' => $this->sanitizeCategory($json['category'] ?? null),
            'raw' => $text,
        ];
    }

    public function listTerms(?string $q = null, ?string $category = null): array
    {
        $qTrim = $q !== null ? trim($q) : '';
        $cat = $this->sanitizeCategory($category);
        $terms = $this->allTerms();
        $filtered = [];
        foreach ($terms as $term) {
            $match = true;
            if ($qTrim !== '') {
                $hay = mb_strtolower(($term['french_term'] ?? '') . ' ' . ($term['english_term'] ?? ''));
                if (!str_contains($hay, mb_strtolower($qTrim))) {
                    $match = false;
                }
            }
            if ($cat) {
                if (($term['category'] ?? null) !== $cat) {
                    $match = false;
                }
            }
            if ($match) {
                $filtered[] = $term;
            }
        }
        usort($filtered, fn($a, $b) => strcmp((string)($b['id'] ?? ''), (string)($a['id'] ?? '')));
        return $filtered;
    }

    public function deleteTerm(string $id): void
    {
        $this->firebaseRequest('DELETE', "terms/{$id}.json");
    }

    private function sanitizeCategory(?string $category): ?string
    {
        if (!$category) {
            return null;
        }
        $category = trim($category);
        return in_array($category, self::CATEGORIES, true) ? $category : null;
    }
}
