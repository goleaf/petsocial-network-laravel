<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class GroupEventsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('group_events')->count() === 0) {
            $groups = DB::table('groups')->get();
            $users = User::all();
            
            if ($groups->count() > 0) {
                $eventTitles = [
                    'Pet Meetup',
                    'Training Workshop',
                    'Adoption Drive',
                    'Pet Health Seminar',
                    'Grooming Clinic',
                    'Pet Photography Session',
                    'Agility Training',
                    'Pet First Aid Workshop',
                    'Breed Showcase',
                    'Pet Nutrition Talk',
                    'Pet-Friendly Hike',
                    'Beach Day with Pets',
                    'Pet Costume Contest',
                    'Fundraiser for Animal Shelter',
                    'Pet Birthday Party'
                ];
                
                $locations = [
                    'Central Park',
                    'Community Center',
                    'Local Pet Store',
                    'Animal Shelter',
                    'Veterinary Clinic',
                    'Dog Park',
                    'Beach',
                    'Hiking Trail',
                    'Training Center',
                    'Pet-Friendly Cafe',
                    'Botanical Garden',
                    'City Square',
                    'Convention Center',
                    'Local School',
                    'Public Library'
                ];
                
                // Create 1-3 events for each group
                foreach ($groups as $group) {
                    $eventCount = rand(1, 3);
                    $groupMembers = DB::table('group_members')
                        ->where('group_id', $group->id)
                        ->where('status', 'active')
                        ->whereIn('role', ['admin', 'moderator'])
                        ->get();
                    
                    if ($groupMembers->count() > 0) {
                        $groupMembersArray = $groupMembers->toArray();
                        
                        for ($i = 0; $i < $eventCount; $i++) {
                            // Get a random organizer
                            $organizerIndex = array_rand($groupMembersArray);
                            $organizer = $groupMembersArray[$organizerIndex];
                            $organizerUser = $users->firstWhere('id', $organizer->user_id);
                            $title = $eventTitles[array_rand($eventTitles)];
                            $location = $locations[array_rand($locations)];
                            
                            // Determine if event is in the past, present, or future
                            $timeframe = rand(0, 2);
                            if ($timeframe == 0) {
                                // Past event
                                $startDate = now()->subDays(rand(1, 30));
                                $endDate = (clone $startDate)->addHours(rand(1, 5));
                            } elseif ($timeframe == 1) {
                                // Current/ongoing event
                                $startDate = now()->subHours(rand(1, 5));
                                $endDate = now()->addHours(rand(1, 5));
                            } else {
                                // Future event
                                $startDate = now()->addDays(rand(1, 30));
                                $endDate = (clone $startDate)->addHours(rand(1, 5));
                            }
                            
                            $eventId = DB::table('group_events')->insertGetId([
                                'title' => $title . ' ' . ($i + 1),
                                'description' => 'Join us for this exciting ' . strtolower($title) . ' event! Bring your pets and enjoy a day of fun and learning.',
                                'group_id' => $group->id,
                                'user_id' => $organizerUser->id,
                                'location' => $location,
                                'start_date' => $startDate,
                                'end_date' => $endDate,
                                'is_online' => rand(0, 1),
                                'max_attendees' => rand(0, 1) ? rand(10, 50) : null,
                                'is_published' => true,
                                'created_at' => now()->subDays(rand(30, 60)),
                                'updated_at' => now(),
                            ]);
                            
                            // Add 5-15 attendees
                            $attendeeCount = rand(5, 15);
                            $allGroupMembers = DB::table('group_members')
                                ->where('group_id', $group->id)
                                ->where('status', 'active')
                                ->get();
                            
                            if ($allGroupMembers->count() > 0) {
                                $allGroupMembersArray = $allGroupMembers->toArray();
                                $attendeeIndices = [];
                                
                                // Get random attendees
                                $maxAttendees = min($attendeeCount, count($allGroupMembersArray));
                                if ($maxAttendees > 0) {
                                    $attendeeIndices = array_rand($allGroupMembersArray, $maxAttendees);
                                    
                                    // Make sure $attendeeIndices is an array even if only one attendee is selected
                                    if (!is_array($attendeeIndices)) {
                                        $attendeeIndices = [$attendeeIndices];
                                    }
                                    
                                    foreach ($attendeeIndices as $index) {
                                        $attendee = $allGroupMembersArray[$index];
                                        $attendeeUser = $users->firstWhere('id', $attendee->user_id);
                                        
                                        // Check if this attendance record already exists
                                        $exists = DB::table('group_event_attendees')
                                            ->where('group_event_id', $eventId)
                                            ->where('user_id', $attendeeUser->id)
                                            ->exists();
                                        
                                        if (!$exists) {
                                            DB::table('group_event_attendees')->insert([
                                                'group_event_id' => $eventId,
                                                'user_id' => $attendeeUser->id,
                                                'status' => 'going', // Only use 'going' as it's the only valid status in the constraint
                                                'reminder_set' => rand(0, 1),
                                                'created_at' => now()->subDays(rand(1, 30)),
                                                'updated_at' => now(),
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                
                $this->command->info('Group events seeded successfully.');
            } else {
                $this->command->info('No groups found. Please seed groups first.');
            }
        }
    }
}
