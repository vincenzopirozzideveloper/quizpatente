<?php

namespace App\Filament\Pages\Theory;

use Filament\Pages\Page;
use App\Models\Topic;
use App\Models\TheoryContent;
use App\Models\UserTheoryProgress;
use Livewire\Attributes\Url;
use Illuminate\Support\Collection;

class TheoryView extends Page
{
    protected static string $view = 'filament.pages.theory.theory-view';
    protected static bool $shouldRegisterNavigation = false;
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
    public $isSidebarOpen = true;
    public Collection $userProgress;
    public $contentStartTime;

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
        
        // Carica lo stato di lettura dell'utente
        $this->loadUserProgress();
        
        // Se non è specificato un sottoargomento, usa il primo disponibile
        if (!$this->subtopicId && $this->subtopics->isNotEmpty()) {
            $this->subtopicId = $this->subtopics->first()->id;
        }
        
        if ($this->subtopicId) {
            $this->loadSubtopic($this->subtopicId);
        }
    }

    protected function loadUserProgress(): void
    {
        $contentIds = TheoryContent::whereIn('subtopic_id', $this->subtopics->pluck('id'))
            ->published()
            ->pluck('id');
            
        $this->userProgress = UserTheoryProgress::where('user_id', auth()->id())
            ->whereIn('theory_content_id', $contentIds)
            ->get()
            ->keyBy('theory_content_id');
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
        
        // Salva il tempo trascorso sul contenuto precedente
        if ($this->currentContent && $this->contentStartTime) {
            $this->updateTimeSpent($this->currentContent->id);
        }
        
        $this->contentId = $contentId;
        $this->currentContent = $this->contents->find($contentId);
        
        if ($this->currentContent) {
            $this->currentContentIndex = $this->contents->search(function ($item) use ($contentId) {
                return $item->id == $contentId;
            });
            
            // Marca come "in lettura"
            UserTheoryProgress::markAsReading(auth()->id(), $contentId);
            $this->contentStartTime = now();
            
            // Ricarica lo stato di progresso
            $this->loadUserProgress();
        }
    }

    public function navigateToContent($contentId): void
    {
        $content = TheoryContent::find($contentId);
        if ($content && $content->subtopic_id !== $this->subtopicId) {
            $this->loadSubtopic($content->subtopic_id);
        }
        $this->loadContent($contentId);
    }

    public function toggleContentReadStatus($contentId): void
    {
        UserTheoryProgress::toggleReadStatus(auth()->id(), $contentId);
        $this->loadUserProgress();
    }

    public function markCurrentAsRead(): void
    {
        if ($this->currentContent) {
            UserTheoryProgress::markAsRead(auth()->id(), $this->currentContent->id);
            $this->loadUserProgress();
        }
    }

    public function nextContent(): void
    {
        // Marca automaticamente come letto il contenuto corrente
        $this->markCurrentAsRead();
        
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

    public function toggleSidebar(): void
    {
        $this->isSidebarOpen = !$this->isSidebarOpen;
    }

    protected function updateTimeSpent($contentId): void
    {
        $progress = UserTheoryProgress::where('user_id', auth()->id())
            ->where('theory_content_id', $contentId)
            ->first();
            
        if ($progress) {
            $timeSpent = now()->diffInSeconds($this->contentStartTime);
            $progress->increment('time_spent', $timeSpent);
        }
    }

    public function getProgressStats(): array
    {
        $totalContents = 0;
        $readContents = 0;
        $readingContents = 0;
        
        foreach ($this->subtopics as $subtopic) {
            $totalContents += $subtopic->theory_contents_count;
        }
        
        $readContents = $this->userProgress->where('status', 'read')->count();
        $readingContents = $this->userProgress->where('status', 'reading')->count();
        
        return [
            'total' => $totalContents,
            'read' => $readContents,
            'reading' => $readingContents,
            'unread' => $totalContents - $readContents - $readingContents,
            'percentage' => $totalContents > 0 ? round(($readContents / $totalContents) * 100, 1) : 0,
        ];
    }

    public function getContentStatus($contentId): string
    {
        return $this->userProgress->get($contentId)?->status ?? 'unread';
    }

    public function getTitle(): string
    {
        return $this->topic ? $this->topic->name : 'Teoria';
    }

    public function backToIndex(): void
    {
        // Salva il tempo dell'ultimo contenuto
        if ($this->currentContent && $this->contentStartTime) {
            $this->updateTimeSpent($this->currentContent->id);
        }
        
        $this->redirect(TheoryIndex::getUrl());
    }

    public function dehydrate(): void
    {
        // Salva il tempo quando il componente viene distrutto
        if ($this->currentContent && $this->contentStartTime) {
            $this->updateTimeSpent($this->currentContent->id);
        }
    }
}