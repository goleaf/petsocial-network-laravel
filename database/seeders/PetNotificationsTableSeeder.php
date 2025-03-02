<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Pet;

class PetNotificationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('pet_notifications')->count() === 0) {
            $pets = Pet::all();
            
            $notificationTypes = [
                'new_friend_request', 'friend_request_accepted', 
                'birthday', 'vaccination_reminder', 'medication_reminder',
                'vet_appointment'
            ];
            
            foreach ($pets as $pet) {
                // Each pet has 0-5 notifications
                for ($i = 0; $i < rand(0, 5); $i++) {
                    $notificationType = $notificationTypes[rand(0, count($notificationTypes) - 1)];
                    $data = [];
                    
                    // Create notification data based on type
                    switch ($notificationType) {
                        case 'new_friend_request':
                            if ($pets->count() > 1) {
                                $requester = $pets->where('id', '!=', $pet->id)->random();
                                $data = [
                                    'requester_id' => $requester->id,
                                    'requester_name' => $requester->name,
                                    'requester_type' => $requester->type
                                ];
                            }
                            break;
                            
                        case 'friend_request_accepted':
                            if ($pets->count() > 1) {
                                $accepter = $pets->where('id', '!=', $pet->id)->random();
                                $data = [
                                    'accepter_id' => $accepter->id,
                                    'accepter_name' => $accepter->name,
                                    'accepter_type' => $accepter->type
                                ];
                            }
                            break;
                            
                        case 'birthday':
                            $data = [
                                'age' => rand(1, 15),
                                'birthday_date' => now()->addDays(rand(1, 30))->format('Y-m-d')
                            ];
                            break;
                            
                        case 'vaccination_reminder':
                            $data = [
                                'vaccination_type' => ['Rabies', 'Distemper', 'Parvovirus', 'Bordetella'][rand(0, 3)],
                                'due_date' => now()->addDays(rand(1, 30))->format('Y-m-d')
                            ];
                            break;
                            
                        case 'medication_reminder':
                            $data = [
                                'medication_name' => ['Heartworm', 'Flea and Tick', 'Antibiotic', 'Pain Relief'][rand(0, 3)],
                                'dosage' => rand(1, 5) . ' ' . ['pill', 'ml', 'tablet', 'application'][rand(0, 3)],
                                'frequency' => ['daily', 'twice daily', 'weekly', 'monthly'][rand(0, 3)]
                            ];
                            break;
                            
                        case 'vet_appointment':
                            $data = [
                                'appointment_date' => now()->addDays(rand(1, 30))->format('Y-m-d H:i:s'),
                                'vet_name' => ['Dr. Smith', 'Dr. Johnson', 'Dr. Williams', 'Dr. Brown'][rand(0, 3)],
                                'reason' => ['Annual Checkup', 'Vaccination', 'Illness', 'Injury', 'Surgery'][rand(0, 4)]
                            ];
                            break;
                    }
                    
                    // Only insert if we have valid data
                    if (!empty($data)) {
                        // Generate content based on notification type
                        $content = '';
                        switch ($notificationType) {
                            case 'new_friend_request':
                                $content = isset($data['requester_name']) ? "{$data['requester_name']} sent you a friend request!" : 'You received a friend request!';
                                break;
                            case 'friend_request_accepted':
                                $content = isset($data['accepter_name']) ? "{$data['accepter_name']} accepted your friend request!" : 'Your friend request was accepted!';
                                break;
                            case 'birthday':
                                $content = "Happy Birthday! You are now {$data['age']} years old!";
                                break;
                            case 'vaccination_reminder':
                                $content = "Reminder: Your {$data['vaccination_type']} vaccination is due on {$data['due_date']}.";
                                break;
                            case 'medication_reminder':
                                $content = "Time to take your {$data['medication_name']} ({$data['dosage']}) {$data['frequency']}.";
                                break;
                            case 'vet_appointment':
                                $content = "You have a vet appointment with {$data['vet_name']} on {$data['appointment_date']} for {$data['reason']}.";
                                break;
                            default:
                                $content = "You have a new notification!";
                        }

                        $notificationData = [
                            'pet_id' => $pet->id,
                            'type' => $notificationType,
                            'content' => $content,
                            'data' => json_encode($data),
                            'read_at' => rand(0, 1) ? now()->subDays(rand(0, 5)) : null,
                            'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 24)),
                            'updated_at' => now()->subDays(rand(0, 15))->subHours(rand(0, 24)),
                        ];
                        
                        // Check if user_id column exists
                        if (Schema::hasColumn('pet_notifications', 'user_id')) {
                            $notificationData['user_id'] = $pet->user_id;
                        }
                        
                        DB::table('pet_notifications')->insert($notificationData);
                    }
                }
            }
            
            $this->command->info('Pet notifications seeded successfully.');
        }
    }
}
