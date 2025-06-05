<?php

namespace App\Filament\Pages\Theory;

use Filament\Pages\Page;
use App\Models\Topic;
use Livewire\Attributes\Url;

class TheoryView extends Page
{
    protected static string $view = 'filament.pages.theory.theory-view';
    protected static bool $shouldRegisterNavigation = false;
    
    #[Url]
    public $topicId;
    
    #[Url]
    public $subtopicId;
    
    public $topic;
    public $subtopics;
    public $currentSubtopic;
    public $contents;
    public $activeContentId;

    public function mount(): void
    {
        if (!$this->topicId) {
            $this->redirect(route('filament.pages.theory.index'));
            return;
        }

        $this->topic = Topic::with('subtopics.theoryContents')->findOrFail($this->topicId);
        $this->subtopics = $this->topic->subtopics()->active()->ordered()->get();
        
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
                
            if ($this->contents->isNotEmpty() && !$this->activeContentId) {
                $this->activeContentId = $this->contents->first()->id;
            }
        }
    }

    public function setActiveContent($contentId): void
    {
        $this->activeContentId = $contentId;
    }

    public function getTitle(): string
    {
        return $this->topic ? $this->topic->name : 'Teoria';
    }
}