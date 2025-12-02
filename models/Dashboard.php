<?php
require_once __DIR__ . '/Glossary.php';

class Dashboard
{
    private Glossary $glossary;

    public function __construct(Glossary $glossary)
    {
        $this->glossary = $glossary;
    }

    public function termCount(): int
    {
        return $this->glossary->countTerms();
    }
}
