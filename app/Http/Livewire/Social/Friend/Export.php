<?php

namespace App\Http\Livewire\Social\Friend;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;

class Export extends Component
{
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
    
    public function mount()
    {
        $this->loadUsers();
    }
    
    public function loadUsers()
    {
        $this->selectedFriends = [];
        $this->selectAll = false;
    }
    
    public function updatedSelectAll()
    {
        if ($this->selectAll) {
            $users = $this->getUsersByType();
            $this->selectedFriends = $users->pluck('id')->toArray();
        } else {
            $this->selectedFriends = [];
        }
    }
    
    public function updatedExportType()
    {
        $this->loadUsers();
    }
    
    public function getUsersByType()
    {
        $query = null;
        
        switch ($this->exportType) {
            case 'friends':
                $query = auth()->user()->friends();
                break;
            case 'followers':
                $query = auth()->user()->followers();
                break;
            case 'following':
                $query = auth()->user()->following();
                break;
        }
        
        if ($this->search) {
            $query->where('name', 'like', "%{$this->search}%");
        }
        
        return $query->get();
    }
    
    public function export()
    {
        $this->validate();
        
        if (empty($this->selectedFriends)) {
            session()->flash('error', 'Please select at least one user to export');
            return;
        }
        
        $users = User::whereIn('id', $this->selectedFriends)->get();
        $filename = auth()->user()->username . '-' . $this->exportType . '-' . now()->format('Y-m-d');
        $content = '';
        
        switch ($this->exportFormat) {
            case 'csv':
                $content = $this->generateCsv($users);
                $filename .= '.csv';
                $contentType = 'text/csv';
                break;
                
            case 'json':
                $content = $this->generateJson($users);
                $filename .= '.json';
                $contentType = 'application/json';
                break;
                
            case 'vcf':
                $content = $this->generateVcf($users);
                $filename .= '.vcf';
                $contentType = 'text/vcard';
                break;
        }
        
        // Store the file temporarily
        $path = 'exports/' . $filename;
        Storage::put($path, $content);
        
        // Generate a temporary URL (valid for 1 hour)
        $url = Storage::temporaryUrl($path, now()->addHour());
        
        // Schedule cleanup after 1 hour
        // In a real app, you would use a job scheduler like Laravel's Task Scheduling
        // For now, we'll rely on the temporary URL expiration
        
        session()->flash('download', [
            'url' => $url,
            'filename' => $filename
        ]);
        
        session()->flash('message', 'Your export is ready for download');
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
            $csv .= "{$user->name},{$user->username}";
            
            if ($this->includeEmails) {
                $csv .= ",{$user->email}";
            }
            
            if ($this->includePhones) {
                $csv .= ",{$user->phone}";
            }
            
            $csv .= "\n";
        }
        
        return $csv;
    }
    
    private function generateJson($users)
    {
        $data = [];
        
        foreach ($users as $user) {
            $userData = [
                'name' => $user->name,
                'username' => $user->username
            ];
            
            if ($this->includeEmails) {
                $userData['email'] = $user->email;
            }
            
            if ($this->includePhones) {
                $userData['phone'] = $user->phone;
            }
            
            $data[] = $userData;
        }
        
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
        
        return view('livewire.friends.export', [
            'users' => $users
        ])->layout('layouts.app');
    }
}
