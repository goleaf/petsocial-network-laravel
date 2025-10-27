<?php

namespace App\Http\Livewire\Common\Friend;

use App\Models\User;
use App\Models\Pet;
use App\Traits\EntityTypeTrait;
use App\Traits\FriendshipTrait;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class Export extends Component
{
    use EntityTypeTrait, FriendshipTrait;
    
    public $exportFormat = 'csv';
    public $includeEmails = false;
    public $includePhones = false;
    public $exportType = 'friends'; // 'friends', 'followers', 'following'
    public $selectedFriends = [];
    public $selectAll = false;
    public $search = '';
    
    protected $rules = [
        'exportFormat' => 'required|in:csv,json,vcf',
        'includeEmails' => 'boolean',
        'includePhones' => 'boolean',
        'exportType' => 'required|in:friends,followers,following',
    ];
    
    public function mount($entityType = 'user', $entityId = null)
    {
        $this->entityType = $entityType;
        $this->entityId = $entityId ?? auth()->id();
        $this->loadUsers();
    }
    
    public function loadUsers()
    {
        // Clear selected friends when loading users
        $this->selectedFriends = [];
        $this->selectAll = false;
    }
    
    public function getUsersByType()
    {
        $entity = $this->getEntity();
        $cacheKey = "{$this->entityType}_{$this->entityId}_export_{$this->exportType}_{$this->search}";

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($entity) {
            // Build the base query that returns the raw models we need to transform later.
            $query = $this->buildBaseQuery($entity);

            // Apply text filtering when a search term is supplied so large friend lists can be narrowed down quickly.
            if ($this->search) {
                $searchTerm = "%{$this->search}%";

                if ($this->entityType === 'pet') {
                    $query->where(function ($innerQuery) use ($searchTerm) {
                        $innerQuery
                            ->where('name', 'like', $searchTerm)
                            ->orWhereHas('user', function ($ownerQuery) use ($searchTerm) {
                                $ownerQuery
                                    ->where('name', 'like', $searchTerm)
                                    ->orWhere('username', 'like', $searchTerm)
                                    ->orWhere('email', 'like', $searchTerm);
                            });
                    });
                } else {
                    $query->where(function ($innerQuery) use ($searchTerm) {
                        $innerQuery
                            ->where('name', 'like', $searchTerm)
                            ->orWhere('username', 'like', $searchTerm)
                            ->orWhere('email', 'like', $searchTerm);
                    });
                }
            }

            $results = $query->orderBy('name')->get();

            // Normalize the data so the Blade view and export generators can rely on the same shape
            // regardless of whether we are exporting users or pets.
            return $results->map(function ($model) {
                if ($this->entityType === 'pet') {
                    $owner = $model->relationLoaded('user') ? $model->user : $model->user()->first();

                    return (object) [
                        'id' => $model->id,
                        'name' => $model->name,
                        'username' => optional($owner)->username ?? $model->name,
                        'email' => optional($owner)->email,
                        'phone' => optional($owner)->phone,
                        'avatar' => $model->avatar_url ?? $model->avatar ?? '/images/default-pet-avatar.png',
                    ];
                }

                return (object) [
                    'id' => $model->id,
                    'name' => $model->name,
                    'username' => $model->username,
                    'email' => $model->email,
                    'phone' => $model->phone,
                    'avatar' => $model->avatar ?? '/images/default-avatar.png',
                ];
            });
        });
    }

    /**
     * Build the underlying query used to collect exportable records for the selected entity type.
     *
     * @param  mixed  $entity
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildBaseQuery($entity)
    {
        // When exporting for a user entity we may need to look at friends, followers or following lists.
        if ($this->entityType === 'user') {
            if ($this->exportType === 'followers') {
                return $entity->followers()->getQuery();
            }

            if ($this->exportType === 'following') {
                return $entity->following()->getQuery();
            }

            $friendIds = $this->getFriendIds();

            return User::query()->whereIn('id', $friendIds);
        }

        $friendIds = $this->getFriendIds();

        return Pet::query()->with('user')->whereIn('id', $friendIds);
    }
    
    public function toggleSelectAll()
    {
        $this->selectAll = !$this->selectAll;
        
        if ($this->selectAll) {
            $users = $this->getUsersByType();
            $this->selectedFriends = $users->pluck('id')->toArray();
        } else {
            $this->selectedFriends = [];
        }
    }
    
    public function updatingSearch()
    {
        // Reset selection when search changes
        $this->selectedFriends = [];
        $this->selectAll = false;
    }
    
    public function updatingExportType()
    {
        // Reset selection when export type changes
        $this->selectedFriends = [];
        $this->selectAll = false;
    }
    
    public function export()
    {
        $this->validate();
        
        if (empty($this->selectedFriends)) {
            session()->flash('error', __('friends.select_at_least_one'));
            return;
        }
        
        $users = $this->getUsersByType()->whereIn('id', $this->selectedFriends);
        
        if ($users->isEmpty()) {
            session()->flash('error', __('friends.no_data_to_export'));
            return;
        }
        
        $fileName = $this->entityType . '_' . $this->exportType . '_' . date('Y-m-d') . '.' . $this->exportFormat;
        $content = '';
        
        if ($this->exportFormat === 'csv') {
            $content = $this->generateCsv($users);
        } elseif ($this->exportFormat === 'json') {
            $content = $this->generateJson($users);
        } else { // vcf
            $content = $this->generateVcf($users);
        }
        
        $path = 'exports/' . $fileName;
        Storage::put($path, $content);
        
        return response()->download(storage_path('app/' . $path))->deleteFileAfterSend();
    }
    
    private function generateCsv($users)
    {
        $csv = "Name,Username";
        
        if ($this->includeEmails) {
            $csv .= ",Email";
        }
        
        if ($this->includePhones) {
            $csv .= ",Phone";
        }
        
        $csv .= "\n";
        
        foreach ($users as $user) {
            $csv .= "\"{$user->name}\",\"{$user->username}\"";
            
            if ($this->includeEmails) {
                $csv .= ",\"{$user->email}\"";
            }
            
            if ($this->includePhones) {
                $csv .= ",\"{$user->phone}\"";
            }
            
            $csv .= "\n";
        }
        
        return $csv;
    }
    
    private function generateJson($users)
    {
        $data = $users->map(function ($user) {
            $userData = [
                'name' => $user->name,
                'username' => $user->username,
            ];
            
            if ($this->includeEmails) {
                $userData['email'] = $user->email;
            }
            
            if ($this->includePhones) {
                $userData['phone'] = $user->phone;
            }
            
            return $userData;
        });
        
        return json_encode($data, JSON_PRETTY_PRINT);
    }
    
    private function generateVcf($users)
    {
        $vcf = "";
        
        foreach ($users as $user) {
            $vcf .= "BEGIN:VCARD\r\n";
            $vcf .= "VERSION:3.0\r\n";
            $vcf .= "FN:{$user->name}\r\n";
            $vcf .= "NICKNAME:{$user->username}\r\n";
            
            if ($this->includeEmails && $user->email) {
                $vcf .= "EMAIL;TYPE=INTERNET:{$user->email}\r\n";
            }
            
            if ($this->includePhones && $user->phone) {
                $vcf .= "TEL;TYPE=CELL:{$user->phone}\r\n";
            }
            
            $vcf .= "END:VCARD\r\n";
        }
        
        return $vcf;
    }
    
    public function render()
    {
        $users = $this->getUsersByType();
        
        return view('livewire.common.friend.export', [
            'users' => $users
        ]);
    }
}
