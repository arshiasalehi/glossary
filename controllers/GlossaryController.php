<?php
require_once __DIR__ . '/../models/Glossary.php';

class GlossaryController
{
    private Glossary $glossary;

    public function __construct(Glossary $glossary)
    {
        $this->glossary = $glossary;
    }

    public function handle(string $action, array $input): void
    {
        if ($action === 'ping') {
            $this->respond(['ok' => true, 'model' => env('GEMINI_MODEL', 'gemini-2.5-flash')]);
        }

        if ($action === 'lookup') {
            $term = trim($input['term'] ?? '');
            $direction = $input['direction'] ?? 'fr_to_en';
            if ($term === '') {
                $this->respond(['error' => 'Term is required'], 400);
            }
            if (!in_array($direction, ['fr_to_en', 'en_to_fr'], true)) {
                $this->respond(['error' => 'Invalid direction'], 400);
            }
            try {
                $found = $this->glossary->findTerm($term, $direction);
                if ($found) {
                    $this->respond(['source' => 'database', 'entry' => $found]);
                }
                $ai = $this->glossary->lookupWithAI($term, $direction);
                $entry = [
                    'french_term' => $ai['french_term'] ?: ($direction === 'en_to_fr' ? '' : $term),
                    'english_term' => $ai['english_term'] ?: ($direction === 'fr_to_en' ? '' : $term),
                    'french_definition' => $ai['french_definition'] ?: null,
                    'english_definition' => $ai['english_definition'] ?: null,
                    'category' => $ai['category'] ?? null,
                ];
                $this->glossary->saveTerm($entry);
                $this->respond(['source' => 'ai', 'entry' => $entry, 'raw' => $ai['raw'] ?? null]);
            } catch (Throwable $e) {
                $this->respond(['error' => $e->getMessage()], 500);
            }
        }

        if ($action === 'list_terms') {
            $q = $input['q'] ?? null;
            $cat = $input['category'] ?? null;
            $this->respond(['terms' => $this->glossary->listTerms($q, $cat)]);
        }

        if ($action === 'create_term') {
            $id = $this->glossary->createTerm($input);
            $this->respond(['ok' => true, 'id' => $id]);
        }

        if ($action === 'update_term') {
            $id = (int)($input['id'] ?? 0);
            if ($id <= 0) {
                $this->respond(['error' => 'Invalid id'], 400);
            }
            $this->glossary->updateTerm($id, $input);
            $this->respond(['ok' => true]);
        }

        if ($action === 'delete_term') {
            $id = (int)($input['id'] ?? 0);
            if ($id <= 0) {
                $this->respond(['error' => 'Invalid id'], 400);
            }
            $this->glossary->deleteTerm($id);
            $this->respond(['ok' => true]);
        }

        $this->respond(['error' => 'Unknown action'], 404);
    }

    private function respond(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }
}
