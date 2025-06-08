<?php

namespace App\Filament\Pages\Theory;

use Filament\Pages\Page;
use App\Models\Topic;
use App\Models\TheoryContent;
use App\Models\UserTheoryProgress;
use Livewire\Attributes\Url;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;

class TheoryView extends Page
{
    protected static string $view = 'filament.pages.theory.theory-view';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $slug = 'theory/view';
    
    #[Url]
    public $topicId;
    
    #[Url]
    public $contentId;
    
    public $topic;
    public Collection $allContents;
    public $currentContent;
    public $currentIndex = 0;
    
    // View state
    public string $viewMode = 'grid'; // 'grid', 'list', 'focus'
    public bool $showProgress = true;
    
    // Real-time state
    public array $contentStatuses = [];
    public array $stats = [
        'total' => 0,
        'completed' => 0,
        'inProgress' => 0,
        'percentage' => 0
    ];
    
    // Filters
    public string $filterStatus = 'all'; // 'all', 'unread', 'reading', 'read'
    public string $searchQuery = '';
    
    protected $listeners = ['contentStatusUpdated' => 'refreshStats'];

    public function mount(): void
    {
        if (!$this->topicId) {
            $this->redirect(TheoryIndex::getUrl());
            return;
        }

        $this->topic = Topic::findOrFail($this->topicId);
        $this->loadAllContents();
        $this->loadUserProgress();
        
        // Se non c'è un contentId, trova il primo non letto
        if (!$this->contentId) {
            $firstUnread = $this->allContents->first(function ($content) {
                return ($this->contentStatuses[$content->id] ?? 'unread') !== 'read';
            });
            
            $this->contentId = $firstUnread?->id ?? $this->allContents->first()?->id;
        }
        
        if ($this->contentId) {
            $this->loadContent($this->contentId);
        }
    }

    protected function loadAllContents(): void
    {
        $this->allContents = TheoryContent::query()
            ->whereHas('subtopic', function ($query) {
                $query->where('topic_id', $this->topicId)
                      ->where('is_active', true);
            })
            ->with(['subtopic'])
            ->published()
            ->orderBy('subtopic_id')
            ->orderBy('order')
            ->get();
            
        $this->stats['total'] = $this->allContents->count();
    }

    protected function loadUserProgress(): void
    {
        $progress = UserTheoryProgress::where('user_id', auth()->id())
            ->whereIn('theory_content_id', $this->allContents->pluck('id'))
            ->get()
            ->keyBy('theory_content_id');
            
        foreach ($this->allContents as $content) {
            $this->contentStatuses[$content->id] = $progress->get($content->id)?->status ?? 'unread';
        }
        
        $this->calculateStats();
    }

    protected function calculateStats(): void
    {
        $this->stats['completed'] = collect($this->contentStatuses)->where('status', 'read')->count();
        $this->stats['inProgress'] = collect($this->contentStatuses)->where('status', 'reading')->count();
        $this->stats['percentage'] = $this->stats['total'] > 0 
            ? round(($this->stats['completed'] / $this->stats['total']) * 100)
            : 0;
    }

    public function loadContent($contentId): void
    {
        $this->contentId = $contentId;
        $this->currentContent = $this->allContents->find($contentId);
        
        if ($this->currentContent) {
            $this->currentIndex = $this->allContents->search(function ($item) use ($contentId) {
                return $item->id == $contentId;
            });
            
            // Marca come in lettura se non è già letto
            if ($this->contentStatuses[$contentId] === 'unread') {
                UserTheoryProgress::markAsReading(auth()->id(), $contentId);
                $this->contentStatuses[$contentId] = 'reading';
                $this->calculateStats();
            }
            
            // Switch to focus mode when loading content
            if ($this->viewMode === 'grid') {
                $this->viewMode = 'focus';
            }
        }
    }

    public function toggleContentStatus($contentId): void
    {
        $currentStatus = $this->contentStatuses[$contentId] ?? 'unread';
        $newStatus = $currentStatus === 'read' ? 'unread' : 'read';
        
        if ($newStatus === 'read') {
            UserTheoryProgress::markAsRead(auth()->id(), $contentId);
        } else {
            UserTheoryProgress::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'theory_content_id' => $contentId,
                ],
                [
                    'status' => 'unread',
                    'completed_at' => null,
                ]
            );
        }
        
        $this->contentStatuses[$contentId] = $newStatus;
        $this->calculateStats();
        
        // Emit event for real-time updates
        $this->dispatch('statsUpdated', stats: $this->stats);
    }

    public function markAsComplete(): void
    {
        if ($this->currentContent) {
            $this->toggleContentStatus($this->currentContent->id);
        }
    }

    public function nextContent(): void
    {
        if ($this->currentIndex < $this->allContents->count() - 1) {
            // Auto-mark current as read
            if ($this->currentContent && $this->contentStatuses[$this->currentContent->id] !== 'read') {
                $this->toggleContentStatus($this->currentContent->id);
            }
            
            $next = $this->allContents[$this->currentIndex + 1];
            $this->loadContent($next->id);
        }
    }

    public function previousContent(): void
    {
        if ($this->currentIndex > 0) {
            $prev = $this->allContents[$this->currentIndex - 1];
            $this->loadContent($prev->id);
        }
    }

    public function jumpToContent($contentId): void
    {
        $this->loadContent($contentId);
    }

    public function switchViewMode($mode): void
    {
        $this->viewMode = $mode;
    }

    public function toggleProgress(): void
    {
        $this->showProgress = !$this->showProgress;
    }

    public function filterByStatus($status): void
    {
        $this->filterStatus = $status;
    }

    public function getFilteredContentsProperty(): Collection
    {
        $filtered = $this->allContents;
        
        // Apply status filter
        if ($this->filterStatus !== 'all') {
            $filtered = $filtered->filter(function ($content) {
                $status = $this->contentStatuses[$content->id] ?? 'unread';
                return $status === $this->filterStatus;
            });
        }
        
        // Apply search filter
        if ($this->searchQuery) {
            $filtered = $filtered->filter(function ($content) {
                return str_contains(strtolower($content->content), strtolower($this->searchQuery)) ||
                       str_contains(strtolower($content->code), strtolower($this->searchQuery)) ||
                       str_contains(strtolower($content->subtopic->title), strtolower($this->searchQuery));
            });
        }
        
        return $filtered;
    }

    public function getProgressBySubtopicProperty(): Collection
    {
        return $this->allContents->groupBy('subtopic_id')->map(function ($contents, $subtopicId) {
            $subtopic = $contents->first()->subtopic;
            $total = $contents->count();
            $completed = $contents->filter(function ($content) {
                return ($this->contentStatuses[$content->id] ?? 'unread') === 'read';
            })->count();
            
            return [
                'id' => $subtopicId,
                'title' => $subtopic->title,
                'code' => $subtopic->code,
                'total' => $total,
                'completed' => $completed,
                'percentage' => $total > 0 ? round(($completed / $total) * 100) : 0
            ];
        });
    }

    public function markAllAsRead(): void
    {
        foreach ($this->allContents as $content) {
            if ($this->contentStatuses[$content->id] !== 'read') {
                UserTheoryProgress::markAsRead(auth()->id(), $content->id);
                $this->contentStatuses[$content->id] = 'read';
            }
        }
        
        $this->calculateStats();
        $this->dispatch('statsUpdated', stats: $this->stats);
    }

    public function resetProgress(): void
    {
        UserTheoryProgress::where('user_id', auth()->id())
            ->whereIn('theory_content_id', $this->allContents->pluck('id'))
            ->delete();
            
        foreach ($this->allContents as $content) {
            $this->contentStatuses[$content->id] = 'unread';
        }
        
        $this->calculateStats();
        $this->dispatch('statsUpdated', stats: $this->stats);
    }

    public function backToTopics(): void
    {
        $this->redirect(TheoryIndex::getUrl());
    }

    #[On('statsUpdated')]
    public function refreshStats($stats): void
    {
        $this->stats = $stats;
    }
}