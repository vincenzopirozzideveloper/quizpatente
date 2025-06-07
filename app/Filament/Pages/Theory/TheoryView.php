<?php

namespace App\Filament\Pages\Theory;

use Filament\Pages\Page;
use App\Models\Topic;
use App\Models\TheoryContent;
use Livewire\Attributes\Url;

class TheoryView extends Page
{
    protected static string $view = 'filament.pages.theory.theory-view';
    protected static bool $shouldRegisterNavigation = false;
    
    // Aggiungiamo slug e route personalizzata
    protected static ?string $slug = 'theory/view';
    
    #[Url]
    public $topicId;
    
    #[Url]
    public $subtopicId;
    
    #[Url]
    public $contentId;
    
    public $topic;
    public $subtopics;
    public $currentSubtopic;
    public $contents;
    public $currentContent;
    public $currentContentIndex = 0;
    public $isMenuOpen = false;
    public $readContents = [];

    public function mount(): void
    {
        if (!$this->topicId) {
            $this->redirect(TheoryIndex::getUrl());
            return;
        }

        $this->topic = Topic::with(['subtopics' => function ($query) {
            $query->active()
                  ->ordered()
                  ->withCount(['theoryContents' => function ($q) {
                      $q->published();
                  }]);
        }])->findOrFail($this->topicId);
        
        $this->subtopics = $this->topic->subtopics;
        
        // Se non è specificato un sottoargomento, usa il primo disponibile
        if (!$this->subtopicId && $this->subtopics->isNotEmpty()) {
            $this->subtopicId = $this->subtopics->first()->id;
        }
        
        if ($this->subtopicId) {
            $this->loadSubtopic($this->subtopicId);
        }
    }

    public function loadSubtopic($subtopicId): void
    {
        $this->subtopicId = $subtopicId;
        $this->currentSubtopic = $this->subtopics->find($subtopicId);
        
        if ($this->currentSubtopic) {
            $this->contents = $this->currentSubtopic->theoryContents()
                ->published()
                ->ordered()
                ->get();
            
            // Se non è specificato un contenuto o non esiste, usa il primo
            if (!$this->contentId || !$this->contents->contains('id', $this->contentId)) {
                $this->contentId = $this->contents->first()?->id;
            }
            
            $this->loadContent($this->contentId);
        }
    }

    public function loadContent($contentId): void
    {
        if (!$contentId) return;
        
        $this->contentId = $contentId;
        $this->currentContent = $this->contents->find($contentId);
        
        if ($this->currentContent) {
            $this->currentContentIndex = $this->contents->search(function ($item) use ($contentId) {
                return $item->id == $contentId;
            });
            
            // Marca come letto
            if (!in_array($contentId, $this->readContents)) {
                $this->readContents[] = $contentId;
            }
        }
    }

    public function navigateToContent($contentId): void
    {
        $content = TheoryContent::find($contentId);
        if ($content && $content->subtopic_id !== $this->subtopicId) {
            $this->loadSubtopic($content->subtopic_id);
        }
        $this->loadContent($contentId);
        $this->isMenuOpen = false;
    }

    public function nextContent(): void
    {
        if ($this->currentContentIndex < $this->contents->count() - 1) {
            $nextContent = $this->contents[$this->currentContentIndex + 1];
            $this->loadContent($nextContent->id);
        } else {
            // Passa al prossimo sottoargomento
            $currentSubtopicIndex = $this->subtopics->search(function ($item) {
                return $item->id == $this->subtopicId;
            });
            
            if ($currentSubtopicIndex !== false && $currentSubtopicIndex < $this->subtopics->count() - 1) {
                $nextSubtopic = $this->subtopics[$currentSubtopicIndex + 1];
                $this->loadSubtopic($nextSubtopic->id);
            }
        }
    }

    public function previousContent(): void
    {
        if ($this->currentContentIndex > 0) {
            $prevContent = $this->contents[$this->currentContentIndex - 1];
            $this->loadContent($prevContent->id);
        } else {
            // Passa al sottoargomento precedente
            $currentSubtopicIndex = $this->subtopics->search(function ($item) {
                return $item->id == $this->subtopicId;
            });
            
            if ($currentSubtopicIndex !== false && $currentSubtopicIndex > 0) {
                $prevSubtopic = $this->subtopics[$currentSubtopicIndex - 1];
                $this->loadSubtopic($prevSubtopic->id);
                // Vai all'ultimo contenuto del sottoargomento precedente
                if ($this->contents->isNotEmpty()) {
                    $this->loadContent($this->contents->last()->id);
                }
            }
        }
    }

    public function toggleMenu(): void
    {
        $this->isMenuOpen = !$this->isMenuOpen;
    }

    public function getProgressPercentage(): float
    {
        $totalContents = 0;
        $readCount = 0;
        
        foreach ($this->subtopics as $subtopic) {
            $totalContents += $subtopic->theory_contents_count;
        }
        
        $readCount = count($this->readContents);
        
        return $totalContents > 0 ? round(($readCount / $totalContents) * 100, 1) : 0;
    }

    public function getTitle(): string
    {
        return $this->topic ? $this->topic->name : 'Teoria';
    }

    public function backToIndex(): void
    {
        $this->redirect(TheoryIndex::getUrl());
    }
}