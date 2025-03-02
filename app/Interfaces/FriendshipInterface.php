<?php

namespace App\Interfaces;

interface FriendshipInterface
{
    /**
     * Accept a friendship request
     * 
     * @return mixed
     */
    public function accept();
    
    /**
     * Decline a friendship request
     * 
     * @return mixed
     */
    public function decline();
    
    /**
     * Block a friendship
     * 
     * @return mixed
     */
    public function block();
    
    /**
     * Update the friendship category
     * 
     * @param string|null $category
     * @return mixed
     */
    public function categorize($category);
}
