<?php

namespace App\Http\Livewire\Common\Friend;

use App\Models\User;
use App\Models\Pet;
use App\Traits\EntityTypeTrait;
use App\Traits\FriendshipTrait;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Cache;

class Finder extends Component
{
    use WithFileUploads, EntityTypeTrait, FriendshipTrait;
    
    public $search = '';
    public $importFile;
    public $importType = 'csv';
    public $importResults = [];
    public $showImportModal = false;
    public $processingImport = false;
    
    protected $listeners = ['refresh' => '$refresh'];

    public function mount($entityType = 'user', $entityId = null)
    {
        $this->entityType = $entityType;
        $this->entityId = $entityId ?? ($entityType === 'user' ? auth()->id() : null);
        
        if (!$this->entityId) {
            throw new \InvalidArgumentException("Entity ID is required");
        }
    }
    
    public function updatedSearch()
    {
        // Reset any previous import results when searching
        $this->importResults = [];
    }
    
    public function showImportModal()
    {
        $this->showImportModal = true;
    }
    
    public function closeImportModal()
    {
        $this->showImportModal = false;
        $this->importFile = null;
        $this->importResults = [];
    }
    
    public function processImport()
    {
        $this->validate([
            'importFile' => 'required|file|max:1024',
            'importType' => 'required|in:csv,vcf',
        ]);
        
        $this->processingImport = true;
        
        try {
            $content = $this->importFile->get();
            
            if ($this->importType === 'csv') {
                $this->importResults = $this->processCsvImport($content);
            } else {
                $this->importResults = $this->processVcfImport($content);
            }
            
            $this->processingImport = false;
        } catch (\Exception $e) {
            $this->processingImport = false;
            session()->flash('error', 'Error processing import: ' . $e->getMessage());
        }
    }
    
    protected function processCsvImport($content)
    {
        $lines = explode("\n", $content);
        $results = [];
        
        // Skip header row
        $header = str_getcsv(array_shift($lines));
        
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            
            $data = str_getcsv($line);
            if (count($data) < 2) continue;
            
            $name = $data[0] ?? '';
            $email = $data[1] ?? '';
            $phone = $data[2] ?? '';
            
            if (empty($name)) continue;
            
            // Search for existing entities
            if ($this->entityType === 'pet') {
                $entity = Pet::where('name', 'like', $name)
                    ->orWhere('email', $email)
                    ->first();
            } else {
                $entity = User::where('name', 'like', $name)
                    ->orWhere('email', $email)
                    ->orWhere('phone', $phone)
                    ->first();
            }
            
            $results[] = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'entity' => $entity,
                'status' => $entity ? ($this->areFriends($entity->id) ? 'friend' : 'found') : 'not_found'
            ];
        }
        
        return $results;
    }
    
    protected function processVcfImport($content)
    {
        $vcards = explode("END:VCARD", $content);
        $results = [];
        
        foreach ($vcards as $vcard) {
            if (empty(trim($vcard))) continue;
            
            // Extract name
            preg_match('/FN:(.*?)[\r\n]/i', $vcard, $nameMatches);
            $name = $nameMatches[1] ?? '';
            
            // Extract email
            preg_match('/EMAIL.*?:(.*?)[\r\n]/i', $vcard, $emailMatches);
            $email = $emailMatches[1] ?? '';
            
            // Extract phone
            preg_match('/TEL.*?:(.*?)[\r\n]/i', $vcard, $phoneMatches);
            $phone = $phoneMatches[1] ?? '';
            
            if (empty($name)) continue;
            
            // Search for existing entities
            if ($this->entityType === 'pet') {
                $entity = Pet::where('name', 'like', $name)
                    ->orWhere('email', $email)
                    ->first();
            } else {
                $entity = User::where('name', 'like', $name)
                    ->orWhere('email', $email)
                    ->orWhere('phone', $phone)
                    ->first();
            }
            
            $results[] = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'entity' => $entity,
                'status' => $entity ? ($this->areFriends($entity->id) ? 'friend' : 'found') : 'not_found'
            ];
        }
        
        return $results;
    }
    
    public function sendFriendRequest($entityId)
    {
        if (!$this->isAuthorized()) {
            session()->flash('error', 'You are not authorized to perform this action');
            return;
        }
        
        $this->addFriend($entityId);
        
        // Update the status in the import results
        foreach ($this->importResults as $key => $result) {
            if ($result['entity'] && $result['entity']->id === $entityId) {
                $this->importResults[$key]['status'] = 'request_sent';
            }
        }
        
        $this->emit('friendRequestSent', $entityId);
        $this->emit('refresh');
    }
    
    public function getSearchResults()
    {
        if (strlen($this->search) < 3) {
            return collect();
        }
        
        $cacheKey = "{$this->entityType}_search_{$this->search}";
        
        return Cache::remember($cacheKey, now()->addMinutes(5), function() {
            $friendIds = $this->getFriendIds();
            $entityModel = $this->getEntityModel();
            
            $query = $entityModel::where(function($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('username', 'like', "%{$this->search}%");
                  
                if ($this->entityType === 'user') {
                    $q->orWhere('email', 'like', "%{$this->search}%");
                }
            });
            
            // Exclude the current entity and friends
            $query->where('id', '!=', $this->entityId)
                  ->whereNotIn('id', $friendIds);
                  
            return $query->limit(20)->get();
        });
    }
    
    public function render()
    {
        $searchResults = $this->search ? $this->getSearchResults() : collect();
        
        return view('livewire.common.friend.finder', [
            'entity' => $this->getEntity(),
            'searchResults' => $searchResults
        ]);
    }
}
